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

// Cargar módulos necesarios para acuerdos económicos
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-empresas.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-catalogo-bonos.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-ordenes-acuerdos.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-ordenes-bonos.php';

// Obtener datos
$ordenes = GA_Ordenes_Trabajo::get_all();
$clientes = GA_Clientes::get_all(array('activo' => 1));
$estadisticas = GA_Ordenes_Trabajo::get_estadisticas();

// Datos para acuerdos económicos
$empresas = GA_Empresas::get_instance()->get_activas();
$bonos_catalogo = GA_Catalogo_Bonos::get_instance()->get_activos();
$tipos_acuerdo = GA_Ordenes_Acuerdos::$tipos_acuerdo;
$frecuencias_pago = GA_Ordenes_Acuerdos::$frecuencias_pago;

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

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="orden-empresa">
                                    <?php esc_html_e('Empresa Pagadora', 'gestionadmin-wolk'); ?> *
                                </label>
                                <select id="orden-empresa" name="empresa_id" class="ga-form-input" required>
                                    <option value=""><?php esc_html_e('— Seleccionar empresa —', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($empresas as $empresa) : ?>
                                        <option value="<?php echo esc_attr($empresa->id); ?>">
                                            <?php echo esc_html($empresa->nombre . ' (' . $empresa->pais_iso . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e('Empresa que realizará los pagos al aplicante contratado.', 'gestionadmin-wolk'); ?></p>
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

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="orden-url-manual">
                            <?php esc_html_e('Manual del Puesto (URL)', 'gestionadmin-wolk'); ?>
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <input type="url" id="orden-url-manual" name="url_manual" class="ga-form-input"
                                   placeholder="<?php esc_attr_e('https://drive.google.com/documento...', 'gestionadmin-wolk'); ?>"
                                   style="flex: 1;">
                            <button type="button" class="ga-btn ga-btn-secondary" id="btn-subir-manual">
                                <?php esc_html_e('Subir a Medios', 'gestionadmin-wolk'); ?>
                            </button>
                        </div>
                        <p class="description"><?php esc_html_e('Enlace al manual, documento de onboarding o instrucciones del puesto (Google Drive, OneDrive, WordPress, etc.)', 'gestionadmin-wolk'); ?></p>
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

                <!-- =========================================================================
                     ACUERDOS ECONÓMICOS
                ========================================================================== -->
                <div class="ga-form-section ga-acuerdos-section">
                    <h3>
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php esc_html_e('Acuerdos Económicos', 'gestionadmin-wolk'); ?>
                    </h3>
                    <p class="description" style="margin-bottom: 15px;">
                        <?php esc_html_e('Define cómo se compensará al aplicante contratado. Puedes agregar múltiples acuerdos.', 'gestionadmin-wolk'); ?>
                    </p>

                    <div id="ga-acuerdos-container">
                        <!-- Los acuerdos se agregan dinámicamente aquí -->
                    </div>

                    <div class="ga-acuerdos-actions">
                        <button type="button" class="ga-btn ga-btn-secondary" id="ga-btn-agregar-acuerdo">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php esc_html_e('Agregar Acuerdo', 'gestionadmin-wolk'); ?>
                        </button>

                        <div class="ga-acuerdos-quick-add">
                            <select id="ga-quick-add-tipo" class="ga-form-input">
                                <option value=""><?php esc_html_e('— Agregar rápido —', 'gestionadmin-wolk'); ?></option>
                                <optgroup label="<?php esc_attr_e('Pago Principal', 'gestionadmin-wolk'); ?>">
                                    <option value="HORA_REPORTADA"><?php esc_html_e('Por hora reportada', 'gestionadmin-wolk'); ?></option>
                                    <option value="HORA_APROBADA"><?php esc_html_e('Por hora aprobada', 'gestionadmin-wolk'); ?></option>
                                    <option value="TRABAJO_COMPLETADO"><?php esc_html_e('Por trabajo completado', 'gestionadmin-wolk'); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e('Comisiones', 'gestionadmin-wolk'); ?>">
                                    <option value="COMISION_FACTURA"><?php esc_html_e('Comisión por factura', 'gestionadmin-wolk'); ?></option>
                                    <option value="COMISION_HORAS_SUPERVISADAS"><?php esc_html_e('Comisión por horas supervisadas', 'gestionadmin-wolk'); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e('Metas', 'gestionadmin-wolk'); ?>">
                                    <option value="META_RENTABILIDAD"><?php esc_html_e('Meta de rentabilidad', 'gestionadmin-wolk'); ?></option>
                                </optgroup>
                            </select>
                            <?php if (!empty($bonos_catalogo)) : ?>
                                <select id="ga-quick-add-bono" class="ga-form-input">
                                    <option value=""><?php esc_html_e('— Agregar bono —', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($bonos_catalogo as $bono) : ?>
                                        <option value="<?php echo esc_attr($bono->id); ?>"
                                                data-valor="<?php echo esc_attr($bono->valor_default); ?>"
                                                data-tipo-valor="<?php echo esc_attr($bono->tipo_valor); ?>"
                                                data-frecuencia="<?php echo esc_attr($bono->frecuencia); ?>">
                                            <?php echo esc_html($bono->nombre); ?>
                                            (<?php echo $bono->tipo_valor === 'PORCENTAJE' ? esc_html($bono->valor_default . '%') : '$' . esc_html(number_format($bono->valor_default, 2)); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ga-acuerdos-resumen" id="ga-acuerdos-resumen" style="display:none;">
                        <strong><?php esc_html_e('Resumen:', 'gestionadmin-wolk'); ?></strong>
                        <span id="ga-resumen-texto"></span>
                    </div>
                </div>

                <!-- =========================================================================
                     BONOS DISPONIBLES
                ========================================================================== -->
                <div class="ga-form-section ga-bonos-section">
                    <h3>
                        <span class="dashicons dashicons-awards"></span>
                        <?php esc_html_e('Bonos Disponibles', 'gestionadmin-wolk'); ?>
                    </h3>
                    <p class="description" style="margin-bottom: 15px;">
                        <?php esc_html_e('Define los bonos que puede ganar el aplicante en esta orden. Estos se mostrarán en la publicación.', 'gestionadmin-wolk'); ?>
                    </p>

                    <div class="ga-bonos-actions" style="margin-bottom: 15px;">
                        <select id="ga-select-bono-catalogo" class="ga-form-input" style="min-width: 300px;">
                            <option value=""><?php esc_html_e('— Seleccionar bono del catálogo —', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($bonos_catalogo as $bono) : ?>
                                <option value="<?php echo esc_attr($bono->id); ?>"
                                        data-nombre="<?php echo esc_attr($bono->nombre); ?>"
                                        data-monto="<?php echo esc_attr($bono->valor_default); ?>"
                                        data-tipo="<?php echo esc_attr($bono->tipo_valor); ?>">
                                    <?php echo esc_html($bono->nombre); ?>
                                    (<?php echo $bono->tipo_valor === 'PORCENTAJE' ? esc_html($bono->valor_default . '%') : '$' . esc_html(number_format($bono->valor_default, 2)); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="ga-btn ga-btn-secondary" id="ga-btn-agregar-bono">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php esc_html_e('Agregar Bono', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>

                    <div id="ga-bonos-container">
                        <!-- Los bonos se agregan dinámicamente aquí -->
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
        // Obtener acuerdos de la orden
        $acuerdos_instance = GA_Ordenes_Acuerdos::get_instance();
        $acuerdos = $acuerdos_instance->get_por_orden($o->id, false);
        // Obtener bonos de la orden
        $bonos = GA_Ordenes_Bonos::get_para_js($o->id);
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
            'url_manual'              => $o->url_manual ?? '',
            'fecha_limite_aplicacion' => $o->fecha_limite_aplicacion,
            'fecha_inicio_estimada'   => $o->fecha_inicio_estimada,
            'duracion_estimada_dias'  => $o->duracion_estimada_dias,
            'estado'                  => $o->estado,
            'prioridad'               => $o->prioridad,
            'cliente_id'              => $o->cliente_id,
            'empresa_id'              => $o->empresa_id,
            'acuerdos'                => $acuerdos,
            'bonos'                   => $bonos,
        );
    }, $ordenes)); ?>;

    // Catálogo de bonos para el selector
    var bonosCatalogo = <?php echo wp_json_encode(array_map(function($b) {
        return array(
            'id'          => $b->id,
            'codigo'      => $b->codigo,
            'nombre'      => $b->nombre,
            'tipo_valor'  => $b->tipo_valor,
            'valor'       => $b->valor_default,
            'frecuencia'  => $b->frecuencia,
            'condicion'   => $b->condicion_descripcion,
            'icono'       => $b->icono,
        );
    }, $bonos_catalogo)); ?>;

    // Tipos de acuerdo y frecuencias
    var tiposAcuerdo = <?php echo wp_json_encode($tipos_acuerdo); ?>;
    var frecuenciasPago = <?php echo wp_json_encode($frecuencias_pago); ?>;

    // Contador para acuerdos y bonos
    var acuerdoCounter = 0;
    var bonoCounter = 0;

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
        // Limpiar acuerdos
        $('#ga-acuerdos-container').empty();
        acuerdoCounter = 0;
        actualizarResumenAcuerdos();
        // Limpiar bonos
        $('#ga-bonos-container').empty();
        bonoCounter = 0;
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
        $('#orden-url-manual').val(o.url_manual || '');
        $('#orden-fecha-limite').val(o.fecha_limite_aplicacion ? o.fecha_limite_aplicacion.split(' ')[0] : '');
        $('#orden-fecha-inicio').val(o.fecha_inicio_estimada ? o.fecha_inicio_estimada.split(' ')[0] : '');
        $('#orden-duracion').val(o.duracion_estimada_dias);
        $('#orden-estado').val(o.estado);
        $('#orden-cliente').val(o.cliente_id);
        $('#orden-empresa').val(o.empresa_id);

        // Habilidades (JSON to comma-separated)
        if (o.habilidades_requeridas) {
            try {
                var habs = JSON.parse(o.habilidades_requeridas);
                $('#orden-habilidades').val(Array.isArray(habs) ? habs.join(', ') : o.habilidades_requeridas);
            } catch (e) {
                $('#orden-habilidades').val(o.habilidades_requeridas);
            }
        }

        // Cargar acuerdos económicos
        $('#ga-acuerdos-container').empty();
        acuerdoCounter = 0;
        if (o.acuerdos && o.acuerdos.length > 0) {
            o.acuerdos.forEach(function(acuerdo) {
                agregarAcuerdo(acuerdo);
            });
        }
        actualizarResumenAcuerdos();

        // Cargar bonos
        $('#ga-bonos-container').empty();
        bonoCounter = 0;
        if (o.bonos && o.bonos.length > 0) {
            o.bonos.forEach(function(bono) {
                agregarBono(bono);
            });
        }

        $('#ga-modal-title-orden').text('<?php echo esc_js(__('Editar Orden:', 'gestionadmin-wolk')); ?> ' + o.codigo);
        $('#ga-section-estado').show();
    }

    // =========================================================================
    // FUNCIONES DE ACUERDOS ECONÓMICOS
    // =========================================================================

    function agregarAcuerdo(data) {
        data = data || {};
        var idx = acuerdoCounter++;
        var id = data.id || '';
        var tipo = data.tipo_acuerdo || 'HORA_APROBADA';
        var valor = data.valor || '';
        var esPorcentaje = data.es_porcentaje || 0;
        var bonoId = data.bono_id || '';
        var descripcion = data.descripcion || '';
        var frecuencia = data.frecuencia_pago || 'MENSUAL';
        var activo = data.activo !== undefined ? data.activo : 1;

        var tipoLabel = tiposAcuerdo[tipo] || tipo;
        var esComision = ['COMISION_FACTURA', 'COMISION_HORAS_SUPERVISADAS', 'META_RENTABILIDAD'].indexOf(tipo) > -1;

        // Para bonos, obtener nombre del catálogo
        if (tipo === 'BONO' && bonoId) {
            var bono = bonosCatalogo.find(function(b) { return b.id == bonoId; });
            if (bono) {
                tipoLabel = bono.nombre;
            }
        }

        var html = '<div class="ga-acuerdo-item" data-idx="' + idx + '">' +
            '<input type="hidden" name="acuerdos[' + idx + '][id]" value="' + id + '">' +
            '<input type="hidden" name="acuerdos[' + idx + '][tipo_acuerdo]" value="' + tipo + '">' +
            '<input type="hidden" name="acuerdos[' + idx + '][bono_id]" value="' + bonoId + '">' +
            '<input type="hidden" name="acuerdos[' + idx + '][es_porcentaje]" value="' + (esComision ? 1 : esPorcentaje) + '">' +
            '<input type="hidden" name="acuerdos[' + idx + '][activo]" value="' + activo + '">' +

            '<div class="ga-acuerdo-header">' +
                '<span class="ga-acuerdo-tipo">' + tipoLabel + '</span>' +
                '<button type="button" class="ga-btn-remove-acuerdo" data-idx="' + idx + '" title="<?php echo esc_js(__('Eliminar', 'gestionadmin-wolk')); ?>">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                '</button>' +
            '</div>' +

            '<div class="ga-acuerdo-body">' +
                '<div class="ga-acuerdo-field">' +
                    '<label><?php echo esc_js(__('Valor', 'gestionadmin-wolk')); ?></label>' +
                    '<div class="ga-input-group">' +
                        '<span class="ga-input-prefix">' + (esComision ? '' : '$') + '</span>' +
                        '<input type="number" name="acuerdos[' + idx + '][valor]" value="' + valor + '" step="0.01" min="0" class="ga-form-input ga-acuerdo-valor">' +
                        '<span class="ga-input-suffix">' + (esComision ? '%' : '') + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="ga-acuerdo-field">' +
                    '<label><?php echo esc_js(__('Frecuencia', 'gestionadmin-wolk')); ?></label>' +
                    '<select name="acuerdos[' + idx + '][frecuencia_pago]" class="ga-form-input">' +
                        Object.keys(frecuenciasPago).map(function(key) {
                            return '<option value="' + key + '"' + (frecuencia === key ? ' selected' : '') + '>' + frecuenciasPago[key] + '</option>';
                        }).join('') +
                    '</select>' +
                '</div>' +
                '<div class="ga-acuerdo-field ga-acuerdo-field-wide">' +
                    '<label><?php echo esc_js(__('Descripción', 'gestionadmin-wolk')); ?></label>' +
                    '<input type="text" name="acuerdos[' + idx + '][descripcion]" value="' + (descripcion || '').replace(/"/g, '&quot;') + '" class="ga-form-input" placeholder="<?php echo esc_js(__('Detalles adicionales...', 'gestionadmin-wolk')); ?>">' +
                '</div>' +
            '</div>' +
        '</div>';

        $('#ga-acuerdos-container').append(html);
        actualizarResumenAcuerdos();
    }

    function actualizarResumenAcuerdos() {
        var $items = $('#ga-acuerdos-container .ga-acuerdo-item');
        var $resumen = $('#ga-acuerdos-resumen');

        if ($items.length === 0) {
            $resumen.hide();
            return;
        }

        var partes = [];
        $items.each(function() {
            var tipo = $(this).find('input[name*="[tipo_acuerdo]"]').val();
            var valor = $(this).find('input[name*="[valor]"]').val() || 0;
            var esPorcentaje = $(this).find('input[name*="[es_porcentaje]"]').val() == 1;

            var valorStr = esPorcentaje ? valor + '%' : '$' + parseFloat(valor).toFixed(2);
            var tipoStr = tiposAcuerdo[tipo] || tipo;

            if (tipo === 'HORA_REPORTADA' || tipo === 'HORA_APROBADA') {
                partes.push(valorStr + '/hora');
            } else if (tipo === 'TRABAJO_COMPLETADO') {
                partes.push(valorStr + ' al completar');
            } else if (tipo === 'COMISION_FACTURA') {
                partes.push(valor + '% comisión');
            } else if (tipo === 'BONO') {
                partes.push('Bono: ' + valorStr);
            }
        });

        if (partes.length > 0) {
            $('#ga-resumen-texto').text(partes.slice(0, 3).join(' + ') + (partes.length > 3 ? '...' : ''));
            $resumen.show();
        } else {
            $resumen.hide();
        }
    }

    // =========================================================================
    // FUNCIONES DE BONOS DISPONIBLES
    // =========================================================================

    /**
     * Agregar un bono a la lista de bonos disponibles
     */
    function agregarBono(data) {
        data = data || {};
        var idx = bonoCounter++;
        var bonoId = data.bono_id || '';
        var nombre = data.nombre || '';
        var monto = data.monto_personalizado || data.monto_efectivo || data.monto_catalogo || '';
        var detalle = data.detalle || '';

        // Si no hay nombre, buscar en catálogo
        if (!nombre && bonoId) {
            var bono = bonosCatalogo.find(function(b) { return b.id == bonoId; });
            if (bono) {
                nombre = bono.nombre;
                if (!monto) monto = bono.valor;
            }
        }

        var html = '<div class="ga-bono-item" data-idx="' + idx + '">' +
            '<input type="hidden" name="bonos[' + idx + '][bono_id]" value="' + bonoId + '">' +
            '<div class="ga-bono-header">' +
                '<span class="dashicons dashicons-awards" style="color: #2271b1;"></span>' +
                '<strong>' + nombre + '</strong>' +
                '<span class="ga-bono-monto">$' + parseFloat(monto || 0).toFixed(2) + '</span>' +
                '<button type="button" class="ga-btn-remove-bono" data-idx="' + idx + '" title="<?php echo esc_js(__('Quitar', 'gestionadmin-wolk')); ?>">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                '</button>' +
            '</div>' +
            '<div class="ga-bono-body">' +
                '<div class="ga-bono-field">' +
                    '<label><?php echo esc_js(__('Monto', 'gestionadmin-wolk')); ?></label>' +
                    '<div class="ga-input-group">' +
                        '<span class="ga-input-prefix">$</span>' +
                        '<input type="number" name="bonos[' + idx + '][monto]" value="' + monto + '" step="0.01" min="0" class="ga-form-input ga-bono-monto-input" placeholder="<?php echo esc_js(__('Vacío = catálogo', 'gestionadmin-wolk')); ?>">' +
                    '</div>' +
                '</div>' +
                '<div class="ga-bono-field ga-bono-field-wide">' +
                    '<label><?php echo esc_js(__('Detalle', 'gestionadmin-wolk')); ?></label>' +
                    '<input type="text" name="bonos[' + idx + '][detalle]" value="' + (detalle || '').replace(/"/g, '&quot;') + '" class="ga-form-input" placeholder="<?php echo esc_js(__('Condiciones o detalles para el aplicante...', 'gestionadmin-wolk')); ?>">' +
                '</div>' +
            '</div>' +
        '</div>';

        $('#ga-bonos-container').append(html);
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

    // Agregar acuerdo nuevo
    $('#ga-btn-agregar-acuerdo').on('click', function() {
        agregarAcuerdo({ tipo_acuerdo: 'HORA_APROBADA', valor: '', frecuencia_pago: 'MENSUAL' });
    });

    // Agregar acuerdo rápido por tipo
    $('#ga-quick-add-tipo').on('change', function() {
        var tipo = $(this).val();
        if (tipo) {
            var defaults = {
                'HORA_REPORTADA': { valor: 15 },
                'HORA_APROBADA': { valor: 15 },
                'TRABAJO_COMPLETADO': { valor: 100, frecuencia_pago: 'AL_FINALIZAR' },
                'COMISION_FACTURA': { valor: 5 },
                'COMISION_HORAS_SUPERVISADAS': { valor: 3 },
                'META_RENTABILIDAD': { valor: 10 },
            };
            var data = defaults[tipo] || {};
            data.tipo_acuerdo = tipo;
            agregarAcuerdo(data);
            $(this).val('');
        }
    });

    // Agregar bono del catálogo
    $('#ga-quick-add-bono').on('change', function() {
        var $option = $(this).find('option:selected');
        var bonoId = $option.val();
        if (bonoId) {
            agregarAcuerdo({
                tipo_acuerdo: 'BONO',
                bono_id: bonoId,
                valor: $option.data('valor') || 0,
                es_porcentaje: $option.data('tipo-valor') === 'PORCENTAJE' ? 1 : 0,
                frecuencia_pago: $option.data('frecuencia') || 'MENSUAL'
            });
            $(this).val('');
        }
    });

    // Eliminar acuerdo
    $(document).on('click', '.ga-btn-remove-acuerdo', function() {
        $(this).closest('.ga-acuerdo-item').remove();
        actualizarResumenAcuerdos();
    });

    // Actualizar resumen cuando cambia un valor
    $(document).on('change keyup', '.ga-acuerdo-valor', function() {
        actualizarResumenAcuerdos();
    });

    // =========================================================================
    // HANDLERS BONOS DISPONIBLES
    // =========================================================================

    // Agregar bono del catálogo (sección Bonos Disponibles)
    $('#ga-btn-agregar-bono').on('click', function() {
        var $select = $('#ga-select-bono-catalogo');
        var bonoId = $select.val();
        if (!bonoId) {
            alert('<?php echo esc_js(__('Selecciona un bono del catálogo', 'gestionadmin-wolk')); ?>');
            return;
        }

        // Verificar si ya está agregado
        var yaExiste = false;
        $('#ga-bonos-container .ga-bono-item').each(function() {
            if ($(this).find('input[name*="[bono_id]"]').val() == bonoId) {
                yaExiste = true;
                return false;
            }
        });

        if (yaExiste) {
            alert('<?php echo esc_js(__('Este bono ya está agregado', 'gestionadmin-wolk')); ?>');
            return;
        }

        var $option = $select.find('option:selected');
        agregarBono({
            bono_id: bonoId,
            nombre: $option.data('nombre'),
            monto_catalogo: $option.data('monto')
        });
        $select.val('');
    });

    // Eliminar bono
    $(document).on('click', '.ga-btn-remove-bono', function() {
        $(this).closest('.ga-bono-item').remove();
    });

    // =========================================================================
    // MEDIA UPLOADER PARA MANUAL
    // =========================================================================

    var mediaUploader;
    $('#btn-subir-manual').on('click', function(e) {
        e.preventDefault();

        // Si ya existe, abrirlo
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Crear nuevo media uploader
        mediaUploader = wp.media({
            title: '<?php echo esc_js(__('Seleccionar Manual del Puesto', 'gestionadmin-wolk')); ?>',
            button: {
                text: '<?php echo esc_js(__('Usar este archivo', 'gestionadmin-wolk')); ?>'
            },
            multiple: false
        });

        // Cuando se seleccione un archivo
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#orden-url-manual').val(attachment.url);
        });

        mediaUploader.open();
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

        // Recopilar acuerdos
        var acuerdos = [];
        $('#ga-acuerdos-container .ga-acuerdo-item').each(function() {
            var $item = $(this);
            var idx = $item.data('idx');
            acuerdos.push({
                id: $item.find('input[name="acuerdos[' + idx + '][id]"]').val(),
                tipo_acuerdo: $item.find('input[name="acuerdos[' + idx + '][tipo_acuerdo]"]').val(),
                bono_id: $item.find('input[name="acuerdos[' + idx + '][bono_id]"]').val(),
                valor: $item.find('input[name="acuerdos[' + idx + '][valor]"]').val(),
                es_porcentaje: $item.find('input[name="acuerdos[' + idx + '][es_porcentaje]"]').val(),
                frecuencia_pago: $item.find('select[name="acuerdos[' + idx + '][frecuencia_pago]"]').val(),
                descripcion: $item.find('input[name="acuerdos[' + idx + '][descripcion]"]').val(),
                activo: $item.find('input[name="acuerdos[' + idx + '][activo]"]').val()
            });
        });

        // Recopilar bonos disponibles
        var bonos = [];
        $('#ga-bonos-container .ga-bono-item').each(function() {
            var $item = $(this);
            var idx = $item.data('idx');
            bonos.push({
                bono_id: $item.find('input[name="bonos[' + idx + '][bono_id]"]').val(),
                monto: $item.find('input[name="bonos[' + idx + '][monto]"]').val(),
                detalle: $item.find('input[name="bonos[' + idx + '][detalle]"]').val()
            });
        });

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
            url_manual: $('#orden-url-manual').val(),
            fecha_limite_aplicacion: $('#orden-fecha-limite').val(),
            fecha_inicio_estimada: $('#orden-fecha-inicio').val(),
            duracion_estimada_dias: $('#orden-duracion').val(),
            estado: $('#orden-estado').val(),
            cliente_id: $('#orden-cliente').val(),
            empresa_id: $('#orden-empresa').val(),
            acuerdos: JSON.stringify(acuerdos),
            bonos: JSON.stringify(bonos)
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

/* Estilos para sección de Acuerdos Económicos */
.ga-acuerdos-section h3 {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ga-acuerdos-section h3 .dashicons {
    color: #2271b1;
}

#ga-acuerdos-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 15px;
}

.ga-acuerdo-item {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 12px;
}

.ga-acuerdo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}

.ga-acuerdo-tipo {
    font-weight: 600;
    color: #1d2327;
    font-size: 13px;
}

.ga-btn-remove-acuerdo {
    background: none;
    border: none;
    color: #a00;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 3px;
}
.ga-btn-remove-acuerdo:hover {
    background: #fee;
    color: #d00;
}

.ga-acuerdo-body {
    display: grid;
    grid-template-columns: 140px 160px 1fr;
    gap: 10px;
    align-items: end;
}

.ga-acuerdo-field label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-bottom: 4px;
}

.ga-acuerdo-field-wide {
    grid-column: span 1;
}

.ga-input-group {
    display: flex;
    align-items: center;
}

.ga-input-prefix,
.ga-input-suffix {
    background: #e0e0e0;
    padding: 6px 8px;
    font-size: 12px;
    color: #666;
    border: 1px solid #ddd;
}

.ga-input-prefix {
    border-right: none;
    border-radius: 4px 0 0 4px;
}

.ga-input-suffix {
    border-left: none;
    border-radius: 0 4px 4px 0;
}

.ga-input-group .ga-form-input {
    border-radius: 0;
    flex: 1;
}

.ga-acuerdos-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.ga-acuerdos-quick-add {
    display: flex;
    gap: 8px;
}

.ga-acuerdos-quick-add select {
    min-width: 180px;
}

.ga-acuerdos-resumen {
    margin-top: 15px;
    padding: 10px 15px;
    background: #e7f5e7;
    border: 1px solid #c3e6c3;
    border-radius: 4px;
    color: #2e7d32;
    font-size: 13px;
}

.ga-acuerdos-resumen strong {
    margin-right: 8px;
}

.ga-btn-secondary {
    background: #f0f0f0;
    border: 1px solid #ddd;
    color: #1d2327;
}
.ga-btn-secondary:hover {
    background: #e5e5e5;
}
.ga-btn-secondary .dashicons {
    vertical-align: middle;
    margin-right: 4px;
}

/* Estilos para sección de Bonos Disponibles */
.ga-bonos-section h3 {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ga-bonos-section h3 .dashicons {
    color: #2271b1;
}

.ga-bonos-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

#ga-bonos-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ga-bono-item {
    background: #e7f5ff;
    border: 1px solid #b8daff;
    border-radius: 6px;
    padding: 12px;
}

.ga-bono-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #c5dff5;
}

.ga-bono-header strong {
    flex: 1;
    color: #1d2327;
    font-size: 13px;
}

.ga-bono-monto {
    background: #2271b1;
    color: #fff;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 12px;
}

.ga-btn-remove-bono {
    background: none;
    border: none;
    color: #a00;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 3px;
}
.ga-btn-remove-bono:hover {
    background: #fee;
    color: #d00;
}

.ga-bono-body {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 10px;
    align-items: end;
}

.ga-bono-field label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-bottom: 4px;
}

.ga-bono-field-wide {
    grid-column: span 1;
}
</style>
