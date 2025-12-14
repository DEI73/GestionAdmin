<?php
/**
 * Template: Portal Cliente - Mi Perfil
 *
 * Perfil y datos de la empresa cliente.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalCliente
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar autenticación
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// Usar header del tema (o fallback del plugin si no está activo)
get_header();

// Imprimir estilos del portal (heredan colores del tema si está activo)
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-cliente">
    <div class="ga-container">
        <div class="ga-portal-header">
            <h1>
                <span class="dashicons dashicons-id"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </h1>
            <p><?php esc_html_e('Datos de tu empresa', 'gestionadmin-wolk'); ?></p>
        </div>

        <div class="ga-coming-soon">
            <div class="ga-coming-soon-icon ga-profile-icon">
                <span class="dashicons dashicons-id"></span>
            </div>
            <h2><?php esc_html_e('En Desarrollo', 'gestionadmin-wolk'); ?></h2>
            <p><?php esc_html_e('La gestión de perfil empresarial estará disponible próximamente con:', 'gestionadmin-wolk'); ?></p>

            <div class="ga-features-list">
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Datos fiscales de la empresa', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Información de contacto', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Usuarios autorizados', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Preferencias de comunicación', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Configuración de seguridad', 'gestionadmin-wolk'); ?>
                </div>
            </div>

            <a href="<?php echo esc_url(home_url('/mi-portal/')); ?>" class="ga-btn-back">
                <span class="dashicons dashicons-arrow-left-alt"></span>
                <?php esc_html_e('Volver al Dashboard', 'gestionadmin-wolk'); ?>
            </a>
        </div>

        <div class="ga-portal-footer">
            <p>
                <?php esc_html_e('Diseñado y desarrollado por', 'gestionadmin-wolk'); ?>
                <a href="https://wolksoftcr.com" target="_blank">Wolksoftcr.com</a>
            </p>
        </div>
    </div>
</div>

<style>
.ga-portal-cliente {
    min-height: 80vh;
    padding: 40px 20px;
    background: #f5f7fa;
}
.ga-portal-header {
    text-align: center;
    margin-bottom: 40px;
}
.ga-portal-header h1 {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    font-size: 32px;
    color: #1a1a2e;
}
.ga-portal-header h1 .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    color: #28a745;
}
.ga-coming-soon {
    background: #fff;
    border-radius: 12px;
    padding: 50px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    max-width: 600px;
    margin: 0 auto;
}
.ga-coming-soon-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
}
.ga-profile-icon {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
}
.ga-coming-soon-icon .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    color: #fff;
}
.ga-coming-soon h2 {
    font-size: 28px;
    margin: 0 0 15px 0;
    color: #1a1a2e;
}
.ga-coming-soon > p {
    color: #666;
    font-size: 16px;
    margin-bottom: 30px;
}
.ga-features-list {
    text-align: left;
    max-width: 400px;
    margin: 0 auto 30px;
}
.ga-feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    color: #333;
    border-bottom: 1px solid #eee;
}
.ga-feature-item:last-child {
    border-bottom: none;
}
.ga-feature-item .dashicons {
    color: #28a745;
}
.ga-btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #20c997;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: background 0.3s;
}
.ga-btn-back:hover {
    background: #1aa179;
    color: #fff;
}
.ga-portal-footer {
    text-align: center;
    margin-top: 40px;
    color: #999;
}
.ga-portal-footer a {
    color: #20c997;
}
</style>

<?php get_footer(); ?>
