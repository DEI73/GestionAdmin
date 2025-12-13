<?php
/**
 * Vista Admin: Solicitudes de Cobro
 *
 * Gestiona las solicitudes de pago de los proveedores.
 * Permite revisar, aprobar, rechazar y marcar como pagadas.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar módulos
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-solicitudes-cobro.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-comisiones.php';

$solicitudes_module = GA_Solicitudes_Cobro::get_instance();
$comisiones_module = GA_Comisiones::get_instance();

// Determinar acción
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$id = isset($_GET['id']) ? absint($_GET['id']) : 0;

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_solicitud_action'])) {
    if (!wp_verify_nonce($_POST['ga_nonce'], 'ga_solicitud_action')) {
        wp_die(__('Error de seguridad.', 'gestionadmin-wolk'));
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para esta acción.', 'gestionadmin-wolk'));
    }

    $solicitud_id = absint($_POST['solicitud_id']);
    $action_type = sanitize_text_field($_POST['ga_solicitud_action']);

    switch ($action_type) {
        case 'en_revision':
            $result = $solicitudes_module->poner_en_revision($solicitud_id);
            if ($result) {
                wp_redirect(add_query_arg(array('page' => 'ga-solicitudes-cobro', 'action' => 'ver', 'id' => $solicitud_id, 'message' => 'revision'), admin_url('admin.php')));
                exit;
            }
            break;

        case 'aprobar':
            $notas = isset($_POST['notas_revision']) ? sanitize_textarea_field($_POST['notas_revision']) : '';
            $result = $solicitudes_module->aprobar($solicitud_id, $notas);
            if ($result && !is_wp_error($result)) {
                wp_redirect(add_query_arg(array('page' => 'ga-solicitudes-cobro', 'action' => 'ver', 'id' => $solicitud_id, 'message' => 'aprobada'), admin_url('admin.php')));
                exit;
            }
            break;

        case 'rechazar':
            $notas = isset($_POST['notas_revision']) ? sanitize_textarea_field($_POST['notas_revision']) : '';
            $result = $solicitudes_module->rechazar($solicitud_id, $notas);
            if ($result && !is_wp_error($result)) {
                wp_redirect(add_query_arg(array('page' => 'ga-solicitudes-cobro', 'action' => 'ver', 'id' => $solicitud_id, 'message' => 'rechazada'), admin_url('admin.php')));
                exit;
            }
            break;

        case 'pagar':
            $comprobante = isset($_POST['comprobante_pago']) ? sanitize_text_field($_POST['comprobante_pago']) : '';
            $result = $solicitudes_module->marcar_pagada($solicitud_id, $comprobante);
            if ($result && !is_wp_error($result)) {
                wp_redirect(add_query_arg(array('page' => 'ga-solicitudes-cobro', 'action' => 'ver', 'id' => $solicitud_id, 'message' => 'pagada'), admin_url('admin.php')));
                exit;
            }
            break;
    }
}

// Obtener enums
$estados = GA_Solicitudes_Cobro::get_estados();
$metodos_pago = GA_Solicitudes_Cobro::get_metodos_pago();

// URL base
$base_url = admin_url('admin.php?page=ga-solicitudes-cobro');

// Mensajes de éxito
$messages = array(
    'revision'  => __('Solicitud puesta en revisión.', 'gestionadmin-wolk'),
    'aprobada'  => __('Solicitud aprobada exitosamente.', 'gestionadmin-wolk'),
    'rechazada' => __('Solicitud rechazada.', 'gestionadmin-wolk'),
    'pagada'    => __('Solicitud marcada como pagada.', 'gestionadmin-wolk'),
);

if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$_GET['message']]) . '</p></div>';
}

// =========================================================================
// VISTA: DETALLE DE SOLICITUD
// =========================================================================
if ($action === 'ver' && $id > 0) :
    $solicitud = $solicitudes_module->get($id);

    if (!$solicitud) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Solicitud no encontrada.', 'gestionadmin-wolk') . '</p></div>';
        return;
    }

    $detalle = $solicitudes_module->get_detalle($id);
    $badge_class = GA_Solicitudes_Cobro::get_estado_badge_class($solicitud->estado);
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <a href="<?php echo esc_url($base_url); ?>" class="ga-back-link">
            <span class="dashicons dashicons-arrow-left-alt"></span>
        </a>
        <?php printf(esc_html__('Solicitud %s', 'gestionadmin-wolk'), $solicitud->numero_solicitud); ?>
        <span class="ga-badge <?php echo esc_attr($badge_class); ?> ga-badge-large">
            <?php echo esc_html($estados[$solicitud->estado] ?? $solicitud->estado); ?>
        </span>
    </h1>
    <hr class="wp-header-end">

    <div class="ga-detail-layout">
        <!-- Información principal -->
        <div class="ga-detail-main">
            <!-- Card de información del solicitante -->
            <div class="ga-card">
                <h3 class="ga-card-title">
                    <span class="dashicons dashicons-businessman"></span>
                    <?php esc_html_e('Solicitante', 'gestionadmin-wolk'); ?>
                </h3>
                <div class="ga-card-content">
                    <div class="ga-info-row">
                        <span class="ga-info-label"><?php esc_html_e('Nombre:', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-info-value">
                            <?php echo esc_html($solicitud->aplicante_nombre . ' ' . $solicitud->aplicante_apellido); ?>
                        </span>
                    </div>
                    <div class="ga-info-row">
                        <span class="ga-info-label"><?php esc_html_e('Email:', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-info-value">
                            <a href="mailto:<?php echo esc_attr($solicitud->aplicante_email); ?>">
                                <?php echo esc_html($solicitud->aplicante_email); ?>
                            </a>
                        </span>
                    </div>
                    <div class="ga-info-row">
                        <span class="ga-info-label"><?php esc_html_e('Fecha Solicitud:', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-info-value">
                            <?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($solicitud->created_at))); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card de montos -->
            <div class="ga-card">
                <h3 class="ga-card-title">
                    <span class="dashicons dashicons-money-alt"></span>
                    <?php esc_html_e('Montos', 'gestionadmin-wolk'); ?>
                </h3>
                <div class="ga-card-content">
                    <div class="ga-amounts-grid">
                        <div class="ga-amount-box">
                            <span class="ga-amount-label"><?php esc_html_e('Disponible', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-amount-value">$<?php echo number_format($solicitud->monto_disponible, 2); ?></span>
                        </div>
                        <div class="ga-amount-box ga-amount-highlight">
                            <span class="ga-amount-label"><?php esc_html_e('Solicitado', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-amount-value">$<?php echo number_format($solicitud->monto_solicitado, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de método de pago -->
            <div class="ga-card">
                <h3 class="ga-card-title">
                    <span class="dashicons dashicons-bank"></span>
                    <?php esc_html_e('Método de Pago', 'gestionadmin-wolk'); ?>
                </h3>
                <div class="ga-card-content">
                    <div class="ga-info-row">
                        <span class="ga-info-label"><?php esc_html_e('Método:', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-info-value ga-badge ga-badge-outline">
                            <?php echo esc_html($metodos_pago[$solicitud->metodo_pago] ?? $solicitud->metodo_pago); ?>
                        </span>
                    </div>
                    <div class="ga-info-row">
                        <span class="ga-info-label"><?php esc_html_e('Moneda:', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-info-value"><?php echo esc_html($solicitud->moneda); ?></span>
                    </div>
                    <?php if (!empty($solicitud->datos_pago)) : ?>
                        <div class="ga-payment-details">
                            <h4><?php esc_html_e('Datos de Pago:', 'gestionadmin-wolk'); ?></h4>
                            <pre class="ga-json-display"><?php echo esc_html(json_encode($solicitud->datos_pago, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detalle de comisiones -->
            <div class="ga-card">
                <h3 class="ga-card-title">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php esc_html_e('Comisiones Incluidas', 'gestionadmin-wolk'); ?>
                </h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Orden', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Monto Original', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Monto Solicitado', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Ajuste', 'gestionadmin-wolk'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($detalle)) : ?>
                            <tr>
                                <td colspan="4" class="ga-no-items">
                                    <?php esc_html_e('No hay comisiones en esta solicitud.', 'gestionadmin-wolk'); ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($detalle as $item) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=ga-ordenes-trabajo&action=ver&id=' . $item->orden_id)); ?>">
                                            <?php echo esc_html($item->orden_codigo); ?>
                                        </a>
                                        <br>
                                        <small class="ga-text-muted"><?php echo esc_html(wp_trim_words($item->orden_titulo, 8)); ?></small>
                                    </td>
                                    <td>$<?php echo number_format($item->monto_original, 2); ?></td>
                                    <td><strong>$<?php echo number_format($item->monto_solicitado, 2); ?></strong></td>
                                    <td>
                                        <?php if ($item->tipo_ajuste !== 'NINGUNO') : ?>
                                            <span class="ga-badge ga-badge-warning">
                                                <?php echo esc_html($item->tipo_ajuste); ?>
                                            </span>
                                            <?php if (!empty($item->motivo_ajuste)) : ?>
                                                <br><small><?php echo esc_html($item->motivo_ajuste); ?></small>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="ga-text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="text-align: right;"><?php esc_html_e('Total Solicitado:', 'gestionadmin-wolk'); ?></th>
                            <th colspan="2"><strong>$<?php echo number_format($solicitud->monto_solicitado, 2); ?></strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if (!empty($solicitud->notas_solicitante)) : ?>
                <div class="ga-card">
                    <h3 class="ga-card-title">
                        <span class="dashicons dashicons-testimonial"></span>
                        <?php esc_html_e('Notas del Solicitante', 'gestionadmin-wolk'); ?>
                    </h3>
                    <div class="ga-card-content">
                        <p><?php echo esc_html($solicitud->notas_solicitante); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar con acciones -->
        <div class="ga-detail-sidebar">
            <!-- Card de acciones -->
            <div class="ga-card ga-card-actions">
                <h3 class="ga-card-title">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?>
                </h3>
                <div class="ga-card-content">
                    <?php if ($solicitud->estado === 'PENDIENTE') : ?>
                        <form method="post" class="ga-action-form">
                            <?php wp_nonce_field('ga_solicitud_action', 'ga_nonce'); ?>
                            <input type="hidden" name="solicitud_id" value="<?php echo esc_attr($solicitud->id); ?>">
                            <input type="hidden" name="ga_solicitud_action" value="en_revision">
                            <button type="submit" class="button button-secondary ga-btn-block">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php esc_html_e('Poner en Revisión', 'gestionadmin-wolk'); ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (in_array($solicitud->estado, array('PENDIENTE', 'EN_REVISION'))) : ?>
                        <hr>
                        <form method="post" class="ga-action-form">
                            <?php wp_nonce_field('ga_solicitud_action', 'ga_nonce'); ?>
                            <input type="hidden" name="solicitud_id" value="<?php echo esc_attr($solicitud->id); ?>">
                            <label for="notas_aprobacion"><?php esc_html_e('Notas:', 'gestionadmin-wolk'); ?></label>
                            <textarea name="notas_revision" id="notas_aprobacion" rows="3" class="widefat"></textarea>
                            <div class="ga-action-buttons">
                                <button type="submit" name="ga_solicitud_action" value="aprobar" class="button button-primary">
                                    <span class="dashicons dashicons-yes"></span>
                                    <?php esc_html_e('Aprobar', 'gestionadmin-wolk'); ?>
                                </button>
                                <button type="submit" name="ga_solicitud_action" value="rechazar" class="button button-secondary ga-btn-danger"
                                        onclick="return confirm('<?php esc_attr_e('¿Seguro que deseas rechazar esta solicitud?', 'gestionadmin-wolk'); ?>');">
                                    <span class="dashicons dashicons-no"></span>
                                    <?php esc_html_e('Rechazar', 'gestionadmin-wolk'); ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <?php if ($solicitud->estado === 'APROBADA') : ?>
                        <form method="post" class="ga-action-form">
                            <?php wp_nonce_field('ga_solicitud_action', 'ga_nonce'); ?>
                            <input type="hidden" name="solicitud_id" value="<?php echo esc_attr($solicitud->id); ?>">
                            <input type="hidden" name="ga_solicitud_action" value="pagar">
                            <label for="comprobante_pago"><?php esc_html_e('Comprobante/Referencia:', 'gestionadmin-wolk'); ?></label>
                            <input type="text" name="comprobante_pago" id="comprobante_pago" class="widefat"
                                   placeholder="<?php esc_attr_e('Número de transacción o URL', 'gestionadmin-wolk'); ?>">
                            <button type="submit" class="button button-primary ga-btn-block ga-btn-success">
                                <span class="dashicons dashicons-money-alt"></span>
                                <?php esc_html_e('Marcar como Pagada', 'gestionadmin-wolk'); ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($solicitud->estado === 'PAGADA') : ?>
                        <div class="ga-paid-info">
                            <span class="dashicons dashicons-yes-alt ga-icon-success"></span>
                            <p><?php esc_html_e('Esta solicitud ha sido pagada.', 'gestionadmin-wolk'); ?></p>
                            <?php if (!empty($solicitud->fecha_pago)) : ?>
                                <small><?php printf(esc_html__('Fecha: %s', 'gestionadmin-wolk'), date_i18n('d/m/Y H:i', strtotime($solicitud->fecha_pago))); ?></small>
                            <?php endif; ?>
                            <?php if (!empty($solicitud->comprobante_pago)) : ?>
                                <br><small><?php printf(esc_html__('Comprobante: %s', 'gestionadmin-wolk'), esc_html($solicitud->comprobante_pago)); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array($solicitud->estado, array('RECHAZADA', 'CANCELADA'))) : ?>
                        <div class="ga-rejected-info">
                            <span class="dashicons dashicons-warning ga-icon-danger"></span>
                            <p>
                                <?php
                                if ($solicitud->estado === 'RECHAZADA') {
                                    esc_html_e('Esta solicitud fue rechazada.', 'gestionadmin-wolk');
                                } else {
                                    esc_html_e('Esta solicitud fue cancelada por el solicitante.', 'gestionadmin-wolk');
                                }
                                ?>
                            </p>
                            <?php if (!empty($solicitud->notas_revision)) : ?>
                                <small><?php printf(esc_html__('Motivo: %s', 'gestionadmin-wolk'), esc_html($solicitud->notas_revision)); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historial de revisión -->
            <?php if (!empty($solicitud->revisado_por) || !empty($solicitud->fecha_revision)) : ?>
                <div class="ga-card">
                    <h3 class="ga-card-title">
                        <span class="dashicons dashicons-clock"></span>
                        <?php esc_html_e('Historial', 'gestionadmin-wolk'); ?>
                    </h3>
                    <div class="ga-card-content">
                        <?php if (!empty($solicitud->revisor_nombre)) : ?>
                            <p><strong><?php esc_html_e('Revisado por:', 'gestionadmin-wolk'); ?></strong> <?php echo esc_html($solicitud->revisor_nombre); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($solicitud->fecha_revision)) : ?>
                            <p><strong><?php esc_html_e('Fecha revisión:', 'gestionadmin-wolk'); ?></strong> <?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($solicitud->fecha_revision))); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($solicitud->notas_revision)) : ?>
                            <p><strong><?php esc_html_e('Notas:', 'gestionadmin-wolk'); ?></strong><br><?php echo esc_html($solicitud->notas_revision); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// =========================================================================
// VISTA: LISTADO DE SOLICITUDES
// =========================================================================
else :
    // Parámetros de filtrado
    $estado_filter = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
    $metodo_filter = isset($_GET['metodo_pago']) ? sanitize_text_field($_GET['metodo_pago']) : '';
    $busqueda      = isset($_GET['busqueda']) ? sanitize_text_field($_GET['busqueda']) : '';
    $fecha_desde   = isset($_GET['fecha_desde']) ? sanitize_text_field($_GET['fecha_desde']) : '';
    $fecha_hasta   = isset($_GET['fecha_hasta']) ? sanitize_text_field($_GET['fecha_hasta']) : '';
    $orderby       = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
    $order_dir     = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
    $paged         = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

    // Obtener datos
    $result = $solicitudes_module->get_all(array(
        'estado'      => $estado_filter,
        'metodo_pago' => $metodo_filter,
        'busqueda'    => $busqueda,
        'fecha_desde' => $fecha_desde,
        'fecha_hasta' => $fecha_hasta,
        'orderby'     => $orderby,
        'order'       => $order_dir,
        'page'        => $paged,
        'per_page'    => 20,
    ));

    $items = $result['items'];
    $total = $result['total'];
    $pages = $result['pages'];

    // Estadísticas
    $stats = $solicitudes_module->get_estadisticas();
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-clipboard"></span>
        <?php esc_html_e('Solicitudes de Cobro', 'gestionadmin-wolk'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Tarjetas de estadísticas -->
    <div class="ga-stats-cards">
        <div class="ga-stat-card ga-stat-pendiente">
            <div class="ga-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="ga-stat-content">
                <span class="ga-stat-number"><?php echo isset($stats['por_estado']['PENDIENTE']) ? $stats['por_estado']['PENDIENTE']['cantidad'] : 0; ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Pendientes', 'gestionadmin-wolk'); ?></span>
                <span class="ga-stat-amount">$<?php echo number_format($stats['pendiente'], 2); ?></span>
            </div>
        </div>

        <div class="ga-stat-card ga-stat-revision">
            <div class="ga-stat-icon">
                <span class="dashicons dashicons-visibility"></span>
            </div>
            <div class="ga-stat-content">
                <span class="ga-stat-number"><?php echo isset($stats['por_estado']['EN_REVISION']) ? $stats['por_estado']['EN_REVISION']['cantidad'] : 0; ?></span>
                <span class="ga-stat-label"><?php esc_html_e('En Revisión', 'gestionadmin-wolk'); ?></span>
                <span class="ga-stat-amount">$<?php echo number_format($stats['en_revision'], 2); ?></span>
            </div>
        </div>

        <div class="ga-stat-card ga-stat-aprobada">
            <div class="ga-stat-icon">
                <span class="dashicons dashicons-yes"></span>
            </div>
            <div class="ga-stat-content">
                <span class="ga-stat-number"><?php echo isset($stats['por_estado']['APROBADA']) ? $stats['por_estado']['APROBADA']['cantidad'] : 0; ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Por Pagar', 'gestionadmin-wolk'); ?></span>
                <span class="ga-stat-amount">$<?php echo number_format($stats['aprobada'], 2); ?></span>
            </div>
        </div>

        <div class="ga-stat-card ga-stat-pagada">
            <div class="ga-stat-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="ga-stat-content">
                <span class="ga-stat-number"><?php echo isset($stats['por_estado']['PAGADA']) ? $stats['por_estado']['PAGADA']['cantidad'] : 0; ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Pagadas', 'gestionadmin-wolk'); ?></span>
                <span class="ga-stat-amount">$<?php echo number_format($stats['pagada'], 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="ga-filters-bar">
        <form method="get" action="<?php echo esc_url($base_url); ?>">
            <input type="hidden" name="page" value="ga-solicitudes-cobro">

            <div class="ga-filter-group">
                <input type="text" name="busqueda" value="<?php echo esc_attr($busqueda); ?>"
                       placeholder="<?php esc_attr_e('Buscar...', 'gestionadmin-wolk'); ?>" class="ga-filter-search">
            </div>

            <div class="ga-filter-group">
                <select name="estado" class="ga-filter-select">
                    <option value=""><?php esc_html_e('Todos los estados', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($estados as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($estado_filter, $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ga-filter-group">
                <select name="metodo_pago" class="ga-filter-select">
                    <option value=""><?php esc_html_e('Todos los métodos', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($metodos_pago as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($metodo_filter, $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ga-filter-group">
                <input type="date" name="fecha_desde" value="<?php echo esc_attr($fecha_desde); ?>" class="ga-filter-date">
            </div>

            <div class="ga-filter-group">
                <input type="date" name="fecha_hasta" value="<?php echo esc_attr($fecha_hasta); ?>" class="ga-filter-date">
            </div>

            <div class="ga-filter-actions">
                <button type="submit" class="button">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?>
                </button>
                <a href="<?php echo esc_url($base_url); ?>" class="button">
                    <?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de solicitudes -->
    <table class="wp-list-table widefat fixed striped ga-table">
        <thead>
            <tr>
                <th scope="col" width="120"><?php esc_html_e('Número', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Solicitante', 'gestionadmin-wolk'); ?></th>
                <th scope="col" width="120"><?php esc_html_e('Método', 'gestionadmin-wolk'); ?></th>
                <th scope="col" width="100"><?php esc_html_e('Disponible', 'gestionadmin-wolk'); ?></th>
                <th scope="col" width="100"><?php esc_html_e('Solicitado', 'gestionadmin-wolk'); ?></th>
                <th scope="col" width="100"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                <th scope="col" width="130"><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
                <th scope="col" width="80"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)) : ?>
                <tr>
                    <td colspan="8" class="ga-no-items">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('No se encontraron solicitudes.', 'gestionadmin-wolk'); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($items as $solicitud) : ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(add_query_arg(array('action' => 'ver', 'id' => $solicitud->id), $base_url)); ?>">
                                    <?php echo esc_html($solicitud->numero_solicitud); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <?php echo esc_html($solicitud->aplicante_nombre . ' ' . $solicitud->aplicante_apellido); ?>
                            <br>
                            <small class="ga-text-muted"><?php echo esc_html($solicitud->aplicante_email); ?></small>
                        </td>
                        <td>
                            <span class="ga-badge ga-badge-outline">
                                <?php echo esc_html($metodos_pago[$solicitud->metodo_pago] ?? $solicitud->metodo_pago); ?>
                            </span>
                        </td>
                        <td>$<?php echo number_format($solicitud->monto_disponible, 2); ?></td>
                        <td><strong>$<?php echo number_format($solicitud->monto_solicitado, 2); ?></strong></td>
                        <td>
                            <?php $badge_class = GA_Solicitudes_Cobro::get_estado_badge_class($solicitud->estado); ?>
                            <span class="ga-badge <?php echo esc_attr($badge_class); ?>">
                                <?php echo esc_html($estados[$solicitud->estado] ?? $solicitud->estado); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n('d/m/Y', strtotime($solicitud->created_at))); ?>
                            <br>
                            <small class="ga-text-muted"><?php echo esc_html(date_i18n('H:i', strtotime($solicitud->created_at))); ?></small>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(array('action' => 'ver', 'id' => $solicitud->id), $base_url)); ?>"
                               class="button button-small" title="<?php esc_attr_e('Ver detalles', 'gestionadmin-wolk'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <?php if ($pages > 1) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(
                        esc_html(_n('%s elemento', '%s elementos', $total, 'gestionadmin-wolk')),
                        number_format_i18n($total)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    echo paginate_links(array(
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $pages,
                        'current'   => $paged,
                    ));
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<style>
/* Estilos comunes */
.ga-admin-wrap {
    margin-top: 20px;
}

.ga-back-link {
    text-decoration: none;
    margin-right: 10px;
}

.ga-back-link .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}

/* Stats Cards */
.ga-stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.ga-stat-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.ga-stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ga-stat-icon .dashicons {
    font-size: 22px;
    width: 22px;
    height: 22px;
    color: #fff;
}

.ga-stat-pendiente .ga-stat-icon { background: #dba617; }
.ga-stat-revision .ga-stat-icon { background: #2271b1; }
.ga-stat-aprobada .ga-stat-icon { background: #00a32a; }
.ga-stat-pagada .ga-stat-icon { background: #135e96; }

.ga-stat-number {
    display: block;
    font-size: 22px;
    font-weight: 600;
    line-height: 1.2;
}

.ga-stat-label {
    display: block;
    color: #646970;
    font-size: 12px;
}

.ga-stat-amount {
    display: block;
    font-size: 13px;
    color: #00a32a;
    font-weight: 500;
}

/* Filters */
.ga-filters-bar {
    background: #fff;
    border: 1px solid #c3c4c7;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.ga-filters-bar form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.ga-filter-search,
.ga-filter-select,
.ga-filter-date {
    min-width: 140px;
}

.ga-filter-actions {
    display: flex;
    gap: 5px;
}

/* Table */
.ga-table .ga-no-items {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
}

.ga-table .ga-no-items .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    display: block;
    margin: 0 auto 10px;
}

/* Badges */
.ga-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.ga-badge-large {
    font-size: 14px;
    padding: 5px 12px;
    vertical-align: middle;
    margin-left: 10px;
}

.ga-badge-success { background: #d7f5dc; color: #006908; }
.ga-badge-warning { background: #fff3cd; color: #856404; }
.ga-badge-primary { background: #cfe2ff; color: #084298; }
.ga-badge-info { background: #cff4fc; color: #055160; }
.ga-badge-danger { background: #f8d7da; color: #842029; }
.ga-badge-secondary { background: #e9ecef; color: #6c757d; }
.ga-badge-outline { background: transparent; border: 1px solid #c3c4c7; color: #50575e; }

.ga-text-muted { color: #646970; }

/* Detail Layout */
.ga-detail-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
    margin-top: 20px;
}

@media (max-width: 1200px) {
    .ga-detail-layout {
        grid-template-columns: 1fr;
    }
}

/* Cards */
.ga-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    margin-bottom: 20px;
}

.ga-card-title {
    margin: 0;
    padding: 12px 15px;
    background: #f6f7f7;
    border-bottom: 1px solid #c3c4c7;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ga-card-title .dashicons {
    color: #2271b1;
}

.ga-card-content {
    padding: 15px;
}

/* Info rows */
.ga-info-row {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f1;
}

.ga-info-row:last-child {
    border-bottom: none;
}

.ga-info-label {
    width: 140px;
    font-weight: 500;
    color: #50575e;
}

.ga-info-value {
    flex: 1;
}

/* Amounts */
.ga-amounts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.ga-amount-box {
    text-align: center;
    padding: 15px;
    background: #f6f7f7;
    border-radius: 4px;
}

.ga-amount-highlight {
    background: #d7f5dc;
}

.ga-amount-label {
    display: block;
    font-size: 12px;
    color: #646970;
    margin-bottom: 5px;
}

.ga-amount-value {
    display: block;
    font-size: 20px;
    font-weight: 600;
}

/* Payment details */
.ga-payment-details {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f1;
}

.ga-payment-details h4 {
    margin: 0 0 10px;
    font-size: 13px;
}

.ga-json-display {
    background: #f6f7f7;
    padding: 10px;
    border-radius: 4px;
    font-size: 12px;
    overflow-x: auto;
}

/* Action forms */
.ga-action-form {
    margin-bottom: 15px;
}

.ga-action-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.ga-action-form textarea,
.ga-action-form input[type="text"] {
    margin-bottom: 10px;
}

.ga-action-buttons {
    display: flex;
    gap: 10px;
}

.ga-btn-block {
    display: block;
    width: 100%;
    text-align: center;
}

.ga-btn-danger {
    color: #d63638 !important;
    border-color: #d63638 !important;
}

.ga-btn-success {
    background: #00a32a !important;
    border-color: #00a32a !important;
}

/* Status info boxes */
.ga-paid-info,
.ga-rejected-info {
    text-align: center;
    padding: 20px;
}

.ga-icon-success {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #00a32a;
}

.ga-icon-danger {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #d63638;
}
</style>
