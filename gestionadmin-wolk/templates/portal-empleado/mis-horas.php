<?php
/**
 * Template: Portal Empleado - Mis Horas
 *
 * Historial de horas trabajadas del empleado con filtros por periodo,
 * agrupacion por dia y resumen de totales.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
 * @since      1.3.0
 * @updated    1.9.0 - Historial de horas funcional
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

global $wpdb;

// =========================================================================
// FILTROS DE PERIODO
// =========================================================================
$filtro_periodo = isset($_GET['periodo']) ? sanitize_text_field($_GET['periodo']) : 'este_mes';
$fecha_inicio_custom = isset($_GET['fecha_inicio']) ? sanitize_text_field($_GET['fecha_inicio']) : '';
$fecha_fin_custom = isset($_GET['fecha_fin']) ? sanitize_text_field($_GET['fecha_fin']) : '';

// Calcular fechas segun el periodo seleccionado
$hoy = current_time('Y-m-d');
$fecha_inicio = '';
$fecha_fin = $hoy;

switch ($filtro_periodo) {
    case 'esta_semana':
        // Lunes de esta semana
        $fecha_inicio = date('Y-m-d', strtotime('monday this week'));
        $periodo_label = __('Esta Semana', 'gestionadmin-wolk');
        break;

    case 'este_mes':
        // Primer dia del mes actual
        $fecha_inicio = date('Y-m-01');
        $periodo_label = __('Este Mes', 'gestionadmin-wolk');
        break;

    case 'mes_anterior':
        // Mes anterior completo
        $fecha_inicio = date('Y-m-01', strtotime('first day of last month'));
        $fecha_fin = date('Y-m-t', strtotime('last day of last month'));
        $periodo_label = __('Mes Anterior', 'gestionadmin-wolk');
        break;

    case 'personalizado':
        // Rango personalizado
        if (!empty($fecha_inicio_custom)) {
            $fecha_inicio = $fecha_inicio_custom;
        } else {
            $fecha_inicio = date('Y-m-01');
        }
        if (!empty($fecha_fin_custom)) {
            $fecha_fin = $fecha_fin_custom;
        }
        $periodo_label = __('Personalizado', 'gestionadmin-wolk');
        break;

    default:
        $fecha_inicio = date('Y-m-01');
        $periodo_label = __('Este Mes', 'gestionadmin-wolk');
}

// =========================================================================
// OBTENER REGISTROS DE HORAS
// =========================================================================
$registros = $wpdb->get_results($wpdb->prepare(
    "SELECT rh.*,
            t.nombre as tarea_nombre,
            t.numero as tarea_numero,
            s.nombre as subtarea_nombre
     FROM {$wpdb->prefix}ga_registro_horas rh
     LEFT JOIN {$wpdb->prefix}ga_tareas t ON rh.tarea_id = t.id
     LEFT JOIN {$wpdb->prefix}ga_subtareas s ON rh.subtarea_id = s.id
     WHERE rh.usuario_id = %d
     AND rh.fecha >= %s
     AND rh.fecha <= %s
     ORDER BY rh.fecha DESC, rh.hora_inicio DESC",
    $wp_user_id,
    $fecha_inicio,
    $fecha_fin
));

// =========================================================================
// AGRUPAR POR DIA
// =========================================================================
$registros_por_dia = array();
foreach ($registros as $registro) {
    $fecha = $registro->fecha;
    if (!isset($registros_por_dia[$fecha])) {
        $registros_por_dia[$fecha] = array(
            'fecha' => $fecha,
            'registros' => array(),
            'minutos_totales' => 0,
        );
    }
    $registros_por_dia[$fecha]['registros'][] = $registro;

    // Sumar minutos (si esta activo, calcular tiempo transcurrido)
    if ($registro->estado === 'ACTIVO') {
        $minutos = round((time() - strtotime($registro->hora_inicio)) / 60);
    } else {
        $minutos = intval($registro->minutos_efectivos);
    }
    $registros_por_dia[$fecha]['minutos_totales'] += $minutos;
}

// =========================================================================
// CALCULAR RESUMEN DEL PERIODO
// =========================================================================
$total_minutos = 0;
$minutos_aprobados = 0;
$minutos_pendientes = 0;
$minutos_rechazados = 0;

foreach ($registros as $registro) {
    if ($registro->estado === 'ACTIVO') {
        $minutos = round((time() - strtotime($registro->hora_inicio)) / 60);
    } else {
        $minutos = intval($registro->minutos_efectivos);
    }

    $total_minutos += $minutos;

    switch ($registro->estado) {
        case 'APROBADO':
        case 'PAGADO':
            $minutos_aprobados += $minutos;
            break;
        case 'RECHAZADO':
            $minutos_rechazados += $minutos;
            break;
        default:
            $minutos_pendientes += $minutos;
    }
}

// =========================================================================
// OBTENER TARIFA DEL USUARIO
// =========================================================================
$tarifa_hora = 0;
if ($usuario_ga && $usuario_ga->puesto_id && $usuario_ga->escala_id) {
    $escala = $wpdb->get_row($wpdb->prepare(
        "SELECT tarifa_hora FROM {$wpdb->prefix}ga_puestos_escalas WHERE id = %d",
        $usuario_ga->escala_id
    ));
    if ($escala) {
        $tarifa_hora = floatval($escala->tarifa_hora);
    }
}

// Calcular valor estimado (solo horas aprobadas)
$valor_estimado = round(($minutos_aprobados / 60) * $tarifa_hora, 2);

// Convertir minutos a horas para mostrar
$total_horas = round($total_minutos / 60, 1);
$horas_aprobadas = round($minutos_aprobados / 60, 1);
$horas_pendientes = round($minutos_pendientes / 60, 1);

// =========================================================================
// ESTADOS DE REGISTRO
// =========================================================================
$estados_registro = array(
    'ACTIVO' => __('Activo', 'gestionadmin-wolk'),
    'BORRADOR' => __('Borrador', 'gestionadmin-wolk'),
    'ENVIADO' => __('Enviado', 'gestionadmin-wolk'),
    'APROBADO' => __('Aprobado', 'gestionadmin-wolk'),
    'RECHAZADO' => __('Rechazado', 'gestionadmin-wolk'),
    'PAGADO' => __('Pagado', 'gestionadmin-wolk'),
);

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
                    <span class="dashicons dashicons-backup"></span>
                    <?php esc_html_e('Mis Horas', 'gestionadmin-wolk'); ?>
                </h1>
                <p class="ga-portal-subtitle">
                    <?php esc_html_e('Historial de horas trabajadas y estado de aprobacion', 'gestionadmin-wolk'); ?>
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
            <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e('Mi Timer', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mis-horas/')); ?>" class="ga-nav-item ga-nav-active">
                <span class="dashicons dashicons-backup"></span>
                <?php esc_html_e('Mis Horas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mi-perfil/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-id"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </a>
        </nav>

        <!-- =========================================================================
             FILTROS DE PERIODO
        ========================================================================== -->
        <div class="ga-horas-filtros">
            <form method="get" class="ga-filtros-form">
                <div class="ga-filtro-group">
                    <label for="filtro-periodo"><?php esc_html_e('Periodo', 'gestionadmin-wolk'); ?></label>
                    <select name="periodo" id="filtro-periodo" onchange="toggleCustomDates(this.value)">
                        <option value="esta_semana" <?php selected($filtro_periodo, 'esta_semana'); ?>>
                            <?php esc_html_e('Esta Semana', 'gestionadmin-wolk'); ?>
                        </option>
                        <option value="este_mes" <?php selected($filtro_periodo, 'este_mes'); ?>>
                            <?php esc_html_e('Este Mes', 'gestionadmin-wolk'); ?>
                        </option>
                        <option value="mes_anterior" <?php selected($filtro_periodo, 'mes_anterior'); ?>>
                            <?php esc_html_e('Mes Anterior', 'gestionadmin-wolk'); ?>
                        </option>
                        <option value="personalizado" <?php selected($filtro_periodo, 'personalizado'); ?>>
                            <?php esc_html_e('Personalizado', 'gestionadmin-wolk'); ?>
                        </option>
                    </select>
                </div>

                <div class="ga-filtro-group ga-filtro-fechas" id="filtro-fechas-custom" style="<?php echo $filtro_periodo !== 'personalizado' ? 'display: none;' : ''; ?>">
                    <label for="fecha-inicio"><?php esc_html_e('Desde', 'gestionadmin-wolk'); ?></label>
                    <input type="date" name="fecha_inicio" id="fecha-inicio" value="<?php echo esc_attr($fecha_inicio_custom ?: $fecha_inicio); ?>">
                </div>

                <div class="ga-filtro-group ga-filtro-fechas" id="filtro-fechas-custom2" style="<?php echo $filtro_periodo !== 'personalizado' ? 'display: none;' : ''; ?>">
                    <label for="fecha-fin"><?php esc_html_e('Hasta', 'gestionadmin-wolk'); ?></label>
                    <input type="date" name="fecha_fin" id="fecha-fin" value="<?php echo esc_attr($fecha_fin_custom ?: $fecha_fin); ?>">
                </div>

                <div class="ga-filtro-actions">
                    <button type="submit" class="ga-btn ga-btn-primary">
                        <span class="dashicons dashicons-filter"></span>
                        <?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?>
                    </button>
                </div>
            </form>

            <div class="ga-periodo-info">
                <span class="dashicons dashicons-calendar-alt"></span>
                <?php
                printf(
                    esc_html__('%s: %s al %s', 'gestionadmin-wolk'),
                    esc_html($periodo_label),
                    esc_html(date_i18n(get_option('date_format'), strtotime($fecha_inicio))),
                    esc_html(date_i18n(get_option('date_format'), strtotime($fecha_fin)))
                );
                ?>
            </div>
        </div>

        <!-- =========================================================================
             RESUMEN DEL PERIODO
        ========================================================================== -->
        <div class="ga-horas-resumen">
            <div class="ga-resumen-card">
                <div class="ga-resumen-icon ga-icon-total">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="ga-resumen-info">
                    <span class="ga-resumen-value"><?php echo esc_html(number_format($total_horas, 1)); ?></span>
                    <span class="ga-resumen-label"><?php esc_html_e('Horas Totales', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-resumen-card">
                <div class="ga-resumen-icon ga-icon-aprobadas">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="ga-resumen-info">
                    <span class="ga-resumen-value"><?php echo esc_html(number_format($horas_aprobadas, 1)); ?></span>
                    <span class="ga-resumen-label"><?php esc_html_e('Horas Aprobadas', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-resumen-card">
                <div class="ga-resumen-icon ga-icon-pendientes">
                    <span class="dashicons dashicons-hourglass"></span>
                </div>
                <div class="ga-resumen-info">
                    <span class="ga-resumen-value"><?php echo esc_html(number_format($horas_pendientes, 1)); ?></span>
                    <span class="ga-resumen-label"><?php esc_html_e('Horas Pendientes', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-resumen-card">
                <div class="ga-resumen-icon ga-icon-valor">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div class="ga-resumen-info">
                    <span class="ga-resumen-value">$<?php echo esc_html(number_format($valor_estimado, 2)); ?></span>
                    <span class="ga-resumen-label"><?php esc_html_e('Valor Estimado', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             LISTA DE REGISTROS POR DIA
        ========================================================================== -->
        <div class="ga-horas-content">
            <?php if (empty($registros_por_dia)): ?>
                <div class="ga-horas-empty">
                    <div class="ga-empty-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <h3><?php esc_html_e('Sin registros', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('No tienes registros de horas en el periodo seleccionado.', 'gestionadmin-wolk'); ?></p>
                    <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-btn ga-btn-primary">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('Ir a Mis Tareas', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="ga-horas-lista">
                    <?php foreach ($registros_por_dia as $fecha => $dia_data):
                        // Formatear fecha del dia
                        $fecha_ts = strtotime($fecha);
                        $dia_nombre = date_i18n('l', $fecha_ts);
                        $dia_numero = date_i18n('j', $fecha_ts);
                        $mes_nombre = date_i18n('F', $fecha_ts);

                        // Formatear total del dia
                        $horas_dia = floor($dia_data['minutos_totales'] / 60);
                        $minutos_dia = $dia_data['minutos_totales'] % 60;
                    ?>
                        <div class="ga-dia-grupo">
                            <div class="ga-dia-header">
                                <div class="ga-dia-fecha">
                                    <span class="ga-dia-nombre"><?php echo esc_html(ucfirst($dia_nombre)); ?></span>
                                    <span class="ga-dia-completa"><?php echo esc_html($dia_numero . ' de ' . $mes_nombre); ?></span>
                                </div>
                                <div class="ga-dia-total">
                                    <span class="ga-dia-horas"><?php echo esc_html(sprintf('%d:%02d', $horas_dia, $minutos_dia)); ?></span>
                                    <span class="ga-dia-label"><?php esc_html_e('horas', 'gestionadmin-wolk'); ?></span>
                                </div>
                            </div>

                            <div class="ga-dia-registros">
                                <?php foreach ($dia_data['registros'] as $registro):
                                    // Calcular duracion
                                    if ($registro->estado === 'ACTIVO') {
                                        $duracion_minutos = round((time() - strtotime($registro->hora_inicio)) / 60);
                                    } else {
                                        $duracion_minutos = intval($registro->minutos_efectivos);
                                    }
                                    $dur_horas = floor($duracion_minutos / 60);
                                    $dur_mins = $duracion_minutos % 60;
                                    $duracion_str = sprintf('%d:%02d', $dur_horas, $dur_mins);

                                    // Hora de inicio y fin
                                    $hora_inicio = date_i18n('H:i', strtotime($registro->hora_inicio));
                                    $hora_fin = $registro->hora_fin ? date_i18n('H:i', strtotime($registro->hora_fin)) : '--:--';

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
                                    <div class="ga-registro-item <?php echo $registro->estado === 'ACTIVO' ? 'ga-registro-activo' : ''; ?>">
                                        <div class="ga-registro-tarea">
                                            <span class="ga-registro-numero"><?php echo esc_html($registro->tarea_numero); ?></span>
                                            <span class="ga-registro-nombre"><?php echo esc_html($registro->tarea_nombre); ?></span>
                                            <?php if ($registro->subtarea_nombre): ?>
                                                <span class="ga-registro-subtarea"><?php echo esc_html($registro->subtarea_nombre); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="ga-registro-horario">
                                            <span class="ga-registro-horas"><?php echo esc_html($hora_inicio); ?> - <?php echo esc_html($hora_fin); ?></span>
                                        </div>

                                        <div class="ga-registro-duracion">
                                            <span class="ga-duracion-valor"><?php echo esc_html($duracion_str); ?></span>
                                        </div>

                                        <div class="ga-registro-estado">
                                            <span class="ga-estado-badge <?php echo esc_attr($estado_clase); ?>">
                                                <?php echo esc_html($estados_registro[$registro->estado] ?? $registro->estado); ?>
                                                <?php if ($registro->estado === 'ACTIVO'): ?>
                                                    <span class="ga-timer-indicator"></span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
   PORTAL EMPLEADO - MIS HORAS
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

/* Filtros */
.ga-horas-filtros {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-filtros-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
    margin-bottom: 15px;
}
.ga-filtro-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.ga-filtro-group label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
}
.ga-filtro-group select,
.ga-filtro-group input {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    min-width: 150px;
}
.ga-filtro-actions {
    display: flex;
    gap: 10px;
}
.ga-periodo-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    font-size: 14px;
    color: #666;
}
.ga-periodo-info .dashicons {
    color: #667eea;
}

/* Buttons */
.ga-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.ga-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}
.ga-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.ga-btn-primary:hover {
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    color: #fff;
}

/* Resumen */
.ga-horas-resumen {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.ga-resumen-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-resumen-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ga-resumen-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #fff;
}
.ga-icon-total {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.ga-icon-aprobadas {
    background: linear-gradient(135deg, #28a745 0%, #20803a 100%);
}
.ga-icon-pendientes {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
}
.ga-icon-valor {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}
.ga-resumen-info {
    display: flex;
    flex-direction: column;
}
.ga-resumen-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    line-height: 1.2;
}
.ga-resumen-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
}

/* Empty State */
.ga-horas-empty {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-empty-icon {
    width: 80px;
    height: 80px;
    background: #f0f2f5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}
.ga-empty-icon .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    color: #aaa;
}
.ga-horas-empty h3 {
    margin: 0 0 10px;
    color: #333;
}
.ga-horas-empty p {
    margin: 0 0 20px;
    color: #666;
}

/* Lista de dias */
.ga-horas-lista {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Grupo por dia */
.ga-dia-grupo {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.ga-dia-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}
.ga-dia-fecha {
    display: flex;
    flex-direction: column;
}
.ga-dia-nombre {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}
.ga-dia-completa {
    font-size: 13px;
    color: #666;
}
.ga-dia-total {
    display: flex;
    align-items: baseline;
    gap: 5px;
}
.ga-dia-horas {
    font-size: 20px;
    font-weight: 700;
    font-family: 'SF Mono', monospace;
    color: #667eea;
}
.ga-dia-label {
    font-size: 12px;
    color: #888;
}

/* Registros del dia */
.ga-dia-registros {
    padding: 0;
}
.ga-registro-item {
    display: grid;
    grid-template-columns: 1fr auto auto auto;
    gap: 20px;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
}
.ga-registro-item:last-child {
    border-bottom: none;
}
.ga-registro-activo {
    background: #f0fff4;
}
.ga-registro-tarea {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.ga-registro-numero {
    font-size: 12px;
    font-weight: 600;
    color: #667eea;
}
.ga-registro-nombre {
    font-size: 14px;
    color: #333;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ga-registro-subtarea {
    font-size: 12px;
    color: #888;
}
.ga-registro-horario {
    text-align: center;
}
.ga-registro-horas {
    font-size: 13px;
    color: #666;
    font-family: 'SF Mono', monospace;
}
.ga-registro-duracion {
    text-align: center;
    min-width: 60px;
}
.ga-duracion-valor {
    font-size: 16px;
    font-weight: 600;
    font-family: 'SF Mono', monospace;
    color: #333;
}
.ga-registro-estado {
    min-width: 100px;
    text-align: right;
}
.ga-estado-badge {
    display: inline-flex;
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
    background: #c3e6cb;
    color: #0b5a20;
}
.ga-timer-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
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
    .ga-horas-resumen {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 768px) {
    .ga-filtros-form {
        flex-direction: column;
    }
    .ga-filtro-group select,
    .ga-filtro-group input {
        width: 100%;
        min-width: 100%;
    }
    .ga-horas-resumen {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .ga-resumen-card {
        padding: 15px;
    }
    .ga-resumen-value {
        font-size: 20px;
    }
    .ga-registro-item {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .ga-registro-horario,
    .ga-registro-duracion,
    .ga-registro-estado {
        text-align: left;
    }
    .ga-registro-item > div {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ga-registro-tarea {
        flex-direction: column;
        align-items: flex-start;
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
    .ga-horas-resumen {
        grid-template-columns: 1fr;
    }
    .ga-dia-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
function toggleCustomDates(value) {
    var customDates = document.querySelectorAll('.ga-filtro-fechas');
    customDates.forEach(function(el) {
        el.style.display = value === 'personalizado' ? 'flex' : 'none';
    });
}
</script>

<?php get_footer(); ?>
