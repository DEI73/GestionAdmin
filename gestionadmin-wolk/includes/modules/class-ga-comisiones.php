<?php
/**
 * Módulo de Comisiones Generadas
 *
 * Gestiona las comisiones calculadas automáticamente cuando se pagan facturas.
 * Cada comisión se genera en base a los acuerdos económicos de la orden.
 *
 * FLUJO:
 * 1. Factura cambia a estado 'PAGADA'
 * 2. Hook dispara calcular_por_factura()
 * 3. Se crean registros en wp_ga_comisiones_generadas
 * 4. Proveedor ve comisiones DISPONIBLES en su portal
 * 5. Proveedor crea solicitud de cobro
 * 6. Finanzas aprueba y paga
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes/Modules
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Comisiones {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Comisiones
     */
    private static $instance = null;

    /**
     * Nombre de la tabla
     *
     * @var string
     */
    private $table_name;

    /**
     * Obtener instancia única
     *
     * @return GA_Comisiones
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
        $this->table_name = $wpdb->prefix . 'ga_comisiones_generadas';
    }

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    /**
     * Crear una comisión generada
     *
     * @param array $data Datos de la comisión
     * @return int|WP_Error ID de la comisión o error
     */
    public function crear($data) {
        global $wpdb;

        // Validar datos requeridos
        $required = array('orden_id', 'acuerdo_id', 'aplicante_id', 'monto_base', 'monto_comision');
        foreach ($required as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                return new WP_Error(
                    'datos_incompletos',
                    sprintf(__('El campo %s es requerido.', 'gestionadmin-wolk'), $field)
                );
            }
        }

        $insert_data = array(
            'orden_id'            => absint($data['orden_id']),
            'acuerdo_id'          => absint($data['acuerdo_id']),
            'aplicante_id'        => absint($data['aplicante_id']),
            'pago_origen_id'      => isset($data['pago_origen_id']) ? absint($data['pago_origen_id']) : null,
            'tipo_origen'         => sanitize_text_field($data['tipo_origen'] ?? 'FACTURA'),
            'monto_base'          => floatval($data['monto_base']),
            'porcentaje_aplicado' => isset($data['porcentaje_aplicado']) ? floatval($data['porcentaje_aplicado']) : null,
            'monto_fijo_aplicado' => isset($data['monto_fijo_aplicado']) ? floatval($data['monto_fijo_aplicado']) : null,
            'monto_comision'      => floatval($data['monto_comision']),
            'estado'              => 'DISPONIBLE',
            'notas'               => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : null,
        );

        $result = $wpdb->insert($this->table_name, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Error al crear la comisión.', 'gestionadmin-wolk'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Obtener comisión por ID
     *
     * @param int $id ID de la comisión
     * @return object|null
     */
    public function get($id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT c.*,
                    o.codigo as orden_codigo,
                    o.titulo as orden_titulo,
                    a.nombre as aplicante_nombre,
                    a.apellido as aplicante_apellido,
                    ac.tipo_acuerdo,
                    ac.descripcion as acuerdo_descripcion
             FROM {$this->table_name} c
             LEFT JOIN {$wpdb->prefix}ga_ordenes_trabajo o ON c.orden_id = o.id
             LEFT JOIN {$wpdb->prefix}ga_aplicantes a ON c.aplicante_id = a.id
             LEFT JOIN {$wpdb->prefix}ga_ordenes_acuerdos ac ON c.acuerdo_id = ac.id
             WHERE c.id = %d",
            $id
        ));
    }

    // =========================================================================
    // CÁLCULO AUTOMÁTICO DE COMISIONES
    // =========================================================================

    /**
     * Calcular y generar comisiones cuando una factura se paga
     *
     * Este método se llama desde el hook de cambio de estado de factura.
     * Itera sobre los acuerdos de la orden y crea las comisiones correspondientes.
     *
     * @param int $factura_id ID de la factura pagada
     * @return array|WP_Error Array de IDs de comisiones creadas o error
     */
    public function calcular_por_factura($factura_id) {
        global $wpdb;

        // Obtener datos de la factura
        $factura = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ga_facturas WHERE id = %d",
            $factura_id
        ));

        if (!$factura) {
            return new WP_Error('factura_no_encontrada', __('Factura no encontrada.', 'gestionadmin-wolk'));
        }

        // Si no tiene orden asociada, no hay comisiones que calcular
        if (empty($factura->orden_id)) {
            return array();
        }

        // Obtener acuerdos activos de la orden
        $acuerdos = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ga_ordenes_acuerdos
             WHERE orden_id = %d AND activo = 1",
            $factura->orden_id
        ));

        if (empty($acuerdos)) {
            return array();
        }

        // Obtener el aplicante asignado a la orden
        $orden = $wpdb->get_row($wpdb->prepare(
            "SELECT aplicante_id FROM {$wpdb->prefix}ga_ordenes_trabajo WHERE id = %d",
            $factura->orden_id
        ));

        if (empty($orden->aplicante_id)) {
            return new WP_Error('sin_aplicante', __('La orden no tiene aplicante asignado.', 'gestionadmin-wolk'));
        }

        $comisiones_creadas = array();
        $monto_factura = floatval($factura->total);

        foreach ($acuerdos as $acuerdo) {
            // Verificar si ya existe una comisión para este acuerdo y factura
            $existe = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_name}
                 WHERE acuerdo_id = %d AND pago_origen_id = %d AND tipo_origen = 'FACTURA'",
                $acuerdo->id,
                $factura_id
            ));

            if ($existe) {
                continue; // Ya se procesó esta comisión
            }

            // Calcular monto de la comisión
            $monto_comision = 0;
            $porcentaje_aplicado = null;
            $monto_fijo_aplicado = null;

            if ($acuerdo->es_porcentaje) {
                // Comisión por porcentaje
                $porcentaje_aplicado = floatval($acuerdo->valor);
                $monto_comision = $monto_factura * ($porcentaje_aplicado / 100);
            } else {
                // Comisión por monto fijo
                $monto_fijo_aplicado = floatval($acuerdo->valor);
                $monto_comision = $monto_fijo_aplicado;
            }

            // Verificar condiciones si existen
            if (!empty($acuerdo->condicion) && !empty($acuerdo->condicion_valor)) {
                if (!$this->evaluar_condicion($acuerdo, $factura, $factura->orden_id)) {
                    continue; // No cumple la condición
                }
            }

            // Solo crear si hay monto positivo
            if ($monto_comision <= 0) {
                continue;
            }

            // Crear la comisión
            $result = $this->crear(array(
                'orden_id'            => $factura->orden_id,
                'acuerdo_id'          => $acuerdo->id,
                'aplicante_id'        => $orden->aplicante_id,
                'pago_origen_id'      => $factura_id,
                'tipo_origen'         => 'FACTURA',
                'monto_base'          => $monto_factura,
                'porcentaje_aplicado' => $porcentaje_aplicado,
                'monto_fijo_aplicado' => $monto_fijo_aplicado,
                'monto_comision'      => $monto_comision,
                'notas'               => sprintf(
                    __('Generada automáticamente desde factura #%s', 'gestionadmin-wolk'),
                    $factura->numero_factura
                ),
            ));

            if (!is_wp_error($result)) {
                $comisiones_creadas[] = $result;
            }
        }

        return $comisiones_creadas;
    }

    /**
     * Evaluar condición de un acuerdo
     *
     * @param object $acuerdo  Acuerdo a evaluar
     * @param object $factura  Factura de contexto
     * @param int    $orden_id ID de la orden
     * @return bool True si cumple la condición
     */
    private function evaluar_condicion($acuerdo, $factura, $orden_id) {
        // Por ahora, implementación básica
        // En el futuro se pueden agregar condiciones más complejas
        // como: rentabilidad > 50%, horas > 150, etc.
        return true;
    }

    // =========================================================================
    // CONSULTAS PARA PROVEEDORES
    // =========================================================================

    /**
     * Obtener comisiones disponibles de un aplicante
     *
     * @param int   $aplicante_id ID del aplicante
     * @param array $args         Argumentos adicionales
     * @return array
     */
    public function get_disponibles($aplicante_id, $args = array()) {
        global $wpdb;

        $defaults = array(
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'page'     => 1,
            'per_page' => 20,
        );
        $args = wp_parse_args($args, $defaults);

        // Ordenamiento seguro
        $allowed_orderby = array('id', 'monto_comision', 'created_at', 'orden_id');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($args['page'] - 1) * $args['per_page'];

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*,
                    o.codigo as orden_codigo,
                    o.titulo as orden_titulo,
                    ac.tipo_acuerdo,
                    ac.descripcion as acuerdo_descripcion
             FROM {$this->table_name} c
             LEFT JOIN {$wpdb->prefix}ga_ordenes_trabajo o ON c.orden_id = o.id
             LEFT JOIN {$wpdb->prefix}ga_ordenes_acuerdos ac ON c.acuerdo_id = ac.id
             WHERE c.aplicante_id = %d AND c.estado = 'DISPONIBLE'
             ORDER BY c.{$orderby} {$order}
             LIMIT %d OFFSET %d",
            $aplicante_id,
            $args['per_page'],
            $offset
        ));

        return $results;
    }

    /**
     * Obtener total disponible de un aplicante
     *
     * @param int $aplicante_id ID del aplicante
     * @return float Total disponible
     */
    public function get_total_disponible($aplicante_id) {
        global $wpdb;

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(monto_comision), 0)
             FROM {$this->table_name}
             WHERE aplicante_id = %d AND estado = 'DISPONIBLE'",
            $aplicante_id
        ));

        return floatval($total);
    }

    /**
     * Contar comisiones disponibles de un aplicante
     *
     * @param int $aplicante_id ID del aplicante
     * @return int Cantidad de comisiones disponibles
     */
    public function count_disponibles($aplicante_id) {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name}
             WHERE aplicante_id = %d AND estado = 'DISPONIBLE'",
            $aplicante_id
        ));
    }

    // =========================================================================
    // GESTIÓN DE ESTADOS
    // =========================================================================

    /**
     * Marcar comisiones como solicitadas
     *
     * @param array $comision_ids IDs de comisiones
     * @param int   $solicitud_id ID de la solicitud
     * @return bool|WP_Error
     */
    public function marcar_solicitadas($comision_ids, $solicitud_id) {
        global $wpdb;

        if (empty($comision_ids) || !is_array($comision_ids)) {
            return new WP_Error('sin_comisiones', __('No se especificaron comisiones.', 'gestionadmin-wolk'));
        }

        $ids = array_map('absint', $comision_ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$this->table_name}
             SET estado = 'SOLICITADA', solicitud_id = %d
             WHERE id IN ({$placeholders}) AND estado = 'DISPONIBLE'",
            array_merge(array($solicitud_id), $ids)
        ));

        return $result !== false;
    }

    /**
     * Marcar comisiones como pagadas
     *
     * @param int $solicitud_id ID de la solicitud pagada
     * @return bool|WP_Error
     */
    public function marcar_pagadas($solicitud_id) {
        global $wpdb;

        $result = $wpdb->update(
            $this->table_name,
            array('estado' => 'PAGADA'),
            array('solicitud_id' => $solicitud_id, 'estado' => 'SOLICITADA'),
            array('%s'),
            array('%d', '%s')
        );

        return $result !== false;
    }

    /**
     * Revertir comisiones a disponibles
     *
     * Se usa cuando se rechaza o cancela una solicitud.
     *
     * @param int $solicitud_id ID de la solicitud
     * @return bool|WP_Error
     */
    public function revertir_a_disponibles($solicitud_id) {
        global $wpdb;

        $result = $wpdb->update(
            $this->table_name,
            array(
                'estado'       => 'DISPONIBLE',
                'solicitud_id' => null,
            ),
            array('solicitud_id' => $solicitud_id, 'estado' => 'SOLICITADA'),
            array('%s', '%s'),
            array('%d', '%s')
        );

        return $result !== false;
    }

    // =========================================================================
    // CONSULTAS ADMINISTRATIVAS
    // =========================================================================

    /**
     * Obtener comisiones por solicitud
     *
     * @param int $solicitud_id ID de la solicitud
     * @return array
     */
    public function get_por_solicitud($solicitud_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.*,
                    o.codigo as orden_codigo,
                    o.titulo as orden_titulo,
                    ac.tipo_acuerdo
             FROM {$this->table_name} c
             LEFT JOIN {$wpdb->prefix}ga_ordenes_trabajo o ON c.orden_id = o.id
             LEFT JOIN {$wpdb->prefix}ga_ordenes_acuerdos ac ON c.acuerdo_id = ac.id
             WHERE c.solicitud_id = %d
             ORDER BY c.id",
            $solicitud_id
        ));
    }

    /**
     * Listar todas las comisiones con filtros
     *
     * @param array $args Argumentos de filtrado
     * @return array Array con items, total y páginas
     */
    public function get_all($args = array()) {
        global $wpdb;

        $defaults = array(
            'aplicante_id' => '',
            'orden_id'     => '',
            'estado'       => '',
            'tipo_origen'  => '',
            'fecha_desde'  => '',
            'fecha_hasta'  => '',
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
            $where[] = 'c.aplicante_id = %d';
            $params[] = absint($args['aplicante_id']);
        }

        if (!empty($args['orden_id'])) {
            $where[] = 'c.orden_id = %d';
            $params[] = absint($args['orden_id']);
        }

        if (!empty($args['estado'])) {
            $where[] = 'c.estado = %s';
            $params[] = sanitize_text_field($args['estado']);
        }

        if (!empty($args['tipo_origen'])) {
            $where[] = 'c.tipo_origen = %s';
            $params[] = sanitize_text_field($args['tipo_origen']);
        }

        if (!empty($args['fecha_desde'])) {
            $where[] = 'DATE(c.created_at) >= %s';
            $params[] = sanitize_text_field($args['fecha_desde']);
        }

        if (!empty($args['fecha_hasta'])) {
            $where[] = 'DATE(c.created_at) <= %s';
            $params[] = sanitize_text_field($args['fecha_hasta']);
        }

        $where_sql = implode(' AND ', $where);

        // Ordenamiento seguro
        $allowed_orderby = array('id', 'monto_comision', 'created_at', 'estado', 'aplicante_id', 'orden_id');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Contar total
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} c WHERE {$where_sql}";
        if (!empty($params)) {
            $count_sql = $wpdb->prepare($count_sql, $params);
        }
        $total = $wpdb->get_var($count_sql);

        // Paginación
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Query principal
        $sql = "SELECT c.*,
                       o.codigo as orden_codigo,
                       o.titulo as orden_titulo,
                       a.nombre as aplicante_nombre,
                       a.apellido as aplicante_apellido,
                       a.email as aplicante_email,
                       ac.tipo_acuerdo
                FROM {$this->table_name} c
                LEFT JOIN {$wpdb->prefix}ga_ordenes_trabajo o ON c.orden_id = o.id
                LEFT JOIN {$wpdb->prefix}ga_aplicantes a ON c.aplicante_id = a.id
                LEFT JOIN {$wpdb->prefix}ga_ordenes_acuerdos ac ON c.acuerdo_id = ac.id
                WHERE {$where_sql}
                ORDER BY c.{$orderby} {$order}
                LIMIT %d OFFSET %d";

        $params[] = $args['per_page'];
        $params[] = $offset;

        $items = $wpdb->get_results($wpdb->prepare($sql, $params));

        return array(
            'items' => $items,
            'total' => intval($total),
            'pages' => ceil($total / $args['per_page']),
        );
    }

    // =========================================================================
    // ESTADÍSTICAS
    // =========================================================================

    /**
     * Obtener estadísticas generales de comisiones
     *
     * @return array
     */
    public function get_estadisticas() {
        global $wpdb;

        $stats = array();

        // Por estado
        $por_estado = $wpdb->get_results(
            "SELECT estado, COUNT(*) as cantidad, COALESCE(SUM(monto_comision), 0) as total
             FROM {$this->table_name}
             GROUP BY estado"
        );

        $stats['por_estado'] = array();
        foreach ($por_estado as $row) {
            $stats['por_estado'][$row->estado] = array(
                'cantidad' => intval($row->cantidad),
                'total'    => floatval($row->total),
            );
        }

        // Totales
        $totales = $wpdb->get_row(
            "SELECT COUNT(*) as total_comisiones,
                    COALESCE(SUM(monto_comision), 0) as monto_total,
                    COALESCE(SUM(CASE WHEN estado = 'DISPONIBLE' THEN monto_comision ELSE 0 END), 0) as pendiente_pago
             FROM {$this->table_name}"
        );

        $stats['total_comisiones'] = intval($totales->total_comisiones);
        $stats['monto_total'] = floatval($totales->monto_total);
        $stats['pendiente_pago'] = floatval($totales->pendiente_pago);

        return $stats;
    }

    /**
     * Obtener estadísticas de un aplicante específico
     *
     * @param int $aplicante_id ID del aplicante
     * @return array
     */
    public function get_estadisticas_aplicante($aplicante_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as total_comisiones,
                    COALESCE(SUM(monto_comision), 0) as monto_total,
                    COALESCE(SUM(CASE WHEN estado = 'DISPONIBLE' THEN monto_comision ELSE 0 END), 0) as disponible,
                    COALESCE(SUM(CASE WHEN estado = 'SOLICITADA' THEN monto_comision ELSE 0 END), 0) as solicitado,
                    COALESCE(SUM(CASE WHEN estado = 'PAGADA' THEN monto_comision ELSE 0 END), 0) as pagado
             FROM {$this->table_name}
             WHERE aplicante_id = %d",
            $aplicante_id
        ), ARRAY_A);
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
            'DISPONIBLE' => __('Disponible', 'gestionadmin-wolk'),
            'SOLICITADA' => __('Solicitada', 'gestionadmin-wolk'),
            'PAGADA'     => __('Pagada', 'gestionadmin-wolk'),
            'CANCELADA'  => __('Cancelada', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener tipos de origen
     *
     * @return array
     */
    public static function get_tipos_origen() {
        return array(
            'FACTURA'     => __('Factura', 'gestionadmin-wolk'),
            'PAGO_MANUAL' => __('Pago Manual', 'gestionadmin-wolk'),
            'OTRO'        => __('Otro', 'gestionadmin-wolk'),
        );
    }
}
