<?php
/**
 * Vista: Tareas con Timer
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Filtros - Sprint 5-6: Agregado filtro por proyecto
$filtro_estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
$filtro_usuario = isset($_GET['usuario']) ? absint($_GET['usuario']) : 0;
$filtro_proyecto = isset($_GET['proyecto']) ? absint($_GET['proyecto']) : 0;

// Obtener tareas con filtros aplicados
$tareas = GA_Tareas::get_all(array(
    'estado' => $filtro_estado,
    'asignado_a' => $filtro_usuario,
    'proyecto_id' => $filtro_proyecto,
));

// Obtener datos para selectores
$estados = GA_Tareas::get_estados();
$prioridades = GA_Tareas::get_prioridades();
$motivos_pausa = GA_Tareas::get_motivos_pausa();
$usuarios = GA_Usuarios::get_for_dropdown();

// Sprint 5-6: Datos para selector de proyecto
$proyectos = GA_Proyectos::get_for_dropdown();
$clientes = GA_Clientes::get_for_dropdown();
$casos = GA_Casos::get_for_dropdown();

// Vista: listado o edición
$edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
$tarea_edit = $edit_id > 0 ? GA_Tareas::get($edit_id) : null;
$subtareas_edit = $edit_id > 0 ? GA_Tareas::get_subtareas($edit_id) : array();
?>
<div class="wrap ga-admin">
    <h1>
        <?php esc_html_e('Tareas', 'gestionadmin-wolk'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-tareas&edit=0')); ?>" class="page-title-action">
            <?php esc_html_e('Nueva Tarea', 'gestionadmin-wolk'); ?>
        </a>
    </h1>

    <!-- Timer Widget (siempre visible) -->
    <div class="ga-card" id="ga-timer-widget" style="margin-bottom: 20px; background: #f0f6fc;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0;"><?php esc_html_e('Timer', 'gestionadmin-wolk'); ?></h3>
                <p id="ga-timer-task-info" style="margin: 5px 0 0 0;"></p>
            </div>
            <div style="text-align: center;">
                <div id="ga-timer-display" style="font-size: 48px; font-weight: bold; font-family: monospace;">
                    00:00:00
                </div>
                <div id="ga-timer-status" style="margin-top: 5px;"></div>
            </div>
            <div id="ga-timer-controls">
                <button type="button" class="ga-btn ga-btn-success" id="ga-timer-btn-resume" style="display:none;">
                    <?php esc_html_e('Reanudar', 'gestionadmin-wolk'); ?>
                </button>
                <button type="button" class="ga-btn" id="ga-timer-btn-pause" style="display:none;">
                    <?php esc_html_e('Pausar', 'gestionadmin-wolk'); ?>
                </button>
                <button type="button" class="ga-btn ga-btn-danger" id="ga-timer-btn-stop" style="display:none;">
                    <?php esc_html_e('Detener', 'gestionadmin-wolk'); ?>
                </button>
                <span id="ga-timer-inactive"><?php esc_html_e('Sin timer activo', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>
    </div>

    <?php if ($edit_id !== false && isset($_GET['edit'])) : ?>
        <!-- Formulario Crear/Editar Tarea -->
        <div class="ga-card">
            <div class="ga-card-header">
                <h2><?php echo $tarea_edit ? esc_html__('Editar Tarea', 'gestionadmin-wolk') : esc_html__('Nueva Tarea', 'gestionadmin-wolk'); ?></h2>
            </div>

            <form id="ga-form-tarea">
                <input type="hidden" name="id" id="tarea-id" value="<?php echo esc_attr($edit_id); ?>">

                <!-- Sprint 5-6: Selector de Proyecto/Caso -->
                <div class="ga-card" style="background: #f9f9f9; padding: 15px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: var(--ga-primary);">
                        <?php esc_html_e('Asignación a Proyecto (Opcional)', 'gestionadmin-wolk'); ?>
                    </h4>
                    <div class="ga-row">
                        <!-- Selector de Proyecto -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group" style="margin-bottom: 0;">
                                <label class="ga-form-label" for="tarea-proyecto">
                                    <?php esc_html_e('Proyecto', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="tarea-proyecto" name="proyecto_id" class="ga-form-select">
                                    <option value=""><?php esc_html_e('-- Sin proyecto --', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($proyectos as $proy) : ?>
                                        <option value="<?php echo esc_attr($proy->id); ?>"
                                            <?php selected($tarea_edit ? $tarea_edit->proyecto_id : '', $proy->id); ?>
                                            data-caso="<?php echo esc_attr($proy->caso_id); ?>">
                                            <?php echo esc_html($proy->codigo . ' - ' . $proy->nombre); ?>
                                            <small>(<?php echo esc_html($proy->cliente_nombre); ?>)</small>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Info del caso (solo lectura) -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group" style="margin-bottom: 0;">
                                <label class="ga-form-label"><?php esc_html_e('Caso Asociado', 'gestionadmin-wolk'); ?></label>
                                <p id="tarea-caso-info" style="padding: 8px; background: #fff; border: 1px solid var(--ga-border); border-radius: 3px; margin: 0;">
                                    <?php
                                    if ($tarea_edit && $tarea_edit->caso_id) {
                                        $caso_info = GA_Casos::get($tarea_edit->caso_id);
                                        echo esc_html($caso_info ? $caso_info->numero . ' - ' . $caso_info->titulo : '-');
                                    } else {
                                        echo '<em>' . esc_html__('Selecciona un proyecto', 'gestionadmin-wolk') . '</em>';
                                    }
                                    ?>
                                </p>
                                <input type="hidden" id="tarea-caso" name="caso_id"
                                       value="<?php echo $tarea_edit ? esc_attr($tarea_edit->caso_id) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ga-row">
                    <div class="ga-col ga-col-6">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-nombre">
                                <?php esc_html_e('Nombre de la Tarea', 'gestionadmin-wolk'); ?> *
                            </label>
                            <input type="text" id="tarea-nombre" name="nombre" class="ga-form-input" required
                                   value="<?php echo $tarea_edit ? esc_attr($tarea_edit->nombre) : ''; ?>">
                        </div>
                    </div>
                    <div class="ga-col ga-col-3">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-prioridad">
                                <?php esc_html_e('Prioridad', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="tarea-prioridad" name="prioridad" class="ga-form-select">
                                <?php foreach ($prioridades as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"
                                        <?php selected($tarea_edit ? $tarea_edit->prioridad : 'MEDIA', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="ga-col ga-col-3">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-estado">
                                <?php esc_html_e('Estado', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="tarea-estado" name="estado" class="ga-form-select">
                                <?php foreach ($estados as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"
                                        <?php selected($tarea_edit ? $tarea_edit->estado : 'PENDIENTE', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="ga-form-group">
                    <label class="ga-form-label" for="tarea-descripcion">
                        <?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?>
                    </label>
                    <textarea id="tarea-descripcion" name="descripcion" class="ga-form-textarea" rows="3"><?php echo $tarea_edit ? esc_textarea($tarea_edit->descripcion) : ''; ?></textarea>
                </div>

                <div class="ga-row">
                    <div class="ga-col ga-col-4">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-asignado">
                                <?php esc_html_e('Asignado a', 'gestionadmin-wolk'); ?> *
                            </label>
                            <select id="tarea-asignado" name="asignado_a" class="ga-form-select" required>
                                <option value=""><?php esc_html_e('-- Seleccionar --', 'gestionadmin-wolk'); ?></option>
                                <?php foreach ($usuarios as $u) : ?>
                                    <option value="<?php echo esc_attr($u->usuario_wp_id); ?>"
                                        <?php selected($tarea_edit ? $tarea_edit->asignado_a : '', $u->usuario_wp_id); ?>>
                                        <?php echo esc_html($u->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="ga-col ga-col-4">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-supervisor">
                                <?php esc_html_e('Supervisor', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="tarea-supervisor" name="supervisor_id" class="ga-form-select">
                                <option value="0"><?php esc_html_e('-- Sin supervisor --', 'gestionadmin-wolk'); ?></option>
                                <?php foreach ($usuarios as $u) : ?>
                                    <option value="<?php echo esc_attr($u->usuario_wp_id); ?>"
                                        <?php selected($tarea_edit ? $tarea_edit->supervisor_id : '', $u->usuario_wp_id); ?>>
                                        <?php echo esc_html($u->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="ga-col ga-col-4">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-horas">
                                <?php esc_html_e('Horas Estimadas', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="number" id="tarea-horas" name="horas_estimadas" class="ga-form-input"
                                   step="0.5" min="0" value="<?php echo $tarea_edit ? esc_attr($tarea_edit->horas_estimadas) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="ga-row">
                    <div class="ga-col ga-col-6">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-inicio">
                                <?php esc_html_e('Fecha Inicio', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="date" id="tarea-inicio" name="fecha_inicio" class="ga-form-input"
                                   value="<?php echo $tarea_edit ? esc_attr($tarea_edit->fecha_inicio) : date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="ga-col ga-col-6">
                        <div class="ga-form-group">
                            <label class="ga-form-label" for="tarea-limite">
                                <?php esc_html_e('Fecha Límite', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="date" id="tarea-limite" name="fecha_limite" class="ga-form-input"
                                   value="<?php echo $tarea_edit ? esc_attr($tarea_edit->fecha_limite) : ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- Subtareas -->
                <div class="ga-form-group" style="margin-top: 20px;">
                    <label class="ga-form-label"><?php esc_html_e('Subtareas', 'gestionadmin-wolk'); ?></label>

                    <table class="ga-table" id="ga-subtareas-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                                <th style="width: 120px;"><?php esc_html_e('Horas Est.', 'gestionadmin-wolk'); ?></th>
                                <th style="width: 80px;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="ga-subtareas-body">
                            <?php if (!empty($subtareas_edit)) : ?>
                                <?php foreach ($subtareas_edit as $i => $sub) : ?>
                                    <tr data-id="<?php echo esc_attr($sub->id); ?>">
                                        <td><?php echo esc_html($i + 1); ?></td>
                                        <td><input type="text" class="ga-form-input subtarea-nombre" value="<?php echo esc_attr($sub->nombre); ?>"></td>
                                        <td><input type="number" class="ga-form-input subtarea-horas" step="0.5" min="0" value="<?php echo esc_attr($sub->horas_estimadas); ?>"></td>
                                        <td><a href="#" class="ga-btn-remove-subtarea" style="color:#d63638;"><?php esc_html_e('Quitar', 'gestionadmin-wolk'); ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <button type="button" class="ga-btn" id="ga-btn-add-subtarea" style="margin-top: 10px;">
                        + <?php esc_html_e('Agregar Subtarea', 'gestionadmin-wolk'); ?>
                    </button>
                </div>

                <div class="ga-form-group" style="margin-top: 20px;">
                    <button type="submit" class="ga-btn ga-btn-primary">
                        <?php esc_html_e('Guardar Tarea', 'gestionadmin-wolk'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-tareas')); ?>" class="ga-btn">
                        <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            </form>
        </div>
    <?php else : ?>
        <!-- Listado de Tareas -->
        <div class="ga-card">
            <!-- Filtros - Sprint 5-6: Agregado filtro por proyecto -->
            <form method="get" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="gestionadmin-tareas">
                <div class="ga-row">
                    <!-- Filtro por estado -->
                    <div class="ga-col ga-col-3">
                        <label class="ga-form-label" style="font-size: 12px; margin-bottom: 3px;">
                            <?php esc_html_e('Estado', 'gestionadmin-wolk'); ?>
                        </label>
                        <select name="estado" class="ga-form-select">
                            <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($estados as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($filtro_estado, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filtro por usuario -->
                    <div class="ga-col ga-col-3">
                        <label class="ga-form-label" style="font-size: 12px; margin-bottom: 3px;">
                            <?php esc_html_e('Usuario', 'gestionadmin-wolk'); ?>
                        </label>
                        <select name="usuario" class="ga-form-select">
                            <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($usuarios as $u) : ?>
                                <option value="<?php echo esc_attr($u->usuario_wp_id); ?>" <?php selected($filtro_usuario, $u->usuario_wp_id); ?>>
                                    <?php echo esc_html($u->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sprint 5-6: Filtro por proyecto -->
                    <div class="ga-col ga-col-3">
                        <label class="ga-form-label" style="font-size: 12px; margin-bottom: 3px;">
                            <?php esc_html_e('Proyecto', 'gestionadmin-wolk'); ?>
                        </label>
                        <select name="proyecto" class="ga-form-select">
                            <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($proyectos as $proy) : ?>
                                <option value="<?php echo esc_attr($proy->id); ?>" <?php selected($filtro_proyecto, $proy->id); ?>>
                                    <?php echo esc_html($proy->codigo . ' - ' . $proy->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="ga-col ga-col-3" style="padding-top: 22px;">
                        <button type="submit" class="ga-btn ga-btn-primary"><?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?></button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-tareas')); ?>" class="ga-btn">
                            <?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?>
                        </a>
                    </div>
                </div>
            </form>

            <?php if (empty($tareas)) : ?>
                <p><?php esc_html_e('No hay tareas que coincidan con los filtros.', 'gestionadmin-wolk'); ?></p>
            <?php else : ?>
                <table class="ga-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Número', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Asignado', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Prioridad', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Horas', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tareas as $tarea) : ?>
                            <tr>
                                <td><code><?php echo esc_html($tarea->numero); ?></code></td>
                                <td>
                                    <strong><?php echo esc_html($tarea->nombre); ?></strong>
                                    <?php if ($tarea->porcentaje_avance > 0) : ?>
                                        <div style="background:#e0e0e0; height:4px; margin-top:5px; border-radius:2px;">
                                            <div style="background:#00a32a; height:4px; width:<?php echo esc_attr($tarea->porcentaje_avance); ?>%; border-radius:2px;"></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($tarea->asignado_nombre); ?></td>
                                <td>
                                    <?php
                                    $estado_class = 'ga-badge-warning';
                                    if (in_array($tarea->estado, array('COMPLETADA', 'APROBADA', 'PAGADA'))) {
                                        $estado_class = 'ga-badge-success';
                                    } elseif (in_array($tarea->estado, array('RECHAZADA', 'CANCELADA'))) {
                                        $estado_class = 'ga-badge-danger';
                                    }
                                    ?>
                                    <span class="ga-badge <?php echo esc_attr($estado_class); ?>">
                                        <?php echo esc_html($estados[$tarea->estado] ?? $tarea->estado); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $prioridad_style = '';
                                    if ($tarea->prioridad === 'URGENTE') $prioridad_style = 'color:#d63638;font-weight:bold;';
                                    elseif ($tarea->prioridad === 'ALTA') $prioridad_style = 'color:#dba617;';
                                    ?>
                                    <span style="<?php echo esc_attr($prioridad_style); ?>">
                                        <?php echo esc_html($prioridades[$tarea->prioridad] ?? $tarea->prioridad); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(($tarea->horas_reales ?: '0') . ' / ' . ($tarea->horas_estimadas ?: '-')); ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-tareas&edit=' . $tarea->id)); ?>">
                                        <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                    </a> |
                                    <a href="#" class="ga-btn-start-timer" data-id="<?php echo esc_attr($tarea->id); ?>" data-nombre="<?php echo esc_attr($tarea->nombre); ?>">
                                        <?php esc_html_e('Iniciar Timer', 'gestionadmin-wolk'); ?>
                                    </a> |
                                    <a href="#" class="ga-btn-delete-tarea" data-id="<?php echo esc_attr($tarea->id); ?>" style="color:#d63638;">
                                        <?php esc_html_e('Eliminar', 'gestionadmin-wolk'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Pausa -->
<div id="ga-modal-pausa" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:#fff; max-width:400px; margin:100px auto; padding:20px; border-radius:4px;">
        <h3 style="margin-top:0;"><?php esc_html_e('Pausar Timer', 'gestionadmin-wolk'); ?></h3>
        <div class="ga-form-group">
            <label class="ga-form-label"><?php esc_html_e('Motivo de la pausa', 'gestionadmin-wolk'); ?></label>
            <select id="pausa-motivo" class="ga-form-select">
                <?php foreach ($motivos_pausa as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ga-form-group">
            <label class="ga-form-label"><?php esc_html_e('Nota (opcional)', 'gestionadmin-wolk'); ?></label>
            <input type="text" id="pausa-nota" class="ga-form-input">
        </div>
        <button type="button" class="ga-btn ga-btn-primary" id="ga-btn-confirm-pausa"><?php esc_html_e('Pausar', 'gestionadmin-wolk'); ?></button>
        <button type="button" class="ga-btn" id="ga-btn-cancel-pausa"><?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?></button>
    </div>
</div>

<!-- Modal Detener Timer -->
<div id="ga-modal-stop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:#fff; max-width:400px; margin:100px auto; padding:20px; border-radius:4px;">
        <h3 style="margin-top:0;"><?php esc_html_e('Detener Timer', 'gestionadmin-wolk'); ?></h3>
        <div class="ga-form-group">
            <label class="ga-form-label"><?php esc_html_e('Descripción del trabajo realizado', 'gestionadmin-wolk'); ?></label>
            <textarea id="stop-descripcion" class="ga-form-textarea" rows="3"></textarea>
        </div>
        <button type="button" class="ga-btn ga-btn-primary" id="ga-btn-confirm-stop"><?php esc_html_e('Detener y Guardar', 'gestionadmin-wolk'); ?></button>
        <button type="button" class="ga-btn" id="ga-btn-cancel-stop"><?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?></button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // =========================================================================
    // TIMER
    // =========================================================================
    var timerData = {
        active: false,
        registro_id: null,
        hora_inicio: null,
        is_paused: false,
        pausa_inicio: null,
        total_pausas_ms: 0
    };

    var timerInterval = null;

    function formatTime(totalSeconds) {
        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        return String(hours).padStart(2, '0') + ':' +
               String(minutes).padStart(2, '0') + ':' +
               String(seconds).padStart(2, '0');
    }

    function updateTimerDisplay() {
        if (!timerData.active || !timerData.hora_inicio) return;

        var now = new Date().getTime();
        var inicio = new Date(timerData.hora_inicio).getTime();
        var elapsed = now - inicio;

        // Restar tiempo de pausas
        if (timerData.is_paused && timerData.pausa_inicio) {
            var pausaStart = new Date(timerData.pausa_inicio).getTime();
            elapsed -= (now - pausaStart);
        }

        elapsed -= timerData.total_pausas_ms;

        var seconds = Math.max(0, Math.floor(elapsed / 1000));
        $('#ga-timer-display').text(formatTime(seconds));
    }

    function startTimerInterval() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(updateTimerDisplay, 1000);
        updateTimerDisplay();
    }

    function stopTimerInterval() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    function updateTimerUI() {
        if (timerData.active) {
            $('#ga-timer-inactive').hide();
            $('#ga-timer-btn-stop').show();

            if (timerData.is_paused) {
                $('#ga-timer-btn-pause').hide();
                $('#ga-timer-btn-resume').show();
                $('#ga-timer-status').html('<span class="ga-badge ga-badge-warning"><?php echo esc_js(__('PAUSADO', 'gestionadmin-wolk')); ?></span>');
                stopTimerInterval();
            } else {
                $('#ga-timer-btn-pause').show();
                $('#ga-timer-btn-resume').hide();
                $('#ga-timer-status').html('<span class="ga-badge ga-badge-success"><?php echo esc_js(__('EN CURSO', 'gestionadmin-wolk')); ?></span>');
                startTimerInterval();
            }
        } else {
            $('#ga-timer-inactive').show();
            $('#ga-timer-btn-pause, #ga-timer-btn-resume, #ga-timer-btn-stop').hide();
            $('#ga-timer-status').empty();
            $('#ga-timer-task-info').empty();
            $('#ga-timer-display').text('00:00:00');
            stopTimerInterval();
        }
    }

    function loadTimerStatus() {
        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_get_timer_status',
            nonce: gaAdmin.nonce
        }, function(response) {
            if (response.success && response.data.active) {
                timerData = response.data;
                timerData.total_pausas_ms = 0; // Se calculará del servidor
                $('#ga-timer-task-info').html(
                    '<strong>' + response.data.tarea_numero + '</strong>: ' + response.data.tarea_nombre
                );
            } else {
                timerData = { active: false };
            }
            updateTimerUI();
        });
    }

    // Cargar estado inicial
    loadTimerStatus();

    // Iniciar timer
    $('.ga-btn-start-timer').on('click', function(e) {
        e.preventDefault();

        if (timerData.active) {
            alert('<?php echo esc_js(__('Ya tienes un timer activo. Deténlo primero.', 'gestionadmin-wolk')); ?>');
            return;
        }

        var tareaId = $(this).data('id');
        var tareaNombre = $(this).data('nombre');

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_timer_start',
            nonce: gaAdmin.nonce,
            tarea_id: tareaId
        }, function(response) {
            if (response.success) {
                timerData = {
                    active: true,
                    registro_id: response.data.registro_id,
                    hora_inicio: response.data.hora_inicio,
                    is_paused: false,
                    total_pausas_ms: 0
                };
                $('#ga-timer-task-info').html('<strong>Tarea</strong>: ' + tareaNombre);
                updateTimerUI();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Pausar
    $('#ga-timer-btn-pause').on('click', function() {
        $('#ga-modal-pausa').show();
    });

    $('#ga-btn-cancel-pausa').on('click', function() {
        $('#ga-modal-pausa').hide();
    });

    $('#ga-btn-confirm-pausa').on('click', function() {
        var motivo = $('#pausa-motivo').val();
        var nota = $('#pausa-nota').val();

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_timer_pause',
            nonce: gaAdmin.nonce,
            registro_id: timerData.registro_id,
            motivo: motivo,
            nota: nota
        }, function(response) {
            if (response.success) {
                timerData.is_paused = true;
                timerData.pausa_inicio = new Date().toISOString();
                updateTimerUI();
                $('#ga-modal-pausa').hide();
                $('#pausa-nota').val('');
            } else {
                alert(response.data.message);
            }
        });
    });

    // Reanudar
    $('#ga-timer-btn-resume').on('click', function() {
        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_timer_resume',
            nonce: gaAdmin.nonce,
            registro_id: timerData.registro_id
        }, function(response) {
            if (response.success) {
                // Calcular tiempo de pausa
                if (timerData.pausa_inicio) {
                    var pausaMs = new Date().getTime() - new Date(timerData.pausa_inicio).getTime();
                    timerData.total_pausas_ms += pausaMs;
                }
                timerData.is_paused = false;
                timerData.pausa_inicio = null;
                updateTimerUI();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Detener
    $('#ga-timer-btn-stop').on('click', function() {
        $('#ga-modal-stop').show();
    });

    $('#ga-btn-cancel-stop').on('click', function() {
        $('#ga-modal-stop').hide();
    });

    $('#ga-btn-confirm-stop').on('click', function() {
        var descripcion = $('#stop-descripcion').val();

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_timer_stop',
            nonce: gaAdmin.nonce,
            registro_id: timerData.registro_id,
            descripcion: descripcion
        }, function(response) {
            if (response.success) {
                alert('<?php echo esc_js(__('Timer detenido. Tiempo efectivo:', 'gestionadmin-wolk')); ?> ' +
                      Math.round(response.data.minutos_efectivos) + ' <?php echo esc_js(__('minutos', 'gestionadmin-wolk')); ?>');
                timerData = { active: false };
                updateTimerUI();
                $('#ga-modal-stop').hide();
                $('#stop-descripcion').val('');
            } else {
                alert(response.data.message);
            }
        });
    });

    // =========================================================================
    // FORMULARIO TAREA
    // =========================================================================

    // Sprint 5-6: Array de casos para buscar info cuando se selecciona proyecto
    var casosData = <?php echo wp_json_encode(array_map(function($c) {
        return array(
            'id' => $c->id,
            'numero' => $c->numero,
            'titulo' => $c->titulo,
        );
    }, $casos)); ?>;

    // Evento: cuando cambia el proyecto, actualizar el caso asociado
    $('#tarea-proyecto').on('change', function() {
        var $selected = $(this).find(':selected');
        var casoId = $selected.data('caso') || '';

        // Actualizar campo hidden de caso
        $('#tarea-caso').val(casoId);

        // Actualizar info visible del caso
        if (casoId) {
            var caso = casosData.find(function(c) { return c.id == casoId; });
            if (caso) {
                $('#tarea-caso-info').html('<strong>' + caso.numero + '</strong> - ' + caso.titulo);
            } else {
                $('#tarea-caso-info').html('-');
            }
        } else {
            $('#tarea-caso-info').html('<em><?php echo esc_js(__('Selecciona un proyecto', 'gestionadmin-wolk')); ?></em>');
        }
    });

    var subtareaCount = <?php echo count($subtareas_edit); ?>;

    $('#ga-btn-add-subtarea').on('click', function() {
        subtareaCount++;
        $('#ga-subtareas-body').append(
            '<tr data-id="0">' +
            '<td>' + subtareaCount + '</td>' +
            '<td><input type="text" class="ga-form-input subtarea-nombre" placeholder="<?php echo esc_js(__('Nombre de la subtarea', 'gestionadmin-wolk')); ?>"></td>' +
            '<td><input type="number" class="ga-form-input subtarea-horas" step="0.5" min="0" value="1"></td>' +
            '<td><a href="#" class="ga-btn-remove-subtarea" style="color:#d63638;"><?php echo esc_js(__('Quitar', 'gestionadmin-wolk')); ?></a></td>' +
            '</tr>'
        );
    });

    $(document).on('click', '.ga-btn-remove-subtarea', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        // Renumerar
        $('#ga-subtareas-body tr').each(function(i) {
            $(this).find('td:first').text(i + 1);
        });
        subtareaCount = $('#ga-subtareas-body tr').length;
    });

    $('#ga-form-tarea').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        // Recopilar subtareas
        var subtareas = [];
        $('#ga-subtareas-body tr').each(function(i) {
            var $row = $(this);
            var nombre = $row.find('.subtarea-nombre').val();
            if (nombre) {
                subtareas.push({
                    id: $row.data('id'),
                    nombre: nombre,
                    horas_estimadas: $row.find('.subtarea-horas').val() || 0,
                    orden: i
                });
            }
        });

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_tarea',
            nonce: gaAdmin.nonce,
            id: $('#tarea-id').val(),
            nombre: $('#tarea-nombre').val(),
            descripcion: $('#tarea-descripcion').val(),
            asignado_a: $('#tarea-asignado').val(),
            supervisor_id: $('#tarea-supervisor').val(),
            horas_estimadas: $('#tarea-horas').val(),
            fecha_inicio: $('#tarea-inicio').val(),
            fecha_limite: $('#tarea-limite').val(),
            prioridad: $('#tarea-prioridad').val(),
            estado: $('#tarea-estado').val(),
            proyecto_id: $('#tarea-proyecto').val(),  // Sprint 5-6
            caso_id: $('#tarea-caso').val(),          // Sprint 5-6
            subtareas: subtareas
        }, function(response) {
            if (response.success) {
                window.location.href = '<?php echo esc_url(admin_url('admin.php?page=gestionadmin-tareas')); ?>';
            } else {
                alert(response.data.message);
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar Tarea', 'gestionadmin-wolk')); ?>');
            }
        });
    });

    // Eliminar tarea
    $('.ga-btn-delete-tarea').on('click', function(e) {
        e.preventDefault();
        if (!confirm(gaAdmin.i18n.confirmDelete)) return;

        var id = $(this).data('id');
        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_tarea',
            nonce: gaAdmin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });
});
</script>
