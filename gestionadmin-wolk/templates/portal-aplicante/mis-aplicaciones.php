<?php
/**
 * Template: Portal Aplicante - Mis Aplicaciones
 *
 * Historial completo de aplicaciones del aplicante a ordenes de trabajo.
 * Permite filtrar por estado, ver detalles de propuestas, y acceder
 * a acciones segun el estado de cada aplicacion.
 *
 * Funcionalidades:
 * - Verificacion de usuario aplicante
 * - Listado de aplicaciones con JOIN a ordenes de trabajo
 * - Filtros por estado con contadores (TODAS, ENVIADA, EN_REVISION, ACEPTADA, RECHAZADA)
 * - Propuesta expandible con detalles completos
 * - Acciones contextuales (Ver Acuerdo, Ver Orden, etc.)
 * - Motivo de rechazo si aplica
 *
 * Tabla principal: wp_ga_aplicaciones_orden
 * Tablas relacionadas: wp_ga_ordenes_trabajo, wp_ga_aplicantes
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 * @updated    1.14.0 - Mis Aplicaciones funcional completo (Sprint A2)
 * @author     Wolksoftcr.com
 */

// =========================================================================
// SEGURIDAD: Verificar acceso directo al archivo
// =========================================================================
if (!defined('ABSPATH')) {
    exit;
}

// =========================================================================
// AUTENTICACION: Verificar que el usuario esta logueado
// =========================================================================
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

// =========================================================================
// OBTENER DATOS DEL USUARIO
// =========================================================================
$wp_user_id = get_current_user_id();

// Cargar modulo de aplicantes
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-aplicantes.php';

// =========================================================================
// VERIFICAR SI ES APLICANTE REGISTRADO
// =========================================================================
$aplicante = GA_Aplicantes::get_by_wp_user($wp_user_id);

// =========================================================================
// SI NO ES APLICANTE: Mostrar pantalla de acceso denegado
// =========================================================================
if (!$aplicante) {
    get_header();
    GA_Theme_Integration::print_portal_styles();
    ?>
    <div class="ga-public-container ga-portal-aplicante ga-access-denied-page">
        <div class="ga-container">
            <div class="ga-access-denied-card">
                <div class="ga-access-icon">
                    <span class="dashicons dashicons-lock"></span>
                </div>
                <h1><?php esc_html_e('Acceso Restringido', 'gestionadmin-wolk'); ?></h1>
                <p><?php esc_html_e('Necesitas registrarte como aplicante para ver tus aplicaciones.', 'gestionadmin-wolk'); ?></p>
                <div class="ga-access-actions">
                    <a href="<?php echo esc_url(home_url('/mi-cuenta/registro/')); ?>" class="ga-btn ga-btn-primary">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php esc_html_e('Registrarme', 'gestionadmin-wolk'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="ga-btn ga-btn-outline">
                        <?php esc_html_e('Volver al Inicio', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <style>
    .ga-access-denied-page {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }
    .ga-access-denied-card {
        background: #fff;
        border-radius: 24px;
        padding: 60px 50px;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
        max-width: 480px;
    }
    .ga-access-icon {
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
    .ga-access-icon .dashicons {
        font-size: 44px;
        width: 44px;
        height: 44px;
        color: #fff;
    }
    .ga-access-denied-card h1 {
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 12px 0;
    }
    .ga-access-denied-card p {
        color: #64748b;
        margin: 0 0 28px 0;
        font-size: 15px;
    }
    .ga-access-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .ga-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 24px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .ga-btn .dashicons {
        font-size: 18px;
        width: 18px;
        height: 18px;
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
    .ga-btn-outline {
        background: #fff;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }
    .ga-btn-outline:hover {
        border-color: #10b981;
        color: #10b981;
    }
    </style>
    <?php
    get_footer();
    exit;
}

// =========================================================================
// APLICANTE ENCONTRADO - CARGAR DATOS
// =========================================================================

global $wpdb;

// =========================================================================
// TABLAS DE BASE DE DATOS
// =========================================================================
$table_aplicaciones = $wpdb->prefix . 'ga_aplicaciones_orden';
$table_ordenes = $wpdb->prefix . 'ga_ordenes_trabajo';

// =========================================================================
// OBTENER FILTRO DE ESTADO
// =========================================================================
$estado_filtro = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';

// Validar estados permitidos
$estados_permitidos = array('ENVIADA', 'EN_REVISION', 'ACEPTADA', 'RECHAZADA');
if (!empty($estado_filtro) && !in_array($estado_filtro, $estados_permitidos)) {
    $estado_filtro = '';
}

// =========================================================================
// OBTENER CONTADORES POR ESTADO
// =========================================================================
$contadores = array(
    'TODAS'       => 0,
    'ENVIADA'     => 0,
    'EN_REVISION' => 0,
    'ACEPTADA'    => 0,
    'RECHAZADA'   => 0,
);

// Total de aplicaciones
$contadores['TODAS'] = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_aplicaciones} WHERE aplicante_id = %d",
    $aplicante->id
));

// Por estado
foreach ($estados_permitidos as $estado) {
    $contadores[$estado] = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_aplicaciones} WHERE aplicante_id = %d AND estado = %s",
        $aplicante->id,
        $estado
    ));
}

// =========================================================================
// OBTENER APLICACIONES CON JOIN A ORDENES
// =========================================================================
$sql = "SELECT a.*,
               o.codigo as orden_codigo,
               o.titulo as orden_titulo,
               o.descripcion as orden_descripcion,
               o.categoria as orden_categoria,
               o.presupuesto_min,
               o.presupuesto_max,
               o.estado as orden_estado
        FROM {$table_aplicaciones} a
        LEFT JOIN {$table_ordenes} o ON a.orden_id = o.id
        WHERE a.aplicante_id = %d";

$params = array($aplicante->id);

// Aplicar filtro de estado si existe
if (!empty($estado_filtro)) {
    $sql .= " AND a.estado = %s";
    $params[] = $estado_filtro;
}

$sql .= " ORDER BY a.created_at DESC";

$aplicaciones = $wpdb->get_results($wpdb->prepare($sql, $params));

// =========================================================================
// URLS DE NAVEGACION
// =========================================================================
$url_dashboard = home_url('/mi-cuenta/');
$url_aplicaciones = home_url('/mi-cuenta/aplicaciones/');
$url_marketplace = home_url('/trabajo/');
$url_pagos = home_url('/mi-cuenta/pagos/');
$url_perfil = home_url('/mi-cuenta/perfil/');

// =========================================================================
// DEFINIR COLORES Y ETIQUETAS DE ESTADOS
// =========================================================================
$estados_config = array(
    'ENVIADA' => array(
        'label'     => __('Enviada', 'gestionadmin-wolk'),
        'color'     => 'blue',
        'icon'      => 'email-alt',
        'bg'        => '#e0f2fe',
        'text'      => '#0369a1',
        'border'    => '#7dd3fc',
    ),
    'EN_REVISION' => array(
        'label'     => __('En Revision', 'gestionadmin-wolk'),
        'color'     => 'yellow',
        'icon'      => 'visibility',
        'bg'        => '#fef3c7',
        'text'      => '#92400e',
        'border'    => '#fcd34d',
    ),
    'ACEPTADA' => array(
        'label'     => __('Aceptada', 'gestionadmin-wolk'),
        'color'     => 'green',
        'icon'      => 'yes-alt',
        'bg'        => '#dcfce7',
        'text'      => '#166534',
        'border'    => '#86efac',
    ),
    'RECHAZADA' => array(
        'label'     => __('Rechazada', 'gestionadmin-wolk'),
        'color'     => 'red',
        'icon'      => 'dismiss',
        'bg'        => '#fef2f2',
        'text'      => '#991b1b',
        'border'    => '#fecaca',
    ),
    'CONTRATADO' => array(
        'label'     => __('Contratado', 'gestionadmin-wolk'),
        'color'     => 'green',
        'icon'      => 'awards',
        'bg'        => '#d1fae5',
        'text'      => '#065f46',
        'border'    => '#6ee7b7',
    ),
);

// =========================================================================
// RENDER: Header del tema WordPress
// =========================================================================
get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-aplicante ga-portal-aplicaciones">
    <div class="ga-container">

        <!-- =====================================================================
             NAVEGACION DEL PORTAL
        ====================================================================== -->
        <nav class="ga-portal-nav" role="navigation" aria-label="<?php esc_attr_e('Navegacion del portal', 'gestionadmin-wolk'); ?>">
            <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-dashboard"></span>
                <span class="ga-nav-text"><?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_aplicaciones); ?>" class="ga-nav-item active">
                <span class="dashicons dashicons-portfolio"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mis Aplicaciones', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_marketplace); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-store"></span>
                <span class="ga-nav-text"><?php esc_html_e('Marketplace', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_pagos); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-money-alt"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mis Pagos', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-admin-users"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></span>
            </a>
        </nav>

        <!-- =====================================================================
             HEADER DE LA PAGINA
        ====================================================================== -->
        <header class="ga-page-header">
            <div class="ga-header-content">
                <div class="ga-header-icon">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
                <div class="ga-header-text">
                    <h1><?php esc_html_e('Mis Aplicaciones', 'gestionadmin-wolk'); ?></h1>
                    <p><?php esc_html_e('Historial y estado de tus postulaciones a ordenes de trabajo', 'gestionadmin-wolk'); ?></p>
                </div>
            </div>
            <div class="ga-header-stats">
                <div class="ga-mini-stat">
                    <span class="ga-mini-stat-value"><?php echo esc_html($contadores['TODAS']); ?></span>
                    <span class="ga-mini-stat-label"><?php esc_html_e('Total', 'gestionadmin-wolk'); ?></span>
                </div>
                <div class="ga-mini-stat ga-mini-stat-success">
                    <span class="ga-mini-stat-value"><?php echo esc_html($contadores['ACEPTADA']); ?></span>
                    <span class="ga-mini-stat-label"><?php esc_html_e('Aceptadas', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </header>

        <!-- =====================================================================
             FILTROS POR ESTADO
        ====================================================================== -->
        <div class="ga-filter-tabs" role="tablist">
            <a href="<?php echo esc_url($url_aplicaciones); ?>"
               class="ga-filter-tab <?php echo empty($estado_filtro) ? 'active' : ''; ?>"
               role="tab"
               aria-selected="<?php echo empty($estado_filtro) ? 'true' : 'false'; ?>">
                <span class="ga-tab-label"><?php esc_html_e('Todas', 'gestionadmin-wolk'); ?></span>
                <span class="ga-tab-count"><?php echo esc_html($contadores['TODAS']); ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg('estado', 'ENVIADA', $url_aplicaciones)); ?>"
               class="ga-filter-tab ga-tab-blue <?php echo $estado_filtro === 'ENVIADA' ? 'active' : ''; ?>"
               role="tab">
                <span class="ga-tab-label"><?php esc_html_e('Enviadas', 'gestionadmin-wolk'); ?></span>
                <span class="ga-tab-count"><?php echo esc_html($contadores['ENVIADA']); ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg('estado', 'EN_REVISION', $url_aplicaciones)); ?>"
               class="ga-filter-tab ga-tab-yellow <?php echo $estado_filtro === 'EN_REVISION' ? 'active' : ''; ?>"
               role="tab">
                <span class="ga-tab-label"><?php esc_html_e('En Revision', 'gestionadmin-wolk'); ?></span>
                <span class="ga-tab-count"><?php echo esc_html($contadores['EN_REVISION']); ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg('estado', 'ACEPTADA', $url_aplicaciones)); ?>"
               class="ga-filter-tab ga-tab-green <?php echo $estado_filtro === 'ACEPTADA' ? 'active' : ''; ?>"
               role="tab">
                <span class="ga-tab-label"><?php esc_html_e('Aceptadas', 'gestionadmin-wolk'); ?></span>
                <span class="ga-tab-count"><?php echo esc_html($contadores['ACEPTADA']); ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg('estado', 'RECHAZADA', $url_aplicaciones)); ?>"
               class="ga-filter-tab ga-tab-red <?php echo $estado_filtro === 'RECHAZADA' ? 'active' : ''; ?>"
               role="tab">
                <span class="ga-tab-label"><?php esc_html_e('Rechazadas', 'gestionadmin-wolk'); ?></span>
                <span class="ga-tab-count"><?php echo esc_html($contadores['RECHAZADA']); ?></span>
            </a>
        </div>

        <!-- =====================================================================
             LISTA DE APLICACIONES
        ====================================================================== -->
        <section class="ga-applications-section" aria-label="<?php esc_attr_e('Lista de aplicaciones', 'gestionadmin-wolk'); ?>">
            <?php if (empty($aplicaciones)): ?>
                <!-- Estado vacio -->
                <div class="ga-empty-state-card">
                    <div class="ga-empty-illustration">
                        <div class="ga-empty-icon">
                            <span class="dashicons dashicons-portfolio"></span>
                        </div>
                        <div class="ga-empty-circles">
                            <div class="ga-circle ga-circle-1"></div>
                            <div class="ga-circle ga-circle-2"></div>
                            <div class="ga-circle ga-circle-3"></div>
                        </div>
                    </div>
                    <h2>
                        <?php if (!empty($estado_filtro)): ?>
                            <?php printf(esc_html__('Sin aplicaciones %s', 'gestionadmin-wolk'), strtolower($estados_config[$estado_filtro]['label'] ?? $estado_filtro)); ?>
                        <?php else: ?>
                            <?php esc_html_e('No tienes aplicaciones', 'gestionadmin-wolk'); ?>
                        <?php endif; ?>
                    </h2>
                    <p>
                        <?php if (!empty($estado_filtro)): ?>
                            <?php esc_html_e('No tienes aplicaciones con este estado. Prueba con otro filtro.', 'gestionadmin-wolk'); ?>
                        <?php else: ?>
                            <?php esc_html_e('Aun no has aplicado a ninguna orden de trabajo. Explora el marketplace para encontrar oportunidades que se ajusten a tu perfil.', 'gestionadmin-wolk'); ?>
                        <?php endif; ?>
                    </p>
                    <div class="ga-empty-actions">
                        <?php if (!empty($estado_filtro)): ?>
                            <a href="<?php echo esc_url($url_aplicaciones); ?>" class="ga-btn ga-btn-outline">
                                <span class="dashicons dashicons-list-view"></span>
                                <?php esc_html_e('Ver Todas', 'gestionadmin-wolk'); ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($url_marketplace); ?>" class="ga-btn ga-btn-primary">
                            <span class="dashicons dashicons-search"></span>
                            <?php esc_html_e('Explorar Marketplace', 'gestionadmin-wolk'); ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Lista de aplicaciones -->
                <div class="ga-applications-list">
                    <?php foreach ($aplicaciones as $app):
                        // Obtener configuracion del estado
                        $estado = isset($estados_config[$app->estado]) ? $app->estado : 'ENVIADA';
                        $estado_info = $estados_config[$estado];

                        // Formatear fecha
                        $fecha_aplicacion = !empty($app->created_at) ? $app->created_at : $app->fecha_aplicacion;
                        $fecha_formateada = date_i18n('d M Y, H:i', strtotime($fecha_aplicacion));
                        $tiempo_transcurrido = human_time_diff(strtotime($fecha_aplicacion), current_time('timestamp'));

                        // Truncar propuesta
                        $propuesta = !empty($app->propuesta) ? $app->propuesta : (!empty($app->mensaje) ? $app->mensaje : '');
                        $propuesta_corta = wp_trim_words($propuesta, 25, '...');
                        $mostrar_expandir = strlen($propuesta) > strlen($propuesta_corta);

                        // Tarifa y tiempo
                        $tarifa = !empty($app->tarifa_propuesta) ? $app->tarifa_propuesta : (!empty($app->propuesta_economica) ? $app->propuesta_economica : 0);
                        $tiempo = !empty($app->tiempo_estimado) ? $app->tiempo_estimado : (!empty($app->tiempo_entrega) ? $app->tiempo_entrega : 0);
                    ?>
                    <article class="ga-application-card" data-estado="<?php echo esc_attr($estado); ?>">
                        <!-- Header de la tarjeta -->
                        <div class="ga-app-header">
                            <div class="ga-app-orden-info">
                                <span class="ga-app-codigo"><?php echo esc_html($app->orden_codigo); ?></span>
                                <h3 class="ga-app-titulo">
                                    <a href="<?php echo esc_url(home_url('/trabajo/' . $app->orden_codigo . '/')); ?>">
                                        <?php echo esc_html($app->orden_titulo); ?>
                                    </a>
                                </h3>
                                <?php if (!empty($app->orden_categoria)): ?>
                                <span class="ga-app-categoria"><?php echo esc_html($app->orden_categoria); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="ga-app-estado" style="--estado-bg: <?php echo esc_attr($estado_info['bg']); ?>; --estado-text: <?php echo esc_attr($estado_info['text']); ?>; --estado-border: <?php echo esc_attr($estado_info['border']); ?>;">
                                <span class="dashicons dashicons-<?php echo esc_attr($estado_info['icon']); ?>"></span>
                                <?php echo esc_html($estado_info['label']); ?>
                            </div>
                        </div>

                        <!-- Contenido principal -->
                        <div class="ga-app-body">
                            <!-- Grid de metadatos -->
                            <div class="ga-app-meta-grid">
                                <div class="ga-app-meta-item">
                                    <span class="ga-meta-icon">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                    </span>
                                    <div class="ga-meta-content">
                                        <span class="ga-meta-label"><?php esc_html_e('Fecha Aplicacion', 'gestionadmin-wolk'); ?></span>
                                        <span class="ga-meta-value"><?php echo esc_html($fecha_formateada); ?></span>
                                        <span class="ga-meta-sublabel"><?php printf(esc_html__('hace %s', 'gestionadmin-wolk'), $tiempo_transcurrido); ?></span>
                                    </div>
                                </div>

                                <?php if ($tarifa > 0): ?>
                                <div class="ga-app-meta-item ga-meta-highlight">
                                    <span class="ga-meta-icon">
                                        <span class="dashicons dashicons-money-alt"></span>
                                    </span>
                                    <div class="ga-meta-content">
                                        <span class="ga-meta-label"><?php esc_html_e('Tarifa Propuesta', 'gestionadmin-wolk'); ?></span>
                                        <span class="ga-meta-value ga-meta-money">$<?php echo esc_html(number_format($tarifa, 0)); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($tiempo > 0): ?>
                                <div class="ga-app-meta-item">
                                    <span class="ga-meta-icon">
                                        <span class="dashicons dashicons-clock"></span>
                                    </span>
                                    <div class="ga-meta-content">
                                        <span class="ga-meta-label"><?php esc_html_e('Tiempo Estimado', 'gestionadmin-wolk'); ?></span>
                                        <span class="ga-meta-value">
                                            <?php echo esc_html($tiempo); ?>
                                            <?php echo esc_html(_n('dia', 'dias', $tiempo, 'gestionadmin-wolk')); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="ga-app-meta-item">
                                    <span class="ga-meta-icon">
                                        <span class="dashicons dashicons-tag"></span>
                                    </span>
                                    <div class="ga-meta-content">
                                        <span class="ga-meta-label"><?php esc_html_e('Presupuesto Orden', 'gestionadmin-wolk'); ?></span>
                                        <span class="ga-meta-value">
                                            <?php if ($app->presupuesto_max > 0): ?>
                                                $<?php echo esc_html(number_format($app->presupuesto_max, 0)); ?>
                                            <?php else: ?>
                                                <?php esc_html_e('A convenir', 'gestionadmin-wolk'); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Propuesta enviada (expandible) -->
                            <?php if (!empty($propuesta)): ?>
                            <details class="ga-app-propuesta">
                                <summary>
                                    <span class="ga-propuesta-icon">
                                        <span class="dashicons dashicons-format-quote"></span>
                                    </span>
                                    <span class="ga-propuesta-title"><?php esc_html_e('Propuesta Enviada', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-propuesta-preview"><?php echo esc_html($propuesta_corta); ?></span>
                                    <span class="ga-propuesta-toggle">
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </span>
                                </summary>
                                <div class="ga-propuesta-content">
                                    <?php echo nl2br(esc_html($propuesta)); ?>
                                </div>
                            </details>
                            <?php endif; ?>

                            <!-- Motivo de rechazo (si aplica) -->
                            <?php if ($app->estado === 'RECHAZADA' && !empty($app->motivo_rechazo)): ?>
                            <div class="ga-app-rejection">
                                <div class="ga-rejection-header">
                                    <span class="dashicons dashicons-info-outline"></span>
                                    <strong><?php esc_html_e('Motivo del Rechazo', 'gestionadmin-wolk'); ?></strong>
                                </div>
                                <p class="ga-rejection-text"><?php echo esc_html($app->motivo_rechazo); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer con acciones -->
                        <div class="ga-app-footer">
                            <a href="<?php echo esc_url(home_url('/trabajo/' . $app->orden_codigo . '/')); ?>" class="ga-btn ga-btn-outline ga-btn-sm">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php esc_html_e('Ver Orden', 'gestionadmin-wolk'); ?>
                            </a>

                            <?php if ($app->estado === 'ACEPTADA' || $app->estado === 'CONTRATADO'): ?>
                            <a href="<?php echo esc_url(home_url('/mi-cuenta/acuerdo/' . $app->id . '/')); ?>" class="ga-btn ga-btn-primary ga-btn-sm">
                                <span class="dashicons dashicons-media-document"></span>
                                <?php esc_html_e('Ver Acuerdo', 'gestionadmin-wolk'); ?>
                            </a>
                            <?php endif; ?>

                            <?php if ($app->estado === 'EN_REVISION'): ?>
                            <span class="ga-app-waiting">
                                <span class="ga-waiting-dot"></span>
                                <?php esc_html_e('Esperando respuesta', 'gestionadmin-wolk'); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- =====================================================================
             FOOTER DEL PORTAL
        ====================================================================== -->
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

<!-- =========================================================================
     ESTILOS CSS - ENTERPRISE GRADE DESIGN SYSTEM
========================================================================== -->
<style>
/* =========================================================================
   PORTAL APLICANTE - MIS APLICACIONES
   Enterprise-Grade Professional Design System
   ========================================================================= */

:root {
    --ga-primary: #10b981;
    --ga-primary-dark: #059669;
    --ga-primary-light: #d1fae5;
    --ga-secondary: #6366f1;
    --ga-secondary-light: #e0e7ff;
    --ga-warning: #f59e0b;
    --ga-warning-light: #fef3c7;
    --ga-danger: #ef4444;
    --ga-danger-light: #fef2f2;
    --ga-success: #22c55e;
    --ga-success-light: #dcfce7;
    --ga-info: #0ea5e9;
    --ga-info-light: #e0f2fe;
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
    --ga-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    --ga-shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    --ga-shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    --ga-radius: 10px;
    --ga-radius-lg: 14px;
    --ga-radius-xl: 20px;
    --ga-radius-2xl: 24px;
}

.ga-portal-aplicaciones {
    min-height: 100vh;
    padding: 28px 24px;
    background: linear-gradient(180deg, var(--ga-neutral-50) 0%, var(--ga-neutral-100) 100%);
}

.ga-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* -------------------------------------------------------------------------
   NAVEGACION
   ------------------------------------------------------------------------- */
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

/* -------------------------------------------------------------------------
   HEADER DE PAGINA
   ------------------------------------------------------------------------- */
.ga-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    background: #fff;
    padding: 28px 32px;
    border-radius: var(--ga-radius-xl);
    margin-bottom: 24px;
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
}

.ga-header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.ga-header-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    border-radius: var(--ga-radius-lg);
    box-shadow: 0 8px 24px rgba(16,185,129,0.3);
}

.ga-header-icon .dashicons {
    font-size: 30px;
    width: 30px;
    height: 30px;
    color: #fff;
}

.ga-header-text h1 {
    margin: 0 0 6px 0;
    font-size: 26px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    letter-spacing: -0.5px;
}

.ga-header-text p {
    margin: 0;
    font-size: 14px;
    color: var(--ga-neutral-500);
}

.ga-header-stats {
    display: flex;
    gap: 20px;
}

.ga-mini-stat {
    text-align: center;
    padding: 12px 20px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius);
    min-width: 80px;
}

.ga-mini-stat-success {
    background: var(--ga-success-light);
}

.ga-mini-stat-value {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    line-height: 1;
}

.ga-mini-stat-success .ga-mini-stat-value {
    color: #166534;
}

.ga-mini-stat-label {
    display: block;
    font-size: 12px;
    color: var(--ga-neutral-500);
    margin-top: 4px;
}

/* -------------------------------------------------------------------------
   FILTROS POR ESTADO
   ------------------------------------------------------------------------- */
.ga-filter-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    background: #fff;
    padding: 10px;
    border-radius: var(--ga-radius-lg);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    overflow-x: auto;
}

.ga-filter-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: var(--ga-radius);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    color: var(--ga-neutral-600);
    background: var(--ga-neutral-50);
    border: 2px solid transparent;
    transition: all 0.25s;
    white-space: nowrap;
}

.ga-filter-tab:hover {
    background: var(--ga-neutral-100);
    color: var(--ga-neutral-800);
}

.ga-filter-tab.active {
    background: var(--ga-neutral-900);
    color: #fff;
    border-color: var(--ga-neutral-900);
}

/* Colores por estado */
.ga-tab-blue.active {
    background: var(--ga-info);
    border-color: var(--ga-info);
}

.ga-tab-yellow.active {
    background: var(--ga-warning);
    border-color: var(--ga-warning);
}

.ga-tab-green.active {
    background: var(--ga-success);
    border-color: var(--ga-success);
}

.ga-tab-red.active {
    background: var(--ga-danger);
    border-color: var(--ga-danger);
}

.ga-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 8px;
    background: rgba(0,0,0,0.1);
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
}

.ga-filter-tab.active .ga-tab-count {
    background: rgba(255,255,255,0.25);
}

/* -------------------------------------------------------------------------
   ESTADO VACIO
   ------------------------------------------------------------------------- */
.ga-empty-state-card {
    background: #fff;
    border-radius: var(--ga-radius-xl);
    padding: 60px 40px;
    text-align: center;
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
}

.ga-empty-illustration {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto 32px;
}

.ga-empty-icon {
    position: absolute;
    inset: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--ga-neutral-100);
    border-radius: 50%;
    z-index: 2;
}

.ga-empty-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: var(--ga-neutral-400);
}

.ga-empty-circles .ga-circle {
    position: absolute;
    border: 2px solid var(--ga-neutral-200);
    border-radius: 50%;
    animation: pulse-ring 3s ease-out infinite;
}

.ga-circle-1 { inset: 10px; animation-delay: 0s; }
.ga-circle-2 { inset: 0; animation-delay: 1s; }
.ga-circle-3 { inset: -10px; animation-delay: 2s; }

@keyframes pulse-ring {
    0% { opacity: 1; transform: scale(1); }
    100% { opacity: 0; transform: scale(1.3); }
}

.ga-empty-state-card h2 {
    margin: 0 0 12px 0;
    font-size: 22px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-empty-state-card p {
    margin: 0 0 28px 0;
    font-size: 15px;
    color: var(--ga-neutral-500);
    line-height: 1.6;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.ga-empty-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

/* -------------------------------------------------------------------------
   LISTA DE APLICACIONES
   ------------------------------------------------------------------------- */
.ga-applications-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ga-application-card {
    background: #fff;
    border-radius: var(--ga-radius-xl);
    overflow: hidden;
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ga-application-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--ga-shadow-lg);
    border-color: var(--ga-primary);
}

/* Header de aplicacion */
.ga-app-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 24px;
    background: linear-gradient(135deg, var(--ga-neutral-50) 0%, #fff 100%);
    border-bottom: 1px solid var(--ga-neutral-100);
}

.ga-app-orden-info {
    flex: 1;
    min-width: 0;
}

.ga-app-codigo {
    display: inline-block;
    padding: 4px 10px;
    background: var(--ga-neutral-200);
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    font-family: 'SF Mono', 'Consolas', monospace;
    color: var(--ga-neutral-600);
    margin-bottom: 8px;
}

.ga-app-titulo {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.3;
}

.ga-app-titulo a {
    color: var(--ga-neutral-800);
    text-decoration: none;
    transition: color 0.2s;
}

.ga-app-titulo a:hover {
    color: var(--ga-primary);
}

.ga-app-categoria {
    display: inline-block;
    padding: 4px 12px;
    background: var(--ga-secondary-light);
    color: #4338ca;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

/* Badge de estado */
.ga-app-estado {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: var(--ga-radius);
    font-size: 13px;
    font-weight: 600;
    background: var(--estado-bg);
    color: var(--estado-text);
    border: 1px solid var(--estado-border);
    white-space: nowrap;
}

.ga-app-estado .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Body de aplicacion */
.ga-app-body {
    padding: 24px;
}

/* Grid de metadatos */
.ga-app-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.ga-app-meta-item {
    display: flex;
    gap: 12px;
}

.ga-meta-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--ga-neutral-100);
    border-radius: var(--ga-radius);
    flex-shrink: 0;
}

.ga-meta-icon .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    color: var(--ga-neutral-500);
}

.ga-meta-highlight .ga-meta-icon {
    background: var(--ga-primary-light);
}

.ga-meta-highlight .ga-meta-icon .dashicons {
    color: var(--ga-primary-dark);
}

.ga-meta-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ga-meta-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ga-meta-value {
    font-size: 15px;
    font-weight: 600;
    color: var(--ga-neutral-800);
}

.ga-meta-money {
    color: var(--ga-primary-dark);
    font-size: 18px;
}

.ga-meta-sublabel {
    font-size: 12px;
    color: var(--ga-neutral-400);
}

/* Propuesta expandible */
.ga-app-propuesta {
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius);
    overflow: hidden;
    margin-top: 20px;
}

.ga-app-propuesta summary {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    cursor: pointer;
    list-style: none;
    transition: background 0.2s;
}

.ga-app-propuesta summary::-webkit-details-marker {
    display: none;
}

.ga-app-propuesta summary:hover {
    background: var(--ga-neutral-100);
}

.ga-propuesta-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: #fff;
    border-radius: var(--ga-radius);
    flex-shrink: 0;
    box-shadow: var(--ga-shadow);
}

.ga-propuesta-icon .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    color: var(--ga-secondary);
}

.ga-propuesta-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--ga-neutral-700);
    flex-shrink: 0;
}

.ga-propuesta-preview {
    flex: 1;
    font-size: 13px;
    color: var(--ga-neutral-500);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ga-propuesta-toggle {
    flex-shrink: 0;
    color: var(--ga-neutral-400);
    transition: transform 0.25s;
}

.ga-app-propuesta[open] .ga-propuesta-toggle {
    transform: rotate(180deg);
}

.ga-propuesta-content {
    padding: 16px 16px 20px 64px;
    font-size: 14px;
    color: var(--ga-neutral-600);
    line-height: 1.7;
    border-top: 1px solid var(--ga-neutral-200);
}

/* Motivo de rechazo */
.ga-app-rejection {
    margin-top: 20px;
    padding: 16px;
    background: var(--ga-danger-light);
    border-radius: var(--ga-radius);
    border: 1px solid #fecaca;
}

.ga-rejection-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.ga-rejection-header .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    color: #991b1b;
}

.ga-rejection-header strong {
    font-size: 13px;
    color: #991b1b;
}

.ga-rejection-text {
    margin: 0;
    font-size: 14px;
    color: #991b1b;
    line-height: 1.5;
}

/* Footer de aplicacion */
.ga-app-footer {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: var(--ga-neutral-50);
    border-top: 1px solid var(--ga-neutral-100);
}

.ga-app-waiting {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    font-size: 13px;
    color: var(--ga-warning);
    font-weight: 500;
}

.ga-waiting-dot {
    width: 8px;
    height: 8px;
    background: var(--ga-warning);
    border-radius: 50%;
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

/* -------------------------------------------------------------------------
   BOTONES
   ------------------------------------------------------------------------- */
.ga-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: var(--ga-radius);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    border: none;
    cursor: pointer;
}

.ga-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.ga-btn-sm {
    padding: 10px 16px;
    font-size: 12px;
}

.ga-btn-primary {
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(16,185,129,0.3);
}

.ga-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16,185,129,0.4);
    color: #fff;
}

.ga-btn-outline {
    background: #fff;
    color: var(--ga-neutral-600);
    border: 2px solid var(--ga-neutral-200);
}

.ga-btn-outline:hover {
    border-color: var(--ga-primary);
    color: var(--ga-primary);
}

/* -------------------------------------------------------------------------
   FOOTER
   ------------------------------------------------------------------------- */
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
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .ga-page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .ga-header-stats {
        width: 100%;
        justify-content: flex-start;
    }
}

@media (max-width: 768px) {
    .ga-portal-aplicaciones {
        padding: 20px 16px;
    }

    .ga-portal-nav {
        gap: 4px;
        padding: 8px;
    }

    .ga-nav-item {
        padding: 10px 14px;
    }

    .ga-nav-text {
        display: none;
    }

    .ga-page-header {
        padding: 20px;
    }

    .ga-header-icon {
        width: 50px;
        height: 50px;
    }

    .ga-header-icon .dashicons {
        font-size: 24px;
        width: 24px;
        height: 24px;
    }

    .ga-header-text h1 {
        font-size: 22px;
    }

    .ga-filter-tabs {
        padding: 8px;
        gap: 6px;
    }

    .ga-filter-tab {
        padding: 10px 14px;
        font-size: 13px;
    }

    .ga-tab-label {
        display: none;
    }

    .ga-app-header {
        flex-direction: column;
        padding: 20px;
    }

    .ga-app-estado {
        align-self: flex-start;
    }

    .ga-app-body {
        padding: 20px;
    }

    .ga-app-meta-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .ga-propuesta-preview {
        display: none;
    }

    .ga-propuesta-content {
        padding-left: 16px;
    }

    .ga-app-footer {
        flex-direction: column;
        padding: 16px 20px;
    }

    .ga-app-footer .ga-btn {
        width: 100%;
        justify-content: center;
    }

    .ga-app-waiting {
        margin-left: 0;
        margin-top: 8px;
    }

    .ga-empty-state-card {
        padding: 40px 24px;
    }
}

@media (max-width: 480px) {
    .ga-header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .ga-mini-stat {
        padding: 10px 16px;
        min-width: 70px;
    }

    .ga-mini-stat-value {
        font-size: 20px;
    }

    .ga-app-titulo {
        font-size: 16px;
    }

    .ga-app-meta-item {
        gap: 10px;
    }

    .ga-meta-icon {
        width: 36px;
        height: 36px;
    }

    .ga-meta-value {
        font-size: 14px;
    }

    .ga-meta-money {
        font-size: 16px;
    }
}
</style>

<?php
// =========================================================================
// RENDER: Footer del tema WordPress
// =========================================================================
get_footer();
?>
