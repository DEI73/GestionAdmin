<?php
/**
 * Vista Admin: Métodos de Pago
 *
 * Panel de administración para gestión del catálogo de métodos de pago:
 * cuentas bancarias, wallets digitales, crypto, etc.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.6.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar módulos necesarios
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-metodos-pago.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-paises.php';

// Determinar acción actual
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$metodo_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_metodo_nonce'])) {
    if (!wp_verify_nonce($_POST['ga_metodo_nonce'], 'ga_metodo_action')) {
        wp_die(__('Error de seguridad', 'gestionadmin-wolk'));
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para realizar esta acción.', 'gestionadmin-wolk'));
    }

    $post_action = isset($_POST['metodo_action']) ? sanitize_text_field($_POST['metodo_action']) : '';

    switch ($post_action) {
        case 'crear':
            $result = GA_Metodos_Pago::save(0, $_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-metodos-pago&action=edit&id=' . $result . '&message=created'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'actualizar':
            $result = GA_Metodos_Pago::save($metodo_id, $_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-metodos-pago&action=edit&id=' . $metodo_id . '&message=updated'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'eliminar':
            $result = GA_Metodos_Pago::delete($metodo_id);
            if ($result) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-metodos-pago&message=deleted'));
                exit;
            }
            $error_message = __('Error al eliminar el método de pago', 'gestionadmin-wolk');
            break;

        case 'toggle_activo':
            $metodo = GA_Metodos_Pago::get($metodo_id);
            if ($metodo) {
                $nuevo_estado = $metodo->activo ? 0 : 1;
                $result = GA_Metodos_Pago::save($metodo_id, array_merge((array)$metodo, array('activo' => $nuevo_estado)));
                if (!is_wp_error($result)) {
                    $msg = $nuevo_estado ? 'activated' : 'deactivated';
                    wp_redirect(admin_url('admin.php?page=gestionadmin-metodos-pago&message=' . $msg));
                    exit;
                }
                $error_message = $result->get_error_message();
            }
            break;
    }
}

// Mensajes de éxito
$messages = array(
    'created'     => __('Método de pago creado exitosamente.', 'gestionadmin-wolk'),
    'updated'     => __('Método de pago actualizado.', 'gestionadmin-wolk'),
    'deleted'     => __('Método de pago desactivado.', 'gestionadmin-wolk'),
    'activated'   => __('Método de pago activado.', 'gestionadmin-wolk'),
    'deactivated' => __('Método de pago desactivado.', 'gestionadmin-wolk'),
);

$success_message = '';
if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    $success_message = $messages[$_GET['message']];
}

// Obtener método si estamos editando
$metodo = null;
if ($action === 'edit' && $metodo_id > 0) {
    $metodo = GA_Metodos_Pago::get($metodo_id);
    if (!$metodo) {
        $action = 'list';
    }
}

// Obtener datos para dropdowns
$paises = GA_Paises::get_for_dropdown(true);
$tipos = GA_Metodos_Pago::get_tipos();
$tipos_cuenta = GA_Metodos_Pago::get_tipos_cuenta();
$redes_crypto = GA_Metodos_Pago::get_redes_crypto();

// Obtener resumen de saldos para el listado
$resumen_saldos = GA_Metodos_Pago::get_resumen_saldos();
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-bank"></span>
        <?php esc_html_e('Métodos de Pago', 'gestionadmin-wolk'); ?>
    </h1>

    <?php if ($action === 'list'): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-metodos-pago&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Nuevo Método', 'gestionadmin-wolk'); ?>
        </a>
    <?php else: ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-metodos-pago')); ?>" class="page-title-action">
            <?php esc_html_e('Volver al Listado', 'gestionadmin-wolk'); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php if (!empty($success_message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Tarjetas de resumen por país -->
        <?php if (!empty($resumen_saldos)): ?>
        <div class="ga-stats-cards">
            <?php foreach ($resumen_saldos as $resumen): ?>
                <div class="ga-stat-card <?php echo $resumen->cuentas_bajo_minimo > 0 ? 'ga-stat-warning' : 'ga-stat-success'; ?>">
                    <div class="ga-stat-number">
                        <?php echo esc_html($resumen->moneda); ?> <?php echo esc_html(number_format($resumen->saldo_total, 2)); ?>
                    </div>
                    <div class="ga-stat-label">
                        <?php echo esc_html($resumen->pais_nombre ?: $resumen->pais_codigo ?: __('Sin País', 'gestionadmin-wolk')); ?>
                        <br>
                        <small><?php echo esc_html($resumen->total_cuentas); ?> <?php esc_html_e('cuenta(s)', 'gestionadmin-wolk'); ?></small>
                        <?php if ($resumen->cuentas_bajo_minimo > 0): ?>
                            <br><small class="ga-alert-text"><?php echo esc_html($resumen->cuentas_bajo_minimo); ?> <?php esc_html_e('bajo mínimo', 'gestionadmin-wolk'); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="ga-filter-bar">
            <form method="get">
                <input type="hidden" name="page" value="gestionadmin-metodos-pago">

                <select name="tipo">
                    <option value=""><?php esc_html_e('Todos los tipos', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($tipos as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected(isset($_GET['tipo']) ? $_GET['tipo'] : '', $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="pais_codigo">
                    <option value=""><?php esc_html_e('Todos los países', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo esc_attr($pais->codigo_iso); ?>" <?php selected(isset($_GET['pais_codigo']) ? $_GET['pais_codigo'] : '', $pais->codigo_iso); ?>>
                            <?php echo esc_html($pais->nombre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="activo">
                    <option value=""><?php esc_html_e('Todos los estados', 'gestionadmin-wolk'); ?></option>
                    <option value="1" <?php selected(isset($_GET['activo']) ? $_GET['activo'] : '', '1'); ?>><?php esc_html_e('Activos', 'gestionadmin-wolk'); ?></option>
                    <option value="0" <?php selected(isset($_GET['activo']) ? $_GET['activo'] : '', '0'); ?>><?php esc_html_e('Inactivos', 'gestionadmin-wolk'); ?></option>
                </select>

                <button type="submit" class="button"><?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?></button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-metodos-pago')); ?>" class="button"><?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?></a>
            </form>
        </div>

        <?php
        // Obtener listado con filtros
        $filters = array();
        if (!empty($_GET['tipo'])) {
            $filters['tipo'] = sanitize_text_field($_GET['tipo']);
        }
        if (!empty($_GET['pais_codigo'])) {
            $filters['pais_codigo'] = sanitize_text_field($_GET['pais_codigo']);
        }
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filters['activo'] = absint($_GET['activo']);
        }

        $lista = GA_Metodos_Pago::get_all($filters);
        ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:120px;"><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                    <th style="width:100px;"><?php esc_html_e('País', 'gestionadmin-wolk'); ?></th>
                    <th style="width:80px;"><?php esc_html_e('Moneda', 'gestionadmin-wolk'); ?></th>
                    <th style="width:120px;"><?php esc_html_e('Saldo Actual', 'gestionadmin-wolk'); ?></th>
                    <th style="width:80px;"><?php esc_html_e('Uso', 'gestionadmin-wolk'); ?></th>
                    <th style="width:80px;"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                    <th style="width:120px;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista)): ?>
                    <tr>
                        <td colspan="8"><?php esc_html_e('No se encontraron métodos de pago.', 'gestionadmin-wolk'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lista as $item): ?>
                        <tr class="<?php echo $item->activo ? '' : 'ga-row-inactive'; ?>">
                            <td>
                                <span class="ga-tipo-badge ga-tipo-<?php echo esc_attr($item->tipo); ?>">
                                    <?php echo esc_html($tipos[$item->tipo] ?? $item->tipo); ?>
                                </span>
                                <?php if ($item->es_principal): ?>
                                    <span class="dashicons dashicons-star-filled ga-principal-star" title="<?php esc_attr_e('Principal', 'gestionadmin-wolk'); ?>"></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-metodos-pago&action=edit&id=' . $item->id)); ?>">
                                        <?php echo esc_html($item->nombre); ?>
                                    </a>
                                </strong>
                                <?php
                                // Mostrar info adicional según tipo
                                $info_extra = '';
                                switch ($item->tipo) {
                                    case 'transferencia':
                                        if ($item->banco_nombre) {
                                            $info_extra = $item->banco_nombre;
                                            if ($item->banco_numero_cuenta) {
                                                $info_extra .= ' - ***' . substr($item->banco_numero_cuenta, -4);
                                            }
                                        }
                                        break;
                                    case 'paypal':
                                    case 'wise':
                                    case 'stripe':
                                        $info_extra = $item->wallet_email;
                                        break;
                                    case 'binance':
                                    case 'crypto':
                                        if ($item->crypto_wallet_address) {
                                            $info_extra = substr($item->crypto_wallet_address, 0, 10) . '...' . substr($item->crypto_wallet_address, -6);
                                        }
                                        if ($item->crypto_red) {
                                            $info_extra .= ' (' . $item->crypto_red . ')';
                                        }
                                        break;
                                }
                                if ($info_extra):
                                ?>
                                    <br><small class="description"><?php echo esc_html($info_extra); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($item->pais_nombre ?: $item->pais_codigo ?: '-'); ?></td>
                            <td><strong><?php echo esc_html($item->moneda); ?></strong></td>
                            <td class="<?php echo ($item->saldo_actual < $item->saldo_minimo) ? 'ga-saldo-bajo' : ''; ?>">
                                <?php echo esc_html(number_format($item->saldo_actual, 2)); ?>
                                <?php if ($item->saldo_minimo > 0 && $item->saldo_actual < $item->saldo_minimo): ?>
                                    <span class="dashicons dashicons-warning" title="<?php esc_attr_e('Bajo mínimo', 'gestionadmin-wolk'); ?>"></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item->uso_pagos_proveedores): ?>
                                    <span class="ga-uso-badge ga-uso-pago" title="<?php esc_attr_e('Para pagos', 'gestionadmin-wolk'); ?>">P</span>
                                <?php endif; ?>
                                <?php if ($item->uso_cobros_clientes): ?>
                                    <span class="ga-uso-badge ga-uso-cobro" title="<?php esc_attr_e('Para cobros', 'gestionadmin-wolk'); ?>">C</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item->activo): ?>
                                    <span class="ga-estado-badge ga-estado-activo"><?php esc_html_e('Activo', 'gestionadmin-wolk'); ?></span>
                                <?php else: ?>
                                    <span class="ga-estado-badge ga-estado-inactivo"><?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-metodos-pago&action=edit&id=' . $item->id)); ?>" class="button button-small">
                                    <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                </a>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('ga_metodo_action', 'ga_metodo_nonce'); ?>
                                    <input type="hidden" name="metodo_action" value="toggle_activo">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($item->id); ?>">
                                    <button type="submit" class="button button-small" title="<?php echo $item->activo ? esc_attr__('Desactivar', 'gestionadmin-wolk') : esc_attr__('Activar', 'gestionadmin-wolk'); ?>">
                                        <span class="dashicons dashicons-<?php echo $item->activo ? 'hidden' : 'visibility'; ?>"></span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- Formulario Crear/Editar -->
        <div class="ga-form-wrap">
            <form method="post" class="ga-form" id="metodo-pago-form">
                <?php wp_nonce_field('ga_metodo_action', 'ga_metodo_nonce'); ?>
                <input type="hidden" name="metodo_action" value="<?php echo $metodo ? 'actualizar' : 'crear'; ?>">

                <div class="ga-form-columns">
                    <div class="ga-form-main">
                        <!-- Información Básica -->
                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Información Básica', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="tipo"><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?> <span class="required">*</span></label></th>
                                        <td>
                                            <select id="tipo" name="tipo" required>
                                                <option value=""><?php esc_html_e('-- Seleccionar --', 'gestionadmin-wolk'); ?></option>
                                                <?php foreach ($tipos as $key => $label): ?>
                                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($metodo ? $metodo->tipo : '', $key); ?>>
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="nombre"><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?> <span class="required">*</span></label></th>
                                        <td>
                                            <input type="text" id="nombre" name="nombre" class="regular-text" required
                                                   value="<?php echo $metodo ? esc_attr($metodo->nombre) : ''; ?>"
                                                   placeholder="<?php esc_attr_e('Ej: Bancolombia Principal', 'gestionadmin-wolk'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="pais_codigo"><?php esc_html_e('País', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="pais_codigo" name="pais_codigo">
                                                <option value=""><?php esc_html_e('-- Sin país específico --', 'gestionadmin-wolk'); ?></option>
                                                <?php foreach ($paises as $pais): ?>
                                                    <option value="<?php echo esc_attr($pais->codigo_iso); ?>" <?php selected($metodo ? $metodo->pais_codigo : '', $pais->codigo_iso); ?>>
                                                        <?php echo esc_html($pais->nombre); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="moneda"><?php esc_html_e('Moneda', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="moneda" name="moneda">
                                                <option value="USD" <?php selected($metodo ? $metodo->moneda : 'USD', 'USD'); ?>>USD - Dólar</option>
                                                <option value="COP" <?php selected($metodo ? $metodo->moneda : '', 'COP'); ?>>COP - Peso Colombiano</option>
                                                <option value="MXN" <?php selected($metodo ? $metodo->moneda : '', 'MXN'); ?>>MXN - Peso Mexicano</option>
                                                <option value="EUR" <?php selected($metodo ? $metodo->moneda : '', 'EUR'); ?>>EUR - Euro</option>
                                                <option value="CRC" <?php selected($metodo ? $metodo->moneda : '', 'CRC'); ?>>CRC - Colón Costarricense</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="descripcion"><?php esc_html_e('Notas Internas', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <textarea id="descripcion" name="descripcion" rows="2" class="large-text"><?php echo $metodo ? esc_textarea($metodo->descripcion) : ''; ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Datos Bancarios (solo para transferencia) -->
                        <div class="postbox ga-campos-transferencia" style="<?php echo (!$metodo || $metodo->tipo !== 'transferencia') ? 'display:none;' : ''; ?>">
                            <h2 class="hndle"><?php esc_html_e('Datos Bancarios', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="banco_nombre"><?php esc_html_e('Nombre del Banco', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_nombre" name="banco_nombre" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_nombre) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_tipo_cuenta"><?php esc_html_e('Tipo de Cuenta', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="banco_tipo_cuenta" name="banco_tipo_cuenta">
                                                <option value=""><?php esc_html_e('-- Seleccionar --', 'gestionadmin-wolk'); ?></option>
                                                <?php foreach ($tipos_cuenta as $key => $label): ?>
                                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($metodo ? $metodo->banco_tipo_cuenta : '', $key); ?>>
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_numero_cuenta"><?php esc_html_e('Número de Cuenta', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_numero_cuenta" name="banco_numero_cuenta" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_numero_cuenta) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_titular"><?php esc_html_e('Titular de la Cuenta', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_titular" name="banco_titular" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_titular) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_documento"><?php esc_html_e('Documento del Titular', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_documento" name="banco_documento" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_documento) : ''; ?>"
                                                   placeholder="<?php esc_attr_e('NIT, CC, RFC, etc.', 'gestionadmin-wolk'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_swift"><?php esc_html_e('SWIFT/BIC', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_swift" name="banco_swift" class="small-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_swift) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_iban"><?php esc_html_e('IBAN', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_iban" name="banco_iban" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_iban) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_routing"><?php esc_html_e('Routing Number (USA)', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_routing" name="banco_routing" class="small-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_routing) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="banco_clabe"><?php esc_html_e('CLABE (México)', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="banco_clabe" name="banco_clabe" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->banco_clabe) : ''; ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Datos Wallet (PayPal, Wise, Stripe) -->
                        <div class="postbox ga-campos-wallet" style="<?php echo (!$metodo || !in_array($metodo->tipo, array('paypal', 'wise', 'stripe'))) ? 'display:none;' : ''; ?>">
                            <h2 class="hndle"><?php esc_html_e('Datos de Wallet Digital', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="wallet_email"><?php esc_html_e('Email de la Cuenta', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="email" id="wallet_email" name="wallet_email" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->wallet_email) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="wallet_usuario"><?php esc_html_e('Usuario/Handle', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="wallet_usuario" name="wallet_usuario" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->wallet_usuario) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="wallet_account_id"><?php esc_html_e('ID de Cuenta', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="wallet_account_id" name="wallet_account_id" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->wallet_account_id) : ''; ?>"
                                                   placeholder="<?php esc_attr_e('ID interno de la plataforma', 'gestionadmin-wolk'); ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Datos Crypto (Binance, crypto wallet) -->
                        <div class="postbox ga-campos-crypto" style="<?php echo (!$metodo || !in_array($metodo->tipo, array('binance', 'crypto'))) ? 'display:none;' : ''; ?>">
                            <h2 class="hndle"><?php esc_html_e('Datos Crypto', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="crypto_red"><?php esc_html_e('Red/Blockchain', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="crypto_red" name="crypto_red">
                                                <option value=""><?php esc_html_e('-- Seleccionar --', 'gestionadmin-wolk'); ?></option>
                                                <?php foreach ($redes_crypto as $key => $label): ?>
                                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($metodo ? $metodo->crypto_red : '', $key); ?>>
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="crypto_wallet_address"><?php esc_html_e('Dirección de Wallet', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="crypto_wallet_address" name="crypto_wallet_address" class="large-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->crypto_wallet_address) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="crypto_token"><?php esc_html_e('Token', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="crypto_token" name="crypto_token" class="small-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->crypto_token) : ''; ?>"
                                                   placeholder="<?php esc_attr_e('USDT, USDC, BTC, etc.', 'gestionadmin-wolk'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="crypto_binance_id"><?php esc_html_e('Binance Pay ID', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="crypto_binance_id" name="crypto_binance_id" class="regular-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->crypto_binance_id) : ''; ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-sidebar">
                        <!-- Control Financiero -->
                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Control Financiero', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="saldo_actual"><?php esc_html_e('Saldo Actual', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="number" id="saldo_actual" name="saldo_actual" step="0.01" class="small-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->saldo_actual) : '0'; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="saldo_minimo"><?php esc_html_e('Saldo Mínimo', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="number" id="saldo_minimo" name="saldo_minimo" step="0.01" class="small-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->saldo_minimo) : '0'; ?>">
                                            <p class="description"><?php esc_html_e('Alerta si baja de este monto', 'gestionadmin-wolk'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="limite_diario"><?php esc_html_e('Límite Diario', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="number" id="limite_diario" name="limite_diario" step="0.01" class="small-text"
                                                   value="<?php echo $metodo && $metodo->limite_diario ? esc_attr($metodo->limite_diario) : ''; ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Configuración', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <p>
                                    <label>
                                        <input type="checkbox" name="uso_pagos_proveedores" value="1" <?php checked(!$metodo || $metodo->uso_pagos_proveedores); ?>>
                                        <?php esc_html_e('Usar para pagos a proveedores', 'gestionadmin-wolk'); ?>
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input type="checkbox" name="uso_cobros_clientes" value="1" <?php checked($metodo && $metodo->uso_cobros_clientes); ?>>
                                        <?php esc_html_e('Usar para cobros a clientes', 'gestionadmin-wolk'); ?>
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input type="checkbox" name="es_principal" value="1" <?php checked($metodo && $metodo->es_principal); ?>>
                                        <?php esc_html_e('Cuenta principal (por tipo/país)', 'gestionadmin-wolk'); ?>
                                    </label>
                                </p>
                                <hr>
                                <table class="form-table">
                                    <tr>
                                        <th><label for="orden_prioridad"><?php esc_html_e('Orden', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="number" id="orden_prioridad" name="orden_prioridad" min="0" class="small-text"
                                                   value="<?php echo $metodo ? esc_attr($metodo->orden_prioridad) : '0'; ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <label>
                                    <input type="checkbox" name="activo" value="1" <?php checked(!$metodo || $metodo->activo); ?>>
                                    <?php esc_html_e('Método activo', 'gestionadmin-wolk'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Solo los métodos activos aparecen en los selectores.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        </div>

                        <div class="ga-form-actions">
                            <button type="submit" class="button button-primary button-large">
                                <?php echo $metodo ? esc_html__('Actualizar Método', 'gestionadmin-wolk') : esc_html__('Crear Método', 'gestionadmin-wolk'); ?>
                            </button>

                            <?php if ($metodo): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-metodos-pago&action=new')); ?>" class="button">
                                    <?php esc_html_e('Crear Nuevo', 'gestionadmin-wolk'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
.ga-admin-wrap h1 .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    margin-right: 10px;
    vertical-align: middle;
}
.ga-stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.ga-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}
.ga-stat-card .ga-stat-number {
    font-size: 22px;
    font-weight: 600;
    color: #1d2327;
}
.ga-stat-card .ga-stat-label {
    color: #666;
    font-size: 12px;
}
.ga-stat-card.ga-stat-success { border-left: 4px solid #28a745; }
.ga-stat-card.ga-stat-warning { border-left: 4px solid #ffc107; }
.ga-stat-card.ga-stat-info { border-left: 4px solid #17a2b8; }
.ga-alert-text { color: #dc3545; }

.ga-filter-bar {
    background: #fff;
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}
.ga-filter-bar form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}
.ga-filter-bar select {
    min-width: 150px;
}

.ga-row-inactive {
    opacity: 0.6;
    background: #f9f9f9;
}

.ga-tipo-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}
.ga-tipo-transferencia { background: #cce5ff; color: #004085; }
.ga-tipo-paypal { background: #003087; color: #fff; }
.ga-tipo-wise { background: #9fe870; color: #000; }
.ga-tipo-binance { background: #f0b90b; color: #000; }
.ga-tipo-stripe { background: #635bff; color: #fff; }
.ga-tipo-crypto { background: #f7931a; color: #fff; }
.ga-tipo-efectivo { background: #28a745; color: #fff; }
.ga-tipo-otro { background: #6c757d; color: #fff; }

.ga-principal-star {
    color: #ffc107;
    font-size: 14px;
    vertical-align: middle;
}

.ga-uso-badge {
    display: inline-block;
    width: 20px;
    height: 20px;
    line-height: 20px;
    text-align: center;
    border-radius: 50%;
    font-size: 10px;
    font-weight: bold;
}
.ga-uso-pago { background: #dc3545; color: #fff; }
.ga-uso-cobro { background: #28a745; color: #fff; }

.ga-saldo-bajo {
    color: #dc3545;
    font-weight: 600;
}
.ga-saldo-bajo .dashicons {
    color: #ffc107;
    font-size: 16px;
    vertical-align: text-bottom;
}

.ga-estado-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}
.ga-estado-activo { background: #d4edda; color: #155724; }
.ga-estado-inactivo { background: #f8d7da; color: #721c24; }

/* Formulario */
.ga-form-wrap {
    margin-top: 20px;
}
.ga-form-columns {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
}
.ga-form-main .postbox,
.ga-form-sidebar .postbox {
    margin-bottom: 20px;
}
.ga-form-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}
.ga-form-actions .button {
    text-align: center;
}

@media screen and (max-width: 1200px) {
    .ga-form-columns {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tipoSelect = document.getElementById('tipo');
    var camposTransferencia = document.querySelector('.ga-campos-transferencia');
    var camposWallet = document.querySelector('.ga-campos-wallet');
    var camposCrypto = document.querySelector('.ga-campos-crypto');

    function toggleCampos() {
        var tipo = tipoSelect.value;

        // Ocultar todos
        if (camposTransferencia) camposTransferencia.style.display = 'none';
        if (camposWallet) camposWallet.style.display = 'none';
        if (camposCrypto) camposCrypto.style.display = 'none';

        // Mostrar según tipo
        switch (tipo) {
            case 'transferencia':
                if (camposTransferencia) camposTransferencia.style.display = 'block';
                break;
            case 'paypal':
            case 'wise':
            case 'stripe':
                if (camposWallet) camposWallet.style.display = 'block';
                break;
            case 'binance':
            case 'crypto':
                if (camposCrypto) camposCrypto.style.display = 'block';
                break;
        }
    }

    if (tipoSelect) {
        tipoSelect.addEventListener('change', toggleCampos);
        // Ejecutar al cargar
        toggleCampos();
    }
});
</script>
