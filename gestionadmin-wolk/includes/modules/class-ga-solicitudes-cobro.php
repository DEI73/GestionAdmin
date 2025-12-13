<?php
/**
 * Módulo de Solicitudes de Cobro
 *
 * Gestiona las solicitudes de pago que crean los proveedores
 * para cobrar sus comisiones disponibles.
 *
 * FLUJO DE ESTADOS:
 * PENDIENTE → EN_REVISION → APROBADA → PAGADA
 *                        ↘ RECHAZADA
 *           ↘ CANCELADA (por el proveedor)
 *
 * VALIDACIONES CRÍTICAS:
 * - monto_solicitado NUNCA puede ser > monto_disponible
 * - Una comisión solo puede estar en UNA solicitud activa
 * - Solo el propietario puede cancelar solicitudes PENDIENTE
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes/Modules
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Solicitudes_Cobro {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Solicitudes_Cobro
     */
    private static $instance = null;

    /**
     * Nombre de la tabla principal
     *
     * @var string
     */
    private $table_name;

    /**
     * Nombre de la tabla de detalle
     *
     * @var string
     */
    private $table_detalle;

    /**
     * Obtener instancia única
     *
     * @return GA_Solicitudes_Cobro
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ga_solicitudes_cobro';
        $this->table_detalle = $wpdb->prefix . 'ga_solicitudes_cobro_detalle';
    }

    // =========================================================================
    // GENERACIÓN DE NÚMERO
    // =========================================================================

    /**
     * Generar número único de solicitud
     *
     * Formato: SOL-YYYY-NNNN
     *
     * @return string Número generado
     */
    public function generar_numero() {
        global $wpdb;

        $year = date('Y');
        $prefix = 'SOL-' . $year . '-';

        // Obtener último consecutivo del año
        $ultimo = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING(numero_solicitud, -4) AS UNSIGNED))
             FROM {$this->table_name}
             WHERE numero_solicitud LIKE %s",
            $prefix . '%'
        ));

        $consecutivo = ($ultimo ?: 0) + 1;

        return sprintf('%s%04d', $prefix, $consecutivo);
    }

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    /**
     * Crear solicitud de cobro
     *
     * @param array $data Datos de la solicitud
     * @return int|WP_Error ID de la solicitud o error
     */
    public function crear($data) {
        global $wpdb;

        // Validar datos requeridos
        if (empty($data['aplicante_id'])) {
            return new WP_Error('sin_aplicante', __('Se requiere ID del aplicante.', 'gestionadmin-wolk'));
        }

        if (empty($data['comisiones']) || !is_array($data['comisiones'])) {
            return new WP_Error('sin_comisiones', __('Se requieren comisiones a incluir.', 'gestionadmin-wolk'));
        }

        if (empty($data['metodo_pago'])) {
            return new WP_Error('sin_metodo', __('Se requiere método de pago.', 'gestionadmin-wolk'));
        }

        $aplicante_id = absint($data['aplicante_id']);

        // Cargar módulo de comisiones
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-comisiones.php';
        $comisiones_module = GA_Comisiones::get_instance();

        // Calcular monto disponible real
        $monto_disponible = $comisiones_module->get_total_disponible($aplicante_id);

        if ($monto_disponible <= 0) {
            return new WP_Error('sin_saldo', __('No tienes comisiones disponibles para solicitar.', 'gestionadmin-wolk'));
        }

        // Validar que las comisiones pertenezcan al aplicante y estén disponibles
        $comision_ids = array();
        $monto_solicitado = 0;

        foreach ($data['comisiones'] as $comision_data) {
            $comision_id = absint($comision_data['id']);
            $comision = $comisiones_module->get($comision_id);

            if (!$comision) {
                return new WP_Error('comision_invalida', sprintf(
                    __('Comisión #%d no encontrada.', 'gestionadmin-wolk'),
                    $comision_id
                ));
            }

            if ($comision->aplicante_id != $aplicante_id) {
                return new WP_Error('comision_ajena', __('No puedes solicitar comisiones de otro proveedor.', 'gestionadmin-wolk'));
            }

            if ($comision->estado !== 'DISPONIBLE') {
                return new WP_Error('comision_no_disponible', sprintf(
                    __('La comisión #%d no está disponible.', 'gestionadmin-wolk'),
                    $comision_id
                ));
            }

            // Calcular monto solicitado (puede ser ajustado)
            $monto_comision = isset($comision_data['monto_solicitado'])
                ? floatval($comision_data['monto_solicitado'])
                : floatval($comision->monto_comision);

            // VALIDACIÓN CRÍTICA: No puede solicitar más del monto original
            if ($monto_comision > floatval($comision->monto_comision)) {
                return new WP_Error('monto_excedido', sprintf(
                    __('El monto solicitado para comisión #%d excede el monto disponible.', 'gestionadmin-wolk'),
                    $comision_id
                ));
            }

            $comision_ids[] = array(
                'comision'         => $comision,
                'monto_solicitado' => $monto_comision,
                'tipo_ajuste'      => $monto_comision < $comision->monto_comision ? 'MONTO_FIJO' : 'NINGUNO',
                'motivo_ajuste'    => $comision_data['motivo_ajuste'] ?? null,
            );

            $monto_solicitado += $monto_comision;
        }

        // VALIDACIÓN CRÍTICA: Total no puede exceder disponible
        if ($monto_solicitado > $monto_disponible) {
            return new WP_Error('total_excedido', __('El monto total solicitado excede tu saldo disponible.', 'gestionadmin-wolk'));
        }

        // Generar número de solicitud
        $numero = $this->generar_numero();

        // Preparar datos de pago
        $datos_pago = isset($data['datos_pago']) ? $data['datos_pago'] : array();
        if (is_array($datos_pago)) {
            $datos_pago = wp_json_encode($datos_pago);
        }

        // Insertar solicitud
        $insert_data = array(
            'numero_solicitud'   => $numero,
            'aplicante_id'       => $aplicante_id,
            'monto_disponible'   => $monto_disponible,
            'monto_solicitado'   => $monto_solicitado,
            'metodo_pago'        => sanitize_text_field($data['metodo_pago']),
            'datos_pago'         => $datos_pago,
            'moneda'             => sanitize_text_field($data['moneda'] ?? 'USD'),
            'notas_solicitante'  => isset($data['notas_solicitante']) ? sanitize_textarea_field($data['notas_solicitante']) : null,
            'estado'             => 'PENDIENTE',
        );

        $result = $wpdb->insert($this->table_name, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Error al crear la solicitud.', 'gestionadmin-wolk'));
        }

        $solicitud_id = $wpdb->insert_id;

        // Insertar detalles y marcar comisiones como solicitadas
        $comision_ids_array = array();

        foreach ($comision_ids as $item) {
            $comision = $item['comision'];

            // Insertar detalle
            $wpdb->insert($this->table_detalle, array(
                'solicitud_id'        => $solicitud_id,
                'comision_id'         => $comision->id,
                'monto_original'      => $comision->monto_comision,
                'porcentaje_original' => $comision->porcentaje_aplicado,
                'tipo_ajuste'         => $item['tipo_ajuste'],
                'monto_solicitado'    => $item['monto_solicitado'],
                'motivo_ajuste'       => $item['motivo_ajuste'],
                'incluida'            => 1,
            ));

            $comision_ids_array[] = $comision->id;
        }

        // Marcar comisiones como solicitadas
        $comisiones_module->marcar_solicitadas($comision_ids_array, $solicitud_id);

        return $solicitud_id;
    }

    /**
     * Obtener solicitud por ID
     *
     * @param int $id ID de la solicitud
     * @return object|null
     */
    public function get($id) {
        global $wpdb;

        $solicitud = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*,
                    a.nombre_completo as aplicante_nombre,
                    a.email as aplicante_email,
                    u.display_name as revisor_nombre
             FROM {$this->table_name} s
             LEFT JOIN {$wpdb->prefix}ga_aplicantes a ON s.aplicante_id = a.id
             LEFT JOIN {$wpdb->users} u ON s.revisado_por = u.ID
             WHERE s.id = %d",
            $id
        ));

        if ($solicitud && $solicitud->datos_pago) {
            $solicitud->datos_pago = json_decode($solicitud->datos_pago, true);
        }

        return $solicitud;
    }

    /**
     * Obtener solicitud por número
     *
     * @param string $numero Número de solicitud
     * @return object|null
     */
    public function get_por_numero($numero) {
        global $wpdb;

        $solicitud = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE numero_solicitud = %s",
            $numero
        ));

        if ($solicitud && $solicitud->datos_pago) {
            $solicitud->datos_pago = json_decode($solicitud->datos_pago, true);
        }

        return $solicitud;
    }

    // =========================================================================
    // GESTIÓN DE ESTADOS
    // =========================================================================

    /**
     * Aprobar solicitud
     *
     * @param int    $id    ID de la solicitud
     * @param string $notas Notas de aprobación
     * @return bool|WP_Error
     */
    public function aprobar($id, $notas = '') {
        global $wpdb;

        $solicitud = $this->get($id);

        if (!$solicitud) {
            return new WP_Error('no_encontrada', __('Solicitud no encontrada.', 'gestionadmin-wolk'));
        }

        if (!in_array($solicitud->estado, array('PENDIENTE', 'EN_REVISION'))) {
            return new WP_Error('estado_invalido', __('Solo se pueden aprobar solicitudes pendientes o en revisión.', 'gestionadmin-wolk'));
        }

        $result = $wpdb->update(
            $this->table_name,
            array(
                'estado'         => 'APROBADA',
                'revisado_por'   => get_current_user_id(),
                'notas_revision' => sanitize_textarea_field($notas),
                'fecha_revision' => current_time('mysql'),
            ),
            array('id' => $id),
            array('%s', '%d', '%s', '%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Rechazar solicitud
     *
     * @param int    $id    ID de la solicitud
     * @param string $notas Motivo del rechazo (requerido)
     * @return bool|WP_Error
     */
    public function rechazar($id, $notas) {
        global $wpdb;

        if (empty($notas)) {
            return new WP_Error('sin_motivo', __('Se requiere motivo del rechazo.', 'gestionadmin-wolk'));
        }

        $solicitud = $this->get($id);

        if (!$solicitud) {
            return new WP_Error('no_encontrada', __('Solicitud no encontrada.', 'gestionadmin-wolk'));
        }

        if (!in_array($solicitud->estado, array('PENDIENTE', 'EN_REVISION'))) {
            return new WP_Error('estado_invalido', __('Solo se pueden rechazar solicitudes pendientes o en revisión.', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $result = $wpdb->update(
            $this->table_name,
            array(
                'estado'         => 'RECHAZADA',
                'revisado_por'   => get_current_user_id(),
                'notas_revision' => sanitize_textarea_field($notas),
                'fecha_revision' => current_time('mysql'),
            ),
            array('id' => $id),
            array('%s', '%d', '%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Error al rechazar la solicitud.', 'gestionadmin-wolk'));
        }

        // Revertir comisiones a disponibles
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-comisiones.php';
        $comisiones_module = GA_Comisiones::get_instance();
        $comisiones_module->revertir_a_disponibles($id);

        return true;
    }

    /**
     * Marcar solicitud como pagada
     *
     * @param int    $id          ID de la solicitud
     * @param string $comprobante URL o referencia del comprobante
     * @return bool|WP_Error
     */
    public function marcar_pagada($id, $comprobante = '') {
        global $wpdb;

        $solicitud = $this->get($id);

        if (!$solicitud) {
            return new WP_Error('no_encontrada', __('Solicitud no encontrada.', 'gestionadmin-wolk'));
        }

        if ($solicitud->estado !== 'APROBADA') {
            return new WP_Error('estado_invalido', __('Solo se pueden pagar solicitudes aprobadas.', 'gestionadmin-wolk'));
        }

        // Actualizar solicitud
        $result = $wpdb->update(
            $this->table_name,
            array(
                'estado'           => 'PAGADA',
                'fecha_pago'       => current_time('mysql'),
                'comprobante_pago' => sanitize_text_field($comprobante),
            ),
            array('id' => $id),
            array('%s', '%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Error al marcar como pagada.', 'gestionadmin-wolk'));
        }

        // Marcar comisiones como pagadas
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-comisiones.php';
        $comisiones_module = GA_Comisiones::get_instance();
        $comisiones_module->marcar_pagadas($id);

        return true;
    }

    /**
     * Cancelar solicitud (solo por el proveedor)
     *
     * @param int $id           ID de la solicitud
     * @param int $aplicante_id ID del aplicante que cancela
     * @return bool|WP_Error
     */
    public function cancelar($id, $aplicante_id) {
        global $wpdb;

        $solicitud = $this->get($id);

        if (!$solicitud) {
            return new WP_Error('no_encontrada', __('Solicitud no encontrada.', 'gestionadmin-wolk'));
        }

        // Verificar que es el propietario
        if ($solicitud->aplicante_id != $aplicante_id) {
            return new WP_Error('no_autorizado', __('No tienes permiso para cancelar esta solicitud.', 'gestionadmin-wolk'));
        }

        // Solo se pueden cancelar solicitudes pendientes
        if ($solicitud->estado !== 'PENDIENTE') {
            return new WP_Error('estado_invalido', __('Solo se pueden cancelar solicitudes pendientes.', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $result = $wpdb->update(
            $this->table_name,
            array('estado' => 'CANCELADA'),
            array('id' => $id),
            array('%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Error al cancelar la solicitud.', 'gestionadmin-wolk'));
        }

        // Revertir comisiones a disponibles
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-comisiones.php';
        $comisiones_module = GA_Comisiones::get_instance();
        $comisiones_module->revertir_a_disponibles($id);

        return true;
    }

    /**
     * Poner solicitud en revisión
     *
     * @param int $id ID de la solicitud
     * @return bool|WP_Error
     */
    public function poner_en_revision($id) {
        global $wpdb;

        $solicitud = $this->get($id);

        if (!$solicitud) {
            return new WP_Error('no_encontrada', __('Solicitud no encontrada.', 'gestionadmin-wolk'));
        }

        if ($solicitud->estado !== 'PENDIENTE') {
            return new WP_Error('estado_invalido', __('Solo se pueden revisar solicitudes pendientes.', 'gestionadmin-wolk'));
        }

        $result = $wpdb->update(
            $this->table_name,
            array(
                'estado'       => 'EN_REVISION',
                'revisado_por' => get_current_user_id(),
            ),
            array('id' => $id),
            array('%s', '%d'),
            array('%d')
        );

        return $result !== false;
    }

    // =========================================================================
    // CONSULTAS
    // =========================================================================

    /**
     * Obtener solicitudes de un aplicante
     *
     * @param int   $aplicante_id ID del aplicante
     * @param array $args         Argumentos adicionales
     * @return array
     */
    public function get_por_aplicante($aplicante_id, $args = array()) {
        global $wpdb;

        $defaults = array(
            'estado'   => '',
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'page'     => 1,
            'per_page' => 10,
        );
        $args = wp_parse_args($args, $defaults);

        $where = array('s.aplicante_id = %d');
        $params = array($aplicante_id);

        if (!empty($args['estado'])) {
            $where[] = 's.estado = %s';
            $params[] = sanitize_text_field($args['estado']);
        }

        $where_sql = implode(' AND ', $where);

        // Ordenamiento seguro
        $allowed_orderby = array('id', 'numero_solicitud', 'monto_solicitado', 'created_at', 'estado');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($args['page'] - 1) * $args['per_page'];

        $params[] = $args['per_page'];
        $params[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT s.*
             FROM {$this->table_name} s
             WHERE {$where_sql}
             ORDER BY s.{$orderby} {$order}
             LIMIT %d OFFSET %d",
            $params
        ));

        // Decodificar datos_pago
        foreach ($results as &$row) {
            if ($row->datos_pago) {
                $row->datos_pago = json_decode($row->datos_pago, true);
            }
        }

        return $results;
    }

    /**
     * Listar todas las solicitudes con filtros
     *
     * @param array $args Argumentos de filtrado
     * @return array Array con items, total y páginas
     */
    public function get_all($args = array()) {
        global $wpdb;

        $defaults = array(
            'aplicante_id' => '',
            'estado'       => '',
            'metodo_pago'  => '',
            'fecha_desde'  => '',
            'fecha_hasta'  => '',
            'busqueda'     => '',
            'orderby'      => 'created_at',
            'order'        => 'DESC',
            'page'         => 1,
            'per_page'     => 20,
        );
        $args = wp_parse_args($args, $defaults);

        // Construir WHERE
        $where = array('1=1');
        $params = array();

        if (!empty($args['aplicante_id'])) {
            $where[] = 's.aplicante_id = %d';
            $params[] = absint($args['aplicante_id']);
        }

        if (!empty($args['estado'])) {
            $where[] = 's.estado = %s';
            $params[] = sanitize_text_field($args['estado']);
        }

        if (!empty($args['metodo_pago'])) {
            $where[] = 's.metodo_pago = %s';
            $params[] = sanitize_text_field($args['metodo_pago']);
        }

        if (!empty($args['fecha_desde'])) {
            $where[] = 'DATE(s.created_at) >= %s';
            $params[] = sanitize_text_field($args['fecha_desde']);
        }

        if (!empty($args['fecha_hasta'])) {
            $where[] = 'DATE(s.created_at) <= %s';
            $params[] = sanitize_text_field($args['fecha_hasta']);
        }

        if (!empty($args['busqueda'])) {
            $where[] = '(s.numero_solicitud LIKE %s OR a.nombre_completo LIKE %s OR a.email LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['busqueda']) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $where_sql = implode(' AND ', $where);

        // Ordenamiento seguro
        $allowed_orderby = array('id', 'numero_solicitud', 'monto_solicitado', 'created_at', 'estado', 'aplicante_id');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Contar total
        $count_sql = "SELECT COUNT(*)
                      FROM {$this->table_name} s
                      LEFT JOIN {$wpdb->prefix}ga_aplicantes a ON s.aplicante_id = a.id
                      WHERE {$where_sql}";
        if (!empty($params)) {
            $count_sql = $wpdb->prepare($count_sql, $params);
        }
        $total = $wpdb->get_var($count_sql);

        // Paginación
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Query principal
        $sql = "SELECT s.*,
                       a.nombre_completo as aplicante_nombre,
                       a.email as aplicante_email,
                       u.display_name as revisor_nombre
                FROM {$this->table_name} s
                LEFT JOIN {$wpdb->prefix}ga_aplicantes a ON s.aplicante_id = a.id
                LEFT JOIN {$wpdb->users} u ON s.revisado_por = u.ID
                WHERE {$where_sql}
                ORDER BY s.{$orderby} {$order}
                LIMIT %d OFFSET %d";

        $params[] = $args['per_page'];
        $params[] = $offset;

        $items = $wpdb->get_results($wpdb->prepare($sql, $params));

        // Decodificar datos_pago
        foreach ($items as &$row) {
            if ($row->datos_pago) {
                $row->datos_pago = json_decode($row->datos_pago, true);
            }
        }

        return array(
            'items' => $items,
            'total' => intval($total),
            'pages' => ceil($total / $args['per_page']),
        );
    }

    /**
     * Obtener detalle de comisiones de una solicitud
     *
     * @param int $solicitud_id ID de la solicitud
     * @return array
     */
    public function get_detalle($solicitud_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT d.*,
                    c.orden_id,
                    c.monto_comision as monto_comision_actual,
                    c.estado as comision_estado,
                    o.codigo as orden_codigo,
                    o.titulo as orden_titulo
             FROM {$this->table_detalle} d
             LEFT JOIN {$wpdb->prefix}ga_comisiones_generadas c ON d.comision_id = c.id
             LEFT JOIN {$wpdb->prefix}ga_ordenes_trabajo o ON c.orden_id = o.id
             WHERE d.solicitud_id = %d AND d.incluida = 1
             ORDER BY d.id",
            $solicitud_id
        ));
    }

    // =========================================================================
    // ESTADÍSTICAS
    // =========================================================================

    /**
     * Contar solicitudes por estado
     *
     * @return array
     */
    public function contar_por_estado() {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT estado, COUNT(*) as cantidad, COALESCE(SUM(monto_solicitado), 0) as total
             FROM {$this->table_name}
             GROUP BY estado"
        );

        $counts = array();
        foreach ($results as $row) {
            $counts[$row->estado] = array(
                'cantidad' => intval($row->cantidad),
                'total'    => floatval($row->total),
            );
        }

        return $counts;
    }

    /**
     * Obtener estadísticas generales
     *
     * @return array
     */
    public function get_estadisticas() {
        global $wpdb;

        $stats = array();

        // Por estado
        $stats['por_estado'] = $this->contar_por_estado();

        // Totales
        $totales = $wpdb->get_row(
            "SELECT COUNT(*) as total_solicitudes,
                    COALESCE(SUM(monto_solicitado), 0) as monto_total,
                    COALESCE(SUM(CASE WHEN estado = 'PENDIENTE' THEN monto_solicitado ELSE 0 END), 0) as pendiente,
                    COALESCE(SUM(CASE WHEN estado = 'EN_REVISION' THEN monto_solicitado ELSE 0 END), 0) as en_revision,
                    COALESCE(SUM(CASE WHEN estado = 'APROBADA' THEN monto_solicitado ELSE 0 END), 0) as aprobada,
                    COALESCE(SUM(CASE WHEN estado = 'PAGADA' THEN monto_solicitado ELSE 0 END), 0) as pagada
             FROM {$this->table_name}"
        );

        $stats['total_solicitudes'] = intval($totales->total_solicitudes);
        $stats['monto_total'] = floatval($totales->monto_total);
        $stats['pendiente'] = floatval($totales->pendiente);
        $stats['en_revision'] = floatval($totales->en_revision);
        $stats['aprobada'] = floatval($totales->aprobada);
        $stats['pagada'] = floatval($totales->pagada);

        // Por método de pago
        $por_metodo = $wpdb->get_results(
            "SELECT metodo_pago, COUNT(*) as cantidad, COALESCE(SUM(monto_solicitado), 0) as total
             FROM {$this->table_name}
             GROUP BY metodo_pago"
        );

        $stats['por_metodo'] = array();
        foreach ($por_metodo as $row) {
            $stats['por_metodo'][$row->metodo_pago] = array(
                'cantidad' => intval($row->cantidad),
                'total'    => floatval($row->total),
            );
        }

        return $stats;
    }

    // =========================================================================
    // ENUMS Y HELPERS
    // =========================================================================

    /**
     * Obtener estados disponibles
     *
     * @return array
     */
    public static function get_estados() {
        return array(
            'PENDIENTE'   => __('Pendiente', 'gestionadmin-wolk'),
            'EN_REVISION' => __('En Revisión', 'gestionadmin-wolk'),
            'APROBADA'    => __('Aprobada', 'gestionadmin-wolk'),
            'RECHAZADA'   => __('Rechazada', 'gestionadmin-wolk'),
            'PAGADA'      => __('Pagada', 'gestionadmin-wolk'),
            'CANCELADA'   => __('Cancelada', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener métodos de pago disponibles
     *
     * @return array
     */
    public static function get_metodos_pago() {
        return array(
            'BINANCE'            => __('Binance Pay', 'gestionadmin-wolk'),
            'WISE'               => __('Wise', 'gestionadmin-wolk'),
            'PAYPAL'             => __('PayPal', 'gestionadmin-wolk'),
            'TRANSFERENCIA_LOCAL'=> __('Transferencia Local', 'gestionadmin-wolk'),
            'OTRO'               => __('Otro', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener tipos de ajuste
     *
     * @return array
     */
    public static function get_tipos_ajuste() {
        return array(
            'NINGUNO'             => __('Sin Ajuste', 'gestionadmin-wolk'),
            'PORCENTAJE_REDUCIDO' => __('Porcentaje Reducido', 'gestionadmin-wolk'),
            'MONTO_FIJO'          => __('Monto Fijo', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener clase CSS para badge de estado
     *
     * @param string $estado Estado de la solicitud
     * @return string Clase CSS
     */
    public static function get_estado_badge_class($estado) {
        $classes = array(
            'PENDIENTE'   => 'ga-badge-warning',
            'EN_REVISION' => 'ga-badge-info',
            'APROBADA'    => 'ga-badge-success',
            'RECHAZADA'   => 'ga-badge-danger',
            'PAGADA'      => 'ga-badge-primary',
            'CANCELADA'   => 'ga-badge-secondary',
        );

        return $classes[$estado] ?? 'ga-badge-secondary';
    }
}
