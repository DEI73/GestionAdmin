<?php
/**
 * Template: Portal Empleado - Dashboard
 *
 * Dashboard principal del empleado interno.
 * Muestra resumen de tareas, horas, y accesos rápidos.
 * Integrado con tema GestionAdmin Theme.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
 * @since      1.3.0
 * @updated    1.6.0 - Integración con tema
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

// TODO: Verificar que el usuario es un empleado
// $usuario = GA_Usuarios::get_by_wp_user(get_current_user_id());

// Usar header del tema (o fallback del plugin si no está activo)
get_header();

// Imprimir estilos del portal (heredan colores del tema si está activo)
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-empleado">
    <div class="ga-container">
        <div class="ga-portal-header">
            <h1>
                <span class="dashicons dashicons-businessman"></span>
                <?php esc_html_e('Portal del Empleado', 'gestionadmin-wolk'); ?>
            </h1>
            <p><?php esc_html_e('Bienvenido a tu espacio de trabajo', 'gestionadmin-wolk'); ?></p>
        </div>

        <div class="ga-coming-soon">
            <div class="ga-coming-soon-icon">
                <span class="dashicons dashicons-hammer"></span>
            </div>
            <h2><?php esc_html_e('En Desarrollo', 'gestionadmin-wolk'); ?></h2>
            <p><?php esc_html_e('El portal del empleado estará disponible próximamente con las siguientes funcionalidades:', 'gestionadmin-wolk'); ?></p>

            <div class="ga-features-grid">
                <div class="ga-feature-card">
                    <span class="dashicons dashicons-list-view"></span>
                    <h3><?php esc_html_e('Mis Tareas', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('Gestiona tus tareas asignadas', 'gestionadmin-wolk'); ?></p>
                </div>
                <div class="ga-feature-card">
                    <span class="dashicons dashicons-clock"></span>
                    <h3><?php esc_html_e('Mi Timer', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('Registra tiempo en tus tareas', 'gestionadmin-wolk'); ?></p>
                </div>
                <div class="ga-feature-card">
                    <span class="dashicons dashicons-backup"></span>
                    <h3><?php esc_html_e('Mis Horas', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('Historial de horas trabajadas', 'gestionadmin-wolk'); ?></p>
                </div>
                <div class="ga-feature-card">
                    <span class="dashicons dashicons-id"></span>
                    <h3><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('Configura tu información', 'gestionadmin-wolk'); ?></p>
                </div>
            </div>
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
.ga-portal-empleado {
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
    color: #667eea;
}
.ga-coming-soon {
    background: #fff;
    border-radius: 12px;
    padding: 50px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    max-width: 800px;
    margin: 0 auto;
}
.ga-coming-soon-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
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
    margin-bottom: 40px;
}
.ga-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
}
.ga-feature-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 25px 20px;
    text-align: center;
}
.ga-feature-card .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #667eea;
    margin-bottom: 15px;
}
.ga-feature-card h3 {
    font-size: 16px;
    margin: 0 0 8px 0;
    color: #1a1a2e;
}
.ga-feature-card p {
    font-size: 13px;
    color: #666;
    margin: 0;
}
.ga-portal-footer {
    text-align: center;
    margin-top: 40px;
    color: #999;
}
.ga-portal-footer a {
    color: #667eea;
}
</style>

<?php get_footer(); ?>
