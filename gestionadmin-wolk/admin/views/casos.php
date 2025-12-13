<?php
/**
 * Vista: Casos GestionAdmin
 *
 * Página de administración para gestionar casos/expedientes.
 * Los casos agrupan proyectos de un mismo cliente.
 * Numeración automática: CASO-[CLIENTE]-[AÑO]-[CONSECUTIVO]
 *
 * @package GestionAdmin_Wolk
 * @since 1.2.0
 */

// Seguridad: Verificar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener datos para la vista
$casos = GA_Casos::get_all();                     // Todos los casos
$clientes = GA_Clientes::get_for_dropdown();      // Clientes para selector
$tipos = GA_Casos::get_tipos();                   // Tipos de caso
$estados = GA_Casos::get_estados();               // Estados posibles
$prioridades = GA_Casos::get_prioridades();       // Prioridades
$usuarios = GA_Usuarios::get_for_dropdown();      // Usuarios para responsable
?>
<div class="wrap ga-admin">
    <!-- Encabezado de página -->
    <h1>
        <?php esc_html_e('Casos', 'gestionadmin-wolk'); ?>
        <a href="#" class="page-title-action" id="ga-btn-nuevo-caso">
            <?php esc_html_e('Nuevo Caso', 'gestionadmin-wolk'); ?>
        </a>
    </h1>

    <p class="description">
        <?php esc_html_e('Los casos son expedientes que agrupan proyectos relacionados de un cliente.', 'gestionadmin-wolk'); ?>
    </p>

    <!-- Filtro por cliente -->
    <div class="ga-card" style="padding: 10px 15px; margin-bottom: 15px;">
        <label style="margin-right: 10px;">
            <strong><?php esc_html_e('Filtrar por cliente:', 'gestionadmin-wolk'); ?></strong>
        </label>
        <select id="filtro-cliente" class="ga-form-select" style="width: auto; display: inline-block;">
            <option value=""><?php esc_html_e('Todos los clientes', 'gestionadmin-wolk'); ?></option>
            <?php foreach ($clientes as $cliente) : ?>
                <option value="<?php echo esc_attr($cliente->id); ?>">
                    <?php echo esc_html($cliente->codigo . ' - ' . $cliente->nombre_comercial); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="ga-row">
        <!-- ============================================================ -->
        <!-- COLUMNA IZQUIERDA: Listado de casos -->
        <!-- ============================================================ -->
        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Casos Registrados', 'gestionadmin-wolk'); ?></h2>
                </div>

                <?php if (empty($casos)) : ?>
                    <p style="padding: 20px; text-align: center; color: #666;">
                        <?php esc_html_e('No hay casos registrados.', 'gestionadmin-wolk'); ?>
                    </p>
                <?php else : ?>
                    <table class="ga-table" id="tabla-casos">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Caso', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Prioridad', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($casos as $caso) : ?>
                                <tr data-id="<?php echo esc_attr($caso->id); ?>"
                                    data-cliente="<?php echo esc_attr($caso->cliente_id); ?>">
                                    <!-- Número y título -->
                                    <td>
                                        <code><?php echo esc_html($caso->numero); ?></code><br>
                                        <strong><?php echo esc_html($caso->titulo); ?></strong>
                                    </td>

                                    <!-- Cliente -->
                                    <td>
                                        <?php echo esc_html($caso->cliente_nombre); ?>
                                    </td>

                                    <!-- Estado con badge de color -->
                                    <td>
                                        <?php
                                        $estado_class = 'ga-badge';
                                        switch ($caso->estado) {
                                            case 'ABIERTO':
                                                $estado_class .= ' ga-badge-info';
                                                break;
                                            case 'EN_PROGRESO':
                                                $estado_class .= ' ga-badge-success';
                                                break;
                                            case 'EN_ESPERA':
                                                $estado_class .= ' ga-badge-warning';
                                                break;
                                            case 'CERRADO':
                                                $estado_class .= '';
                                                break;
                                            case 'CANCELADO':
                                                $estado_class .= ' ga-badge-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo esc_attr($estado_class); ?>">
                                            <?php echo esc_html($estados[$caso->estado] ?? $caso->estado); ?>
                                        </span>
                                    </td>

                                    <!-- Prioridad -->
                                    <td>
                                        <?php if ($caso->prioridad === 'URGENTE') : ?>
                                            <span class="priority-urgente">
                                                <?php echo esc_html($prioridades[$caso->prioridad]); ?>
                                            </span>
                                        <?php elseif ($caso->prioridad === 'ALTA') : ?>
                                            <span class="priority-alta">
                                                <?php echo esc_html($prioridades[$caso->prioridad]); ?>
                                            </span>
                                        <?php else : ?>
                                            <?php echo esc_html($prioridades[$caso->prioridad] ?? $caso->prioridad); ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Acciones -->
                                    <td>
                                        <a href="#" class="ga-btn-edit-caso" data-id="<?php echo esc_attr($caso->id); ?>">
                                            <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                        </a>
                                        <?php if (!in_array($caso->estado, array('CERRADO', 'CANCELADO'))) : ?>
                                            <a href="#" class="ga-btn-delete-caso" data-id="<?php echo esc_attr($caso->id); ?>"
                                               style="color: var(--ga-danger); margin-left: 10px;">
                                                <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
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
        <!-- COLUMNA DERECHA: Formulario de caso -->
        <!-- ============================================================ -->
        <div class="ga-col ga-col-6">
            <div class="ga-card" id="ga-form-caso-card">
                <div class="ga-card-header">
                    <h2 id="ga-form-title-caso">
                        <?php esc_html_e('Nuevo Caso', 'gestionadmin-wolk'); ?>
                    </h2>
                </div>

                <form id="ga-form-caso">
                    <input type="hidden" name="id" id="caso-id" value="">

                    <!-- Cliente -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="caso-cliente">
                            <?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?> *
                        </label>
                        <select id="caso-cliente" name="cliente_id" class="ga-form-select" required>
                            <option value=""><?php esc_html_e('-- Seleccionar cliente --', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($clientes as $cliente) : ?>
                                <option value="<?php echo esc_attr($cliente->id); ?>">
                                    <?php echo esc_html($cliente->codigo . ' - ' . $cliente->nombre_comercial); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Título -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="caso-titulo">
                            <?php esc_html_e('Título del Caso', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="caso-titulo" name="titulo" class="ga-form-input"
                               required maxlength="200"
                               placeholder="<?php esc_attr_e('Descripción breve del caso', 'gestionadmin-wolk'); ?>">
                    </div>

                    <!-- Descripción -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="caso-descripcion">
                            <?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="caso-descripcion" name="descripcion" class="ga-form-textarea"
                                  rows="3"></textarea>
                    </div>

                    <div class="ga-row">
                        <!-- Tipo -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-tipo">
                                    <?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="caso-tipo" name="tipo" class="ga-form-select">
                                    <?php foreach ($tipos as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Prioridad -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-prioridad">
                                    <?php esc_html_e('Prioridad', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="caso-prioridad" name="prioridad" class="ga-form-select">
                                    <?php foreach ($prioridades as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($key, 'MEDIA'); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <!-- Estado -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-estado">
                                    <?php esc_html_e('Estado', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="caso-estado" name="estado" class="ga-form-select">
                                    <?php foreach ($estados as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Responsable -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-responsable">
                                    <?php esc_html_e('Responsable', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="caso-responsable" name="responsable_id" class="ga-form-select">
                                    <option value=""><?php esc_html_e('Sin asignar', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($usuarios as $usuario) : ?>
                                        <option value="<?php echo esc_attr($usuario->usuario_wp_id); ?>">
                                            <?php echo esc_html($usuario->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <h4 style="margin: 20px 0 10px; border-bottom: 1px solid var(--ga-border); padding-bottom: 5px;">
                        <?php esc_html_e('Fechas', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-row">
                        <!-- Fecha apertura -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-fecha-apertura">
                                    <?php esc_html_e('Fecha Apertura', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="date" id="caso-fecha-apertura" name="fecha_apertura"
                                       class="ga-form-input" required
                                       value="<?php echo esc_attr(date('Y-m-d')); ?>">
                            </div>
                        </div>

                        <!-- Fecha cierre estimada -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-fecha-cierre">
                                    <?php esc_html_e('Fecha Cierre Est.', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="date" id="caso-fecha-cierre" name="fecha_cierre_estimada"
                                       class="ga-form-input">
                            </div>
                        </div>
                    </div>

                    <!-- Presupuesto -->
                    <h4 style="margin: 20px 0 10px; border-bottom: 1px solid var(--ga-border); padding-bottom: 5px;">
                        <?php esc_html_e('Presupuesto', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-row">
                        <!-- Horas -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-horas">
                                    <?php esc_html_e('Horas Presupuestadas', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="caso-horas" name="presupuesto_horas"
                                       class="ga-form-input" min="0">
                            </div>
                        </div>

                        <!-- Dinero -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="caso-dinero">
                                    <?php esc_html_e('Monto USD', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="caso-dinero" name="presupuesto_dinero"
                                       class="ga-form-input" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="caso-notas">
                            <?php esc_html_e('Notas Internas', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="caso-notas" name="notas" class="ga-form-textarea" rows="2"></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="ga-form-group">
                        <button type="submit" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                        </button>
                        <button type="button" class="ga-btn" id="ga-btn-cancelar-caso">
                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT: Lógica del formulario de casos -->
<!-- ============================================================ -->
<script>
jQuery(document).ready(function($) {
    /**
     * Array de casos cargado desde PHP
     */
    var casos = <?php echo wp_json_encode(array_map(function($c) {
        return array(
            'id'                   => $c->id,
            'numero'               => $c->numero,
            'cliente_id'           => $c->cliente_id,
            'titulo'               => $c->titulo,
            'descripcion'          => $c->descripcion,
            'tipo'                 => $c->tipo,
            'estado'               => $c->estado,
            'prioridad'            => $c->prioridad,
            'fecha_apertura'       => $c->fecha_apertura,
            'fecha_cierre_estimada' => $c->fecha_cierre_estimada,
            'responsable_id'       => $c->responsable_id,
            'presupuesto_horas'    => $c->presupuesto_horas,
            'presupuesto_dinero'   => $c->presupuesto_dinero,
            'notas'                => $c->notas,
        );
    }, $casos)); ?>;

    /**
     * Resetear formulario
     */
    function resetForm() {
        $('#ga-form-caso')[0].reset();
        $('#caso-id').val('');
        $('#caso-fecha-apertura').val('<?php echo date('Y-m-d'); ?>');
        $('#ga-form-title-caso').text('<?php echo esc_js(__('Nuevo Caso', 'gestionadmin-wolk')); ?>');
    }

    /**
     * Cargar datos de un caso para edición
     */
    function loadCaso(id) {
        var c = casos.find(function(caso) { return caso.id == id; });

        if (c) {
            $('#ga-form-title-caso').text('<?php echo esc_js(__('Editar Caso', 'gestionadmin-wolk')); ?>');

            $('#caso-id').val(c.id);
            $('#caso-cliente').val(c.cliente_id);
            $('#caso-titulo').val(c.titulo);
            $('#caso-descripcion').val(c.descripcion);
            $('#caso-tipo').val(c.tipo);
            $('#caso-estado').val(c.estado);
            $('#caso-prioridad').val(c.prioridad);
            $('#caso-fecha-apertura').val(c.fecha_apertura);
            $('#caso-fecha-cierre').val(c.fecha_cierre_estimada);
            $('#caso-responsable').val(c.responsable_id || '');
            $('#caso-horas').val(c.presupuesto_horas);
            $('#caso-dinero').val(c.presupuesto_dinero);
            $('#caso-notas').val(c.notas);

            // Scroll en móvil
            if ($(window).width() < 782) {
                $('html, body').animate({
                    scrollTop: $('#ga-form-caso-card').offset().top - 50
                }, 300);
            }
        }
    }

    /**
     * Filtrar tabla por cliente
     */
    function filterByCliente(clienteId) {
        $('#tabla-casos tbody tr').each(function() {
            var $row = $(this);
            if (clienteId === '' || $row.data('cliente') == clienteId) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }

    // ============================================================
    // EVENT HANDLERS
    // ============================================================

    // Filtro por cliente
    $('#filtro-cliente').on('change', function() {
        filterByCliente($(this).val());
    });

    // Botón nuevo caso
    $('#ga-btn-nuevo-caso').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    // Botón cancelar
    $('#ga-btn-cancelar-caso').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    // Clic en editar caso
    $('.ga-btn-edit-caso').on('click', function(e) {
        e.preventDefault();
        loadCaso($(this).data('id'));
    });

    // Clic en cancelar caso
    $('.ga-btn-delete-caso').on('click', function(e) {
        e.preventDefault();

        if (!confirm('<?php echo esc_js(__('¿Estás seguro de cancelar este caso?', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        var id = $(this).data('id');
        var $row = $(this).closest('tr');

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_caso',
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

    // Envío del formulario
    $('#ga-form-caso').on('submit', function(e) {
        e.preventDefault();

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        var formData = {
            action: 'ga_save_caso',
            nonce: gaAdmin.nonce,
            id: $('#caso-id').val(),
            cliente_id: $('#caso-cliente').val(),
            titulo: $('#caso-titulo').val(),
            descripcion: $('#caso-descripcion').val(),
            tipo: $('#caso-tipo').val(),
            estado: $('#caso-estado').val(),
            prioridad: $('#caso-prioridad').val(),
            fecha_apertura: $('#caso-fecha-apertura').val(),
            fecha_cierre_estimada: $('#caso-fecha-cierre').val(),
            responsable_id: $('#caso-responsable').val(),
            presupuesto_horas: $('#caso-horas').val(),
            presupuesto_dinero: $('#caso-dinero').val(),
            notas: $('#caso-notas').val()
        };

        $.post(gaAdmin.ajaxUrl, formData, function(response) {
            if (response.success) {
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
