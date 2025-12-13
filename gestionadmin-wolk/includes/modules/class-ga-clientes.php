<?php
/**
 * Módulo de Clientes GestionAdmin
 *
 * Gestiona los clientes de la empresa con soporte para:
 * - Personas naturales y empresas
 * - Datos fiscales por país
 * - Portal de cliente con usuario WP
 * - Integración con Stripe/PayPal
 *
 * @package GestionAdmin_Wolk
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Clientes {

    /**
     * Obtener todos los clientes
     *
     * @param bool $activo_only Si true, solo retorna clientes activos
     * @return array Lista de objetos cliente con datos de país
     */
    public static function get_all($activo_only = false) {
        global $wpdb;

        // Definir tablas a consultar
        $table = $wpdb->prefix . 'ga_clientes';
        $table_paises = $wpdb->prefix . 'ga_paises_config';

        // Construir SQL con LEFT JOIN para obtener nombre del país
        $sql = "SELECT c.*,
                       p.nombre as pais_nombre
                FROM {$table} c
                LEFT JOIN {$table_paises} p ON c.pais = p.codigo_iso";

        // Filtrar por estado activo si se solicita
        if ($activo_only) {
            $sql .= " WHERE c.activo = 1";
        }

        // Ordenar por nombre comercial
        $sql .= " ORDER BY c.nombre_comercial ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un cliente por ID
     *
     * @param int $id ID del cliente
     * @return object|null Objeto cliente o null si no existe
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_clientes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Obtener cliente por código
     *
     * @param string $codigo Código del cliente (CLI-XXX)
     * @return object|null Objeto cliente o null si no existe
     */
    public static function get_by_codigo($codigo) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_clientes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE codigo = %s",
            $codigo
        ));
    }

    /**
     * Generar código único para cliente
     *
     * Formato: CLI-XXX donde XXX es un consecutivo de 3 dígitos
     *
     * @return string Código único generado
     */
    public static function generate_codigo() {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_clientes';

        // Obtener el último código generado
        $last = $wpdb->get_var("SELECT codigo FROM {$table} ORDER BY id DESC LIMIT 1");

        if ($last) {
            // Extraer número del código existente
            $num = intval(substr($last, 4)); // CLI-XXX -> XXX
            $next = $num + 1;
        } else {
            $next = 1;
        }

        // Formatear con ceros a la izquierda (mínimo 3 dígitos)
        return 'CLI-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Guardar cliente (crear o actualizar)
     *
     * @param int $id ID del cliente (0 para nuevo)
     * @param array $data Datos del cliente
     * @return int|WP_Error ID del cliente guardado o error
     */
    public static function save($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_clientes';

        // Validar código único
        if (!empty($data['codigo'])) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE codigo = %s AND id != %d",
                $data['codigo'],
                $id
            ));

            if ($existing) {
                return new WP_Error('duplicate_code', __('El código de cliente ya existe', 'gestionadmin-wolk'));
            }
        }

        // Validar documento único (si se proporciona)
        if (!empty($data['documento_numero'])) {
            $existing_doc = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE documento_numero = %s AND id != %d",
                $data['documento_numero'],
                $id
            ));

            if ($existing_doc) {
                return new WP_Error('duplicate_document', __('El número de documento ya está registrado', 'gestionadmin-wolk'));
            }
        }

        if ($id > 0) {
            // Actualizar cliente existente
            $result = $wpdb->update($table, $data, array('id' => $id));

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
            }

            return $id;
        } else {
            // Crear nuevo cliente
            $data['created_at'] = current_time('mysql');

            // Generar código si no se proporciona
            if (empty($data['codigo'])) {
                $data['codigo'] = self::generate_codigo();
            }

            $result = $wpdb->insert($table, $data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al insertar', 'gestionadmin-wolk'));
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Eliminar cliente (soft delete)
     *
     * @param int $id ID del cliente
     * @return bool True si se eliminó correctamente
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_clientes';

        // Soft delete: marcar como inactivo
        return $wpdb->update(
            $table,
            array('activo' => 0),
            array('id' => $id)
        ) !== false;
    }

    /**
     * Obtener clientes para dropdown
     *
     * @param bool $activo_only Solo clientes activos
     * @return array Lista simplificada para select
     */
    public static function get_for_dropdown($activo_only = true) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_clientes';

        $sql = "SELECT id, codigo, nombre_comercial
                FROM {$table}";

        if ($activo_only) {
            $sql .= " WHERE activo = 1";
        }

        $sql .= " ORDER BY nombre_comercial ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener tipos de cliente
     *
     * @return array Array asociativo tipo => etiqueta
     */
    public static function get_tipos() {
        return array(
            'PERSONA_NATURAL' => __('Persona Natural', 'gestionadmin-wolk'),
            'EMPRESA'         => __('Empresa', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener tipos de documento por país
     *
     * @param string $pais Código ISO del país
     * @return array Array de tipos de documento
     */
    public static function get_tipos_documento($pais = '') {
        // Tipos de documento comunes por país
        $tipos = array(
            'CO' => array('NIT', 'CC', 'CE', 'PASAPORTE'),
            'MX' => array('RFC', 'CURP', 'INE'),
            'US' => array('EIN', 'SSN', 'ITIN'),
            'CR' => array('CEDULA_JURIDICA', 'CEDULA_FISICA', 'DIMEX'),
        );

        // Tipos genéricos si no hay país específico
        $default = array('NIT', 'CC', 'PASAPORTE', 'RFC', 'OTRO');

        return isset($tipos[$pais]) ? $tipos[$pais] : $default;
    }

    /**
     * Obtener métodos de pago disponibles para clientes
     *
     * @return array Array asociativo método => etiqueta
     */
    public static function get_metodos_pago() {
        return array(
            'TRANSFERENCIA' => __('Transferencia Bancaria', 'gestionadmin-wolk'),
            'STRIPE'        => 'Stripe',
            'PAYPAL'        => 'PayPal',
            'EFECTIVO'      => __('Efectivo', 'gestionadmin-wolk'),
        );
    }

    /**
     * Contar casos activos de un cliente
     *
     * @param int $cliente_id ID del cliente
     * @return int Número de casos activos
     */
    public static function count_casos($cliente_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_casos';

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE cliente_id = %d AND estado NOT IN ('CERRADO', 'CANCELADO')",
            $cliente_id
        ));
    }

    /**
     * Buscar clientes
     *
     * @param string $search Término de búsqueda
     * @param int $limit Límite de resultados
     * @return array Lista de clientes que coinciden
     */
    public static function search($search, $limit = 10) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_clientes';

        $like = '%' . $wpdb->esc_like($search) . '%';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT id, codigo, nombre_comercial, email
             FROM {$table}
             WHERE activo = 1
               AND (codigo LIKE %s OR nombre_comercial LIKE %s OR email LIKE %s)
             ORDER BY nombre_comercial ASC
             LIMIT %d",
            $like, $like, $like, $limit
        ));
    }
}
