<?php
/**
 * Template: Portal Empleado - Mis Tareas
 *
 * Lista de tareas asignadas al empleado con filtros por estado y prioridad.
 * Muestra subtareas expandibles y botón para iniciar timer.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
 * @since      1.3.0
 * @updated    1.7.0 - Lista de tareas funcional
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

// Verificar que es un empleado registrado
$usuario_ga = GA_Usuarios::get_by_wp_id($wp_user_id);
if (!$usuario_ga) {
    wp_redirect(home_url('/portal-empleado/'));
    exit;
}

// Obtener filtros desde GET
$filtro_estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
$filtro_prioridad = isset($_GET['prioridad']) ? sanitize_text_field($_GET['prioridad']) : '';
$filtro_buscar = isset($_GET['buscar']) ? sanitize_text_field($_GET['buscar']) : '';

// Preparar argumentos para la consulta
$args = array(
    'asignado_a' => $wp_user_id,
    'limit' => 100,
);

if (!empty($filtro_estado)) {
    $args['estado'] = $filtro_estado;
}

if (!empty($filtro_prioridad)) {
    $args['prioridad'] = $filtro_prioridad;
}

// Obtener tareas
$tareas = GA_Tareas::get_all($args);

// Filtrar por búsqueda si existe
if (!empty($filtro_buscar) && !empty($tareas)) {
    $tareas = array_filter($tareas, function($tarea) use ($filtro_buscar) {
        $buscar_lower = strtolower($filtro_buscar);
        return (
            strpos(strtolower($tarea->nombre), $buscar_lower) !== false ||
            strpos(strtolower($tarea->numero), $buscar_lower) !== false
        );
    });
}

// Obtener timer activo para saber si ya hay uno corriendo
$timer_activo = GA_Tareas::get_active_timer($wp_user_id);

// Obtener listas de estados y prioridades
$estados = GA_Tareas::get_estados();
$prioridades = GA_Tareas::get_prioridades();

// Estados para filtro (solo los relevantes para el empleado)
$estados_filtro = array(
    'PENDIENTE' => __('Pendiente', 'gestionadmin-wolk'),
    'EN_PROGRESO' => __('En Progreso', 'gestionadmin-wolk'),
    'EN_REVISION' => __('En Revisión', 'gestionadmin-wolk'),
    'COMPLETADA' => __('Completada', 'gestionadmin-wolk'),
    'RECHAZADA' => __('Rechazada', 'gestionadmin-wolk'),
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
                    <span class="dashicons dashicons-list-view"></span>
                    <?php esc_html_e('Mis Tareas', 'gestionadmin-wolk'); ?>
                </h1>
                <p class="ga-portal-subtitle">
                    <?php esc_html_e('Gestiona y da seguimiento a tus tareas asignadas', 'gestionadmin-wolk'); ?>
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
            <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-nav-item ga-nav-active">
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

        <!-- =========================================================================
             FILTROS
        ========================================================================== -->
        <div class="ga-tareas-filtros">
            <form method="get" class="ga-filtros-form">
                <div class="ga-filtro-group">
                    <label for="filtro-estado"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></label>
                    <select name="estado" id="filtro-estado">
                        <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($estados_filtro as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filtro_estado, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ga-filtro-group">
                    <label for="filtro-prioridad"><?php esc_html_e('Prioridad', 'gestionadmin-wolk'); ?></label>
                    <select name="prioridad" id="filtro-prioridad">
                        <option value=""><?php esc_html_e('Todas', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($prioridades as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filtro_prioridad, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ga-filtro-group ga-filtro-buscar">
                    <label for="filtro-buscar"><?php esc_html_e('Buscar', 'gestionadmin-wolk'); ?></label>
                    <input type="text" name="buscar" id="filtro-buscar" value="<?php echo esc_attr($filtro_buscar); ?>" placeholder="<?php esc_attr_e('Nombre o número...', 'gestionadmin-wolk'); ?>">
                </div>

                <div class="ga-filtro-actions">
                    <button type="submit" class="ga-btn ga-btn-primary">
                        <span class="dashicons dashicons-search"></span>
                        <?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?>
                    </button>
                    <?php if ($filtro_estado || $filtro_prioridad || $filtro_buscar): ?>
                        <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-btn ga-btn-outline">
                            <?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="ga-tareas-count">
                <?php
                printf(
                    esc_html(_n('%d tarea encontrada', '%d tareas encontradas', count($tareas), 'gestionadmin-wolk')),
                    count($tareas)
                );
                ?>
            </div>
        </div>

        <!-- =========================================================================
             LISTA DE TAREAS
        ========================================================================== -->
        <div class="ga-tareas-content">
            <?php if (empty($tareas)): ?>
                <div class="ga-tareas-empty">
                    <div class="ga-empty-icon">
                        <span class="dashicons dashicons-clipboard"></span>
                    </div>
                    <h3><?php esc_html_e('No hay tareas', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('No tienes tareas asignadas que coincidan con los filtros seleccionados.', 'gestionadmin-wolk'); ?></p>
                </div>
            <?php else: ?>
                <div class="ga-tareas-list">
                    <?php foreach ($tareas as $tarea):
                        // Obtener subtareas
                        $subtareas = GA_Tareas::get_subtareas($tarea->id);
                        $total_subtareas = count($subtareas);
                        $subtareas_completadas = 0;
                        foreach ($subtareas as $sub) {
                            if ($sub->estado === 'COMPLETADA') {
                                $subtareas_completadas++;
                            }
                        }

                        // Calcular horas
                        $horas_estimadas = $tarea->minutos_estimados ? round($tarea->minutos_estimados / 60, 1) : 0;
                        $horas_reales = $tarea->minutos_reales ? round($tarea->minutos_reales / 60, 1) : 0;

                        // Determinar si puede iniciar timer
                        $puede_iniciar_timer = !$timer_activo['active'] && in_array($tarea->estado, array('PENDIENTE', 'EN_PROGRESO'));

                        // Verificar si esta tarea tiene el timer activo
                        $es_tarea_activa = $timer_activo['active'] && $timer_activo['tarea_id'] == $tarea->id;

                        // Formatear fecha límite
                        $fecha_limite = '';
                        $clase_vencida = '';
                        if ($tarea->fecha_limite) {
                            $fecha_limite_ts = strtotime($tarea->fecha_limite);
                            $hoy_ts = strtotime('today');
                            $fecha_limite = date_i18n(get_option('date_format'), $fecha_limite_ts);

                            if ($fecha_limite_ts < $hoy_ts && !in_array($tarea->estado, array('COMPLETADA', 'APROBADA', 'PAGADA', 'CANCELADA'))) {
                                $clase_vencida = 'ga-fecha-vencida';
                            } elseif ($fecha_limite_ts === $hoy_ts) {
                                $clase_vencida = 'ga-fecha-hoy';
                            }
                        }

                        // Clase de prioridad
                        $prioridad_clases = array(
                            'URGENTE' => 'ga-prioridad-urgente',
                            'ALTA' => 'ga-prioridad-alta',
                            'MEDIA' => 'ga-prioridad-media',
                            'BAJA' => 'ga-prioridad-baja',
                        );
                        $prioridad_clase = isset($prioridad_clases[$tarea->prioridad]) ? $prioridad_clases[$tarea->prioridad] : '';

                        // Iconos de prioridad
                        $prioridad_iconos = array(
                            'URGENTE' => 'warning',
                            'ALTA' => 'flag',
                            'MEDIA' => 'marker',
                            'BAJA' => 'minus',
                        );
                        $prioridad_icono = isset($prioridad_iconos[$tarea->prioridad]) ? $prioridad_iconos[$tarea->prioridad] : 'marker';

                        // Clase de estado
                        $estado_clases = array(
                            'PENDIENTE' => 'ga-estado-pendiente',
                            'EN_PROGRESO' => 'ga-estado-progreso',
                            'PAUSADA' => 'ga-estado-pausada',
                            'COMPLETADA' => 'ga-estado-completada',
                            'EN_QA' => 'ga-estado-qa',
                            'APROBADA_QA' => 'ga-estado-aprobada',
                            'EN_REVISION' => 'ga-estado-revision',
                            'APROBADA' => 'ga-estado-aprobada',
                            'RECHAZADA' => 'ga-estado-rechazada',
                            'PAGADA' => 'ga-estado-pagada',
                            'CANCELADA' => 'ga-estado-cancelada',
                        );
                        $estado_clase = isset($estado_clases[$tarea->estado]) ? $estado_clases[$tarea->estado] : '';
                    ?>
                        <article class="ga-tarea-card <?php echo esc_attr($prioridad_clase); ?><?php echo $es_tarea_activa ? ' ga-tarea-activa' : ''; ?>">
                            <!-- Header de la tarea -->
                            <header class="ga-tarea-header">
                                <div class="ga-tarea-prioridad">
                                    <span class="dashicons dashicons-<?php echo esc_attr($prioridad_icono); ?>"></span>
                                    <span class="ga-prioridad-label"><?php echo esc_html($prioridades[$tarea->prioridad] ?? $tarea->prioridad); ?></span>
                                </div>
                                <span class="ga-tarea-numero"><?php echo esc_html($tarea->numero); ?></span>
                                <?php if ($es_tarea_activa): ?>
                                    <span class="ga-badge ga-badge-timer">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php esc_html_e('Timer Activo', 'gestionadmin-wolk'); ?>
                                    </span>
                                <?php endif; ?>
                            </header>

                            <!-- Cuerpo de la tarea -->
                            <div class="ga-tarea-body">
                                <h3 class="ga-tarea-titulo"><?php echo esc_html($tarea->nombre); ?></h3>

                                <div class="ga-tarea-meta">
                                    <span class="ga-tarea-estado <?php echo esc_attr($estado_clase); ?>">
                                        <?php echo esc_html($estados[$tarea->estado] ?? $tarea->estado); ?>
                                    </span>

                                    <?php if ($fecha_limite): ?>
                                        <span class="ga-tarea-fecha <?php echo esc_attr($clase_vencida); ?>">
                                            <span class="dashicons dashicons-calendar-alt"></span>
                                            <?php echo esc_html($fecha_limite); ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="ga-tarea-horas">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php
                                        printf(
                                            esc_html__('Est: %sh | Real: %sh', 'gestionadmin-wolk'),
                                            esc_html($horas_estimadas),
                                            esc_html($horas_reales)
                                        );
                                        ?>
                                    </span>
                                </div>

                                <?php if ($total_subtareas > 0): ?>
                                    <div class="ga-tarea-subtareas-resumen">
                                        <span class="dashicons dashicons-editor-ul"></span>
                                        <?php
                                        printf(
                                            esc_html__('Subtareas: %d/%d completadas', 'gestionadmin-wolk'),
                                            $subtareas_completadas,
                                            $total_subtareas
                                        );
                                        ?>
                                        <?php if ($subtareas_completadas === $total_subtareas && $total_subtareas > 0): ?>
                                            <span class="dashicons dashicons-yes" style="color: #28a745;"></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Lista de subtareas expandible -->
                                    <details class="ga-subtareas-details">
                                        <summary><?php esc_html_e('Ver subtareas', 'gestionadmin-wolk'); ?></summary>
                                        <ul class="ga-subtareas-list">
                                            <?php foreach ($subtareas as $subtarea): ?>
                                                <li class="<?php echo $subtarea->estado === 'COMPLETADA' ? 'ga-subtarea-completada' : ''; ?>">
                                                    <span class="dashicons dashicons-<?php echo $subtarea->estado === 'COMPLETADA' ? 'yes' : 'marker'; ?>"></span>
                                                    <span class="ga-subtarea-nombre"><?php echo esc_html($subtarea->nombre); ?></span>
                                                    <?php if ($subtarea->minutos_estimados): ?>
                                                        <span class="ga-subtarea-tiempo"><?php echo esc_html(round($subtarea->minutos_estimados / 60, 1)); ?>h</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </details>
                                <?php endif; ?>

                                <?php if ($tarea->porcentaje_avance > 0): ?>
                                    <div class="ga-tarea-progreso">
                                        <div class="ga-progreso-bar">
                                            <div class="ga-progreso-fill" style="width: <?php echo esc_attr($tarea->porcentaje_avance); ?>%;"></div>
                                        </div>
                                        <span class="ga-progreso-text"><?php echo esc_html($tarea->porcentaje_avance); ?>%</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer de la tarea -->
                            <footer class="ga-tarea-footer">
                                <?php if ($es_tarea_activa): ?>
                                    <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/')); ?>" class="ga-btn ga-btn-primary">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php esc_html_e('Ir al Timer', 'gestionadmin-wolk'); ?>
                                    </a>
                                <?php elseif ($puede_iniciar_timer): ?>
                                    <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/?tarea_id=' . $tarea->id)); ?>" class="ga-btn ga-btn-success">
                                        <span class="dashicons dashicons-controls-play"></span>
                                        <?php esc_html_e('Iniciar Timer', 'gestionadmin-wolk'); ?>
                                    </a>
                                <?php elseif ($timer_activo['active']): ?>
                                    <span class="ga-btn ga-btn-disabled" title="<?php esc_attr_e('Ya tienes un timer activo', 'gestionadmin-wolk'); ?>">
                                        <span class="dashicons dashicons-lock"></span>
                                        <?php esc_html_e('Timer Ocupado', 'gestionadmin-wolk'); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($tarea->descripcion)): ?>
                                    <button type="button" class="ga-btn ga-btn-outline ga-btn-ver-detalle" data-tarea-id="<?php echo esc_attr($tarea->id); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                        <?php esc_html_e('Ver Detalle', 'gestionadmin-wolk'); ?>
                                    </button>
                                <?php endif; ?>
                            </footer>

                            <?php if (!empty($tarea->descripcion)): ?>
                                <!-- Descripción expandible -->
                                <div class="ga-tarea-descripcion" id="descripcion-<?php echo esc_attr($tarea->id); ?>" style="display: none;">
                                    <h4><?php esc_html_e('Descripción / Instrucciones', 'gestionadmin-wolk'); ?></h4>
                                    <div class="ga-descripcion-content">
                                        <?php echo wp_kses_post(wpautop($tarea->descripcion)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </article>
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
   PORTAL EMPLEADO - MIS TAREAS
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
.ga-tareas-filtros {
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
.ga-filtro-buscar input {
    min-width: 200px;
}
.ga-filtro-actions {
    display: flex;
    gap: 10px;
}
.ga-tareas-count {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    font-size: 14px;
    color: #666;
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
}
.ga-btn-success {
    background: #28a745;
    color: #fff;
}
.ga-btn-success:hover {
    background: #218838;
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
.ga-btn-disabled {
    background: #e9ecef;
    color: #999;
    cursor: not-allowed;
}

/* Empty State */
.ga-tareas-empty {
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
.ga-tareas-empty h3 {
    margin: 0 0 10px;
    color: #333;
}
.ga-tareas-empty p {
    margin: 0;
    color: #666;
}

/* Lista de tareas */
.ga-tareas-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Tarea Card */
.ga-tarea-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
    border-left: 4px solid #ddd;
}
.ga-tarea-card.ga-prioridad-urgente { border-left-color: #dc3545; }
.ga-tarea-card.ga-prioridad-alta { border-left-color: #fd7e14; }
.ga-tarea-card.ga-prioridad-media { border-left-color: #ffc107; }
.ga-tarea-card.ga-prioridad-baja { border-left-color: #28a745; }
.ga-tarea-card.ga-tarea-activa {
    border-left-color: #667eea;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.2);
}

/* Tarea Header */
.ga-tarea-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}
.ga-tarea-prioridad {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.ga-prioridad-urgente .ga-tarea-prioridad { color: #dc3545; }
.ga-prioridad-alta .ga-tarea-prioridad { color: #fd7e14; }
.ga-prioridad-media .ga-tarea-prioridad { color: #ffc107; }
.ga-prioridad-baja .ga-tarea-prioridad { color: #28a745; }
.ga-tarea-numero {
    color: #667eea;
    font-weight: 600;
    font-size: 13px;
}
.ga-badge-timer {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 5px;
    background: #667eea;
    color: #fff;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* Tarea Body */
.ga-tarea-body {
    padding: 20px;
}
.ga-tarea-titulo {
    margin: 0 0 15px;
    font-size: 18px;
    color: #333;
}
.ga-tarea-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}
.ga-tarea-estado {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.ga-estado-pendiente { background: #fff3cd; color: #856404; }
.ga-estado-progreso { background: #cce5ff; color: #004085; }
.ga-estado-pausada { background: #e2e3e5; color: #383d41; }
.ga-estado-completada { background: #d4edda; color: #155724; }
.ga-estado-qa { background: #e2d5f1; color: #5a2d82; }
.ga-estado-revision { background: #d1ecf1; color: #0c5460; }
.ga-estado-aprobada { background: #d4edda; color: #155724; }
.ga-estado-rechazada { background: #f8d7da; color: #721c24; }
.ga-estado-pagada { background: #d4edda; color: #155724; }
.ga-estado-cancelada { background: #e2e3e5; color: #383d41; }

.ga-tarea-fecha,
.ga-tarea-horas {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #666;
}
.ga-tarea-fecha .dashicons,
.ga-tarea-horas .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}
.ga-fecha-vencida {
    color: #dc3545;
    font-weight: 600;
}
.ga-fecha-hoy {
    color: #fd7e14;
    font-weight: 600;
}

/* Subtareas resumen */
.ga-tarea-subtareas-resumen {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
}

/* Subtareas expandible */
.ga-subtareas-details {
    margin-top: 10px;
}
.ga-subtareas-details summary {
    cursor: pointer;
    font-size: 13px;
    color: #667eea;
    font-weight: 500;
    padding: 5px 0;
}
.ga-subtareas-details summary:hover {
    text-decoration: underline;
}
.ga-subtareas-list {
    list-style: none;
    margin: 10px 0 0;
    padding: 0;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px 15px;
}
.ga-subtareas-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    font-size: 13px;
}
.ga-subtareas-list li:last-child {
    border-bottom: none;
}
.ga-subtareas-list li .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: #999;
}
.ga-subtarea-completada .dashicons {
    color: #28a745 !important;
}
.ga-subtarea-completada .ga-subtarea-nombre {
    text-decoration: line-through;
    color: #999;
}
.ga-subtarea-tiempo {
    margin-left: auto;
    color: #888;
    font-size: 12px;
}

/* Barra de progreso */
.ga-tarea-progreso {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 15px;
}
.ga-progreso-bar {
    flex-grow: 1;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}
.ga-progreso-fill {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 3px;
    transition: width 0.3s;
}
.ga-progreso-text {
    font-size: 12px;
    font-weight: 600;
    color: #667eea;
    min-width: 35px;
}

/* Tarea Footer */
.ga-tarea-footer {
    display: flex;
    gap: 10px;
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

/* Descripcion expandible */
.ga-tarea-descripcion {
    padding: 20px;
    background: #fafbfc;
    border-top: 1px solid #eee;
}
.ga-tarea-descripcion h4 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #333;
}
.ga-descripcion-content {
    font-size: 14px;
    color: #555;
    line-height: 1.6;
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
    .ga-filtros-form {
        flex-direction: column;
    }
    .ga-filtro-group select,
    .ga-filtro-group input,
    .ga-filtro-buscar input {
        width: 100%;
        min-width: 100%;
    }
    .ga-tarea-meta {
        flex-direction: column;
        gap: 10px;
    }
    .ga-tarea-footer {
        flex-direction: column;
    }
    .ga-tarea-footer .ga-btn {
        width: 100%;
        justify-content: center;
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
    .ga-tarea-header {
        flex-wrap: wrap;
    }
    .ga-badge-timer {
        margin-left: 0;
        margin-top: 10px;
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle descripcion de tarea
    var btnsDetalle = document.querySelectorAll('.ga-btn-ver-detalle');
    btnsDetalle.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tareaId = this.getAttribute('data-tarea-id');
            var descripcion = document.getElementById('descripcion-' + tareaId);
            if (descripcion) {
                if (descripcion.style.display === 'none') {
                    descripcion.style.display = 'block';
                    this.innerHTML = '<span class="dashicons dashicons-hidden"></span> <?php esc_html_e('Ocultar', 'gestionadmin-wolk'); ?>';
                } else {
                    descripcion.style.display = 'none';
                    this.innerHTML = '<span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Ver Detalle', 'gestionadmin-wolk'); ?>';
                }
            }
        });
    });
});
</script>

<?php get_footer(); ?>
