<?php
/**
 * Template: Portal Cliente - Dashboard
 *
 * Dashboard principal del cliente con resumen de:
 * - Casos activos y su estado
 * - Proyectos en curso con avance
 * - Facturas pendientes y vencidas
 * - Accesos rapidos a otras secciones
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalCliente
 * @since      1.3.0
 * @updated    1.11.0 - Dashboard funcional completo (Sprint C1)
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
                <p><?php esc_html_e('No tienes acceso al portal de clientes. Si crees que esto es un error, por favor contacta con soporte.', 'gestionadmin-wolk'); ?></p>
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
        background: #f5f7fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ga-access-denied {
        background: #fff;
        border-radius: 12px;
        padding: 60px 40px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        max-width: 500px;
    }
    .ga-access-denied-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
    }
    .ga-access-denied-icon .dashicons {
        font-size: 40px;
        width: 40px;
        height: 40px;
        color: #fff;
    }
    .ga-access-denied h2 {
        font-size: 24px;
        margin: 0 0 15px 0;
        color: #1a1a2e;
    }
    .ga-access-denied p {
        color: #666;
        margin-bottom: 25px;
    }
    .ga-btn {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }
    .ga-btn-primary {
        background: #28a745;
        color: #fff;
    }
    .ga-btn-primary:hover {
        background: #218838;
        color: #fff;
    }
    </style>
    <?php
    get_footer();
    exit;
}

global $wpdb;

// =========================================================================
// OBTENER DATOS DEL DASHBOARD
// =========================================================================

// Datos del cliente
$nombre_cliente = !empty($cliente->nombre_comercial) ? $cliente->nombre_comercial : $wp_user->display_name;
$tipo_cliente = $cliente->tipo;
$codigo_cliente = $cliente->codigo;

// Tipos de cliente legibles
$tipos_cliente = GA_Clientes::get_tipos();
$tipo_cliente_label = isset($tipos_cliente[$tipo_cliente]) ? $tipos_cliente[$tipo_cliente] : $tipo_cliente;

// =========================================================================
// CONTAR CASOS POR ESTADO
// =========================================================================
$table_casos = $wpdb->prefix . 'ga_casos';

$casos_abiertos = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_casos} WHERE cliente_id = %d AND estado = 'ABIERTO'",
    $cliente->id
));

$casos_en_progreso = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_casos} WHERE cliente_id = %d AND estado = 'EN_PROGRESO'",
    $cliente->id
));

$casos_en_espera = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_casos} WHERE cliente_id = %d AND estado = 'EN_ESPERA'",
    $cliente->id
));

$casos_cerrados = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_casos} WHERE cliente_id = %d AND estado = 'CERRADO'",
    $cliente->id
));

$total_casos_activos = $casos_abiertos + $casos_en_progreso + $casos_en_espera;

// =========================================================================
// CONTAR PROYECTOS Y CALCULAR AVANCE PROMEDIO
// =========================================================================
$table_proyectos = $wpdb->prefix . 'ga_proyectos';

// Obtener IDs de casos del cliente para buscar proyectos
$casos_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT id FROM {$table_casos} WHERE cliente_id = %d",
    $cliente->id
));

$proyectos_en_progreso = 0;
$proyectos_completados = 0;
$proyectos_total = 0;
$suma_avance = 0;
$avance_promedio = 0;

if (!empty($casos_ids)) {
    $casos_ids_str = implode(',', array_map('intval', $casos_ids));

    $proyectos_en_progreso = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$table_proyectos} WHERE caso_id IN ({$casos_ids_str}) AND estado = 'EN_PROGRESO'"
    );

    $proyectos_completados = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$table_proyectos} WHERE caso_id IN ({$casos_ids_str}) AND estado = 'COMPLETADO'"
    );

    $proyectos_total = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$table_proyectos} WHERE caso_id IN ({$casos_ids_str})"
    );

    // Calcular avance promedio de proyectos en progreso
    $suma_avance = (float) $wpdb->get_var(
        "SELECT COALESCE(AVG(porcentaje_avance), 0) FROM {$table_proyectos} WHERE caso_id IN ({$casos_ids_str}) AND estado = 'EN_PROGRESO'"
    );
    $avance_promedio = round($suma_avance);
}

// =========================================================================
// FACTURAS PENDIENTES Y VENCIDAS
// =========================================================================
$table_facturas = $wpdb->prefix . 'ga_facturas';

// Facturas pendientes (ENVIADA)
$facturas_pendientes = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_facturas} WHERE cliente_id = %d AND estado = 'ENVIADA'",
    $cliente->id
));

// Facturas vencidas
$facturas_vencidas = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_facturas} WHERE cliente_id = %d AND estado = 'VENCIDA'",
    $cliente->id
));

// Total pendiente de pago
$total_pendiente = (float) $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(saldo_pendiente), 0) FROM {$table_facturas}
     WHERE cliente_id = %d AND estado IN ('ENVIADA', 'VENCIDA', 'PARCIAL')",
    $cliente->id
));

// Total vencido
$total_vencido = (float) $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(saldo_pendiente), 0) FROM {$table_facturas}
     WHERE cliente_id = %d AND estado = 'VENCIDA'",
    $cliente->id
));

// =========================================================================
// FACTURAS PROXIMAS A VENCER (en los proximos 15 dias)
// =========================================================================
$fecha_limite = date('Y-m-d', strtotime('+15 days'));
$fecha_hoy = date('Y-m-d');

$facturas_proximas = $wpdb->get_results($wpdb->prepare(
    "SELECT numero, total_a_pagar, saldo_pendiente, fecha_vencimiento, moneda
     FROM {$table_facturas}
     WHERE cliente_id = %d
       AND estado IN ('ENVIADA', 'PARCIAL')
       AND fecha_vencimiento BETWEEN %s AND %s
     ORDER BY fecha_vencimiento ASC
     LIMIT 5",
    $cliente->id,
    $fecha_hoy,
    $fecha_limite
));

// =========================================================================
// PROYECTOS RECIENTES (ultimos 5 en progreso)
// =========================================================================
$proyectos_recientes = array();
if (!empty($casos_ids)) {
    $casos_ids_str = implode(',', array_map('intval', $casos_ids));

    $proyectos_recientes = $wpdb->get_results(
        "SELECT p.codigo, p.nombre, p.estado, p.porcentaje_avance, p.fecha_fin_estimada
         FROM {$table_proyectos} p
         WHERE p.caso_id IN ({$casos_ids_str})
           AND p.estado IN ('EN_PROGRESO', 'PLANIFICACION', 'PAUSADO')
         ORDER BY p.updated_at DESC
         LIMIT 5"
    );
}

// URLs de navegacion
$url_dashboard = home_url('/cliente/');
$url_casos = home_url('/cliente/mis-casos/');
$url_facturas = home_url('/cliente/mis-facturas/');
$url_perfil = home_url('/cliente/mi-perfil/');

// Usar header del tema
get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-cliente">
    <div class="ga-container">

        <!-- Header con navegacion -->
        <div class="ga-portal-nav">
            <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-nav-item active">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($url_casos); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-portfolio"></span>
                <?php esc_html_e('Mis Casos', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($url_facturas); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-media-text"></span>
                <?php esc_html_e('Mis Facturas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-id"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </a>
        </div>

        <!-- Bienvenida -->
        <div class="ga-welcome-card">
            <div class="ga-welcome-avatar">
                <?php echo get_avatar($wp_user_id, 80); ?>
            </div>
            <div class="ga-welcome-info">
                <h1><?php printf(esc_html__('Bienvenido, %s', 'gestionadmin-wolk'), esc_html($nombre_cliente)); ?></h1>
                <p class="ga-welcome-meta">
                    <span class="ga-codigo"><?php echo esc_html($codigo_cliente); ?></span>
                    <span class="ga-separator">|</span>
                    <span class="ga-tipo"><?php echo esc_html($tipo_cliente_label); ?></span>
                    <?php if ($cliente->activo): ?>
                        <span class="ga-badge ga-badge-success"><?php esc_html_e('Activo', 'gestionadmin-wolk'); ?></span>
                    <?php else: ?>
                        <span class="ga-badge ga-badge-danger"><?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="ga-stats-grid">
            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-icon-blue">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-number"><?php echo esc_html($total_casos_activos); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Casos Activos', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-icon-purple">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-number"><?php echo esc_html($proyectos_en_progreso); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Proyectos en Curso', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-icon-yellow">
                    <span class="dashicons dashicons-media-text"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-number"><?php echo esc_html($facturas_pendientes + $facturas_vencidas); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Facturas Pendientes', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-icon-green">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-number">$<?php echo esc_html(number_format($total_pendiente, 2)); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Por Pagar', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </div>

        <!-- Alerta de facturas vencidas -->
        <?php if ($facturas_vencidas > 0): ?>
        <div class="ga-alert ga-alert-danger">
            <span class="dashicons dashicons-warning"></span>
            <div class="ga-alert-content">
                <strong><?php esc_html_e('Atencion:', 'gestionadmin-wolk'); ?></strong>
                <?php
                printf(
                    esc_html(_n(
                        'Tienes %d factura vencida por un total de $%s',
                        'Tienes %d facturas vencidas por un total de $%s',
                        $facturas_vencidas,
                        'gestionadmin-wolk'
                    )),
                    $facturas_vencidas,
                    number_format($total_vencido, 2)
                );
                ?>
            </div>
            <a href="<?php echo esc_url($url_facturas . '?estado=VENCIDA'); ?>" class="ga-alert-link">
                <?php esc_html_e('Ver facturas', 'gestionadmin-wolk'); ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- Contenido en dos columnas -->
        <div class="ga-dashboard-grid">

            <!-- Columna izquierda: Facturas proximas a vencer -->
            <div class="ga-dashboard-section">
                <div class="ga-section-header">
                    <h2>
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('Facturas Proximas a Vencer', 'gestionadmin-wolk'); ?>
                    </h2>
                    <a href="<?php echo esc_url($url_facturas); ?>" class="ga-section-link">
                        <?php esc_html_e('Ver todas', 'gestionadmin-wolk'); ?>
                    </a>
                </div>

                <div class="ga-section-content">
                    <?php if (empty($facturas_proximas)): ?>
                        <div class="ga-empty-state ga-empty-state-small">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <p><?php esc_html_e('No tienes facturas proximas a vencer', 'gestionadmin-wolk'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="ga-facturas-list">
                            <?php foreach ($facturas_proximas as $factura):
                                $dias_restantes = (strtotime($factura->fecha_vencimiento) - strtotime($fecha_hoy)) / 86400;
                                $urgente = $dias_restantes <= 5;
                            ?>
                            <div class="ga-factura-item <?php echo $urgente ? 'ga-factura-urgente' : ''; ?>">
                                <div class="ga-factura-info">
                                    <span class="ga-factura-numero"><?php echo esc_html($factura->numero); ?></span>
                                    <span class="ga-factura-vence">
                                        <?php
                                        printf(
                                            esc_html__('Vence: %s', 'gestionadmin-wolk'),
                                            date_i18n('d M', strtotime($factura->fecha_vencimiento))
                                        );
                                        ?>
                                        <?php if ($urgente): ?>
                                            <span class="ga-badge ga-badge-warning"><?php echo esc_html(round($dias_restantes)); ?>d</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="ga-factura-monto">
                                    <strong>$<?php echo esc_html(number_format($factura->saldo_pendiente, 2)); ?></strong>
                                    <span class="ga-moneda"><?php echo esc_html($factura->moneda); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna derecha: Proyectos recientes -->
            <div class="ga-dashboard-section">
                <div class="ga-section-header">
                    <h2>
                        <span class="dashicons dashicons-chart-line"></span>
                        <?php esc_html_e('Proyectos Recientes', 'gestionadmin-wolk'); ?>
                    </h2>
                    <a href="<?php echo esc_url($url_casos); ?>" class="ga-section-link">
                        <?php esc_html_e('Ver todos', 'gestionadmin-wolk'); ?>
                    </a>
                </div>

                <div class="ga-section-content">
                    <?php if (empty($proyectos_recientes)): ?>
                        <div class="ga-empty-state ga-empty-state-small">
                            <span class="dashicons dashicons-clipboard"></span>
                            <p><?php esc_html_e('No hay proyectos activos', 'gestionadmin-wolk'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="ga-proyectos-list">
                            <?php foreach ($proyectos_recientes as $proyecto):
                                $avance = (int) $proyecto->porcentaje_avance;
                                $estado_class = '';
                                switch ($proyecto->estado) {
                                    case 'EN_PROGRESO':
                                        $estado_class = 'ga-estado-progress';
                                        $estado_label = __('En Progreso', 'gestionadmin-wolk');
                                        break;
                                    case 'PLANIFICACION':
                                        $estado_class = 'ga-estado-planning';
                                        $estado_label = __('Planificacion', 'gestionadmin-wolk');
                                        break;
                                    case 'PAUSADO':
                                        $estado_class = 'ga-estado-paused';
                                        $estado_label = __('Pausado', 'gestionadmin-wolk');
                                        break;
                                    case 'COMPLETADO':
                                        $estado_class = 'ga-estado-completed';
                                        $estado_label = __('Completado', 'gestionadmin-wolk');
                                        break;
                                    default:
                                        $estado_class = 'ga-estado-default';
                                        $estado_label = $proyecto->estado;
                                }
                            ?>
                            <div class="ga-proyecto-item">
                                <div class="ga-proyecto-header">
                                    <span class="ga-proyecto-codigo"><?php echo esc_html($proyecto->codigo); ?></span>
                                    <span class="ga-proyecto-estado <?php echo esc_attr($estado_class); ?>">
                                        <?php echo esc_html($estado_label); ?>
                                    </span>
                                </div>
                                <div class="ga-proyecto-nombre">
                                    <?php echo esc_html($proyecto->nombre); ?>
                                </div>
                                <div class="ga-proyecto-progress">
                                    <div class="ga-progress-bar">
                                        <div class="ga-progress-fill" style="width: <?php echo esc_attr($avance); ?>%;"></div>
                                    </div>
                                    <span class="ga-progress-text"><?php echo esc_html($avance); ?>%</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Accesos rapidos -->
        <div class="ga-quick-access">
            <h3><?php esc_html_e('Accesos Rapidos', 'gestionadmin-wolk'); ?></h3>
            <div class="ga-quick-grid">
                <a href="<?php echo esc_url($url_casos); ?>" class="ga-quick-card">
                    <span class="dashicons dashicons-portfolio"></span>
                    <span class="ga-quick-label"><?php esc_html_e('Mis Casos', 'gestionadmin-wolk'); ?></span>
                </a>
                <a href="<?php echo esc_url($url_facturas); ?>" class="ga-quick-card">
                    <span class="dashicons dashicons-media-text"></span>
                    <span class="ga-quick-label"><?php esc_html_e('Mis Facturas', 'gestionadmin-wolk'); ?></span>
                </a>
                <a href="<?php echo esc_url($url_perfil); ?>" class="ga-quick-card">
                    <span class="dashicons dashicons-id"></span>
                    <span class="ga-quick-label"><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></span>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="ga-portal-footer">
            <p>
                <?php esc_html_e('Desarrollado por', 'gestionadmin-wolk'); ?>
                <a href="https://wolksoftcr.com" target="_blank">Wolksoftcr.com</a>
            </p>
        </div>

    </div>
</div>

<style>
/* =========================================================================
   PORTAL CLIENTE - DASHBOARD STYLES
   ========================================================================= */

.ga-portal-cliente {
    min-height: 100vh;
    padding: 30px 20px;
    background: #f5f7fa;
}

.ga-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* =========================================================================
   NAVEGACION
   ========================================================================= */

.ga-portal-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 25px;
    background: #fff;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    flex-wrap: wrap;
}

.ga-nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
}

.ga-nav-item:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.ga-nav-item.active {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
}

.ga-nav-item .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* =========================================================================
   TARJETA DE BIENVENIDA
   ========================================================================= */

.ga-welcome-card {
    display: flex;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 25px;
    color: #fff;
}

.ga-welcome-avatar img {
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.3);
}

.ga-welcome-info h1 {
    margin: 0 0 8px 0;
    font-size: 26px;
    font-weight: 600;
    color: #fff;
}

.ga-welcome-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 14px;
    color: rgba(255,255,255,0.9);
    margin: 0;
}

.ga-separator {
    opacity: 0.5;
}

.ga-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.ga-badge-success {
    background: rgba(255,255,255,0.2);
    color: #fff;
}

.ga-badge-danger {
    background: #dc3545;
    color: #fff;
}

.ga-badge-warning {
    background: #ffc107;
    color: #000;
}

/* =========================================================================
   TARJETAS DE ESTADISTICAS
   ========================================================================= */

.ga-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.ga-stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
}

.ga-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.ga-stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ga-stat-icon .dashicons {
    font-size: 26px;
    width: 26px;
    height: 26px;
    color: #fff;
}

.ga-stat-icon-blue {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.ga-stat-icon-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

.ga-stat-icon-yellow {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.ga-stat-icon-green {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.ga-stat-content {
    display: flex;
    flex-direction: column;
}

.ga-stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.ga-stat-label {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
}

/* =========================================================================
   ALERTA
   ========================================================================= */

.ga-alert {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.ga-alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
}

.ga-alert-danger .dashicons {
    color: #dc2626;
    font-size: 24px;
    width: 24px;
    height: 24px;
}

.ga-alert-content {
    flex: 1;
    color: #991b1b;
    font-size: 14px;
}

.ga-alert-link {
    color: #dc2626;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}

.ga-alert-link:hover {
    text-decoration: underline;
}

/* =========================================================================
   GRID DEL DASHBOARD
   ========================================================================= */

.ga-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.ga-dashboard-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
}

.ga-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.ga-section-header h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.ga-section-header h2 .dashicons {
    color: #28a745;
}

.ga-section-link {
    color: #28a745;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

.ga-section-link:hover {
    text-decoration: underline;
}

.ga-section-content {
    padding: 20px 24px;
}

/* =========================================================================
   ESTADO VACIO
   ========================================================================= */

.ga-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}

.ga-empty-state-small {
    padding: 30px 20px;
}

.ga-empty-state .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.ga-empty-state p {
    margin: 0;
    font-size: 14px;
}

/* =========================================================================
   LISTA DE FACTURAS
   ========================================================================= */

.ga-facturas-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ga-factura-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 3px solid #f59e0b;
    transition: all 0.2s;
}

.ga-factura-item:hover {
    background: #f1f5f9;
}

.ga-factura-urgente {
    border-left-color: #dc2626;
    background: #fef2f2;
}

.ga-factura-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ga-factura-numero {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}

.ga-factura-vence {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ga-factura-monto {
    text-align: right;
}

.ga-factura-monto strong {
    display: block;
    font-size: 16px;
    color: #1e293b;
}

.ga-factura-monto .ga-moneda {
    font-size: 11px;
    color: #94a3b8;
}

/* =========================================================================
   LISTA DE PROYECTOS
   ========================================================================= */

.ga-proyectos-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ga-proyecto-item {
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
    transition: all 0.2s;
}

.ga-proyecto-item:hover {
    background: #f1f5f9;
}

.ga-proyecto-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.ga-proyecto-codigo {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.ga-proyecto-estado {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.ga-estado-progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.ga-estado-planning {
    background: #e5e7eb;
    color: #4b5563;
}

.ga-estado-paused {
    background: #fef3c7;
    color: #92400e;
}

.ga-estado-completed {
    background: #d1fae5;
    color: #047857;
}

.ga-estado-default {
    background: #f1f5f9;
    color: #475569;
}

.ga-proyecto-nombre {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
    margin-bottom: 12px;
}

.ga-proyecto-progress {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ga-progress-bar {
    flex: 1;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.ga-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.ga-progress-text {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    min-width: 40px;
    text-align: right;
}

/* =========================================================================
   ACCESOS RAPIDOS
   ========================================================================= */

.ga-quick-access {
    margin-bottom: 30px;
}

.ga-quick-access h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 16px 0;
}

.ga-quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
}

.ga-quick-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 24px 20px;
    background: #fff;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.2s;
}

.ga-quick-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.ga-quick-card .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #28a745;
}

.ga-quick-label {
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
}

/* =========================================================================
   FOOTER
   ========================================================================= */

.ga-portal-footer {
    text-align: center;
    padding: 20px;
    color: #94a3b8;
    font-size: 13px;
}

.ga-portal-footer a {
    color: #28a745;
    text-decoration: none;
}

.ga-portal-footer a:hover {
    text-decoration: underline;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */

@media (max-width: 768px) {
    .ga-portal-cliente {
        padding: 20px 15px;
    }

    .ga-portal-nav {
        gap: 6px;
        padding: 10px;
    }

    .ga-nav-item {
        padding: 8px 12px;
        font-size: 13px;
    }

    .ga-nav-item .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
    }

    .ga-welcome-card {
        flex-direction: column;
        text-align: center;
        padding: 25px 20px;
    }

    .ga-welcome-info h1 {
        font-size: 22px;
    }

    .ga-welcome-meta {
        justify-content: center;
    }

    .ga-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .ga-stat-card {
        padding: 16px;
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }

    .ga-stat-icon {
        width: 48px;
        height: 48px;
    }

    .ga-stat-icon .dashicons {
        font-size: 22px;
        width: 22px;
        height: 22px;
    }

    .ga-stat-number {
        font-size: 22px;
    }

    .ga-dashboard-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .ga-alert {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .ga-factura-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .ga-factura-monto {
        text-align: left;
    }

    .ga-quick-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .ga-quick-card {
        padding: 20px 15px;
    }

    .ga-quick-card .dashicons {
        font-size: 26px;
        width: 26px;
        height: 26px;
    }

    .ga-quick-label {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .ga-stats-grid {
        grid-template-columns: 1fr;
    }

    .ga-stat-card {
        flex-direction: row;
        text-align: left;
    }

    .ga-quick-grid {
        grid-template-columns: 1fr;
    }

    .ga-quick-card {
        flex-direction: row;
        justify-content: flex-start;
        gap: 15px;
    }
}
</style>

<?php get_footer(); ?>
