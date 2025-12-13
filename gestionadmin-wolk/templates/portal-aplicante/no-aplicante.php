<?php
/**
 * Template: No es Aplicante
 *
 * Se muestra cuando un usuario logueado no tiene perfil de aplicante.
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

<div class="ga-public-container ga-no-aplicante">
    <div class="ga-container">
        <div class="ga-auth-card">
            <div class="ga-auth-icon ga-auth-icon-info">
                <span class="dashicons dashicons-info"></span>
            </div>

            <h1><?php esc_html_e('Completa tu Perfil', 'gestionadmin-wolk'); ?></h1>

            <p><?php esc_html_e('Tu cuenta de usuario no tiene un perfil de aplicante asociado. Completa tu registro para acceder al portal.', 'gestionadmin-wolk'); ?></p>

            <div class="ga-auth-actions">
                <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>"
                   class="ga-btn ga-btn-primary ga-btn-large">
                    <?php esc_html_e('Completar Registro', 'gestionadmin-wolk'); ?>
                </a>
            </div>

            <p class="ga-auth-alt">
                <a href="<?php echo esc_url(home_url('/trabajo/')); ?>">
                    <?php esc_html_e('Explorar oportunidades sin cuenta', 'gestionadmin-wolk'); ?> →
                </a>
            </p>
        </div>
    </div>
</div>

<?php get_footer(); ?>
