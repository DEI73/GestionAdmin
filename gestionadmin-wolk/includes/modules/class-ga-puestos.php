<?php
/**
 * Módulo de Puestos
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Puestos {

    /**
     * Obtener todos los puestos
     */
    public static function get_all($activo_only = false, $departamento_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos';
        $table_dep = $wpdb->prefix . 'ga_departamentos';

        $sql = "SELECT p.*, d.nombre as departamento_nombre
                FROM {$table} p
                LEFT JOIN {$table_dep} d ON p.departamento_id = d.id
                WHERE 1=1";

        if ($activo_only) {
            $sql .= " AND p.activo = 1";
        }

        if ($departamento_id > 0) {
            $sql .= $wpdb->prepare(" AND p.departamento_id = %d", $departamento_id);
        }

        $sql .= " ORDER BY d.nombre ASC, p.nivel_jerarquico ASC, p.nombre ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un puesto por ID
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Guardar puesto
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos';

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
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table, $data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al insertar', 'gestionadmin-wolk'));
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Eliminar puesto
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos';

        // Verificar si hay usuarios asociados
        $usuarios = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_usuarios WHERE puesto_id = %d",
            $id
        ));

        if ($usuarios > 0) {
            return new WP_Error('has_usuarios', __('No se puede eliminar: tiene usuarios asociados', 'gestionadmin-wolk'));
        }

        // Eliminar escalas primero
        $wpdb->delete($wpdb->prefix . 'ga_puestos_escalas', array('puesto_id' => $id), array('%d'));

        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al eliminar', 'gestionadmin-wolk'));
        }

        return true;
    }

    /**
     * Obtener niveles jerárquicos
     */
    public static function get_niveles() {
        return array(
            1 => __('Nivel 1 - Socio', 'gestionadmin-wolk'),
            2 => __('Nivel 2 - Director', 'gestionadmin-wolk'),
            3 => __('Nivel 3 - Jefe', 'gestionadmin-wolk'),
            4 => __('Nivel 4 - Empleado', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener flujos de revisión
     */
    public static function get_flujos() {
        return array(
            'SOLO_JEFE' => __('Solo Jefe', 'gestionadmin-wolk'),
            'QA_JEFE' => __('QA + Jefe', 'gestionadmin-wolk'),
            'QA_JEFE_DIRECTOR' => __('QA + Jefe + Director', 'gestionadmin-wolk'),
        );
    }

    // =========================================================================
    // ESCALAS DE TARIFA
    // =========================================================================

    /**
     * Obtener escalas de un puesto
     */
    public static function get_escalas($puesto_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos_escalas';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE puesto_id = %d ORDER BY anio_antiguedad ASC",
            $puesto_id
        ));
    }

    /**
     * Obtener una escala por ID
     */
    public static function get_escala($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos_escalas';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Guardar escala
     */
    public static function save_escala($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos_escalas';

        // Validar año único por puesto
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE puesto_id = %d AND anio_antiguedad = %d AND id != %d",
            $data['puesto_id'],
            $data['anio_antiguedad'],
            $id
        ));

        if ($existing) {
            return new WP_Error('duplicate_year', __('Ya existe una escala para ese año', 'gestionadmin-wolk'));
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
     * Eliminar escala
     */
    public static function delete_escala($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_puestos_escalas';

        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al eliminar', 'gestionadmin-wolk'));
        }

        return true;
    }
}
