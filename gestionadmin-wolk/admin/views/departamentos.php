<?php
/**
 * Vista: Departamentos CRUD
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$departamentos = GA_Departamentos::get_all();
$tipos = GA_Departamentos::get_tipos();
$usuarios = GA_Usuarios::get_for_dropdown();

// Obtener departamento para edición
$edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
$edit_data = $edit_id > 0 ? GA_Departamentos::get($edit_id) : null;
?>
<div class="wrap ga-admin">
    <h1>
        <?php esc_html_e('Departamentos', 'gestionadmin-wolk'); ?>
        <a href="#" class="page-title-action" id="ga-btn-nuevo-departamento">
            <?php esc_html_e('Añadir Nuevo', 'gestionadmin-wolk'); ?>
        </a>
    </h1>

    <div class="ga-row">
        <!-- Listado -->
        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Listado de Departamentos', 'gestionadmin-wolk'); ?></h2>
                </div>

                <?php if (empty($departamentos)) : ?>
                    <p><?php esc_html_e('No hay departamentos registrados.', 'gestionadmin-wolk'); ?></p>
                <?php else : ?>
                    <table class="ga-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departamentos as $dep) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($dep->codigo); ?></code></td>
                                    <td><?php echo esc_html($dep->nombre); ?></td>
                                    <td><?php echo esc_html(isset($tipos[$dep->tipo]) ? $tipos[$dep->tipo] : $dep->tipo); ?></td>
                                    <td>
                                        <?php if ($dep->activo) : ?>
                                            <span class="ga-badge ga-badge-success"><?php esc_html_e('Activo', 'gestionadmin-wolk'); ?></span>
                                        <?php else : ?>
                                            <span class="ga-badge ga-badge-danger"><?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="#" class="ga-btn-edit-departamento" data-id="<?php echo esc_attr($dep->id); ?>">
                                            <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                        </a> |
                                        <a href="#" class="ga-btn-delete-departamento" data-id="<?php echo esc_attr($dep->id); ?>" style="color: #d63638;">
                                            <?php esc_html_e('Eliminar', 'gestionadmin-wolk'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario -->
        <div class="ga-col ga-col-6">
            <div class="ga-card" id="ga-form-departamento-card">
                <div class="ga-card-header">
                    <h2 id="ga-form-title"><?php esc_html_e('Nuevo Departamento', 'gestionadmin-wolk'); ?></h2>
                </div>

                <form id="ga-form-departamento">
                    <input type="hidden" name="id" id="dep-id" value="">

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="dep-codigo">
                            <?php esc_html_e('Código', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="dep-codigo" name="codigo" class="ga-form-input" required
                               placeholder="Ej: DEV, ADMIN, SOPORTE" maxlength="20">
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="dep-nombre">
                            <?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="dep-nombre" name="nombre" class="ga-form-input" required
                               placeholder="Ej: Desarrollo, Administración">
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="dep-descripcion">
                            <?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="dep-descripcion" name="descripcion" class="ga-form-textarea" rows="3"></textarea>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="dep-tipo">
                            <?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?> *
                        </label>
                        <select id="dep-tipo" name="tipo" class="ga-form-select" required>
                            <?php foreach ($tipos as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="dep-jefe">
                            <?php esc_html_e('Jefe del Departamento', 'gestionadmin-wolk'); ?>
                        </label>
                        <select id="dep-jefe" name="jefe_id" class="ga-form-select">
                            <option value="0"><?php esc_html_e('-- Sin asignar --', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($usuarios as $usuario) : ?>
                                <option value="<?php echo esc_attr($usuario->usuario_wp_id); ?>">
                                    <?php echo esc_html($usuario->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="dep-activo" name="activo" value="1" checked>
                            <?php esc_html_e('Activo', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <div class="ga-form-group">
                        <button type="submit" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                        </button>
                        <button type="button" class="ga-btn" id="ga-btn-cancelar-departamento">
                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Datos de departamentos para edición
    var departamentos = <?php echo wp_json_encode(array_map(function($d) {
        return array(
            'id' => $d->id,
            'codigo' => $d->codigo,
            'nombre' => $d->nombre,
            'descripcion' => $d->descripcion,
            'tipo' => $d->tipo,
            'jefe_id' => $d->jefe_id,
            'activo' => $d->activo,
        );
    }, $departamentos)); ?>;

    function resetForm() {
        $('#ga-form-departamento')[0].reset();
        $('#dep-id').val('');
        $('#ga-form-title').text('<?php echo esc_js(__('Nuevo Departamento', 'gestionadmin-wolk')); ?>');
    }

    function loadDepartamento(id) {
        var dep = departamentos.find(function(d) { return d.id == id; });
        if (dep) {
            $('#dep-id').val(dep.id);
            $('#dep-codigo').val(dep.codigo);
            $('#dep-nombre').val(dep.nombre);
            $('#dep-descripcion').val(dep.descripcion);
            $('#dep-tipo').val(dep.tipo);
            $('#dep-jefe').val(dep.jefe_id || 0);
            $('#dep-activo').prop('checked', dep.activo == 1);
            $('#ga-form-title').text('<?php echo esc_js(__('Editar Departamento', 'gestionadmin-wolk')); ?>');
        }
    }

    $('#ga-btn-nuevo-departamento').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    $('#ga-btn-cancelar-departamento').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    $('.ga-btn-edit-departamento').on('click', function(e) {
        e.preventDefault();
        loadDepartamento($(this).data('id'));
    });

    $('.ga-btn-delete-departamento').on('click', function(e) {
        e.preventDefault();
        if (!confirm(gaAdmin.i18n.confirmDelete)) return;

        var id = $(this).data('id');
        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_departamento',
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

    $('#ga-form-departamento').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');

        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_departamento',
            nonce: gaAdmin.nonce,
            id: $('#dep-id').val(),
            codigo: $('#dep-codigo').val(),
            nombre: $('#dep-nombre').val(),
            descripcion: $('#dep-descripcion').val(),
            tipo: $('#dep-tipo').val(),
            jefe_id: $('#dep-jefe').val(),
            activo: $('#dep-activo').is(':checked') ? 1 : 0
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar', 'gestionadmin-wolk')); ?>');
            }
        });
    });
});
</script>
