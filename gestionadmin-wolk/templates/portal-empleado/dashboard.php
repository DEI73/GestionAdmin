<?php
/**
 * Template: Portal Empleado - Dashboard
 *
 * Dashboard principal del empleado interno.
 * Muestra resumen de tareas, timer activo, horas del mes y accesos rápidos.
 * Integrado con tema GestionAdmin Theme.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
 * @since      1.3.0
 * @updated    1.7.0 - Dashboard funcional con datos reales
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

// Obtener usuario actual
$wp_user_id = get_current_user_id();
$wp_user = wp_get_current_user();

// Cargar módulos necesarios
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-usuarios.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-puestos.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-departamentos.php';

// Obtener datos del empleado en GA
$usuario_ga = GA_Usuarios::get_by_wp_id($wp_user_id);

// Si no tiene perfil de empleado en GA, mostrar mensaje
if (!$usuario_ga) {
    get_header();
    GA_Theme_Integration::print_portal_styles();
    ?>
    <div class="ga-public-container ga-portal-empleado">
        <div class="ga-container">
            <div class="ga-portal-header">
                <h1>
                    <span class="dashicons dashicons-businessman"></span>
                    <?php esc_html_e('Portal del Empleado', 'gestionadmin-wolk'); ?>
                </h1>
            </div>
            <div class="ga-coming-soon">
                <div class="ga-coming-soon-icon">
                    <span class="dashicons dashicons-warning"></span>
                </div>
                <h2><?php esc_html_e('Perfil No Configurado', 'gestionadmin-wolk'); ?></h2>
                <p><?php esc_html_e('Tu cuenta de empleado aún no ha sido configurada en el sistema. Por favor contacta a tu supervisor o al departamento de RRHH.', 'gestionadmin-wolk'); ?></p>
            </div>
        </div>
    </div>
    <style>
    .ga-portal-empleado { min-height: 80vh; padding: 40px 20px; background: #f5f7fa; }
    .ga-portal-header { text-align: center; margin-bottom: 40px; padding: 48px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; }
    .ga-portal-header h1 { display: flex; align-items: center; justify-content: center; gap: 15px; font-size: 32px; color: #ffffff; }
    .ga-portal-header h1 .dashicons { font-size: 40px; width: 40px; height: 40px; color: #ffffff; }
    .ga-coming-soon { background: #fff; border-radius: 12px; padding: 50px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 600px; margin: 0 auto; }
    .ga-coming-soon-icon { width: 80px; height: 80px; background: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; }
    .ga-coming-soon-icon .dashicons { font-size: 40px; width: 40px; height: 40px; color: #fff; }
    .ga-coming-soon h2 { font-size: 24px; margin: 0 0 15px 0; color: #1a1a2e; }
    .ga-coming-soon p { color: #666; font-size: 16px; margin: 0; }
    </style>
    <?php
    get_footer();
    return;
}

// Obtener información adicional del empleado
$puesto = null;
$departamento = null;
$tarifa_hora = 0;

if ($usuario_ga->puesto_id) {
    $puesto = GA_Puestos::get($usuario_ga->puesto_id);

    // Calcular años de antigüedad
    $fecha_ingreso = $usuario_ga->fecha_ingreso ? new DateTime($usuario_ga->fecha_ingreso) : null;
    $anios_antiguedad = 1;
    if ($fecha_ingreso) {
        $hoy = new DateTime();
        $diff = $hoy->diff($fecha_ingreso);
        $anios_antiguedad = max(1, $diff->y + 1);
    }

    // Obtener tarifa según escala
    $escalas = GA_Puestos::get_escalas($usuario_ga->puesto_id);
    foreach ($escalas as $escala) {
        if ($escala->anio_antiguedad <= $anios_antiguedad) {
            $tarifa_hora = floatval($escala->tarifa_hora);
        }
    }
}

if ($usuario_ga->departamento_id) {
    $departamento = GA_Departamentos::get($usuario_ga->departamento_id);
}

// Obtener todas las tareas del usuario
$todas_tareas = GA_Tareas::get_all(array(
    'asignado_a' => $wp_user_id,
    'limit' => 500, // Obtener suficientes para contar
));

// Contar tareas por estado
$conteo_tareas = array(
    'pendiente' => 0,
    'en_progreso' => 0,
    'en_revision' => 0,
    'completada' => 0,
);

$mes_actual = date('Y-m');
foreach ($todas_tareas as $tarea) {
    switch ($tarea->estado) {
        case 'PENDIENTE':
            $conteo_tareas['pendiente']++;
            break;
        case 'EN_PROGRESO':
            $conteo_tareas['en_progreso']++;
            break;
        case 'EN_REVISION':
        case 'EN_QA':
            $conteo_tareas['en_revision']++;
            break;
        case 'COMPLETADA':
        case 'APROBADA':
        case 'APROBADA_QA':
            // Solo contar completadas del mes actual
            if ($tarea->fecha_completada && substr($tarea->fecha_completada, 0, 7) === $mes_actual) {
                $conteo_tareas['completada']++;
            }
            break;
    }
}

// Obtener timer activo
$timer_activo = GA_Tareas::get_active_timer($wp_user_id);

// Calcular horas del mes actual
global $wpdb;
$horas_mes = $wpdb->get_row($wpdb->prepare(
    "SELECT
        COALESCE(SUM(minutos_efectivos), 0) as minutos_totales,
        COALESCE(SUM(CASE WHEN estado IN ('APROBADO', 'PAGADO') THEN minutos_efectivos ELSE 0 END), 0) as minutos_aprobados,
        COUNT(*) as total_registros
     FROM {$wpdb->prefix}ga_registro_horas
     WHERE usuario_id = %d
     AND MONTH(fecha) = MONTH(CURRENT_DATE())
     AND YEAR(fecha) = YEAR(CURRENT_DATE())",
    $wp_user_id
));

$horas_trabajadas = $horas_mes ? round($horas_mes->minutos_totales / 60, 1) : 0;
$horas_aprobadas = $horas_mes ? round($horas_mes->minutos_aprobados / 60, 1) : 0;
$valor_estimado = $horas_aprobadas * $tarifa_hora;

// Usar header del tema
get_header();

// Imprimir estilos del portal
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-empleado">
    <div class="ga-container">
        <!-- =========================================================================
             HEADER DEL DASHBOARD
        ========================================================================== -->
        <div class="ga-portal-header ga-dashboard-welcome">
            <div class="ga-welcome-content">
                <h1>
                    <span class="dashicons dashicons-admin-users"></span>
                    <?php printf(esc_html__('Bienvenido, %s', 'gestionadmin-wolk'), esc_html($wp_user->display_name)); ?>
                </h1>
                <p class="ga-portal-subtitle">
                    <?php if ($puesto && $departamento): ?>
                        <?php echo esc_html($puesto->nombre); ?> &bull; <?php echo esc_html($departamento->nombre); ?>
                    <?php elseif ($puesto): ?>
                        <?php echo esc_html($puesto->nombre); ?>
                    <?php else: ?>
                        <?php esc_html_e('Empleado', 'gestionadmin-wolk'); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- =========================================================================
             NAVEGACION DEL PORTAL
        ========================================================================== -->
        <nav class="ga-dashboard-nav">
            <a href="<?php echo esc_url(home_url('/portal-empleado/')); ?>" class="ga-nav-item ga-nav-active">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('Mis Tareas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e('Mi Timer', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mis-horas/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-backup"></span>
                <?php esc_html_e('Mis Horas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mi-perfil/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-id"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </a>
        </nav>

        <div class="ga-dashboard-content">
            <?php if ($timer_activo['active']): ?>
            <!-- =========================================================================
                 TIMER ACTIVO
            ========================================================================== -->
            <section class="ga-timer-activo-card">
                <div class="ga-timer-header">
                    <span class="dashicons dashicons-clock"></span>
                    <h2><?php esc_html_e('Timer Activo', 'gestionadmin-wolk'); ?></h2>
                    <?php if ($timer_activo['is_paused']): ?>
                        <span class="ga-badge ga-badge-warning"><?php esc_html_e('Pausado', 'gestionadmin-wolk'); ?></span>
                    <?php else: ?>
                        <span class="ga-badge ga-badge-success"><?php esc_html_e('Activo', 'gestionadmin-wolk'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="ga-timer-body">
                    <div class="ga-timer-tarea">
                        <strong><?php echo esc_html($timer_activo['tarea_numero']); ?></strong>
                        <span><?php echo esc_html($timer_activo['tarea_nombre']); ?></span>
                        <?php if ($timer_activo['subtarea_nombre']): ?>
                            <small><?php echo esc_html($timer_activo['subtarea_nombre']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="ga-timer-tiempo">
                        <span class="ga-timer-display" data-inicio="<?php echo esc_attr($timer_activo['hora_inicio']); ?>" data-pausado="<?php echo $timer_activo['is_paused'] ? '1' : '0'; ?>" data-pausa-inicio="<?php echo esc_attr($timer_activo['pausa_inicio']); ?>">
                            00:00:00
                        </span>
                    </div>
                </div>
                <div class="ga-timer-footer">
                    <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/')); ?>" class="ga-btn ga-btn-primary">
                        <?php esc_html_e('Ir a Mi Timer', 'gestionadmin-wolk'); ?> &rarr;
                    </a>
                </div>
            </section>
            <?php endif; ?>

            <!-- =========================================================================
                 RESUMEN DE TAREAS
            ========================================================================== -->
            <section class="ga-dashboard-stats">
                <h2 class="ga-section-title">
                    <span class="dashicons dashicons-clipboard"></span>
                    <?php esc_html_e('Resumen de Tareas', 'gestionadmin-wolk'); ?>
                </h2>
                <div class="ga-stats-grid">
                    <div class="ga-stat-card ga-stat-pending">
                        <div class="ga-stat-icon">
                            <span class="dashicons dashicons-marker"></span>
                        </div>
                        <div class="ga-stat-content">
                            <span class="ga-stat-number"><?php echo esc_html($conteo_tareas['pendiente']); ?></span>
                            <span class="ga-stat-label"><?php esc_html_e('Pendientes', 'gestionadmin-wolk'); ?></span>
                        </div>
                    </div>
                    <div class="ga-stat-card ga-stat-progress">
                        <div class="ga-stat-icon">
                            <span class="dashicons dashicons-controls-play"></span>
                        </div>
                        <div class="ga-stat-content">
                            <span class="ga-stat-number"><?php echo esc_html($conteo_tareas['en_progreso']); ?></span>
                            <span class="ga-stat-label"><?php esc_html_e('En Progreso', 'gestionadmin-wolk'); ?></span>
                        </div>
                    </div>
                    <div class="ga-stat-card ga-stat-review">
                        <div class="ga-stat-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <div class="ga-stat-content">
                            <span class="ga-stat-number"><?php echo esc_html($conteo_tareas['en_revision']); ?></span>
                            <span class="ga-stat-label"><?php esc_html_e('En Revision', 'gestionadmin-wolk'); ?></span>
                        </div>
                    </div>
                    <div class="ga-stat-card ga-stat-complete">
                        <div class="ga-stat-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div class="ga-stat-content">
                            <span class="ga-stat-number"><?php echo esc_html($conteo_tareas['completada']); ?></span>
                            <span class="ga-stat-label"><?php esc_html_e('Este Mes', 'gestionadmin-wolk'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =========================================================================
                 METRICAS DEL MES
            ========================================================================== -->
            <section class="ga-dashboard-metrics">
                <h2 class="ga-section-title">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e('Este Mes', 'gestionadmin-wolk'); ?>
                </h2>
                <div class="ga-metrics-grid">
                    <div class="ga-metric-card">
                        <div class="ga-metric-icon">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <div class="ga-metric-content">
                            <span class="ga-metric-value"><?php echo esc_html(number_format($horas_trabajadas, 1)); ?> <small>hrs</small></span>
                            <span class="ga-metric-label"><?php esc_html_e('Horas Trabajadas', 'gestionadmin-wolk'); ?></span>
                        </div>
                    </div>
                    <div class="ga-metric-card">
                        <div class="ga-metric-icon ga-metric-success">
                            <span class="dashicons dashicons-yes"></span>
                        </div>
                        <div class="ga-metric-content">
                            <span class="ga-metric-value"><?php echo esc_html(number_format($horas_aprobadas, 1)); ?> <small>hrs</small></span>
                            <span class="ga-metric-label"><?php esc_html_e('Horas Aprobadas', 'gestionadmin-wolk'); ?></span>
                        </div>
                    </div>
                    <?php if ($tarifa_hora > 0): ?>
                    <div class="ga-metric-card">
                        <div class="ga-metric-icon ga-metric-money">
                            <span class="dashicons dashicons-money-alt"></span>
                        </div>
                        <div class="ga-metric-content">
                            <span class="ga-metric-value">$<?php echo esc_html(number_format($valor_estimado, 2)); ?></span>
                            <span class="ga-metric-label"><?php esc_html_e('Listo para Cobrar', 'gestionadmin-wolk'); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- =========================================================================
                 ACCESOS RAPIDOS
            ========================================================================== -->
            <section class="ga-quick-actions">
                <h2 class="ga-section-title">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php esc_html_e('Accesos Rapidos', 'gestionadmin-wolk'); ?>
                </h2>
                <div class="ga-actions-grid">
                    <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-action-card">
                        <span class="dashicons dashicons-list-view"></span>
                        <span class="ga-action-label"><?php esc_html_e('Mis Tareas', 'gestionadmin-wolk'); ?></span>
                        <?php if ($conteo_tareas['pendiente'] > 0): ?>
                            <span class="ga-action-badge"><?php echo esc_html($conteo_tareas['pendiente']); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/')); ?>" class="ga-action-card">
                        <span class="dashicons dashicons-clock"></span>
                        <span class="ga-action-label"><?php esc_html_e('Mi Timer', 'gestionadmin-wolk'); ?></span>
                        <?php if ($timer_activo['active']): ?>
                            <span class="ga-action-badge ga-badge-active"></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/portal-empleado/mis-horas/')); ?>" class="ga-action-card">
                        <span class="dashicons dashicons-backup"></span>
                        <span class="ga-action-label"><?php esc_html_e('Mis Horas', 'gestionadmin-wolk'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/portal-empleado/mi-perfil/')); ?>" class="ga-action-card">
                        <span class="dashicons dashicons-id"></span>
                        <span class="ga-action-label"><?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?></span>
                    </a>
                </div>
            </section>
        </div>

        <div class="ga-portal-footer">
            <p>
                <?php esc_html_e('Disenado y desarrollado por', 'gestionadmin-wolk'); ?>
                <a href="https://wolksoftcr.com" target="_blank">Wolksoftcr.com</a>
            </p>
        </div>
    </div>
</div>

<style>
/* =========================================================================
   PORTAL EMPLEADO - DASHBOARD
   ========================================================================== */
.ga-portal-empleado {
    min-height: 80vh;
    padding: 30px 20px;
    background: #f5f7fa;
}

/* Header / Welcome */
.ga-portal-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
}
.ga-portal-header h1 {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    font-size: 28px;
    color: #ffffff;
    margin: 0 0 10px 0;
}
.ga-portal-header h1 .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    color: #ffffff;
}
.ga-portal-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    margin: 0;
}

/* Navegacion */
.ga-dashboard-nav {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    padding: 15px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow-x: auto;
}
.ga-nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #555;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    transition: all 0.2s;
}
.ga-nav-item:hover {
    background: #f0f2f5;
    color: #333;
}
.ga-nav-item.ga-nav-active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.ga-nav-item .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Dashboard Content */
.ga-dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

/* Timer Activo Card */
.ga-timer-activo-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
    border-left: 4px solid #667eea;
    overflow: hidden;
}
.ga-timer-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.ga-timer-header h2 {
    margin: 0;
    font-size: 16px;
    flex-grow: 1;
}
.ga-timer-header .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}
.ga-timer-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    gap: 20px;
}
.ga-timer-tarea {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ga-timer-tarea strong {
    color: #667eea;
    font-size: 14px;
}
.ga-timer-tarea span {
    font-size: 16px;
    color: #333;
}
.ga-timer-tarea small {
    color: #888;
    font-size: 13px;
}
.ga-timer-display {
    font-size: 32px;
    font-weight: 700;
    color: #667eea;
    font-family: 'Courier New', monospace;
}
.ga-timer-footer {
    padding: 15px 20px;
    background: #f8f9fa;
    text-align: right;
}

/* Badges */
.ga-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.ga-badge-success {
    background: #28a745;
    color: #fff;
}
.ga-badge-warning {
    background: #ffc107;
    color: #333;
}

/* Section Title */
.ga-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    color: #333;
    margin: 0 0 20px 0;
}
.ga-section-title .dashicons {
    color: #667eea;
}

/* Stats Grid */
.ga-dashboard-stats {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}
.ga-stat-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border-radius: 10px;
    background: #f8f9fa;
}
.ga-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ga-stat-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #fff;
}
.ga-stat-pending .ga-stat-icon { background: #ffc107; }
.ga-stat-progress .ga-stat-icon { background: #17a2b8; }
.ga-stat-review .ga-stat-icon { background: #6f42c1; }
.ga-stat-complete .ga-stat-icon { background: #28a745; }
.ga-stat-content {
    display: flex;
    flex-direction: column;
}
.ga-stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    line-height: 1;
}
.ga-stat-label {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

/* Metrics Grid */
.ga-dashboard-metrics {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}
.ga-metric-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    border: 1px solid #eee;
}
.ga-metric-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ga-metric-icon.ga-metric-success { background: #28a745; }
.ga-metric-icon.ga-metric-money { background: #ffc107; }
.ga-metric-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #fff;
}
.ga-metric-content {
    display: flex;
    flex-direction: column;
}
.ga-metric-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}
.ga-metric-value small {
    font-size: 14px;
    font-weight: 400;
    color: #888;
}
.ga-metric-label {
    font-size: 13px;
    color: #666;
    margin-top: 2px;
}

/* Quick Actions */
.ga-quick-actions {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}
.ga-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 25px 15px;
    border-radius: 10px;
    background: #f8f9fa;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
    position: relative;
}
.ga-action-card:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}
.ga-action-card:hover .dashicons {
    color: #fff;
}
.ga-action-card .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #667eea;
    transition: color 0.2s;
}
.ga-action-label {
    font-size: 14px;
    font-weight: 500;
}
.ga-action-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    background: #dc3545;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ga-action-badge.ga-badge-active {
    width: 12px;
    height: 12px;
    min-width: 12px;
    padding: 0;
    background: #28a745;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Buttons */
.ga-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.ga-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.ga-btn-primary:hover {
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transform: translateY(-1px);
}

/* Footer */
.ga-portal-footer {
    text-align: center;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    color: #999;
    font-size: 13px;
}
.ga-portal-footer a {
    color: #667eea;
    text-decoration: none;
}

/* Responsive */
@media (max-width: 992px) {
    .ga-stats-grid,
    .ga-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 600px) {
    .ga-portal-header h1 {
        font-size: 22px;
    }
    .ga-dashboard-nav {
        gap: 5px;
        padding: 10px;
    }
    .ga-nav-item {
        padding: 8px 12px;
        font-size: 13px;
    }
    .ga-stats-grid,
    .ga-actions-grid {
        grid-template-columns: 1fr 1fr;
    }
    .ga-stat-card {
        padding: 15px;
    }
    .ga-stat-number {
        font-size: 22px;
    }
    .ga-timer-body {
        flex-direction: column;
        text-align: center;
    }
    .ga-timer-display {
        font-size: 28px;
    }
}
</style>

<script>
(function() {
    // Timer en tiempo real
    var timerDisplay = document.querySelector('.ga-timer-display');
    if (!timerDisplay) return;

    var inicio = timerDisplay.getAttribute('data-inicio');
    var pausado = timerDisplay.getAttribute('data-pausado') === '1';
    var pausaInicio = timerDisplay.getAttribute('data-pausa-inicio');

    if (!inicio) return;

    var inicioDate = new Date(inicio.replace(' ', 'T'));

    function updateTimer() {
        var ahora = new Date();
        var diff = Math.floor((ahora - inicioDate) / 1000);

        // Si esta pausado, no incrementar
        if (pausado && pausaInicio) {
            var pausaDate = new Date(pausaInicio.replace(' ', 'T'));
            diff = Math.floor((pausaDate - inicioDate) / 1000);
        }

        if (diff < 0) diff = 0;

        var horas = Math.floor(diff / 3600);
        var minutos = Math.floor((diff % 3600) / 60);
        var segundos = diff % 60;

        timerDisplay.textContent =
            String(horas).padStart(2, '0') + ':' +
            String(minutos).padStart(2, '0') + ':' +
            String(segundos).padStart(2, '0');
    }

    updateTimer();
    if (!pausado) {
        setInterval(updateTimer, 1000);
    }
})();
</script>

<?php get_footer(); ?>
