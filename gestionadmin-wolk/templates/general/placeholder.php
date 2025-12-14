<?php
/**
 * Template: Placeholder - Página en Construcción
 *
 * Template genérico que se muestra cuando una página del plugin
 * aún no tiene su template específico desarrollado.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/General
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener información de la página actual
require_once GA_PLUGIN_DIR . 'includes/class-ga-pages-manager.php';
$pages_manager = GA_Pages_Manager::get_instance();
$page_key = $pages_manager->detect_current_page();
$page_config = $page_key ? $pages_manager->get_page_config($page_key) : null;

// Usar header del tema (o fallback del plugin si no está activo)
get_header();

// Imprimir estilos del portal (heredan colores del tema si está activo)
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-placeholder-page">
    <div class="ga-container">
        <div class="ga-placeholder-card">
            <div class="ga-placeholder-icon">
                <span class="dashicons dashicons-hammer"></span>
            </div>

            <h1><?php esc_html_e('Página en Construcción', 'gestionadmin-wolk'); ?></h1>

            <?php if ($page_config) : ?>
                <p class="ga-placeholder-title">
                    <strong><?php echo esc_html($page_config['title']); ?></strong>
                </p>
                <p class="ga-placeholder-description">
                    <?php echo esc_html($page_config['description']); ?>
                </p>
            <?php endif; ?>

            <p class="ga-placeholder-message">
                <?php esc_html_e('Esta funcionalidad estará disponible próximamente.', 'gestionadmin-wolk'); ?>
            </p>

            <div class="ga-placeholder-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="ga-btn ga-btn-outline">
                    <?php esc_html_e('Volver al Inicio', 'gestionadmin-wolk'); ?>
                </a>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" class="ga-btn ga-btn-primary">
                        <?php esc_html_e('Mi Cuenta', 'gestionadmin-wolk'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.ga-placeholder-page {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
}
.ga-placeholder-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 60px 40px;
    text-align: center;
    max-width: 500px;
    width: 100%;
}
.ga-placeholder-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
}
.ga-placeholder-icon .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    color: #fff;
}
.ga-placeholder-card h1 {
    font-size: 28px;
    margin: 0 0 15px 0;
    color: #1a1a2e;
}
.ga-placeholder-title {
    font-size: 18px;
    color: #667eea;
    margin-bottom: 10px;
}
.ga-placeholder-description {
    color: #666;
    margin-bottom: 20px;
}
.ga-placeholder-message {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    color: #495057;
    margin-bottom: 30px;
}
.ga-placeholder-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}
</style>

<?php get_footer(); ?>
