<?php
/**
 * Módulo de Acuerdos Económicos de Órdenes
 *
 * Gestiona los acuerdos económicos específicos de cada orden de trabajo.
 * Define cómo se compensará al aplicante contratado.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes/Modules
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Ordenes_Acuerdos {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Ordenes_Acuerdos
     */
    private static $instance = null;

    /**
     * Nombre de la tabla
     *
     * @var string
     */
    private $table_name;

    /**
     * Tipos de acuerdo disponibles
     *
     * @var array
     */
    public static $tipos_acuerdo = array(
        'HORA_REPORTADA'            => 'Por hora reportada',
        'HORA_APROBADA'             => 'Por hora aprobada',
        'TRABAJO_COMPLETADO'        => 'Por trabajo completado',
        'COMISION_FACTURA'          => 'Comisión por factura pagada',
        'COMISION_HORAS_SUPERVISADAS' => 'Comisión por horas supervisadas',
        'META_RENTABILIDAD'         => 'Meta de rentabilidad',
        'BONO'                      => 'Bono del catálogo',
    );

    /**
     * Frecuencias de pago
     *
     * @var array
     */
    public static $frecuencias_pago = array(
        'POR_EVENTO'   => 'Por evento/entrega',
        'SEMANAL'      => 'Semanal',
        'QUINCENAL'    => 'Quincenal',
        'MENSUAL'      => 'Mensual',
        'AL_FINALIZAR' => 'Al finalizar orden',
    );

    /**
     * Obtener instancia única
     *
     * @return GA_Ordenes_Acuerdos
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
        $this->table_name = $wpdb->prefix . 'ga_ordenes_acuerdos';
    }

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    /**
     * Obtener acuerdo por ID
     *
     * @param int $id ID del acuerdo
     * @return object|null
     */
    public function get($id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, b.nombre as bono_nombre, b.icono as bono_icono
             FROM {$this->table_name} a
             LEFT JOIN {$wpdb->prefix}ga_catalogo_bonos b ON a.bono_id = b.id
             WHERE a.id = %d",
            $id
        ));
    }

    /**
     * Obtener todos los acuerdos de una orden
     *
     * @param int $orden_id ID de la orden de trabajo
     * @param bool $solo_activos Solo retornar acuerdos activos
     * @return array Lista de acuerdos
     */
    public function get_por_orden($orden_id, $solo_activos = true) {
        global $wpdb;

        $where_activo = $solo_activos ? 'AND a.activo = 1' : '';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, b.nombre as bono_nombre, b.icono as bono_icono,
                    b.condicion_descripcion as bono_condicion
             FROM {$this->table_name} a
             LEFT JOIN {$wpdb->prefix}ga_catalogo_bonos b ON a.bono_id = b.id
             WHERE a.orden_id = %d {$where_activo}
             ORDER BY a.orden, a.id",
            $orden_id
        ));
    }

    /**
     * Obtener acuerdos de una orden agrupados por tipo
     *
     * @param int $orden_id ID de la orden
     * @return array Acuerdos agrupados
     */
    public function get_por_orden_agrupados($orden_id) {
        $acuerdos = $this->get_por_orden($orden_id);
        $agrupados = array(
            'principales' => array(), // hora_reportada, hora_aprobada, trabajo_completado
            'comisiones'  => array(), // comision_factura, comision_horas_supervisadas
            'metas'       => array(), // meta_rentabilidad
            'bonos'       => array(), // bonos del catálogo
        );

        foreach ($acuerdos as $acuerdo) {
            switch ($acuerdo->tipo_acuerdo) {
                case 'HORA_REPORTADA':
                case 'HORA_APROBADA':
                case 'TRABAJO_COMPLETADO':
                    $agrupados['principales'][] = $acuerdo;
                    break;
                case 'COMISION_FACTURA':
                case 'COMISION_HORAS_SUPERVISADAS':
                    $agrupados['comisiones'][] = $acuerdo;
                    break;
                case 'META_RENTABILIDAD':
                    $agrupados['metas'][] = $acuerdo;
                    break;
                case 'BONO':
                    $agrupados['bonos'][] = $acuerdo;
                    break;
            }
        }

        return $agrupados;
    }

    /**
     * Crear nuevo acuerdo
     *
     * @param array $data Datos del acuerdo
     * @return int|WP_Error ID del acuerdo creado o error
     */
    public function crear($data) {
        global $wpdb;

        // Validar datos requeridos
        if (empty($data['orden_id'])) {
            return new WP_Error('sin_orden', __('El ID de la orden es requerido.', 'gestionadmin-wolk'));
        }

        if (empty($data['tipo_acuerdo']) || !isset(self::$tipos_acuerdo[$data['tipo_acuerdo']])) {
            return new WP_Error('tipo_invalido', __('Tipo de acuerdo inválido.', 'gestionadmin-wolk'));
        }

        // Si es tipo BONO, validar que exista el bono
        if ($data['tipo_acuerdo'] === 'BONO' && empty($data['bono_id'])) {
            return new WP_Error('sin_bono', __('Debe seleccionar un bono del catálogo.', 'gestionadmin-wolk'));
        }

        // Obtener siguiente orden para esta orden de trabajo
        $max_orden = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(orden) FROM {$this->table_name} WHERE orden_id = %d",
            $data['orden_id']
        ));
        $nuevo_orden = ($max_orden ?: 0) + 1;

        // Preparar datos para insertar
        $insert_data = array(
            'orden_id'        => absint($data['orden_id']),
            'tipo_acuerdo'    => $data['tipo_acuerdo'],
            'valor'           => floatval($data['valor'] ?? 0),
            'es_porcentaje'   => absint($data['es_porcentaje'] ?? 0),
            'bono_id'         => !empty($data['bono_id']) ? absint($data['bono_id']) : null,
            'condicion'       => sanitize_text_field($data['condicion'] ?? ''),
            'condicion_valor' => isset($data['condicion_valor']) ? floatval($data['condicion_valor']) : null,
            'descripcion'     => sanitize_textarea_field($data['descripcion'] ?? ''),
            'notas_internas'  => sanitize_textarea_field($data['notas_internas'] ?? ''),
            'frecuencia_pago' => in_array($data['frecuencia_pago'] ?? '', array_keys(self::$frecuencias_pago))
                                 ? $data['frecuencia_pago'] : 'MENSUAL',
            'orden'           => isset($data['orden']) ? absint($data['orden']) : $nuevo_orden,
            'activo'          => 1,
            'created_by'      => get_current_user_id(),
        );

        // Determinar si es porcentaje automáticamente según el tipo
        if (in_array($data['tipo_acuerdo'], array('COMISION_FACTURA', 'COMISION_HORAS_SUPERVISADAS', 'META_RENTABILIDAD'))) {
            $insert_data['es_porcentaje'] = 1;
        }

        $result = $wpdb->insert($this->table_name, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Error al crear el acuerdo.', 'gestionadmin-wolk'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Actualizar acuerdo existente
     *
     * @param int $id ID del acuerdo
     * @param array $data Datos a actualizar
     * @return bool|WP_Error
     */
    public function actualizar($id, $data) {
        global $wpdb;

        $acuerdo = $this->get($id);
        if (!$acuerdo) {
            return new WP_Error('no_encontrado', __('Acuerdo no encontrado.', 'gestionadmin-wolk'));
        }

        $update_data = array();
        $update_format = array();

        if (isset($data['valor'])) {
            $update_data['valor'] = floatval($data['valor']);
            $update_format[] = '%f';
        }

        if (isset($data['es_porcentaje'])) {
            $update_data['es_porcentaje'] = absint($data['es_porcentaje']);
            $update_format[] = '%d';
        }

        if (isset($data['condicion'])) {
            $update_data['condicion'] = sanitize_text_field($data['condicion']);
            $update_format[] = '%s';
        }

        if (isset($data['condicion_valor'])) {
            $update_data['condicion_valor'] = floatval($data['condicion_valor']);
            $update_format[] = '%f';
        }

        if (isset($data['descripcion'])) {
            $update_data['descripcion'] = sanitize_textarea_field($data['descripcion']);
            $update_format[] = '%s';
        }

        if (isset($data['notas_internas'])) {
            $update_data['notas_internas'] = sanitize_textarea_field($data['notas_internas']);
            $update_format[] = '%s';
        }

        if (isset($data['frecuencia_pago']) && in_array($data['frecuencia_pago'], array_keys(self::$frecuencias_pago))) {
            $update_data['frecuencia_pago'] = $data['frecuencia_pago'];
            $update_format[] = '%s';
        }

        if (isset($data['orden'])) {
            $update_data['orden'] = absint($data['orden']);
            $update_format[] = '%d';
        }

        if (isset($data['activo'])) {
            $update_data['activo'] = absint($data['activo']);
            $update_format[] = '%d';
        }

        if (empty($update_data)) {
            return true;
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Eliminar acuerdo
     *
     * @param int $id ID del acuerdo
     * @return bool|WP_Error
     */
    public function eliminar($id) {
        global $wpdb;

        $acuerdo = $this->get($id);
        if (!$acuerdo) {
            return new WP_Error('no_encontrado', __('Acuerdo no encontrado.', 'gestionadmin-wolk'));
        }

        $result = $wpdb->delete($this->table_name, array('id' => $id), array('%d'));

        return $result !== false;
    }

    /**
     * Desactivar acuerdo (soft delete)
     *
     * @param int $id ID del acuerdo
     * @return bool|WP_Error
     */
    public function desactivar($id) {
        return $this->actualizar($id, array('activo' => 0));
    }

    // =========================================================================
    // OPERACIONES EN LOTE
    // =========================================================================

    /**
     * Guardar múltiples acuerdos para una orden
     *
     * Este método es la forma principal de guardar acuerdos desde el formulario.
     * Recibe un array de acuerdos y los sincroniza con los existentes.
     *
     * @param int $orden_id ID de la orden de trabajo
     * @param array $acuerdos_data Array de acuerdos a guardar
     * @return array|WP_Error Resultado con creados, actualizados, eliminados
     */
    public function guardar_acuerdos($orden_id, $acuerdos_data) {
        global $wpdb;

        if (empty($orden_id)) {
            return new WP_Error('sin_orden', __('El ID de la orden es requerido.', 'gestionadmin-wolk'));
        }

        // Validar que haya al menos un acuerdo activo
        $tiene_acuerdo_activo = false;
        foreach ($acuerdos_data as $acuerdo) {
            if (!empty($acuerdo['activo']) || !isset($acuerdo['activo'])) {
                $tiene_acuerdo_activo = true;
                break;
            }
        }

        if (!$tiene_acuerdo_activo && !empty($acuerdos_data)) {
            return new WP_Error('sin_acuerdo_activo', __('Debe haber al menos un acuerdo económico activo.', 'gestionadmin-wolk'));
        }

        $resultado = array(
            'creados'      => 0,
            'actualizados' => 0,
            'eliminados'   => 0,
            'errores'      => array(),
        );

        // Obtener IDs de acuerdos existentes
        $existentes = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE orden_id = %d",
            $orden_id
        ));
        $ids_procesados = array();

        // Procesar cada acuerdo del formulario
        $orden_counter = 1;
        foreach ($acuerdos_data as $acuerdo_data) {
            $acuerdo_data['orden_id'] = $orden_id;
            $acuerdo_data['orden'] = $orden_counter++;

            // Si tiene ID, actualizar
            if (!empty($acuerdo_data['id'])) {
                $id = absint($acuerdo_data['id']);
                $ids_procesados[] = $id;

                $result = $this->actualizar($id, $acuerdo_data);
                if (is_wp_error($result)) {
                    $resultado['errores'][] = $result->get_error_message();
                } else {
                    $resultado['actualizados']++;
                }
            } else {
                // Crear nuevo
                $result = $this->crear($acuerdo_data);
                if (is_wp_error($result)) {
                    $resultado['errores'][] = $result->get_error_message();
                } else {
                    $ids_procesados[] = $result;
                    $resultado['creados']++;
                }
            }
        }

        // Eliminar acuerdos que ya no están en el formulario
        $ids_a_eliminar = array_diff($existentes, $ids_procesados);
        foreach ($ids_a_eliminar as $id_eliminar) {
            $wpdb->delete($this->table_name, array('id' => $id_eliminar), array('%d'));
            $resultado['eliminados']++;
        }

        return $resultado;
    }

    /**
     * Eliminar todos los acuerdos de una orden
     *
     * @param int $orden_id ID de la orden
     * @return int Número de acuerdos eliminados
     */
    public function eliminar_por_orden($orden_id) {
        global $wpdb;

        return $wpdb->delete(
            $this->table_name,
            array('orden_id' => $orden_id),
            array('%d')
        );
    }

    /**
     * Copiar acuerdos de una orden a otra
     *
     * Útil para duplicar órdenes de trabajo.
     *
     * @param int $orden_origen_id ID de la orden origen
     * @param int $orden_destino_id ID de la orden destino
     * @return int Número de acuerdos copiados
     */
    public function copiar_acuerdos($orden_origen_id, $orden_destino_id) {
        $acuerdos = $this->get_por_orden($orden_origen_id, false);
        $copiados = 0;

        foreach ($acuerdos as $acuerdo) {
            $nuevo_acuerdo = array(
                'orden_id'        => $orden_destino_id,
                'tipo_acuerdo'    => $acuerdo->tipo_acuerdo,
                'valor'           => $acuerdo->valor,
                'es_porcentaje'   => $acuerdo->es_porcentaje,
                'bono_id'         => $acuerdo->bono_id,
                'condicion'       => $acuerdo->condicion,
                'condicion_valor' => $acuerdo->condicion_valor,
                'descripcion'     => $acuerdo->descripcion,
                'frecuencia_pago' => $acuerdo->frecuencia_pago,
                'orden'           => $acuerdo->orden,
                'activo'          => $acuerdo->activo,
            );

            $result = $this->crear($nuevo_acuerdo);
            if (!is_wp_error($result)) {
                $copiados++;
            }
        }

        return $copiados;
    }

    // =========================================================================
    // UTILIDADES Y FORMATEADORES
    // =========================================================================

    /**
     * Formatear acuerdo para mostrar en UI
     *
     * @param object $acuerdo Objeto acuerdo
     * @return array Datos formateados
     */
    public function formatear_para_ui($acuerdo) {
        $valor_formateado = $acuerdo->es_porcentaje
            ? number_format($acuerdo->valor, 2) . '%'
            : '$' . number_format($acuerdo->valor, 2);

        $descripcion_tipo = self::$tipos_acuerdo[$acuerdo->tipo_acuerdo] ?? $acuerdo->tipo_acuerdo;

        // Para bonos, usar nombre del catálogo
        if ($acuerdo->tipo_acuerdo === 'BONO' && $acuerdo->bono_nombre) {
            $descripcion_tipo = $acuerdo->bono_nombre;
        }

        return array(
            'id'                 => $acuerdo->id,
            'tipo'               => $acuerdo->tipo_acuerdo,
            'tipo_label'         => $descripcion_tipo,
            'valor'              => $acuerdo->valor,
            'valor_formateado'   => $valor_formateado,
            'es_porcentaje'      => (bool) $acuerdo->es_porcentaje,
            'descripcion'        => $acuerdo->descripcion,
            'condicion'          => $acuerdo->condicion,
            'condicion_valor'    => $acuerdo->condicion_valor,
            'frecuencia'         => self::$frecuencias_pago[$acuerdo->frecuencia_pago] ?? $acuerdo->frecuencia_pago,
            'bono_icono'         => $acuerdo->bono_icono ?? 'dashicons-money-alt',
            'activo'             => (bool) $acuerdo->activo,
        );
    }

    /**
     * Obtener acuerdos formateados para mostrar en portal público
     *
     * @param int $orden_id ID de la orden
     * @return array Acuerdos formateados para UI
     */
    public function get_para_portal($orden_id) {
        $acuerdos = $this->get_por_orden($orden_id);
        $formateados = array();

        foreach ($acuerdos as $acuerdo) {
            $formateados[] = $this->formatear_para_ui($acuerdo);
        }

        return $formateados;
    }

    /**
     * Verificar si una orden tiene al menos un acuerdo activo
     *
     * @param int $orden_id ID de la orden
     * @return bool
     */
    public function tiene_acuerdos($orden_id) {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE orden_id = %d AND activo = 1",
            $orden_id
        ));

        return $count > 0;
    }

    /**
     * Obtener resumen de compensación de una orden
     *
     * @param int $orden_id ID de la orden
     * @return string Texto resumen para mostrar
     */
    public function get_resumen_compensacion($orden_id) {
        $acuerdos = $this->get_por_orden($orden_id);

        if (empty($acuerdos)) {
            return __('Sin acuerdos económicos definidos', 'gestionadmin-wolk');
        }

        $partes = array();

        foreach ($acuerdos as $acuerdo) {
            $valor = $acuerdo->es_porcentaje
                ? $acuerdo->valor . '%'
                : '$' . number_format($acuerdo->valor, 2);

            switch ($acuerdo->tipo_acuerdo) {
                case 'HORA_REPORTADA':
                    $partes[] = $valor . '/hora reportada';
                    break;
                case 'HORA_APROBADA':
                    $partes[] = $valor . '/hora aprobada';
                    break;
                case 'TRABAJO_COMPLETADO':
                    $partes[] = $valor . ' al completar';
                    break;
                case 'COMISION_FACTURA':
                    $partes[] = $valor . ' comisión facturas';
                    break;
                case 'BONO':
                    $nombre = $acuerdo->bono_nombre ?: 'Bono';
                    $partes[] = $nombre . ': ' . $valor;
                    break;
            }
        }

        return implode(' + ', array_slice($partes, 0, 3)) . (count($partes) > 3 ? '...' : '');
    }
}
