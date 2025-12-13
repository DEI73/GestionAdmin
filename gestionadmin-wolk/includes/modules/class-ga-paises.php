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
     * Guardar país (crear o actualizar)
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_paises_config';

        if ($id > 0) {
            // Actualizar existente
            $result = $wpdb->update(
                $table,
                $data,
                array('id' => $id)
            );

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
            }

            return $id;
        } else {
            // Crear nuevo - verificar que tenga código_iso
            if (empty($data['codigo_iso'])) {
                return new WP_Error('missing_code', __('El código ISO es requerido', 'gestionadmin-wolk'));
            }

            // Verificar que el código no exista
            $exists = self::get_by_code($data['codigo_iso']);
            if ($exists) {
                return new WP_Error('duplicate_code', __('Ya existe un país con ese código ISO', 'gestionadmin-wolk'));
            }

            $result = $wpdb->insert($table, $data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al crear país', 'gestionadmin-wolk'));
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Eliminar país
     *
     * Solo permite eliminar si no tiene datos relacionados (facturas, usuarios, etc.)
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_paises_config';

        // Verificar que el país existe
        $pais = self::get($id);
        if (!$pais) {
            return new WP_Error('not_found', __('País no encontrado', 'gestionadmin-wolk'));
        }

        // Verificar dependencias: facturas
        $facturas = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_facturas WHERE pais_codigo = %s",
            $pais->codigo_iso
        ));
        if ($facturas > 0) {
            return new WP_Error('has_facturas', sprintf(
                __('No se puede eliminar: tiene %d factura(s) asociada(s)', 'gestionadmin-wolk'),
                $facturas
            ));
        }

        // Verificar dependencias: usuarios
        $usuarios = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_usuarios WHERE pais = %s",
            $pais->codigo_iso
        ));
        if ($usuarios > 0) {
            return new WP_Error('has_usuarios', sprintf(
                __('No se puede eliminar: tiene %d usuario(s) asociado(s)', 'gestionadmin-wolk'),
                $usuarios
            ));
        }

        // Verificar dependencias: clientes
        $clientes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_clientes WHERE pais = %s",
            $pais->codigo_iso
        ));
        if ($clientes > 0) {
            return new WP_Error('has_clientes', sprintf(
                __('No se puede eliminar: tiene %d cliente(s) asociado(s)', 'gestionadmin-wolk'),
                $clientes
            ));
        }

        // Eliminar
        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al eliminar', 'gestionadmin-wolk'));
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
