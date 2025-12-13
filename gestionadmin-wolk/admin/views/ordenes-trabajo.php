<?php
/**
 * Vista: Órdenes de Trabajo (Marketplace)
 *
 * Gestión administrativa de órdenes de trabajo.
 * Permite crear, editar, publicar y gestionar órdenes.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener datos
$ordenes = GA_Ordenes_Trabajo::get_all();
$clientes = GA_Clientes::get_all(array('activo' => 1));
$estadisticas = GA_Ordenes_Trabajo::get_estadisticas();

// Enums para los selectores
$estados = GA_Ordenes_Trabajo::get_estados();
$categorias = GA_Ordenes_Trabajo::get_categorias();
$tipos_pago = GA_Ordenes_Trabajo::get_tipos_pago();
$modalidades = GA_Ordenes_Trabajo::get_modalidades();
$niveles = GA_Ordenes_Trabajo::get_niveles_experiencia();
$prioridades = GA_Ordenes_Trabajo::get_prioridades();
?>
<div class="wrap ga-admin">
    <h1>
        <?php esc_html_e('Órdenes de Trabajo', 'gestionadmin-wolk'); ?>
        <button type="button" class="page-title-action" id="ga-btn-new-orden">
            <?php esc_html_e('Añadir Nueva', 'gestionadmin-wolk'); ?>
        </button>
    </h1>

    <p class="description">
        <?php esc_html_e('Gestiona las órdenes de trabajo del Marketplace. Los aplicantes pueden postularse a órdenes publicadas.', 'gestionadmin-wolk'); ?>
    </p>

    <!-- =========================================================================
         ESTADÍSTICAS RÁPIDAS
    ========================================================================== -->
    <div class="ga-row" style="margin-top: 20px;">
        <div class="ga-col ga-col-2">
            <div class="ga-card ga-stat-card">
                <span class="ga-stat-number"><?php echo esc_html($estadisticas['total']); ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Total', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>
        <div class="ga-col ga-col-2">
            <div class="ga-card ga-stat-card ga-stat-success">
                <span class="ga-stat-number"><?php echo esc_html($estadisticas['activas']); ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Publicadas', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>
        <div class="ga-col ga-col-2">
            <div class="ga-card ga-stat-card ga-stat-info">
                <span class="ga-stat-number">
                    <?php echo isset($estadisticas['por_estado']['EN_PROGRESO']) ? esc_html($estadisticas['por_estado']['EN_PROGRESO']->total) : '0'; ?>
                </span>
                <span class="ga-stat-label"><?php esc_html_e('En Progreso', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>
        <div class="ga-col ga-col-2">
            <div class="ga-card ga-stat-card ga-stat-warning">
                <span class="ga-stat-number">
                    <?php echo isset($estadisticas['por_estado']['BORRADOR']) ? esc_html($estadisticas['por_estado']['BORRADOR']->total) : '0'; ?>
                </span>
                <span class="ga-stat-label"><?php esc_html_e('Borradores', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>
        <div class="ga-col ga-col-2">
            <div class="ga-card ga-stat-card">
                <span class="ga-stat-number">
                    <?php echo isset($estadisticas['por_estado']['COMPLETADA']) ? esc_html($estadisticas['por_estado']['COMPLETADA']->total) : '0'; ?>
                </span>
                <span class="ga-stat-label"><?php esc_html_e('Completadas', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         FILTROS
    ========================================================================== -->
    <div class="ga-card" style="margin-top: 20px;">
        <div class="ga-row">
            <div class="ga-col ga-col-3">
                <label class="ga-form-label"><?php esc_html_e('Buscar', 'gestionadmin-wolk'); ?></label>
                <input type="text" id="ga-filter-buscar" class="ga-form-input"
                       placeholder="<?php esc_attr_e('Código, título...', 'gestionadmin-wolk'); ?>">
            </div>
            <div class="ga-col ga-col-2">
                <label class="ga-form-label"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></label>
                <select id="ga-filter-estado" class="ga-form-input">
                    <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($estados as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ga-col ga-col-2">
                <label class="ga-form-label"><?php esc_html_e('Categoría', 'gestionadmin-wolk'); ?></label>
                <select id="ga-filter-categoria" class="ga-form-input">
                    <option value=""><?php esc_html_e('Todas', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($categorias as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ga-col ga-col-2">
                <label class="ga-form-label"><?php esc_html_e('Modalidad', 'gestionadmin-wolk'); ?></label>
                <select id="ga-filter-modalidad" class="ga-form-input">
                    <option value=""><?php esc_html_e('Todas', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($modalidades as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ga-col ga-col-3" style="display: flex; align-items: flex-end;">
                <button type="button" class="ga-btn" id="ga-btn-filter">
                    <?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?>
                </button>
                <button type="button" class="ga-btn ga-btn-link" id="ga-btn-clear-filter">
                    <?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TABLA DE ÓRDENES
    ========================================================================== -->
    <div class="ga-card" style="margin-top: 20px;">
        <table class="ga-table" id="ga-ordenes-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Título', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Categoría', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Tipo Pago', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Presupuesto', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Aplicaciones', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ordenes)) : ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <?php esc_html_e('No hay órdenes de trabajo. Crea la primera.', 'gestionadmin-wolk'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($ordenes as $orden) :
                        $num_aplicaciones = GA_Ordenes_Trabajo::count_aplicaciones($orden->id);
                        $presupuesto = '';
                        if ($orden->presupuesto_min && $orden->presupuesto_max) {
                            $presupuesto = '$' . number_format($orden->presupuesto_min, 0) . ' - $' . number_format($orden->presupuesto_max, 0);
                        } elseif ($orden->presupuesto_max) {
                            $presupuesto = 'Hasta $' . number_format($orden->presupuesto_max, 0);
                        } elseif ($orden->presupuesto_min) {
                            $presupuesto = 'Desde $' . number_format($orden->presupuesto_min, 0);
                        } else {
                            $presupuesto = __('A convenir', 'gestionadmin-wolk');
                        }
                    ?>
                        <tr data-id="<?php echo esc_attr($orden->id); ?>"
                            data-estado="<?php echo esc_attr($orden->estado); ?>"
                            data-categoria="<?php echo esc_attr($orden->categoria); ?>"
                            data-modalidad="<?php echo esc_attr($orden->modalidad); ?>">
                            <td>
                                <strong><?php echo esc_html($orden->codigo); ?></strong>
                                <?php if ($orden->prioridad === 'URGENTE') : ?>
                                    <span class="ga-badge ga-badge-danger" style="font-size: 10px;">URGENTE</span>
                                <?php elseif ($orden->prioridad === 'ALTA') : ?>
                                    <span class="ga-badge ga-badge-warning" style="font-size: 10px;">ALTA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="ga-btn-edit-orden" data-id="<?php echo esc_attr($orden->id); ?>">
                                    <?php echo esc_html(wp_trim_words($orden->titulo, 8)); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($orden->cliente_nombre ?: '-'); ?></td>
                            <td>
                                <small><?php echo esc_html($categorias[$orden->categoria] ?? $orden->categoria); ?></small>
                            </td>
                            <td><?php echo esc_html($tipos_pago[$orden->tipo_pago] ?? $orden->tipo_pago); ?></td>
                            <td><?php echo esc_html($presupuesto); ?></td>
                            <td style="text-align: center;">
                                <?php if ($num_aplicaciones > 0) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-aplicantes&orden_id=' . $orden->id)); ?>"
                                       class="ga-badge ga-badge-info">
                                        <?php echo esc_html($num_aplicaciones); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="ga-badge ga-badge-secondary">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="ga-badge <?php echo esc_attr(GA_Ordenes_Trabajo::get_estado_class($orden->estado)); ?>">
                                    <?php echo esc_html($estados[$orden->estado] ?? $orden->estado); ?>
                                </span>
                            </td>
                            <td>
                                <div class="ga-actions">
                                    <a href="#" class="ga-btn-edit-orden" data-id="<?php echo esc_attr($orden->id); ?>" title="<?php esc_attr_e('Editar', 'gestionadmin-wolk'); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <?php if ($orden->estado === 'BORRADOR') : ?>
                                        <a href="#" class="ga-btn-publish-orden" data-id="<?php echo esc_attr($orden->id); ?>" title="<?php esc_attr_e('Publicar', 'gestionadmin-wolk'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array($orden->estado, array('BORRADOR', 'CANCELADA'))) : ?>
                                        <a href="#" class="ga-btn-delete-orden" data-id="<?php echo esc_attr($orden->id); ?>" title="<?php esc_attr_e('Eliminar', 'gestionadmin-wolk'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- =============================================================================
     MODAL: FORMULARIO DE ORDEN DE TRABAJO
============================================================================== -->
<div id="ga-modal-orden" class="ga-modal" style="display: none;">
    <div class="ga-modal-content ga-modal-large">
        <div class="ga-modal-header">
            <h2 id="ga-modal-title-orden"><?php esc_html_e('Nueva Orden de Trabajo', 'gestionadmin-wolk'); ?></h2>
            <button type="button" class="ga-modal-close">&times;</button>
        </div>

        <form id="ga-form-orden">
            <input type="hidden" name="id" id="orden-id" value="0">

            <div class="ga-modal-body">
                <!-- Información básica -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Información Básica', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-8">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-titulo">
                                    <?php esc_html_e('Título', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="text" id="orden-titulo" name="titulo" class="ga-form-input" required
                                       placeholder="<?php esc_attr_e('Ej: Desarrollo de aplicación móvil', 'gestionadmin-wolk'); ?>">
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-cliente">
                                    <?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="orden-cliente" name="cliente_id" class="ga-form-input">
                                    <option value=""><?php esc_html_e('Sin cliente específico', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($clientes as $cliente) : ?>
                                        <option value="<?php echo esc_attr($cliente->id); ?>">
                                            <?php echo esc_html($cliente->nombre_comercial); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="orden-descripcion">
                            <?php esc_html_e('Descripción del trabajo', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="orden-descripcion" name="descripcion" class="ga-form-input" rows="5"
                                  placeholder="<?php esc_attr_e('Describe detalladamente el trabajo requerido, objetivos, entregables...', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-categoria">
                                    <?php esc_html_e('Categoría', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="orden-categoria" name="categoria" class="ga-form-input">
                                    <?php foreach ($categorias as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-modalidad">
                                    <?php esc_html_e('Modalidad', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="orden-modalidad" name="modalidad" class="ga-form-input">
                                    <?php foreach ($modalidades as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-prioridad">
                                    <?php esc_html_e('Prioridad', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="orden-prioridad" name="prioridad" class="ga-form-input">
                                    <?php foreach ($prioridades as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($key, 'NORMAL'); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Presupuesto y pago -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Presupuesto y Pago', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-tipo-pago">
                                    <?php esc_html_e('Tipo de Pago', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="orden-tipo-pago" name="tipo_pago" class="ga-form-input">
                                    <?php foreach ($tipos_pago as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-presupuesto-min">
                                    <?php esc_html_e('Presupuesto Mínimo (USD)', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="orden-presupuesto-min" name="presupuesto_min"
                                       class="ga-form-input" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-presupuesto-max">
                                    <?php esc_html_e('Presupuesto Máximo (USD)', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="orden-presupuesto-max" name="presupuesto_max"
                                       class="ga-form-input" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requisitos -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Requisitos', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-nivel-experiencia">
                                    <?php esc_html_e('Nivel de Experiencia', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="orden-nivel-experiencia" name="nivel_experiencia" class="ga-form-input">
                                    <?php foreach ($niveles as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-ubicacion">
                                    <?php esc_html_e('Ubicación Requerida', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="orden-ubicacion" name="ubicacion_requerida" class="ga-form-input"
                                       placeholder="<?php esc_attr_e('Ciudad, País (si aplica)', 'gestionadmin-wolk'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="orden-habilidades">
                            <?php esc_html_e('Habilidades Requeridas', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="text" id="orden-habilidades" name="habilidades_requeridas" class="ga-form-input"
                               placeholder="<?php esc_attr_e('PHP, WordPress, JavaScript (separar por comas)', 'gestionadmin-wolk'); ?>">
                        <p class="description"><?php esc_html_e('Separa las habilidades con comas.', 'gestionadmin-wolk'); ?></p>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="orden-requisitos-adicionales">
                            <?php esc_html_e('Requisitos Adicionales', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="orden-requisitos-adicionales" name="requisitos_adicionales" class="ga-form-input" rows="3"
                                  placeholder="<?php esc_attr_e('Otros requisitos o notas importantes...', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Fechas y Tiempos', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-fecha-limite">
                                    <?php esc_html_e('Fecha Límite para Aplicar', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="date" id="orden-fecha-limite" name="fecha_limite_aplicacion" class="ga-form-input">
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-fecha-inicio">
                                    <?php esc_html_e('Fecha Inicio Estimada', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="date" id="orden-fecha-inicio" name="fecha_inicio_estimada" class="ga-form-input">
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-duracion">
                                    <?php esc_html_e('Duración Estimada (días)', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="orden-duracion" name="duracion_estimada_dias"
                                       class="ga-form-input" min="1" placeholder="30">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado (solo en edición) -->
                <div class="ga-form-section" id="ga-section-estado" style="display: none;">
                    <h3><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-estado">
                                    <?php esc_html_e('Estado Actual', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="orden-estado" name="estado" class="ga-form-input">
                                    <?php foreach ($estados as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ga-modal-footer">
                <button type="button" class="ga-btn ga-modal-close-btn">
                    <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                </button>
                <button type="submit" class="ga-btn ga-btn-primary" id="ga-btn-save-orden">
                    <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =============================================================================
     JAVASCRIPT
============================================================================== -->
<script>
jQuery(document).ready(function($) {

    // =========================================================================
    // VARIABLES Y CACHE DE DATOS
    // =========================================================================

    var ordenes = <?php echo wp_json_encode(array_map(function($o) {
        return array(
            'id'                      => $o->id,
            'codigo'                  => $o->codigo,
            'titulo'                  => $o->titulo,
            'descripcion'             => $o->descripcion,
            'categoria'               => $o->categoria,
            'tipo_pago'               => $o->tipo_pago,
            'presupuesto_min'         => $o->presupuesto_min,
            'presupuesto_max'         => $o->presupuesto_max,
            'modalidad'               => $o->modalidad,
            'ubicacion_requerida'     => $o->ubicacion_requerida,
            'nivel_experiencia'       => $o->nivel_experiencia,
            'habilidades_requeridas'  => $o->habilidades_requeridas,
            'requisitos_adicionales'  => $o->requisitos_adicionales,
            'fecha_limite_aplicacion' => $o->fecha_limite_aplicacion,
            'fecha_inicio_estimada'   => $o->fecha_inicio_estimada,
            'duracion_estimada_dias'  => $o->duracion_estimada_dias,
            'estado'                  => $o->estado,
            'prioridad'               => $o->prioridad,
            'cliente_id'              => $o->cliente_id,
        );
    }, $ordenes)); ?>;

    // =========================================================================
    // MODAL FUNCTIONS
    // =========================================================================

    function openModal() {
        $('#ga-modal-orden').fadeIn(200);
    }

    function closeModal() {
        $('#ga-modal-orden').fadeOut(200);
        resetForm();
    }

    function resetForm() {
        $('#ga-form-orden')[0].reset();
        $('#orden-id').val(0);
        $('#ga-modal-title-orden').text('<?php echo esc_js(__('Nueva Orden de Trabajo', 'gestionadmin-wolk')); ?>');
        $('#ga-section-estado').hide();
    }

    function loadOrden(id) {
        var o = ordenes.find(function(item) { return item.id == id; });
        if (!o) return;

        $('#orden-id').val(o.id);
        $('#orden-titulo').val(o.titulo);
        $('#orden-descripcion').val(o.descripcion);
        $('#orden-categoria').val(o.categoria);
        $('#orden-modalidad').val(o.modalidad);
        $('#orden-prioridad').val(o.prioridad);
        $('#orden-tipo-pago').val(o.tipo_pago);
        $('#orden-presupuesto-min').val(o.presupuesto_min);
        $('#orden-presupuesto-max').val(o.presupuesto_max);
        $('#orden-nivel-experiencia').val(o.nivel_experiencia);
        $('#orden-ubicacion').val(o.ubicacion_requerida);
        $('#orden-requisitos-adicionales').val(o.requisitos_adicionales);
        $('#orden-fecha-limite').val(o.fecha_limite_aplicacion ? o.fecha_limite_aplicacion.split(' ')[0] : '');
        $('#orden-fecha-inicio').val(o.fecha_inicio_estimada ? o.fecha_inicio_estimada.split(' ')[0] : '');
        $('#orden-duracion').val(o.duracion_estimada_dias);
        $('#orden-estado').val(o.estado);
        $('#orden-cliente').val(o.cliente_id);

        // Habilidades (JSON to comma-separated)
        if (o.habilidades_requeridas) {
            try {
                var habs = JSON.parse(o.habilidades_requeridas);
                $('#orden-habilidades').val(Array.isArray(habs) ? habs.join(', ') : o.habilidades_requeridas);
            } catch (e) {
                $('#orden-habilidades').val(o.habilidades_requeridas);
            }
        }

        $('#ga-modal-title-orden').text('<?php echo esc_js(__('Editar Orden:', 'gestionadmin-wolk')); ?> ' + o.codigo);
        $('#ga-section-estado').show();
    }

    // =========================================================================
    // EVENT HANDLERS
    // =========================================================================

    // Nueva orden
    $('#ga-btn-new-orden').on('click', function() {
        resetForm();
        openModal();
    });

    // Editar orden
    $(document).on('click', '.ga-btn-edit-orden', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        loadOrden(id);
        openModal();
    });

    // Cerrar modal
    $('.ga-modal-close, .ga-modal-close-btn').on('click', function() {
        closeModal();
    });

    // Click fuera del modal
    $('#ga-modal-orden').on('click', function(e) {
        if ($(e.target).hasClass('ga-modal')) {
            closeModal();
        }
    });

    // Guardar orden
    $('#ga-form-orden').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#ga-btn-save-orden');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        // Procesar habilidades a array
        var habilidades = $('#orden-habilidades').val();
        if (habilidades) {
            habilidades = habilidades.split(',').map(function(h) { return h.trim(); }).filter(function(h) { return h; });
        } else {
            habilidades = [];
        }

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_orden_trabajo',
            nonce: gaAdmin.nonce,
            id: $('#orden-id').val(),
            titulo: $('#orden-titulo').val(),
            descripcion: $('#orden-descripcion').val(),
            categoria: $('#orden-categoria').val(),
            modalidad: $('#orden-modalidad').val(),
            prioridad: $('#orden-prioridad').val(),
            tipo_pago: $('#orden-tipo-pago').val(),
            presupuesto_min: $('#orden-presupuesto-min').val(),
            presupuesto_max: $('#orden-presupuesto-max').val(),
            nivel_experiencia: $('#orden-nivel-experiencia').val(),
            ubicacion_requerida: $('#orden-ubicacion').val(),
            habilidades_requeridas: JSON.stringify(habilidades),
            requisitos_adicionales: $('#orden-requisitos-adicionales').val(),
            fecha_limite_aplicacion: $('#orden-fecha-limite').val(),
            fecha_inicio_estimada: $('#orden-fecha-inicio').val(),
            duracion_estimada_dias: $('#orden-duracion').val(),
            estado: $('#orden-estado').val(),
            cliente_id: $('#orden-cliente').val()
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php echo esc_js(__('Error al guardar', 'gestionadmin-wolk')); ?>');
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar', 'gestionadmin-wolk')); ?>');
            }
        }).fail(function() {
            alert('<?php echo esc_js(__('Error de conexión', 'gestionadmin-wolk')); ?>');
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar', 'gestionadmin-wolk')); ?>');
        });
    });

    // Publicar orden
    $(document).on('click', '.ga-btn-publish-orden', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('<?php echo esc_js(__('¿Publicar esta orden? Será visible en el Marketplace.', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_change_orden_estado',
            nonce: gaAdmin.nonce,
            id: id,
            estado: 'PUBLICADA'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Eliminar orden
    $(document).on('click', '.ga-btn-delete-orden', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('<?php echo esc_js(__('¿Eliminar esta orden de trabajo? Esta acción no se puede deshacer.', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_orden_trabajo',
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

    // =========================================================================
    // FILTROS
    // =========================================================================

    function applyFilters() {
        var buscar = $('#ga-filter-buscar').val().toLowerCase();
        var estado = $('#ga-filter-estado').val();
        var categoria = $('#ga-filter-categoria').val();
        var modalidad = $('#ga-filter-modalidad').val();

        $('#ga-ordenes-table tbody tr').each(function() {
            var $row = $(this);
            var show = true;

            if (buscar && $row.text().toLowerCase().indexOf(buscar) === -1) {
                show = false;
            }
            if (estado && $row.data('estado') !== estado) {
                show = false;
            }
            if (categoria && $row.data('categoria') !== categoria) {
                show = false;
            }
            if (modalidad && $row.data('modalidad') !== modalidad) {
                show = false;
            }

            $row.toggle(show);
        });
    }

    $('#ga-btn-filter').on('click', applyFilters);

    $('#ga-filter-buscar').on('keyup', function(e) {
        if (e.keyCode === 13) {
            applyFilters();
        }
    });

    $('#ga-btn-clear-filter').on('click', function() {
        $('#ga-filter-buscar').val('');
        $('#ga-filter-estado').val('');
        $('#ga-filter-categoria').val('');
        $('#ga-filter-modalidad').val('');
        $('#ga-ordenes-table tbody tr').show();
    });

});
</script>

<style>
/* Estilos adicionales para esta vista */
.ga-stat-card {
    text-align: center;
    padding: 15px;
}
.ga-stat-number {
    display: block;
    font-size: 32px;
    font-weight: bold;
    color: #2271b1;
}
.ga-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}
.ga-stat-success .ga-stat-number { color: #00a32a; }
.ga-stat-warning .ga-stat-number { color: #dba617; }
.ga-stat-info .ga-stat-number { color: #0073aa; }

.ga-form-section {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid #eee;
}
.ga-form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.ga-form-section h3 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
}

.ga-modal-large {
    max-width: 900px;
    width: 90%;
}

.ga-modal-body {
    max-height: 70vh;
    overflow-y: auto;
    padding: 20px;
}

.ga-actions {
    display: flex;
    gap: 8px;
}
.ga-actions a {
    color: #2271b1;
    text-decoration: none;
}
.ga-actions a:hover {
    color: #135e96;
}

.ga-btn-link {
    background: none;
    border: none;
    color: #2271b1;
    cursor: pointer;
    padding: 8px;
}
.ga-btn-link:hover {
    text-decoration: underline;
}
</style>
