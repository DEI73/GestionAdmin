<?php
/**
 * Módulo: Órdenes de Trabajo (Work Orders)
 *
 * Gestión completa de órdenes de trabajo para el Marketplace.
 * Las órdenes de trabajo son publicaciones que clientes/empresas crean
 * para que aplicantes (freelancers/empresas externas) puedan postularse.
 *
 * Funcionalidades principales:
 * - CRUD completo de órdenes de trabajo
 * - Auto-numeración con formato OT-YYYY-NNNN
 * - Filtrado por estado, categoría, modalidad
 * - Gestión de ciclo de vida (borrador → publicada → asignada → completada)
 * - Estadísticas de aplicaciones por orden
 *
 * Tabla: wp_ga_ordenes_trabajo
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Modules
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GA_Ordenes_Trabajo
 *
 * Maneja todas las operaciones relacionadas con órdenes de trabajo.
 * Implementa el patrón estático para acceso global sin instanciación.
 *
 * @since 1.3.0
 */
class GA_Ordenes_Trabajo {

    // =========================================================================
    // CONSTANTES DE ENUMERACIÓN
    // =========================================================================

    /**
     * Estados posibles de una orden de trabajo
     *
     * Flujo típico:
     * BORRADOR → PUBLICADA → CERRADA → ASIGNADA → EN_PROGRESO → COMPLETADA
     *                    ↘ CANCELADA (en cualquier momento)
     *
     * @var array
     */
    const ESTADOS = array(
        'BORRADOR'    => 'Borrador',           // En preparación, no visible públicamente
        'PUBLICADA'   => 'Publicada',          // Visible en marketplace, aceptando aplicaciones
        'CERRADA'     => 'Cerrada',            // No acepta más aplicaciones, en evaluación
        'ASIGNADA'    => 'Asignada',           // Aplicante seleccionado, pendiente inicio
        'EN_PROGRESO' => 'En Progreso',        // Trabajo en ejecución
        'COMPLETADA'  => 'Completada',         // Trabajo finalizado satisfactoriamente
        'CANCELADA'   => 'Cancelada',          // Orden cancelada (por cliente o sistema)
    );

    /**
     * Categorías de trabajo disponibles
     *
     * Clasificación principal del tipo de trabajo requerido.
     * Ayuda a los aplicantes a filtrar órdenes de su especialidad.
     *
     * @var array
     */
    const CATEGORIAS = array(
        'DESARROLLO'       => 'Desarrollo de Software',
        'DISENO'           => 'Diseño Gráfico/UI/UX',
        'MARKETING'        => 'Marketing Digital',
        'LEGAL'            => 'Servicios Legales',
        'CONTABILIDAD'     => 'Contabilidad/Finanzas',
        'ADMINISTRATIVO'   => 'Administrativo',
        'CONSULTORIA'      => 'Consultoría',
        'SOPORTE'          => 'Soporte Técnico',
        'REDACCION'        => 'Redacción/Contenido',
        'TRADUCCION'       => 'Traducción',
        'VIDEO'            => 'Video/Multimedia',
        'AUDIO'            => 'Audio/Podcast',
        'OTRO'             => 'Otro',
    );

    /**
     * Tipos de pago disponibles
     *
     * Define cómo se compensará el trabajo:
     * - POR_HORA: Tarifa por hora trabajada
     * - PRECIO_FIJO: Monto total acordado independiente del tiempo
     * - A_CONVENIR: Se negocia con el aplicante seleccionado
     *
     * @var array
     */
    const TIPOS_PAGO = array(
        'POR_HORA'    => 'Por Hora',
        'PRECIO_FIJO' => 'Precio Fijo',
        'A_CONVENIR'  => 'A Convenir',
    );

    /**
     * Modalidades de trabajo
     *
     * Define dónde se realizará el trabajo:
     * - REMOTO: 100% desde cualquier ubicación
     * - PRESENCIAL: En las instalaciones del cliente
     * - HIBRIDO: Combinación de ambas
     *
     * @var array
     */
    const MODALIDADES = array(
        'REMOTO'     => 'Remoto',
        'PRESENCIAL' => 'Presencial',
        'HIBRIDO'    => 'Híbrido',
    );

    /**
     * Niveles de experiencia requeridos
     *
     * Indica el nivel de experiencia esperado para el trabajo.
     * Ayuda a filtrar aplicantes calificados.
     *
     * @var array
     */
    const NIVELES_EXPERIENCIA = array(
        'JUNIOR'     => 'Junior (0-2 años)',
        'SEMI_SR'    => 'Semi Senior (2-4 años)',
        'SENIOR'     => 'Senior (4-7 años)',
        'EXPERTO'    => 'Experto (7+ años)',
        'CUALQUIERA' => 'Cualquier nivel',
    );

    /**
     * Prioridades de la orden
     *
     * Indica la urgencia del trabajo.
     * Afecta la visibilidad y ordenamiento en el marketplace.
     *
     * @var array
     */
    const PRIORIDADES = array(
        'BAJA'    => 'Baja',
        'NORMAL'  => 'Normal',
        'ALTA'    => 'Alta',
        'URGENTE' => 'Urgente',
    );

    // =========================================================================
    // MÉTODOS DE LECTURA (GET)
    // =========================================================================

    /**
     * Obtiene todas las órdenes de trabajo con filtros opcionales
     *
     * Permite filtrar por múltiples criterios y ordenar los resultados.
     * Incluye información del cliente que creó la orden.
     *
     * @since 1.3.0
     *
     * @param array $args {
     *     Argumentos opcionales para filtrar y ordenar.
     *
     *     @type string $estado       Filtrar por estado específico.
     *     @type string $categoria    Filtrar por categoría.
     *     @type string $modalidad    Filtrar por modalidad de trabajo.
     *     @type string $tipo_pago    Filtrar por tipo de pago.
     *     @type int    $cliente_id   Filtrar por cliente que creó la orden.
     *     @type int    $caso_id      Filtrar por caso asociado.
     *     @type int    $proyecto_id  Filtrar por proyecto asociado.
     *     @type bool   $solo_activas Solo órdenes que aceptan aplicaciones (PUBLICADA).
     *     @type string $buscar       Término de búsqueda en título/descripción.
     *     @type string $orderby      Campo para ordenar (default: 'created_at').
     *     @type string $order        Dirección: 'ASC' o 'DESC' (default: 'DESC').
     *     @type int    $limit        Límite de resultados (default: sin límite).
     *     @type int    $offset       Desplazamiento para paginación.
     * }
     *
     * @return array Lista de objetos de órdenes de trabajo.
     */
    public static function get_all($args = array()) {
        global $wpdb;

        // Configuración por defecto
        $defaults = array(
            'estado'       => '',
            'categoria'    => '',
            'modalidad'    => '',
            'tipo_pago'    => '',
            'cliente_id'   => 0,
            'caso_id'      => 0,
            'proyecto_id'  => 0,
            'solo_activas' => false,
            'buscar'       => '',
            'orderby'      => 'created_at',
            'order'        => 'DESC',
            'limit'        => 0,
            'offset'       => 0,
        );

        $args = wp_parse_args($args, $defaults);

        $table_ordenes = $wpdb->prefix . 'ga_ordenes_trabajo';
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        // Query base con JOIN a clientes para obtener nombre
        $sql = "SELECT o.*, c.nombre_comercial as cliente_nombre
                FROM {$table_ordenes} o
                LEFT JOIN {$table_clientes} c ON o.cliente_id = c.id
                WHERE 1=1";

        $params = array();

        // -------------------------------------------------------------------------
        // Aplicar filtros condicionales
        // -------------------------------------------------------------------------

        // Filtro por estado específico
        if (!empty($args['estado'])) {
            $sql .= " AND o.estado = %s";
            $params[] = $args['estado'];
        }

        // Filtro para solo órdenes que aceptan aplicaciones
        if ($args['solo_activas']) {
            $sql .= " AND o.estado = 'PUBLICADA'";
        }

        // Filtro por categoría
        if (!empty($args['categoria'])) {
            $sql .= " AND o.categoria = %s";
            $params[] = $args['categoria'];
        }

        // Filtro por modalidad de trabajo
        if (!empty($args['modalidad'])) {
            $sql .= " AND o.modalidad = %s";
            $params[] = $args['modalidad'];
        }

        // Filtro por tipo de pago
        if (!empty($args['tipo_pago'])) {
            $sql .= " AND o.tipo_pago = %s";
            $params[] = $args['tipo_pago'];
        }

        // Filtro por cliente
        if (!empty($args['cliente_id'])) {
            $sql .= " AND o.cliente_id = %d";
            $params[] = absint($args['cliente_id']);
        }

        // Filtro por caso
        if (!empty($args['caso_id'])) {
            $sql .= " AND o.caso_id = %d";
            $params[] = absint($args['caso_id']);
        }

        // Filtro por proyecto
        if (!empty($args['proyecto_id'])) {
            $sql .= " AND o.proyecto_id = %d";
            $params[] = absint($args['proyecto_id']);
        }

        // Búsqueda en título y descripción
        if (!empty($args['buscar'])) {
            $buscar = '%' . $wpdb->esc_like($args['buscar']) . '%';
            $sql .= " AND (o.titulo LIKE %s OR o.descripcion LIKE %s OR o.codigo LIKE %s)";
            $params[] = $buscar;
            $params[] = $buscar;
            $params[] = $buscar;
        }

        // -------------------------------------------------------------------------
        // Ordenamiento
        // -------------------------------------------------------------------------

        // Campos permitidos para ordenar (seguridad contra SQL injection)
        $allowed_orderby = array(
            'id', 'codigo', 'titulo', 'categoria', 'tipo_pago', 'estado',
            'prioridad', 'tarifa_hora_min', 'tarifa_hora_max', 'presupuesto_fijo',
            'fecha_limite_aplicacion', 'fecha_inicio_estimada',
            'created_at', 'updated_at'
        );

        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY o.{$orderby} {$order}";

        // -------------------------------------------------------------------------
        // Límite y paginación
        // -------------------------------------------------------------------------

        if (!empty($args['limit'])) {
            $sql .= " LIMIT %d";
            $params[] = absint($args['limit']);

            if (!empty($args['offset'])) {
                $sql .= " OFFSET %d";
                $params[] = absint($args['offset']);
            }
        }

        // Ejecutar query
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Obtiene una orden de trabajo específica por ID
     *
     * Incluye información relacionada del cliente, caso y proyecto.
     *
     * @since 1.3.0
     *
     * @param int $id ID de la orden de trabajo.
     *
     * @return object|null Objeto de la orden o null si no existe.
     */
    public static function get($id) {
        global $wpdb;

        $table_ordenes = $wpdb->prefix . 'ga_ordenes_trabajo';
        $table_clientes = $wpdb->prefix . 'ga_clientes';
        $table_casos = $wpdb->prefix . 'ga_casos';
        $table_proyectos = $wpdb->prefix . 'ga_proyectos';

        $sql = $wpdb->prepare(
            "SELECT o.*,
                    cl.nombre_comercial as cliente_nombre,
                    cl.email as cliente_email,
                    ca.codigo as caso_codigo,
                    ca.titulo as caso_titulo,
                    pr.codigo as proyecto_codigo,
                    pr.nombre as proyecto_nombre
             FROM {$table_ordenes} o
             LEFT JOIN {$table_clientes} cl ON o.cliente_id = cl.id
             LEFT JOIN {$table_casos} ca ON o.caso_id = ca.id
             LEFT JOIN {$table_proyectos} pr ON o.proyecto_id = pr.id
             WHERE o.id = %d",
            absint($id)
        );

        return $wpdb->get_row($sql);
    }

    /**
     * Obtiene una orden de trabajo por su código único
     *
     * Útil para URLs amigables y búsqueda por código.
     *
     * @since 1.3.0
     *
     * @param string $codigo Código de la orden (formato: OT-YYYY-NNNN).
     *
     * @return object|null Objeto de la orden o null si no existe.
     */
    public static function get_by_codigo($codigo) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_ordenes_trabajo';

        // Normalizar a mayúsculas para búsqueda consistente
        $codigo_normalizado = strtoupper(sanitize_text_field($codigo));

        $sql = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE UPPER(codigo) = %s",
            $codigo_normalizado
        );

        return $wpdb->get_row($sql);
    }

    // =========================================================================
    // MÉTODOS DE ESCRITURA (SAVE/DELETE)
    // =========================================================================

    /**
     * Guarda una orden de trabajo (crear o actualizar)
     *
     * Si se proporciona un ID existente, actualiza el registro.
     * Si no hay ID, crea una nueva orden con código auto-generado.
     *
     * @since 1.3.0
     *
     * @param array $data {
     *     Datos de la orden de trabajo.
     *
     *     @type int    $id                      ID para actualizar (0 = crear nueva).
     *     @type string $titulo                  Título de la orden (requerido).
     *     @type string $descripcion             Descripción detallada.
     *     @type string $categoria               Categoría del trabajo.
     *     @type string $tipo_pago               Tipo de pago (POR_HORA, PRECIO_FIJO, A_CONVENIR).
     *     @type float  $presupuesto_min         Presupuesto mínimo en USD.
     *     @type float  $presupuesto_max         Presupuesto máximo en USD.
     *     @type string $modalidad               Modalidad (REMOTO, PRESENCIAL, HIBRIDO).
     *     @type string $ubicacion_requerida     Ciudad/país si es presencial.
     *     @type string $nivel_experiencia       Nivel requerido.
     *     @type array  $habilidades_requeridas  Lista de habilidades.
     *     @type string $requisitos_adicionales  Otros requisitos.
     *     @type string $fecha_limite_aplicacion Fecha límite para aplicar.
     *     @type string $fecha_inicio_estimada   Fecha estimada de inicio.
     *     @type int    $duracion_estimada_dias  Duración en días.
     *     @type string $estado                  Estado de la orden.
     *     @type string $prioridad               Prioridad.
     *     @type int    $cliente_id              Cliente que crea la orden.
     *     @type int    $caso_id                 Caso asociado (opcional).
     *     @type int    $proyecto_id             Proyecto asociado (opcional).
     * }
     *
     * @return array {
     *     Resultado de la operación.
     *
     *     @type bool   $success Éxito de la operación.
     *     @type int    $id      ID de la orden guardada.
     *     @type string $codigo  Código de la orden.
     *     @type string $message Mensaje descriptivo.
     * }
     */
    public static function save($data) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_ordenes_trabajo';
        $id = isset($data['id']) ? absint($data['id']) : 0;

        // -------------------------------------------------------------------------
        // Validaciones básicas
        // -------------------------------------------------------------------------

        if (empty($data['titulo'])) {
            return array(
                'success' => false,
                'message' => __('El título es obligatorio.', 'gestionadmin-wolk'),
            );
        }

        // -------------------------------------------------------------------------
        // Preparar datos para inserción/actualización
        // -------------------------------------------------------------------------

        // Procesar habilidades como JSON si es array
        $habilidades = '';
        if (!empty($data['habilidades_requeridas'])) {
            if (is_array($data['habilidades_requeridas'])) {
                $habilidades = wp_json_encode($data['habilidades_requeridas']);
            } else {
                $habilidades = $data['habilidades_requeridas'];
            }
        }

        $record = array(
            'titulo'                  => sanitize_text_field($data['titulo']),
            'descripcion'             => isset($data['descripcion']) ? wp_kses_post($data['descripcion']) : '',
            'categoria'               => isset($data['categoria']) ? sanitize_text_field($data['categoria']) : 'OTRO',
            'tipo_pago'               => isset($data['tipo_pago']) ? sanitize_text_field($data['tipo_pago']) : 'A_CONVENIR',
            'tarifa_hora_min'         => isset($data['tarifa_hora_min']) ? floatval($data['tarifa_hora_min']) : null,
            'tarifa_hora_max'         => isset($data['tarifa_hora_max']) ? floatval($data['tarifa_hora_max']) : null,
            'presupuesto_fijo'        => isset($data['presupuesto_fijo']) ? floatval($data['presupuesto_fijo']) : null,
            'modalidad'               => isset($data['modalidad']) ? sanitize_text_field($data['modalidad']) : 'REMOTO',
            'ubicacion_requerida'     => isset($data['ubicacion_requerida']) ? sanitize_text_field($data['ubicacion_requerida']) : '',
            'nivel_experiencia'       => isset($data['nivel_experiencia']) ? sanitize_text_field($data['nivel_experiencia']) : 'CUALQUIERA',
            'habilidades_requeridas'  => $habilidades,
            'requisitos_adicionales'  => isset($data['requisitos_adicionales']) ? wp_kses_post($data['requisitos_adicionales']) : '',
            'fecha_limite_aplicacion' => !empty($data['fecha_limite_aplicacion']) ? sanitize_text_field($data['fecha_limite_aplicacion']) : null,
            'fecha_inicio_estimada'   => !empty($data['fecha_inicio_estimada']) ? sanitize_text_field($data['fecha_inicio_estimada']) : null,
            'duracion_estimada_dias'  => isset($data['duracion_estimada_dias']) ? absint($data['duracion_estimada_dias']) : null,
            'estado'                  => isset($data['estado']) ? sanitize_text_field($data['estado']) : 'BORRADOR',
            'prioridad'               => isset($data['prioridad']) ? sanitize_text_field($data['prioridad']) : 'NORMAL',
            'cliente_id'              => isset($data['cliente_id']) ? absint($data['cliente_id']) : null,
            'caso_id'                 => !empty($data['caso_id']) ? absint($data['caso_id']) : null,
            'proyecto_id'             => !empty($data['proyecto_id']) ? absint($data['proyecto_id']) : null,
            'creado_por'              => isset($data['creado_por']) ? absint($data['creado_por']) : get_current_user_id(),
            'updated_at'              => current_time('mysql'),
        );

        $format = array(
            '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s'
        );

        // -------------------------------------------------------------------------
        // Insertar o actualizar
        // -------------------------------------------------------------------------

        if ($id > 0) {
            // Actualizar registro existente
            $result = $wpdb->update(
                $table,
                $record,
                array('id' => $id),
                $format,
                array('%d')
            );

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Error al actualizar la orden de trabajo.', 'gestionadmin-wolk'),
                );
            }

            // Obtener código existente
            $orden = self::get($id);
            $codigo = $orden ? $orden->codigo : '';

        } else {
            // Nueva orden: generar código automático
            $codigo = self::generate_codigo();
            $record['codigo'] = $codigo;
            $record['created_at'] = current_time('mysql');

            // Agregar formato para código y created_at
            array_unshift($format, '%s');
            $format[] = '%s';

            $result = $wpdb->insert($table, $record, $format);

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Error al crear la orden de trabajo.', 'gestionadmin-wolk'),
                );
            }

            $id = $wpdb->insert_id;
        }

        return array(
            'success' => true,
            'id'      => $id,
            'codigo'  => $codigo,
            'message' => __('Orden de trabajo guardada correctamente.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Elimina una orden de trabajo
     *
     * Solo permite eliminar órdenes en estado BORRADOR o CANCELADA.
     * Las órdenes con aplicaciones no pueden eliminarse.
     *
     * @since 1.3.0
     *
     * @param int $id ID de la orden a eliminar.
     *
     * @return array {
     *     Resultado de la operación.
     *
     *     @type bool   $success Éxito de la operación.
     *     @type string $message Mensaje descriptivo.
     * }
     */
    public static function delete($id) {
        global $wpdb;

        $id = absint($id);
        $orden = self::get($id);

        if (!$orden) {
            return array(
                'success' => false,
                'message' => __('Orden de trabajo no encontrada.', 'gestionadmin-wolk'),
            );
        }

        // Verificar estado: solo BORRADOR o CANCELADA pueden eliminarse
        if (!in_array($orden->estado, array('BORRADOR', 'CANCELADA'))) {
            return array(
                'success' => false,
                'message' => __('Solo se pueden eliminar órdenes en borrador o canceladas.', 'gestionadmin-wolk'),
            );
        }

        // Verificar si tiene aplicaciones
        $count_aplicaciones = self::count_aplicaciones($id);
        if ($count_aplicaciones > 0) {
            return array(
                'success' => false,
                'message' => sprintf(
                    __('No se puede eliminar: la orden tiene %d aplicación(es).', 'gestionadmin-wolk'),
                    $count_aplicaciones
                ),
            );
        }

        // Eliminar
        $table = $wpdb->prefix . 'ga_ordenes_trabajo';
        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al eliminar la orden de trabajo.', 'gestionadmin-wolk'),
            );
        }

        return array(
            'success' => true,
            'message' => __('Orden de trabajo eliminada correctamente.', 'gestionadmin-wolk'),
        );
    }

    // =========================================================================
    // MÉTODOS DE ESTADO
    // =========================================================================

    /**
     * Cambia el estado de una orden de trabajo
     *
     * Valida transiciones permitidas y registra el cambio.
     *
     * @since 1.3.0
     *
     * @param int    $id          ID de la orden.
     * @param string $nuevo_estado Nuevo estado a asignar.
     *
     * @return array Resultado de la operación.
     */
    public static function cambiar_estado($id, $nuevo_estado) {
        global $wpdb;

        $orden = self::get($id);

        if (!$orden) {
            return array(
                'success' => false,
                'message' => __('Orden de trabajo no encontrada.', 'gestionadmin-wolk'),
            );
        }

        // Validar que el nuevo estado existe
        if (!array_key_exists($nuevo_estado, self::ESTADOS)) {
            return array(
                'success' => false,
                'message' => __('Estado no válido.', 'gestionadmin-wolk'),
            );
        }

        // Validar transiciones permitidas
        $transiciones_validas = self::get_transiciones_validas($orden->estado);
        if (!in_array($nuevo_estado, $transiciones_validas)) {
            return array(
                'success' => false,
                'message' => sprintf(
                    __('No se puede cambiar de "%s" a "%s".', 'gestionadmin-wolk'),
                    self::ESTADOS[$orden->estado],
                    self::ESTADOS[$nuevo_estado]
                ),
            );
        }

        // Actualizar estado
        $table = $wpdb->prefix . 'ga_ordenes_trabajo';
        $result = $wpdb->update(
            $table,
            array(
                'estado'     => $nuevo_estado,
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al cambiar el estado.', 'gestionadmin-wolk'),
            );
        }

        return array(
            'success'       => true,
            'estado_previo' => $orden->estado,
            'estado_nuevo'  => $nuevo_estado,
            'message'       => __('Estado actualizado correctamente.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtiene las transiciones de estado válidas desde un estado dado
     *
     * Define el flujo de trabajo permitido para órdenes.
     *
     * @since 1.3.0
     *
     * @param string $estado_actual Estado actual de la orden.
     *
     * @return array Lista de estados a los que se puede transicionar.
     */
    public static function get_transiciones_validas($estado_actual) {
        $transiciones = array(
            'BORRADOR'    => array('PUBLICADA', 'CANCELADA'),
            'PUBLICADA'   => array('CERRADA', 'CANCELADA'),
            'CERRADA'     => array('PUBLICADA', 'ASIGNADA', 'CANCELADA'),
            'ASIGNADA'    => array('EN_PROGRESO', 'CANCELADA'),
            'EN_PROGRESO' => array('COMPLETADA', 'CANCELADA'),
            'COMPLETADA'  => array(),  // Estado final
            'CANCELADA'   => array('BORRADOR'),  // Puede reactivarse como borrador
        );

        return isset($transiciones[$estado_actual]) ? $transiciones[$estado_actual] : array();
    }

    // =========================================================================
    // MÉTODOS DE GENERACIÓN DE CÓDIGO
    // =========================================================================

    /**
     * Genera código único para una nueva orden de trabajo
     *
     * Formato: OT-YYYY-NNNN
     * Donde:
     * - OT = Prefijo "Orden de Trabajo"
     * - YYYY = Año actual
     * - NNNN = Número secuencial del año (con padding de 4 dígitos)
     *
     * Ejemplo: OT-2024-0001, OT-2024-0002, etc.
     *
     * @since 1.3.0
     *
     * @return string Código único generado.
     */
    public static function generate_codigo() {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_ordenes_trabajo';
        $year = date('Y');
        $prefix = "OT-{$year}-";

        // Obtener el último número del año actual
        $last_codigo = $wpdb->get_var($wpdb->prepare(
            "SELECT codigo FROM {$table}
             WHERE codigo LIKE %s
             ORDER BY codigo DESC
             LIMIT 1",
            $prefix . '%'
        ));

        if ($last_codigo) {
            // Extraer número y aumentar
            $last_number = intval(substr($last_codigo, -4));
            $new_number = $last_number + 1;
        } else {
            // Primera orden del año
            $new_number = 1;
        }

        // Formatear con padding de 4 dígitos
        return $prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // MÉTODOS DE ESTADÍSTICAS
    // =========================================================================

    /**
     * Cuenta las aplicaciones para una orden de trabajo
     *
     * @since 1.3.0
     *
     * @param int    $orden_id ID de la orden.
     * @param string $estado   Estado específico a contar (opcional).
     *
     * @return int Número de aplicaciones.
     */
    public static function count_aplicaciones($orden_id, $estado = '') {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';

        $sql = "SELECT COUNT(*) FROM {$table} WHERE orden_trabajo_id = %d";
        $params = array(absint($orden_id));

        if (!empty($estado)) {
            $sql .= " AND estado = %s";
            $params[] = sanitize_text_field($estado);
        }

        return (int) $wpdb->get_var($wpdb->prepare($sql, $params));
    }

    /**
     * Obtiene estadísticas generales de órdenes de trabajo
     *
     * @since 1.3.0
     *
     * @return array Estadísticas por estado y categoría.
     */
    public static function get_estadisticas() {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_ordenes_trabajo';

        // Contar por estado
        $por_estado = $wpdb->get_results(
            "SELECT estado, COUNT(*) as total
             FROM {$table}
             GROUP BY estado",
            OBJECT_K
        );

        // Contar por categoría
        $por_categoria = $wpdb->get_results(
            "SELECT categoria, COUNT(*) as total
             FROM {$table}
             WHERE estado != 'BORRADOR'
             GROUP BY categoria",
            OBJECT_K
        );

        // Total general
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

        // Publicadas actualmente
        $activas = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table} WHERE estado = 'PUBLICADA'"
        );

        return array(
            'total'         => (int) $total,
            'activas'       => (int) $activas,
            'por_estado'    => $por_estado,
            'por_categoria' => $por_categoria,
        );
    }

    /**
     * Obtiene las órdenes más recientes
     *
     * Útil para widgets de dashboard y vistas rápidas.
     *
     * @since 1.3.0
     *
     * @param int  $limit       Número de órdenes a obtener.
     * @param bool $solo_activas Solo incluir publicadas.
     *
     * @return array Lista de órdenes recientes.
     */
    public static function get_recientes($limit = 5, $solo_activas = false) {
        return self::get_all(array(
            'solo_activas' => $solo_activas,
            'orderby'      => 'created_at',
            'order'        => 'DESC',
            'limit'        => $limit,
        ));
    }

    // =========================================================================
    // MÉTODOS HELPER PARA ENUMS
    // =========================================================================

    /**
     * Obtiene todos los estados posibles
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de estados.
     */
    public static function get_estados() {
        return self::ESTADOS;
    }

    /**
     * Obtiene todas las categorías
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de categorías.
     */
    public static function get_categorias() {
        return self::CATEGORIAS;
    }

    /**
     * Obtiene todos los tipos de pago
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de tipos de pago.
     */
    public static function get_tipos_pago() {
        return self::TIPOS_PAGO;
    }

    /**
     * Obtiene todas las modalidades
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de modalidades.
     */
    public static function get_modalidades() {
        return self::MODALIDADES;
    }

    /**
     * Obtiene todos los niveles de experiencia
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de niveles.
     */
    public static function get_niveles_experiencia() {
        return self::NIVELES_EXPERIENCIA;
    }

    /**
     * Obtiene todas las prioridades
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de prioridades.
     */
    public static function get_prioridades() {
        return self::PRIORIDADES;
    }

    /**
     * Obtiene la etiqueta de un estado
     *
     * @since 1.3.0
     *
     * @param string $estado Código del estado.
     *
     * @return string Etiqueta legible del estado.
     */
    public static function get_estado_label($estado) {
        return isset(self::ESTADOS[$estado]) ? self::ESTADOS[$estado] : $estado;
    }

    /**
     * Obtiene la clase CSS para un estado (para badges)
     *
     * @since 1.3.0
     *
     * @param string $estado Código del estado.
     *
     * @return string Clase CSS para el badge.
     */
    public static function get_estado_class($estado) {
        $clases = array(
            'BORRADOR'    => 'ga-badge-secondary',
            'PUBLICADA'   => 'ga-badge-success',
            'CERRADA'     => 'ga-badge-warning',
            'ASIGNADA'    => 'ga-badge-info',
            'EN_PROGRESO' => 'ga-badge-primary',
            'COMPLETADA'  => 'ga-badge-success',
            'CANCELADA'   => 'ga-badge-danger',
        );

        return isset($clases[$estado]) ? $clases[$estado] : 'ga-badge-secondary';
    }
}
