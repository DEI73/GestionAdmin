<?php
/**
 * Template: Portal Empleado - Mis Horas
 *
 * Historial de horas trabajadas del empleado.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
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

<div class="ga-public-container ga-portal-empleado">
    <div class="ga-container">
        <div class="ga-portal-header">
            <h1>
                <span class="dashicons dashicons-calendar-alt"></span>
                <?php esc_html_e('Mis Horas', 'gestionadmin-wolk'); ?>
            </h1>
            <p><?php esc_html_e('Historial de horas trabajadas', 'gestionadmin-wolk'); ?></p>
        </div>

        <div class="ga-coming-soon">
            <div class="ga-coming-soon-icon ga-hours-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <h2><?php esc_html_e('En Desarrollo', 'gestionadmin-wolk'); ?></h2>
            <p><?php esc_html_e('El registro de horas estará disponible próximamente con:', 'gestionadmin-wolk'); ?></p>

            <div class="ga-features-list">
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Calendario visual de horas trabajadas', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Detalle por día, semana y mes', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Distribución por proyecto y tarea', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Exportación a PDF y Excel', 'gestionadmin-wolk'); ?>
                </div>
                <div class="ga-feature-item">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Gráficas de productividad', 'gestionadmin-wolk'); ?>
                </div>
            </div>

            <a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" class="ga-btn-back">
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
    color: #007bff;
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
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
}
.ga-hours-icon {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
    background: #17a2b8;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: background 0.3s;
}
.ga-btn-back:hover {
    background: #138496;
    color: #fff;
}
.ga-portal-footer {
    text-align: center;
    margin-top: 40px;
    color: #999;
}
.ga-portal-footer a {
    color: #17a2b8;
}
</style>

<?php get_footer(); ?>
