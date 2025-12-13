<?php
/**
 * Módulo de Proyectos GestionAdmin
 *
 * Gestiona proyectos dentro de casos.
 * Los proyectos son la unidad de trabajo donde se asignan tareas:
 * - Presupuesto de horas y dinero
 * - Tracking de avance
 * - Configuración de visibilidad en portal cliente
 *
 * @package GestionAdmin_Wolk
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Proyectos {

    /**
     * Obtener todos los proyectos
     *
     * @param array $filters Filtros opcionales (caso_id, estado, cliente_id)
     * @return array Lista de proyectos con datos relacionados
     */
    public static function get_all($filters = array()) {
        global $wpdb;

        // Definir tablas
        $table = $wpdb->prefix . 'ga_proyectos';
        $table_casos = $wpdb->prefix . 'ga_casos';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        // Construir SQL con JOINs
        $sql = "SELECT p.*,
                       c.numero as caso_numero,
                       c.titulo as caso_titulo,
                       cl.id as cliente_id,
                       cl.nombre_comercial as cliente_nombre,
                       u.display_name as responsable_nombre
                FROM {$table} p
                LEFT JOIN {$table_casos} c ON p.caso_id = c.id
                LEFT JOIN {$table_clientes} cl ON c.cliente_id = cl.id
                LEFT JOIN {$wpdb->users} u ON p.responsable_id = u.ID
                WHERE 1=1";

        // Filtro por caso
        if (!empty($filters['caso_id'])) {
            $sql .= $wpdb->prepare(" AND p.caso_id = %d", $filters['caso_id']);
        }

        // Filtro por estado
        if (!empty($filters['estado'])) {
            $sql .= $wpdb->prepare(" AND p.estado = %s", $filters['estado']);
        }

        // Filtro por cliente (a través del caso)
        if (!empty($filters['cliente_id'])) {
            $sql .= $wpdb->prepare(" AND c.cliente_id = %d", $filters['cliente_id']);
        }

        // Ordenar por fecha de inicio descendente
        $sql .= " ORDER BY p.fecha_inicio DESC, p.id DESC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un proyecto por ID
     *
     * @param int $id ID del proyecto
     * @return object|null Objeto proyecto con datos relacionados
     */
    public static function get($id) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_proyectos';
        $table_casos = $wpdb->prefix . 'ga_casos';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT p.*,
                    c.numero as caso_numero,
                    c.titulo as caso_titulo,
                    c.cliente_id,
                    cl.nombre_comercial as cliente_nombre
             FROM {$table} p
             LEFT JOIN {$table_casos} c ON p.caso_id = c.id
             LEFT JOIN {$table_clientes} cl ON c.cliente_id = cl.id
             WHERE p.id = %d",
            $id
        ));
    }

    /**
     * Obtener proyecto por código
     *
     * @param string $codigo Código del proyecto (PRY-XXX)
     * @return object|null Objeto proyecto o null
     */
    public static function get_by_codigo($codigo) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_proyectos';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE codigo = %s",
            $codigo
        ));
    }

    /**
     * Generar código único para proyecto
     *
     * Formato: PRY-XXX donde XXX es consecutivo de 3 dígitos
     *
     * @return string Código único generado
     */
    public static function generate_codigo() {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_proyectos';

        // Obtener último código
        $last = $wpdb->get_var("SELECT codigo FROM {$table} ORDER BY id DESC LIMIT 1");

        if ($last) {
            // Extraer número: PRY-XXX -> XXX
            $num = intval(substr($last, 4)) + 1;
        } else {
            $num = 1;
        }

        return 'PRY-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Guardar proyecto (crear o actualizar)
     *
     * @param int $id ID del proyecto (0 para nuevo)
     * @param array $data Datos del proyecto
     * @return int|WP_Error ID del proyecto guardado o error
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_proyectos';

        // Validar caso requerido
        if (empty($data['caso_id'])) {
            return new WP_Error('missing_case', __('El caso es obligatorio', 'gestionadmin-wolk'));
        }

        // Validar nombre requerido
        if (empty($data['nombre'])) {
            return new WP_Error('missing_name', __('El nombre es obligatorio', 'gestionadmin-wolk'));
        }

        // Validar código único
        if (!empty($data['codigo'])) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE codigo = %s AND id != %d",
                $data['codigo'],
                $id
            ));

            if ($existing) {
                return new WP_Error('duplicate_code', __('El código de proyecto ya existe', 'gestionadmin-wolk'));
            }
        }

        if ($id > 0) {
            // Actualizar proyecto existente
            $result = $wpdb->update($table, $data, array('id' => $id));

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
            }

            return $id;
        } else {
            // Crear nuevo proyecto
            $data['created_at'] = current_time('mysql');
            $data['created_by'] = get_current_user_id();

            // Generar código si no se proporciona
            if (empty($data['codigo'])) {
                $data['codigo'] = self::generate_codigo();
            }

            $result = $wpdb->insert($table, $data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al insertar', 'gestionadmin-wolk'));
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Cambiar estado de un proyecto
     *
     * @param int $id ID del proyecto
     * @param string $estado Nuevo estado
     * @return bool True si se actualizó correctamente
     */
    public static function change_estado($id, $estado) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_proyectos';

        $data = array('estado' => $estado);

        // Si se completa, registrar fecha de fin real
        if (in_array($estado, array('COMPLETADO', 'CANCELADO'))) {
            $data['fecha_fin_real'] = current_time('Y-m-d');
        }

        return $wpdb->update($table, $data, array('id' => $id)) !== false;
    }

    /**
     * Obtener proyectos para dropdown
     *
     * @param int $caso_id ID del caso (0 para todos activos)
     * @return array Lista simplificada para select
     */
    public static function get_for_dropdown($caso_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_proyectos';
        $table_casos = $wpdb->prefix . 'ga_casos';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        $sql = "SELECT p.id, p.codigo, p.nombre, p.caso_id,
                       c.titulo as caso_titulo,
                       cl.nombre_comercial as cliente_nombre
                FROM {$table} p
                LEFT JOIN {$table_casos} c ON p.caso_id = c.id
                LEFT JOIN {$table_clientes} cl ON c.cliente_id = cl.id
                WHERE p.estado NOT IN ('COMPLETADO', 'CANCELADO')";

        if ($caso_id > 0) {
            $sql .= $wpdb->prepare(" AND p.caso_id = %d", $caso_id);
        }

        $sql .= " ORDER BY p.nombre ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener estados de proyecto
     *
     * @return array Array asociativo estado => etiqueta
     */
    public static function get_estados() {
        return array(
            'PLANIFICACION' => __('Planificación', 'gestionadmin-wolk'),
            'EN_PROGRESO'   => __('En Progreso', 'gestionadmin-wolk'),
            'PAUSADO'       => __('Pausado', 'gestionadmin-wolk'),
            'COMPLETADO'    => __('Completado', 'gestionadmin-wolk'),
            'CANCELADO'     => __('Cancelado', 'gestionadmin-wolk'),
        );
    }

    /**
     * Actualizar horas consumidas de un proyecto
     *
     * Recalcula las horas desde la tabla de registro_horas
     *
     * @param int $proyecto_id ID del proyecto
     * @return bool True si se actualizó correctamente
     */
    public static function update_horas_consumidas($proyecto_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_proyectos';
        $table_horas = $wpdb->prefix . 'ga_registro_horas';

        // Sumar minutos efectivos y convertir a horas
        $minutos = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(minutos_efectivos) FROM {$table_horas} WHERE proyecto_id = %d",
            $proyecto_id
        ));

        $horas = round(($minutos ?: 0) / 60, 2);

        return $wpdb->update(
            $table,
            array('horas_consumidas' => $horas),
            array('id' => $proyecto_id)
        ) !== false;
    }

    /**
     * Actualizar porcentaje de avance de un proyecto
     *
     * Calcula basado en tareas completadas vs total
     *
     * @param int $proyecto_id ID del proyecto
     * @return bool True si se actualizó correctamente
     */
    public static function update_porcentaje_avance($proyecto_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_proyectos';
        $table_tareas = $wpdb->prefix . 'ga_tareas';

        // Contar tareas totales y completadas
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado IN ('APROBADA', 'PAGADA') THEN 1 ELSE 0 END) as completadas
             FROM {$table_tareas}
             WHERE proyecto_id = %d AND estado != 'CANCELADA'",
            $proyecto_id
        ));

        // Calcular porcentaje
        $porcentaje = 0;
        if ($stats && $stats->total > 0) {
            $porcentaje = round(($stats->completadas / $stats->total) * 100);
        }

        return $wpdb->update(
            $table,
            array('porcentaje_avance' => $porcentaje),
            array('id' => $proyecto_id)
        ) !== false;
    }

    /**
     * Obtener resumen de un proyecto
     *
     * @param int $id ID del proyecto
     * @return object Resumen con tareas, horas, avance
     */
    public static function get_summary($id) {
        global $wpdb;

        $table_tareas = $wpdb->prefix . 'ga_tareas';
        $table_horas = $wpdb->prefix . 'ga_registro_horas';

        // Estadísticas de tareas
        $tareas = $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'EN_PROGRESO' THEN 1 ELSE 0 END) as en_progreso,
                SUM(CASE WHEN estado IN ('APROBADA', 'PAGADA') THEN 1 ELSE 0 END) as completadas
             FROM {$table_tareas}
             WHERE proyecto_id = %d AND estado != 'CANCELADA'",
            $id
        ));

        // Horas registradas
        $horas = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(minutos_efectivos) as minutos_totales,
                COUNT(DISTINCT usuario_id) as usuarios_involucrados
             FROM {$table_horas}
             WHERE proyecto_id = %d",
            $id
        ));

        return (object) array(
            'tareas_total'          => $tareas->total ?: 0,
            'tareas_pendientes'     => $tareas->pendientes ?: 0,
            'tareas_en_progreso'    => $tareas->en_progreso ?: 0,
            'tareas_completadas'    => $tareas->completadas ?: 0,
            'horas_registradas'     => round(($horas->minutos_totales ?: 0) / 60, 2),
            'usuarios_involucrados' => $horas->usuarios_involucrados ?: 0,
        );
    }

    /**
     * Obtener proyectos por cliente
     *
     * @param int $cliente_id ID del cliente
     * @return array Lista de proyectos del cliente
     */
    public static function get_by_cliente($cliente_id) {
        return self::get_all(array('cliente_id' => $cliente_id));
    }
}
