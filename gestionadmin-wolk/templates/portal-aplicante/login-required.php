<?php
/**
 * Template: Login Requerido
 *
 * Se muestra cuando un usuario no autenticado intenta acceder
 * a una sección que requiere login.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="ga-public-container ga-login-required">
    <div class="ga-container">
        <div class="ga-auth-card">
            <div class="ga-auth-icon">
                <span class="dashicons dashicons-lock"></span>
            </div>

            <h1><?php esc_html_e('Acceso Restringido', 'gestionadmin-wolk'); ?></h1>

            <p><?php esc_html_e('Debes iniciar sesión para acceder a esta sección.', 'gestionadmin-wolk'); ?></p>

            <div class="ga-auth-actions">
                <a href="<?php echo esc_url(wp_login_url(home_url($_SERVER['REQUEST_URI']))); ?>"
                   class="ga-btn ga-btn-primary ga-btn-large">
                    <?php esc_html_e('Iniciar Sesión', 'gestionadmin-wolk'); ?>
                </a>
            </div>

            <p class="ga-auth-alt">
                <?php esc_html_e('¿No tienes cuenta?', 'gestionadmin-wolk'); ?>
                <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>">
                    <?php esc_html_e('Regístrate gratis', 'gestionadmin-wolk'); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<?php get_footer(); ?>
