<?php
/**
 * Vista: Dashboard Principal
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Estadísticas
$total_departamentos = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ga_departamentos WHERE activo = 1");
$total_puestos = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ga_puestos WHERE activo = 1");
$total_usuarios = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ga_usuarios WHERE activo = 1");
$total_tareas = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ga_tareas");
$tareas_pendientes = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ga_tareas WHERE estado = 'PENDIENTE'");
$tareas_progreso = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ga_tareas WHERE estado = 'EN_PROGRESO'");
?>
<div class="wrap ga-admin">
    <h1><?php esc_html_e('GestionAdmin by Wolksoftcr', 'gestionadmin-wolk'); ?></h1>
    <p><?php esc_html_e('Sistema integral de gestión empresarial', 'gestionadmin-wolk'); ?></p>
    <p class="description"><?php esc_html_e('Diseñado y desarrollado por Wolksoftcr.com', 'gestionadmin-wolk'); ?></p>

    <div class="ga-row" style="margin-top: 20px;">
        <!-- Estadísticas -->
        <div class="ga-col ga-col-3">
            <div class="ga-card">
                <h3 style="margin-top: 0;"><?php esc_html_e('Departamentos', 'gestionadmin-wolk'); ?></h3>
                <p style="font-size: 36px; font-weight: bold; margin: 0; color: #2271b1;">
                    <?php echo esc_html($total_departamentos); ?>
                </p>
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-departamentos')); ?>">
                    <?php esc_html_e('Ver todos', 'gestionadmin-wolk'); ?> &rarr;
                </a></p>
            </div>
        </div>

        <div class="ga-col ga-col-3">
            <div class="ga-card">
                <h3 style="margin-top: 0;"><?php esc_html_e('Puestos', 'gestionadmin-wolk'); ?></h3>
                <p style="font-size: 36px; font-weight: bold; margin: 0; color: #2271b1;">
                    <?php echo esc_html($total_puestos); ?>
                </p>
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-puestos')); ?>">
                    <?php esc_html_e('Ver todos', 'gestionadmin-wolk'); ?> &rarr;
                </a></p>
            </div>
        </div>

        <div class="ga-col ga-col-3">
            <div class="ga-card">
                <h3 style="margin-top: 0;"><?php esc_html_e('Usuarios GA', 'gestionadmin-wolk'); ?></h3>
                <p style="font-size: 36px; font-weight: bold; margin: 0; color: #2271b1;">
                    <?php echo esc_html($total_usuarios); ?>
                </p>
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-usuarios')); ?>">
                    <?php esc_html_e('Ver todos', 'gestionadmin-wolk'); ?> &rarr;
                </a></p>
            </div>
        </div>

        <div class="ga-col ga-col-3">
            <div class="ga-card">
                <h3 style="margin-top: 0;"><?php esc_html_e('Tareas', 'gestionadmin-wolk'); ?></h3>
                <p style="font-size: 36px; font-weight: bold; margin: 0; color: #2271b1;">
                    <?php echo esc_html($total_tareas); ?>
                </p>
                <p>
                    <span class="ga-badge ga-badge-warning"><?php echo esc_html($tareas_pendientes); ?> <?php esc_html_e('pendientes', 'gestionadmin-wolk'); ?></span>
                    <span class="ga-badge ga-badge-success"><?php echo esc_html($tareas_progreso); ?> <?php esc_html_e('en progreso', 'gestionadmin-wolk'); ?></span>
                </p>
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-tareas')); ?>">
                    <?php esc_html_e('Ver todas', 'gestionadmin-wolk'); ?> &rarr;
                </a></p>
            </div>
        </div>
    </div>

    <!-- Timer Widget -->
    <div class="ga-row" style="margin-top: 20px;">
        <div class="ga-col ga-col-6">
            <div class="ga-card" id="ga-timer-widget">
                <h3 style="margin-top: 0;"><?php esc_html_e('Timer Activo', 'gestionadmin-wolk'); ?></h3>
                <div id="ga-timer-content">
                    <p class="ga-timer-loading"><?php esc_html_e('Cargando...', 'gestionadmin-wolk'); ?></p>
                </div>
            </div>
        </div>

        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <h3 style="margin-top: 0;"><?php esc_html_e('Información del Sistema', 'gestionadmin-wolk'); ?></h3>
                <table class="ga-table" style="margin: 0;">
                    <tr>
                        <td><strong><?php esc_html_e('Versión del Plugin', 'gestionadmin-wolk'); ?></strong></td>
                        <td><?php echo esc_html(GA_VERSION); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Versión de WordPress', 'gestionadmin-wolk'); ?></strong></td>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('Versión de PHP', 'gestionadmin-wolk'); ?></strong></td>
                        <td><?php echo esc_html(phpversion()); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
