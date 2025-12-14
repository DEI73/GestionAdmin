<?php
/**
 * Template: Portal Empleado - Mi Timer
 *
 * Timer integrado para registro de horas de trabajo con controles
 * de inicio, pausa, reanudacion y detencion.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
 * @since      1.3.0
 * @updated    1.8.0 - Timer funcional completo
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar autenticacion
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// Obtener usuario actual
$wp_user_id = get_current_user_id();
$wp_user = wp_get_current_user();

// Cargar modulos necesarios
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-usuarios.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';

// Verificar que es un empleado registrado
$usuario_ga = GA_Usuarios::get_by_wp_id($wp_user_id);
if (!$usuario_ga) {
    wp_redirect(home_url('/portal-empleado/'));
    exit;
}

// Verificar si viene con tarea_id para iniciar timer
$tarea_id_iniciar = isset($_GET['tarea_id']) ? absint($_GET['tarea_id']) : 0;

// Obtener timer activo
$timer_activo = GA_Tareas::get_active_timer($wp_user_id);

// Obtener motivos de pausa
$motivos_pausa = GA_Tareas::get_motivos_pausa();

// Obtener historial del dia
global $wpdb;
$hoy = current_time('Y-m-d');
$registros_hoy = $wpdb->get_results($wpdb->prepare(
    "SELECT rh.*,
            t.nombre as tarea_nombre,
            t.numero as tarea_numero,
            s.nombre as subtarea_nombre
     FROM {$wpdb->prefix}ga_registro_horas rh
     LEFT JOIN {$wpdb->prefix}ga_tareas t ON rh.tarea_id = t.id
     LEFT JOIN {$wpdb->prefix}ga_subtareas s ON rh.subtarea_id = s.id
     WHERE rh.usuario_id = %d
     AND rh.fecha = %s
     ORDER BY rh.hora_inicio DESC",
    $wp_user_id,
    $hoy
));

// Calcular total de horas hoy
$minutos_hoy = 0;
foreach ($registros_hoy as $reg) {
    if ($reg->estado === 'ACTIVO') {
        // Timer activo: calcular tiempo transcurrido
        $minutos_hoy += round((time() - strtotime($reg->hora_inicio)) / 60);
    } else {
        $minutos_hoy += intval($reg->minutos_efectivos);
    }
}
$horas_hoy = round($minutos_hoy / 60, 2);

// Estados de registro para mostrar
$estados_registro = array(
    'ACTIVO' => __('Activo', 'gestionadmin-wolk'),
    'BORRADOR' => __('Borrador', 'gestionadmin-wolk'),
    'ENVIADO' => __('Enviado', 'gestionadmin-wolk'),
    'APROBADO' => __('Aprobado', 'gestionadmin-wolk'),
    'RECHAZADO' => __('Rechazado', 'gestionadmin-wolk'),
    'PAGADO' => __('Pagado', 'gestionadmin-wolk'),
);

// Generar nonce para operaciones AJAX
$timer_nonce = wp_create_nonce('ga_timer_nonce');

// Si viene tarea_id y no hay timer activo, obtener datos de la tarea
$tarea_para_iniciar = null;
$subtareas_para_iniciar = array();
if ($tarea_id_iniciar > 0 && !$timer_activo['active']) {
    $tarea_para_iniciar = GA_Tareas::get($tarea_id_iniciar);
    if ($tarea_para_iniciar && $tarea_para_iniciar->asignado_a == $wp_user_id) {
        $subtareas_para_iniciar = GA_Tareas::get_subtareas($tarea_id_iniciar);
    } else {
        $tarea_para_iniciar = null;
    }
}

// Usar header del tema
get_header();

// Imprimir estilos del portal
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-empleado">
    <div class="ga-container">
        <!-- =========================================================================
             HEADER
        ========================================================================== -->
        <div class="ga-portal-header">
            <div class="ga-welcome-content">
                <h1>
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e('Mi Timer', 'gestionadmin-wolk'); ?>
                </h1>
                <p class="ga-portal-subtitle">
                    <?php esc_html_e('Control de tiempo de trabajo en tiempo real', 'gestionadmin-wolk'); ?>
                </p>
            </div>
        </div>

        <!-- =========================================================================
             NAVEGACION DEL PORTAL
        ========================================================================== -->
        <nav class="ga-dashboard-nav">
            <a href="<?php echo esc_url(home_url('/portal-empleado/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('Mis Tareas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/')); ?>" class="ga-nav-item ga-nav-active">
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

        <!-- =========================================================================
             CONTENIDO PRINCIPAL
        ========================================================================== -->
        <div class="ga-timer-content">

            <?php if ($timer_activo['active']): ?>
                <!-- =========================================================================
                     TIMER ACTIVO
                ========================================================================== -->
                <div class="ga-timer-card ga-timer-active" id="ga-timer-container"
                     data-registro-id="<?php echo esc_attr($timer_activo['registro_id']); ?>"
                     data-hora-inicio="<?php echo esc_attr($timer_activo['hora_inicio']); ?>"
                     data-is-paused="<?php echo $timer_activo['is_paused'] ? '1' : '0'; ?>"
                     data-pausa-inicio="<?php echo esc_attr($timer_activo['pausa_inicio']); ?>">

                    <!-- Contador grande -->
                    <div class="ga-timer-display">
                        <div class="ga-timer-clock" id="ga-timer-clock">
                            <span class="ga-timer-hours">00</span>
                            <span class="ga-timer-separator">:</span>
                            <span class="ga-timer-minutes">00</span>
                            <span class="ga-timer-separator">:</span>
                            <span class="ga-timer-seconds">00</span>
                        </div>
                        <div class="ga-timer-status <?php echo $timer_activo['is_paused'] ? 'ga-status-paused' : 'ga-status-running'; ?>" id="ga-timer-status">
                            <?php if ($timer_activo['is_paused']): ?>
                                <span class="dashicons dashicons-controls-pause"></span>
                                <?php esc_html_e('PAUSADO', 'gestionadmin-wolk'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-controls-play"></span>
                                <?php esc_html_e('ACTIVO', 'gestionadmin-wolk'); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Info de la tarea -->
                    <div class="ga-timer-info">
                        <div class="ga-timer-tarea">
                            <span class="ga-timer-label"><?php esc_html_e('Tarea:', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-timer-value">
                                <strong><?php echo esc_html($timer_activo['tarea_numero']); ?></strong>
                                - <?php echo esc_html($timer_activo['tarea_nombre']); ?>
                            </span>
                        </div>
                        <?php if ($timer_activo['subtarea_nombre']): ?>
                            <div class="ga-timer-subtarea">
                                <span class="ga-timer-label"><?php esc_html_e('Subtarea:', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-timer-value"><?php echo esc_html($timer_activo['subtarea_nombre']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Controles del timer -->
                    <div class="ga-timer-controls">
                        <?php if ($timer_activo['is_paused']): ?>
                            <button type="button" class="ga-btn ga-btn-success ga-btn-lg" id="btn-reanudar">
                                <span class="dashicons dashicons-controls-play"></span>
                                <?php esc_html_e('Reanudar', 'gestionadmin-wolk'); ?>
                            </button>
                        <?php else: ?>
                            <button type="button" class="ga-btn ga-btn-warning ga-btn-lg" id="btn-pausar">
                                <span class="dashicons dashicons-controls-pause"></span>
                                <?php esc_html_e('Pausar', 'gestionadmin-wolk'); ?>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="ga-btn ga-btn-danger ga-btn-lg" id="btn-detener">
                            <span class="dashicons dashicons-controls-stop"></span>
                            <?php esc_html_e('Detener', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </div>

            <?php elseif ($tarea_para_iniciar): ?>
                <!-- =========================================================================
                     INICIAR TIMER PARA TAREA ESPECIFICA
                ========================================================================== -->
                <div class="ga-timer-card ga-timer-start" id="ga-timer-start-container"
                     data-tarea-id="<?php echo esc_attr($tarea_para_iniciar->id); ?>">

                    <div class="ga-start-icon">
                        <span class="dashicons dashicons-controls-play"></span>
                    </div>

                    <h2><?php esc_html_e('Iniciar Timer', 'gestionadmin-wolk'); ?></h2>

                    <div class="ga-start-tarea-info">
                        <p class="ga-start-tarea-numero"><?php echo esc_html($tarea_para_iniciar->numero); ?></p>
                        <p class="ga-start-tarea-nombre"><?php echo esc_html($tarea_para_iniciar->nombre); ?></p>
                    </div>

                    <?php if (!empty($subtareas_para_iniciar)): ?>
                        <div class="ga-start-subtarea-select">
                            <label for="subtarea-select"><?php esc_html_e('Seleccionar subtarea (opcional):', 'gestionadmin-wolk'); ?></label>
                            <select id="subtarea-select" name="subtarea_id">
                                <option value="0"><?php esc_html_e('-- Sin subtarea especifica --', 'gestionadmin-wolk'); ?></option>
                                <?php foreach ($subtareas_para_iniciar as $sub): ?>
                                    <?php if ($sub->estado !== 'COMPLETADA'): ?>
                                        <option value="<?php echo esc_attr($sub->id); ?>">
                                            <?php echo esc_html($sub->codigo . ' - ' . $sub->nombre); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="button" class="ga-btn ga-btn-success ga-btn-xl" id="btn-iniciar-timer">
                        <span class="dashicons dashicons-controls-play"></span>
                        <?php esc_html_e('Iniciar Timer', 'gestionadmin-wolk'); ?>
                    </button>
                </div>

            <?php else: ?>
                <!-- =========================================================================
                     SIN TIMER ACTIVO
                ========================================================================== -->
                <div class="ga-timer-card ga-timer-empty">
                    <div class="ga-empty-icon">
                        <span class="dashicons dashicons-clock"></span>
                    </div>
                    <h2><?php esc_html_e('No tienes timer activo', 'gestionadmin-wolk'); ?></h2>
                    <p><?php esc_html_e('Ve a Mis Tareas para iniciar un timer en una de tus tareas asignadas.', 'gestionadmin-wolk'); ?></p>
                    <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-btn ga-btn-primary ga-btn-lg">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('Ir a Mis Tareas', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- =========================================================================
                 HISTORIAL DEL DIA
            ========================================================================== -->
            <div class="ga-timer-history">
                <h3>
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e('Hoy has trabajado', 'gestionadmin-wolk'); ?>
                    <span class="ga-total-hoy"><?php echo esc_html(number_format($horas_hoy, 1)); ?> <?php esc_html_e('horas', 'gestionadmin-wolk'); ?></span>
                </h3>

                <?php if (empty($registros_hoy)): ?>
                    <div class="ga-history-empty">
                        <p><?php esc_html_e('No tienes registros de tiempo para hoy.', 'gestionadmin-wolk'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="ga-history-list">
                        <?php foreach ($registros_hoy as $registro):
                            // Calcular duracion
                            if ($registro->estado === 'ACTIVO') {
                                $duracion_minutos = round((time() - strtotime($registro->hora_inicio)) / 60);
                            } else {
                                $duracion_minutos = intval($registro->minutos_efectivos);
                            }
                            $horas = floor($duracion_minutos / 60);
                            $minutos = $duracion_minutos % 60;
                            $duracion_str = sprintf('%d:%02d', $horas, $minutos);

                            // Clase de estado
                            $estado_clase = '';
                            switch ($registro->estado) {
                                case 'ACTIVO': $estado_clase = 'ga-estado-activo'; break;
                                case 'BORRADOR': $estado_clase = 'ga-estado-borrador'; break;
                                case 'ENVIADO': $estado_clase = 'ga-estado-enviado'; break;
                                case 'APROBADO': $estado_clase = 'ga-estado-aprobado'; break;
                                case 'RECHAZADO': $estado_clase = 'ga-estado-rechazado'; break;
                                case 'PAGADO': $estado_clase = 'ga-estado-pagado'; break;
                            }
                        ?>
                            <div class="ga-history-item <?php echo $registro->estado === 'ACTIVO' ? 'ga-history-active' : ''; ?>">
                                <div class="ga-history-tarea">
                                    <span class="ga-history-numero"><?php echo esc_html($registro->tarea_numero); ?></span>
                                    <span class="ga-history-nombre"><?php echo esc_html($registro->tarea_nombre); ?></span>
                                    <?php if ($registro->subtarea_nombre): ?>
                                        <span class="ga-history-subtarea"><?php echo esc_html($registro->subtarea_nombre); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ga-history-meta">
                                    <span class="ga-history-duracion"><?php echo esc_html($duracion_str); ?></span>
                                    <span class="ga-history-estado <?php echo esc_attr($estado_clase); ?>">
                                        <?php echo esc_html($estados_registro[$registro->estado] ?? $registro->estado); ?>
                                        <?php if ($registro->estado === 'ACTIVO'): ?>
                                            <span class="ga-timer-indicator"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- =========================================================================
             MODAL: PAUSAR TIMER
        ========================================================================== -->
        <div class="ga-modal" id="modal-pausar" style="display: none;">
            <div class="ga-modal-overlay"></div>
            <div class="ga-modal-content">
                <div class="ga-modal-header">
                    <h3>
                        <span class="dashicons dashicons-controls-pause"></span>
                        <?php esc_html_e('Pausar Timer', 'gestionadmin-wolk'); ?>
                    </h3>
                    <button type="button" class="ga-modal-close" data-dismiss="modal">&times;</button>
                </div>
                <div class="ga-modal-body">
                    <div class="ga-form-group">
                        <label><?php esc_html_e('Motivo de la pausa:', 'gestionadmin-wolk'); ?></label>
                        <div class="ga-radio-group">
                            <?php foreach ($motivos_pausa as $key => $label): ?>
                                <label class="ga-radio-item">
                                    <input type="radio" name="motivo_pausa" value="<?php echo esc_attr($key); ?>" <?php echo $key === 'DESCANSO' ? 'checked' : ''; ?>>
                                    <span class="ga-radio-label"><?php echo esc_html($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="ga-form-group">
                        <label for="nota_pausa"><?php esc_html_e('Nota (opcional):', 'gestionadmin-wolk'); ?></label>
                        <textarea id="nota_pausa" name="nota_pausa" rows="2" placeholder="<?php esc_attr_e('Agregar una nota...', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>
                </div>
                <div class="ga-modal-footer">
                    <button type="button" class="ga-btn ga-btn-outline" data-dismiss="modal">
                        <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                    </button>
                    <button type="button" class="ga-btn ga-btn-warning" id="btn-confirmar-pausa">
                        <span class="dashicons dashicons-controls-pause"></span>
                        <?php esc_html_e('Pausar Timer', 'gestionadmin-wolk'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODAL: DETENER TIMER
        ========================================================================== -->
        <div class="ga-modal" id="modal-detener" style="display: none;">
            <div class="ga-modal-overlay"></div>
            <div class="ga-modal-content">
                <div class="ga-modal-header">
                    <h3>
                        <span class="dashicons dashicons-controls-stop"></span>
                        <?php esc_html_e('Detener Timer', 'gestionadmin-wolk'); ?>
                    </h3>
                    <button type="button" class="ga-modal-close" data-dismiss="modal">&times;</button>
                </div>
                <div class="ga-modal-body">
                    <p class="ga-modal-info">
                        <?php esc_html_e('Al detener el timer, el registro quedara en estado BORRADOR para que puedas enviarlo a revision.', 'gestionadmin-wolk'); ?>
                    </p>
                    <div class="ga-form-group">
                        <label for="descripcion_trabajo"><?php esc_html_e('Descripcion del trabajo realizado:', 'gestionadmin-wolk'); ?></label>
                        <textarea id="descripcion_trabajo" name="descripcion_trabajo" rows="4" placeholder="<?php esc_attr_e('Describe brevemente lo que trabajaste...', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>
                </div>
                <div class="ga-modal-footer">
                    <button type="button" class="ga-btn ga-btn-outline" data-dismiss="modal">
                        <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                    </button>
                    <button type="button" class="ga-btn ga-btn-danger" id="btn-confirmar-detener">
                        <span class="dashicons dashicons-controls-stop"></span>
                        <?php esc_html_e('Detener Timer', 'gestionadmin-wolk'); ?>
                    </button>
                </div>
            </div>
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
   PORTAL EMPLEADO - MI TIMER
   ========================================================================== */
.ga-portal-empleado {
    min-height: 80vh;
    padding: 30px 20px;
    background: #f5f7fa;
}

/* Header */
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

/* Timer Card */
.ga-timer-card {
    background: #fff;
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

/* Timer Activo */
.ga-timer-active {
    border: 3px solid #667eea;
}
.ga-timer-display {
    margin-bottom: 30px;
}
.ga-timer-clock {
    font-size: 72px;
    font-weight: 700;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    color: #333;
    letter-spacing: 4px;
    margin-bottom: 15px;
}
.ga-timer-separator {
    color: #667eea;
    animation: blink 1s infinite;
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
.ga-timer-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.ga-status-running {
    background: #d4edda;
    color: #155724;
}
.ga-status-paused {
    background: #fff3cd;
    color: #856404;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.ga-timer-info {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}
.ga-timer-tarea,
.ga-timer-subtarea {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 8px;
}
.ga-timer-tarea:last-child,
.ga-timer-subtarea:last-child {
    margin-bottom: 0;
}
.ga-timer-label {
    color: #666;
    font-size: 14px;
}
.ga-timer-value {
    color: #333;
    font-size: 15px;
}

.ga-timer-controls {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Timer Empty / Start */
.ga-timer-empty,
.ga-timer-start {
    padding: 60px 40px;
}
.ga-empty-icon,
.ga-start-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
}
.ga-empty-icon .dashicons,
.ga-start-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #fff;
}
.ga-timer-empty h2,
.ga-timer-start h2 {
    margin: 0 0 15px;
    font-size: 24px;
    color: #333;
}
.ga-timer-empty p,
.ga-timer-start p {
    color: #666;
    font-size: 16px;
    margin: 0 0 25px;
}

/* Start Timer */
.ga-start-tarea-info {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}
.ga-start-tarea-numero {
    color: #667eea;
    font-weight: 600;
    font-size: 14px;
    margin: 0 0 5px;
}
.ga-start-tarea-nombre {
    color: #333;
    font-size: 18px;
    font-weight: 500;
    margin: 0;
}
.ga-start-subtarea-select {
    margin-bottom: 25px;
    text-align: left;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.ga-start-subtarea-select label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: #555;
}
.ga-start-subtarea-select select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

/* Buttons */
.ga-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.ga-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}
.ga-btn-lg {
    padding: 14px 28px;
    font-size: 16px;
}
.ga-btn-xl {
    padding: 18px 40px;
    font-size: 18px;
}
.ga-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.ga-btn-primary:hover {
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    color: #fff;
}
.ga-btn-success {
    background: #28a745;
    color: #fff;
}
.ga-btn-success:hover {
    background: #218838;
    color: #fff;
}
.ga-btn-warning {
    background: #ffc107;
    color: #212529;
}
.ga-btn-warning:hover {
    background: #e0a800;
}
.ga-btn-danger {
    background: #dc3545;
    color: #fff;
}
.ga-btn-danger:hover {
    background: #c82333;
    color: #fff;
}
.ga-btn-outline {
    background: transparent;
    border: 1px solid #ddd;
    color: #555;
}
.ga-btn-outline:hover {
    background: #f5f5f5;
    border-color: #ccc;
}

/* History */
.ga-timer-history {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-timer-history h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 20px;
    font-size: 18px;
    color: #333;
}
.ga-timer-history h3 .dashicons {
    color: #667eea;
}
.ga-total-hoy {
    margin-left: auto;
    background: #e8f4fd;
    color: #0066cc;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}
.ga-history-empty {
    text-align: center;
    padding: 30px;
    color: #888;
}
.ga-history-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.ga-history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #ddd;
}
.ga-history-active {
    border-left-color: #28a745;
    background: #f0fff4;
}
.ga-history-tarea {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ga-history-numero {
    color: #667eea;
    font-weight: 600;
    font-size: 12px;
}
.ga-history-nombre {
    color: #333;
    font-size: 14px;
    font-weight: 500;
}
.ga-history-subtarea {
    color: #666;
    font-size: 12px;
}
.ga-history-meta {
    display: flex;
    align-items: center;
    gap: 15px;
}
.ga-history-duracion {
    font-family: 'SF Mono', monospace;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}
.ga-history-estado {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.ga-estado-activo {
    background: #d4edda;
    color: #155724;
}
.ga-estado-borrador {
    background: #e2e3e5;
    color: #383d41;
}
.ga-estado-enviado {
    background: #cce5ff;
    color: #004085;
}
.ga-estado-aprobado {
    background: #d4edda;
    color: #155724;
}
.ga-estado-rechazado {
    background: #f8d7da;
    color: #721c24;
}
.ga-estado-pagado {
    background: #d4edda;
    color: #155724;
}
.ga-timer-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 1s infinite;
}

/* Modal */
.ga-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ga-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}
.ga-modal-content {
    position: relative;
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 450px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    animation: modalIn 0.2s ease-out;
}
@keyframes modalIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.ga-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
}
.ga-modal-header h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    font-size: 18px;
    color: #333;
}
.ga-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #999;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.ga-modal-close:hover {
    color: #333;
}
.ga-modal-body {
    padding: 25px;
}
.ga-modal-info {
    background: #f0f7ff;
    border-left: 4px solid #667eea;
    padding: 12px 15px;
    margin: 0 0 20px;
    font-size: 14px;
    color: #444;
    border-radius: 0 8px 8px 0;
}
.ga-form-group {
    margin-bottom: 20px;
}
.ga-form-group:last-child {
    margin-bottom: 0;
}
.ga-form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 500;
    color: #333;
}
.ga-form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    resize: vertical;
}
.ga-radio-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.ga-radio-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}
.ga-radio-item:hover {
    background: #e9ecef;
}
.ga-radio-item input[type="radio"] {
    margin: 0;
}
.ga-radio-label {
    font-size: 14px;
    color: #333;
}
.ga-modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding: 20px 25px;
    border-top: 1px solid #eee;
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
@media (max-width: 768px) {
    .ga-timer-clock {
        font-size: 48px;
        letter-spacing: 2px;
    }
    .ga-timer-controls {
        flex-direction: column;
    }
    .ga-timer-controls .ga-btn {
        width: 100%;
        justify-content: center;
    }
    .ga-history-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .ga-history-meta {
        width: 100%;
        justify-content: space-between;
    }
    .ga-modal-content {
        margin: 20px;
        width: calc(100% - 40px);
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
    .ga-timer-card {
        padding: 30px 20px;
    }
    .ga-timer-clock {
        font-size: 36px;
    }
    .ga-timer-history h3 {
        flex-wrap: wrap;
    }
    .ga-total-hoy {
        margin-left: 0;
        margin-top: 10px;
    }
}
</style>

<script>
(function() {
    'use strict';

    // =========================================================================
    // CONFIGURACION
    // =========================================================================
    var config = {
        nonce: '<?php echo esc_js($timer_nonce); ?>',
        ajaxUrl: '<?php echo esc_url(rest_url('gestionadmin/v1/timer/')); ?>',
        registroId: null,
        horaInicio: null,
        isPaused: false,
        pausaInicio: null,
        timerInterval: null
    };

    // =========================================================================
    // INICIALIZACION
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function() {
        initTimer();
        initModals();
        initButtons();
    });

    // =========================================================================
    // TIMER
    // =========================================================================
    function initTimer() {
        var container = document.getElementById('ga-timer-container');
        if (!container) return;

        config.registroId = container.dataset.registroId;
        config.horaInicio = container.dataset.horaInicio;
        config.isPaused = container.dataset.isPaused === '1';
        config.pausaInicio = container.dataset.pausaInicio;

        // Iniciar el contador
        updateTimerDisplay();
        config.timerInterval = setInterval(updateTimerDisplay, 1000);
    }

    function updateTimerDisplay() {
        var clockEl = document.getElementById('ga-timer-clock');
        if (!clockEl) return;

        var horaInicio = new Date(config.horaInicio.replace(' ', 'T'));
        var ahora = new Date();
        var diff = Math.floor((ahora - horaInicio) / 1000);

        // Si esta pausado, calcular tiempo hasta el inicio de la pausa
        if (config.isPaused && config.pausaInicio) {
            var pausaInicio = new Date(config.pausaInicio.replace(' ', 'T'));
            diff = Math.floor((pausaInicio - horaInicio) / 1000);
        }

        if (diff < 0) diff = 0;

        var hours = Math.floor(diff / 3600);
        var minutes = Math.floor((diff % 3600) / 60);
        var seconds = diff % 60;

        clockEl.querySelector('.ga-timer-hours').textContent = String(hours).padStart(2, '0');
        clockEl.querySelector('.ga-timer-minutes').textContent = String(minutes).padStart(2, '0');
        clockEl.querySelector('.ga-timer-seconds').textContent = String(seconds).padStart(2, '0');
    }

    // =========================================================================
    // MODALS
    // =========================================================================
    function initModals() {
        // Cerrar modal con overlay o boton X
        document.querySelectorAll('.ga-modal-overlay, .ga-modal-close, [data-dismiss="modal"]').forEach(function(el) {
            el.addEventListener('click', function() {
                closeAllModals();
            });
        });

        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });
    }

    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeAllModals() {
        document.querySelectorAll('.ga-modal').forEach(function(modal) {
            modal.style.display = 'none';
        });
    }

    // =========================================================================
    // BOTONES
    // =========================================================================
    function initButtons() {
        // Boton Pausar
        var btnPausar = document.getElementById('btn-pausar');
        if (btnPausar) {
            btnPausar.addEventListener('click', function() {
                openModal('modal-pausar');
            });
        }

        // Boton Confirmar Pausa
        var btnConfirmarPausa = document.getElementById('btn-confirmar-pausa');
        if (btnConfirmarPausa) {
            btnConfirmarPausa.addEventListener('click', function() {
                var motivo = document.querySelector('input[name="motivo_pausa"]:checked');
                var nota = document.getElementById('nota_pausa');

                if (!motivo) {
                    alert('<?php echo esc_js(__('Selecciona un motivo de pausa', 'gestionadmin-wolk')); ?>');
                    return;
                }

                pausarTimer(motivo.value, nota ? nota.value : '');
            });
        }

        // Boton Reanudar
        var btnReanudar = document.getElementById('btn-reanudar');
        if (btnReanudar) {
            btnReanudar.addEventListener('click', function() {
                reanudarTimer();
            });
        }

        // Boton Detener
        var btnDetener = document.getElementById('btn-detener');
        if (btnDetener) {
            btnDetener.addEventListener('click', function() {
                openModal('modal-detener');
            });
        }

        // Boton Confirmar Detener
        var btnConfirmarDetener = document.getElementById('btn-confirmar-detener');
        if (btnConfirmarDetener) {
            btnConfirmarDetener.addEventListener('click', function() {
                var descripcion = document.getElementById('descripcion_trabajo');
                detenerTimer(descripcion ? descripcion.value : '');
            });
        }

        // Boton Iniciar Timer
        var btnIniciar = document.getElementById('btn-iniciar-timer');
        if (btnIniciar) {
            btnIniciar.addEventListener('click', function() {
                var container = document.getElementById('ga-timer-start-container');
                var tareaId = container ? container.dataset.tareaId : 0;
                var subtareaSelect = document.getElementById('subtarea-select');
                var subtareaId = subtareaSelect ? subtareaSelect.value : 0;

                iniciarTimer(tareaId, subtareaId);
            });
        }
    }

    // =========================================================================
    // API CALLS
    // =========================================================================
    function iniciarTimer(tareaId, subtareaId) {
        var btn = document.getElementById('btn-iniciar-timer');
        btn.disabled = true;
        btn.innerHTML = '<span class="dashicons dashicons-update"></span> <?php echo esc_js(__('Iniciando...', 'gestionadmin-wolk')); ?>';

        fetch(config.ajaxUrl + 'start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce
            },
            body: JSON.stringify({
                tarea_id: tareaId,
                subtarea_id: subtareaId
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || '<?php echo esc_js(__('Error al iniciar timer', 'gestionadmin-wolk')); ?>');
                btn.disabled = false;
                btn.innerHTML = '<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js(__('Iniciar Timer', 'gestionadmin-wolk')); ?>';
            }
        })
        .catch(function(error) {
            alert('<?php echo esc_js(__('Error de conexion', 'gestionadmin-wolk')); ?>');
            btn.disabled = false;
            btn.innerHTML = '<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js(__('Iniciar Timer', 'gestionadmin-wolk')); ?>';
        });
    }

    function pausarTimer(motivo, nota) {
        var btn = document.getElementById('btn-confirmar-pausa');
        btn.disabled = true;

        fetch(config.ajaxUrl + 'pause', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce
            },
            body: JSON.stringify({
                registro_id: config.registroId,
                motivo: motivo,
                nota: nota
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || '<?php echo esc_js(__('Error al pausar timer', 'gestionadmin-wolk')); ?>');
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            alert('<?php echo esc_js(__('Error de conexion', 'gestionadmin-wolk')); ?>');
            btn.disabled = false;
        });
    }

    function reanudarTimer() {
        var btn = document.getElementById('btn-reanudar');
        btn.disabled = true;
        btn.innerHTML = '<span class="dashicons dashicons-update"></span> <?php echo esc_js(__('Reanudando...', 'gestionadmin-wolk')); ?>';

        fetch(config.ajaxUrl + 'resume', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce
            },
            body: JSON.stringify({
                registro_id: config.registroId
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || '<?php echo esc_js(__('Error al reanudar timer', 'gestionadmin-wolk')); ?>');
                btn.disabled = false;
                btn.innerHTML = '<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js(__('Reanudar', 'gestionadmin-wolk')); ?>';
            }
        })
        .catch(function(error) {
            alert('<?php echo esc_js(__('Error de conexion', 'gestionadmin-wolk')); ?>');
            btn.disabled = false;
            btn.innerHTML = '<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js(__('Reanudar', 'gestionadmin-wolk')); ?>';
        });
    }

    function detenerTimer(descripcion) {
        var btn = document.getElementById('btn-confirmar-detener');
        btn.disabled = true;

        fetch(config.ajaxUrl + 'stop', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce
            },
            body: JSON.stringify({
                registro_id: config.registroId,
                descripcion: descripcion
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || '<?php echo esc_js(__('Error al detener timer', 'gestionadmin-wolk')); ?>');
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            alert('<?php echo esc_js(__('Error de conexion', 'gestionadmin-wolk')); ?>');
            btn.disabled = false;
        });
    }

})();
</script>

<?php get_footer(); ?>
