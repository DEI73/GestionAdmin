<?php
/**
 * Módulo de Departamentos
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Departamentos {

    /**
     * Obtener todos los departamentos
     */
    public static function get_all($activo_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_departamentos';

        $sql = "SELECT d.*, u.display_name as jefe_nombre
                FROM {$table} d
                LEFT JOIN {$wpdb->users} u ON d.jefe_id = u.ID";

        if ($activo_only) {
            $sql .= " WHERE d.activo = 1";
        }

        $sql .= " ORDER BY d.nombre ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un departamento por ID
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_departamentos';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Guardar departamento (crear o actualizar)
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_departamentos';

        // Validar código único
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE codigo = %s AND id != %d",
            $data['codigo'],
            $id
        ));

        if ($existing) {
            return new WP_Error('duplicate_code', __('El código ya existe', 'gestionadmin-wolk'));
        }

        if ($id > 0) {
            // Actualizar
            $result = $wpdb->update(
                $table,
                $data,
                array('id' => $id),
                array('%s', '%s', '%s', '%s', '%d', '%d'),
                array('%d')
            );

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
            }

            return $id;
        } else {
            // Insertar
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert(
                $table,
                $data,
                array('%s', '%s', '%s', '%s', '%d', '%d', '%s')
            );

            if ($result === false) {
                return new WP_Error('db_error', __('Error al insertar', 'gestionadmin-wolk'));
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Eliminar departamento
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_departamentos';

        // Verificar si hay puestos asociados
        $puestos = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_puestos WHERE departamento_id = %d",
            $id
        ));

        if ($puestos > 0) {
            return new WP_Error('has_puestos', __('No se puede eliminar: tiene puestos asociados', 'gestionadmin-wolk'));
        }

        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al eliminar', 'gestionadmin-wolk'));
        }

        return true;
    }

    /**
     * Obtener tipos de departamento
     */
    public static function get_tipos() {
        return array(
            'OPERACION_FIJA' => __('Operación Fija', 'gestionadmin-wolk'),
            'PROYECTOS' => __('Proyectos', 'gestionadmin-wolk'),
            'SOPORTE' => __('Soporte', 'gestionadmin-wolk'),
            'COMERCIAL' => __('Comercial', 'gestionadmin-wolk'),
        );
    }
}
