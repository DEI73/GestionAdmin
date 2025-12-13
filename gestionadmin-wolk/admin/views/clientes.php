<?php
/**
 * Vista: Clientes GestionAdmin
 *
 * Página de administración para gestionar clientes.
 * Soporta personas naturales y empresas con datos fiscales.
 * Diseño responsive con grid de 2 columnas.
 *
 * @package GestionAdmin_Wolk
 * @since 1.2.0
 */

// Seguridad: Verificar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener datos para la vista
$clientes = GA_Clientes::get_all();           // Todos los clientes (activos e inactivos)
$paises = GA_Paises::get_for_dropdown();       // Países para selector
$tipos = GA_Clientes::get_tipos();             // Tipos: Persona Natural, Empresa
$metodos_pago = GA_Clientes::get_metodos_pago(); // Métodos de pago disponibles
?>
<div class="wrap ga-admin">
    <!-- Encabezado de página -->
    <h1>
        <?php esc_html_e('Clientes', 'gestionadmin-wolk'); ?>
        <a href="#" class="page-title-action" id="ga-btn-nuevo-cliente">
            <?php esc_html_e('Nuevo Cliente', 'gestionadmin-wolk'); ?>
        </a>
    </h1>

    <p class="description">
        <?php esc_html_e('Gestiona los clientes de tu empresa. Cada cliente puede tener múltiples casos y proyectos.', 'gestionadmin-wolk'); ?>
    </p>

    <div class="ga-row">
        <!-- ============================================================ -->
        <!-- COLUMNA IZQUIERDA: Listado de clientes -->
        <!-- ============================================================ -->
        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Clientes Registrados', 'gestionadmin-wolk'); ?></h2>
                </div>

                <?php if (empty($clientes)) : ?>
                    <!-- Mensaje cuando no hay clientes -->
                    <p style="padding: 20px; text-align: center; color: #666;">
                        <?php esc_html_e('No hay clientes registrados. Haz clic en "Nuevo Cliente" para agregar uno.', 'gestionadmin-wolk'); ?>
                    </p>
                <?php else : ?>
                    <!-- Tabla de clientes -->
                    <table class="ga-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('País', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente) : ?>
                                <tr data-id="<?php echo esc_attr($cliente->id); ?>">
                                    <!-- Nombre y código -->
                                    <td>
                                        <strong><?php echo esc_html($cliente->nombre_comercial); ?></strong><br>
                                        <code><?php echo esc_html($cliente->codigo); ?></code>
                                        <?php if ($cliente->email) : ?>
                                            <br><small><?php echo esc_html($cliente->email); ?></small>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Tipo de cliente -->
                                    <td>
                                        <?php if ($cliente->tipo === 'EMPRESA') : ?>
                                            <span class="ga-badge ga-badge-info">
                                                <?php esc_html_e('Empresa', 'gestionadmin-wolk'); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="ga-badge">
                                                <?php esc_html_e('Persona', 'gestionadmin-wolk'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- País -->
                                    <td>
                                        <?php echo esc_html($cliente->pais_nombre ?: $cliente->pais ?: '-'); ?>
                                    </td>

                                    <!-- Estado activo/inactivo -->
                                    <td>
                                        <?php if ($cliente->activo) : ?>
                                            <span class="ga-badge ga-badge-success">
                                                <?php esc_html_e('Activo', 'gestionadmin-wolk'); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="ga-badge ga-badge-danger">
                                                <?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Botones de acción -->
                                    <td>
                                        <a href="#" class="ga-btn-edit-cliente" data-id="<?php echo esc_attr($cliente->id); ?>">
                                            <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                        </a>
                                        <?php if ($cliente->activo) : ?>
                                            <a href="#" class="ga-btn-delete-cliente" data-id="<?php echo esc_attr($cliente->id); ?>"
                                               style="color: var(--ga-danger); margin-left: 10px;">
                                                <?php esc_html_e('Eliminar', 'gestionadmin-wolk'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- COLUMNA DERECHA: Formulario de cliente -->
        <!-- ============================================================ -->
        <div class="ga-col ga-col-6">
            <div class="ga-card" id="ga-form-cliente-card">
                <div class="ga-card-header">
                    <h2 id="ga-form-title-cliente">
                        <?php esc_html_e('Nuevo Cliente', 'gestionadmin-wolk'); ?>
                    </h2>
                </div>

                <form id="ga-form-cliente">
                    <!-- Campo oculto para ID (0 = nuevo) -->
                    <input type="hidden" name="id" id="cliente-id" value="">

                    <!-- ====== SECCIÓN: Datos Básicos ====== -->
                    <div class="ga-row">
                        <!-- Tipo de cliente -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-tipo">
                                    <?php esc_html_e('Tipo de Cliente', 'gestionadmin-wolk'); ?> *
                                </label>
                                <select id="cliente-tipo" name="tipo" class="ga-form-select" required>
                                    <?php foreach ($tipos as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Código (auto-generado) -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-codigo">
                                    <?php esc_html_e('Código', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="cliente-codigo" name="codigo" class="ga-form-input"
                                       placeholder="<?php esc_attr_e('Auto-generado', 'gestionadmin-wolk'); ?>"
                                       readonly style="background: #f6f7f7;">
                            </div>
                        </div>
                    </div>

                    <!-- Nombre comercial -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="cliente-nombre">
                            <?php esc_html_e('Nombre Comercial', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="cliente-nombre" name="nombre_comercial" class="ga-form-input"
                               required maxlength="200"
                               placeholder="<?php esc_attr_e('Nombre comercial o nombre completo', 'gestionadmin-wolk'); ?>">
                    </div>

                    <!-- Razón social (solo empresas) -->
                    <div class="ga-form-group" id="grupo-razon-social">
                        <label class="ga-form-label" for="cliente-razon">
                            <?php esc_html_e('Razón Social', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="text" id="cliente-razon" name="razon_social" class="ga-form-input"
                               maxlength="200"
                               placeholder="<?php esc_attr_e('Razón social legal', 'gestionadmin-wolk'); ?>">
                    </div>

                    <!-- ====== SECCIÓN: Datos Fiscales ====== -->
                    <h4 style="margin: 20px 0 10px; border-bottom: 1px solid var(--ga-border); padding-bottom: 5px;">
                        <?php esc_html_e('Datos Fiscales', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-row">
                        <!-- Tipo de documento -->
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-doc-tipo">
                                    <?php esc_html_e('Tipo Doc.', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="cliente-doc-tipo" name="documento_tipo" class="ga-form-select">
                                    <option value=""><?php esc_html_e('Seleccionar', 'gestionadmin-wolk'); ?></option>
                                    <option value="NIT">NIT</option>
                                    <option value="CC">CC</option>
                                    <option value="RFC">RFC</option>
                                    <option value="EIN">EIN</option>
                                    <option value="CEDULA_JURIDICA">Cédula Jurídica</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                        </div>

                        <!-- Número de documento -->
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-doc-numero">
                                    <?php esc_html_e('Número', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="cliente-doc-numero" name="documento_numero"
                                       class="ga-form-input" maxlength="50">
                            </div>
                        </div>

                        <!-- País -->
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-pais">
                                    <?php esc_html_e('País', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="cliente-pais" name="pais" class="ga-form-select">
                                    <option value=""><?php esc_html_e('Seleccionar', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($paises as $pais) : ?>
                                        <option value="<?php echo esc_attr($pais->codigo_iso); ?>">
                                            <?php echo esc_html($pais->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <!-- Ciudad -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-ciudad">
                                    <?php esc_html_e('Ciudad', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="cliente-ciudad" name="ciudad" class="ga-form-input"
                                       maxlength="100">
                            </div>
                        </div>

                        <!-- Régimen fiscal -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-regimen">
                                    <?php esc_html_e('Régimen Fiscal', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="cliente-regimen" name="regimen_fiscal" class="ga-form-input"
                                       maxlength="50" placeholder="<?php esc_attr_e('Ej: Simplificado, General', 'gestionadmin-wolk'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="cliente-direccion">
                            <?php esc_html_e('Dirección', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="cliente-direccion" name="direccion" class="ga-form-textarea"
                                  rows="2"></textarea>
                    </div>

                    <!-- ====== SECCIÓN: Contacto ====== -->
                    <h4 style="margin: 20px 0 10px; border-bottom: 1px solid var(--ga-border); padding-bottom: 5px;">
                        <?php esc_html_e('Información de Contacto', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-row">
                        <!-- Email -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-email">
                                    <?php esc_html_e('Email Principal', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="email" id="cliente-email" name="email" class="ga-form-input"
                                       maxlength="200">
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-telefono">
                                    <?php esc_html_e('Teléfono', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="tel" id="cliente-telefono" name="telefono" class="ga-form-input"
                                       maxlength="50">
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <!-- Contacto nombre -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-contacto-nombre">
                                    <?php esc_html_e('Nombre Contacto', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="cliente-contacto-nombre" name="contacto_nombre"
                                       class="ga-form-input" maxlength="200">
                            </div>
                        </div>

                        <!-- Contacto cargo -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-contacto-cargo">
                                    <?php esc_html_e('Cargo', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="cliente-contacto-cargo" name="contacto_cargo"
                                       class="ga-form-input" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <!-- ====== SECCIÓN: Pagos ====== -->
                    <h4 style="margin: 20px 0 10px; border-bottom: 1px solid var(--ga-border); padding-bottom: 5px;">
                        <?php esc_html_e('Configuración de Pagos', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-row">
                        <!-- Método de pago -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-metodo-pago">
                                    <?php esc_html_e('Método Preferido', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="cliente-metodo-pago" name="metodo_pago_preferido" class="ga-form-select">
                                    <?php foreach ($metodos_pago as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Retención por defecto -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="cliente-retencion">
                                    <?php esc_html_e('% Retención Default', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="cliente-retencion" name="retencion_default"
                                       class="ga-form-input" step="0.01" min="0" max="100" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="cliente-notas">
                            <?php esc_html_e('Notas Internas', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="cliente-notas" name="notas" class="ga-form-textarea" rows="2"
                                  placeholder="<?php esc_attr_e('Notas visibles solo para el equipo interno', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>

                    <!-- Estado activo -->
                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="cliente-activo" name="activo" value="1" checked>
                            <?php esc_html_e('Cliente Activo', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <!-- Botones de acción -->
                    <div class="ga-form-group">
                        <button type="submit" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                        </button>
                        <button type="button" class="ga-btn" id="ga-btn-cancelar-cliente">
                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT: Lógica del formulario de clientes -->
<!-- ============================================================ -->
<script>
jQuery(document).ready(function($) {
    /**
     * Array de clientes cargado desde PHP para edición rápida.
     * Evita llamadas AJAX adicionales al editar.
     */
    var clientes = <?php echo wp_json_encode(array_map(function($c) {
        return array(
            'id'                   => $c->id,
            'codigo'               => $c->codigo,
            'tipo'                 => $c->tipo,
            'nombre_comercial'     => $c->nombre_comercial,
            'razon_social'         => $c->razon_social,
            'documento_tipo'       => $c->documento_tipo,
            'documento_numero'     => $c->documento_numero,
            'email'                => $c->email,
            'telefono'             => $c->telefono,
            'pais'                 => $c->pais,
            'ciudad'               => $c->ciudad,
            'direccion'            => $c->direccion,
            'regimen_fiscal'       => $c->regimen_fiscal,
            'retencion_default'    => $c->retencion_default,
            'contacto_nombre'      => $c->contacto_nombre,
            'contacto_cargo'       => $c->contacto_cargo,
            'contacto_email'       => $c->contacto_email,
            'contacto_telefono'    => $c->contacto_telefono,
            'metodo_pago_preferido' => $c->metodo_pago_preferido,
            'notas'                => $c->notas,
            'activo'               => $c->activo,
        );
    }, $clientes)); ?>;

    /**
     * Resetear formulario a estado inicial (nuevo cliente)
     */
    function resetForm() {
        // Limpiar todos los campos
        $('#ga-form-cliente')[0].reset();
        $('#cliente-id').val('');
        $('#cliente-codigo').val('').removeAttr('readonly').css('background', '');

        // Restaurar título
        $('#ga-form-title-cliente').text('<?php echo esc_js(__('Nuevo Cliente', 'gestionadmin-wolk')); ?>');

        // Mostrar razón social por defecto (tipo empresa)
        $('#grupo-razon-social').show();
        $('#cliente-activo').prop('checked', true);
    }

    /**
     * Cargar datos de un cliente para edición
     * @param {number} id - ID del cliente a cargar
     */
    function loadCliente(id) {
        // Buscar cliente en el array local
        var c = clientes.find(function(cli) { return cli.id == id; });

        if (c) {
            // Cambiar título del formulario
            $('#ga-form-title-cliente').text('<?php echo esc_js(__('Editar Cliente', 'gestionadmin-wolk')); ?>');

            // Rellenar campos del formulario
            $('#cliente-id').val(c.id);
            $('#cliente-codigo').val(c.codigo).attr('readonly', true).css('background', '#f6f7f7');
            $('#cliente-tipo').val(c.tipo);
            $('#cliente-nombre').val(c.nombre_comercial);
            $('#cliente-razon').val(c.razon_social);
            $('#cliente-doc-tipo').val(c.documento_tipo);
            $('#cliente-doc-numero').val(c.documento_numero);
            $('#cliente-email').val(c.email);
            $('#cliente-telefono').val(c.telefono);
            $('#cliente-pais').val(c.pais);
            $('#cliente-ciudad').val(c.ciudad);
            $('#cliente-direccion').val(c.direccion);
            $('#cliente-regimen').val(c.regimen_fiscal);
            $('#cliente-retencion').val(c.retencion_default);
            $('#cliente-contacto-nombre').val(c.contacto_nombre);
            $('#cliente-contacto-cargo').val(c.contacto_cargo);
            $('#cliente-metodo-pago').val(c.metodo_pago_preferido);
            $('#cliente-notas').val(c.notas);
            $('#cliente-activo').prop('checked', c.activo == 1);

            // Mostrar/ocultar razón social según tipo
            toggleRazonSocial(c.tipo);

            // Scroll al formulario en móvil
            if ($(window).width() < 782) {
                $('html, body').animate({
                    scrollTop: $('#ga-form-cliente-card').offset().top - 50
                }, 300);
            }
        }
    }

    /**
     * Mostrar/ocultar campo razón social según tipo de cliente
     * @param {string} tipo - Tipo de cliente (EMPRESA o PERSONA_NATURAL)
     */
    function toggleRazonSocial(tipo) {
        if (tipo === 'EMPRESA') {
            $('#grupo-razon-social').slideDown(200);
        } else {
            $('#grupo-razon-social').slideUp(200);
            $('#cliente-razon').val('');
        }
    }

    // ============================================================
    // EVENT HANDLERS
    // ============================================================

    // Cambio de tipo de cliente
    $('#cliente-tipo').on('change', function() {
        toggleRazonSocial($(this).val());
    });

    // Botón nuevo cliente
    $('#ga-btn-nuevo-cliente').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    // Botón cancelar
    $('#ga-btn-cancelar-cliente').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    // Clic en editar cliente
    $('.ga-btn-edit-cliente').on('click', function(e) {
        e.preventDefault();
        loadCliente($(this).data('id'));
    });

    // Clic en eliminar cliente
    $('.ga-btn-delete-cliente').on('click', function(e) {
        e.preventDefault();

        if (!confirm(gaAdmin.i18n.confirmDelete)) {
            return;
        }

        var id = $(this).data('id');
        var $row = $(this).closest('tr');

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_cliente',
            nonce: gaAdmin.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                // Remover fila de la tabla con animación
                $row.fadeOut(300, function() {
                    $(this).remove();
                });
                resetForm();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Envío del formulario
    $('#ga-form-cliente').on('submit', function(e) {
        e.preventDefault();

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        // Construir datos del formulario
        var formData = {
            action: 'ga_save_cliente',
            nonce: gaAdmin.nonce,
            id: $('#cliente-id').val(),
            codigo: $('#cliente-codigo').val(),
            tipo: $('#cliente-tipo').val(),
            nombre_comercial: $('#cliente-nombre').val(),
            razon_social: $('#cliente-razon').val(),
            documento_tipo: $('#cliente-doc-tipo').val(),
            documento_numero: $('#cliente-doc-numero').val(),
            email: $('#cliente-email').val(),
            telefono: $('#cliente-telefono').val(),
            pais: $('#cliente-pais').val(),
            ciudad: $('#cliente-ciudad').val(),
            direccion: $('#cliente-direccion').val(),
            regimen_fiscal: $('#cliente-regimen').val(),
            retencion_default: $('#cliente-retencion').val(),
            contacto_nombre: $('#cliente-contacto-nombre').val(),
            contacto_cargo: $('#cliente-contacto-cargo').val(),
            metodo_pago_preferido: $('#cliente-metodo-pago').val(),
            notas: $('#cliente-notas').val(),
            activo: $('#cliente-activo').is(':checked') ? 1 : 0
        };

        // Enviar al servidor
        $.post(gaAdmin.ajaxUrl, formData, function(response) {
            if (response.success) {
                // Recargar página para ver cambios
                location.reload();
            } else {
                alert(response.data.message);
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar', 'gestionadmin-wolk')); ?>');
            }
        }).fail(function() {
            alert(gaAdmin.i18n.error);
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar', 'gestionadmin-wolk')); ?>');
        });
    });
});
</script>
