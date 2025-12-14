<?php
/**
 * Template: Portal Aplicante - Mi Perfil
 *
 * Pagina de perfil del aplicante con informacion personal, documentos,
 * habilidades, metodo de pago y estado de verificacion.
 *
 * Funcionalidades:
 * - Verificacion de usuario aplicante con GA_Aplicantes::get_by_wp_user()
 * - Tarjeta principal con avatar, nombre, codigo y estado de verificacion
 * - Seccion de documentos: CV, portafolio, documentos de identidad
 * - Seccion de habilidades con tags/badges
 * - Informacion personal: nombre, email, telefono, pais, tipo
 * - Metodo de pago preferido con datos enmascarados
 * - Seccion de seguridad con cambio de contrasena
 * - Mensajes explicativos segun estado de verificacion
 *
 * Tabla principal: wp_ga_aplicantes
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 * @updated    1.15.0 - Mi Perfil rediseño completo (Sprint A3)
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
$wp_user = get_userdata($wp_user_id);

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
                <p><?php esc_html_e('Necesitas registrarte como aplicante para acceder a tu perfil.', 'gestionadmin-wolk'); ?></p>
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
// APLICANTE ENCONTRADO - PREPARAR DATOS
// =========================================================================

// Decodificar habilidades JSON
$habilidades = array();
if (!empty($aplicante->habilidades)) {
    $habilidades = json_decode($aplicante->habilidades, true);
    if (!is_array($habilidades)) {
        $habilidades = array();
    }
}

// Generar codigo del aplicante
$codigo_aplicante = 'APL-' . str_pad($aplicante->id, 4, '0', STR_PAD_LEFT);

// Configuracion de estados de verificacion
$estados_verificacion = array(
    'PENDIENTE_VERIFICACION' => array(
        'label'   => __('Pendiente de Verificacion', 'gestionadmin-wolk'),
        'icon'    => 'clock',
        'bg'      => '#fef3c7',
        'text'    => '#92400e',
        'border'  => '#fcd34d',
        'message' => __('Tu cuenta esta en revision. Asegurate de subir todos los documentos requeridos para acelerar el proceso.', 'gestionadmin-wolk'),
    ),
    'VERIFICADO' => array(
        'label'   => __('Verificado', 'gestionadmin-wolk'),
        'icon'    => 'yes-alt',
        'bg'      => '#dcfce7',
        'text'    => '#166534',
        'border'  => '#86efac',
        'message' => __('Tu cuenta esta verificada. Puedes aplicar a ordenes de trabajo en el marketplace.', 'gestionadmin-wolk'),
    ),
    'RECHAZADO' => array(
        'label'   => __('Rechazado', 'gestionadmin-wolk'),
        'icon'    => 'dismiss',
        'bg'      => '#fef2f2',
        'text'    => '#991b1b',
        'border'  => '#fecaca',
        'message' => __('Tu verificacion fue rechazada. Por favor contacta a soporte para mas informacion.', 'gestionadmin-wolk'),
    ),
    'SUSPENDIDO' => array(
        'label'   => __('Suspendido', 'gestionadmin-wolk'),
        'icon'    => 'warning',
        'bg'      => '#fef2f2',
        'text'    => '#991b1b',
        'border'  => '#fecaca',
        'message' => __('Tu cuenta ha sido suspendida. Contacta a soporte para resolver esta situacion.', 'gestionadmin-wolk'),
    ),
);

$estado_actual = isset($aplicante->estado) ? $aplicante->estado : 'PENDIENTE_VERIFICACION';
$estado_info = isset($estados_verificacion[$estado_actual])
    ? $estados_verificacion[$estado_actual]
    : $estados_verificacion['PENDIENTE_VERIFICACION'];

// Configuracion de tipos de aplicante
$tipos_aplicante = array(
    'PERSONA_NATURAL' => __('Persona Natural', 'gestionadmin-wolk'),
    'EMPRESA'         => __('Empresa', 'gestionadmin-wolk'),
);

// Configuracion de metodos de pago
$metodos_pago_labels = array(
    'BINANCE'       => 'Binance (Crypto)',
    'WISE'          => 'Wise',
    'PAYPAL'        => 'PayPal',
    'PAYONEER'      => 'Payoneer',
    'STRIPE'        => 'Stripe',
    'TRANSFERENCIA' => 'Transferencia Bancaria',
);

// Enmascarar datos de pago (mostrar solo ultimos 4 caracteres)
$datos_pago_display = '';
if (!empty($aplicante->datos_pago)) {
    $datos_pago_raw = $aplicante->datos_pago;
    // Si es JSON, decodificar
    $datos_json = json_decode($datos_pago_raw, true);
    if (is_array($datos_json)) {
        $datos_pago_raw = implode(' | ', array_values($datos_json));
    }
    $longitud = strlen($datos_pago_raw);
    if ($longitud > 4) {
        $datos_pago_display = str_repeat('*', min($longitud - 4, 12)) . substr($datos_pago_raw, -4);
    } else {
        $datos_pago_display = $datos_pago_raw;
    }
}

// Verificar documentos
$tiene_cv = !empty($aplicante->cv_archivo);
$tiene_doc_frente = !empty($aplicante->documento_frente);
$tiene_doc_reverso = !empty($aplicante->documento_reverso);

// URLs de navegacion
$url_dashboard = home_url('/mi-cuenta/');
$url_aplicaciones = home_url('/mi-cuenta/aplicaciones/');
$url_marketplace = home_url('/trabajo/');
$url_pagos = home_url('/mi-cuenta/pagos/');
$url_perfil = home_url('/mi-cuenta/perfil/');
$url_soporte = home_url('/contacto/');

// URL para cambio de contrasena
$url_cambiar_password = wp_lostpassword_url(home_url('/mi-cuenta/'));

// =========================================================================
// RENDER: Header del tema WordPress
// =========================================================================
get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-aplicante ga-portal-perfil">
    <div class="ga-container">

        <!-- =====================================================================
             NAVEGACION DEL PORTAL
        ====================================================================== -->
        <nav class="ga-portal-nav" role="navigation" aria-label="<?php esc_attr_e('Navegacion del portal', 'gestionadmin-wolk'); ?>">
            <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-nav-item">
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
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-nav-item active">
                <span class="dashicons dashicons-admin-users"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></span>
            </a>
        </nav>

        <!-- =====================================================================
             ALERTA DE ESTADO DE VERIFICACION
        ====================================================================== -->
        <div class="ga-verification-alert" style="--alert-bg: <?php echo esc_attr($estado_info['bg']); ?>; --alert-text: <?php echo esc_attr($estado_info['text']); ?>; --alert-border: <?php echo esc_attr($estado_info['border']); ?>;">
            <div class="ga-alert-icon">
                <span class="dashicons dashicons-<?php echo esc_attr($estado_info['icon']); ?>"></span>
            </div>
            <div class="ga-alert-content">
                <strong class="ga-alert-title"><?php echo esc_html($estado_info['label']); ?></strong>
                <p class="ga-alert-message"><?php echo esc_html($estado_info['message']); ?></p>
            </div>
            <?php if ($estado_actual === 'RECHAZADO' || $estado_actual === 'SUSPENDIDO'): ?>
            <a href="<?php echo esc_url($url_soporte); ?>" class="ga-btn ga-btn-sm ga-btn-alert">
                <?php esc_html_e('Contactar Soporte', 'gestionadmin-wolk'); ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- =====================================================================
             TARJETA PRINCIPAL DEL PERFIL
        ====================================================================== -->
        <section class="ga-profile-hero">
            <div class="ga-profile-header">
                <div class="ga-profile-avatar-wrapper">
                    <div class="ga-profile-avatar">
                        <?php echo get_avatar($wp_user_id, 140); ?>
                    </div>
                    <div class="ga-profile-status-badge" style="--badge-bg: <?php echo esc_attr($estado_info['bg']); ?>; --badge-text: <?php echo esc_attr($estado_info['text']); ?>; --badge-border: <?php echo esc_attr($estado_info['border']); ?>;">
                        <span class="dashicons dashicons-<?php echo esc_attr($estado_info['icon']); ?>"></span>
                    </div>
                </div>
                <div class="ga-profile-main-info">
                    <span class="ga-profile-code"><?php echo esc_html($codigo_aplicante); ?></span>
                    <h1 class="ga-profile-name"><?php echo esc_html($aplicante->nombre_completo); ?></h1>
                    <p class="ga-profile-email"><?php echo esc_html($aplicante->email); ?></p>
                    <div class="ga-profile-tags">
                        <span class="ga-profile-tag ga-tag-type">
                            <span class="dashicons dashicons-<?php echo $aplicante->tipo === 'EMPRESA' ? 'building' : 'businessman'; ?>"></span>
                            <?php echo esc_html(isset($tipos_aplicante[$aplicante->tipo]) ? $tipos_aplicante[$aplicante->tipo] : $aplicante->tipo); ?>
                        </span>
                        <?php if (!empty($aplicante->titulo_profesional)): ?>
                        <span class="ga-profile-tag ga-tag-profession">
                            <span class="dashicons dashicons-awards"></span>
                            <?php echo esc_html($aplicante->titulo_profesional); ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($aplicante->nivel_experiencia)): ?>
                        <span class="ga-profile-tag ga-tag-level">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php echo esc_html(GA_Aplicantes::NIVELES[$aplicante->nivel_experiencia] ?? $aplicante->nivel_experiencia); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ga-profile-quick-stats">
                    <div class="ga-quick-stat">
                        <span class="ga-quick-stat-value"><?php echo esc_html($aplicante->trabajos_completados ?? 0); ?></span>
                        <span class="ga-quick-stat-label"><?php esc_html_e('Trabajos', 'gestionadmin-wolk'); ?></span>
                    </div>
                    <div class="ga-quick-stat">
                        <span class="ga-quick-stat-value">
                            <?php
                            $rating = floatval($aplicante->calificacion_promedio ?? 0);
                            echo $rating > 0 ? number_format($rating, 1) : '-';
                            ?>
                        </span>
                        <span class="ga-quick-stat-label"><?php esc_html_e('Calificacion', 'gestionadmin-wolk'); ?></span>
                    </div>
                    <div class="ga-quick-stat">
                        <span class="ga-quick-stat-value">
                            <?php echo esc_html(date_i18n('M Y', strtotime($aplicante->created_at))); ?>
                        </span>
                        <span class="ga-quick-stat-label"><?php esc_html_e('Miembro desde', 'gestionadmin-wolk'); ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================================
             GRID DE SECCIONES
        ====================================================================== -->
        <div class="ga-profile-grid">

            <!-- =================================================================
                 SECCION: DOCUMENTOS
            ================================================================== -->
            <section class="ga-profile-section ga-section-documents">
                <div class="ga-section-header">
                    <div class="ga-section-icon">
                        <span class="dashicons dashicons-media-document"></span>
                    </div>
                    <h2><?php esc_html_e('Documentos', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <!-- CV / Hoja de Vida -->
                    <div class="ga-document-item">
                        <div class="ga-document-info">
                            <span class="ga-document-icon">
                                <span class="dashicons dashicons-media-text"></span>
                            </span>
                            <div class="ga-document-details">
                                <span class="ga-document-name"><?php esc_html_e('Curriculum Vitae (CV)', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-document-status <?php echo $tiene_cv ? 'status-ok' : 'status-missing'; ?>">
                                    <?php if ($tiene_cv): ?>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('Subido', 'gestionadmin-wolk'); ?>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e('No subido', 'gestionadmin-wolk'); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="ga-document-actions">
                            <?php if ($tiene_cv): ?>
                            <a href="<?php echo esc_url($aplicante->cv_archivo); ?>" target="_blank" class="ga-btn ga-btn-sm ga-btn-outline">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php esc_html_e('Ver', 'gestionadmin-wolk'); ?>
                            </a>
                            <?php endif; ?>
                            <button type="button" class="ga-btn ga-btn-sm ga-btn-secondary" disabled title="<?php esc_attr_e('Contacta soporte para actualizar', 'gestionadmin-wolk'); ?>">
                                <span class="dashicons dashicons-upload"></span>
                                <?php esc_html_e('Actualizar', 'gestionadmin-wolk'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Portafolio URL -->
                    <div class="ga-document-item">
                        <div class="ga-document-info">
                            <span class="ga-document-icon ga-icon-portfolio">
                                <span class="dashicons dashicons-portfolio"></span>
                            </span>
                            <div class="ga-document-details">
                                <span class="ga-document-name"><?php esc_html_e('Portafolio / Website', 'gestionadmin-wolk'); ?></span>
                                <?php if (!empty($aplicante->portfolio_url)): ?>
                                <a href="<?php echo esc_url($aplicante->portfolio_url); ?>" target="_blank" class="ga-document-link">
                                    <?php echo esc_html(parse_url($aplicante->portfolio_url, PHP_URL_HOST)); ?>
                                    <span class="dashicons dashicons-external"></span>
                                </a>
                                <?php else: ?>
                                <span class="ga-document-status status-missing">
                                    <span class="dashicons dashicons-minus"></span>
                                    <?php esc_html_e('No registrado', 'gestionadmin-wolk'); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Documento de Identidad - Frente -->
                    <div class="ga-document-item">
                        <div class="ga-document-info">
                            <span class="ga-document-icon ga-icon-id">
                                <span class="dashicons dashicons-id-alt"></span>
                            </span>
                            <div class="ga-document-details">
                                <span class="ga-document-name"><?php esc_html_e('Documento de Identidad (Frente)', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-document-status <?php echo $tiene_doc_frente ? 'status-ok' : 'status-missing'; ?>">
                                    <?php if ($tiene_doc_frente): ?>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('Subido', 'gestionadmin-wolk'); ?>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e('Requerido', 'gestionadmin-wolk'); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Documento de Identidad - Reverso -->
                    <div class="ga-document-item">
                        <div class="ga-document-info">
                            <span class="ga-document-icon ga-icon-id">
                                <span class="dashicons dashicons-id"></span>
                            </span>
                            <div class="ga-document-details">
                                <span class="ga-document-name"><?php esc_html_e('Documento de Identidad (Reverso)', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-document-status <?php echo $tiene_doc_reverso ? 'status-ok' : 'status-missing'; ?>">
                                    <?php if ($tiene_doc_reverso): ?>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('Subido', 'gestionadmin-wolk'); ?>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e('Requerido', 'gestionadmin-wolk'); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Nota de soporte -->
                    <div class="ga-section-note">
                        <span class="dashicons dashicons-info-outline"></span>
                        <?php esc_html_e('Para actualizar documentos, por favor contacta a soporte.', 'gestionadmin-wolk'); ?>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 SECCION: HABILIDADES
            ================================================================== -->
            <section class="ga-profile-section ga-section-skills">
                <div class="ga-section-header">
                    <div class="ga-section-icon ga-icon-skills">
                        <span class="dashicons dashicons-lightbulb"></span>
                    </div>
                    <h2><?php esc_html_e('Habilidades', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <?php if (!empty($habilidades)): ?>
                    <div class="ga-skills-grid">
                        <?php foreach ($habilidades as $habilidad): ?>
                        <span class="ga-skill-tag">
                            <span class="dashicons dashicons-yes"></span>
                            <?php echo esc_html($habilidad); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="ga-empty-skills">
                        <span class="dashicons dashicons-lightbulb"></span>
                        <p><?php esc_html_e('No hay habilidades registradas', 'gestionadmin-wolk'); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Nota de actualizacion -->
                    <div class="ga-section-note">
                        <span class="dashicons dashicons-info-outline"></span>
                        <?php esc_html_e('Para actualizar tus habilidades, por favor contacta a soporte.', 'gestionadmin-wolk'); ?>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 SECCION: INFORMACION PERSONAL
            ================================================================== -->
            <section class="ga-profile-section ga-section-personal">
                <div class="ga-section-header">
                    <div class="ga-section-icon ga-icon-personal">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <h2><?php esc_html_e('Informacion Personal', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <div class="ga-info-grid">
                        <!-- Nombre Completo -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-businessperson"></span>
                                <?php esc_html_e('Nombre Completo', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value"><?php echo esc_html($aplicante->nombre_completo); ?></span>
                        </div>

                        <!-- Email -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-email"></span>
                                <?php esc_html_e('Email', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value"><?php echo esc_html($aplicante->email); ?></span>
                        </div>

                        <!-- Telefono -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-phone"></span>
                                <?php esc_html_e('Telefono', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value">
                                <?php echo !empty($aplicante->telefono) ? esc_html($aplicante->telefono) : '<span class="ga-no-data">-</span>'; ?>
                            </span>
                        </div>

                        <!-- Pais -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-location"></span>
                                <?php esc_html_e('Pais', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value">
                                <?php echo !empty($aplicante->pais) ? esc_html($aplicante->pais) : '<span class="ga-no-data">-</span>'; ?>
                            </span>
                        </div>

                        <!-- Tipo de Documento -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-id-alt"></span>
                                <?php esc_html_e('Tipo Documento', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value">
                                <?php echo !empty($aplicante->documento_tipo) ? esc_html($aplicante->documento_tipo) : '<span class="ga-no-data">-</span>'; ?>
                            </span>
                        </div>

                        <!-- Numero de Documento -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-clipboard"></span>
                                <?php esc_html_e('Numero Documento', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value">
                                <?php echo !empty($aplicante->documento_numero) ? esc_html($aplicante->documento_numero) : '<span class="ga-no-data">-</span>'; ?>
                            </span>
                        </div>

                        <!-- Tipo Aplicante -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-groups"></span>
                                <?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value">
                                <?php echo esc_html(isset($tipos_aplicante[$aplicante->tipo]) ? $tipos_aplicante[$aplicante->tipo] : $aplicante->tipo); ?>
                            </span>
                        </div>

                        <!-- Ciudad -->
                        <div class="ga-info-item">
                            <span class="ga-info-label">
                                <span class="dashicons dashicons-location-alt"></span>
                                <?php esc_html_e('Ciudad', 'gestionadmin-wolk'); ?>
                            </span>
                            <span class="ga-info-value">
                                <?php echo !empty($aplicante->ciudad) ? esc_html($aplicante->ciudad) : '<span class="ga-no-data">-</span>'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Nota de solo lectura -->
                    <div class="ga-section-note">
                        <span class="dashicons dashicons-lock"></span>
                        <?php esc_html_e('Esta informacion es de solo lectura. Contacta a soporte para cambios.', 'gestionadmin-wolk'); ?>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 SECCION: METODO DE PAGO
            ================================================================== -->
            <section class="ga-profile-section ga-section-payment">
                <div class="ga-section-header">
                    <div class="ga-section-icon ga-icon-payment">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <h2><?php esc_html_e('Metodo de Pago', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <div class="ga-payment-card">
                        <div class="ga-payment-method">
                            <span class="ga-payment-icon">
                                <?php
                                $metodo = $aplicante->metodo_pago_preferido ?? '';
                                $icono_metodo = 'bank';
                                if ($metodo === 'BINANCE') $icono_metodo = 'bitcoin';
                                elseif ($metodo === 'PAYPAL') $icono_metodo = 'money-alt';
                                elseif ($metodo === 'WISE' || $metodo === 'PAYONEER') $icono_metodo = 'networking';
                                elseif ($metodo === 'STRIPE') $icono_metodo = 'tickets-alt';
                                ?>
                                <span class="dashicons dashicons-<?php echo esc_attr($icono_metodo); ?>"></span>
                            </span>
                            <div class="ga-payment-info">
                                <span class="ga-payment-label"><?php esc_html_e('Metodo Preferido', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-payment-value">
                                    <?php
                                    if (!empty($metodo) && isset($metodos_pago_labels[$metodo])) {
                                        echo esc_html($metodos_pago_labels[$metodo]);
                                    } else {
                                        echo '<span class="ga-no-data">' . esc_html__('No configurado', 'gestionadmin-wolk') . '</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($datos_pago_display)): ?>
                        <div class="ga-payment-data">
                            <span class="ga-data-label"><?php esc_html_e('Datos de Cuenta', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-data-value ga-masked"><?php echo esc_html($datos_pago_display); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($aplicante->tarifa_hora_min) || !empty($aplicante->tarifa_hora_max)): ?>
                        <div class="ga-payment-rates">
                            <span class="ga-rates-label"><?php esc_html_e('Tarifa por Hora', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-rates-value">
                                $<?php echo esc_html(number_format($aplicante->tarifa_hora_min ?? 0, 0)); ?>
                                <?php if (!empty($aplicante->tarifa_hora_max)): ?>
                                - $<?php echo esc_html(number_format($aplicante->tarifa_hora_max, 0)); ?>
                                <?php endif; ?>
                                <span class="ga-rates-currency">USD</span>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Nota de cambios -->
                    <div class="ga-section-note ga-note-warning">
                        <span class="dashicons dashicons-shield"></span>
                        <?php esc_html_e('Para cambios en tu metodo de pago, contacta a soporte por seguridad.', 'gestionadmin-wolk'); ?>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 SECCION: SEGURIDAD
            ================================================================== -->
            <section class="ga-profile-section ga-section-security">
                <div class="ga-section-header">
                    <div class="ga-section-icon ga-icon-security">
                        <span class="dashicons dashicons-shield-alt"></span>
                    </div>
                    <h2><?php esc_html_e('Seguridad', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <div class="ga-security-grid">
                        <!-- Email de Acceso -->
                        <div class="ga-security-item">
                            <div class="ga-security-info">
                                <span class="ga-security-icon">
                                    <span class="dashicons dashicons-email-alt"></span>
                                </span>
                                <div class="ga-security-details">
                                    <span class="ga-security-label"><?php esc_html_e('Email de Acceso', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-security-value"><?php echo esc_html($wp_user->user_email); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Cambiar Contrasena -->
                        <div class="ga-security-item ga-security-action">
                            <div class="ga-security-info">
                                <span class="ga-security-icon">
                                    <span class="dashicons dashicons-lock"></span>
                                </span>
                                <div class="ga-security-details">
                                    <span class="ga-security-label"><?php esc_html_e('Contrasena', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-security-value">••••••••••</span>
                                </div>
                            </div>
                            <a href="<?php echo esc_url($url_cambiar_password); ?>" class="ga-btn ga-btn-sm ga-btn-outline">
                                <span class="dashicons dashicons-edit"></span>
                                <?php esc_html_e('Cambiar', 'gestionadmin-wolk'); ?>
                            </a>
                        </div>

                        <!-- Ultimo acceso -->
                        <div class="ga-security-item">
                            <div class="ga-security-info">
                                <span class="ga-security-icon">
                                    <span class="dashicons dashicons-clock"></span>
                                </span>
                                <div class="ga-security-details">
                                    <span class="ga-security-label"><?php esc_html_e('Usuario WordPress', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-security-value"><?php echo esc_html($wp_user->user_login); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Rol -->
                        <div class="ga-security-item">
                            <div class="ga-security-info">
                                <span class="ga-security-icon">
                                    <span class="dashicons dashicons-admin-users"></span>
                                </span>
                                <div class="ga-security-details">
                                    <span class="ga-security-label"><?php esc_html_e('Tipo de Cuenta', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-security-value ga-security-badge"><?php esc_html_e('Aplicante', 'gestionadmin-wolk'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

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
   PORTAL APLICANTE - MI PERFIL
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

.ga-portal-perfil {
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
   ALERTA DE VERIFICACION
   ------------------------------------------------------------------------- */
.ga-verification-alert {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    background: var(--alert-bg);
    border: 1px solid var(--alert-border);
    border-radius: var(--ga-radius-lg);
    margin-bottom: 24px;
}

.ga-alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: #fff;
    border-radius: var(--ga-radius);
    flex-shrink: 0;
}

.ga-alert-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: var(--alert-text);
}

.ga-alert-content {
    flex: 1;
}

.ga-alert-title {
    display: block;
    font-size: 15px;
    font-weight: 700;
    color: var(--alert-text);
    margin-bottom: 4px;
}

.ga-alert-message {
    margin: 0;
    font-size: 13px;
    color: var(--alert-text);
    opacity: 0.85;
    line-height: 1.5;
}

.ga-btn-alert {
    background: var(--alert-text);
    color: #fff;
    border: none;
    flex-shrink: 0;
}

.ga-btn-alert:hover {
    opacity: 0.9;
    color: #fff;
}

/* -------------------------------------------------------------------------
   TARJETA PRINCIPAL DEL PERFIL
   ------------------------------------------------------------------------- */
.ga-profile-hero {
    background: #fff;
    border-radius: var(--ga-radius-xl);
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: var(--ga-shadow-md);
    border: 1px solid var(--ga-neutral-200);
}

.ga-profile-header {
    display: flex;
    align-items: center;
    gap: 28px;
    flex-wrap: wrap;
}

.ga-profile-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.ga-profile-avatar {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #fff;
    box-shadow: var(--ga-shadow-lg);
}

.ga-profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ga-profile-status-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 36px;
    height: 36px;
    background: var(--badge-bg);
    border: 3px solid #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--ga-shadow);
}

.ga-profile-status-badge .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    color: var(--badge-text);
}

.ga-profile-main-info {
    flex: 1;
    min-width: 250px;
}

.ga-profile-code {
    display: inline-block;
    padding: 4px 12px;
    background: var(--ga-neutral-100);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'SF Mono', 'Consolas', monospace;
    color: var(--ga-neutral-600);
    margin-bottom: 8px;
}

.ga-profile-name {
    margin: 0 0 4px 0;
    font-size: 28px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    letter-spacing: -0.5px;
}

.ga-profile-email {
    margin: 0 0 12px 0;
    font-size: 15px;
    color: var(--ga-neutral-500);
}

.ga-profile-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.ga-profile-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.ga-profile-tag .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.ga-tag-type {
    background: var(--ga-secondary-light);
    color: #4338ca;
}

.ga-tag-profession {
    background: var(--ga-primary-light);
    color: var(--ga-primary-dark);
}

.ga-tag-level {
    background: var(--ga-warning-light);
    color: #92400e;
}

.ga-profile-quick-stats {
    display: flex;
    gap: 20px;
    flex-shrink: 0;
}

.ga-quick-stat {
    text-align: center;
    padding: 16px 20px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius);
    min-width: 100px;
}

.ga-quick-stat-value {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    line-height: 1;
    margin-bottom: 4px;
}

.ga-quick-stat-label {
    display: block;
    font-size: 11px;
    color: var(--ga-neutral-500);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* -------------------------------------------------------------------------
   GRID DE SECCIONES
   ------------------------------------------------------------------------- */
.ga-profile-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.ga-profile-section {
    background: #fff;
    border-radius: var(--ga-radius-xl);
    overflow: hidden;
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
}

.ga-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 20px 24px;
    background: linear-gradient(135deg, var(--ga-neutral-50) 0%, #fff 100%);
    border-bottom: 1px solid var(--ga-neutral-100);
}

.ga-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    border-radius: var(--ga-radius);
    box-shadow: 0 4px 14px rgba(16,185,129,0.3);
}

.ga-section-icon .dashicons {
    font-size: 22px;
    width: 22px;
    height: 22px;
    color: #fff;
}

.ga-icon-skills {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 4px 14px rgba(245,158,11,0.3);
}

.ga-icon-personal {
    background: linear-gradient(135deg, var(--ga-secondary) 0%, #4f46e5 100%);
    box-shadow: 0 4px 14px rgba(99,102,241,0.3);
}

.ga-icon-payment {
    background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    box-shadow: 0 4px 14px rgba(20,184,166,0.3);
}

.ga-icon-security {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    box-shadow: 0 4px 14px rgba(139,92,246,0.3);
}

.ga-section-header h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-section-body {
    padding: 24px;
}

/* -------------------------------------------------------------------------
   SECCION: DOCUMENTOS
   ------------------------------------------------------------------------- */
.ga-document-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius);
    margin-bottom: 12px;
}

.ga-document-item:last-of-type {
    margin-bottom: 0;
}

.ga-document-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.ga-document-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    background: #fff;
    border-radius: var(--ga-radius);
    box-shadow: var(--ga-shadow);
}

.ga-document-icon .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    color: var(--ga-neutral-500);
}

.ga-icon-portfolio .dashicons {
    color: var(--ga-secondary);
}

.ga-icon-id .dashicons {
    color: var(--ga-primary);
}

.ga-document-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ga-document-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--ga-neutral-800);
}

.ga-document-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
}

.ga-document-status .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.ga-document-status.status-ok {
    color: var(--ga-success);
}

.ga-document-status.status-missing {
    color: var(--ga-warning);
}

.ga-document-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--ga-secondary);
    text-decoration: none;
}

.ga-document-link:hover {
    text-decoration: underline;
}

.ga-document-link .dashicons {
    font-size: 12px;
    width: 12px;
    height: 12px;
}

.ga-document-actions {
    display: flex;
    gap: 8px;
}

/* -------------------------------------------------------------------------
   SECCION: HABILIDADES
   ------------------------------------------------------------------------- */
.ga-skills-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}

.ga-skill-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: linear-gradient(135deg, var(--ga-primary-light) 0%, #a7f3d0 100%);
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    color: var(--ga-primary-dark);
    transition: all 0.2s;
}

.ga-skill-tag .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.ga-skill-tag:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16,185,129,0.2);
}

.ga-empty-skills {
    text-align: center;
    padding: 32px;
    color: var(--ga-neutral-400);
}

.ga-empty-skills .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.ga-empty-skills p {
    margin: 0;
    font-size: 14px;
}

/* -------------------------------------------------------------------------
   SECCION: INFORMACION PERSONAL
   ------------------------------------------------------------------------- */
.ga-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}

.ga-info-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius);
}

.ga-info-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ga-info-label .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.ga-info-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--ga-neutral-800);
}

.ga-no-data {
    color: var(--ga-neutral-400);
    font-style: italic;
}

/* -------------------------------------------------------------------------
   SECCION: METODO DE PAGO
   ------------------------------------------------------------------------- */
.ga-payment-card {
    background: linear-gradient(135deg, var(--ga-neutral-50) 0%, var(--ga-neutral-100) 100%);
    border-radius: var(--ga-radius-lg);
    padding: 20px;
    margin-bottom: 16px;
}

.ga-payment-method {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 16px;
    border-bottom: 1px dashed var(--ga-neutral-300);
    margin-bottom: 16px;
}

.ga-payment-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    border-radius: var(--ga-radius);
    box-shadow: 0 4px 14px rgba(20,184,166,0.3);
}

.ga-payment-icon .dashicons {
    font-size: 26px;
    width: 26px;
    height: 26px;
    color: #fff;
}

.ga-payment-info {
    flex: 1;
}

.ga-payment-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.ga-payment-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-payment-data {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 16px;
    border-bottom: 1px dashed var(--ga-neutral-300);
    margin-bottom: 16px;
}

.ga-data-label {
    font-size: 12px;
    color: var(--ga-neutral-500);
}

.ga-data-value {
    font-family: 'SF Mono', 'Consolas', monospace;
    font-size: 14px;
    font-weight: 600;
    color: var(--ga-neutral-600);
    letter-spacing: 2px;
}

.ga-payment-rates {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ga-rates-label {
    font-size: 12px;
    color: var(--ga-neutral-500);
}

.ga-rates-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--ga-primary-dark);
}

.ga-rates-currency {
    font-size: 12px;
    font-weight: 500;
    color: var(--ga-neutral-400);
    margin-left: 4px;
}

/* -------------------------------------------------------------------------
   SECCION: SEGURIDAD
   ------------------------------------------------------------------------- */
.ga-security-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ga-security-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius);
}

.ga-security-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.ga-security-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #fff;
    border-radius: var(--ga-radius);
    box-shadow: var(--ga-shadow);
}

.ga-security-icon .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    color: var(--ga-neutral-500);
}

.ga-security-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ga-security-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ga-security-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--ga-neutral-700);
}

.ga-security-badge {
    display: inline-block;
    padding: 4px 12px;
    background: var(--ga-primary-light);
    color: var(--ga-primary-dark);
    border-radius: 20px;
    font-size: 12px;
}

/* -------------------------------------------------------------------------
   NOTAS DE SECCION
   ------------------------------------------------------------------------- */
.ga-section-note {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    background: var(--ga-info-light);
    border-radius: var(--ga-radius);
    margin-top: 16px;
    font-size: 13px;
    color: #0369a1;
}

.ga-section-note .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.ga-note-warning {
    background: var(--ga-warning-light);
    color: #92400e;
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
    padding: 8px 14px;
    font-size: 12px;
}

.ga-btn-sm .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
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

.ga-btn-secondary {
    background: var(--ga-neutral-200);
    color: var(--ga-neutral-600);
}

.ga-btn-secondary:hover {
    background: var(--ga-neutral-300);
}

.ga-btn-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
    .ga-profile-grid {
        grid-template-columns: 1fr;
    }

    .ga-profile-header {
        flex-direction: column;
        text-align: center;
    }

    .ga-profile-main-info {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .ga-profile-tags {
        justify-content: center;
    }

    .ga-profile-quick-stats {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .ga-portal-perfil {
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

    .ga-verification-alert {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }

    .ga-alert-icon {
        margin: 0 auto;
    }

    .ga-profile-hero {
        padding: 24px;
    }

    .ga-profile-avatar {
        width: 100px;
        height: 100px;
    }

    .ga-profile-status-badge {
        width: 30px;
        height: 30px;
        bottom: 4px;
        right: 4px;
    }

    .ga-profile-name {
        font-size: 22px;
    }

    .ga-profile-quick-stats {
        flex-wrap: wrap;
    }

    .ga-quick-stat {
        flex: 1;
        min-width: 80px;
        padding: 12px 14px;
    }

    .ga-quick-stat-value {
        font-size: 20px;
    }

    .ga-section-body {
        padding: 20px;
    }

    .ga-info-grid {
        grid-template-columns: 1fr;
    }

    .ga-document-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .ga-document-actions {
        width: 100%;
    }

    .ga-document-actions .ga-btn {
        flex: 1;
        justify-content: center;
    }

    .ga-payment-method {
        flex-direction: column;
        text-align: center;
    }

    .ga-payment-data,
    .ga-payment-rates {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }

    .ga-security-item {
        flex-direction: column;
        gap: 12px;
    }

    .ga-security-item .ga-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .ga-profile-tags {
        flex-direction: column;
    }

    .ga-skills-grid {
        justify-content: center;
    }

    .ga-section-note {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php
// =========================================================================
// RENDER: Footer del tema WordPress
// =========================================================================
get_footer();
?>
