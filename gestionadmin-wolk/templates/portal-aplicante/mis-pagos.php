<?php
/**
 * Template: Portal Aplicante - Mis Pagos (Placeholder)
 *
 * NOTA IMPORTANTE: Los aplicantes NO tienen pagos.
 * Cuando un aplicante es aceptado en una orden de trabajo,
 * se convierte en EMPLEADO y gestiona sus pagos desde el Portal Empleado.
 *
 * Este archivo redirige a Mis Aplicaciones o muestra un mensaje informativo.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.5.0
 * @updated    1.16.0 - Convertido a placeholder (Sprint A4 corregido)
 * @author     Wolksoftcr.com
 */

// =========================================================================
// SEGURIDAD: Verificar acceso directo
// =========================================================================
if (!defined('ABSPATH')) {
    exit;
}

// =========================================================================
// REDIRECCION: Enviar a Mis Aplicaciones
// =========================================================================
// Opcion 1: Redirigir automaticamente (recomendado)
wp_redirect(home_url('/mi-cuenta/aplicaciones/'));
exit;

// =========================================================================
// OPCION 2: Si prefieres mostrar un mensaje en lugar de redirigir,
// comenta las lineas anteriores (wp_redirect y exit) y descomenta lo siguiente:
// =========================================================================

/*
// Verificar autenticacion
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

$wp_user_id = get_current_user_id();

// Cargar modulo de aplicantes
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-aplicantes.php';

// Verificar si es aplicante
$aplicante = GA_Aplicantes::get_by_wp_user($wp_user_id);

// URLs de navegacion (sin Mis Pagos)
$url_dashboard = home_url('/mi-cuenta/');
$url_aplicaciones = home_url('/mi-cuenta/aplicaciones/');
$url_marketplace = home_url('/trabajo/');
$url_perfil = home_url('/mi-cuenta/perfil/');

get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-aplicante ga-portal-pagos-placeholder">
    <div class="ga-container">

        <!-- Navegacion del Portal (SIN Mis Pagos) -->
        <nav class="ga-portal-nav" role="navigation">
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
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-admin-users"></span>
                <span class="ga-nav-text"><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></span>
            </a>
        </nav>

        <!-- Mensaje Informativo -->
        <div class="ga-info-card">
            <div class="ga-info-icon">
                <span class="dashicons dashicons-info-outline"></span>
            </div>
            <h1><?php esc_html_e('Seccion No Disponible', 'gestionadmin-wolk'); ?></h1>
            <p class="ga-info-message">
                <?php esc_html_e('Como aplicante, aun no tienes acceso a la seccion de pagos.', 'gestionadmin-wolk'); ?>
            </p>
            <div class="ga-info-details">
                <h3><?php esc_html_e('Como funciona:', 'gestionadmin-wolk'); ?></h3>
                <ol>
                    <li><?php esc_html_e('Aplica a ordenes de trabajo en el Marketplace', 'gestionadmin-wolk'); ?></li>
                    <li><?php esc_html_e('Cuando seas aceptado, se te asignara como empleado del proyecto', 'gestionadmin-wolk'); ?></li>
                    <li><?php esc_html_e('Podras registrar horas y gestionar pagos desde el Portal del Empleado', 'gestionadmin-wolk'); ?></li>
                </ol>
            </div>
            <div class="ga-info-actions">
                <a href="<?php echo esc_url($url_marketplace); ?>" class="ga-btn ga-btn-primary">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e('Explorar Oportunidades', 'gestionadmin-wolk'); ?>
                </a>
                <a href="<?php echo esc_url($url_aplicaciones); ?>" class="ga-btn ga-btn-outline">
                    <span class="dashicons dashicons-portfolio"></span>
                    <?php esc_html_e('Ver Mis Aplicaciones', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </div>

    </div>
</div>

<style>
.ga-portal-pagos-placeholder {
    min-height: 100vh;
    padding: 28px 24px;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
}

.ga-container {
    max-width: 800px;
    margin: 0 auto;
}

.ga-portal-nav {
    display: flex;
    gap: 6px;
    margin-bottom: 40px;
    background: #fff;
    padding: 10px 14px;
    border-radius: 14px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    flex-wrap: wrap;
    border: 1px solid #e2e8f0;
}

.ga-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 10px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.25s;
}

.ga-nav-item:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.ga-nav-item .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.ga-info-card {
    background: #fff;
    border-radius: 24px;
    padding: 60px 50px;
    text-align: center;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.ga-info-icon {
    width: 88px;
    height: 88px;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 28px;
    box-shadow: 0 10px 40px rgba(14,165,233,0.3);
}

.ga-info-icon .dashicons {
    font-size: 44px;
    width: 44px;
    height: 44px;
    color: #fff;
}

.ga-info-card h1 {
    font-size: 26px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px 0;
}

.ga-info-message {
    font-size: 16px;
    color: #64748b;
    margin: 0 0 32px 0;
}

.ga-info-details {
    background: #f8fafc;
    border-radius: 14px;
    padding: 24px 32px;
    text-align: left;
    margin-bottom: 32px;
}

.ga-info-details h3 {
    font-size: 15px;
    font-weight: 600;
    color: #334155;
    margin: 0 0 16px 0;
}

.ga-info-details ol {
    margin: 0;
    padding-left: 20px;
    color: #475569;
}

.ga-info-details li {
    margin-bottom: 10px;
    line-height: 1.5;
}

.ga-info-details li:last-child {
    margin-bottom: 0;
}

.ga-info-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.ga-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
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

@media (max-width: 768px) {
    .ga-portal-pagos-placeholder {
        padding: 20px 16px;
    }

    .ga-nav-item .ga-nav-text {
        display: none;
    }

    .ga-info-card {
        padding: 40px 24px;
    }

    .ga-info-actions {
        flex-direction: column;
    }

    .ga-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php
get_footer();
*/
?>
