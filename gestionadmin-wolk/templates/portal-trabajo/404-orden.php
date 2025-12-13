<?php
/**
 * Template: Orden No Encontrada
 *
 * Se muestra cuando la orden no existe o no está disponible.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalTrabajo
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="ga-public-container ga-404-page">
    <div class="ga-container">
        <div class="ga-error-card">
            <div class="ga-error-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>

            <h1><?php esc_html_e('Orden No Encontrada', 'gestionadmin-wolk'); ?></h1>

            <p><?php esc_html_e('La orden de trabajo que buscas no existe o ya no está disponible.', 'gestionadmin-wolk'); ?></p>

            <div class="ga-error-actions">
                <a href="<?php echo esc_url(home_url('/trabajo/')); ?>"
                   class="ga-btn ga-btn-primary">
                    <?php esc_html_e('Ver Oportunidades Disponibles', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
