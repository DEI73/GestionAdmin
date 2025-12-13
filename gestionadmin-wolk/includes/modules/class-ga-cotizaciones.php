<?php
/**
 * Módulo de Cotizaciones - GestionAdmin
 *
 * =========================================================================
 * PROPÓSITO:
 * =========================================================================
 * Gestión completa de cotizaciones/presupuestos a clientes. Incluye:
 * - CRUD de cotizaciones y líneas de detalle
 * - Generación automática de número (COT-YYYY-NNNN)
 * - Estados: BORRADOR, ENVIADA, APROBADA, RECHAZADA, FACTURADA, VENCIDA, CANCELADA
 * - Conversión automática de cotización a factura
 *
 * =========================================================================
 * NUMERACIÓN:
 * =========================================================================
 * Formato: COT-[AÑO]-[CONSECUTIVO]
 * El consecutivo se reinicia cada año.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Modules
 * @since      1.4.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Cotizaciones {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Cotizaciones
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
     * @return GA_Cotizaciones
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
        $this->table_name    = $wpdb->prefix . 'ga_cotizaciones';
        $this->table_detalle = $wpdb->prefix . 'ga_cotizaciones_detalle';
    }

    // =========================================================================
    // GENERACIÓN DE NÚMERO DE COTIZACIÓN
    // =========================================================================

    /**
     * Generar número de cotización automático
     *
     * Formato: COT-[AÑO]-[CONSECUTIVO]
     * El consecutivo es único por año.
     *
     * @return string Número generado (ej: COT-2024-0001)
     */
    public function generar_numero() {
        global $wpdb;

        // Año actual
        $anio = date('Y');

        // Obtener último consecutivo del año
        $patron = 'COT-' . $anio . '-%';

        $ultimo = $wpdb->get_var($wpdb->prepare(
            "SELECT numero FROM {$this->table_name}
             WHERE numero LIKE %s
             ORDER BY id DESC
             LIMIT 1",
            $patron
        ));

        // Extraer consecutivo o iniciar en 1
        if ($ultimo) {
            $partes = explode('-', $ultimo);
            $consecutivo = intval(end($partes)) + 1;
        } else {
            $consecutivo = 1;
        }

        // Formatear número con 4 dígitos
        return sprintf('COT-%s-%04d', $anio, $consecutivo);
    }

    // =========================================================================
    // CRUD - CREAR COTIZACIÓN
    // =========================================================================

    /**
     * Crear nueva cotización
     *
     * @param array $data Datos de la cotización
     * @return int|WP_Error ID de la cotización creada o error
     */
    public function crear($data) {
        global $wpdb;

        // Validaciones básicas
        if (empty($data['cliente_id'])) {
            return new WP_Error('missing_client', __('El cliente es requerido', 'gestionadmin-wolk'));
        }

        // Obtener datos del cliente
        $cliente = $this->get_cliente_data($data['cliente_id']);
        if (!$cliente) {
            return new WP_Error('invalid_client', __('Cliente no encontrado', 'gestionadmin-wolk'));
        }

        // Generar número automático
        $numero = $this->generar_numero();

        // Calcular fecha de vigencia
        $dias_vigencia = isset($data['dias_vigencia']) ? absint($data['dias_vigencia']) : 30;
        $fecha_emision = isset($data['fecha_emision']) ? $data['fecha_emision'] : date('Y-m-d');
        $fecha_vigencia = date('Y-m-d', strtotime($fecha_emision . ' + ' . $dias_vigencia . ' days'));

        // Obtener impuesto del país del cliente
        $pais_config = null;
        if (!empty($cliente->pais)) {
            $pais_config = $this->get_pais_config($cliente->pais);
        }

        // Preparar datos para inserción
        $cotizacion_data = array(
            'numero'             => $numero,
            'cliente_id'         => absint($data['cliente_id']),
            'cliente_nombre'     => sanitize_text_field($cliente->nombre_comercial),
            'cliente_email'      => sanitize_email($cliente->email),
            'contacto_nombre'    => isset($data['contacto_nombre']) ? sanitize_text_field($data['contacto_nombre']) : ($cliente->contacto_nombre ?? ''),
            'caso_id'            => isset($data['caso_id']) ? absint($data['caso_id']) : null,
            'proyecto_id'        => isset($data['proyecto_id']) ? absint($data['proyecto_id']) : null,
            'titulo'             => isset($data['titulo']) ? sanitize_text_field($data['titulo']) : '',
            'descripcion'        => isset($data['descripcion']) ? sanitize_textarea_field($data['descripcion']) : '',
            'moneda'             => isset($data['moneda']) ? strtoupper(sanitize_text_field($data['moneda'])) : 'USD',
            'pais_destino'       => $cliente->pais ?? '',
            'impuesto_nombre'    => $pais_config ? $pais_config->impuesto_nombre : '',
            'impuesto_porcentaje'=> $pais_config ? $pais_config->impuesto_porcentaje : 0,
            'fecha_emision'      => sanitize_text_field($fecha_emision),
            'fecha_vigencia'     => $fecha_vigencia,
            'dias_vigencia'      => $dias_vigencia,
            'estado'             => 'BORRADOR',
            'notas'              => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : '',
            'notas_internas'     => isset($data['notas_internas']) ? sanitize_textarea_field($data['notas_internas']) : '',
            'terminos'           => isset($data['terminos']) ? sanitize_textarea_field($data['terminos']) : '',
            'forma_pago'         => isset($data['forma_pago']) ? sanitize_textarea_field($data['forma_pago']) : '',
            'creado_por'         => get_current_user_id(),
        );

        // Insertar cotización
        $inserted = $wpdb->insert($this->table_name, $cotizacion_data);

        if (!$inserted) {
            return new WP_Error('db_error', __('Error al crear la cotización', 'gestionadmin-wolk'));
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
    // CRUD - LEER COTIZACIÓN
    // =========================================================================

    /**
     * Obtener cotización por ID
     *
     * @param int  $id          ID de la cotización
     * @param bool $con_detalle Incluir líneas de detalle
     * @return object|null Cotización o null si no existe
     */
    public function get($id, $con_detalle = false) {
        global $wpdb;

        $cotizacion = $wpdb->get_row($wpdb->prepare(
            "SELECT c.*,
                    cl.nombre_comercial as cliente_actual_nombre
             FROM {$this->table_name} c
             LEFT JOIN {$wpdb->prefix}ga_clientes cl ON c.cliente_id = cl.id
             WHERE c.id = %d",
            $id
        ));

        if ($cotizacion && $con_detalle) {
            $cotizacion->detalle = $this->get_detalle($id);
        }

        return $cotizacion;
    }

    /**
     * Obtener cotización por número
     *
     * @param string $numero Número de cotización
     * @return object|null Cotización o null
     */
    public function get_by_numero($numero) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE numero = %s",
            $numero
        ));
    }

    /**
     * Obtener líneas de detalle de una cotización
     *
     * @param int $cotizacion_id ID de la cotización
     * @return array Líneas de detalle
     */
    public function get_detalle($cotizacion_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_detalle}
             WHERE cotizacion_id = %d
             ORDER BY orden ASC, id ASC",
            $cotizacion_id
        ));
    }

    /**
     * Listar cotizaciones con filtros y paginación
     *
     * @param array $args Argumentos de filtrado
     * @return array Lista de cotizaciones con total
     */
    public function listar($args = array()) {
        global $wpdb;

        // Valores por defecto
        $defaults = array(
            'estado'     => '',
            'cliente_id' => 0,
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
            $where[] = 'c.estado = %s';
            $values[] = $args['estado'];
        }

        // Filtro por cliente
        if (!empty($args['cliente_id'])) {
            $where[] = 'c.cliente_id = %d';
            $values[] = absint($args['cliente_id']);
        }

        // Filtro por rango de fechas
        if (!empty($args['fecha_desde'])) {
            $where[] = 'c.fecha_emision >= %s';
            $values[] = $args['fecha_desde'];
        }

        if (!empty($args['fecha_hasta'])) {
            $where[] = 'c.fecha_emision <= %s';
            $values[] = $args['fecha_hasta'];
        }

        // Búsqueda
        if (!empty($args['buscar'])) {
            $where[] = '(c.numero LIKE %s OR c.cliente_nombre LIKE %s OR c.titulo LIKE %s)';
            $buscar = '%' . $wpdb->esc_like($args['buscar']) . '%';
            $values[] = $buscar;
            $values[] = $buscar;
            $values[] = $buscar;
        }

        $where_sql = implode(' AND ', $where);

        // Ordenamiento seguro
        $allowed_orderby = array('fecha_emision', 'numero', 'total', 'estado', 'cliente_nombre', 'fecha_vigencia');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'fecha_emision';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Contar total
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} c WHERE {$where_sql}";
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
        $sql = "SELECT c.*,
                       cl.nombre_comercial as cliente_actual_nombre
                FROM {$this->table_name} c
                LEFT JOIN {$wpdb->prefix}ga_clientes cl ON c.cliente_id = cl.id
                WHERE {$where_sql}
                ORDER BY c.{$orderby} {$order}
                LIMIT {$per_page} OFFSET {$offset}";

        if (!empty($values)) {
            $cotizaciones = $wpdb->get_results($wpdb->prepare($sql, $values));
        } else {
            $cotizaciones = $wpdb->get_results($sql);
        }

        return array(
            'items'    => $cotizaciones,
            'total'    => intval($total),
            'pages'    => ceil($total / $per_page),
            'page'     => $page,
            'per_page' => $per_page,
        );
    }

    // =========================================================================
    // CRUD - ACTUALIZAR COTIZACIÓN
    // =========================================================================

    /**
     * Actualizar cotización
     *
     * Solo permite actualizar cotizaciones en estado BORRADOR o ENVIADA.
     *
     * @param int   $id   ID de la cotización
     * @param array $data Datos a actualizar
     * @return bool|WP_Error True si éxito, WP_Error si falla
     */
    public function actualizar($id, $data) {
        global $wpdb;

        // Verificar que existe
        $cotizacion = $this->get($id);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        // Solo editar borradores o enviadas (antes de respuesta)
        if (!in_array($cotizacion->estado, array('BORRADOR', 'ENVIADA'))) {
            return new WP_Error('not_editable', __('Esta cotización no se puede editar', 'gestionadmin-wolk'));
        }

        // Campos permitidos
        $campos_permitidos = array(
            'titulo', 'descripcion', 'caso_id', 'proyecto_id',
            'moneda', 'impuesto_porcentaje',
            'fecha_emision', 'dias_vigencia',
            'notas', 'notas_internas', 'terminos', 'forma_pago',
            'descuento_porcentaje', 'contacto_nombre'
        );

        $update_data = array();
        foreach ($campos_permitidos as $campo) {
            if (isset($data[$campo])) {
                $update_data[$campo] = $data[$campo];
            }
        }

        // Recalcular fecha de vigencia si cambió
        if (isset($data['fecha_emision']) || isset($data['dias_vigencia'])) {
            $fecha_emision = isset($data['fecha_emision']) ? $data['fecha_emision'] : $cotizacion->fecha_emision;
            $dias_vigencia = isset($data['dias_vigencia']) ? $data['dias_vigencia'] : $cotizacion->dias_vigencia;
            $update_data['fecha_vigencia'] = date('Y-m-d', strtotime($fecha_emision . ' + ' . $dias_vigencia . ' days'));
        }

        if (empty($update_data)) {
            return true;
        }

        $updated = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id)
        );

        if ($updated === false) {
            return new WP_Error('db_error', __('Error al actualizar la cotización', 'gestionadmin-wolk'));
        }

        // Recalcular totales
        $this->recalcular_totales($id);

        return true;
    }

    // =========================================================================
    // GESTIÓN DE LÍNEAS DE DETALLE
    // =========================================================================

    /**
     * Agregar línea de detalle a cotización
     *
     * @param int   $cotizacion_id ID de la cotización
     * @param array $data          Datos de la línea
     * @return int|WP_Error ID de la línea o error
     */
    public function agregar_linea($cotizacion_id, $data) {
        global $wpdb;

        // Verificar cotización editable
        $cotizacion = $this->get($cotizacion_id);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        if (!in_array($cotizacion->estado, array('BORRADOR', 'ENVIADA'))) {
            return new WP_Error('not_editable', __('Esta cotización no se puede editar', 'gestionadmin-wolk'));
        }

        // Validar descripción
        if (empty($data['descripcion'])) {
            return new WP_Error('missing_description', __('La descripción es requerida', 'gestionadmin-wolk'));
        }

        // Obtener siguiente orden
        $max_orden = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(orden) FROM {$this->table_detalle} WHERE cotizacion_id = %d",
            $cotizacion_id
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

        // Impuesto
        $aplica_impuesto = isset($data['aplica_impuesto']) ? (int)$data['aplica_impuesto'] : 1;
        $impuesto_porcentaje = $aplica_impuesto ? $cotizacion->impuesto_porcentaje : 0;
        $impuesto_monto = $subtotal * ($impuesto_porcentaje / 100);
        $total_linea = $subtotal + $impuesto_monto;

        // Preparar datos
        $linea_data = array(
            'cotizacion_id'       => $cotizacion_id,
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
            'horas_estimadas'     => isset($data['horas_estimadas']) ? floatval($data['horas_estimadas']) : null,
            'tarifa_hora'         => isset($data['tarifa_hora']) ? floatval($data['tarifa_hora']) : null,
            'notas'               => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : '',
        );

        // Insertar línea
        $inserted = $wpdb->insert($this->table_detalle, $linea_data);

        if (!$inserted) {
            return new WP_Error('db_error', __('Error al agregar línea', 'gestionadmin-wolk'));
        }

        // Recalcular totales
        $this->recalcular_totales($cotizacion_id);

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

        // Obtener línea
        $linea = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_detalle} WHERE id = %d",
            $linea_id
        ));

        if (!$linea) {
            return new WP_Error('not_found', __('Línea no encontrada', 'gestionadmin-wolk'));
        }

        // Verificar cotización editable
        $cotizacion = $this->get($linea->cotizacion_id);
        if (!in_array($cotizacion->estado, array('BORRADOR', 'ENVIADA'))) {
            return new WP_Error('not_editable', __('Esta cotización no se puede editar', 'gestionadmin-wolk'));
        }

        // Eliminar línea
        $wpdb->delete($this->table_detalle, array('id' => $linea_id));

        // Recalcular totales
        $this->recalcular_totales($linea->cotizacion_id);

        return true;
    }

    /**
     * Recalcular totales de la cotización
     *
     * @param int $cotizacion_id ID de la cotización
     */
    public function recalcular_totales($cotizacion_id) {
        global $wpdb;

        $cotizacion = $this->get($cotizacion_id);
        if (!$cotizacion) {
            return;
        }

        // Sumar líneas
        $totales = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(subtotal) as subtotal,
                SUM(impuesto_monto) as impuesto_monto,
                SUM(total_linea) as total
             FROM {$this->table_detalle}
             WHERE cotizacion_id = %d",
            $cotizacion_id
        ));

        $subtotal = floatval($totales->subtotal);

        // Aplicar descuento global
        $descuento_porcentaje = floatval($cotizacion->descuento_porcentaje);
        $descuento_monto = $subtotal * ($descuento_porcentaje / 100);
        $base_impuesto = $subtotal - $descuento_monto;

        // Recalcular impuesto
        $impuesto_monto = $base_impuesto * ($cotizacion->impuesto_porcentaje / 100);
        $total = $base_impuesto + $impuesto_monto;

        // Actualizar cotización
        $wpdb->update(
            $this->table_name,
            array(
                'subtotal'        => $subtotal,
                'descuento_monto' => $descuento_monto,
                'impuesto_monto'  => $impuesto_monto,
                'total'           => $total,
            ),
            array('id' => $cotizacion_id)
        );
    }

    // =========================================================================
    // CAMBIOS DE ESTADO
    // =========================================================================

    /**
     * Enviar cotización (BORRADOR → ENVIADA)
     *
     * @param int $id ID de la cotización
     * @return bool|WP_Error
     */
    public function enviar($id) {
        global $wpdb;

        $cotizacion = $this->get($id, true);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        if ($cotizacion->estado !== 'BORRADOR') {
            return new WP_Error('invalid_state', __('Solo cotizaciones en borrador pueden enviarse', 'gestionadmin-wolk'));
        }

        // Verificar que tiene líneas
        if (empty($cotizacion->detalle)) {
            return new WP_Error('no_items', __('La cotización debe tener al menos un concepto', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $wpdb->update(
            $this->table_name,
            array(
                'estado'      => 'ENVIADA',
                'enviado_por' => get_current_user_id(),
                'fecha_envio' => current_time('mysql'),
            ),
            array('id' => $id)
        );

        // TODO: Enviar email al cliente con PDF adjunto

        return true;
    }

    /**
     * Aprobar cotización (cliente aceptó)
     *
     * @param int $id ID de la cotización
     * @return bool|WP_Error
     */
    public function aprobar($id) {
        global $wpdb;

        $cotizacion = $this->get($id);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        if ($cotizacion->estado !== 'ENVIADA') {
            return new WP_Error('invalid_state', __('Solo cotizaciones enviadas pueden aprobarse', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $wpdb->update(
            $this->table_name,
            array(
                'estado'          => 'APROBADA',
                'fecha_respuesta' => current_time('mysql'),
            ),
            array('id' => $id)
        );

        return true;
    }

    /**
     * Rechazar cotización (cliente rechazó)
     *
     * @param int    $id     ID de la cotización
     * @param string $motivo Motivo del rechazo
     * @return bool|WP_Error
     */
    public function rechazar($id, $motivo = '') {
        global $wpdb;

        $cotizacion = $this->get($id);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        if ($cotizacion->estado !== 'ENVIADA') {
            return new WP_Error('invalid_state', __('Solo cotizaciones enviadas pueden rechazarse', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $wpdb->update(
            $this->table_name,
            array(
                'estado'          => 'RECHAZADA',
                'fecha_respuesta' => current_time('mysql'),
                'motivo_rechazo'  => sanitize_textarea_field($motivo),
            ),
            array('id' => $id)
        );

        return true;
    }

    /**
     * Cancelar cotización
     *
     * @param int $id ID de la cotización
     * @return bool|WP_Error
     */
    public function cancelar($id) {
        global $wpdb;

        $cotizacion = $this->get($id);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        if (in_array($cotizacion->estado, array('FACTURADA', 'CANCELADA'))) {
            return new WP_Error('invalid_state', __('Esta cotización no puede cancelarse', 'gestionadmin-wolk'));
        }

        // Actualizar estado
        $wpdb->update(
            $this->table_name,
            array('estado' => 'CANCELADA'),
            array('id' => $id)
        );

        return true;
    }

    /**
     * Eliminar cotización (solo borradores)
     *
     * @param int $id ID de la cotización
     * @return bool|WP_Error
     */
    public function eliminar($id) {
        global $wpdb;

        $cotizacion = $this->get($id);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        if ($cotizacion->estado !== 'BORRADOR') {
            return new WP_Error('not_deletable', __('Solo se pueden eliminar cotizaciones en borrador', 'gestionadmin-wolk'));
        }

        // Eliminar líneas de detalle
        $wpdb->delete($this->table_detalle, array('cotizacion_id' => $id));

        // Eliminar cotización
        $wpdb->delete($this->table_name, array('id' => $id));

        return true;
    }

    // =========================================================================
    // CONVERTIR A FACTURA
    // =========================================================================

    /**
     * Convertir cotización aprobada a factura
     *
     * Crea una nueva factura con los mismos datos y líneas de la cotización.
     *
     * @param int    $id                ID de la cotización
     * @param string $pais_facturacion  País para la factura (obligatorio)
     * @return int|WP_Error ID de la factura creada o error
     */
    public function convertir_a_factura($id, $pais_facturacion) {
        global $wpdb;

        // Validar cotización
        $cotizacion = $this->get($id, true);
        if (!$cotizacion) {
            return new WP_Error('not_found', __('Cotización no encontrada', 'gestionadmin-wolk'));
        }

        // Solo convertir cotizaciones aprobadas
        if ($cotizacion->estado !== 'APROBADA') {
            return new WP_Error('invalid_state', __('Solo cotizaciones aprobadas pueden convertirse a factura', 'gestionadmin-wolk'));
        }

        // Validar país
        if (empty($pais_facturacion)) {
            return new WP_Error('missing_country', __('El país de facturación es requerido', 'gestionadmin-wolk'));
        }

        // Cargar módulo de facturas
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-facturas.php';
        $facturas = GA_Facturas::get_instance();

        // Crear factura
        $factura_data = array(
            'cliente_id'          => $cotizacion->cliente_id,
            'caso_id'             => $cotizacion->caso_id,
            'proyecto_id'         => $cotizacion->proyecto_id,
            'cotizacion_origen_id'=> $id,
            'pais_facturacion'    => $pais_facturacion,
            'moneda'              => $cotizacion->moneda,
            'concepto_general'    => $cotizacion->titulo . "\n" . $cotizacion->descripcion,
            'notas'               => $cotizacion->notas,
            'notas_internas'      => sprintf(
                __('Generada desde cotización %s', 'gestionadmin-wolk'),
                $cotizacion->numero
            ),
            'terminos'            => $cotizacion->terminos,
        );

        $factura_id = $facturas->crear($factura_data);

        if (is_wp_error($factura_id)) {
            return $factura_id;
        }

        // Copiar líneas de detalle
        foreach ($cotizacion->detalle as $linea) {
            $facturas->agregar_linea($factura_id, array(
                'tipo'                => $linea->tipo,
                'codigo'              => $linea->codigo,
                'descripcion'         => $linea->descripcion,
                'cantidad'            => $linea->cantidad,
                'unidad'              => $linea->unidad,
                'precio_unitario'     => $linea->precio_unitario,
                'descuento_porcentaje'=> $linea->descuento_porcentaje,
                'aplica_impuesto'     => $linea->aplica_impuesto,
            ));
        }

        // Actualizar cotización como facturada
        $wpdb->update(
            $this->table_name,
            array(
                'estado'              => 'FACTURADA',
                'factura_generada_id' => $factura_id,
                'fecha_conversion'    => current_time('mysql'),
                'convertido_por'      => get_current_user_id(),
            ),
            array('id' => $id)
        );

        return $factura_id;
    }

    // =========================================================================
    // VERIFICAR VENCIMIENTOS
    // =========================================================================

    /**
     * Marcar cotizaciones vencidas
     *
     * Actualiza el estado de cotizaciones cuya fecha de vigencia ya pasó.
     *
     * @return int Número de cotizaciones marcadas como vencidas
     */
    public function verificar_vencimientos() {
        global $wpdb;

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$this->table_name}
             SET estado = 'VENCIDA'
             WHERE estado = 'ENVIADA'
             AND fecha_vigencia < %s",
            date('Y-m-d')
        ));

        return $result;
    }

    // =========================================================================
    // ESTADÍSTICAS
    // =========================================================================

    /**
     * Obtener estadísticas de cotizaciones
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

        $where_sql = implode(' AND ', $where);

        $sql = "SELECT
                    COUNT(*) as total_cotizaciones,
                    COUNT(CASE WHEN estado = 'BORRADOR' THEN 1 END) as borradores,
                    COUNT(CASE WHEN estado = 'ENVIADA' THEN 1 END) as enviadas,
                    COUNT(CASE WHEN estado = 'APROBADA' THEN 1 END) as aprobadas,
                    COUNT(CASE WHEN estado = 'RECHAZADA' THEN 1 END) as rechazadas,
                    COUNT(CASE WHEN estado = 'FACTURADA' THEN 1 END) as facturadas,
                    COUNT(CASE WHEN estado = 'VENCIDA' THEN 1 END) as vencidas,
                    COUNT(CASE WHEN estado = 'CANCELADA' THEN 1 END) as canceladas,
                    COALESCE(SUM(CASE WHEN estado NOT IN ('BORRADOR', 'CANCELADA') THEN total END), 0) as total_cotizado,
                    COALESCE(SUM(CASE WHEN estado = 'FACTURADA' THEN total END), 0) as total_convertido,
                    COALESCE(SUM(CASE WHEN estado IN ('APROBADA') THEN total END), 0) as total_pendiente_facturar
                FROM {$this->table_name}
                WHERE {$where_sql}";

        if (!empty($values)) {
            $stats = $wpdb->get_row($wpdb->prepare($sql, $values));
        } else {
            $stats = $wpdb->get_row($sql);
        }

        // Calcular tasa de conversión
        $enviadas_total = $stats->enviadas + $stats->aprobadas + $stats->rechazadas + $stats->facturadas + $stats->vencidas;
        $stats->tasa_conversion = ($enviadas_total > 0)
            ? round((($stats->aprobadas + $stats->facturadas) / $enviadas_total) * 100, 1)
            : 0;

        return $stats;
    }

    // =========================================================================
    // GENERACIÓN DE HTML PREVIEW
    // =========================================================================

    /**
     * Generar HTML para vista previa/impresión de cotización
     *
     * @param int $id ID de la cotización
     * @return string HTML de la cotización
     */
    public function generar_html_preview($id) {
        $cotizacion = $this->get($id, true);
        if (!$cotizacion) {
            return '';
        }

        ob_start();
        ?>
        <div class="ga-cotizacion-preview">
            <div class="ga-cotizacion-header">
                <div class="ga-cotizacion-empresa">
                    <h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
                    <p><?php echo esc_html(get_option('ga_empresa_direccion', '')); ?></p>
                </div>
                <div class="ga-cotizacion-info">
                    <h1><?php esc_html_e('COTIZACIÓN', 'gestionadmin-wolk'); ?></h1>
                    <p><strong><?php echo esc_html($cotizacion->numero); ?></strong></p>
                    <p><?php esc_html_e('Fecha:', 'gestionadmin-wolk'); ?> <?php echo esc_html($cotizacion->fecha_emision); ?></p>
                    <p><?php esc_html_e('Vigencia:', 'gestionadmin-wolk'); ?> <?php echo esc_html($cotizacion->fecha_vigencia); ?></p>
                </div>
            </div>

            <div class="ga-cotizacion-cliente">
                <h3><?php esc_html_e('Para:', 'gestionadmin-wolk'); ?></h3>
                <p><strong><?php echo esc_html($cotizacion->cliente_nombre); ?></strong></p>
                <?php if ($cotizacion->contacto_nombre): ?>
                <p><?php esc_html_e('Atención:', 'gestionadmin-wolk'); ?> <?php echo esc_html($cotizacion->contacto_nombre); ?></p>
                <?php endif; ?>
                <p><?php echo esc_html($cotizacion->cliente_email); ?></p>
            </div>

            <?php if ($cotizacion->titulo): ?>
            <div class="ga-cotizacion-titulo">
                <h3><?php echo esc_html($cotizacion->titulo); ?></h3>
                <?php if ($cotizacion->descripcion): ?>
                <p><?php echo nl2br(esc_html($cotizacion->descripcion)); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <table class="ga-cotizacion-tabla">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Cantidad', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Precio', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Subtotal', 'gestionadmin-wolk'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cotizacion->detalle as $linea): ?>
                    <tr>
                        <td>
                            <?php echo esc_html($linea->descripcion); ?>
                            <?php if ($linea->notas): ?>
                            <br><small><?php echo esc_html($linea->notas); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(number_format($linea->cantidad, 2)); ?> <?php echo esc_html($linea->unidad); ?></td>
                        <td><?php echo esc_html($cotizacion->moneda); ?> <?php echo esc_html(number_format($linea->precio_unitario, 2)); ?></td>
                        <td><?php echo esc_html($cotizacion->moneda); ?> <?php echo esc_html(number_format($linea->subtotal, 2)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="ga-cotizacion-totales">
                <table>
                    <tr>
                        <td><?php esc_html_e('Subtotal:', 'gestionadmin-wolk'); ?></td>
                        <td><?php echo esc_html($cotizacion->moneda); ?> <?php echo esc_html(number_format($cotizacion->subtotal, 2)); ?></td>
                    </tr>
                    <?php if ($cotizacion->descuento_monto > 0): ?>
                    <tr>
                        <td><?php esc_html_e('Descuento:', 'gestionadmin-wolk'); ?></td>
                        <td>-<?php echo esc_html($cotizacion->moneda); ?> <?php echo esc_html(number_format($cotizacion->descuento_monto, 2)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($cotizacion->impuesto_monto > 0): ?>
                    <tr>
                        <td><?php echo esc_html($cotizacion->impuesto_nombre); ?> (<?php echo esc_html($cotizacion->impuesto_porcentaje); ?>%):</td>
                        <td><?php echo esc_html($cotizacion->moneda); ?> <?php echo esc_html(number_format($cotizacion->impuesto_monto, 2)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="ga-total">
                        <td><strong><?php esc_html_e('Total:', 'gestionadmin-wolk'); ?></strong></td>
                        <td><strong><?php echo esc_html($cotizacion->moneda); ?> <?php echo esc_html(number_format($cotizacion->total, 2)); ?></strong></td>
                    </tr>
                </table>
            </div>

            <?php if ($cotizacion->notas): ?>
            <div class="ga-cotizacion-notas">
                <h4><?php esc_html_e('Notas:', 'gestionadmin-wolk'); ?></h4>
                <p><?php echo nl2br(esc_html($cotizacion->notas)); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($cotizacion->forma_pago): ?>
            <div class="ga-cotizacion-pago">
                <h4><?php esc_html_e('Forma de Pago:', 'gestionadmin-wolk'); ?></h4>
                <p><?php echo nl2br(esc_html($cotizacion->forma_pago)); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($cotizacion->terminos): ?>
            <div class="ga-cotizacion-terminos">
                <h4><?php esc_html_e('Términos y Condiciones:', 'gestionadmin-wolk'); ?></h4>
                <p><?php echo nl2br(esc_html($cotizacion->terminos)); ?></p>
            </div>
            <?php endif; ?>

            <div class="ga-cotizacion-vigencia">
                <p><em><?php printf(
                    esc_html__('Esta cotización es válida hasta el %s (%d días).', 'gestionadmin-wolk'),
                    esc_html($cotizacion->fecha_vigencia),
                    esc_html($cotizacion->dias_vigencia)
                ); ?></em></p>
            </div>

            <div class="ga-cotizacion-footer">
                <p><?php esc_html_e('Diseñado y desarrollado por', 'gestionadmin-wolk'); ?> <a href="https://wolksoftcr.com">Wolksoftcr.com</a></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
