<?php
/**
 * Módulo de Facturas - GestionAdmin
 *
 * =========================================================================
 * PROPÓSITO:
 * =========================================================================
 * Gestión completa de facturas a clientes. Incluye:
 * - CRUD de facturas y líneas de detalle
 * - Generación automática de número por país (FAC-XX-YYYY-NNNN)
 * - Cálculo de impuestos según configuración del país
 * - Estados: BORRADOR, ENVIADA, PARCIAL, PAGADA, VENCIDA, ANULADA
 * - Facturación de horas desde wp_ga_registro_horas
 * - Generación de PDF para impresión
 *
 * =========================================================================
 * NUMERACIÓN:
 * =========================================================================
 * Formato: FAC-[PAÍS]-[AÑO]-[CONSECUTIVO]
 * El consecutivo se reinicia cada año y es único por país.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Modules
 * @since      1.4.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Facturas {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Facturas
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
     * @return GA_Facturas
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        global $wpdb;
        $this->table_name    = $wpdb->prefix . 'ga_facturas';
        $this->table_detalle = $wpdb->prefix . 'ga_facturas_detalle';
    }

    // =========================================================================
    // GENERACIÓN DE NÚMERO DE FACTURA
    // =========================================================================

    /**
     * Generar número de factura automático
     *
     * Formato: FAC-[PAÍS]-[AÑO]-[CONSECUTIVO]
     * El consecutivo es único por país y año.
     *
     * @param string $pais_iso Código ISO del país (CO, US, MX, CR)
     * @return string Número generado (ej: FAC-CO-2024-0001)
     */
    public function generar_numero($pais_iso) {
        global $wpdb;

        // Validar país
        $pais_iso = strtoupper(sanitize_text_field($pais_iso));
        if (strlen($pais_iso) !== 2) {
            $pais_iso = 'US'; // Default a USA si país inválido
        }

        // Año actual
        $anio = date('Y');

        // Obtener último consecutivo del año y país
        $patron = 'FAC-' . $pais_iso . '-' . $anio . '-%';

        $ultimo = $wpdb->get_var($wpdb->prepare(
            "SELECT numero FROM {$this->table_name}
             WHERE numero LIKE %s
             ORDER BY id DESC
             LIMIT 1",
            $patron
        ));

        // Extraer consecutivo o iniciar en 1
        if ($ultimo) {
            // Extraer los últimos 4 dígitos (consecutivo)
            $partes = explode('-', $ultimo);
            $consecutivo = intval(end($partes)) + 1;
        } else {
            $consecutivo = 1;
        }

        // Formatear número con 4 dígitos
        return sprintf('FAC-%s-%s-%04d', $pais_iso, $anio, $consecutivo);
    }

    // =========================================================================
    // CRUD - CREAR FACTURA
    // =========================================================================

    /**
     * Crear nueva factura
     *
     * Crea una factura en estado BORRADOR con los datos básicos.
     * Las líneas de detalle se agregan por separado.
     *
     * @param array $data Datos de la factura
     * @return int|WP_Error ID de la factura creada o error
     */
    public function crear($data) {
        global $wpdb;

        // Validaciones básicas
        if (empty($data['cliente_id'])) {
            return new WP_Error('missing_client', __('El cliente es requerido', 'gestionadmin-wolk'));
        }

        if (empty($data['pais_facturacion'])) {
            return new WP_Error('missing_country', __('El país de facturación es requerido', 'gestionadmin-wolk'));
        }

        // Obtener datos del cliente para snapshot
        $cliente = $this->get_cliente_data($data['cliente_id']);
        if (!$cliente) {
            return new WP_Error('invalid_client', __('Cliente no encontrado', 'gestionadmin-wolk'));
        }

        // Obtener configuración fiscal del país
        $pais_config = $this->get_pais_config($data['pais_facturacion']);

        // Generar número automático
        $numero = $this->generar_numero($data['pais_facturacion']);

        // Calcular fecha de vencimiento
        $dias_credito = isset($data['dias_credito']) ? absint($data['dias_credito']) : 30;
        $fecha_emision = isset($data['fecha_emision']) ? $data['fecha_emision'] : date('Y-m-d');
        $fecha_vencimiento = date('Y-m-d', strtotime($fecha_emision . ' + ' . $dias_credito . ' days'));

        // Preparar datos para inserción
        $factura_data = array(
            'numero'              => $numero,
            'cliente_id'          => absint($data['cliente_id']),
            'cliente_nombre'      => sanitize_text_field($cliente->nombre_comercial),
            'cliente_documento'   => sanitize_text_field($cliente->documento_numero),
            'cliente_direccion'   => sanitize_textarea_field($cliente->direccion),
            'cliente_email'       => sanitize_email($cliente->email),
            'caso_id'             => isset($data['caso_id']) ? absint($data['caso_id']) : null,
            'proyecto_id'         => isset($data['proyecto_id']) ? absint($data['proyecto_id']) : null,
            'cotizacion_origen_id'=> isset($data['cotizacion_origen_id']) ? absint($data['cotizacion_origen_id']) : null,
            'pais_facturacion'    => strtoupper(sanitize_text_field($data['pais_facturacion'])),
            'moneda'              => isset($data['moneda']) ? strtoupper(sanitize_text_field($data['moneda'])) : 'USD',
            'tasa_cambio'         => isset($data['tasa_cambio']) ? floatval($data['tasa_cambio']) : 1.0000,
            'impuesto_nombre'     => $pais_config ? $pais_config->impuesto_nombre : '',
            'impuesto_porcentaje' => $pais_config ? $pais_config->impuesto_porcentaje : 0,
            'retencion_nombre'    => isset($data['retencion_nombre']) ? sanitize_text_field($data['retencion_nombre']) : '',
            'retencion_porcentaje'=> isset($data['retencion_porcentaje']) ? floatval($data['retencion_porcentaje']) : ($cliente->retencion_default ?? 0),
            'fecha_emision'       => sanitize_text_field($fecha_emision),
            'fecha_vencimiento'   => $fecha_vencimiento,
            'dias_credito'        => $dias_credito,
            'estado'              => 'BORRADOR',
            'concepto_general'    => isset($data['concepto_general']) ? sanitize_textarea_field($data['concepto_general']) : '',
            'notas'               => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : '',
            'notas_internas'      => isset($data['notas_internas']) ? sanitize_textarea_field($data['notas_internas']) : '',
            'terminos'            => isset($data['terminos']) ? sanitize_textarea_field($data['terminos']) : '',
            'creado_por'          => get_current_user_id(),
        );

        // Insertar factura
        $inserted = $wpdb->insert($this->table_name, $factura_data);

        if (!$inserted) {
            return new WP_Error('db_error', __('Error al crear la factura', 'gestionadmin-wolk'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Obtener datos del cliente
     *
     * @param int $cliente_id ID del cliente
     * @return object|null Datos del cliente
     */
    private function get_cliente_data($cliente_id) {
        global $wpdb;
        $table_clientes = $wpdb->prefix . 'ga_clientes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_clientes} WHERE id = %d",
            $cliente_id
        ));
    }

    /**
     * Obtener configuración fiscal del país
     *
     * @param string $pais_iso Código ISO del país
     * @return object|null Configuración del país
     */
    private function get_pais_config($pais_iso) {
        global $wpdb;
        $table_paises = $wpdb->prefix . 'ga_paises_config';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_paises} WHERE codigo_iso = %s AND activo = 1",
            strtoupper($pais_iso)
        ));
    }

    // =========================================================================
    // CRUD - LEER FACTURA
    // =========================================================================

    /**
     * Obtener factura por ID
     *
     * @param int  $id          ID de la factura
     * @param bool $con_detalle Incluir líneas de detalle
     * @return object|null Factura o null si no existe
     */
    public function get($id, $con_detalle = false) {
        global $wpdb;

        $factura = $wpdb->get_row($wpdb->prepare(
            "SELECT f.*,
                    c.nombre_comercial as cliente_actual_nombre,
                    p.nombre as pais_nombre
             FROM {$this->table_name} f
             LEFT JOIN {$wpdb->prefix}ga_clientes c ON f.cliente_id = c.id
             LEFT JOIN {$wpdb->prefix}ga_paises_config p ON f.pais_facturacion = p.codigo_iso
             WHERE f.id = %d",
            $id
        ));

        if ($factura && $con_detalle) {
            $factura->detalle = $this->get_detalle($id);
        }

        return $factura;
    }

    /**
     * Obtener factura por número
     *
     * @param string $numero Número de factura
     * @return object|null Factura o null
     */
    public function get_by_numero($numero) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE numero = %s",
            $numero
        ));
    }

    /**
     * Obtener líneas de detalle de una factura
     *
     * @param int $factura_id ID de la factura
     * @return array Líneas de detalle
     */
    public function get_detalle($factura_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_detalle}
             WHERE factura_id = %d
             ORDER BY orden ASC, id ASC",
            $factura_id
        ));
    }

    /**
     * Listar facturas con filtros y paginación
     *
     * @param array $args Argumentos de filtrado
     * @return array Lista de facturas con total
     */
    public function listar($args = array()) {
        global $wpdb;

        // Valores por defecto
        $defaults = array(
            'estado'     => '',
            'cliente_id' => 0,
            'pais'       => '',
            'fecha_desde'=> '',
            'fecha_hasta'=> '',
            'buscar'     => '',
            'orderby'    => 'fecha_emision',
            'order'      => 'DESC',
            'per_page'   => 20,
            'page'       => 1,
        );

        $args = wp_parse_args($args, $defaults);

        // Construir WHERE
        $where = array('1=1');
        $values = array();

        // Filtro por estado
        if (!empty($args['estado'])) {
            $where[] = 'f.estado = %s';
            $values[] = $args['estado'];
        }

        // Filtro por cliente
        if (!empty($args['cliente_id'])) {
            $where[] = 'f.cliente_id = %d';
            $values[] = absint($args['cliente_id']);
        }

        // Filtro por país
        if (!empty($args['pais'])) {
            $where[] = 'f.pais_facturacion = %s';
            $values[] = strtoupper($args['pais']);
        }

        // Filtro por rango de fechas
        if (!empty($args['fecha_desde'])) {
            $where[] = 'f.fecha_emision >= %s';
            $values[] = $args['fecha_desde'];
        }

        if (!empty($args['fecha_hasta'])) {
            $where[] = 'f.fecha_emision <= %s';
            $values[] = $args['fecha_hasta'];
        }

        // Búsqueda por número o cliente
        if (!empty($args['buscar'])) {
            $where[] = '(f.numero LIKE %s OR f.cliente_nombre LIKE %s)';
            $buscar = '%' . $wpdb->esc_like($args['buscar']) . '%';
            $values[] = $buscar;
            $values[] = $buscar;
        }

        $where_sql = implode(' AND ', $where);

        // Ordenamiento seguro
        $allowed_orderby = array('fecha_emision', 'numero', 'total', 'estado', 'cliente_nombre', 'fecha_vencimiento');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'fecha_emision';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Contar total
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} f WHERE {$where_sql}";
        if (!empty($values)) {
            $total = $wpdb->get_var($wpdb->prepare($count_sql, $values));
        } else {
            $total = $wpdb->get_var($count_sql);
        }

        // Paginación
        $per_page = absint($args['per_page']);
        $page = absint($args['page']);
        $offset = ($page - 1) * $per_page;

        // Query principal
        $sql = "SELECT f.*,
                       c.nombre_comercial as cliente_actual_nombre,
                       p.nombre as pais_nombre,
                       p.moneda_simbolo
                FROM {$this->table_name} f
                LEFT JOIN {$wpdb->prefix}ga_clientes c ON f.cliente_id = c.id
                LEFT JOIN {$wpdb->prefix}ga_paises_config p ON f.pais_facturacion = p.codigo_iso
                WHERE {$where_sql}
                ORDER BY f.{$orderby} {$order}
                LIMIT {$per_page} OFFSET {$offset}";

        if (!empty($values)) {
            $facturas = $wpdb->get_results($wpdb->prepare($sql, $values));
        } else {
            $facturas = $wpdb->get_results($sql);
        }

        return array(
            'items'      => $facturas,
            'total'      => intval($total),
            'pages'      => ceil($total / $per_page),
            'page'       => $page,
            'per_page'   => $per_page,
        );
    }

    // =========================================================================
    // CRUD - ACTUALIZAR FACTURA
    // =========================================================================

    /**
     * Actualizar factura
     *
     * Solo permite actualizar facturas en estado BORRADOR.
     *
     * @param int   $id   ID de la factura
     * @param array $data Datos a actualizar
     * @return bool|WP_Error True si éxito, WP_Error si falla
     */
    public function actualizar($id, $data) {
        global $wpdb;

        // Verificar que existe y está en BORRADOR
        $factura = $this->get($id);
        if (!$factura) {
            return new WP_Error('not_found', __('Factura no encontrada', 'gestionadmin-wolk'));
        }

        if ($factura->estado !== 'BORRADOR') {
            return new WP_Error('not_editable', __('Solo se pueden editar facturas en borrador', 'gestionadmin-wolk'));
        }

        // Campos permitidos para actualización
        $campos_permitidos = array(
            'caso_id', 'proyecto_id', 'moneda', 'tasa_cambio',
            'retencion_nombre', 'retencion_porcentaje',
            'fecha_emision', 'dias_credito',
            'concepto_general', 'notas', 'notas_internas', 'terminos',
            'descuento_porcentaje'
        );

        $update_data = array();
        foreach ($campos_permitidos as $campo) {
            if (isset($data[$campo])) {
                $update_data[$campo] = $data[$campo];
            }
        }

        // Recalcular fecha de vencimiento si cambió fecha_emision o días_credito
        if (isset($data['fecha_emision']) || isset($data['dias_credito'])) {
            $fecha_emision = isset($data['fecha_emision']) ? $data['fecha_emision'] : $factura->fecha_emision;
            $dias_credito = isset($data['dias_credito']) ? $data['dias_credito'] : $factura->dias_credito;
            $update_data['fecha_vencimiento'] = date('Y-m-d', strtotime($fecha_emision . ' + ' . $dias_credito . ' days'));
        }

        if (empty($update_data)) {
            return true; // Nada que actualizar
        }

        $updated = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id)
        );

        if ($updated === false) {
            return new WP_Error('db_error', __('Error al actualizar la factura', 'gestionadmin-wolk'));
        }

        // Recalcular totales
        $this->recalcular_totales($id);

        return true;
    }

    // =========================================================================
    // GESTIÓN DE LÍNEAS DE DETALLE
    // =========================================================================

    /**
     * Agregar línea de detalle a factura
     *
     * @param int   $factura_id ID de la factura
     * @param array $data       Datos de la línea
     * @return int|WP_Error ID de la línea o error
     */
    public function agregar_linea($factura_id, $data) {
        global $wpdb;

        // Verificar factura en BORRADOR
        $factura = $this->get($factura_id);
        if (!$factura) {
            return new WP_Error('not_found', __('Factura no encontrada', 'gestionadmin-wolk'));
        }

        if ($factura->estado !== 'BORRADOR') {
            return new WP_Error('not_editable', __('Solo se pueden editar facturas en borrador', 'gestionadmin-wolk'));
        }

        // Validar descripción
        if (empty($data['descripcion'])) {
            return new WP_Error('missing_description', __('La descripción es requerida', 'gestionadmin-wolk'));
        }

        // Obtener siguiente orden
        $max_orden = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(orden) FROM {$this->table_detalle} WHERE factura_id = %d",
            $factura_id
        ));
        $orden = ($max_orden !== null) ? $max_orden + 1 : 0;

        // Calcular valores
        $cantidad = isset($data['cantidad']) ? floatval($data['cantidad']) : 1;
        $precio_unitario = isset($data['precio_unitario']) ? floatval($data['precio_unitario']) : 0;
        $descuento_porcentaje = isset($data['descuento_porcentaje']) ? floatval($data['descuento_porcentaje']) : 0;

        // Subtotal = cantidad * precio - descuento
        $subtotal_bruto = $cantidad * $precio_unitario;
        $descuento_monto = $subtotal_bruto * ($descuento_porcentaje / 100);
        $subtotal = $subtotal_bruto - $descuento_monto;

        // Impuesto de la línea (si aplica)
        $aplica_impuesto = isset($data['aplica_impuesto']) ? (int)$data['aplica_impuesto'] : 1;
        $impuesto_porcentaje = $aplica_impuesto ? $factura->impuesto_porcentaje : 0;
        $impuesto_monto = $subtotal * ($impuesto_porcentaje / 100);
        $total_linea = $subtotal + $impuesto_monto;

        // Preparar datos
        $linea_data = array(
            'factura_id'          => $factura_id,
            'orden'               => $orden,
            'tipo'                => isset($data['tipo']) ? sanitize_text_field($data['tipo']) : 'SERVICIO',
            'codigo'              => isset($data['codigo']) ? sanitize_text_field($data['codigo']) : '',
            'descripcion'         => sanitize_textarea_field($data['descripcion']),
            'cantidad'            => $cantidad,
            'unidad'              => isset($data['unidad']) ? sanitize_text_field($data['unidad']) : 'UNIDAD',
            'precio_unitario'     => $precio_unitario,
            'descuento_porcentaje'=> $descuento_porcentaje,
            'descuento_monto'     => $descuento_monto,
            'subtotal'            => $subtotal,
            'aplica_impuesto'     => $aplica_impuesto,
            'impuesto_porcentaje' => $impuesto_porcentaje,
            'impuesto_monto'      => $impuesto_monto,
            'total_linea'         => $total_linea,
            'registro_hora_id'    => isset($data['registro_hora_id']) ? absint($data['registro_hora_id']) : null,
            'tarea_id'            => isset($data['tarea_id']) ? absint($data['tarea_id']) : null,
            'fecha_servicio'      => isset($data['fecha_servicio']) ? sanitize_text_field($data['fecha_servicio']) : null,
            'costo_unitario'      => isset($data['costo_unitario']) ? floatval($data['costo_unitario']) : 0,
            'costo_total'         => isset($data['costo_unitario']) ? floatval($data['costo_unitario']) * $cantidad : 0,
        );

        // Insertar línea
        $inserted = $wpdb->insert($this->table_detalle, $linea_data);

        if (!$inserted) {
            return new WP_Error('db_error', __('Error al agregar línea', 'gestionadmin-wolk'));
        }

        // Recalcular totales de la factura
        $this->recalcular_totales($factura_id);

        return $wpdb->insert_id;
    }

    /**
     * Eliminar línea de detalle
     *
     * @param int $linea_id ID de la línea
     * @return bool|WP_Error True si éxito
     */
    public function eliminar_linea($linea_id) {
        global $wpdb;

        // Obtener línea para saber la factura
        $linea = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_detalle} WHERE id = %d",
            $linea_id
        ));

        if (!$linea) {
            return new WP_Error('not_found', __('Línea no encontrada', 'gestionadmin-wolk'));
        }

        // Verificar factura en BORRADOR
        $factura = $this->get($linea->factura_id);
        if ($factura->estado !== 'BORRADOR') {
            return new WP_Error('not_editable', __('Solo se pueden editar facturas en borrador', 'gestionadmin-wolk'));
        }

        // Eliminar línea
        $wpdb->delete($this->table_detalle, array('id' => $linea_id));

        // Recalcular totales
        $this->recalcular_totales($linea->factura_id);

        return true;
    }

    /**
     * Recalcular totales de la factura
     *
     * Suma todas las líneas y aplica descuento global y retención.
     *
     * @param int $factura_id ID de la factura
     */
    public function recalcular_totales($factura_id) {
        global $wpdb;

        $factura = $this->get($factura_id);
        if (!$factura) {
            return;
        }

        // Sumar líneas
        $totales = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(subtotal) as subtotal,
                SUM(impuesto_monto) as impuesto_monto,
                SUM(total_linea) as total,
                SUM(costo_total) as costo_total
             FROM {$this->table_detalle}
             WHERE factura_id = %d",
            $factura_id
        ));

        $subtotal = floatval($totales->subtotal);
        $impuesto_monto = floatval($totales->impuesto_monto);
        $total = floatval($totales->total);
        $costo_total = floatval($totales->costo_total);

        // Aplicar descuento global
        $descuento_porcentaje = floatval($factura->descuento_porcentaje);
        $descuento_monto = $subtotal * ($descuento_porcentaje / 100);
        $base_impuesto = $subtotal - $descuento_monto;

        // Recalcular impuesto sobre base con descuento
        $impuesto_recalculado = $base_impuesto * ($factura->impuesto_porcentaje / 100);
        $total_final = $base_impuesto + $impuesto_recalculado;

        // Aplicar retención
        $retencion_monto = $base_impuesto * ($factura->retencion_porcentaje / 100);
        $total_a_pagar = $total_final - $retencion_monto;

        // Calcular saldo pendiente
        $saldo_pendiente = $total_a_pagar - floatval($factura->monto_pagado);

        // Calcular utilidad
        $utilidad_bruta = $subtotal - $costo_total;
        $utilidad_neta = $utilidad_bruta - floatval($factura->comisiones_total);
        $margen = ($subtotal > 0) ? ($utilidad_neta / $subtotal) * 100 : 0;

        // Actualizar factura
        $wpdb->update(
            $this->table_name,
            array(
                'subtotal'           => $subtotal,
                'descuento_monto'    => $descuento_monto,
                'base_impuesto'      => $base_impuesto,
                'impuesto_monto'     => $impuesto_recalculado,
                'total'              => $total_final,
                'retencion_monto'    => $retencion_monto,
                'total_a_pagar'      => $total_a_pagar,
                'saldo_pendiente'    => $saldo_pendiente,
                'costo_horas'        => $costo_total,
                'utilidad_bruta'     => $utilidad_bruta,
                'utilidad_neta'      => $utilidad_neta,
                'margen_porcentaje'  => $margen,
            ),
            array('id' => $factura_id)
        );
    }

    // =========================================================================
    // CAMBIOS DE ESTADO
    // =========================================================================

    /**
     * Enviar factura (BORRADOR → ENVIADA)
     *
     * @param int $id ID de la factura
     * @return bool|WP_Error
     */
    public function enviar($id) {
        global $wpdb;

        $factura = $this->get($id, true);
        if (!$factura) {
            return new WP_Error('not_found', __('Factura no encontrada', 'gestionadmin-wolk'));
        }

        if ($factura->estado !== 'BORRADOR') {
            return new WP_Error('invalid_state', __('Solo facturas en borrador pueden enviarse', 'gestionadmin-wolk'));
        }

        // Verificar que tiene al menos una línea
        if (empty($factura->detalle)) {
            return new WP_Error('no_items', __('La factura debe tener al menos un concepto', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $wpdb->update(
            $this->table_name,
            array(
                'estado'     => 'ENVIADA',
                'enviado_por'=> get_current_user_id(),
                'fecha_envio'=> current_time('mysql'),
            ),
            array('id' => $id)
        );

        // TODO: Enviar email al cliente con PDF adjunto

        return true;
    }

    /**
     * Registrar pago en factura
     *
     * @param int   $id    ID de la factura
     * @param float $monto Monto del pago
     * @return bool|WP_Error
     */
    public function registrar_pago($id, $monto) {
        global $wpdb;

        $factura = $this->get($id);
        if (!$factura) {
            return new WP_Error('not_found', __('Factura no encontrada', 'gestionadmin-wolk'));
        }

        if (!in_array($factura->estado, array('ENVIADA', 'PARCIAL', 'VENCIDA'))) {
            return new WP_Error('invalid_state', __('Estado de factura no permite pagos', 'gestionadmin-wolk'));
        }

        $monto = floatval($monto);
        if ($monto <= 0) {
            return new WP_Error('invalid_amount', __('El monto debe ser mayor a cero', 'gestionadmin-wolk'));
        }

        // Calcular nuevo total pagado
        $nuevo_pagado = floatval($factura->monto_pagado) + $monto;
        $nuevo_saldo = floatval($factura->total_a_pagar) - $nuevo_pagado;

        // Determinar nuevo estado
        if ($nuevo_saldo <= 0) {
            $nuevo_estado = 'PAGADA';
            $nuevo_saldo = 0;
        } else {
            $nuevo_estado = 'PARCIAL';
        }

        // Actualizar factura
        $wpdb->update(
            $this->table_name,
            array(
                'monto_pagado'    => $nuevo_pagado,
                'saldo_pendiente' => $nuevo_saldo,
                'estado'          => $nuevo_estado,
            ),
            array('id' => $id)
        );

        return true;
    }

    /**
     * Anular factura
     *
     * @param int    $id     ID de la factura
     * @param string $motivo Motivo de anulación
     * @return bool|WP_Error
     */
    public function anular($id, $motivo = '') {
        global $wpdb;

        $factura = $this->get($id);
        if (!$factura) {
            return new WP_Error('not_found', __('Factura no encontrada', 'gestionadmin-wolk'));
        }

        if ($factura->estado === 'ANULADA') {
            return new WP_Error('already_cancelled', __('La factura ya está anulada', 'gestionadmin-wolk'));
        }

        if ($factura->estado === 'PAGADA') {
            return new WP_Error('paid_invoice', __('No se puede anular una factura pagada', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $wpdb->update(
            $this->table_name,
            array(
                'estado'           => 'ANULADA',
                'anulado_por'      => get_current_user_id(),
                'fecha_anulacion'  => current_time('mysql'),
                'motivo_anulacion' => sanitize_textarea_field($motivo),
            ),
            array('id' => $id)
        );

        return true;
    }

    /**
     * Eliminar factura (solo borradores)
     *
     * @param int $id ID de la factura
     * @return bool|WP_Error
     */
    public function eliminar($id) {
        global $wpdb;

        $factura = $this->get($id);
        if (!$factura) {
            return new WP_Error('not_found', __('Factura no encontrada', 'gestionadmin-wolk'));
        }

        if ($factura->estado !== 'BORRADOR') {
            return new WP_Error('not_deletable', __('Solo se pueden eliminar facturas en borrador', 'gestionadmin-wolk'));
        }

        // Eliminar líneas de detalle
        $wpdb->delete($this->table_detalle, array('factura_id' => $id));

        // Eliminar factura
        $wpdb->delete($this->table_name, array('id' => $id));

        return true;
    }

    // =========================================================================
    // FACTURAR HORAS DESDE REGISTRO
    // =========================================================================

    /**
     * Obtener horas facturables
     *
     * Retorna horas aprobadas que no han sido facturadas.
     *
     * @param array $filtros Filtros opcionales
     * @return array Lista de horas facturables
     */
    public function get_horas_facturables($filtros = array()) {
        global $wpdb;

        $table_horas = $wpdb->prefix . 'ga_registro_horas';
        $table_usuarios = $wpdb->prefix . 'ga_usuarios';
        $table_tareas = $wpdb->prefix . 'ga_tareas';
        $table_proyectos = $wpdb->prefix . 'ga_proyectos';
        $table_casos = $wpdb->prefix . 'ga_casos';

        $where = array("rh.estado = 'APROBADO'", "rh.incluido_en_cobro_id IS NULL");
        $values = array();

        // Filtro por cliente (a través del proyecto/caso)
        if (!empty($filtros['cliente_id'])) {
            $where[] = '(c.cliente_id = %d OR p.caso_id IN (SELECT id FROM ' . $wpdb->prefix . 'ga_casos WHERE cliente_id = %d))';
            $values[] = absint($filtros['cliente_id']);
            $values[] = absint($filtros['cliente_id']);
        }

        // Filtro por proyecto
        if (!empty($filtros['proyecto_id'])) {
            $where[] = 'rh.proyecto_id = %d';
            $values[] = absint($filtros['proyecto_id']);
        }

        // Filtro por rango de fechas
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'rh.fecha >= %s';
            $values[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'rh.fecha <= %s';
            $values[] = $filtros['fecha_hasta'];
        }

        $where_sql = implode(' AND ', $where);

        $sql = "SELECT rh.*,
                       u.display_name as usuario_nombre,
                       t.nombre as tarea_nombre,
                       p.nombre as proyecto_nombre,
                       p.caso_id,
                       c.cliente_id
                FROM {$table_horas} rh
                LEFT JOIN {$wpdb->users} u ON rh.usuario_id = u.ID
                LEFT JOIN {$table_tareas} t ON rh.tarea_id = t.id
                LEFT JOIN {$table_proyectos} p ON rh.proyecto_id = p.id
                LEFT JOIN {$table_casos} c ON p.caso_id = c.id
                WHERE {$where_sql}
                ORDER BY rh.fecha ASC, rh.id ASC";

        if (!empty($values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $values));
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Facturar horas seleccionadas
     *
     * Crea líneas de factura a partir de registros de horas.
     *
     * @param int   $factura_id  ID de la factura
     * @param array $horas_ids   IDs de registros de horas
     * @param float $tarifa_hora Tarifa por hora a facturar
     * @return int|WP_Error Número de líneas creadas
     */
    public function facturar_horas($factura_id, $horas_ids, $tarifa_hora) {
        global $wpdb;

        $factura = $this->get($factura_id);
        if (!$factura || $factura->estado !== 'BORRADOR') {
            return new WP_Error('invalid_invoice', __('Factura inválida o no editable', 'gestionadmin-wolk'));
        }

        $tarifa_hora = floatval($tarifa_hora);
        if ($tarifa_hora <= 0) {
            return new WP_Error('invalid_rate', __('La tarifa por hora debe ser mayor a cero', 'gestionadmin-wolk'));
        }

        $table_horas = $wpdb->prefix . 'ga_registro_horas';
        $lineas_creadas = 0;

        foreach ($horas_ids as $hora_id) {
            $hora_id = absint($hora_id);

            // Obtener registro de hora
            $hora = $wpdb->get_row($wpdb->prepare(
                "SELECT rh.*, t.nombre as tarea_nombre, u.display_name as usuario_nombre
                 FROM {$table_horas} rh
                 LEFT JOIN {$wpdb->prefix}ga_tareas t ON rh.tarea_id = t.id
                 LEFT JOIN {$wpdb->users} u ON rh.usuario_id = u.ID
                 WHERE rh.id = %d AND rh.estado = 'APROBADO' AND rh.incluido_en_cobro_id IS NULL",
                $hora_id
            ));

            if (!$hora) {
                continue; // Saltar horas no válidas
            }

            // Calcular horas (minutos a horas decimales)
            $horas_decimal = $hora->minutos_efectivos / 60;

            // Crear descripción
            $descripcion = sprintf(
                '%s - %s (%s)',
                $hora->fecha,
                $hora->tarea_nombre ?: 'Trabajo general',
                $hora->usuario_nombre
            );

            // Agregar línea
            $result = $this->agregar_linea($factura_id, array(
                'tipo'             => 'HORA',
                'descripcion'      => $descripcion,
                'cantidad'         => round($horas_decimal, 2),
                'unidad'           => 'HORA',
                'precio_unitario'  => $tarifa_hora,
                'registro_hora_id' => $hora_id,
                'tarea_id'         => $hora->tarea_id,
                'fecha_servicio'   => $hora->fecha,
                'costo_unitario'   => $hora->tarifa_hora ?: 0, // Costo interno
            ));

            if (!is_wp_error($result)) {
                // Marcar hora como incluida en factura
                $wpdb->update(
                    $table_horas,
                    array('incluido_en_cobro_id' => $factura_id),
                    array('id' => $hora_id)
                );
                $lineas_creadas++;
            }
        }

        return $lineas_creadas;
    }

    // =========================================================================
    // ESTADÍSTICAS Y REPORTES
    // =========================================================================

    /**
     * Obtener estadísticas de facturación
     *
     * @param array $filtros Filtros opcionales
     * @return object Estadísticas
     */
    public function get_estadisticas($filtros = array()) {
        global $wpdb;

        $where = array('1=1');
        $values = array();

        // Filtro por año
        $anio = isset($filtros['anio']) ? absint($filtros['anio']) : date('Y');
        $where[] = 'YEAR(fecha_emision) = %d';
        $values[] = $anio;

        // Filtro por país
        if (!empty($filtros['pais'])) {
            $where[] = 'pais_facturacion = %s';
            $values[] = strtoupper($filtros['pais']);
        }

        $where_sql = implode(' AND ', $where);

        $sql = "SELECT
                    COUNT(*) as total_facturas,
                    COUNT(CASE WHEN estado = 'BORRADOR' THEN 1 END) as borradores,
                    COUNT(CASE WHEN estado = 'ENVIADA' THEN 1 END) as enviadas,
                    COUNT(CASE WHEN estado = 'PARCIAL' THEN 1 END) as parciales,
                    COUNT(CASE WHEN estado = 'PAGADA' THEN 1 END) as pagadas,
                    COUNT(CASE WHEN estado = 'VENCIDA' THEN 1 END) as vencidas,
                    COUNT(CASE WHEN estado = 'ANULADA' THEN 1 END) as anuladas,
                    COALESCE(SUM(CASE WHEN estado NOT IN ('BORRADOR', 'ANULADA') THEN total END), 0) as total_facturado,
                    COALESCE(SUM(CASE WHEN estado = 'PAGADA' THEN total_a_pagar END), 0) as total_cobrado,
                    COALESCE(SUM(CASE WHEN estado IN ('ENVIADA', 'PARCIAL', 'VENCIDA') THEN saldo_pendiente END), 0) as total_pendiente,
                    COALESCE(SUM(CASE WHEN estado NOT IN ('BORRADOR', 'ANULADA') THEN utilidad_neta END), 0) as utilidad_total
                FROM {$this->table_name}
                WHERE {$where_sql}";

        if (!empty($values)) {
            return $wpdb->get_row($wpdb->prepare($sql, $values));
        }

        return $wpdb->get_row($sql);
    }

    /**
     * Obtener facturas vencidas
     *
     * @return array Lista de facturas vencidas
     */
    public function get_vencidas() {
        global $wpdb;

        // Actualizar estado de vencidas primero
        $wpdb->query($wpdb->prepare(
            "UPDATE {$this->table_name}
             SET estado = 'VENCIDA'
             WHERE estado IN ('ENVIADA', 'PARCIAL')
             AND fecha_vencimiento < %s",
            date('Y-m-d')
        ));

        // Retornar lista
        return $wpdb->get_results(
            "SELECT f.*, c.nombre_comercial as cliente_nombre_actual,
                    DATEDIFF(CURDATE(), f.fecha_vencimiento) as dias_vencida
             FROM {$this->table_name} f
             LEFT JOIN {$wpdb->prefix}ga_clientes c ON f.cliente_id = c.id
             WHERE f.estado = 'VENCIDA'
             ORDER BY f.fecha_vencimiento ASC"
        );
    }

    // =========================================================================
    // GENERACIÓN DE PDF (Vista previa)
    // =========================================================================

    /**
     * Generar HTML para vista previa/impresión de factura
     *
     * @param int $id ID de la factura
     * @return string HTML de la factura
     */
    public function generar_html_preview($id) {
        $factura = $this->get($id, true);
        if (!$factura) {
            return '';
        }

        ob_start();
        ?>
        <div class="ga-factura-preview">
            <div class="ga-factura-header">
                <div class="ga-factura-empresa">
                    <h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
                    <p><?php echo esc_html(get_option('ga_empresa_direccion', '')); ?></p>
                    <p><?php echo esc_html(get_option('ga_empresa_nit', '')); ?></p>
                </div>
                <div class="ga-factura-info">
                    <h1><?php esc_html_e('FACTURA', 'gestionadmin-wolk'); ?></h1>
                    <p><strong><?php echo esc_html($factura->numero); ?></strong></p>
                    <p><?php esc_html_e('Fecha:', 'gestionadmin-wolk'); ?> <?php echo esc_html($factura->fecha_emision); ?></p>
                    <p><?php esc_html_e('Vence:', 'gestionadmin-wolk'); ?> <?php echo esc_html($factura->fecha_vencimiento); ?></p>
                </div>
            </div>

            <div class="ga-factura-cliente">
                <h3><?php esc_html_e('Facturar a:', 'gestionadmin-wolk'); ?></h3>
                <p><strong><?php echo esc_html($factura->cliente_nombre); ?></strong></p>
                <p><?php echo esc_html($factura->cliente_documento); ?></p>
                <p><?php echo esc_html($factura->cliente_direccion); ?></p>
                <p><?php echo esc_html($factura->cliente_email); ?></p>
            </div>

            <?php if ($factura->concepto_general): ?>
            <div class="ga-factura-concepto">
                <h4><?php esc_html_e('Concepto:', 'gestionadmin-wolk'); ?></h4>
                <p><?php echo esc_html($factura->concepto_general); ?></p>
            </div>
            <?php endif; ?>

            <table class="ga-factura-tabla">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Cantidad', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Precio', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Subtotal', 'gestionadmin-wolk'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factura->detalle as $linea): ?>
                    <tr>
                        <td><?php echo esc_html($linea->descripcion); ?></td>
                        <td><?php echo esc_html(number_format($linea->cantidad, 2)); ?> <?php echo esc_html($linea->unidad); ?></td>
                        <td><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($linea->precio_unitario, 2)); ?></td>
                        <td><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($linea->subtotal, 2)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="ga-factura-totales">
                <table>
                    <tr>
                        <td><?php esc_html_e('Subtotal:', 'gestionadmin-wolk'); ?></td>
                        <td><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->subtotal, 2)); ?></td>
                    </tr>
                    <?php if ($factura->descuento_monto > 0): ?>
                    <tr>
                        <td><?php esc_html_e('Descuento:', 'gestionadmin-wolk'); ?> (<?php echo esc_html($factura->descuento_porcentaje); ?>%)</td>
                        <td>-<?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->descuento_monto, 2)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($factura->impuesto_monto > 0): ?>
                    <tr>
                        <td><?php echo esc_html($factura->impuesto_nombre); ?> (<?php echo esc_html($factura->impuesto_porcentaje); ?>%):</td>
                        <td><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->impuesto_monto, 2)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="ga-total">
                        <td><strong><?php esc_html_e('Total:', 'gestionadmin-wolk'); ?></strong></td>
                        <td><strong><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->total, 2)); ?></strong></td>
                    </tr>
                    <?php if ($factura->retencion_monto > 0): ?>
                    <tr>
                        <td><?php esc_html_e('Retención:', 'gestionadmin-wolk'); ?> (<?php echo esc_html($factura->retencion_porcentaje); ?>%)</td>
                        <td>-<?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->retencion_monto, 2)); ?></td>
                    </tr>
                    <tr class="ga-total-pagar">
                        <td><strong><?php esc_html_e('Total a Pagar:', 'gestionadmin-wolk'); ?></strong></td>
                        <td><strong><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->total_a_pagar, 2)); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if ($factura->notas): ?>
            <div class="ga-factura-notas">
                <h4><?php esc_html_e('Notas:', 'gestionadmin-wolk'); ?></h4>
                <p><?php echo nl2br(esc_html($factura->notas)); ?></p>
            </div>
            <?php endif; ?>

            <div class="ga-factura-footer">
                <p><?php esc_html_e('Diseñado y desarrollado por', 'gestionadmin-wolk'); ?> <a href="https://wolksoftcr.com">Wolksoftcr.com</a></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
