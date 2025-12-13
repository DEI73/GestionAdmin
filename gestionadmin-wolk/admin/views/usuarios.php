<?php
/**
 * Vista: Usuarios GestionAdmin
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$usuarios_ga = GA_Usuarios::get_all();
$usuarios_wp_disponibles = GA_Usuarios::get_wp_users_not_in_ga();
$departamentos = GA_Departamentos::get_all(true);
$puestos = GA_Puestos::get_all(true);
$paises = GA_Paises::get_for_dropdown();
$metodos_pago = GA_Usuarios::get_metodos_pago();
$niveles = GA_Puestos::get_niveles();
?>
<div class="wrap ga-admin">
    <h1>
        <?php esc_html_e('Usuarios GestionAdmin', 'gestionadmin-wolk'); ?>
        <a href="#" class="page-title-action" id="ga-btn-nuevo-usuario">
            <?php esc_html_e('Asignar Usuario WP', 'gestionadmin-wolk'); ?>
        </a>
    </h1>

    <p class="description">
        <?php esc_html_e('Aquí puedes asignar usuarios de WordPress al sistema GestionAdmin con sus puestos y departamentos.', 'gestionadmin-wolk'); ?>
    </p>

    <div class="ga-row">
        <!-- Listado -->
        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Usuarios Registrados', 'gestionadmin-wolk'); ?></h2>
                </div>

                <?php if (empty($usuarios_ga)) : ?>
                    <p><?php esc_html_e('No hay usuarios registrados en GestionAdmin.', 'gestionadmin-wolk'); ?></p>
                <?php else : ?>
                    <table class="ga-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Usuario', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Puesto', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios_ga as $usuario) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($usuario->wp_nombre); ?></strong><br>
                                        <small><?php echo esc_html($usuario->wp_email); ?></small>
                                    </td>
                                    <td><code><?php echo esc_html($usuario->codigo_empleado ?: '-'); ?></code></td>
                                    <td>
                                        <?php echo esc_html($usuario->puesto_nombre ?: '-'); ?><br>
                                        <small><?php echo esc_html($usuario->departamento_nombre ?: ''); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($usuario->activo) : ?>
                                            <span class="ga-badge ga-badge-success"><?php esc_html_e('Activo', 'gestionadmin-wolk'); ?></span>
                                        <?php else : ?>
                                            <span class="ga-badge ga-badge-danger"><?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="#" class="ga-btn-edit-usuario" data-id="<?php echo esc_attr($usuario->id); ?>">
                                            <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
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
            <div class="ga-card" id="ga-form-usuario-card">
                <div class="ga-card-header">
                    <h2 id="ga-form-title-usuario"><?php esc_html_e('Asignar Usuario WordPress', 'gestionadmin-wolk'); ?></h2>
                </div>

                <form id="ga-form-usuario">
                    <input type="hidden" name="id" id="usuario-id" value="">

                    <div class="ga-form-group" id="usuario-wp-select-group">
                        <label class="ga-form-label" for="usuario-wp">
                            <?php esc_html_e('Usuario WordPress', 'gestionadmin-wolk'); ?> *
                        </label>
                        <select id="usuario-wp" name="usuario_wp_id" class="ga-form-select" required>
                            <option value=""><?php esc_html_e('-- Seleccionar usuario --', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($usuarios_wp_disponibles as $wp_user) : ?>
                                <option value="<?php echo esc_attr($wp_user->ID); ?>">
                                    <?php echo esc_html($wp_user->display_name . ' (' . $wp_user->user_email . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Solo se muestran usuarios WP no registrados en GestionAdmin', 'gestionadmin-wolk'); ?>
                        </p>
                    </div>

                    <div class="ga-form-group" id="usuario-wp-display" style="display: none;">
                        <label class="ga-form-label"><?php esc_html_e('Usuario WordPress', 'gestionadmin-wolk'); ?></label>
                        <p id="usuario-wp-nombre" style="font-weight: bold;"></p>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="usuario-codigo">
                                    <?php esc_html_e('Código Empleado', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="usuario-codigo" name="codigo_empleado" class="ga-form-input"
                                       placeholder="Ej: EMP-001" maxlength="20">
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="usuario-ingreso">
                                    <?php esc_html_e('Fecha Ingreso', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="date" id="usuario-ingreso" name="fecha_ingreso" class="ga-form-input">
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="usuario-departamento">
                                    <?php esc_html_e('Departamento', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="usuario-departamento" name="departamento_id" class="ga-form-select">
                                    <option value="0"><?php esc_html_e('-- Sin asignar --', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($departamentos as $dep) : ?>
                                        <option value="<?php echo esc_attr($dep->id); ?>">
                                            <?php echo esc_html($dep->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="usuario-puesto">
                                    <?php esc_html_e('Puesto', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="usuario-puesto" name="puesto_id" class="ga-form-select">
                                    <option value="0"><?php esc_html_e('-- Sin asignar --', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($puestos as $puesto) : ?>
                                        <option value="<?php echo esc_attr($puesto->id); ?>" data-dep="<?php echo esc_attr($puesto->departamento_id); ?>">
                                            <?php echo esc_html($puesto->nombre . ' (' . $puesto->departamento_nombre . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="usuario-nivel">
                                    <?php esc_html_e('Nivel Jerárquico', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="usuario-nivel" name="nivel_jerarquico" class="ga-form-select">
                                    <?php foreach ($niveles as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($key, 4); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="usuario-pais">
                                    <?php esc_html_e('País Residencia', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="usuario-pais" name="pais_residencia" class="ga-form-select">
                                    <option value=""><?php esc_html_e('-- Seleccionar --', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($paises as $pais) : ?>
                                        <option value="<?php echo esc_attr($pais->codigo_iso); ?>">
                                            <?php echo esc_html($pais->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="usuario-metodo-pago">
                            <?php esc_html_e('Método de Pago Preferido', 'gestionadmin-wolk'); ?>
                        </label>
                        <select id="usuario-metodo-pago" name="metodo_pago_preferido" class="ga-form-select">
                            <?php foreach ($metodos_pago as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="usuario-activo" name="activo" value="1" checked>
                            <?php esc_html_e('Activo', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <div class="ga-form-group">
                        <button type="submit" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                        </button>
                        <button type="button" class="ga-btn" id="ga-btn-cancelar-usuario">
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
    var usuarios = <?php echo wp_json_encode(array_map(function($u) {
        return array(
            'id' => $u->id,
            'usuario_wp_id' => $u->usuario_wp_id,
            'wp_nombre' => $u->wp_nombre,
            'wp_email' => $u->wp_email,
            'puesto_id' => $u->puesto_id,
            'departamento_id' => $u->departamento_id,
            'codigo_empleado' => $u->codigo_empleado,
            'fecha_ingreso' => $u->fecha_ingreso,
            'nivel_jerarquico' => $u->nivel_jerarquico,
            'metodo_pago_preferido' => $u->metodo_pago_preferido,
            'pais_residencia' => $u->pais_residencia,
            'activo' => $u->activo,
        );
    }, $usuarios_ga)); ?>;

    function resetForm() {
        $('#ga-form-usuario')[0].reset();
        $('#usuario-id').val('');
        $('#usuario-wp-select-group').show();
        $('#usuario-wp-display').hide();
        $('#usuario-wp').prop('required', true);
        $('#usuario-activo').prop('checked', true);
        $('#ga-form-title-usuario').text('<?php echo esc_js(__('Asignar Usuario WordPress', 'gestionadmin-wolk')); ?>');
    }

    function loadUsuario(id) {
        var u = usuarios.find(function(u) { return u.id == id; });
        if (u) {
            $('#usuario-id').val(u.id);
            $('#usuario-wp').val(u.usuario_wp_id).prop('required', false);
            $('#usuario-wp-select-group').hide();
            $('#usuario-wp-display').show();
            $('#usuario-wp-nombre').text(u.wp_nombre + ' (' + u.wp_email + ')');

            $('#usuario-codigo').val(u.codigo_empleado);
            $('#usuario-ingreso').val(u.fecha_ingreso);
            $('#usuario-departamento').val(u.departamento_id || 0);
            $('#usuario-puesto').val(u.puesto_id || 0);
            $('#usuario-nivel').val(u.nivel_jerarquico);
            $('#usuario-pais').val(u.pais_residencia);
            $('#usuario-metodo-pago').val(u.metodo_pago_preferido);
            $('#usuario-activo').prop('checked', u.activo == 1);
            $('#ga-form-title-usuario').text('<?php echo esc_js(__('Editar Usuario', 'gestionadmin-wolk')); ?>');
        }
    }

    $('#ga-btn-nuevo-usuario').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    $('#ga-btn-cancelar-usuario').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    $('.ga-btn-edit-usuario').on('click', function(e) {
        e.preventDefault();
        loadUsuario($(this).data('id'));
    });

    // Filtrar puestos por departamento
    $('#usuario-departamento').on('change', function() {
        var depId = $(this).val();
        $('#usuario-puesto option').each(function() {
            var $opt = $(this);
            if ($opt.val() === '0' || $opt.data('dep') == depId || depId == '0') {
                $opt.show();
            } else {
                $opt.hide();
            }
        });
    });

    $('#ga-form-usuario').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        var wpId = $('#usuario-id').val() ?
            usuarios.find(function(u) { return u.id == $('#usuario-id').val(); }).usuario_wp_id :
            $('#usuario-wp').val();

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_usuario',
            nonce: gaAdmin.nonce,
            id: $('#usuario-id').val(),
            usuario_wp_id: wpId,
            puesto_id: $('#usuario-puesto').val(),
            departamento_id: $('#usuario-departamento').val(),
            codigo_empleado: $('#usuario-codigo').val(),
            fecha_ingreso: $('#usuario-ingreso').val(),
            nivel_jerarquico: $('#usuario-nivel').val(),
            metodo_pago_preferido: $('#usuario-metodo-pago').val(),
            pais_residencia: $('#usuario-pais').val(),
            activo: $('#usuario-activo').is(':checked') ? 1 : 0
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
