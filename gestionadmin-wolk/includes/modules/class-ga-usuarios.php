<?php
/**
 * Módulo de Usuarios GestionAdmin
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Usuarios {

    /**
     * Obtener todos los usuarios GA
     */
    public static function get_all($activo_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_usuarios';
        $table_puestos = $wpdb->prefix . 'ga_puestos';
        $table_dep = $wpdb->prefix . 'ga_departamentos';

        $sql = "SELECT u.*,
                       wp.display_name as wp_nombre,
                       wp.user_email as wp_email,
                       p.nombre as puesto_nombre,
                       d.nombre as departamento_nombre
                FROM {$table} u
                LEFT JOIN {$wpdb->users} wp ON u.usuario_wp_id = wp.ID
                LEFT JOIN {$table_puestos} p ON u.puesto_id = p.id
                LEFT JOIN {$table_dep} d ON u.departamento_id = d.id";

        if ($activo_only) {
            $sql .= " WHERE u.activo = 1";
        }

        $sql .= " ORDER BY wp.display_name ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un usuario GA por ID
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_usuarios';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Obtener un usuario GA por usuario WP ID
     */
    public static function get_by_wp_id($wp_user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_usuarios';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE usuario_wp_id = %d",
            $wp_user_id
        ));
    }

    /**
     * Guardar usuario
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_usuarios';

        // Validar usuario WP único
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE usuario_wp_id = %d AND id != %d",
            $data['usuario_wp_id'],
            $id
        ));

        if ($existing) {
            return new WP_Error('duplicate_user', __('Este usuario WordPress ya está registrado', 'gestionadmin-wolk'));
        }

        // Validar código empleado único
        if (!empty($data['codigo_empleado'])) {
            $existing_code = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE codigo_empleado = %s AND id != %d",
                $data['codigo_empleado'],
                $id
            ));

            if ($existing_code) {
                return new WP_Error('duplicate_code', __('El código de empleado ya existe', 'gestionadmin-wolk'));
            }
        }

        if ($id > 0) {
            $result = $wpdb->update($table, $data, array('id' => $id));

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
            }

            return $id;
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table, $data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al insertar', 'gestionadmin-wolk'));
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Obtener usuarios WordPress no registrados en GA
     */
    public static function get_wp_users_not_in_ga() {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_usuarios';

        $registered_ids = $wpdb->get_col("SELECT usuario_wp_id FROM {$table}");

        $args = array(
            'exclude' => $registered_ids,
            'orderby' => 'display_name',
            'order' => 'ASC'
        );

        return get_users($args);
    }

    /**
     * Obtener usuarios para dropdown
     */
    public static function get_for_dropdown($activo_only = true) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_usuarios';

        $sql = "SELECT u.id, u.usuario_wp_id, wp.display_name as nombre
                FROM {$table} u
                LEFT JOIN {$wpdb->users} wp ON u.usuario_wp_id = wp.ID";

        if ($activo_only) {
            $sql .= " WHERE u.activo = 1";
        }

        $sql .= " ORDER BY wp.display_name ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener métodos de pago
     */
    public static function get_metodos_pago() {
        return array(
            'BINANCE' => 'Binance',
            'WISE' => 'Wise',
            'PAYPAL' => 'PayPal',
            'PAYONEER' => 'Payoneer',
            'TRANSFERENCIA' => __('Transferencia Bancaria', 'gestionadmin-wolk'),
            'EFECTIVO' => __('Efectivo', 'gestionadmin-wolk'),
        );
    }
}
