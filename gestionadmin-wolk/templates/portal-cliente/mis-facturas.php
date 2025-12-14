<?php
/**
 * Template: Portal Cliente - Mis Facturas
 *
 * Centro de facturacion del cliente con:
 * - Resumen financiero (pendiente, pagado, vencido)
 * - Filtros por estado y periodo
 * - Lista de facturas con estados visuales
 * - Alertas de facturas vencidas y proximas a vencer
 * - Acceso a PDF de facturas
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalCliente
 * @since      1.3.0
 * @updated    1.11.0 - Mis Facturas funcional completo (Sprint C3)
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar autenticacion
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// Obtener usuario actual
$wp_user_id = get_current_user_id();
$wp_user = wp_get_current_user();

// Cargar modulo de clientes
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-clientes.php';

// Verificar que el usuario es un cliente registrado
$cliente = GA_Clientes::get_by_wp_id($wp_user_id);

// Si no es cliente, mostrar mensaje de acceso denegado
if (!$cliente) {
    get_header();
    GA_Theme_Integration::print_portal_styles();
    ?>
    <div class="ga-public-container ga-portal-cliente">
        <div class="ga-container">
            <div class="ga-access-denied">
                <div class="ga-access-denied-icon">
                    <span class="dashicons dashicons-lock"></span>
                </div>
                <h2><?php esc_html_e('Acceso Restringido', 'gestionadmin-wolk'); ?></h2>
                <p><?php esc_html_e('No tienes acceso al portal de clientes.', 'gestionadmin-wolk'); ?></p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="ga-btn ga-btn-primary">
                    <?php esc_html_e('Volver al Inicio', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </div>
    </div>
    <style>
    .ga-portal-cliente {
        min-height: 80vh;
        padding: 40px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ga-access-denied {
        background: #fff;
        border-radius: 16px;
        padding: 60px 40px;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
        max-width: 500px;
    }
    .ga-access-denied-icon {
        width: 88px;
        height: 88px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 28px;
        box-shadow: 0 10px 40px rgba(239,68,68,0.3);
    }
    .ga-access-denied-icon .dashicons {
        font-size: 44px;
        width: 44px;
        height: 44px;
        color: #fff;
    }
    .ga-access-denied h2 {
        font-size: 26px;
        margin: 0 0 12px 0;
        color: #0f172a;
        font-weight: 700;
    }
    .ga-access-denied p {
        color: #64748b;
        margin-bottom: 28px;
        font-size: 15px;
    }
    .ga-btn {
        display: inline-block;
        padding: 14px 28px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .ga-btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(16,185,129,0.4);
    }
    .ga-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16,185,129,0.5);
        color: #fff;
    }
    </style>
    <?php
    get_footer();
    exit;
}

global $wpdb;

// =========================================================================
// PROCESAR FILTROS
// =========================================================================

$filtro_estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
$filtro_periodo = isset($_GET['periodo']) ? sanitize_text_field($_GET['periodo']) : 'todo';

// Estados validos
$estados_validos = array('BORRADOR', 'ENVIADA', 'PARCIAL', 'PAGADA', 'VENCIDA', 'ANULADA');

// Validar estado
if (!empty($filtro_estado) && !in_array($filtro_estado, $estados_validos, true)) {
    $filtro_estado = '';
}

// Calcular fechas segun periodo
$fecha_inicio = '';
$fecha_fin = date('Y-m-d');

switch ($filtro_periodo) {
    case 'este_mes':
        $fecha_inicio = date('Y-m-01');
        break;
    case 'ultimos_3_meses':
        $fecha_inicio = date('Y-m-d', strtotime('-3 months'));
        break;
    case 'este_ano':
        $fecha_inicio = date('Y-01-01');
        break;
    case 'todo':
    default:
        $fecha_inicio = '';
        break;
}

// =========================================================================
// OBTENER FACTURAS DEL CLIENTE
// =========================================================================
$table_facturas = $wpdb->prefix . 'ga_facturas';
$table_proyectos = $wpdb->prefix . 'ga_proyectos';

// Construir query base
$sql = "SELECT f.*,
               p.codigo as proyecto_codigo,
               p.nombre as proyecto_nombre
        FROM {$table_facturas} f
        LEFT JOIN {$table_proyectos} p ON f.proyecto_id = p.id
        WHERE f.cliente_id = %d";

$params = array($cliente->id);

// Aplicar filtro de estado
if (!empty($filtro_estado)) {
    $sql .= " AND f.estado = %s";
    $params[] = $filtro_estado;
}

// Aplicar filtro de periodo
if (!empty($fecha_inicio)) {
    $sql .= " AND f.fecha_emision >= %s";
    $params[] = $fecha_inicio;
}

$sql .= " ORDER BY f.fecha_emision DESC, f.id DESC";

$facturas = $wpdb->get_results($wpdb->prepare($sql, $params));

// =========================================================================
// CALCULAR METRICAS FINANCIERAS
// =========================================================================

// Total pendiente (ENVIADA + PARCIAL + VENCIDA)
$total_pendiente = (float) $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(saldo_pendiente), 0)
     FROM {$table_facturas}
     WHERE cliente_id = %d AND estado IN ('ENVIADA', 'PARCIAL', 'VENCIDA')",
    $cliente->id
));

// Total pagado en el periodo seleccionado
$sql_pagado = "SELECT COALESCE(SUM(monto_pagado), 0)
               FROM {$table_facturas}
               WHERE cliente_id = %d AND estado IN ('PAGADA', 'PARCIAL')";
$params_pagado = array($cliente->id);

if (!empty($fecha_inicio)) {
    $sql_pagado .= " AND fecha_emision >= %s";
    $params_pagado[] = $fecha_inicio;
}

$total_pagado = (float) $wpdb->get_var($wpdb->prepare($sql_pagado, $params_pagado));

// Facturas vencidas (cantidad y monto)
$facturas_vencidas_count = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*)
     FROM {$table_facturas}
     WHERE cliente_id = %d AND estado = 'VENCIDA'",
    $cliente->id
));

$total_vencido = (float) $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(saldo_pendiente), 0)
     FROM {$table_facturas}
     WHERE cliente_id = %d AND estado = 'VENCIDA'",
    $cliente->id
));

// Contar facturas por estado (para badges)
$conteo_estados = $wpdb->get_results($wpdb->prepare(
    "SELECT estado, COUNT(*) as total
     FROM {$table_facturas}
     WHERE cliente_id = %d
     GROUP BY estado",
    $cliente->id
), OBJECT_K);

$total_facturas = 0;
foreach ($conteo_estados as $e) {
    $total_facturas += (int) $e->total;
}

// Fecha de hoy para calculos de vencimiento
$fecha_hoy = date('Y-m-d');

// URLs de navegacion
$url_dashboard = home_url('/cliente/');
$url_casos = home_url('/cliente/mis-casos/');
$url_facturas = home_url('/cliente/mis-facturas/');
$url_perfil = home_url('/cliente/mi-perfil/');

// Usar header del tema
get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-cliente ga-portal-facturas">
    <div class="ga-container">

        <!-- Header con navegacion -->
        <nav class="ga-portal-nav" role="navigation" aria-label="<?php esc_attr_e('Navegacion del portal', 'gestionadmin-wolk'); ?>">
            <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-dashboard"></span>
                <span class="ga-nav-text"><?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_casos); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-portfolio"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mis Casos', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_facturas); ?>" class="ga-nav-item active">
                <span class="dashicons dashicons-media-text"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mis Facturas', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-id"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></span>
            </a>
        </nav>

        <!-- Titulo de pagina -->
        <header class="ga-page-header">
            <div class="ga-page-title">
                <h1>
                    <span class="ga-icon-wrapper">
                        <span class="dashicons dashicons-media-text"></span>
                    </span>
                    <?php esc_html_e('Centro de Facturacion', 'gestionadmin-wolk'); ?>
                </h1>
                <p><?php esc_html_e('Gestiona y consulta todas tus facturas en un solo lugar', 'gestionadmin-wolk'); ?></p>
            </div>
        </header>

        <!-- Resumen Financiero -->
        <section class="ga-financial-summary" aria-label="<?php esc_attr_e('Resumen financiero', 'gestionadmin-wolk'); ?>">
            <div class="ga-summary-card ga-summary-pending">
                <div class="ga-summary-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="ga-summary-content">
                    <span class="ga-summary-label"><?php esc_html_e('Total Pendiente', 'gestionadmin-wolk'); ?></span>
                    <span class="ga-summary-amount">$<?php echo esc_html(number_format($total_pendiente, 2)); ?></span>
                    <span class="ga-summary-currency">USD</span>
                </div>
                <div class="ga-summary-decoration"></div>
            </div>

            <div class="ga-summary-card ga-summary-paid">
                <div class="ga-summary-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="ga-summary-content">
                    <span class="ga-summary-label">
                        <?php
                        switch ($filtro_periodo) {
                            case 'este_mes':
                                esc_html_e('Pagado Este Mes', 'gestionadmin-wolk');
                                break;
                            case 'ultimos_3_meses':
                                esc_html_e('Pagado (3 meses)', 'gestionadmin-wolk');
                                break;
                            case 'este_ano':
                                esc_html_e('Pagado Este Ano', 'gestionadmin-wolk');
                                break;
                            default:
                                esc_html_e('Total Pagado', 'gestionadmin-wolk');
                        }
                        ?>
                    </span>
                    <span class="ga-summary-amount">$<?php echo esc_html(number_format($total_pagado, 2)); ?></span>
                    <span class="ga-summary-currency">USD</span>
                </div>
                <div class="ga-summary-decoration"></div>
            </div>

            <div class="ga-summary-card ga-summary-overdue <?php echo $facturas_vencidas_count > 0 ? 'ga-has-overdue' : ''; ?>">
                <div class="ga-summary-icon">
                    <span class="dashicons dashicons-warning"></span>
                </div>
                <div class="ga-summary-content">
                    <span class="ga-summary-label"><?php esc_html_e('Facturas Vencidas', 'gestionadmin-wolk'); ?></span>
                    <span class="ga-summary-amount">
                        <?php if ($facturas_vencidas_count > 0): ?>
                            <?php echo esc_html($facturas_vencidas_count); ?>
                            <small>($<?php echo esc_html(number_format($total_vencido, 2)); ?>)</small>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    </span>
                    <span class="ga-summary-status">
                        <?php echo $facturas_vencidas_count > 0
                            ? esc_html__('Requiere atencion', 'gestionadmin-wolk')
                            : esc_html__('Al dia', 'gestionadmin-wolk'); ?>
                    </span>
                </div>
                <div class="ga-summary-decoration"></div>
            </div>
        </section>

        <!-- Alerta de facturas vencidas -->
        <?php if ($facturas_vencidas_count > 0): ?>
        <div class="ga-alert ga-alert-danger" role="alert">
            <div class="ga-alert-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="ga-alert-content">
                <strong><?php esc_html_e('Atencion Requerida', 'gestionadmin-wolk'); ?></strong>
                <p>
                    <?php
                    printf(
                        esc_html(_n(
                            'Tienes %d factura vencida por un total de $%s. Por favor, regulariza tu situacion lo antes posible.',
                            'Tienes %d facturas vencidas por un total de $%s. Por favor, regulariza tu situacion lo antes posible.',
                            $facturas_vencidas_count,
                            'gestionadmin-wolk'
                        )),
                        $facturas_vencidas_count,
                        number_format($total_vencido, 2)
                    );
                    ?>
                </p>
            </div>
            <a href="<?php echo esc_url(add_query_arg('estado', 'VENCIDA', $url_facturas)); ?>" class="ga-alert-action">
                <?php esc_html_e('Ver Vencidas', 'gestionadmin-wolk'); ?>
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="ga-filters-section">
            <form method="get" action="<?php echo esc_url($url_facturas); ?>" class="ga-filters-form">

                <!-- Filtro por estado -->
                <div class="ga-filter-group">
                    <label class="ga-filter-label"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></label>
                    <div class="ga-filter-tabs">
                        <a href="<?php echo esc_url(remove_query_arg('estado', add_query_arg('periodo', $filtro_periodo, $url_facturas))); ?>"
                           class="ga-filter-tab <?php echo empty($filtro_estado) ? 'active' : ''; ?>">
                            <?php esc_html_e('Todas', 'gestionadmin-wolk'); ?>
                            <span class="ga-tab-count"><?php echo esc_html($total_facturas); ?></span>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('estado' => 'ENVIADA', 'periodo' => $filtro_periodo), $url_facturas)); ?>"
                           class="ga-filter-tab ga-tab-pending <?php echo $filtro_estado === 'ENVIADA' ? 'active' : ''; ?>">
                            <?php esc_html_e('Pendientes', 'gestionadmin-wolk'); ?>
                            <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['ENVIADA']) ? $conteo_estados['ENVIADA']->total : 0); ?></span>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('estado' => 'PAGADA', 'periodo' => $filtro_periodo), $url_facturas)); ?>"
                           class="ga-filter-tab ga-tab-paid <?php echo $filtro_estado === 'PAGADA' ? 'active' : ''; ?>">
                            <?php esc_html_e('Pagadas', 'gestionadmin-wolk'); ?>
                            <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['PAGADA']) ? $conteo_estados['PAGADA']->total : 0); ?></span>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('estado' => 'VENCIDA', 'periodo' => $filtro_periodo), $url_facturas)); ?>"
                           class="ga-filter-tab ga-tab-overdue <?php echo $filtro_estado === 'VENCIDA' ? 'active' : ''; ?>">
                            <?php esc_html_e('Vencidas', 'gestionadmin-wolk'); ?>
                            <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['VENCIDA']) ? $conteo_estados['VENCIDA']->total : 0); ?></span>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('estado' => 'ANULADA', 'periodo' => $filtro_periodo), $url_facturas)); ?>"
                           class="ga-filter-tab ga-tab-cancelled <?php echo $filtro_estado === 'ANULADA' ? 'active' : ''; ?>">
                            <?php esc_html_e('Anuladas', 'gestionadmin-wolk'); ?>
                            <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['ANULADA']) ? $conteo_estados['ANULADA']->total : 0); ?></span>
                        </a>
                    </div>
                </div>

                <!-- Filtro por periodo -->
                <div class="ga-filter-group ga-filter-period">
                    <label class="ga-filter-label"><?php esc_html_e('Periodo', 'gestionadmin-wolk'); ?></label>
                    <div class="ga-select-wrapper">
                        <select name="periodo" onchange="this.form.submit()" class="ga-select">
                            <option value="todo" <?php selected($filtro_periodo, 'todo'); ?>>
                                <?php esc_html_e('Todo el historial', 'gestionadmin-wolk'); ?>
                            </option>
                            <option value="este_mes" <?php selected($filtro_periodo, 'este_mes'); ?>>
                                <?php esc_html_e('Este mes', 'gestionadmin-wolk'); ?>
                            </option>
                            <option value="ultimos_3_meses" <?php selected($filtro_periodo, 'ultimos_3_meses'); ?>>
                                <?php esc_html_e('Ultimos 3 meses', 'gestionadmin-wolk'); ?>
                            </option>
                            <option value="este_ano" <?php selected($filtro_periodo, 'este_ano'); ?>>
                                <?php esc_html_e('Este ano', 'gestionadmin-wolk'); ?>
                            </option>
                        </select>
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <?php if (!empty($filtro_estado)): ?>
                        <input type="hidden" name="estado" value="<?php echo esc_attr($filtro_estado); ?>">
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <!-- Lista de facturas -->
        <section class="ga-invoices-section" aria-label="<?php esc_attr_e('Lista de facturas', 'gestionadmin-wolk'); ?>">
            <?php if (empty($facturas)): ?>
                <div class="ga-empty-state">
                    <div class="ga-empty-illustration">
                        <div class="ga-empty-icon">
                            <span class="dashicons dashicons-media-text"></span>
                        </div>
                        <div class="ga-empty-circles">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                    <?php if (!empty($filtro_estado) || $filtro_periodo !== 'todo'): ?>
                        <h3><?php esc_html_e('No se encontraron facturas', 'gestionadmin-wolk'); ?></h3>
                        <p><?php esc_html_e('No hay facturas que coincidan con los filtros seleccionados.', 'gestionadmin-wolk'); ?></p>
                        <a href="<?php echo esc_url($url_facturas); ?>" class="ga-btn ga-btn-secondary">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php esc_html_e('Limpiar filtros', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php else: ?>
                        <h3><?php esc_html_e('Sin facturas registradas', 'gestionadmin-wolk'); ?></h3>
                        <p><?php esc_html_e('Aun no tienes facturas emitidas. Cuando se generen facturas para tus proyectos, apareceran aqui.', 'gestionadmin-wolk'); ?></p>
                        <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-btn ga-btn-secondary">
                            <span class="dashicons dashicons-arrow-left-alt"></span>
                            <?php esc_html_e('Volver al Dashboard', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="ga-invoices-grid">
                    <?php foreach ($facturas as $factura):
                        // Calcular dias para vencimiento
                        $dias_vencimiento = null;
                        $proxima_vencer = false;
                        $esta_vencida = ($factura->estado === 'VENCIDA');

                        if ($factura->fecha_vencimiento && in_array($factura->estado, array('ENVIADA', 'PARCIAL'))) {
                            $dias_vencimiento = (strtotime($factura->fecha_vencimiento) - strtotime($fecha_hoy)) / 86400;
                            $proxima_vencer = ($dias_vencimiento <= 5 && $dias_vencimiento >= 0);
                        }

                        // Determinar clase y etiqueta del estado
                        $estado_class = '';
                        $estado_label = '';
                        $estado_icon = '';
                        switch ($factura->estado) {
                            case 'BORRADOR':
                                $estado_class = 'ga-status-draft';
                                $estado_label = __('Borrador', 'gestionadmin-wolk');
                                $estado_icon = 'edit';
                                break;
                            case 'ENVIADA':
                                $estado_class = 'ga-status-pending';
                                $estado_label = __('Pendiente', 'gestionadmin-wolk');
                                $estado_icon = 'clock';
                                break;
                            case 'PARCIAL':
                                $estado_class = 'ga-status-partial';
                                $estado_label = __('Pago Parcial', 'gestionadmin-wolk');
                                $estado_icon = 'chart-pie';
                                break;
                            case 'PAGADA':
                                $estado_class = 'ga-status-paid';
                                $estado_label = __('Pagada', 'gestionadmin-wolk');
                                $estado_icon = 'yes-alt';
                                break;
                            case 'VENCIDA':
                                $estado_class = 'ga-status-overdue';
                                $estado_label = __('Vencida', 'gestionadmin-wolk');
                                $estado_icon = 'warning';
                                break;
                            case 'ANULADA':
                                $estado_class = 'ga-status-cancelled';
                                $estado_label = __('Anulada', 'gestionadmin-wolk');
                                $estado_icon = 'dismiss';
                                break;
                            default:
                                $estado_class = 'ga-status-default';
                                $estado_label = $factura->estado;
                                $estado_icon = 'marker';
                        }

                        // URL del PDF (preparada aunque no exista)
                        $pdf_url = !empty($factura->url_pdf) ? $factura->url_pdf : '#';
                        $has_pdf = !empty($factura->url_pdf);
                    ?>
                    <article class="ga-invoice-card <?php echo esc_attr($estado_class); ?> <?php echo $esta_vencida ? 'ga-invoice-overdue' : ''; ?> <?php echo $proxima_vencer ? 'ga-invoice-warning' : ''; ?>">

                        <?php if ($esta_vencida): ?>
                        <div class="ga-invoice-alert-banner">
                            <span class="dashicons dashicons-warning"></span>
                            <?php esc_html_e('FACTURA VENCIDA', 'gestionadmin-wolk'); ?>
                        </div>
                        <?php elseif ($proxima_vencer): ?>
                        <div class="ga-invoice-warning-banner">
                            <span class="dashicons dashicons-clock"></span>
                            <?php
                            printf(
                                esc_html(_n('Vence en %d dia', 'Vence en %d dias', max(0, floor($dias_vencimiento)), 'gestionadmin-wolk')),
                                max(0, floor($dias_vencimiento))
                            );
                            ?>
                        </div>
                        <?php endif; ?>

                        <div class="ga-invoice-header">
                            <div class="ga-invoice-number">
                                <span class="ga-invoice-label"><?php esc_html_e('Factura', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-invoice-code"><?php echo esc_html($factura->numero); ?></span>
                            </div>
                            <div class="ga-invoice-status <?php echo esc_attr($estado_class); ?>">
                                <span class="dashicons dashicons-<?php echo esc_attr($estado_icon); ?>"></span>
                                <?php echo esc_html($estado_label); ?>
                            </div>
                        </div>

                        <div class="ga-invoice-body">
                            <div class="ga-invoice-amount">
                                <span class="ga-amount-value">$<?php echo esc_html(number_format($factura->total_a_pagar, 2)); ?></span>
                                <span class="ga-amount-currency"><?php echo esc_html($factura->moneda ?: 'USD'); ?></span>
                            </div>

                            <?php if ($factura->saldo_pendiente > 0 && $factura->saldo_pendiente < $factura->total_a_pagar): ?>
                            <div class="ga-invoice-balance">
                                <span class="ga-balance-label"><?php esc_html_e('Saldo pendiente:', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-balance-value">$<?php echo esc_html(number_format($factura->saldo_pendiente, 2)); ?></span>
                            </div>
                            <?php endif; ?>

                            <div class="ga-invoice-dates">
                                <div class="ga-date-item">
                                    <span class="ga-date-label"><?php esc_html_e('Emision', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-date-value">
                                        <?php echo $factura->fecha_emision ? esc_html(date_i18n('d M Y', strtotime($factura->fecha_emision))) : '-'; ?>
                                    </span>
                                </div>
                                <div class="ga-date-item <?php echo $esta_vencida ? 'ga-date-overdue' : ''; ?>">
                                    <span class="ga-date-label"><?php esc_html_e('Vencimiento', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-date-value">
                                        <?php echo $factura->fecha_vencimiento ? esc_html(date_i18n('d M Y', strtotime($factura->fecha_vencimiento))) : '-'; ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($factura->proyecto_codigo)): ?>
                            <div class="ga-invoice-project">
                                <span class="dashicons dashicons-portfolio"></span>
                                <span class="ga-project-code"><?php echo esc_html($factura->proyecto_codigo); ?></span>
                                <span class="ga-project-name"><?php echo esc_html($factura->proyecto_nombre); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="ga-invoice-footer">
                            <?php if ($has_pdf): ?>
                            <a href="<?php echo esc_url($pdf_url); ?>" class="ga-invoice-btn ga-btn-pdf" target="_blank" rel="noopener">
                                <span class="dashicons dashicons-pdf"></span>
                                <?php esc_html_e('Ver PDF', 'gestionadmin-wolk'); ?>
                            </a>
                            <?php else: ?>
                            <span class="ga-invoice-btn ga-btn-pdf-disabled" title="<?php esc_attr_e('PDF no disponible', 'gestionadmin-wolk'); ?>">
                                <span class="dashicons dashicons-pdf"></span>
                                <?php esc_html_e('PDF', 'gestionadmin-wolk'); ?>
                            </span>
                            <?php endif; ?>

                            <?php if (in_array($factura->estado, array('ENVIADA', 'PARCIAL', 'VENCIDA'))): ?>
                            <a href="<?php echo esc_url(add_query_arg('factura', $factura->id, home_url('/cliente/pagar/'))); ?>" class="ga-invoice-btn ga-btn-pay">
                                <span class="dashicons dashicons-money-alt"></span>
                                <?php esc_html_e('Pagar', 'gestionadmin-wolk'); ?>
                            </a>
                            <?php endif; ?>
                        </div>

                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Footer -->
        <footer class="ga-portal-footer">
            <div class="ga-footer-content">
                <p>
                    <?php esc_html_e('Desarrollado por', 'gestionadmin-wolk'); ?>
                    <a href="https://wolksoftcr.com" target="_blank" rel="noopener">Wolksoftcr.com</a>
                </p>
            </div>
        </footer>

    </div>
</div>

<style>
/* =========================================================================
   PORTAL CLIENTE - MIS FACTURAS
   Enterprise-Grade Professional Design System
   ========================================================================= */

:root {
    --ga-primary: #10b981;
    --ga-primary-dark: #059669;
    --ga-primary-light: #d1fae5;
    --ga-secondary: #6366f1;
    --ga-warning: #f59e0b;
    --ga-warning-light: #fef3c7;
    --ga-danger: #ef4444;
    --ga-danger-light: #fef2f2;
    --ga-success: #22c55e;
    --ga-success-light: #dcfce7;
    --ga-neutral-50: #f8fafc;
    --ga-neutral-100: #f1f5f9;
    --ga-neutral-200: #e2e8f0;
    --ga-neutral-300: #cbd5e1;
    --ga-neutral-400: #94a3b8;
    --ga-neutral-500: #64748b;
    --ga-neutral-600: #475569;
    --ga-neutral-700: #334155;
    --ga-neutral-800: #1e293b;
    --ga-neutral-900: #0f172a;
    --ga-shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
    --ga-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    --ga-shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    --ga-shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    --ga-shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.25);
    --ga-radius-sm: 6px;
    --ga-radius: 10px;
    --ga-radius-lg: 14px;
    --ga-radius-xl: 20px;
}

.ga-portal-facturas {
    min-height: 100vh;
    padding: 28px 24px;
    background: linear-gradient(180deg, var(--ga-neutral-50) 0%, var(--ga-neutral-100) 100%);
}

.ga-container {
    max-width: 1280px;
    margin: 0 auto;
}

/* =========================================================================
   NAVEGACION PROFESIONAL
   ========================================================================= */

.ga-portal-nav {
    display: flex;
    gap: 6px;
    margin-bottom: 28px;
    background: #fff;
    padding: 10px 14px;
    border-radius: var(--ga-radius-lg);
    box-shadow: var(--ga-shadow);
    flex-wrap: wrap;
    border: 1px solid var(--ga-neutral-200);
}

.ga-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: var(--ga-radius);
    text-decoration: none;
    color: var(--ga-neutral-500);
    font-weight: 500;
    font-size: 14px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.ga-nav-item::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    opacity: 0;
    transition: opacity 0.25s;
}

.ga-nav-item:hover {
    background: var(--ga-neutral-100);
    color: var(--ga-neutral-800);
}

.ga-nav-item.active {
    color: #fff;
    box-shadow: 0 4px 14px rgba(16,185,129,0.35);
}

.ga-nav-item.active::before {
    opacity: 1;
}

.ga-nav-item .dashicons,
.ga-nav-item .ga-nav-text {
    position: relative;
    z-index: 1;
}

.ga-nav-item .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* =========================================================================
   HEADER DE PAGINA
   ========================================================================= */

.ga-page-header {
    margin-bottom: 28px;
    padding: 32px 36px;
    background: linear-gradient(135deg, #fff 0%, var(--ga-neutral-50) 100%);
    border-radius: var(--ga-radius-xl);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
}

.ga-page-title h1 {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--ga-neutral-900);
    letter-spacing: -0.5px;
}

.ga-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    border-radius: var(--ga-radius-lg);
    box-shadow: 0 8px 24px rgba(16,185,129,0.3);
}

.ga-icon-wrapper .dashicons {
    font-size: 26px;
    width: 26px;
    height: 26px;
    color: #fff;
}

.ga-page-title p {
    margin: 0;
    color: var(--ga-neutral-500);
    font-size: 15px;
    padding-left: 68px;
}

/* =========================================================================
   RESUMEN FINANCIERO
   ========================================================================= */

.ga-financial-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 28px;
}

.ga-summary-card {
    position: relative;
    background: #fff;
    border-radius: var(--ga-radius-xl);
    padding: 28px;
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ga-summary-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--ga-shadow-lg);
}

.ga-summary-decoration {
    position: absolute;
    top: 0;
    right: 0;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    opacity: 0.08;
    transform: translate(30%, -30%);
}

.ga-summary-pending .ga-summary-decoration {
    background: var(--ga-warning);
}

.ga-summary-paid .ga-summary-decoration {
    background: var(--ga-success);
}

.ga-summary-overdue .ga-summary-decoration {
    background: var(--ga-neutral-400);
}

.ga-summary-overdue.ga-has-overdue .ga-summary-decoration {
    background: var(--ga-danger);
}

.ga-summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: var(--ga-radius-lg);
    margin-bottom: 20px;
}

.ga-summary-pending .ga-summary-icon {
    background: linear-gradient(135deg, var(--ga-warning) 0%, #d97706 100%);
    box-shadow: 0 8px 24px rgba(245,158,11,0.3);
}

.ga-summary-paid .ga-summary-icon {
    background: linear-gradient(135deg, var(--ga-success) 0%, #16a34a 100%);
    box-shadow: 0 8px 24px rgba(34,197,94,0.3);
}

.ga-summary-overdue .ga-summary-icon {
    background: linear-gradient(135deg, var(--ga-neutral-400) 0%, var(--ga-neutral-500) 100%);
    box-shadow: 0 8px 24px rgba(148,163,184,0.3);
}

.ga-summary-overdue.ga-has-overdue .ga-summary-icon {
    background: linear-gradient(135deg, var(--ga-danger) 0%, #dc2626 100%);
    box-shadow: 0 8px 24px rgba(239,68,68,0.3);
}

.ga-summary-icon .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    color: #fff;
}

.ga-summary-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--ga-neutral-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.ga-summary-amount {
    display: block;
    font-size: 32px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    line-height: 1.1;
    margin-bottom: 6px;
    letter-spacing: -1px;
}

.ga-summary-amount small {
    font-size: 16px;
    font-weight: 600;
    color: var(--ga-neutral-500);
}

.ga-summary-currency {
    font-size: 13px;
    font-weight: 600;
    color: var(--ga-neutral-400);
}

.ga-summary-status {
    font-size: 13px;
    font-weight: 600;
    color: var(--ga-neutral-500);
}

.ga-summary-overdue.ga-has-overdue .ga-summary-status {
    color: var(--ga-danger);
}

/* =========================================================================
   ALERTA DE VENCIMIENTO
   ========================================================================= */

.ga-alert {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
    border-radius: var(--ga-radius-lg);
    margin-bottom: 28px;
    border: 1px solid;
}

.ga-alert-danger {
    background: linear-gradient(135deg, var(--ga-danger-light) 0%, #fff 100%);
    border-color: #fecaca;
}

.ga-alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: var(--ga-danger);
    border-radius: var(--ga-radius);
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(239,68,68,0.35);
}

.ga-alert-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #fff;
}

.ga-alert-content {
    flex: 1;
}

.ga-alert-content strong {
    display: block;
    color: #991b1b;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 4px;
}

.ga-alert-content p {
    margin: 0;
    color: #b91c1c;
    font-size: 14px;
}

.ga-alert-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 12px 20px;
    background: var(--ga-danger);
    color: #fff;
    border-radius: var(--ga-radius);
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.25s;
    box-shadow: 0 4px 14px rgba(239,68,68,0.35);
    white-space: nowrap;
}

.ga-alert-action:hover {
    background: #dc2626;
    transform: translateX(4px);
    color: #fff;
}

.ga-alert-action .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* =========================================================================
   FILTROS
   ========================================================================= */

.ga-filters-section {
    background: #fff;
    border-radius: var(--ga-radius-xl);
    padding: 24px;
    margin-bottom: 28px;
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
}

.ga-filters-form {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 24px;
    flex-wrap: wrap;
}

.ga-filter-group {
    flex: 1;
}

.ga-filter-period {
    flex: 0 0 220px;
}

.ga-filter-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--ga-neutral-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.ga-filter-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.ga-filter-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: var(--ga-radius);
    text-decoration: none;
    color: var(--ga-neutral-600);
    font-size: 13px;
    font-weight: 500;
    transition: all 0.25s;
    background: var(--ga-neutral-100);
    border: 1px solid transparent;
}

.ga-filter-tab:hover {
    background: var(--ga-neutral-200);
    color: var(--ga-neutral-800);
}

.ga-filter-tab.active {
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(16,185,129,0.35);
}

.ga-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 22px;
    padding: 0 8px;
    background: rgba(0,0,0,0.08);
    border-radius: 11px;
    font-size: 11px;
    font-weight: 600;
}

.ga-filter-tab.active .ga-tab-count {
    background: rgba(255,255,255,0.25);
}

/* Colores especificos para tabs */
.ga-tab-pending.active {
    background: linear-gradient(135deg, var(--ga-warning) 0%, #d97706 100%);
    box-shadow: 0 4px 14px rgba(245,158,11,0.35);
}

.ga-tab-paid.active {
    background: linear-gradient(135deg, var(--ga-success) 0%, #16a34a 100%);
    box-shadow: 0 4px 14px rgba(34,197,94,0.35);
}

.ga-tab-overdue.active {
    background: linear-gradient(135deg, var(--ga-danger) 0%, #dc2626 100%);
    box-shadow: 0 4px 14px rgba(239,68,68,0.35);
}

.ga-tab-cancelled.active {
    background: linear-gradient(135deg, var(--ga-neutral-500) 0%, var(--ga-neutral-600) 100%);
    box-shadow: 0 4px 14px rgba(100,116,139,0.35);
}

/* Select de periodo */
.ga-select-wrapper {
    position: relative;
}

.ga-select {
    width: 100%;
    padding: 12px 40px 12px 16px;
    font-size: 14px;
    font-weight: 500;
    color: var(--ga-neutral-700);
    background: var(--ga-neutral-100);
    border: 1px solid var(--ga-neutral-200);
    border-radius: var(--ga-radius);
    appearance: none;
    cursor: pointer;
    transition: all 0.25s;
}

.ga-select:hover {
    background: var(--ga-neutral-50);
    border-color: var(--ga-neutral-300);
}

.ga-select:focus {
    outline: none;
    border-color: var(--ga-primary);
    box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
}

.ga-select-wrapper .dashicons {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: var(--ga-neutral-400);
    pointer-events: none;
}

/* =========================================================================
   GRID DE FACTURAS
   ========================================================================= */

.ga-invoices-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 24px;
}

/* =========================================================================
   TARJETA DE FACTURA
   ========================================================================= */

.ga-invoice-card {
    position: relative;
    background: #fff;
    border-radius: var(--ga-radius-xl);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ga-invoice-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--ga-shadow-lg);
}

/* Banner de alerta */
.ga-invoice-alert-banner,
.ga-invoice-warning-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ga-invoice-alert-banner {
    background: linear-gradient(135deg, var(--ga-danger) 0%, #dc2626 100%);
    color: #fff;
}

.ga-invoice-warning-banner {
    background: linear-gradient(135deg, var(--ga-warning) 0%, #d97706 100%);
    color: #fff;
}

.ga-invoice-alert-banner .dashicons,
.ga-invoice-warning-banner .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

/* Factura vencida */
.ga-invoice-overdue {
    border-color: #fecaca;
    background: linear-gradient(135deg, #fff 0%, var(--ga-danger-light) 100%);
}

/* Factura proxima a vencer */
.ga-invoice-warning {
    border-color: #fde68a;
    background: linear-gradient(135deg, #fff 0%, var(--ga-warning-light) 100%);
}

/* Header de factura */
.ga-invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px 24px 0;
}

.ga-invoice-number {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ga-invoice-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ga-invoice-code {
    font-size: 18px;
    font-weight: 700;
    color: var(--ga-neutral-900);
    font-family: 'SF Mono', 'Consolas', monospace;
}

.ga-invoice-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ga-invoice-status .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

/* Colores de estado */
.ga-status-draft {
    background: var(--ga-neutral-100);
    color: var(--ga-neutral-600);
}

.ga-status-pending {
    background: var(--ga-warning-light);
    color: #b45309;
}

.ga-status-partial {
    background: #e0e7ff;
    color: #4338ca;
}

.ga-status-paid {
    background: var(--ga-success-light);
    color: #15803d;
}

.ga-status-overdue {
    background: var(--ga-danger-light);
    color: #dc2626;
}

.ga-status-cancelled {
    background: var(--ga-neutral-200);
    color: var(--ga-neutral-500);
}

/* Body de factura */
.ga-invoice-body {
    padding: 20px 24px;
}

.ga-invoice-amount {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 16px;
}

.ga-amount-value {
    font-size: 36px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    letter-spacing: -1px;
    line-height: 1;
}

.ga-amount-currency {
    font-size: 14px;
    font-weight: 600;
    color: var(--ga-neutral-400);
}

.ga-invoice-balance {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: var(--ga-warning-light);
    border-radius: var(--ga-radius);
    margin-bottom: 16px;
    font-size: 13px;
}

.ga-balance-label {
    color: #92400e;
    font-weight: 500;
}

.ga-balance-value {
    color: #b45309;
    font-weight: 700;
}

.ga-invoice-dates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
    padding: 16px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius);
}

.ga-date-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ga-date-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ga-date-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--ga-neutral-700);
}

.ga-date-overdue .ga-date-value {
    color: var(--ga-danger);
}

.ga-invoice-project {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: var(--ga-primary-light);
    border-radius: var(--ga-radius);
    font-size: 13px;
}

.ga-invoice-project .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: var(--ga-primary-dark);
}

.ga-project-code {
    font-weight: 700;
    color: var(--ga-primary-dark);
    font-family: 'SF Mono', 'Consolas', monospace;
}

.ga-project-name {
    color: var(--ga-neutral-600);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Footer de factura */
.ga-invoice-footer {
    display: flex;
    gap: 10px;
    padding: 16px 24px 24px;
    border-top: 1px solid var(--ga-neutral-100);
}

.ga-invoice-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    border-radius: var(--ga-radius);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s;
}

.ga-invoice-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.ga-btn-pdf {
    background: var(--ga-neutral-100);
    color: var(--ga-neutral-700);
    border: 1px solid var(--ga-neutral-200);
}

.ga-btn-pdf:hover {
    background: var(--ga-neutral-200);
    color: var(--ga-neutral-800);
}

.ga-btn-pdf-disabled {
    background: var(--ga-neutral-50);
    color: var(--ga-neutral-400);
    border: 1px solid var(--ga-neutral-200);
    cursor: not-allowed;
}

.ga-btn-pay {
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(16,185,129,0.35);
}

.ga-btn-pay:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16,185,129,0.45);
    color: #fff;
}

/* =========================================================================
   ESTADO VACIO
   ========================================================================= */

.ga-empty-state {
    text-align: center;
    padding: 80px 40px;
    background: #fff;
    border-radius: var(--ga-radius-xl);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
}

.ga-empty-illustration {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 32px;
}

.ga-empty-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--ga-neutral-100) 0%, var(--ga-neutral-200) 100%);
    border-radius: 50%;
    z-index: 2;
}

.ga-empty-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: var(--ga-neutral-400);
}

.ga-empty-circles {
    position: absolute;
    inset: -20px;
}

.ga-empty-circles span {
    position: absolute;
    border-radius: 50%;
    border: 2px dashed var(--ga-neutral-200);
    animation: pulse 3s ease-in-out infinite;
}

.ga-empty-circles span:nth-child(1) {
    inset: 0;
    animation-delay: 0s;
}

.ga-empty-circles span:nth-child(2) {
    inset: -15px;
    animation-delay: 0.5s;
}

.ga-empty-circles span:nth-child(3) {
    inset: -30px;
    animation-delay: 1s;
}

@keyframes pulse {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.02); }
}

.ga-empty-state h3 {
    margin: 0 0 12px 0;
    font-size: 22px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-empty-state p {
    margin: 0 0 28px 0;
    color: var(--ga-neutral-500);
    font-size: 15px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.ga-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    background: var(--ga-neutral-100);
    color: var(--ga-neutral-700);
    border-radius: var(--ga-radius);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.25s;
    border: 1px solid var(--ga-neutral-200);
}

.ga-btn-secondary:hover {
    background: var(--ga-neutral-200);
    color: var(--ga-neutral-800);
}

.ga-btn-secondary .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* =========================================================================
   FOOTER
   ========================================================================= */

.ga-portal-footer {
    text-align: center;
    padding: 32px 20px;
    margin-top: 20px;
}

.ga-footer-content p {
    margin: 0;
    color: var(--ga-neutral-400);
    font-size: 13px;
}

.ga-portal-footer a {
    color: var(--ga-primary);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.ga-portal-footer a:hover {
    color: var(--ga-primary-dark);
}

/* =========================================================================
   RESPONSIVE - TABLET
   ========================================================================= */

@media (max-width: 1024px) {
    .ga-financial-summary {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .ga-summary-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 24px;
    }

    .ga-summary-icon {
        margin-bottom: 0;
    }

    .ga-invoices-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .ga-portal-facturas {
        padding: 20px 16px;
    }

    .ga-portal-nav {
        gap: 4px;
        padding: 8px;
    }

    .ga-nav-item {
        padding: 10px 14px;
        font-size: 13px;
    }

    .ga-nav-text {
        display: none;
    }

    .ga-nav-item .dashicons {
        font-size: 20px;
        width: 20px;
        height: 20px;
    }

    .ga-page-header {
        padding: 24px 20px;
    }

    .ga-page-title h1 {
        font-size: 22px;
        gap: 12px;
    }

    .ga-icon-wrapper {
        width: 44px;
        height: 44px;
    }

    .ga-icon-wrapper .dashicons {
        font-size: 22px;
        width: 22px;
        height: 22px;
    }

    .ga-page-title p {
        padding-left: 56px;
        font-size: 14px;
    }

    .ga-summary-amount {
        font-size: 26px;
    }

    .ga-alert {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }

    .ga-alert-action {
        width: 100%;
        justify-content: center;
    }

    .ga-filters-form {
        flex-direction: column;
        align-items: stretch;
    }

    .ga-filter-period {
        flex: 1;
    }

    .ga-filter-tabs {
        justify-content: flex-start;
        overflow-x: auto;
        padding-bottom: 8px;
        -webkit-overflow-scrolling: touch;
    }

    .ga-filter-tab {
        flex-shrink: 0;
        padding: 8px 12px;
        font-size: 12px;
    }

    .ga-invoice-header {
        padding: 20px 20px 0;
    }

    .ga-invoice-body {
        padding: 16px 20px;
    }

    .ga-invoice-footer {
        padding: 16px 20px 20px;
    }

    .ga-amount-value {
        font-size: 28px;
    }

    .ga-invoice-dates {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .ga-empty-state {
        padding: 60px 24px;
    }

    .ga-empty-illustration {
        width: 100px;
        height: 100px;
    }

    .ga-empty-icon .dashicons {
        font-size: 40px;
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .ga-invoice-card {
        border-radius: var(--ga-radius-lg);
    }

    .ga-invoice-code {
        font-size: 16px;
    }

    .ga-invoice-status {
        padding: 6px 10px;
        font-size: 10px;
    }

    .ga-amount-value {
        font-size: 24px;
    }

    .ga-invoice-btn {
        padding: 10px 12px;
        font-size: 12px;
    }
}
</style>

<?php get_footer(); ?>
