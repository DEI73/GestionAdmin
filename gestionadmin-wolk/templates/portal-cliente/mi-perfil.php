<?php
/**
 * Template: Portal Cliente - Mi Perfil
 *
 * Perfil completo del cliente con:
 * - Tarjeta principal con avatar, nombre, codigo, tipo, estado
 * - Informacion de contacto (email, telefono, direccion, pais)
 * - Informacion fiscal (identificacion, razon social)
 * - Estadisticas del cliente (casos, proyectos, facturas)
 * - Seccion de seguridad con cambio de contrasena
 * - Datos en solo lectura
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalCliente
 * @since      1.3.0
 * @updated    1.12.0 - Mi Perfil funcional completo (Sprint C4)
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
// OBTENER ESTADISTICAS DEL CLIENTE
// =========================================================================
$table_casos = $wpdb->prefix . 'ga_casos';
$table_proyectos = $wpdb->prefix . 'ga_proyectos';
$table_facturas = $wpdb->prefix . 'ga_facturas';

// Total de casos
$total_casos = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_casos} WHERE cliente_id = %d",
    $cliente->id
));

// Total de proyectos
$total_proyectos = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_proyectos} WHERE cliente_id = %d",
    $cliente->id
));

// Facturas pagadas
$facturas_pagadas = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$table_facturas} WHERE cliente_id = %d AND estado = 'PAGADA'",
    $cliente->id
));

// Total facturado (historico)
$total_facturado = (float) $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(total_a_pagar), 0) FROM {$table_facturas} WHERE cliente_id = %d AND estado IN ('PAGADA', 'PARCIAL', 'ENVIADA')",
    $cliente->id
));

// Fecha de registro del cliente
$fecha_registro = !empty($cliente->fecha_creacion) ? $cliente->fecha_creacion : $cliente->created_at;

// URLs de navegacion
$url_dashboard = home_url('/cliente/');
$url_casos = home_url('/cliente/mis-casos/');
$url_facturas = home_url('/cliente/mis-facturas/');
$url_perfil = home_url('/cliente/mi-perfil/');

// URL para cambiar contrasena
$url_cambiar_password = wp_lostpassword_url(home_url('/cliente/'));

// Determinar tipo de cliente
$tipo_cliente = isset($cliente->tipo) ? $cliente->tipo : 'EMPRESA';
$es_empresa = ($tipo_cliente === 'EMPRESA');

// Determinar estado del cliente
$estado_cliente = isset($cliente->activo) ? ($cliente->activo ? 'ACTIVO' : 'INACTIVO') : 'ACTIVO';

// Avatar - usar Gravatar del email del cliente
$avatar_url = get_avatar_url($cliente->email, array('size' => 150, 'default' => 'identicon'));

// Obtener paises para mostrar nombre completo
$paises = array(
    'CO' => 'Colombia',
    'US' => 'Estados Unidos',
    'MX' => 'Mexico',
    'CR' => 'Costa Rica',
    'PA' => 'Panama',
    'ES' => 'Espana',
    'AR' => 'Argentina',
    'CL' => 'Chile',
    'PE' => 'Peru',
    'EC' => 'Ecuador',
    'VE' => 'Venezuela',
    'GT' => 'Guatemala',
    'HN' => 'Honduras',
    'SV' => 'El Salvador',
    'NI' => 'Nicaragua',
    'DO' => 'Republica Dominicana',
    'PR' => 'Puerto Rico',
    'BO' => 'Bolivia',
    'PY' => 'Paraguay',
    'UY' => 'Uruguay',
);
$pais_nombre = isset($paises[$cliente->pais]) ? $paises[$cliente->pais] : $cliente->pais;

// Usar header del tema
get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-cliente ga-portal-perfil">
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
            <a href="<?php echo esc_url($url_facturas); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-media-text"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mis Facturas', 'gestionadmin-wolk'); ?></span>
            </a>
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-nav-item active">
                <span class="dashicons dashicons-id"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></span>
            </a>
        </nav>

        <!-- Tarjeta Principal del Cliente -->
        <header class="ga-profile-hero">
            <div class="ga-profile-hero-bg"></div>
            <div class="ga-profile-hero-content">
                <div class="ga-profile-avatar">
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($cliente->nombre_comercial); ?>">
                    <div class="ga-profile-status <?php echo $estado_cliente === 'ACTIVO' ? 'ga-status-active' : 'ga-status-inactive'; ?>">
                        <span class="dashicons dashicons-<?php echo $estado_cliente === 'ACTIVO' ? 'yes' : 'minus'; ?>"></span>
                    </div>
                </div>
                <div class="ga-profile-info">
                    <div class="ga-profile-name-row">
                        <h1><?php echo esc_html($cliente->nombre_comercial); ?></h1>
                        <span class="ga-profile-type <?php echo $es_empresa ? 'ga-type-empresa' : 'ga-type-persona'; ?>">
                            <span class="dashicons dashicons-<?php echo $es_empresa ? 'building' : 'admin-users'; ?>"></span>
                            <?php echo $es_empresa ? esc_html__('Empresa', 'gestionadmin-wolk') : esc_html__('Persona Natural', 'gestionadmin-wolk'); ?>
                        </span>
                    </div>
                    <div class="ga-profile-meta">
                        <span class="ga-meta-item ga-meta-code">
                            <span class="dashicons dashicons-tag"></span>
                            <?php echo esc_html($cliente->codigo); ?>
                        </span>
                        <span class="ga-meta-item">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php
                            printf(
                                esc_html__('Cliente desde %s', 'gestionadmin-wolk'),
                                date_i18n('F Y', strtotime($fecha_registro))
                            );
                            ?>
                        </span>
                        <span class="ga-meta-item ga-meta-status <?php echo $estado_cliente === 'ACTIVO' ? 'ga-active' : 'ga-inactive'; ?>">
                            <span class="ga-status-dot"></span>
                            <?php echo $estado_cliente === 'ACTIVO' ? esc_html__('Cuenta Activa', 'gestionadmin-wolk') : esc_html__('Cuenta Inactiva', 'gestionadmin-wolk'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Estadisticas del Cliente -->
        <section class="ga-profile-stats" aria-label="<?php esc_attr_e('Estadisticas', 'gestionadmin-wolk'); ?>">
            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-casos">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value"><?php echo esc_html($total_casos); ?></span>
                    <span class="ga-stat-label"><?php echo esc_html(_n('Caso', 'Casos', $total_casos, 'gestionadmin-wolk')); ?></span>
                </div>
            </div>
            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-proyectos">
                    <span class="dashicons dashicons-clipboard"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value"><?php echo esc_html($total_proyectos); ?></span>
                    <span class="ga-stat-label"><?php echo esc_html(_n('Proyecto', 'Proyectos', $total_proyectos, 'gestionadmin-wolk')); ?></span>
                </div>
            </div>
            <div class="ga-stat-card">
                <div class="ga-stat-icon ga-stat-facturas">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value"><?php echo esc_html($facturas_pagadas); ?></span>
                    <span class="ga-stat-label"><?php echo esc_html(_n('Factura Pagada', 'Facturas Pagadas', $facturas_pagadas, 'gestionadmin-wolk')); ?></span>
                </div>
            </div>
            <div class="ga-stat-card ga-stat-highlight">
                <div class="ga-stat-icon ga-stat-total">
                    <span class="dashicons dashicons-chart-area"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value">$<?php echo esc_html(number_format($total_facturado, 0)); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Total Facturado', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </section>

        <!-- Grid de Secciones -->
        <div class="ga-profile-grid">

            <!-- Informacion de Contacto -->
            <section class="ga-profile-section ga-section-contact">
                <div class="ga-section-header">
                    <div class="ga-section-icon">
                        <span class="dashicons dashicons-email-alt"></span>
                    </div>
                    <h2><?php esc_html_e('Informacion de Contacto', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <div class="ga-info-grid">
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-email"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Correo Electronico', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value"><?php echo esc_html($cliente->email); ?></span>
                            </div>
                        </div>
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-phone"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Telefono', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value">
                                    <?php echo !empty($cliente->telefono) ? esc_html($cliente->telefono) : '<em class="ga-no-data">' . esc_html__('No registrado', 'gestionadmin-wolk') . '</em>'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="ga-info-item ga-info-full">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-location"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Direccion', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value">
                                    <?php echo !empty($cliente->direccion) ? esc_html($cliente->direccion) : '<em class="ga-no-data">' . esc_html__('No registrada', 'gestionadmin-wolk') . '</em>'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Pais', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value ga-info-country">
                                    <span class="ga-country-flag"><?php echo esc_html($cliente->pais); ?></span>
                                    <?php echo esc_html($pais_nombre); ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($cliente->ciudad)): ?>
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-location-alt"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Ciudad', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value"><?php echo esc_html($cliente->ciudad); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Informacion Fiscal -->
            <section class="ga-profile-section ga-section-fiscal">
                <div class="ga-section-header">
                    <div class="ga-section-icon">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                    </div>
                    <h2><?php esc_html_e('Informacion Fiscal', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <div class="ga-info-grid">
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-id-alt"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Identificacion Fiscal', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value ga-info-fiscal-id">
                                    <?php echo !empty($cliente->identificacion_fiscal) ? esc_html($cliente->identificacion_fiscal) : '<em class="ga-no-data">' . esc_html__('No registrada', 'gestionadmin-wolk') . '</em>'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-building"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Razon Social', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value">
                                    <?php echo !empty($cliente->razon_social) ? esc_html($cliente->razon_social) : esc_html($cliente->nombre_comercial); ?>
                                </span>
                            </div>
                        </div>
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-groups"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Tipo de Cliente', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value">
                                    <span class="ga-badge <?php echo $es_empresa ? 'ga-badge-empresa' : 'ga-badge-persona'; ?>">
                                        <?php echo $es_empresa ? esc_html__('Empresa / Persona Juridica', 'gestionadmin-wolk') : esc_html__('Persona Natural', 'gestionadmin-wolk'); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($cliente->regimen_fiscal)): ?>
                        <div class="ga-info-item">
                            <div class="ga-info-icon">
                                <span class="dashicons dashicons-archive"></span>
                            </div>
                            <div class="ga-info-content">
                                <span class="ga-info-label"><?php esc_html_e('Regimen Fiscal', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value"><?php echo esc_html($cliente->regimen_fiscal); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Seguridad de la Cuenta -->
            <section class="ga-profile-section ga-section-security">
                <div class="ga-section-header">
                    <div class="ga-section-icon ga-icon-security">
                        <span class="dashicons dashicons-shield-alt"></span>
                    </div>
                    <h2><?php esc_html_e('Seguridad de la Cuenta', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-section-body">
                    <div class="ga-security-content">
                        <div class="ga-security-item">
                            <div class="ga-security-info">
                                <div class="ga-security-icon">
                                    <span class="dashicons dashicons-lock"></span>
                                </div>
                                <div class="ga-security-text">
                                    <span class="ga-security-title"><?php esc_html_e('Contrasena', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-security-desc"><?php esc_html_e('Actualiza tu contrasena regularmente para mantener tu cuenta segura.', 'gestionadmin-wolk'); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo esc_url($url_cambiar_password); ?>" class="ga-btn ga-btn-outline">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e('Cambiar Contrasena', 'gestionadmin-wolk'); ?>
                            </a>
                        </div>
                        <div class="ga-security-item">
                            <div class="ga-security-info">
                                <div class="ga-security-icon">
                                    <span class="dashicons dashicons-admin-users"></span>
                                </div>
                                <div class="ga-security-text">
                                    <span class="ga-security-title"><?php esc_html_e('Usuario de Acceso', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-security-desc"><?php echo esc_html($wp_user->user_login); ?></span>
                                </div>
                            </div>
                            <span class="ga-security-badge">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Verificado', 'gestionadmin-wolk'); ?>
                            </span>
                        </div>
                        <div class="ga-security-item">
                            <div class="ga-security-info">
                                <div class="ga-security-icon">
                                    <span class="dashicons dashicons-clock"></span>
                                </div>
                                <div class="ga-security-text">
                                    <span class="ga-security-title"><?php esc_html_e('Ultima Sesion', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-security-desc">
                                        <?php
                                        $last_login = get_user_meta($wp_user_id, 'last_login', true);
                                        if ($last_login) {
                                            echo esc_html(date_i18n('d M Y, H:i', strtotime($last_login)));
                                        } else {
                                            esc_html_e('Sesion actual', 'gestionadmin-wolk');
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Nota Informativa -->
            <section class="ga-profile-section ga-section-notice">
                <div class="ga-notice-card">
                    <div class="ga-notice-icon">
                        <span class="dashicons dashicons-info-outline"></span>
                    </div>
                    <div class="ga-notice-content">
                        <h3><?php esc_html_e('Actualizacion de Datos', 'gestionadmin-wolk'); ?></h3>
                        <p>
                            <?php esc_html_e('Para actualizar tus datos de facturacion, informacion fiscal o datos de contacto, por favor contacta a nuestro equipo de soporte. Esto nos permite verificar y mantener la integridad de tu informacion.', 'gestionadmin-wolk'); ?>
                        </p>
                        <div class="ga-notice-actions">
                            <a href="mailto:soporte@ejemplo.com" class="ga-btn ga-btn-notice">
                                <span class="dashicons dashicons-email"></span>
                                <?php esc_html_e('Contactar Soporte', 'gestionadmin-wolk'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

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
   PORTAL CLIENTE - MI PERFIL
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
    --ga-shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
    --ga-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    --ga-shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    --ga-shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    --ga-shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.25);
    --ga-radius-sm: 6px;
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
   HERO DEL PERFIL
   ========================================================================= */

.ga-profile-hero {
    position: relative;
    background: #fff;
    border-radius: var(--ga-radius-2xl);
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: var(--ga-shadow-md);
    border: 1px solid var(--ga-neutral-200);
}

.ga-profile-hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 140px;
    background: linear-gradient(135deg, var(--ga-primary) 0%, #0d9488 50%, var(--ga-secondary) 100%);
}

.ga-profile-hero-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.ga-profile-hero-content {
    position: relative;
    display: flex;
    align-items: flex-end;
    gap: 28px;
    padding: 100px 36px 32px;
}

.ga-profile-avatar {
    position: relative;
    flex-shrink: 0;
}

.ga-profile-avatar img {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 5px solid #fff;
    box-shadow: var(--ga-shadow-lg);
    object-fit: cover;
    background: #fff;
}

.ga-profile-status {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
    box-shadow: var(--ga-shadow);
}

.ga-profile-status.ga-status-active {
    background: var(--ga-success);
}

.ga-profile-status.ga-status-inactive {
    background: var(--ga-neutral-400);
}

.ga-profile-status .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: #fff;
}

.ga-profile-info {
    flex: 1;
    min-width: 0;
    padding-bottom: 8px;
}

.ga-profile-name-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.ga-profile-name-row h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    letter-spacing: -0.5px;
}

.ga-profile-type {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ga-type-empresa {
    background: var(--ga-secondary-light);
    color: #4338ca;
}

.ga-type-persona {
    background: var(--ga-info-light);
    color: #0369a1;
}

.ga-profile-type .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.ga-profile-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.ga-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: var(--ga-neutral-500);
}

.ga-meta-item .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: var(--ga-neutral-400);
}

.ga-meta-code {
    font-family: 'SF Mono', 'Consolas', monospace;
    font-weight: 600;
    color: var(--ga-neutral-700);
    background: var(--ga-neutral-100);
    padding: 4px 10px;
    border-radius: var(--ga-radius-sm);
}

.ga-meta-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.ga-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: pulse-dot 2s ease-in-out infinite;
}

.ga-meta-status.ga-active .ga-status-dot {
    background: var(--ga-success);
    box-shadow: 0 0 0 3px rgba(34,197,94,0.2);
}

.ga-meta-status.ga-inactive .ga-status-dot {
    background: var(--ga-neutral-400);
    animation: none;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.1); }
}

/* =========================================================================
   ESTADISTICAS
   ========================================================================= */

.ga-profile-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.ga-stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border-radius: var(--ga-radius-xl);
    padding: 24px;
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ga-stat-card:hover {
    transform: translateY(-3px);
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

.ga-stat-casos {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    box-shadow: 0 8px 24px rgba(99,102,241,0.3);
}

.ga-stat-proyectos {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    box-shadow: 0 8px 24px rgba(14,165,233,0.3);
}

.ga-stat-facturas {
    background: linear-gradient(135deg, var(--ga-success) 0%, #16a34a 100%);
    box-shadow: 0 8px 24px rgba(34,197,94,0.3);
}

.ga-stat-total {
    background: linear-gradient(135deg, var(--ga-primary) 0%, var(--ga-primary-dark) 100%);
    box-shadow: 0 8px 24px rgba(16,185,129,0.3);
}

.ga-stat-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ga-stat-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--ga-neutral-900);
    line-height: 1;
    letter-spacing: -0.5px;
}

.ga-stat-label {
    font-size: 13px;
    color: var(--ga-neutral-500);
    font-weight: 500;
}

.ga-stat-highlight {
    background: linear-gradient(135deg, var(--ga-primary-light) 0%, #ecfdf5 100%);
    border-color: var(--ga-primary);
}

/* =========================================================================
   GRID DE SECCIONES
   ========================================================================= */

.ga-profile-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.ga-profile-section {
    background: #fff;
    border-radius: var(--ga-radius-xl);
    box-shadow: var(--ga-shadow);
    border: 1px solid var(--ga-neutral-200);
    overflow: hidden;
}

.ga-section-notice {
    grid-column: 1 / -1;
}

.ga-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 24px 28px;
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
    box-shadow: 0 4px 14px rgba(16,185,129,0.25);
}

.ga-section-icon .dashicons {
    font-size: 22px;
    width: 22px;
    height: 22px;
    color: #fff;
}

.ga-icon-security {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    box-shadow: 0 4px 14px rgba(99,102,241,0.25);
}

.ga-section-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--ga-neutral-800);
}

.ga-section-body {
    padding: 28px;
}

/* =========================================================================
   GRID DE INFORMACION
   ========================================================================= */

.ga-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.ga-info-full {
    grid-column: 1 / -1;
}

.ga-info-item {
    display: flex;
    gap: 14px;
}

.ga-info-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    background: var(--ga-neutral-100);
    border-radius: var(--ga-radius);
    flex-shrink: 0;
}

.ga-info-icon .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    color: var(--ga-neutral-500);
}

.ga-info-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.ga-info-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--ga-neutral-400);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ga-info-value {
    font-size: 15px;
    font-weight: 500;
    color: var(--ga-neutral-800);
    word-break: break-word;
}

.ga-info-fiscal-id {
    font-family: 'SF Mono', 'Consolas', monospace;
    font-weight: 600;
    color: var(--ga-neutral-900);
}

.ga-info-country {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.ga-country-flag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 20px;
    background: var(--ga-neutral-200);
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    color: var(--ga-neutral-600);
}

.ga-no-data {
    color: var(--ga-neutral-400);
    font-style: italic;
    font-weight: 400;
}

.ga-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.ga-badge-empresa {
    background: var(--ga-secondary-light);
    color: #4338ca;
}

.ga-badge-persona {
    background: var(--ga-info-light);
    color: #0369a1;
}

/* =========================================================================
   SECCION DE SEGURIDAD
   ========================================================================= */

.ga-security-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ga-security-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 20px;
    background: var(--ga-neutral-50);
    border-radius: var(--ga-radius-lg);
    border: 1px solid var(--ga-neutral-200);
}

.ga-security-info {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
    min-width: 0;
}

.ga-security-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: #fff;
    border-radius: var(--ga-radius);
    box-shadow: var(--ga-shadow-sm);
    flex-shrink: 0;
}

.ga-security-icon .dashicons {
    font-size: 22px;
    width: 22px;
    height: 22px;
    color: var(--ga-neutral-600);
}

.ga-security-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.ga-security-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--ga-neutral-800);
}

.ga-security-desc {
    font-size: 13px;
    color: var(--ga-neutral-500);
}

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

.ga-btn-outline {
    background: #fff;
    color: var(--ga-primary);
    border: 2px solid var(--ga-primary);
}

.ga-btn-outline:hover {
    background: var(--ga-primary);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(16,185,129,0.35);
}

.ga-security-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: var(--ga-success-light);
    color: #15803d;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.ga-security-badge .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

/* =========================================================================
   NOTA INFORMATIVA
   ========================================================================= */

.ga-notice-card {
    display: flex;
    gap: 24px;
    padding: 28px;
    background: linear-gradient(135deg, var(--ga-info-light) 0%, #f0f9ff 100%);
    border-radius: var(--ga-radius-lg);
    border: 1px solid #bae6fd;
}

.ga-notice-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    background: var(--ga-info);
    border-radius: var(--ga-radius-lg);
    flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(14,165,233,0.25);
}

.ga-notice-icon .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    color: #fff;
}

.ga-notice-content {
    flex: 1;
}

.ga-notice-content h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 700;
    color: #0c4a6e;
}

.ga-notice-content p {
    margin: 0 0 20px 0;
    font-size: 14px;
    color: #0369a1;
    line-height: 1.6;
}

.ga-notice-actions {
    display: flex;
    gap: 12px;
}

.ga-btn-notice {
    background: var(--ga-info);
    color: #fff;
    box-shadow: 0 4px 14px rgba(14,165,233,0.35);
}

.ga-btn-notice:hover {
    background: #0284c7;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(14,165,233,0.45);
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
    .ga-profile-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .ga-profile-grid {
        grid-template-columns: 1fr;
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

    .ga-profile-hero-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 80px 24px 28px;
        gap: 20px;
    }

    .ga-profile-avatar img {
        width: 110px;
        height: 110px;
    }

    .ga-profile-name-row {
        flex-direction: column;
        gap: 12px;
    }

    .ga-profile-name-row h1 {
        font-size: 24px;
    }

    .ga-profile-meta {
        justify-content: center;
        gap: 12px;
    }

    .ga-meta-item {
        font-size: 13px;
    }

    .ga-profile-stats {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .ga-stat-card {
        padding: 20px;
    }

    .ga-stat-value {
        font-size: 24px;
    }

    .ga-section-header {
        padding: 20px;
    }

    .ga-section-body {
        padding: 20px;
    }

    .ga-info-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .ga-security-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
        padding: 16px;
    }

    .ga-btn-outline {
        width: 100%;
        justify-content: center;
    }

    .ga-notice-card {
        flex-direction: column;
        text-align: center;
        padding: 24px;
    }

    .ga-notice-icon {
        margin: 0 auto;
    }

    .ga-notice-actions {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .ga-profile-hero-bg {
        height: 100px;
    }

    .ga-profile-hero-content {
        padding-top: 60px;
    }

    .ga-profile-avatar img {
        width: 90px;
        height: 90px;
    }

    .ga-profile-status {
        width: 28px;
        height: 28px;
    }

    .ga-profile-status .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
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

    .ga-info-item {
        gap: 12px;
    }

    .ga-info-icon {
        width: 38px;
        height: 38px;
    }

    .ga-info-icon .dashicons {
        font-size: 18px;
        width: 18px;
        height: 18px;
    }

    .ga-security-icon {
        width: 42px;
        height: 42px;
    }

    .ga-security-icon .dashicons {
        font-size: 20px;
        width: 20px;
        height: 20px;
    }
}
</style>

<?php get_footer(); ?>
