<?php
/**
 * Módulo de Métodos de Pago
 *
 * Gestiona el catálogo de cuentas bancarias, wallets digitales
 * y otros métodos de pago para la empresa.
 *
 * @package GestionAdmin_Wolk
 * @since 1.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Metodos_Pago {

    /**
     * Tipos de método de pago disponibles
     */
    const TIPOS = array(
        'transferencia' => 'Transferencia Bancaria',
        'paypal'        => 'PayPal',
        'wise'          => 'Wise',
        'binance'       => 'Binance',
        'stripe'        => 'Stripe',
        'crypto'        => 'Crypto Wallet',
        'efectivo'      => 'Efectivo',
        'otro'          => 'Otro',
    );

    /**
     * Tipos de cuenta bancaria
     */
    const TIPOS_CUENTA = array(
        'ahorros'  => 'Ahorros',
        'corriente' => 'Corriente',
        'checking' => 'Checking',
        'savings'  => 'Savings',
    );

    /**
     * Redes crypto disponibles
     */
    const REDES_CRYPTO = array(
        'BTC'     => 'Bitcoin (BTC)',
        'ETH'     => 'Ethereum (ETH)',
        'BSC'     => 'Binance Smart Chain (BSC)',
        'TRC20'   => 'Tron (TRC20)',
        'ERC20'   => 'Ethereum (ERC20)',
        'POLYGON' => 'Polygon',
        'SOLANA'  => 'Solana',
        'otro'    => 'Otra Red',
    );

    /**
     * Obtener nombre de la tabla
     *
     * @return string Nombre completo de la tabla
     */
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'ga_metodos_pago';
    }

    /**
     * Obtener todos los métodos de pago
     *
     * @param array $filters Filtros opcionales (tipo, pais_codigo, activo, uso_pagos, uso_cobros)
     * @return array Lista de métodos de pago
     */
    public static function get_all($filters = array()) {
        global $wpdb;

        $table = self::table_name();
        $table_paises = $wpdb->prefix . 'ga_paises_config';

        $sql = "SELECT mp.*,
                       pc.nombre as pais_nombre
                FROM {$table} mp
                LEFT JOIN {$table_paises} pc ON mp.pais_codigo = pc.codigo_iso
                WHERE 1=1";

        // Filtro por tipo
        if (!empty($filters['tipo'])) {
            $sql .= $wpdb->prepare(" AND mp.tipo = %s", $filters['tipo']);
        }

        // Filtro por país
        if (!empty($filters['pais_codigo'])) {
            $sql .= $wpdb->prepare(" AND mp.pais_codigo = %s", $filters['pais_codigo']);
        }

        // Filtro por activo
        if (isset($filters['activo'])) {
            $sql .= $wpdb->prepare(" AND mp.activo = %d", $filters['activo'] ? 1 : 0);
        }

        // Filtro para pagos a proveedores
        if (isset($filters['uso_pagos']) && $filters['uso_pagos']) {
            $sql .= " AND mp.uso_pagos_proveedores = 1";
        }

        // Filtro para cobros de clientes
        if (isset($filters['uso_cobros']) && $filters['uso_cobros']) {
            $sql .= " AND mp.uso_cobros_clientes = 1";
        }

        $sql .= " ORDER BY mp.pais_codigo ASC, mp.orden_prioridad ASC, mp.nombre ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener un método de pago por ID
     *
     * @param int $id ID del método de pago
     * @return object|null Objeto método de pago
     */
    public static function get($id) {
        global $wpdb;

        $table = self::table_name();
        $table_paises = $wpdb->prefix . 'ga_paises_config';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT mp.*,
                    pc.nombre as pais_nombre
             FROM {$table} mp
             LEFT JOIN {$table_paises} pc ON mp.pais_codigo = pc.codigo_iso
             WHERE mp.id = %d",
            $id
        ));
    }

    /**
     * Guardar método de pago (crear o actualizar)
     *
     * @param int   $id   ID del método (0 para nuevo)
     * @param array $data Datos del método
     * @return int|WP_Error ID del método guardado o error
     */
    public static function save($id, $data) {
        global $wpdb;

        $table = self::table_name();

        // Validar campos requeridos
        if (empty($data['tipo'])) {
            return new WP_Error('missing_tipo', __('El tipo es obligatorio', 'gestionadmin-wolk'));
        }

        if (empty($data['nombre'])) {
            return new WP_Error('missing_nombre', __('El nombre es obligatorio', 'gestionadmin-wolk'));
        }

        // Preparar datos según tipo
        $prepared_data = self::prepare_data_by_type($data);

        if ($id > 0) {
            // Actualizar existente
            $prepared_data['updated_by'] = get_current_user_id();

            $result = $wpdb->update($table, $prepared_data, array('id' => $id));

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar', 'gestionadmin-wolk'));
            }

            // Si es principal, quitar principal a los demás del mismo tipo/país
            if (!empty($prepared_data['es_principal'])) {
                self::unset_others_as_principal($id, $prepared_data['tipo'], $prepared_data['pais_codigo'] ?? null);
            }

            return $id;
        } else {
            // Crear nuevo
            $prepared_data['created_at'] = current_time('mysql');
            $prepared_data['created_by'] = get_current_user_id();

            $result = $wpdb->insert($table, $prepared_data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al crear', 'gestionadmin-wolk'));
            }

            $new_id = $wpdb->insert_id;

            // Si es principal, quitar principal a los demás
            if (!empty($prepared_data['es_principal'])) {
                self::unset_others_as_principal($new_id, $prepared_data['tipo'], $prepared_data['pais_codigo'] ?? null);
            }

            return $new_id;
        }
    }

    /**
     * Preparar datos según el tipo de método
     *
     * Limpia campos que no aplican al tipo seleccionado.
     *
     * @param array $data Datos originales
     * @return array Datos preparados
     */
    private static function prepare_data_by_type($data) {
        $prepared = array(
            'tipo'                 => sanitize_text_field($data['tipo']),
            'pais_codigo'          => !empty($data['pais_codigo']) ? sanitize_text_field($data['pais_codigo']) : null,
            'moneda'               => !empty($data['moneda']) ? sanitize_text_field($data['moneda']) : 'USD',
            'nombre'               => sanitize_text_field($data['nombre']),
            'descripcion'          => !empty($data['descripcion']) ? sanitize_textarea_field($data['descripcion']) : null,
            'saldo_actual'         => isset($data['saldo_actual']) ? floatval($data['saldo_actual']) : 0,
            'saldo_minimo'         => isset($data['saldo_minimo']) ? floatval($data['saldo_minimo']) : 0,
            'limite_diario'        => !empty($data['limite_diario']) ? floatval($data['limite_diario']) : null,
            'uso_pagos_proveedores' => !empty($data['uso_pagos_proveedores']) ? 1 : 0,
            'uso_cobros_clientes'  => !empty($data['uso_cobros_clientes']) ? 1 : 0,
            'es_principal'         => !empty($data['es_principal']) ? 1 : 0,
            'orden_prioridad'      => isset($data['orden_prioridad']) ? absint($data['orden_prioridad']) : 0,
            'activo'               => isset($data['activo']) ? ($data['activo'] ? 1 : 0) : 1,
        );

        // Campos bancarios (solo para transferencia)
        if ($data['tipo'] === 'transferencia') {
            $prepared['banco_nombre'] = sanitize_text_field($data['banco_nombre'] ?? '');
            $prepared['banco_tipo_cuenta'] = !empty($data['banco_tipo_cuenta']) ? sanitize_text_field($data['banco_tipo_cuenta']) : null;
            $prepared['banco_numero_cuenta'] = sanitize_text_field($data['banco_numero_cuenta'] ?? '');
            $prepared['banco_titular'] = sanitize_text_field($data['banco_titular'] ?? '');
            $prepared['banco_documento'] = sanitize_text_field($data['banco_documento'] ?? '');
            $prepared['banco_swift'] = sanitize_text_field($data['banco_swift'] ?? '');
            $prepared['banco_iban'] = sanitize_text_field($data['banco_iban'] ?? '');
            $prepared['banco_routing'] = sanitize_text_field($data['banco_routing'] ?? '');
            $prepared['banco_clabe'] = sanitize_text_field($data['banco_clabe'] ?? '');
        } else {
            // Limpiar campos bancarios
            $prepared['banco_nombre'] = null;
            $prepared['banco_tipo_cuenta'] = null;
            $prepared['banco_numero_cuenta'] = null;
            $prepared['banco_titular'] = null;
            $prepared['banco_documento'] = null;
            $prepared['banco_swift'] = null;
            $prepared['banco_iban'] = null;
            $prepared['banco_routing'] = null;
            $prepared['banco_clabe'] = null;
        }

        // Campos wallet digital (PayPal, Wise, Stripe)
        if (in_array($data['tipo'], array('paypal', 'wise', 'stripe'))) {
            $prepared['wallet_email'] = sanitize_email($data['wallet_email'] ?? '');
            $prepared['wallet_usuario'] = sanitize_text_field($data['wallet_usuario'] ?? '');
            $prepared['wallet_account_id'] = sanitize_text_field($data['wallet_account_id'] ?? '');
        } else {
            $prepared['wallet_email'] = null;
            $prepared['wallet_usuario'] = null;
            $prepared['wallet_account_id'] = null;
        }

        // Campos crypto (Binance, crypto wallet)
        if (in_array($data['tipo'], array('binance', 'crypto'))) {
            $prepared['crypto_red'] = !empty($data['crypto_red']) ? sanitize_text_field($data['crypto_red']) : null;
            $prepared['crypto_wallet_address'] = sanitize_text_field($data['crypto_wallet_address'] ?? '');
            $prepared['crypto_token'] = sanitize_text_field($data['crypto_token'] ?? '');
            $prepared['crypto_binance_id'] = sanitize_text_field($data['crypto_binance_id'] ?? '');
        } else {
            $prepared['crypto_red'] = null;
            $prepared['crypto_wallet_address'] = null;
            $prepared['crypto_token'] = null;
            $prepared['crypto_binance_id'] = null;
        }

        return $prepared;
    }

    /**
     * Quitar es_principal a otros métodos del mismo tipo/país
     *
     * @param int         $except_id    ID a excluir
     * @param string      $tipo         Tipo de método
     * @param string|null $pais_codigo  Código de país
     */
    private static function unset_others_as_principal($except_id, $tipo, $pais_codigo = null) {
        global $wpdb;

        $table = self::table_name();

        $sql = $wpdb->prepare(
            "UPDATE {$table} SET es_principal = 0 WHERE id != %d AND tipo = %s",
            $except_id,
            $tipo
        );

        if ($pais_codigo) {
            $sql .= $wpdb->prepare(" AND pais_codigo = %s", $pais_codigo);
        } else {
            $sql .= " AND pais_codigo IS NULL";
        }

        $wpdb->query($sql);
    }

    /**
     * Eliminar método de pago (soft delete)
     *
     * @param int $id ID del método
     * @return bool True si se desactivó correctamente
     */
    public static function delete($id) {
        global $wpdb;

        $table = self::table_name();

        return $wpdb->update(
            $table,
            array('activo' => 0, 'updated_by' => get_current_user_id()),
            array('id' => $id)
        ) !== false;
    }

    /**
     * Eliminar permanentemente un método de pago
     *
     * @param int $id ID del método
     * @return bool|WP_Error True si se eliminó o error
     */
    public static function hard_delete($id) {
        global $wpdb;

        $table = self::table_name();

        // Verificar si está en uso en solicitudes de cobro
        $en_uso = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_solicitudes_cobro WHERE metodo_pago_id = %d",
            $id
        ));

        if ($en_uso > 0) {
            return new WP_Error('in_use', sprintf(
                __('No se puede eliminar: tiene %d solicitud(es) de cobro asociada(s)', 'gestionadmin-wolk'),
                $en_uso
            ));
        }

        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        return $result !== false;
    }

    /**
     * Obtener métodos para dropdown
     *
     * @param array $filters Filtros opcionales
     * @return array Lista simplificada para select
     */
    public static function get_for_dropdown($filters = array()) {
        global $wpdb;

        $table = self::table_name();

        $sql = "SELECT id, tipo, nombre, pais_codigo, moneda FROM {$table} WHERE activo = 1";

        if (!empty($filters['tipo'])) {
            $sql .= $wpdb->prepare(" AND tipo = %s", $filters['tipo']);
        }

        if (!empty($filters['pais_codigo'])) {
            $sql .= $wpdb->prepare(" AND pais_codigo = %s", $filters['pais_codigo']);
        }

        if (isset($filters['uso_pagos']) && $filters['uso_pagos']) {
            $sql .= " AND uso_pagos_proveedores = 1";
        }

        if (isset($filters['uso_cobros']) && $filters['uso_cobros']) {
            $sql .= " AND uso_cobros_clientes = 1";
        }

        $sql .= " ORDER BY pais_codigo ASC, orden_prioridad ASC, nombre ASC";

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener método principal por tipo y país
     *
     * @param string      $tipo        Tipo de método
     * @param string|null $pais_codigo Código de país
     * @return object|null Método principal
     */
    public static function get_principal($tipo, $pais_codigo = null) {
        global $wpdb;

        $table = self::table_name();

        $sql = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE tipo = %s AND es_principal = 1 AND activo = 1",
            $tipo
        );

        if ($pais_codigo) {
            $sql .= $wpdb->prepare(" AND pais_codigo = %s", $pais_codigo);
        }

        return $wpdb->get_row($sql);
    }

    /**
     * Actualizar saldo de una cuenta
     *
     * @param int   $id        ID del método
     * @param float $monto     Monto a sumar (negativo para restar)
     * @param bool  $set_exact Si es true, establece el monto exacto en lugar de sumar
     * @return bool True si se actualizó correctamente
     */
    public static function update_saldo($id, $monto, $set_exact = false) {
        global $wpdb;

        $table = self::table_name();

        if ($set_exact) {
            return $wpdb->update(
                $table,
                array('saldo_actual' => floatval($monto)),
                array('id' => $id)
            ) !== false;
        } else {
            return $wpdb->query($wpdb->prepare(
                "UPDATE {$table} SET saldo_actual = saldo_actual + %f WHERE id = %d",
                floatval($monto),
                $id
            )) !== false;
        }
    }

    /**
     * Obtener resumen de saldos por país
     *
     * @return array Resumen agrupado por país
     */
    public static function get_resumen_saldos() {
        global $wpdb;

        $table = self::table_name();
        $table_paises = $wpdb->prefix . 'ga_paises_config';

        return $wpdb->get_results(
            "SELECT mp.pais_codigo,
                    pc.nombre as pais_nombre,
                    mp.moneda,
                    COUNT(*) as total_cuentas,
                    SUM(mp.saldo_actual) as saldo_total,
                    SUM(CASE WHEN mp.saldo_actual < mp.saldo_minimo THEN 1 ELSE 0 END) as cuentas_bajo_minimo
             FROM {$table} mp
             LEFT JOIN {$table_paises} pc ON mp.pais_codigo = pc.codigo_iso
             WHERE mp.activo = 1
             GROUP BY mp.pais_codigo, mp.moneda
             ORDER BY mp.pais_codigo ASC"
        );
    }

    /**
     * Obtener tipos de método
     *
     * @return array Array asociativo tipo => etiqueta
     */
    public static function get_tipos() {
        return self::TIPOS;
    }

    /**
     * Obtener tipos de cuenta bancaria
     *
     * @return array Array asociativo tipo => etiqueta
     */
    public static function get_tipos_cuenta() {
        return self::TIPOS_CUENTA;
    }

    /**
     * Obtener redes crypto
     *
     * @return array Array asociativo red => etiqueta
     */
    public static function get_redes_crypto() {
        return self::REDES_CRYPTO;
    }

    /**
     * Formatear método para mostrar
     *
     * @param object $metodo Objeto método de pago
     * @return string Texto formateado
     */
    public static function format_display($metodo) {
        $tipo_label = self::TIPOS[$metodo->tipo] ?? $metodo->tipo;
        $pais = $metodo->pais_codigo ? " ({$metodo->pais_codigo})" : '';

        return sprintf('%s - %s%s', $tipo_label, $metodo->nombre, $pais);
    }

    /**
     * Obtener datos resumidos de un método (para solicitudes de cobro)
     *
     * @param int $id ID del método
     * @return string Resumen textual del método
     */
    public static function get_resumen_texto($id) {
        $metodo = self::get($id);

        if (!$metodo) {
            return '';
        }

        $lines = array();
        $lines[] = self::format_display($metodo);

        switch ($metodo->tipo) {
            case 'transferencia':
                if ($metodo->banco_nombre) {
                    $lines[] = "Banco: {$metodo->banco_nombre}";
                }
                if ($metodo->banco_numero_cuenta) {
                    $lines[] = "Cuenta: {$metodo->banco_numero_cuenta}";
                }
                if ($metodo->banco_titular) {
                    $lines[] = "Titular: {$metodo->banco_titular}";
                }
                break;

            case 'paypal':
            case 'wise':
            case 'stripe':
                if ($metodo->wallet_email) {
                    $lines[] = "Email: {$metodo->wallet_email}";
                }
                break;

            case 'binance':
            case 'crypto':
                if ($metodo->crypto_wallet_address) {
                    $lines[] = "Wallet: " . substr($metodo->crypto_wallet_address, 0, 20) . '...';
                }
                if ($metodo->crypto_red) {
                    $lines[] = "Red: {$metodo->crypto_red}";
                }
                break;
        }

        return implode("\n", $lines);
    }
}
