<?php
/**
 * Template: Portal Aplicante - Dashboard
 *
 * Panel principal del aplicante/freelancer con:
 * - Estado de verificacion prominente (PENDIENTE/VERIFICADO/RECHAZADO)
 * - Documentos faltantes si esta pendiente
 * - Resumen de aplicaciones (enviadas, en revision, aceptadas, rechazadas)
 * - Lista de aplicaciones EN_REVISION recientes
 * - Ordenes recomendadas basadas en habilidades del aplicante
 * - Navegacion completa del portal
 *
 * Este dashboard es el centro de operaciones del freelancer donde puede:
 * - Ver el estado de su cuenta y verificacion
 * - Monitorear sus aplicaciones a ordenes de trabajo
 * - Descubrir nuevas oportunidades relevantes a su perfil
 * - Acceder rapidamente a todas las secciones del portal
 *
 * Tabla principal: wp_ga_aplicantes
 * Tablas relacionadas: wp_ga_aplicaciones_orden, wp_ga_ordenes_trabajo
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 * @updated    1.13.0 - Dashboard funcional completo (Sprint A1)
 * @author     Wolksoftcr.com
 */

// =========================================================================
// SEGURIDAD: Verificar acceso directo al archivo
// =========================================================================
if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// =========================================================================
// AUTENTICACION: Verificar que el usuario esta logueado
// =========================================================================
if (!is_user_logged_in()) {
    // Redirigir a pagina de acceso con URL de retorno
    wp_redirect(home_url('/acceso/?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

// =========================================================================
// OBTENER DATOS DEL USUARIO
// =========================================================================
$wp_user_id = get_current_user_id();
$wp_user = wp_get_current_user();

// Cargar modulo de aplicantes
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-aplicantes.php';

// =========================================================================
// VERIFICAR SI ES APLICANTE REGISTRADO
// Usamos get_by_wp_user() que busca por usuario_wp_id en ga_aplicantes
// =========================================================================
$aplicante = GA_Aplicantes::get_by_wp_user($wp_user_id);

// =========================================================================
// SI NO ES APLICANTE: Mostrar pantalla de registro
// Esto permite que usuarios nuevos sepan como registrarse
// =========================================================================
if (!$aplicante) {
    get_header();
    GA_Theme_Integration::print_portal_styles();
    ?>
    <div class="ga-public-container ga-portal-aplicante ga-registro-aplicante">
        <div class="ga-container">
            <div class="ga-registro-card">
                <!-- Ilustracion animada -->
                <div class="ga-registro-illustration">
                    <div class="ga-illustration-bg">
                        <div class="ga-floating-icon ga-float-1">
                            <span class="dashicons dashicons-portfolio"></span>
                        </div>
                        <div class="ga-floating-icon ga-float-2">
                            <span class="dashicons dashicons-money-alt"></span>
                        </div>
                        <div class="ga-floating-icon ga-float-3">
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                    </div>
                    <div class="ga-main-icon">
                        <span class="dashicons dashicons-businessperson"></span>
                    </div>
                </div>

                <!-- Contenido -->
                <div class="ga-registro-content">
                    <h1><?php esc_html_e('Unete como Freelancer', 'gestionadmin-wolk'); ?></h1>
                    <p class="ga-registro-subtitle">
                        <?php esc_html_e('Accede a cientos de oportunidades de trabajo remoto. Registrate como aplicante y comienza a trabajar con empresas de todo el mundo.', 'gestionadmin-wolk'); ?>
                    </p>

                    <!-- Beneficios -->
                    <div class="ga-beneficios-grid">
                        <div class="ga-beneficio-item">
                            <div class="ga-beneficio-icon">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </div>
                            <div class="ga-beneficio-text">
                                <strong><?php esc_html_e('Proyectos Verificados', 'gestionadmin-wolk'); ?></strong>
                                <span><?php esc_html_e('Empresas reales con pagos garantizados', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                        <div class="ga-beneficio-item">
                            <div class="ga-beneficio-icon">
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                            </div>
                            <div class="ga-beneficio-text">
                                <strong><?php esc_html_e('Trabajo 100% Remoto', 'gestionadmin-wolk'); ?></strong>
                                <span><?php esc_html_e('Trabaja desde cualquier lugar', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                        <div class="ga-beneficio-item">
                            <div class="ga-beneficio-icon">
                                <span class="dashicons dashicons-money-alt"></span>
                            </div>
                            <div class="ga-beneficio-text">
                                <strong><?php esc_html_e('Pagos Internacionales', 'gestionadmin-wolk'); ?></strong>
                                <span><?php esc_html_e('Binance, Wise, PayPal, Payoneer', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                        <div class="ga-beneficio-item">
                            <div class="ga-beneficio-icon">
                                <span class="dashicons dashicons-chart-line"></span>
                            </div>
                            <div class="ga-beneficio-text">
                                <strong><?php esc_html_e('Crece tu Reputacion', 'gestionadmin-wolk'); ?></strong>
                                <span><?php esc_html_e('Sistema de calificaciones y reviews', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="ga-registro-actions">
                        <a href="<?php echo esc_url(home_url('/mi-cuenta/registro/')); ?>" class="ga-btn ga-btn-primary ga-btn-lg">
                            <span class="dashicons dashicons-admin-users"></span>
                            <?php esc_html_e('Registrarme como Freelancer', 'gestionadmin-wolk'); ?>
                        </a>
                        <p class="ga-registro-note">
                            <?php esc_html_e('Ya tienes cuenta?', 'gestionadmin-wolk'); ?>
                            <a href="<?php echo esc_url(wp_login_url(home_url('/mi-cuenta/'))); ?>">
                                <?php esc_html_e('Inicia sesion', 'gestionadmin-wolk'); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>

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
       PANTALLA DE REGISTRO - APLICANTE NO REGISTRADO
       ========================================================================= */
    :root {
        --ga-primary: #10b981;
        --ga-primary-dark: #059669;
        --ga-primary-light: #d1fae5;
        --ga-secondary: #6366f1;
        --ga-secondary-light: #e0e7ff;
        --ga-neutral-50: #f8fafc;
        --ga-neutral-100: #f1f5f9;
        --ga-neutral-200: #e2e8f0;
        --ga-neutral-400: #94a3b8;
        --ga-neutral-500: #64748b;
        --ga-neutral-700: #334155;
        --ga-neutral-800: #1e293b;
        --ga-neutral-900: #0f172a;
        --ga-shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        --ga-shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.25);
        --ga-radius-xl: 20px;
        --ga-radius-2xl: 24px;
    }

    .ga-registro-aplicante {
        min-height: 100vh;
        padding: 40px 24px;
        background: linear-gradient(135deg, var(--ga-neutral-50) 0%, var(--ga-neutral-100) 50%, var(--ga-primary-light) 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .ga-registro-card {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 0;
        background: #fff;
        border-radius: var(--ga-radius-2xl);
        overflow: hidden;
        box-shadow: var(--ga-shadow-xl);
        max-width: 1000px;
        width: 100%;
    }

    /* Ilustracion */
    .ga-registro-illustration {
        position: relative;
        background: linear-gradient(135deg, var(--ga-primary) 0%, #0d9488 50%, var(--ga-secondary) 100%);
        padding: 60px 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .ga-illustration-bg {
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .ga-floating-icon {
        position: absolute;
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        animation: float 6s ease-in-out infinite;
    }

    .ga-floating-icon .dashicons {
        font-size: 24px;
        width: 24px;
        height: 24px;
        color: #fff;
    }

    .ga-float-1 { top: 20%; left: 15%; animation-delay: 0s; }
    .ga-float-2 { top: 60%; right: 20%; animation-delay: 2s; }
    .ga-float-3 { bottom: 20%; left: 25%; animation-delay: 4s; }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    .ga-main-icon {
        position: relative;
        width: 140px;
        height: 140px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(20px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }

    .ga-main-icon .dashicons {
        font-size: 70px;
        width: 70px;
        height: 70px;
        color: #fff;
    }

    /* Contenido */
    .ga-registro-content {
        padding: 50px;
    }

    .ga-registro-content h1 {
        margin: 0 0 12px 0;
        font-size: 32px;
        font-weight: 800;
        color: var(--ga-neutral-900);
        letter-spacing: -0.5px;
    }

    .ga-registro-subtitle {
        font-size: 16px;
        color: var(--ga-neutral-500);
        line-height: 1.6;
        margin: 0 0 32px 0;
    }

    /* Beneficios */
    .ga-beneficios-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 36px;
    }

    .ga-beneficio-item {
        display: flex;
        gap: 14px;
    }

    .ga-beneficio-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: var(--ga-primary-light);
        border-radius: 12px;
        flex-shrink: 0;
    }

    .ga-beneficio-icon .dashicons {
        font-size: 22px;
        width: 22px;
        height: 22px;
        color: var(--ga-primary-dark);
    }

    .ga-beneficio-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .ga-beneficio-text strong {
        font-size: 14px;
        font-weight: 600;
        color: var(--ga-neutral-800);
    }

    .ga-beneficio-text span {
        font-size: 13px;
        color: var(--ga-neutral-500);
    }

    /* Actions */
    .ga-registro-actions {
        text-align: center;
    }

    .ga-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ga-btn .dashicons {
        font-size: 20px;
        width: 20px;
        height: 20px;
    }

    .ga-btn-primary {
        background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(16,185,129,0.35);
    }

    .ga-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(16,185,129,0.45);
        color: #fff;
    }

    .ga-btn-lg {
        padding: 18px 40px;
        font-size: 16px;
    }

    .ga-registro-note {
        margin: 20px 0 0 0;
        font-size: 14px;
        color: var(--ga-neutral-500);
    }

    .ga-registro-note a {
        color: var(--ga-primary);
        text-decoration: none;
        font-weight: 600;
    }

    .ga-registro-note a:hover {
        color: var(--ga-primary-dark);
    }

    /* Footer */
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

    /* Responsive */
    @media (max-width: 900px) {
        .ga-registro-card {
            grid-template-columns: 1fr;
            max-width: 550px;
        }

        .ga-registro-illustration {
            padding: 40px;
            min-height: 200px;
        }

        .ga-main-icon {
            width: 100px;
            height: 100px;
        }

        .ga-main-icon .dashicons {
            font-size: 50px;
            width: 50px;
            height: 50px;
        }

        .ga-registro-content {
            padding: 36px;
        }

        .ga-registro-content h1 {
            font-size: 26px;
        }

        .ga-beneficios-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .ga-registro-aplicante {
            padding: 20px 16px;
        }

        .ga-registro-content {
            padding: 28px 24px;
        }

        .ga-registro-content h1 {
            font-size: 22px;
        }

        .ga-btn-lg {
            padding: 14px 28px;
            font-size: 14px;
        }
    }
    </style>
    <?php
    get_footer();
    exit;
}

// =========================================================================
// APLICANTE ENCONTRADO - CARGAR DATOS DEL DASHBOARD
// =========================================================================

global $wpdb;

// =========================================================================
// TABLAS DE BASE DE DATOS
// =========================================================================
$table_aplicaciones = $wpdb->prefix . 'ga_aplicaciones_orden';
$table_ordenes = $wpdb->prefix . 'ga_ordenes_trabajo';

// =========================================================================
// OBTENER ESTADISTICAS DE APLICACIONES
// Contamos las aplicaciones por estado para mostrar metricas
// =========================================================================

// Total de aplicaciones enviadas
$total_aplicaciones = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_aplicaciones} WHERE aplicante_id = %d",
    $aplicante->id
));

// Aplicaciones en revision (esperando respuesta)
$en_revision = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_aplicaciones} WHERE aplicante_id = %d AND estado = 'EN_REVISION'",
    $aplicante->id
));

// Aplicaciones aceptadas/contratadas
$aceptadas = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_aplicaciones} WHERE aplicante_id = %d AND estado IN ('ACEPTADA', 'CONTRATADO')",
    $aplicante->id
));

// Aplicaciones rechazadas
$rechazadas = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_aplicaciones} WHERE aplicante_id = %d AND estado = 'RECHAZADA'",
    $aplicante->id
));

// =========================================================================
// OBTENER APLICACIONES EN REVISION (ultimas 5)
// Estas son las que estan pendientes de respuesta del empleador
// =========================================================================
$aplicaciones_revision = $wpdb->get_results($wpdb->prepare(
    "SELECT a.*, o.codigo as orden_codigo, o.titulo as orden_titulo, o.presupuesto_max
     FROM {$table_aplicaciones} a
     LEFT JOIN {$table_ordenes} o ON a.orden_id = o.id
     WHERE a.aplicante_id = %d AND a.estado = 'EN_REVISION'
     ORDER BY a.created_at DESC
     LIMIT 5",
    $aplicante->id
));

// =========================================================================
// OBTENER ORDENES RECOMENDADAS
// Buscamos ordenes PUBLICADAS que coincidan con las habilidades del aplicante
// =========================================================================
$ordenes_recomendadas = array();

// Decodificar habilidades del aplicante (JSON)
$habilidades_aplicante = array();
if (!empty($aplicante->habilidades)) {
    $decoded = json_decode($aplicante->habilidades, true);
    if (is_array($decoded)) {
        $habilidades_aplicante = array_map('strtolower', $decoded);
    }
}

// Si el aplicante tiene habilidades, buscar ordenes que coincidan
if (!empty($habilidades_aplicante)) {
    // Construir condiciones LIKE para cada habilidad
    $like_conditions = array();
    $params = array();

    foreach ($habilidades_aplicante as $habilidad) {
        $like_conditions[] = "LOWER(o.habilidades_requeridas) LIKE %s";
        $params[] = '%' . $wpdb->esc_like($habilidad) . '%';
    }

    $where_habilidades = '(' . implode(' OR ', $like_conditions) . ')';

    // Buscar ordenes publicadas que coincidan y que el aplicante no haya aplicado ya
    $sql = "SELECT o.*
            FROM {$table_ordenes} o
            WHERE o.estado = 'PUBLICADA'
            AND {$where_habilidades}
            AND o.id NOT IN (
                SELECT orden_id FROM {$table_aplicaciones} WHERE aplicante_id = %d
            )
            ORDER BY o.created_at DESC
            LIMIT 3";

    $params[] = $aplicante->id;
    $ordenes_recomendadas = $wpdb->get_results($wpdb->prepare($sql, $params));
}

// Si no hay suficientes ordenes por habilidades, completar con recientes
if (count($ordenes_recomendadas) < 3) {
    $ids_excluir = array($aplicante->id);
    if (!empty($ordenes_recomendadas)) {
        foreach ($ordenes_recomendadas as $o) {
            $ids_excluir[] = $o->id;
        }
    }

    $placeholders = implode(',', array_fill(0, count($ids_excluir), '%d'));
    $limit = 3 - count($ordenes_recomendadas);

    $ordenes_adicionales = $wpdb->get_results($wpdb->prepare(
        "SELECT o.*
         FROM {$table_ordenes} o
         WHERE o.estado = 'PUBLICADA'
         AND o.id NOT IN (
             SELECT orden_id FROM {$table_aplicaciones} WHERE aplicante_id = %d
         )
         ORDER BY o.created_at DESC
         LIMIT %d",
        $aplicante->id,
        $limit
    ));

    $ordenes_recomendadas = array_merge($ordenes_recomendadas, $ordenes_adicionales);
}

// =========================================================================
// VERIFICAR DOCUMENTOS FALTANTES (para aplicantes PENDIENTES)
// =========================================================================
$documentos_faltantes = array();
if ($aplicante->estado === 'PENDIENTE_VERIFICACION') {
    if (empty($aplicante->cv_archivo)) {
        $documentos_faltantes[] = array(
            'nombre' => __('Curriculum Vitae (CV)', 'gestionadmin-wolk'),
            'campo'  => 'cv_archivo',
            'icono'  => 'media-document'
        );
    }
    if (empty($aplicante->documento_frente)) {
        $documentos_faltantes[] = array(
            'nombre' => __('Documento de Identidad (Frente)', 'gestionadmin-wolk'),
            'campo'  => 'documento_frente',
            'icono'  => 'id'
        );
    }
    if (empty($aplicante->documento_reverso)) {
        $documentos_faltantes[] = array(
            'nombre' => __('Documento de Identidad (Reverso)', 'gestionadmin-wolk'),
            'campo'  => 'documento_reverso',
            'icono'  => 'id-alt'
        );
    }
}

// =========================================================================
// URLS DE NAVEGACION
// =========================================================================
$url_dashboard = home_url('/mi-cuenta/');
$url_aplicaciones = home_url('/mi-cuenta/aplicaciones/');
$url_marketplace = home_url('/trabajo/');
$url_pagos = home_url('/mi-cuenta/pagos/');
$url_perfil = home_url('/mi-cuenta/perfil/');

// =========================================================================
// OBTENER AVATAR DEL APLICANTE
// =========================================================================
$avatar_url = get_avatar_url($aplicante->email, array('size' => 120, 'default' => 'identicon'));

// =========================================================================
// RENDER: Header del tema WordPress
// =========================================================================
get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-aplicante ga-portal-dashboard">
    <div class="ga-container">

        <!-- =====================================================================
             NAVEGACION DEL PORTAL
             Barra de navegacion consistente con todos los portales
        ====================================================================== -->
        <nav class="ga-portal-nav" role="navigation" aria-label="<?php esc_attr_e('Navegacion del portal', 'gestionadmin-wolk'); ?>">
            <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-nav-item active">
                <span class="dashicons dashicons-dashboard"></span>
                <span class="ga-nav-text"><?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_aplicaciones); ?>" class="ga-nav-item">
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
             TARJETA DE BIENVENIDA CON ESTADO DE VERIFICACION
             Muestra el estado prominente del aplicante
        ====================================================================== -->
        <header class="ga-welcome-card <?php echo 'ga-estado-' . strtolower(str_replace('_', '-', $aplicante->estado)); ?>">
            <div class="ga-welcome-bg"></div>
            <div class="ga-welcome-content">
                <div class="ga-welcome-avatar">
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($aplicante->nombre_completo); ?>">
                </div>
                <div class="ga-welcome-info">
                    <div class="ga-welcome-greeting">
                        <span class="ga-greeting-label"><?php esc_html_e('Bienvenido de vuelta', 'gestionadmin-wolk'); ?></span>
                        <h1><?php echo esc_html($aplicante->nombre_completo); ?></h1>
                    </div>
                    <div class="ga-welcome-meta">
                        <?php if (!empty($aplicante->titulo_profesional)): ?>
                        <span class="ga-meta-item">
                            <span class="dashicons dashicons-businessman"></span>
                            <?php echo esc_html($aplicante->titulo_profesional); ?>
                        </span>
                        <?php endif; ?>
                        <span class="ga-meta-item">
                            <span class="dashicons dashicons-email"></span>
                            <?php echo esc_html($aplicante->email); ?>
                        </span>
                    </div>
                </div>

                <!-- Estado de verificacion prominente -->
                <div class="ga-verification-status">
                    <?php if ($aplicante->estado === 'VERIFICADO'): ?>
                        <div class="ga-status-badge ga-status-verified">
                            <div class="ga-status-icon">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </div>
                            <div class="ga-status-text">
                                <span class="ga-status-label"><?php esc_html_e('Cuenta Verificada', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-status-desc"><?php esc_html_e('Puedes aplicar a trabajos', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                    <?php elseif ($aplicante->estado === 'PENDIENTE_VERIFICACION'): ?>
                        <div class="ga-status-badge ga-status-pending">
                            <div class="ga-status-icon">
                                <span class="dashicons dashicons-clock"></span>
                            </div>
                            <div class="ga-status-text">
                                <span class="ga-status-label"><?php esc_html_e('Verificacion Pendiente', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-status-desc"><?php esc_html_e('Completa tu perfil', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                    <?php elseif ($aplicante->estado === 'RECHAZADO'): ?>
                        <div class="ga-status-badge ga-status-rejected">
                            <div class="ga-status-icon">
                                <span class="dashicons dashicons-dismiss"></span>
                            </div>
                            <div class="ga-status-text">
                                <span class="ga-status-label"><?php esc_html_e('Verificacion Rechazada', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-status-desc"><?php esc_html_e('Contacta a soporte', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="ga-status-badge ga-status-suspended">
                            <div class="ga-status-icon">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="ga-status-text">
                                <span class="ga-status-label"><?php esc_html_e('Cuenta Suspendida', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-status-desc"><?php esc_html_e('Contacta a soporte', 'gestionadmin-wolk'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- =====================================================================
             ALERTA DE DOCUMENTOS FALTANTES
             Solo se muestra si el aplicante esta pendiente y le faltan documentos
        ====================================================================== -->
        <?php if ($aplicante->estado === 'PENDIENTE_VERIFICACION' && !empty($documentos_faltantes)): ?>
        <div class="ga-alert ga-alert-warning" role="alert">
            <div class="ga-alert-icon">
                <span class="dashicons dashicons-info-outline"></span>
            </div>
            <div class="ga-alert-content">
                <strong><?php esc_html_e('Completa tu perfil para ser verificado', 'gestionadmin-wolk'); ?></strong>
                <p><?php esc_html_e('Para que tu cuenta sea verificada y puedas aplicar a trabajos, necesitas subir los siguientes documentos:', 'gestionadmin-wolk'); ?></p>
                <ul class="ga-docs-list">
                    <?php foreach ($documentos_faltantes as $doc): ?>
                    <li>
                        <span class="dashicons dashicons-<?php echo esc_attr($doc['icono']); ?>"></span>
                        <?php echo esc_html($doc['nombre']); ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-alert-action">
                <?php esc_html_e('Completar Perfil', 'gestionadmin-wolk'); ?>
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>
        <?php endif; ?>

        <!-- =====================================================================
             ESTADISTICAS DE APLICACIONES
             Tarjetas con metricas clave del aplicante
        ====================================================================== -->
        <section class="ga-stats-section" aria-label="<?php esc_attr_e('Estadisticas', 'gestionadmin-wolk'); ?>">
            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-total">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value"><?php echo esc_html($total_aplicaciones); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Total Enviadas', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-review">
                    <span class="dashicons dashicons-visibility"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value"><?php echo esc_html($en_revision); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('En Revision', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-accepted">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value"><?php echo esc_html($aceptadas); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Aceptadas', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-rejected">
                    <span class="dashicons dashicons-dismiss"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value"><?php echo esc_html($rechazadas); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Rechazadas', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </section>

        <!-- =====================================================================
             GRID DE CONTENIDO PRINCIPAL
             Dos columnas: aplicaciones en revision + ordenes recomendadas
        ====================================================================== -->
        <div class="ga-dashboard-grid">

            <!-- Columna Izquierda: Aplicaciones en revision -->
            <section class="ga-dashboard-section" aria-label="<?php esc_attr_e('Aplicaciones en revision', 'gestionadmin-wolk'); ?>">
                <div class="ga-section-header">
                    <div class="ga-section-title">
                        <div class="ga-section-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <h2><?php esc_html_e('Aplicaciones en Revision', 'gestionadmin-wolk'); ?></h2>
                    </div>
                    <a href="<?php echo esc_url($url_aplicaciones); ?>" class="ga-section-link">
                        <?php esc_html_e('Ver todas', 'gestionadmin-wolk'); ?>
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                    </a>
                </div>

                <div class="ga-section-body">
                    <?php if (empty($aplicaciones_revision)): ?>
                        <div class="ga-empty-state">
                            <div class="ga-empty-icon">
                                <span class="dashicons dashicons-portfolio"></span>
                            </div>
                            <h3><?php esc_html_e('Sin aplicaciones pendientes', 'gestionadmin-wolk'); ?></h3>
                            <p><?php esc_html_e('No tienes aplicaciones esperando revision. Explora el marketplace para encontrar nuevas oportunidades.', 'gestionadmin-wolk'); ?></p>
                            <a href="<?php echo esc_url($url_marketplace); ?>" class="ga-btn ga-btn-outline">
                                <span class="dashicons dashicons-search"></span>
                                <?php esc_html_e('Explorar Trabajos', 'gestionadmin-wolk'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="ga-applications-list">
                            <?php foreach ($aplicaciones_revision as $app): ?>
                            <article class="ga-application-card">
                                <div class="ga-application-header">
                                    <span class="ga-application-code"><?php echo esc_html($app->orden_codigo); ?></span>
                                    <span class="ga-application-status">
                                        <span class="ga-status-dot"></span>
                                        <?php esc_html_e('En Revision', 'gestionadmin-wolk'); ?>
                                    </span>
                                </div>
                                <h3 class="ga-application-title"><?php echo esc_html($app->orden_titulo); ?></h3>
                                <div class="ga-application-meta">
                                    <?php if ($app->presupuesto_max > 0): ?>
                                    <span class="ga-meta-budget">
                                        <span class="dashicons dashicons-money-alt"></span>
                                        $<?php echo esc_html(number_format($app->presupuesto_max, 0)); ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="ga-meta-date">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <?php echo esc_html(human_time_diff(strtotime($app->created_at), current_time('timestamp'))); ?>
                                    </span>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Columna Derecha: Ordenes recomendadas -->
            <section class="ga-dashboard-section" aria-label="<?php esc_attr_e('Ordenes recomendadas', 'gestionadmin-wolk'); ?>">
                <div class="ga-section-header">
                    <div class="ga-section-title">
                        <div class="ga-section-icon ga-icon-recommended">
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <h2><?php esc_html_e('Ordenes Recomendadas', 'gestionadmin-wolk'); ?></h2>
                    </div>
                    <a href="<?php echo esc_url($url_marketplace); ?>" class="ga-section-link">
                        <?php esc_html_e('Ver marketplace', 'gestionadmin-wolk'); ?>
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                    </a>
                </div>

                <div class="ga-section-body">
                    <?php if (empty($ordenes_recomendadas)): ?>
                        <div class="ga-empty-state">
                            <div class="ga-empty-icon">
                                <span class="dashicons dashicons-store"></span>
                            </div>
                            <h3><?php esc_html_e('Sin ordenes disponibles', 'gestionadmin-wolk'); ?></h3>
                            <p><?php esc_html_e('No hay ordenes publicadas en este momento. Vuelve pronto para ver nuevas oportunidades.', 'gestionadmin-wolk'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="ga-orders-list">
                            <?php foreach ($ordenes_recomendadas as $orden):
                                // Decodificar habilidades de la orden
                                $habilidades_orden = array();
                                if (!empty($orden->habilidades_requeridas)) {
                                    $habilidades_orden = json_decode($orden->habilidades_requeridas, true) ?: array();
                                }
                            ?>
                            <article class="ga-order-card">
                                <div class="ga-order-header">
                                    <span class="ga-order-code"><?php echo esc_html($orden->codigo); ?></span>
                                    <?php if (!empty($orden->categoria)): ?>
                                    <span class="ga-order-category"><?php echo esc_html($orden->categoria); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="ga-order-title"><?php echo esc_html($orden->titulo); ?></h3>
                                <?php if (!empty($habilidades_orden)): ?>
                                <div class="ga-order-skills">
                                    <?php
                                    $skills_to_show = array_slice($habilidades_orden, 0, 3);
                                    foreach ($skills_to_show as $skill):
                                    ?>
                                    <span class="ga-skill-tag"><?php echo esc_html($skill); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($habilidades_orden) > 3): ?>
                                    <span class="ga-skill-more">+<?php echo count($habilidades_orden) - 3; ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <div class="ga-order-footer">
                                    <div class="ga-order-budget">
                                        <?php if ($orden->presupuesto_max > 0): ?>
                                        <span class="ga-budget-value">$<?php echo esc_html(number_format($orden->presupuesto_max, 0)); ?></span>
                                        <span class="ga-budget-label"><?php esc_html_e('Presupuesto', 'gestionadmin-wolk'); ?></span>
                                        <?php else: ?>
                                        <span class="ga-budget-value"><?php esc_html_e('A convenir', 'gestionadmin-wolk'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo esc_url(home_url('/trabajo/' . $orden->codigo . '/')); ?>" class="ga-btn ga-btn-sm ga-btn-primary">
                                        <?php esc_html_e('Ver Detalles', 'gestionadmin-wolk'); ?>
                                    </a>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </div>

        <!-- =====================================================================
             ACCESOS RAPIDOS
             Tarjetas de navegacion rapida a acciones comunes
        ====================================================================== -->
        <section class="ga-quick-actions" aria-label="<?php esc_attr_e('Accesos rapidos', 'gestionadmin-wolk'); ?>">
            <a href="<?php echo esc_url($url_marketplace); ?>" class="ga-quick-card">
                <div class="ga-quick-icon ga-quick-marketplace">
                    <span class="dashicons dashicons-store"></span>
                </div>
                <div class="ga-quick-content">
                    <h3><?php esc_html_e('Explorar Marketplace', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('Encuentra nuevas oportunidades de trabajo', 'gestionadmin-wolk'); ?></p>
                </div>
                <span class="ga-quick-arrow">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                </span>
            </a>

            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-quick-card">
                <div class="ga-quick-icon ga-quick-profile">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <div class="ga-quick-content">
                    <h3><?php esc_html_e('Actualizar Perfil', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('Mejora tu visibilidad con un perfil completo', 'gestionadmin-wolk'); ?></p>
                </div>
                <span class="ga-quick-arrow">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                </span>
            </a>

            <a href="<?php echo esc_url($url_pagos); ?>" class="ga-quick-card">
                <div class="ga-quick-icon ga-quick-payments">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div class="ga-quick-content">
                    <h3><?php esc_html_e('Mis Pagos', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('Consulta tu historial de pagos recibidos', 'gestionadmin-wolk'); ?></p>
                </div>
                <span class="ga-quick-arrow">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                </span>
            </a>
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
     Sistema de diseno profesional con variables CSS para consistencia
========================================================================== -->
<style>
/* =========================================================================
   PORTAL APLICANTE - DASHBOARD
   Enterprise-Grade Professional Design System
   Inspirado en plataformas como Upwork, Fiverr, Toptal
   ========================================================================= */

/* -------------------------------------------------------------------------
   VARIABLES CSS
   Sistema de tokens de diseno centralizado para facil mantenimiento
   ------------------------------------------------------------------------- */
:root {
    /* Colores primarios - Verde corporativo */
    --ga-primary: #10b981;
    --ga-primary-dark: #059669;
    --ga-primary-light: #d1fae5;

    /* Colores secundarios - Morado para acentos */
    --ga-secondary: #6366f1;
    --ga-secondary-light: #e0e7ff;

    /* Colores semanticos */
    --ga-warning: #f59e0b;
    --ga-warning-light: #fef3c7;
    --ga-danger: #ef4444;
    --ga-danger-light: #fef2f2;
    --ga-success: #22c55e;
    --ga-success-light: #dcfce7;
    --ga-info: #0ea5e9;
    --ga-info-light: #e0f2fe;

    /* Escala de grises neutral */
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

    /* Sombras con diferentes intensidades */
    --ga-shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
    --ga-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    --ga-shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    --ga-shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    --ga-shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.25);

    /* Border radius - Escala consistente */
    --ga-radius-sm: 6px;
    --ga-radius: 10px;
    --ga-radius-lg: 14px;
    --ga-radius-xl: 20px;
    --ga-radius-2xl: 24px;
}

/* -------------------------------------------------------------------------
   CONTENEDOR PRINCIPAL
   ------------------------------------------------------------------------- */
.ga-portal-dashboard {
    min-height: 100vh;
    padding: 28px 24px;
    background: linear-gradient(180deg, var(--ga-neutral-50) 0%, var(--ga-neutral-100) 100%);
}

.ga-container {
    max-width: 1320px;
    margin: 0 auto;
}

/* -------------------------------------------------------------------------
   NAVEGACION DEL PORTAL
   Barra de navegacion con efecto glass morphism
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

/* Efecto de fondo animado para item activo */
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
   TARJETA DE BIENVENIDA
   Header hero con avatar y estado de verificacion
   ------------------------------------------------------------------------- */
.ga-welcome-card {
    position: relative;
    background: #fff;
    border-radius: var(--ga-radius-2xl);
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: var(--ga-shadow-md);
    border: 1px solid var(--ga-neutral-200);
}

/* Fondo decorativo segun estado */
.ga-welcome-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 120px;
    background: linear-gradient(135deg, var(--ga-primary) 0%, #0d9488 50%, var(--ga-secondary) 100%);
}

/* Patron decorativo SVG */
.ga-welcome-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* Colores de fondo segun estado */
.ga-estado-pendiente-verificacion .ga-welcome-bg {
    background: linear-gradient(135deg, var(--ga-warning) 0%, #d97706 50%, #ea580c 100%);
}

.ga-estado-rechazado .ga-welcome-bg,
.ga-estado-suspendido .ga-welcome-bg {
    background: linear-gradient(135deg, var(--ga-danger) 0%, #dc2626 50%, #991b1b 100%);
}

.ga-welcome-content {
    position: relative;
    display: flex;
    align-items: flex-end;
    gap: 24px;
    padding: 80px 32px 28px;
}

/* Avatar del aplicante */
.ga-welcome-avatar {
    flex-shrink: 0;
}

.ga-welcome-avatar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: var(--ga-shadow-lg);
    object-fit: cover;
    background: #fff;
}

/* Informacion de bienvenida */
.ga-welcome-info {
    flex: 1;
    min-width: 0;
    padding-bottom: 4px;
}

.ga-greeting-label {
    display: block;
    font-size: 13px;
    color: var(--ga-neutral-500);
    margin-bottom: 4px;
}

.ga-welcome-greeting h1 {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    letter-spacing: -0.5px;
}

.ga-welcome-meta {
    display: flex;
    gap: 20px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.ga-welcome-meta .ga-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: var(--ga-neutral-500);
}

.ga-welcome-meta .ga-meta-item .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: var(--ga-neutral-400);
}

/* Badge de estado de verificacion */
.ga-verification-status {
    flex-shrink: 0;
}

.ga-status-badge {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 24px;
    border-radius: var(--ga-radius-xl);
    background: #fff;
    box-shadow: var(--ga-shadow-md);
    border: 2px solid;
}

.ga-status-verified {
    border-color: var(--ga-success);
    background: linear-gradient(135deg, #fff 0%, var(--ga-success-light) 100%);
}

.ga-status-pending {
    border-color: var(--ga-warning);
    background: linear-gradient(135deg, #fff 0%, var(--ga-warning-light) 100%);
}

.ga-status-rejected,
.ga-status-suspended {
    border-color: var(--ga-danger);
    background: linear-gradient(135deg, #fff 0%, var(--ga-danger-light) 100%);
}

.ga-status-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
}

.ga-status-verified .ga-status-icon {
    background: var(--ga-success);
    box-shadow: 0 8px 20px rgba(34,197,94,0.3);
}

.ga-status-pending .ga-status-icon {
    background: var(--ga-warning);
    box-shadow: 0 8px 20px rgba(245,158,11,0.3);
}

.ga-status-rejected .ga-status-icon,
.ga-status-suspended .ga-status-icon {
    background: var(--ga-danger);
    box-shadow: 0 8px 20px rgba(239,68,68,0.3);
}

.ga-status-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #fff;
}

.ga-status-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ga-status-label {
    font-size: 15px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-status-desc {
    font-size: 13px;
    color: var(--ga-neutral-500);
}

/* -------------------------------------------------------------------------
   ALERTA DE DOCUMENTOS FALTANTES
   ------------------------------------------------------------------------- */
.ga-alert {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 24px;
    border-radius: var(--ga-radius-xl);
    margin-bottom: 28px;
    border: 1px solid;
}

.ga-alert-warning {
    background: linear-gradient(135deg, var(--ga-warning-light) 0%, #fffbeb 100%);
    border-color: #fcd34d;
}

.ga-alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    background: var(--ga-warning);
    border-radius: var(--ga-radius-lg);
    flex-shrink: 0;
    box-shadow: 0 6px 20px rgba(245,158,11,0.3);
}

.ga-alert-icon .dashicons {
    font-size: 26px;
    width: 26px;
    height: 26px;
    color: #fff;
}

.ga-alert-content {
    flex: 1;
}

.ga-alert-content strong {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 6px;
}

.ga-alert-content p {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #b45309;
    line-height: 1.5;
}

.ga-docs-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.ga-docs-list li {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: rgba(255,255,255,0.7);
    border-radius: var(--ga-radius);
    font-size: 13px;
    font-weight: 500;
    color: #92400e;
}

.ga-docs-list .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: var(--ga-warning);
}

.ga-alert-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    background: var(--ga-warning);
    color: #fff;
    border-radius: var(--ga-radius);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.25s;
    box-shadow: 0 6px 20px rgba(245,158,11,0.3);
    white-space: nowrap;
    align-self: center;
}

.ga-alert-action:hover {
    background: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(245,158,11,0.4);
    color: #fff;
}

.ga-alert-action .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* -------------------------------------------------------------------------
   ESTADISTICAS
   Grid de tarjetas con metricas
   ------------------------------------------------------------------------- */
.ga-stats-section {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.ga-stat-card {
    display: flex;
    align-items: center;
    gap: 18px;
    background: #fff;
    padding: 24px;
    border-radius: var(--ga-radius-xl);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ga-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--ga-shadow-lg);
}

.ga-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: var(--ga-radius-lg);
    flex-shrink: 0;
}

.ga-stat-icon .dashicons {
    font-size: 26px;
    width: 26px;
    height: 26px;
    color: #fff;
}

.ga-stat-total {
    background: linear-gradient(135deg, var(--ga-secondary) 0%, #4f46e5 100%);
    box-shadow: 0 8px 24px rgba(99,102,241,0.3);
}

.ga-stat-review {
    background: linear-gradient(135deg, var(--ga-warning) 0%, #d97706 100%);
    box-shadow: 0 8px 24px rgba(245,158,11,0.3);
}

.ga-stat-accepted {
    background: linear-gradient(135deg, var(--ga-success) 0%, #16a34a 100%);
    box-shadow: 0 8px 24px rgba(34,197,94,0.3);
}

.ga-stat-rejected {
    background: linear-gradient(135deg, var(--ga-danger) 0%, #dc2626 100%);
    box-shadow: 0 8px 24px rgba(239,68,68,0.3);
}

.ga-stat-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ga-stat-value {
    font-size: 32px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    line-height: 1;
    letter-spacing: -1px;
}

.ga-stat-label {
    font-size: 13px;
    color: var(--ga-neutral-500);
    font-weight: 500;
}

/* -------------------------------------------------------------------------
   GRID DEL DASHBOARD
   Layout de dos columnas
   ------------------------------------------------------------------------- */
.ga-dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 28px;
}

/* Seccion generica */
.ga-dashboard-section {
    background: #fff;
    border-radius: var(--ga-radius-xl);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    overflow: hidden;
}

.ga-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px;
    border-bottom: 1px solid var(--ga-neutral-100);
}

.ga-section-title {
    display: flex;
    align-items: center;
    gap: 14px;
}

.ga-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    border-radius: var(--ga-radius);
    box-shadow: 0 4px 14px rgba(16,185,129,0.25);
}

.ga-icon-recommended {
    background: linear-gradient(135deg, var(--ga-warning) 0%, #d97706 100%);
    box-shadow: 0 4px 14px rgba(245,158,11,0.25);
}

.ga-section-icon .dashicons {
    font-size: 22px;
    width: 22px;
    height: 22px;
    color: #fff;
}

.ga-section-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-section-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--ga-primary);
    text-decoration: none;
    transition: all 0.2s;
}

.ga-section-link:hover {
    color: var(--ga-primary-dark);
    gap: 10px;
}

.ga-section-link .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.ga-section-body {
    padding: 24px;
}

/* Estado vacio */
.ga-empty-state {
    text-align: center;
    padding: 40px 20px;
}

.ga-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: var(--ga-neutral-100);
    border-radius: 50%;
    margin: 0 auto 20px;
}

.ga-empty-icon .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    color: var(--ga-neutral-400);
}

.ga-empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--ga-neutral-700);
}

.ga-empty-state p {
    margin: 0 0 20px 0;
    font-size: 14px;
    color: var(--ga-neutral-500);
    line-height: 1.5;
}

/* -------------------------------------------------------------------------
   LISTA DE APLICACIONES EN REVISION
   ------------------------------------------------------------------------- */
.ga-applications-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ga-application-card {
    padding: 20px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius-lg);
    border: 1px solid var(--ga-neutral-200);
    transition: all 0.25s;
}

.ga-application-card:hover {
    background: #fff;
    border-color: var(--ga-primary);
    box-shadow: var(--ga-shadow);
}

.ga-application-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.ga-application-code {
    font-size: 12px;
    font-weight: 700;
    font-family: 'SF Mono', 'Consolas', monospace;
    color: var(--ga-neutral-500);
    background: var(--ga-neutral-200);
    padding: 4px 10px;
    border-radius: var(--ga-radius-sm);
}

.ga-application-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--ga-warning);
}

.ga-status-dot {
    width: 8px;
    height: 8px;
    background: var(--ga-warning);
    border-radius: 50%;
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.2); }
}

.ga-application-title {
    margin: 0 0 10px 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--ga-neutral-800);
    line-height: 1.4;
}

.ga-application-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.ga-application-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--ga-neutral-500);
}

.ga-application-meta .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    color: var(--ga-neutral-400);
}

/* -------------------------------------------------------------------------
   LISTA DE ORDENES RECOMENDADAS
   ------------------------------------------------------------------------- */
.ga-orders-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ga-order-card {
    padding: 20px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius-lg);
    border: 1px solid var(--ga-neutral-200);
    transition: all 0.25s;
}

.ga-order-card:hover {
    background: #fff;
    border-color: var(--ga-warning);
    box-shadow: var(--ga-shadow);
}

.ga-order-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.ga-order-code {
    font-size: 11px;
    font-weight: 700;
    font-family: 'SF Mono', 'Consolas', monospace;
    color: var(--ga-neutral-500);
    background: var(--ga-neutral-200);
    padding: 4px 8px;
    border-radius: var(--ga-radius-sm);
}

.ga-order-category {
    font-size: 11px;
    font-weight: 600;
    color: var(--ga-secondary);
    background: var(--ga-secondary-light);
    padding: 4px 10px;
    border-radius: 20px;
}

.ga-order-title {
    margin: 0 0 12px 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--ga-neutral-800);
    line-height: 1.4;
}

.ga-order-skills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}

.ga-skill-tag {
    display: inline-block;
    padding: 4px 10px;
    background: var(--ga-primary-light);
    color: var(--ga-primary-dark);
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
}

.ga-skill-more {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    background: var(--ga-neutral-200);
    color: var(--ga-neutral-600);
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
}

.ga-order-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid var(--ga-neutral-200);
}

.ga-order-budget {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ga-budget-value {
    font-size: 18px;
    font-weight: 800;
    color: var(--ga-neutral-900);
}

.ga-budget-label {
    font-size: 11px;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.3px;
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
    color: var(--ga-primary);
    border: 2px solid var(--ga-primary);
}

.ga-btn-outline:hover {
    background: var(--ga-primary);
    color: #fff;
}

/* -------------------------------------------------------------------------
   ACCESOS RAPIDOS
   ------------------------------------------------------------------------- */
.ga-quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.ga-quick-card {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 24px;
    background: #fff;
    border-radius: var(--ga-radius-xl);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ga-quick-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--ga-shadow-lg);
    border-color: var(--ga-primary);
}

.ga-quick-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: var(--ga-radius-lg);
    flex-shrink: 0;
}

.ga-quick-icon .dashicons {
    font-size: 26px;
    width: 26px;
    height: 26px;
    color: #fff;
}

.ga-quick-marketplace {
    background: linear-gradient(135deg, var(--ga-secondary) 0%, #4f46e5 100%);
    box-shadow: 0 8px 24px rgba(99,102,241,0.3);
}

.ga-quick-profile {
    background: linear-gradient(135deg, var(--ga-info) 0%, #0284c7 100%);
    box-shadow: 0 8px 24px rgba(14,165,233,0.3);
}

.ga-quick-payments {
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    box-shadow: 0 8px 24px rgba(16,185,129,0.3);
}

.ga-quick-content {
    flex: 1;
    min-width: 0;
}

.ga-quick-content h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-quick-content p {
    margin: 0;
    font-size: 13px;
    color: var(--ga-neutral-500);
}

.ga-quick-arrow {
    flex-shrink: 0;
    color: var(--ga-neutral-300);
    transition: all 0.25s;
}

.ga-quick-card:hover .ga-quick-arrow {
    color: var(--ga-primary);
    transform: translateX(4px);
}

.ga-quick-arrow .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}

/* -------------------------------------------------------------------------
   FOOTER
   ------------------------------------------------------------------------- */
.ga-portal-footer {
    text-align: center;
    padding: 32px 20px;
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
    .ga-stats-section {
        grid-template-columns: repeat(2, 1fr);
    }

    .ga-dashboard-grid {
        grid-template-columns: 1fr;
    }

    .ga-quick-actions {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .ga-portal-dashboard {
        padding: 20px 16px;
    }

    /* Navegacion movil */
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

    /* Welcome card mobile */
    .ga-welcome-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 70px 20px 24px;
        gap: 16px;
    }

    .ga-welcome-avatar img {
        width: 80px;
        height: 80px;
    }

    .ga-welcome-greeting h1 {
        font-size: 22px;
    }

    .ga-welcome-meta {
        justify-content: center;
        gap: 12px;
    }

    .ga-verification-status {
        width: 100%;
    }

    .ga-status-badge {
        width: 100%;
        justify-content: center;
        padding: 14px 20px;
    }

    /* Stats mobile */
    .ga-stats-section {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .ga-stat-card {
        flex-direction: column;
        text-align: center;
        gap: 12px;
        padding: 20px;
    }

    .ga-stat-value {
        font-size: 28px;
    }

    /* Alert mobile */
    .ga-alert {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }

    .ga-alert-action {
        width: 100%;
        justify-content: center;
    }

    /* Sections mobile */
    .ga-section-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    .ga-section-body {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .ga-welcome-bg {
        height: 90px;
    }

    .ga-welcome-content {
        padding-top: 50px;
    }

    .ga-welcome-avatar img {
        width: 70px;
        height: 70px;
    }

    .ga-status-badge {
        flex-direction: column;
        gap: 10px;
        text-align: center;
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

    .ga-stat-value {
        font-size: 24px;
    }

    .ga-quick-card {
        padding: 20px;
    }

    .ga-quick-icon {
        width: 48px;
        height: 48px;
    }

    .ga-quick-icon .dashicons {
        font-size: 22px;
        width: 22px;
        height: 22px;
    }
}
</style>

<?php
// =========================================================================
// RENDER: Footer del tema WordPress
// =========================================================================
get_footer();
?>
