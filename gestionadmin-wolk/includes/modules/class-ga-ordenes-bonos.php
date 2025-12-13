<?php
/**
 * Módulo de Bonos para Órdenes de Trabajo
 *
 * Gestiona la relación entre órdenes de trabajo y bonos del catálogo.
 * Permite personalizar el monto y agregar detalle específico para cada orden.
 *
 * @package GestionAdmin_Wolk
 * @since 1.5.3
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Ordenes_Bonos {

    /**
     * Nombre de la tabla (sin prefijo)
     */
    private static $table = 'ga_ordenes_bonos';

    /**
     * Obtener nombre completo de la tabla
     *
     * @return string Nombre de tabla con prefijo
     */
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table;
    }

    /**
     * Guardar bonos de una orden
     *
     * Elimina los bonos anteriores y guarda los nuevos.
     *
     * @param int   $orden_id    ID de la orden de trabajo
     * @param array $bonos_data  Array de bonos con estructura:
     *                           [['bono_id' => 1, 'monto' => 50.00, 'detalle' => 'texto'], ...]
     * @return bool True si se guardó correctamente
     */
    public static function guardar_bonos($orden_id, $bonos_data) {
        global $wpdb;

        $orden_id = absint($orden_id);
        if ($orden_id <= 0) {
            return false;
        }

        // Eliminar bonos anteriores de esta orden
        $wpdb->delete(
            self::table_name(),
            array('orden_id' => $orden_id),
            array('%d')
        );

        // Si no hay bonos nuevos, terminar
        if (empty($bonos_data) || !is_array($bonos_data)) {
            return true;
        }

        // Insertar nuevos bonos
        foreach ($bonos_data as $bono) {
            if (empty($bono['bono_id'])) {
                continue;
            }

            $monto_personalizado = null;
            if (!empty($bono['monto']) && floatval($bono['monto']) > 0) {
                $monto_personalizado = floatval($bono['monto']);
            }

            $wpdb->insert(
                self::table_name(),
                array(
                    'orden_id'            => $orden_id,
                    'bono_id'             => absint($bono['bono_id']),
                    'detalle'             => sanitize_textarea_field($bono['detalle'] ?? ''),
                    'monto_personalizado' => $monto_personalizado,
                    'activo'              => 1,
                    'created_at'          => current_time('mysql'),
                ),
                array('%d', '%d', '%s', '%f', '%d', '%s')
            );
        }

        return true;
    }

    /**
     * Obtener bonos de una orden (con datos del catálogo)
     *
     * @param int $orden_id ID de la orden de trabajo
     * @return array Lista de bonos con datos del catálogo
     */
    public static function get_por_orden($orden_id) {
        global $wpdb;

        $orden_id = absint($orden_id);
        if ($orden_id <= 0) {
            return array();
        }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT ob.*,
                    cb.nombre,
                    cb.descripcion as descripcion_catalogo,
                    cb.monto as monto_catalogo,
                    cb.tipo
             FROM " . self::table_name() . " ob
             LEFT JOIN {$wpdb->prefix}ga_catalogo_bonos cb ON ob.bono_id = cb.id
             WHERE ob.orden_id = %d AND ob.activo = 1
             ORDER BY ob.id ASC",
            $orden_id
        ));
    }

    /**
     * Obtener monto efectivo de un bono (personalizado o del catálogo)
     *
     * @param object $orden_bono Objeto bono de orden
     * @return float Monto a aplicar
     */
    public static function get_monto_efectivo($orden_bono) {
        if (!empty($orden_bono->monto_personalizado) && $orden_bono->monto_personalizado > 0) {
            return floatval($orden_bono->monto_personalizado);
        }
        return floatval($orden_bono->monto_catalogo ?? 0);
    }

    /**
     * Eliminar todos los bonos de una orden
     *
     * @param int $orden_id ID de la orden de trabajo
     * @return bool True si se eliminaron correctamente
     */
    public static function eliminar_por_orden($orden_id) {
        global $wpdb;

        $orden_id = absint($orden_id);
        if ($orden_id <= 0) {
            return false;
        }

        return $wpdb->delete(
            self::table_name(),
            array('orden_id' => $orden_id),
            array('%d')
        ) !== false;
    }

    /**
     * Obtener bonos formateados para JavaScript
     *
     * @param int $orden_id ID de la orden de trabajo
     * @return array Array formateado para JS
     */
    public static function get_para_js($orden_id) {
        $bonos = self::get_por_orden($orden_id);
        $resultado = array();

        foreach ($bonos as $bono) {
            $resultado[] = array(
                'id'                  => $bono->id,
                'bono_id'             => $bono->bono_id,
                'nombre'              => $bono->nombre,
                'detalle'             => $bono->detalle,
                'monto_personalizado' => $bono->monto_personalizado,
                'monto_catalogo'      => $bono->monto_catalogo,
                'monto_efectivo'      => self::get_monto_efectivo($bono),
                'tipo'                => $bono->tipo,
            );
        }

        return $resultado;
    }
}
