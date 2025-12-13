<?php
/**
 * Vista: Puestos CRUD
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$puestos = GA_Puestos::get_all();
$departamentos = GA_Departamentos::get_all(true);
$niveles = GA_Puestos::get_niveles();
$flujos = GA_Puestos::get_flujos();
?>
<div class="wrap ga-admin">
    <h1>
        <?php esc_html_e('Puestos', 'gestionadmin-wolk'); ?>
        <a href="#" class="page-title-action" id="ga-btn-nuevo-puesto">
            <?php esc_html_e('Añadir Nuevo', 'gestionadmin-wolk'); ?>
        </a>
    </h1>

    <div class="ga-row">
        <!-- Listado -->
        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Listado de Puestos', 'gestionadmin-wolk'); ?></h2>
                </div>

                <?php if (empty($puestos)) : ?>
                    <p><?php esc_html_e('No hay puestos registrados.', 'gestionadmin-wolk'); ?></p>
                <?php else : ?>
                    <table class="ga-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Departamento', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Nivel', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($puestos as $puesto) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($puesto->codigo); ?></code></td>
                                    <td><?php echo esc_html($puesto->nombre); ?></td>
                                    <td><?php echo esc_html($puesto->departamento_nombre); ?></td>
                                    <td><?php echo esc_html($niveles[$puesto->nivel_jerarquico] ?? $puesto->nivel_jerarquico); ?></td>
                                    <td>
                                        <a href="#" class="ga-btn-edit-puesto" data-id="<?php echo esc_attr($puesto->id); ?>">
                                            <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                        </a> |
                                        <a href="#" class="ga-btn-escalas-puesto" data-id="<?php echo esc_attr($puesto->id); ?>" data-nombre="<?php echo esc_attr($puesto->nombre); ?>">
                                            <?php esc_html_e('Escalas', 'gestionadmin-wolk'); ?>
                                        </a> |
                                        <a href="#" class="ga-btn-delete-puesto" data-id="<?php echo esc_attr($puesto->id); ?>" style="color: #d63638;">
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

        <!-- Formulario Puesto -->
        <div class="ga-col ga-col-6">
            <div class="ga-card" id="ga-form-puesto-card">
                <div class="ga-card-header">
                    <h2 id="ga-form-title-puesto"><?php esc_html_e('Nuevo Puesto', 'gestionadmin-wolk'); ?></h2>
                </div>

                <form id="ga-form-puesto">
                    <input type="hidden" name="id" id="puesto-id" value="">

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="puesto-departamento">
                            <?php esc_html_e('Departamento', 'gestionadmin-wolk'); ?> *
                        </label>
                        <select id="puesto-departamento" name="departamento_id" class="ga-form-select" required>
                            <option value=""><?php esc_html_e('-- Seleccionar --', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($departamentos as $dep) : ?>
                                <option value="<?php echo esc_attr($dep->id); ?>"><?php echo esc_html($dep->nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="puesto-codigo">
                            <?php esc_html_e('Código', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="puesto-codigo" name="codigo" class="ga-form-input" required
                               placeholder="Ej: DEV-BACK, QA-SR" maxlength="20">
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="puesto-nombre">
                            <?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="puesto-nombre" name="nombre" class="ga-form-input" required>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="puesto-descripcion">
                            <?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="puesto-descripcion" name="descripcion" class="ga-form-textarea" rows="2"></textarea>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="puesto-nivel">
                                    <?php esc_html_e('Nivel Jerárquico', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="puesto-nivel" name="nivel_jerarquico" class="ga-form-select">
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
                                <label class="ga-form-label" for="puesto-horas">
                                    <?php esc_html_e('Horas/Semana', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="puesto-horas" name="capacidad_horas_semana"
                                       class="ga-form-input" value="40" min="1" max="168">
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="puesto-flujo">
                            <?php esc_html_e('Flujo de Revisión', 'gestionadmin-wolk'); ?>
                        </label>
                        <select id="puesto-flujo" name="flujo_revision_default" class="ga-form-select">
                            <?php foreach ($flujos as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="puesto-qa" name="requiere_qa" value="1">
                            <?php esc_html_e('Requiere QA', 'gestionadmin-wolk'); ?>
                        </label>
                        <label class="ga-form-label" style="margin-left: 20px;">
                            <input type="checkbox" id="puesto-activo" name="activo" value="1" checked>
                            <?php esc_html_e('Activo', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <div class="ga-form-group">
                        <button type="submit" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                        </button>
                        <button type="button" class="ga-btn" id="ga-btn-cancelar-puesto">
                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Formulario Escalas -->
            <div class="ga-card" id="ga-form-escalas-card" style="display: none; margin-top: 20px;">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Escalas de Tarifa', 'gestionadmin-wolk'); ?> - <span id="escala-puesto-nombre"></span></h2>
                </div>

                <input type="hidden" id="escala-puesto-id" value="">

                <table class="ga-table" id="ga-escalas-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Año', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Tarifa/Hora (USD)', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Incremento %', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <form id="ga-form-escala" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #c3c4c7;">
                    <h4 style="margin-top: 0;"><?php esc_html_e('Agregar/Editar Escala', 'gestionadmin-wolk'); ?></h4>
                    <input type="hidden" id="escala-id" value="">

                    <div class="ga-row">
                        <div class="ga-col ga-col-3">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="escala-anio"><?php esc_html_e('Año', 'gestionadmin-wolk'); ?></label>
                                <select id="escala-anio" class="ga-form-select" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5+</option>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-3">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="escala-tarifa"><?php esc_html_e('Tarifa', 'gestionadmin-wolk'); ?></label>
                                <input type="number" id="escala-tarifa" class="ga-form-input" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="ga-col ga-col-3">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="escala-incremento"><?php esc_html_e('% Inc.', 'gestionadmin-wolk'); ?></label>
                                <input type="number" id="escala-incremento" class="ga-form-input" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="ga-col ga-col-3">
                            <div class="ga-form-group">
                                <label class="ga-form-label">&nbsp;</label>
                                <button type="submit" class="ga-btn ga-btn-primary"><?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label><input type="checkbox" id="escala-aprobacion-jefe" checked> <?php esc_html_e('Requiere aprobación Jefe', 'gestionadmin-wolk'); ?></label>
                        <label style="margin-left: 15px;"><input type="checkbox" id="escala-aprobacion-director"> <?php esc_html_e('Requiere aprobación Director', 'gestionadmin-wolk'); ?></label>
                    </div>
                </form>

                <button type="button" class="ga-btn" id="ga-btn-cerrar-escalas" style="margin-top: 10px;">
                    <?php esc_html_e('Cerrar', 'gestionadmin-wolk'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var puestos = <?php echo wp_json_encode(array_map(function($p) {
        return array(
            'id' => $p->id,
            'departamento_id' => $p->departamento_id,
            'codigo' => $p->codigo,
            'nombre' => $p->nombre,
            'descripcion' => $p->descripcion,
            'nivel_jerarquico' => $p->nivel_jerarquico,
            'capacidad_horas_semana' => $p->capacidad_horas_semana,
            'requiere_qa' => $p->requiere_qa,
            'flujo_revision_default' => $p->flujo_revision_default,
            'activo' => $p->activo,
        );
    }, $puestos)); ?>;

    function resetFormPuesto() {
        $('#ga-form-puesto')[0].reset();
        $('#puesto-id').val('');
        $('#puesto-activo').prop('checked', true);
        $('#ga-form-title-puesto').text('<?php echo esc_js(__('Nuevo Puesto', 'gestionadmin-wolk')); ?>');
    }

    function loadPuesto(id) {
        var p = puestos.find(function(p) { return p.id == id; });
        if (p) {
            $('#puesto-id').val(p.id);
            $('#puesto-departamento').val(p.departamento_id);
            $('#puesto-codigo').val(p.codigo);
            $('#puesto-nombre').val(p.nombre);
            $('#puesto-descripcion').val(p.descripcion);
            $('#puesto-nivel').val(p.nivel_jerarquico);
            $('#puesto-horas').val(p.capacidad_horas_semana);
            $('#puesto-qa').prop('checked', p.requiere_qa == 1);
            $('#puesto-flujo').val(p.flujo_revision_default);
            $('#puesto-activo').prop('checked', p.activo == 1);
            $('#ga-form-title-puesto').text('<?php echo esc_js(__('Editar Puesto', 'gestionadmin-wolk')); ?>');
        }
    }

    $('#ga-btn-nuevo-puesto').on('click', function(e) {
        e.preventDefault();
        resetFormPuesto();
    });

    $('#ga-btn-cancelar-puesto').on('click', function(e) {
        e.preventDefault();
        resetFormPuesto();
    });

    $('.ga-btn-edit-puesto').on('click', function(e) {
        e.preventDefault();
        loadPuesto($(this).data('id'));
    });

    $('.ga-btn-delete-puesto').on('click', function(e) {
        e.preventDefault();
        if (!confirm(gaAdmin.i18n.confirmDelete)) return;

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_puesto',
            nonce: gaAdmin.nonce,
            id: $(this).data('id')
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });

    $('#ga-form-puesto').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_puesto',
            nonce: gaAdmin.nonce,
            id: $('#puesto-id').val(),
            departamento_id: $('#puesto-departamento').val(),
            codigo: $('#puesto-codigo').val(),
            nombre: $('#puesto-nombre').val(),
            descripcion: $('#puesto-descripcion').val(),
            nivel_jerarquico: $('#puesto-nivel').val(),
            capacidad_horas_semana: $('#puesto-horas').val(),
            requiere_qa: $('#puesto-qa').is(':checked') ? 1 : 0,
            flujo_revision_default: $('#puesto-flujo').val(),
            activo: $('#puesto-activo').is(':checked') ? 1 : 0
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar', 'gestionadmin-wolk')); ?>');
            }
        });
    });

    // Escalas
    $('.ga-btn-escalas-puesto').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');

        $('#escala-puesto-id').val(id);
        $('#escala-puesto-nombre').text(nombre);
        $('#ga-form-escalas-card').show();

        loadEscalas(id);
    });

    $('#ga-btn-cerrar-escalas').on('click', function() {
        $('#ga-form-escalas-card').hide();
    });

    function loadEscalas(puestoId) {
        // Cargar escalas vía AJAX (simplificado - carga en PHP)
        $.get(gaAdmin.ajaxUrl, {
            action: 'ga_get_escalas',
            nonce: gaAdmin.nonce,
            puesto_id: puestoId
        }, function(response) {
            var $tbody = $('#ga-escalas-table tbody');
            $tbody.empty();

            if (response.success && response.data.length > 0) {
                response.data.forEach(function(e) {
                    $tbody.append(
                        '<tr>' +
                        '<td>Año ' + e.anio_antiguedad + '</td>' +
                        '<td>$' + parseFloat(e.tarifa_hora).toFixed(2) + '</td>' +
                        '<td>' + e.incremento_porcentaje + '%</td>' +
                        '<td><a href="#" class="ga-btn-delete-escala" data-id="' + e.id + '" style="color:#d63638;">Eliminar</a></td>' +
                        '</tr>'
                    );
                });
            } else {
                $tbody.append('<tr><td colspan="4"><?php echo esc_js(__('No hay escalas', 'gestionadmin-wolk')); ?></td></tr>');
            }
        });
    }

    $('#ga-form-escala').on('submit', function(e) {
        e.preventDefault();

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_escala',
            nonce: gaAdmin.nonce,
            id: $('#escala-id').val(),
            puesto_id: $('#escala-puesto-id').val(),
            anio_antiguedad: $('#escala-anio').val(),
            tarifa_hora: $('#escala-tarifa').val(),
            incremento_porcentaje: $('#escala-incremento').val(),
            requiere_aprobacion_jefe: $('#escala-aprobacion-jefe').is(':checked') ? 1 : 0,
            requiere_aprobacion_director: $('#escala-aprobacion-director').is(':checked') ? 1 : 0
        }, function(response) {
            if (response.success) {
                $('#escala-id').val('');
                $('#escala-tarifa').val('');
                $('#escala-incremento').val('0');
                loadEscalas($('#escala-puesto-id').val());
            } else {
                alert(response.data.message);
            }
        });
    });

    $(document).on('click', '.ga-btn-delete-escala', function(e) {
        e.preventDefault();
        if (!confirm(gaAdmin.i18n.confirmDelete)) return;

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_escala',
            nonce: gaAdmin.nonce,
            id: $(this).data('id')
        }, function(response) {
            if (response.success) {
                loadEscalas($('#escala-puesto-id').val());
            } else {
                alert(response.data.message);
            }
        });
    });
});
</script>
