<?php
/**
 * Vista: Países Configuración
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$paises = GA_Paises::get_all();
?>
<div class="wrap ga-admin">
    <h1><?php esc_html_e('Configuración de Países', 'gestionadmin-wolk'); ?></h1>

    <p class="description">
        <?php esc_html_e('Configure los impuestos, retenciones y datos de facturación electrónica por país.', 'gestionadmin-wolk'); ?>
    </p>

    <div class="ga-row">
        <!-- Listado -->
        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Países Configurados', 'gestionadmin-wolk'); ?></h2>
                </div>

                <table class="ga-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('País', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Moneda', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Impuesto', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                            <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paises as $pais) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($pais->codigo_iso); ?></strong></td>
                                <td><?php echo esc_html($pais->nombre); ?></td>
                                <td><?php echo esc_html($pais->moneda_simbolo . ' ' . $pais->moneda_codigo); ?></td>
                                <td>
                                    <?php echo esc_html($pais->impuesto_nombre . ' ' . $pais->impuesto_porcentaje . '%'); ?>
                                    <?php if ($pais->requiere_electronica) : ?>
                                        <br><small><?php esc_html_e('Factura-e:', 'gestionadmin-wolk'); ?> <?php echo esc_html($pais->proveedor_electronica); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($pais->activo) : ?>
                                        <span class="ga-badge ga-badge-success"><?php esc_html_e('Activo', 'gestionadmin-wolk'); ?></span>
                                    <?php else : ?>
                                        <span class="ga-badge ga-badge-danger"><?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="#" class="ga-btn-edit-pais" data-id="<?php echo esc_attr($pais->id); ?>">
                                        <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Formulario -->
        <div class="ga-col ga-col-6">
            <div class="ga-card" id="ga-form-pais-card">
                <div class="ga-card-header">
                    <h2 id="ga-form-title-pais"><?php esc_html_e('Editar País', 'gestionadmin-wolk'); ?></h2>
                </div>

                <form id="ga-form-pais">
                    <input type="hidden" name="id" id="pais-id" value="">

                    <div class="ga-form-group">
                        <label class="ga-form-label"><?php esc_html_e('Código ISO', 'gestionadmin-wolk'); ?></label>
                        <p id="pais-codigo" style="font-weight: bold; font-size: 24px;"></p>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="pais-nombre">
                            <?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="pais-nombre" name="nombre" class="ga-form-input" required>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="pais-moneda-codigo">
                                    <?php esc_html_e('Código Moneda', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="pais-moneda-codigo" name="moneda_codigo" class="ga-form-input" maxlength="3">
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="pais-moneda-simbolo">
                                    <?php esc_html_e('Símbolo', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="pais-moneda-simbolo" name="moneda_simbolo" class="ga-form-input" maxlength="5">
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="pais-impuesto-nombre">
                                    <?php esc_html_e('Nombre Impuesto', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="pais-impuesto-nombre" name="impuesto_nombre" class="ga-form-input"
                                       placeholder="Ej: IVA, Sales Tax">
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="pais-impuesto-porcentaje">
                                    <?php esc_html_e('% Impuesto', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="pais-impuesto-porcentaje" name="impuesto_porcentaje"
                                       class="ga-form-input" step="0.01" min="0" max="100">
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="pais-retencion">
                            <?php esc_html_e('% Retención Default', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="number" id="pais-retencion" name="retencion_default"
                               class="ga-form-input" step="0.01" min="0" max="100">
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="pais-electronica" name="requiere_electronica" value="1">
                            <?php esc_html_e('Requiere Facturación Electrónica', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <div class="ga-form-group" id="pais-proveedor-group">
                        <label class="ga-form-label" for="pais-proveedor">
                            <?php esc_html_e('Proveedor Factura Electrónica', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="text" id="pais-proveedor" name="proveedor_electronica" class="ga-form-input"
                               placeholder="Ej: DIAN, SAT, SII">
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="pais-activo" name="activo" value="1" checked>
                            <?php esc_html_e('Activo', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <div class="ga-form-group">
                        <button type="submit" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                        </button>
                        <button type="button" class="ga-btn" id="ga-btn-cancelar-pais">
                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </form>

                <div id="pais-select-message" style="padding: 20px; text-align: center; color: #666;">
                    <p><?php esc_html_e('Selecciona un país de la lista para editar su configuración.', 'gestionadmin-wolk'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var paises = <?php echo wp_json_encode(array_map(function($p) {
        return array(
            'id' => $p->id,
            'codigo_iso' => $p->codigo_iso,
            'nombre' => $p->nombre,
            'moneda_codigo' => $p->moneda_codigo,
            'moneda_simbolo' => $p->moneda_simbolo,
            'impuesto_nombre' => $p->impuesto_nombre,
            'impuesto_porcentaje' => $p->impuesto_porcentaje,
            'retencion_default' => $p->retencion_default,
            'requiere_electronica' => $p->requiere_electronica,
            'proveedor_electronica' => $p->proveedor_electronica,
            'activo' => $p->activo,
        );
    }, $paises)); ?>;

    // Ocultar formulario inicialmente
    $('#ga-form-pais').hide();

    function loadPais(id) {
        var p = paises.find(function(p) { return p.id == id; });
        if (p) {
            $('#pais-select-message').hide();
            $('#ga-form-pais').show();

            $('#pais-id').val(p.id);
            $('#pais-codigo').text(p.codigo_iso);
            $('#pais-nombre').val(p.nombre);
            $('#pais-moneda-codigo').val(p.moneda_codigo);
            $('#pais-moneda-simbolo').val(p.moneda_simbolo);
            $('#pais-impuesto-nombre').val(p.impuesto_nombre);
            $('#pais-impuesto-porcentaje').val(p.impuesto_porcentaje);
            $('#pais-retencion').val(p.retencion_default);
            $('#pais-electronica').prop('checked', p.requiere_electronica == 1);
            $('#pais-proveedor').val(p.proveedor_electronica);
            $('#pais-activo').prop('checked', p.activo == 1);

            toggleProveedorField();
        }
    }

    function toggleProveedorField() {
        if ($('#pais-electronica').is(':checked')) {
            $('#pais-proveedor-group').show();
        } else {
            $('#pais-proveedor-group').hide();
        }
    }

    $('#pais-electronica').on('change', toggleProveedorField);

    $('#ga-btn-cancelar-pais').on('click', function(e) {
        e.preventDefault();
        $('#ga-form-pais').hide();
        $('#pais-select-message').show();
    });

    $('.ga-btn-edit-pais').on('click', function(e) {
        e.preventDefault();
        loadPais($(this).data('id'));
    });

    $('#ga-form-pais').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_pais',
            nonce: gaAdmin.nonce,
            id: $('#pais-id').val(),
            nombre: $('#pais-nombre').val(),
            moneda_codigo: $('#pais-moneda-codigo').val(),
            moneda_simbolo: $('#pais-moneda-simbolo').val(),
            impuesto_nombre: $('#pais-impuesto-nombre').val(),
            impuesto_porcentaje: $('#pais-impuesto-porcentaje').val(),
            retencion_default: $('#pais-retencion').val(),
            requiere_electronica: $('#pais-electronica').is(':checked') ? 1 : 0,
            proveedor_electronica: $('#pais-proveedor').val(),
            activo: $('#pais-activo').is(':checked') ? 1 : 0
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
