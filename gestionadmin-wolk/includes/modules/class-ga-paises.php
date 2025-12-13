<?php
/**
 * Módulo de Países
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Paises {

    /**
     * Obtener todos los países
     */
    public static function get_all($activo_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_paises_config';

        $sql = "SELECT * FROM {$table}";

        if ($activo_only) {
            $sql .= " WHERE activo = 1";
        }

        $sql .= " ORDER BY nombre ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un país por ID
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_paises_config';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Obtener un país por código ISO
     */
    public static function get_by_code($codigo_iso) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_paises_config';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE codigo_iso = %s",
            $codigo_iso
        ));
    }

    /**
     * Guardar país (solo actualizar, no crear)
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_paises_config';

        $result = $wpdb->update(
            $table,
            $data,
            array('id' => $id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
        }

        return true;
    }

    /**
     * Obtener para dropdown
     */
    public static function get_for_dropdown($activo_only = true) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_paises_config';

        $sql = "SELECT codigo_iso, nombre FROM {$table}";

        if ($activo_only) {
            $sql .= " WHERE activo = 1";
        }

        $sql .= " ORDER BY nombre ASC";

        return $wpdb->get_results($sql);
    }
}
