<?php
/**
 * Módulo de Casos GestionAdmin
 *
 * Gestiona casos/expedientes de clientes.
 * Un caso agrupa proyectos relacionados y tiene:
 * - Numeración automática: CASO-[CLIENTE]-[AÑO]-[CONSECUTIVO]
 * - Estados de seguimiento
 * - Presupuesto y responsable asignado
 *
 * @package GestionAdmin_Wolk
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Casos {

    /**
     * Obtener todos los casos
     *
     * @param array $filters Filtros opcionales (cliente_id, estado)
     * @return array Lista de casos con datos relacionados
     */
    public static function get_all($filters = array()) {
        global $wpdb;

        // Definir tablas
        $table = $wpdb->prefix . 'ga_casos';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        // Construir SQL con JOIN a clientes
        $sql = "SELECT c.*,
                       cl.codigo as cliente_codigo,
                       cl.nombre_comercial as cliente_nombre,
                       u.display_name as responsable_nombre
                FROM {$table} c
                LEFT JOIN {$table_clientes} cl ON c.cliente_id = cl.id
                LEFT JOIN {$wpdb->users} u ON c.responsable_id = u.ID
                WHERE 1=1";

        // Aplicar filtro por cliente
        if (!empty($filters['cliente_id'])) {
            $sql .= $wpdb->prepare(" AND c.cliente_id = %d", $filters['cliente_id']);
        }

        // Aplicar filtro por estado
        if (!empty($filters['estado'])) {
            $sql .= $wpdb->prepare(" AND c.estado = %s", $filters['estado']);
        }

        // Ordenar por fecha de apertura descendente (más recientes primero)
        $sql .= " ORDER BY c.fecha_apertura DESC, c.id DESC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un caso por ID
     *
     * @param int $id ID del caso
     * @return object|null Objeto caso con datos relacionados
     */
    public static function get($id) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_casos';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT c.*,
                    cl.codigo as cliente_codigo,
                    cl.nombre_comercial as cliente_nombre
             FROM {$table} c
             LEFT JOIN {$table_clientes} cl ON c.cliente_id = cl.id
             WHERE c.id = %d",
            $id
        ));
    }

    /**
     * Obtener caso por número
     *
     * @param string $numero Número del caso
     * @return object|null Objeto caso o null
     */
    public static function get_by_numero($numero) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_casos';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE numero = %s",
            $numero
        ));
    }

    /**
     * Generar número único para caso
     *
     * Formato: CASO-[CLIENTE_CODIGO]-[AÑO]-[CONSECUTIVO]
     * Ejemplo: CASO-CLI001-2024-0001
     *
     * @param int $cliente_id ID del cliente
     * @return string Número único generado
     */
    public static function generate_numero($cliente_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_casos';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        // Obtener código del cliente
        $cliente_codigo = $wpdb->get_var($wpdb->prepare(
            "SELECT codigo FROM {$table_clientes} WHERE id = %d",
            $cliente_id
        ));

        // Si no hay cliente, usar genérico
        if (!$cliente_codigo) {
            $cliente_codigo = 'GEN';
        }

        // Año actual
        $year = date('Y');

        // Obtener último consecutivo del año para este cliente
        $like = 'CASO-' . $cliente_codigo . '-' . $year . '-%';
        $last = $wpdb->get_var($wpdb->prepare(
            "SELECT numero FROM {$table} WHERE numero LIKE %s ORDER BY id DESC LIMIT 1",
            $like
        ));

        if ($last) {
            // Extraer consecutivo: CASO-CLI001-2024-0001 -> 0001
            $parts = explode('-', $last);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }

        // Formatear número completo
        return 'CASO-' . $cliente_codigo . '-' . $year . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Guardar caso (crear o actualizar)
     *
     * @param int $id ID del caso (0 para nuevo)
     * @param array $data Datos del caso
     * @return int|WP_Error ID del caso guardado o error
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_casos';

        // Validar cliente requerido
        if (empty($data['cliente_id'])) {
            return new WP_Error('missing_client', __('El cliente es obligatorio', 'gestionadmin-wolk'));
        }

        // Validar título requerido
        if (empty($data['titulo'])) {
            return new WP_Error('missing_title', __('El título es obligatorio', 'gestionadmin-wolk'));
        }

        if ($id > 0) {
            // Actualizar caso existente
            $result = $wpdb->update($table, $data, array('id' => $id));

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
            }

            return $id;
        } else {
            // Crear nuevo caso
            $data['created_at'] = current_time('mysql');
            $data['created_by'] = get_current_user_id();

            // Generar número si no se proporciona
            if (empty($data['numero'])) {
                $data['numero'] = self::generate_numero($data['cliente_id']);
            }

            // Fecha de apertura por defecto
            if (empty($data['fecha_apertura'])) {
                $data['fecha_apertura'] = current_time('Y-m-d');
            }

            $result = $wpdb->insert($table, $data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al insertar', 'gestionadmin-wolk'));
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Cambiar estado de un caso
     *
     * @param int $id ID del caso
     * @param string $estado Nuevo estado
     * @return bool True si se actualizó correctamente
     */
    public static function change_estado($id, $estado) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_casos';

        $data = array('estado' => $estado);

        // Si se cierra, registrar fecha de cierre
        if (in_array($estado, array('CERRADO', 'CANCELADO'))) {
            $data['fecha_cierre_real'] = current_time('mysql');
        }

        return $wpdb->update($table, $data, array('id' => $id)) !== false;
    }

    /**
     * Obtener casos para dropdown
     *
     * @param int $cliente_id ID del cliente (0 para todos)
     * @return array Lista simplificada para select
     */
    public static function get_for_dropdown($cliente_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_casos';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        $sql = "SELECT c.id, c.numero, c.titulo, cl.nombre_comercial as cliente_nombre
                FROM {$table} c
                LEFT JOIN {$table_clientes} cl ON c.cliente_id = cl.id
                WHERE c.estado NOT IN ('CERRADO', 'CANCELADO')";

        if ($cliente_id > 0) {
            $sql .= $wpdb->prepare(" AND c.cliente_id = %d", $cliente_id);
        }

        $sql .= " ORDER BY c.fecha_apertura DESC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener tipos de caso
     *
     * @return array Array asociativo tipo => etiqueta
     */
    public static function get_tipos() {
        return array(
            'PROYECTO'    => __('Proyecto', 'gestionadmin-wolk'),
            'LEGAL'       => __('Legal', 'gestionadmin-wolk'),
            'SOPORTE'     => __('Soporte', 'gestionadmin-wolk'),
            'CONSULTORIA' => __('Consultoría', 'gestionadmin-wolk'),
            'OTRO'        => __('Otro', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener estados de caso
     *
     * @return array Array asociativo estado => etiqueta
     */
    public static function get_estados() {
        return array(
            'ABIERTO'     => __('Abierto', 'gestionadmin-wolk'),
            'EN_PROGRESO' => __('En Progreso', 'gestionadmin-wolk'),
            'EN_ESPERA'   => __('En Espera', 'gestionadmin-wolk'),
            'CERRADO'     => __('Cerrado', 'gestionadmin-wolk'),
            'CANCELADO'   => __('Cancelado', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener prioridades
     *
     * @return array Array asociativo prioridad => etiqueta
     */
    public static function get_prioridades() {
        return array(
            'BAJA'    => __('Baja', 'gestionadmin-wolk'),
            'MEDIA'   => __('Media', 'gestionadmin-wolk'),
            'ALTA'    => __('Alta', 'gestionadmin-wolk'),
            'URGENTE' => __('Urgente', 'gestionadmin-wolk'),
        );
    }

    /**
     * Contar proyectos de un caso
     *
     * @param int $caso_id ID del caso
     * @return int Número de proyectos
     */
    public static function count_proyectos($caso_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_proyectos';

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE caso_id = %d",
            $caso_id
        ));
    }

    /**
     * Obtener estadísticas de casos por cliente
     *
     * @param int $cliente_id ID del cliente
     * @return object Estadísticas (total, abiertos, cerrados)
     */
    public static function get_stats_by_cliente($cliente_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_casos';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado IN ('ABIERTO', 'EN_PROGRESO', 'EN_ESPERA') THEN 1 ELSE 0 END) as abiertos,
                SUM(CASE WHEN estado = 'CERRADO' THEN 1 ELSE 0 END) as cerrados,
                SUM(CASE WHEN estado = 'CANCELADO' THEN 1 ELSE 0 END) as cancelados
             FROM {$table}
             WHERE cliente_id = %d",
            $cliente_id
        ));
    }
}
