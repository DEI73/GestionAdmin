<?php
/**
 * Vista: Proyectos GestionAdmin
 *
 * Página de administración para gestionar proyectos.
 * Los proyectos pertenecen a un caso y contienen tareas.
 * Código automático: PRY-XXX
 *
 * @package GestionAdmin_Wolk
 * @since 1.2.0
 */

// Seguridad: Verificar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener datos para la vista
$proyectos = GA_Proyectos::get_all();             // Todos los proyectos
$clientes = GA_Clientes::get_for_dropdown();      // Clientes para filtro
$casos = GA_Casos::get_for_dropdown();            // Casos para selector
$estados = GA_Proyectos::get_estados();           // Estados posibles
$usuarios = GA_Usuarios::get_for_dropdown();      // Usuarios para responsable
?>
<div class="wrap ga-admin">
    <!-- Encabezado de página -->
    <h1>
        <?php esc_html_e('Proyectos', 'gestionadmin-wolk'); ?>
        <a href="#" class="page-title-action" id="ga-btn-nuevo-proyecto">
            <?php esc_html_e('Nuevo Proyecto', 'gestionadmin-wolk'); ?>
        </a>
    </h1>

    <p class="description">
        <?php esc_html_e('Los proyectos son unidades de trabajo donde se asignan tareas. Cada proyecto pertenece a un caso.', 'gestionadmin-wolk'); ?>
    </p>

    <!-- Filtros -->
    <div class="ga-card" style="padding: 10px 15px; margin-bottom: 15px;">
        <!-- Filtro por cliente -->
        <label style="margin-right: 10px;">
            <strong><?php esc_html_e('Cliente:', 'gestionadmin-wolk'); ?></strong>
        </label>
        <select id="filtro-cliente" class="ga-form-select" style="width: auto; display: inline-block; margin-right: 20px;">
            <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
            <?php foreach ($clientes as $cliente) : ?>
                <option value="<?php echo esc_attr($cliente->id); ?>">
                    <?php echo esc_html($cliente->nombre_comercial); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Filtro por estado -->
        <label style="margin-right: 10px;">
            <strong><?php esc_html_e('Estado:', 'gestionadmin-wolk'); ?></strong>
        </label>
        <select id="filtro-estado" class="ga-form-select" style="width: auto; display: inline-block;">
            <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
            <?php foreach ($estados as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>">
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="ga-row">
        <!-- ============================================================ -->
        <!-- COLUMNA IZQUIERDA: Listado de proyectos -->
        <!-- ============================================================ -->
        <div class="ga-col ga-col-6">
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Proyectos Registrados', 'gestionadmin-wolk'); ?></h2>
                </div>

                <?php if (empty($proyectos)) : ?>
                    <p style="padding: 20px; text-align: center; color: #666;">
                        <?php esc_html_e('No hay proyectos registrados.', 'gestionadmin-wolk'); ?>
                    </p>
                <?php else : ?>
                    <table class="ga-table" id="tabla-proyectos">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Proyecto', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Caso/Cliente', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Avance', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proyectos as $proyecto) : ?>
                                <tr data-id="<?php echo esc_attr($proyecto->id); ?>"
                                    data-cliente="<?php echo esc_attr($proyecto->cliente_id); ?>"
                                    data-estado="<?php echo esc_attr($proyecto->estado); ?>">
                                    <!-- Código y nombre -->
                                    <td>
                                        <code><?php echo esc_html($proyecto->codigo); ?></code><br>
                                        <strong><?php echo esc_html($proyecto->nombre); ?></strong>
                                    </td>

                                    <!-- Caso y cliente -->
                                    <td>
                                        <small><?php echo esc_html($proyecto->caso_numero); ?></small><br>
                                        <?php echo esc_html($proyecto->cliente_nombre); ?>
                                    </td>

                                    <!-- Estado -->
                                    <td>
                                        <?php
                                        $estado_class = 'ga-badge';
                                        switch ($proyecto->estado) {
                                            case 'PLANIFICACION':
                                                $estado_class .= ' ga-badge-info';
                                                break;
                                            case 'EN_PROGRESO':
                                                $estado_class .= ' ga-badge-success';
                                                break;
                                            case 'PAUSADO':
                                                $estado_class .= ' ga-badge-warning';
                                                break;
                                            case 'COMPLETADO':
                                                $estado_class .= '';
                                                break;
                                            case 'CANCELADO':
                                                $estado_class .= ' ga-badge-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo esc_attr($estado_class); ?>">
                                            <?php echo esc_html($estados[$proyecto->estado] ?? $proyecto->estado); ?>
                                        </span>
                                    </td>

                                    <!-- Porcentaje de avance -->
                                    <td>
                                        <div class="ga-progress" style="width: 60px;">
                                            <div class="ga-progress-bar" style="width: <?php echo esc_attr($proyecto->porcentaje_avance); ?>%;"></div>
                                        </div>
                                        <small><?php echo esc_html($proyecto->porcentaje_avance); ?>%</small>
                                    </td>

                                    <!-- Acciones -->
                                    <td>
                                        <a href="#" class="ga-btn-edit-proyecto" data-id="<?php echo esc_attr($proyecto->id); ?>">
                                            <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                        </a>
                                        <?php if (!in_array($proyecto->estado, array('COMPLETADO', 'CANCELADO'))) : ?>
                                            <a href="#" class="ga-btn-delete-proyecto" data-id="<?php echo esc_attr($proyecto->id); ?>"
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
        <!-- COLUMNA DERECHA: Formulario de proyecto -->
        <!-- ============================================================ -->
        <div class="ga-col ga-col-6">
            <div class="ga-card" id="ga-form-proyecto-card">
                <div class="ga-card-header">
                    <h2 id="ga-form-title-proyecto">
                        <?php esc_html_e('Nuevo Proyecto', 'gestionadmin-wolk'); ?>
                    </h2>
                </div>

                <form id="ga-form-proyecto">
                    <input type="hidden" name="id" id="proyecto-id" value="">

                    <!-- Caso (con filtro dinámico por cliente) -->
                    <div class="ga-row">
                        <!-- Filtro de cliente para casos -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="proyecto-cliente-filtro">
                                    <?php esc_html_e('Filtrar por Cliente', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="proyecto-cliente-filtro" class="ga-form-select">
                                    <option value=""><?php esc_html_e('Todos los clientes', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($clientes as $cliente) : ?>
                                        <option value="<?php echo esc_attr($cliente->id); ?>">
                                            <?php echo esc_html($cliente->nombre_comercial); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Selector de caso -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="proyecto-caso">
                                    <?php esc_html_e('Caso', 'gestionadmin-wolk'); ?> *
                                </label>
                                <select id="proyecto-caso" name="caso_id" class="ga-form-select" required>
                                    <option value=""><?php esc_html_e('-- Seleccionar --', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($casos as $caso) : ?>
                                        <option value="<?php echo esc_attr($caso->id); ?>"
                                                data-cliente="<?php echo esc_html($caso->cliente_nombre); ?>">
                                            <?php echo esc_html($caso->numero . ' - ' . $caso->titulo); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="proyecto-nombre">
                            <?php esc_html_e('Nombre del Proyecto', 'gestionadmin-wolk'); ?> *
                        </label>
                        <input type="text" id="proyecto-nombre" name="nombre" class="ga-form-input"
                               required maxlength="200">
                    </div>

                    <!-- Descripción -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="proyecto-descripcion">
                            <?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="proyecto-descripcion" name="descripcion" class="ga-form-textarea"
                                  rows="3"></textarea>
                    </div>

                    <div class="ga-row">
                        <!-- Estado -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="proyecto-estado">
                                    <?php esc_html_e('Estado', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="proyecto-estado" name="estado" class="ga-form-select">
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
                                <label class="ga-form-label" for="proyecto-responsable">
                                    <?php esc_html_e('Responsable', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="proyecto-responsable" name="responsable_id" class="ga-form-select">
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
                        <?php esc_html_e('Cronograma', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-row">
                        <!-- Fecha inicio -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="proyecto-fecha-inicio">
                                    <?php esc_html_e('Fecha Inicio', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="date" id="proyecto-fecha-inicio" name="fecha_inicio"
                                       class="ga-form-input">
                            </div>
                        </div>

                        <!-- Fecha fin estimada -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="proyecto-fecha-fin">
                                    <?php esc_html_e('Fecha Fin Est.', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="date" id="proyecto-fecha-fin" name="fecha_fin_estimada"
                                       class="ga-form-input">
                            </div>
                        </div>
                    </div>

                    <!-- Presupuesto -->
                    <h4 style="margin: 20px 0 10px; border-bottom: 1px solid var(--ga-border); padding-bottom: 5px;">
                        <?php esc_html_e('Presupuesto', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-row">
                        <!-- Horas Presupuestadas -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="proyecto-horas">
                                    <?php esc_html_e('Horas Presupuestadas', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="number" id="proyecto-horas" name="presupuesto_horas"
                                       class="ga-form-input ga-calc-trigger" min="0" step="0.5" value="0">
                            </div>
                        </div>

                        <!-- Tarifa por Hora -->
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="proyecto-tarifa">
                                    <?php esc_html_e('Tarifa por Hora (USD)', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="number" id="proyecto-tarifa" name="tarifa_hora"
                                       class="ga-form-input ga-calc-trigger" step="0.01" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Cálculo de presupuesto -->
                    <div class="ga-presupuesto-calc" style="background: #f9f9f9; border: 1px solid var(--ga-border); border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                        <!-- Subtotal -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div>
                                <strong><?php esc_html_e('Subtotal:', 'gestionadmin-wolk'); ?></strong>
                                <small id="display-calculo" style="color: #666; margin-left: 5px;">(0 hrs × $0.00)</small>
                            </div>
                            <span id="display-subtotal" style="font-size: 16px; font-weight: bold;">$0.00</span>
                        </div>

                        <!-- Descuento -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed var(--ga-border);">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <strong><?php esc_html_e('Descuento:', 'gestionadmin-wolk'); ?></strong>
                                <input type="number" id="proyecto-descuento-pct" name="descuento_porcentaje"
                                       class="ga-form-input ga-calc-trigger" style="width: 70px; text-align: right;"
                                       min="0" max="100" step="0.01" value="0">
                                <span>%</span>
                                <span style="color: #999;"><?php esc_html_e('ó', 'gestionadmin-wolk'); ?></span>
                                <span>$</span>
                                <input type="number" id="proyecto-descuento-monto" name="descuento_monto"
                                       class="ga-form-input ga-calc-trigger" style="width: 100px; text-align: right;"
                                       min="0" step="0.01" value="0">
                            </div>
                            <span id="display-descuento" style="color: var(--ga-danger);">-$0.00</span>
                        </div>

                        <!-- Total -->
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="font-size: 16px;"><?php esc_html_e('TOTAL A COBRAR:', 'gestionadmin-wolk'); ?></strong>
                            <span id="display-total" style="font-size: 20px; font-weight: bold; color: var(--ga-success);">$0.00</span>
                        </div>

                        <!-- Campos hidden para enviar al servidor -->
                        <input type="hidden" id="proyecto-subtotal" name="subtotal" value="0">
                        <input type="hidden" id="proyecto-total" name="total" value="0">
                        <!-- Campo legado para compatibilidad -->
                        <input type="hidden" id="proyecto-dinero" name="presupuesto_dinero" value="0">
                    </div>

                    <!-- Visibilidad en portal -->
                    <h4 style="margin: 20px 0 10px; border-bottom: 1px solid var(--ga-border); padding-bottom: 5px;">
                        <?php esc_html_e('Portal Cliente', 'gestionadmin-wolk'); ?>
                    </h4>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="proyecto-mostrar-tareas" name="mostrar_tareas_equipo" value="1" checked>
                            <?php esc_html_e('Mostrar tareas del equipo', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="proyecto-mostrar-horas" name="mostrar_horas_equipo" value="1">
                            <?php esc_html_e('Mostrar horas del equipo', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="proyecto-mostrar-ranking" name="mostrar_ranking" value="1">
                            <?php esc_html_e('Mostrar ranking de productividad', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <!-- Notas -->
                    <div class="ga-form-group">
                        <label class="ga-form-label" for="proyecto-notas">
                            <?php esc_html_e('Notas Internas', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="proyecto-notas" name="notas" class="ga-form-textarea" rows="2"></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="ga-form-group">
                        <button type="submit" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                        </button>
                        <button type="button" class="ga-btn" id="ga-btn-cancelar-proyecto">
                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT: Lógica del formulario de proyectos -->
<!-- ============================================================ -->
<script>
jQuery(document).ready(function($) {
    /**
     * Array de proyectos y casos cargados desde PHP
     */
    var proyectos = <?php echo wp_json_encode(array_map(function($p) {
        return array(
            'id'                   => $p->id,
            'caso_id'              => $p->caso_id,
            'cliente_id'           => $p->cliente_id,
            'codigo'               => $p->codigo,
            'nombre'               => $p->nombre,
            'descripcion'          => $p->descripcion,
            'estado'               => $p->estado,
            'responsable_id'       => $p->responsable_id,
            'fecha_inicio'         => $p->fecha_inicio,
            'fecha_fin_estimada'   => $p->fecha_fin_estimada,
            'presupuesto_horas'    => $p->presupuesto_horas,
            'tarifa_hora'          => $p->tarifa_hora ?? 0,
            'descuento_porcentaje' => $p->descuento_porcentaje ?? 0,
            'descuento_monto'      => $p->descuento_monto ?? 0,
            'subtotal'             => $p->subtotal ?? 0,
            'total'                => $p->total ?? 0,
            'presupuesto_dinero'   => $p->presupuesto_dinero,
            'mostrar_ranking'      => $p->mostrar_ranking,
            'mostrar_tareas_equipo' => $p->mostrar_tareas_equipo,
            'mostrar_horas_equipo' => $p->mostrar_horas_equipo,
            'notas'                => $p->notas,
        );
    }, $proyectos)); ?>;

    var casos = <?php echo wp_json_encode(array_map(function($c) {
        return array(
            'id'             => $c->id,
            'numero'         => $c->numero,
            'titulo'         => $c->titulo,
            'cliente_nombre' => $c->cliente_nombre,
        );
    }, $casos)); ?>;

    /**
     * Resetear formulario
     */
    function resetForm() {
        $('#ga-form-proyecto')[0].reset();
        $('#proyecto-id').val('');
        $('#proyecto-mostrar-tareas').prop('checked', true);
        $('#ga-form-title-proyecto').text('<?php echo esc_js(__('Nuevo Proyecto', 'gestionadmin-wolk')); ?>');

        // Resetear campos de presupuesto
        $('#proyecto-horas').val(0);
        $('#proyecto-tarifa').val(0);
        $('#proyecto-descuento-pct').val(0);
        $('#proyecto-descuento-monto').val(0);
        calcularPresupuesto();
    }

    /**
     * Calcular presupuesto en tiempo real
     * Fórmula: Horas × Tarifa = Subtotal - Descuento = Total
     */
    function calcularPresupuesto() {
        var horas = parseFloat($('#proyecto-horas').val()) || 0;
        var tarifa = parseFloat($('#proyecto-tarifa').val()) || 0;
        var descuentoPct = parseFloat($('#proyecto-descuento-pct').val()) || 0;
        var descuentoMonto = parseFloat($('#proyecto-descuento-monto').val()) || 0;

        // Calcular subtotal
        var subtotal = horas * tarifa;

        // Calcular descuento (prioridad: porcentaje si ambos tienen valor)
        var descuento = 0;
        if (descuentoPct > 0) {
            descuento = subtotal * (descuentoPct / 100);
        } else if (descuentoMonto > 0) {
            descuento = descuentoMonto;
        }

        // Calcular total
        var total = Math.max(0, subtotal - descuento);

        // Actualizar displays
        $('#display-calculo').text('(' + horas + ' hrs × $' + tarifa.toFixed(2) + ')');
        $('#display-subtotal').text('$' + subtotal.toFixed(2));
        $('#display-descuento').text('-$' + descuento.toFixed(2));
        $('#display-total').text('$' + total.toFixed(2));

        // Actualizar campos hidden
        $('#proyecto-subtotal').val(subtotal.toFixed(2));
        $('#proyecto-total').val(total.toFixed(2));
        $('#proyecto-dinero').val(total.toFixed(2)); // Campo legado
    }

    /**
     * Cargar datos de un proyecto para edición
     */
    function loadProyecto(id) {
        var p = proyectos.find(function(proy) { return proy.id == id; });

        if (p) {
            $('#ga-form-title-proyecto').text('<?php echo esc_js(__('Editar Proyecto', 'gestionadmin-wolk')); ?>');

            $('#proyecto-id').val(p.id);
            $('#proyecto-caso').val(p.caso_id);
            $('#proyecto-nombre').val(p.nombre);
            $('#proyecto-descripcion').val(p.descripcion);
            $('#proyecto-estado').val(p.estado);
            $('#proyecto-responsable').val(p.responsable_id || '');
            $('#proyecto-fecha-inicio').val(p.fecha_inicio);
            $('#proyecto-fecha-fin').val(p.fecha_fin_estimada);
            $('#proyecto-horas').val(p.presupuesto_horas);
            $('#proyecto-tarifa').val(p.tarifa_hora || 0);
            $('#proyecto-descuento-pct').val(p.descuento_porcentaje || 0);
            $('#proyecto-descuento-monto').val(p.descuento_monto || 0);
            calcularPresupuesto(); // Recalcular para actualizar displays
            $('#proyecto-mostrar-tareas').prop('checked', p.mostrar_tareas_equipo == 1);
            $('#proyecto-mostrar-horas').prop('checked', p.mostrar_horas_equipo == 1);
            $('#proyecto-mostrar-ranking').prop('checked', p.mostrar_ranking == 1);
            $('#proyecto-notas').val(p.notas);

            // Scroll en móvil
            if ($(window).width() < 782) {
                $('html, body').animate({
                    scrollTop: $('#ga-form-proyecto-card').offset().top - 50
                }, 300);
            }
        }
    }

    /**
     * Filtrar tabla por cliente y estado
     */
    function applyFilters() {
        var clienteId = $('#filtro-cliente').val();
        var estado = $('#filtro-estado').val();

        $('#tabla-proyectos tbody tr').each(function() {
            var $row = $(this);
            var matchCliente = clienteId === '' || $row.data('cliente') == clienteId;
            var matchEstado = estado === '' || $row.data('estado') === estado;

            if (matchCliente && matchEstado) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }

    /**
     * Filtrar opciones del selector de casos por cliente
     */
    function filterCasosByCliente(clienteId) {
        var $casoSelect = $('#proyecto-caso');
        var currentValue = $casoSelect.val();

        $casoSelect.find('option').each(function() {
            var $opt = $(this);
            var optCliente = $opt.data('cliente');

            if ($opt.val() === '' || clienteId === '') {
                $opt.show();
            } else {
                // Buscar el caso y verificar cliente
                var caso = casos.find(function(c) { return c.id == $opt.val(); });
                if (caso && caso.cliente_nombre.includes(clienteId)) {
                    $opt.show();
                } else {
                    $opt.hide();
                }
            }
        });
    }

    // ============================================================
    // EVENT HANDLERS
    // ============================================================

    // Filtros de tabla
    $('#filtro-cliente, #filtro-estado').on('change', applyFilters);

    // ============================================================
    // CÁLCULO DE PRESUPUESTO EN TIEMPO REAL
    // ============================================================

    // Evento para campos que disparan el cálculo
    $('.ga-calc-trigger').on('input change', function() {
        calcularPresupuesto();
    });

    // Descuento mutuamente excluyente: si escribes porcentaje, limpia monto y viceversa
    $('#proyecto-descuento-pct').on('input', function() {
        if (parseFloat($(this).val()) > 0) {
            $('#proyecto-descuento-monto').val(0);
        }
    });

    $('#proyecto-descuento-monto').on('input', function() {
        if (parseFloat($(this).val()) > 0) {
            $('#proyecto-descuento-pct').val(0);
        }
    });

    // Calcular presupuesto inicial
    calcularPresupuesto();

    // Filtro de cliente en formulario para casos
    $('#proyecto-cliente-filtro').on('change', function() {
        // Cargar casos por AJAX según cliente
        var clienteId = $(this).val();
        if (clienteId) {
            $.get(gaAdmin.ajaxUrl, {
                action: 'ga_get_casos_by_cliente',
                nonce: gaAdmin.nonce,
                cliente_id: clienteId
            }, function(response) {
                if (response.success) {
                    var $select = $('#proyecto-caso');
                    $select.html('<option value=""><?php echo esc_js(__('-- Seleccionar --', 'gestionadmin-wolk')); ?></option>');

                    response.data.forEach(function(caso) {
                        $select.append('<option value="' + caso.id + '">' + caso.numero + ' - ' + caso.titulo + '</option>');
                    });
                }
            });
        } else {
            // Recargar todos los casos
            location.reload();
        }
    });

    // Botón nuevo proyecto
    $('#ga-btn-nuevo-proyecto').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    // Botón cancelar
    $('#ga-btn-cancelar-proyecto').on('click', function(e) {
        e.preventDefault();
        resetForm();
    });

    // Clic en editar proyecto
    $('.ga-btn-edit-proyecto').on('click', function(e) {
        e.preventDefault();
        loadProyecto($(this).data('id'));
    });

    // Clic en cancelar proyecto
    $('.ga-btn-delete-proyecto').on('click', function(e) {
        e.preventDefault();

        if (!confirm('<?php echo esc_js(__('¿Estás seguro de cancelar este proyecto?', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        var id = $(this).data('id');

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_proyecto',
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
    $('#ga-form-proyecto').on('submit', function(e) {
        e.preventDefault();

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        var formData = {
            action: 'ga_save_proyecto',
            nonce: gaAdmin.nonce,
            id: $('#proyecto-id').val(),
            caso_id: $('#proyecto-caso').val(),
            nombre: $('#proyecto-nombre').val(),
            descripcion: $('#proyecto-descripcion').val(),
            estado: $('#proyecto-estado').val(),
            responsable_id: $('#proyecto-responsable').val(),
            fecha_inicio: $('#proyecto-fecha-inicio').val(),
            fecha_fin_estimada: $('#proyecto-fecha-fin').val(),
            presupuesto_horas: $('#proyecto-horas').val(),
            tarifa_hora: $('#proyecto-tarifa').val(),
            descuento_porcentaje: $('#proyecto-descuento-pct').val(),
            descuento_monto: $('#proyecto-descuento-monto').val(),
            subtotal: $('#proyecto-subtotal').val(),
            total: $('#proyecto-total').val(),
            presupuesto_dinero: $('#proyecto-dinero').val(),
            mostrar_tareas_equipo: $('#proyecto-mostrar-tareas').is(':checked') ? 1 : 0,
            mostrar_horas_equipo: $('#proyecto-mostrar-horas').is(':checked') ? 1 : 0,
            mostrar_ranking: $('#proyecto-mostrar-ranking').is(':checked') ? 1 : 0,
            notas: $('#proyecto-notas').val()
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
