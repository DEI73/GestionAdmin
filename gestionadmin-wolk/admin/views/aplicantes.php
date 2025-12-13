<?php
/**
 * Vista: Gestión de Aplicantes (Freelancers/Empresas)
 *
 * Administración de aplicantes del Marketplace.
 * Permite verificar, gestionar y revisar aplicantes.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener parámetros de URL
$orden_id_filter = isset($_GET['orden_id']) ? absint($_GET['orden_id']) : 0;

// Obtener datos
$aplicantes = GA_Aplicantes::get_all();
$estadisticas = GA_Aplicantes::get_estadisticas();

// Si hay filtro por orden, obtener aplicaciones de esa orden
$aplicaciones_orden = array();
$orden_info = null;
if ($orden_id_filter) {
    $orden_info = GA_Ordenes_Trabajo::get($orden_id_filter);
    $aplicaciones_orden = GA_Aplicaciones::get_by_orden($orden_id_filter);
}

// Enums
$estados = GA_Aplicantes::get_estados();
$tipos = GA_Aplicantes::get_tipos();
$metodos_pago = GA_Aplicantes::get_metodos_pago();
$niveles = GA_Aplicantes::get_niveles();
$estados_aplicacion = GA_Aplicaciones::get_estados();

// Obtener países para el filtro
global $wpdb;
$paises = $wpdb->get_results("SELECT codigo_iso, nombre FROM {$wpdb->prefix}ga_paises_config WHERE activo = 1 ORDER BY nombre");
?>
<div class="wrap ga-admin">
    <?php if ($orden_id_filter && $orden_info) : ?>
        <!-- Vista: Aplicaciones para una orden específica -->
        <h1>
            <?php esc_html_e('Aplicaciones para:', 'gestionadmin-wolk'); ?>
            <?php echo esc_html($orden_info->codigo . ' - ' . $orden_info->titulo); ?>
        </h1>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-ordenes')); ?>">
                &larr; <?php esc_html_e('Volver a Órdenes de Trabajo', 'gestionadmin-wolk'); ?>
            </a>
        </p>

        <!-- Tabla de aplicaciones -->
        <div class="ga-card" style="margin-top: 20px;">
            <table class="ga-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Aplicante', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Propuesta', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Disponibilidad', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Rating', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aplicaciones_orden)) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <?php esc_html_e('No hay aplicaciones para esta orden.', 'gestionadmin-wolk'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($aplicaciones_orden as $app) : ?>
                            <tr data-id="<?php echo esc_attr($app->id); ?>">
                                <td>
                                    <strong><?php echo esc_html($app->aplicante_nombre); ?></strong>
                                    <br><small><?php echo esc_html($app->aplicante_email); ?></small>
                                </td>
                                <td>
                                    <span class="ga-badge ga-badge-secondary">
                                        <?php echo esc_html($tipos[$app->aplicante_tipo] ?? $app->aplicante_tipo); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($app->propuesta_monto) : ?>
                                        <strong>$<?php echo esc_html(number_format($app->propuesta_monto, 2)); ?></strong>
                                        <?php if ($app->propuesta_tiempo) : ?>
                                            <br><small><?php echo esc_html($app->propuesta_tiempo); ?></small>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($app->disponibilidad ?: '-'); ?></td>
                                <td>
                                    <?php if ($app->aplicante_rating) : ?>
                                        <span class="ga-rating">
                                            <?php echo esc_html(number_format($app->aplicante_rating, 1)); ?> ★
                                        </span>
                                    <?php else : ?>
                                        <span class="ga-badge ga-badge-secondary"><?php esc_html_e('Nuevo', 'gestionadmin-wolk'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="ga-badge <?php echo esc_attr(GA_Aplicaciones::get_estado_class($app->estado)); ?>">
                                        <?php echo esc_html($estados_aplicacion[$app->estado] ?? $app->estado); ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($app->created_at))); ?></small>
                                </td>
                                <td>
                                    <div class="ga-actions">
                                        <a href="#" class="ga-btn-view-aplicacion" data-id="<?php echo esc_attr($app->id); ?>"
                                           title="<?php esc_attr_e('Ver detalles', 'gestionadmin-wolk'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </a>
                                        <?php if (!in_array($app->estado, array('CONTRATADO', 'RECHAZADA', 'RETIRADA'))) : ?>
                                            <a href="#" class="ga-btn-change-estado-app" data-id="<?php echo esc_attr($app->id); ?>"
                                               title="<?php esc_attr_e('Cambiar estado', 'gestionadmin-wolk'); ?>">
                                                <span class="dashicons dashicons-update"></span>
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

    <?php else : ?>
        <!-- Vista: Listado general de aplicantes -->
        <h1>
            <?php esc_html_e('Aplicantes', 'gestionadmin-wolk'); ?>
            <button type="button" class="page-title-action" id="ga-btn-new-aplicante">
                <?php esc_html_e('Añadir Nuevo', 'gestionadmin-wolk'); ?>
            </button>
        </h1>

        <p class="description">
            <?php esc_html_e('Freelancers y empresas registradas para postularse a órdenes de trabajo.', 'gestionadmin-wolk'); ?>
        </p>

        <!-- Estadísticas -->
        <div class="ga-row" style="margin-top: 20px;">
            <div class="ga-col ga-col-3">
                <div class="ga-card ga-stat-card">
                    <span class="ga-stat-number"><?php echo esc_html($estadisticas['total']); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Total Aplicantes', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
            <div class="ga-col ga-col-3">
                <div class="ga-card ga-stat-card ga-stat-success">
                    <span class="ga-stat-number"><?php echo esc_html($estadisticas['verificados']); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Verificados', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
            <div class="ga-col ga-col-3">
                <div class="ga-card ga-stat-card ga-stat-warning">
                    <span class="ga-stat-number">
                        <?php echo isset($estadisticas['por_estado']['PENDIENTE_VERIFICACION']) ? esc_html($estadisticas['por_estado']['PENDIENTE_VERIFICACION']->total) : '0'; ?>
                    </span>
                    <span class="ga-stat-label"><?php esc_html_e('Pendientes', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
            <div class="ga-col ga-col-3">
                <div class="ga-card ga-stat-card">
                    <span class="ga-stat-number">
                        <?php echo isset($estadisticas['por_tipo']['EMPRESA']) ? esc_html($estadisticas['por_tipo']['EMPRESA']->total) : '0'; ?>
                    </span>
                    <span class="ga-stat-label"><?php esc_html_e('Empresas', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="ga-card" style="margin-top: 20px;">
            <div class="ga-row">
                <div class="ga-col ga-col-3">
                    <label class="ga-form-label"><?php esc_html_e('Buscar', 'gestionadmin-wolk'); ?></label>
                    <input type="text" id="ga-filter-buscar" class="ga-form-input"
                           placeholder="<?php esc_attr_e('Nombre, email, documento...', 'gestionadmin-wolk'); ?>">
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
                    <label class="ga-form-label"><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?></label>
                    <select id="ga-filter-tipo" class="ga-form-input">
                        <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($tipos as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ga-col ga-col-2">
                    <label class="ga-form-label"><?php esc_html_e('País', 'gestionadmin-wolk'); ?></label>
                    <select id="ga-filter-pais" class="ga-form-input">
                        <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($paises as $p) : ?>
                            <option value="<?php echo esc_attr($p->codigo_iso); ?>"><?php echo esc_html($p->nombre); ?></option>
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

        <!-- Tabla de aplicantes -->
        <div class="ga-card" style="margin-top: 20px;">
            <table class="ga-table" id="ga-aplicantes-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('País', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Habilidades', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Tarifa/Hora', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Rating', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Trabajos', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aplicantes)) : ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">
                                <?php esc_html_e('No hay aplicantes registrados.', 'gestionadmin-wolk'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($aplicantes as $ap) :
                            // Formatear habilidades
                            $habilidades_display = '';
                            if ($ap->habilidades) {
                                $habs = json_decode($ap->habilidades, true);
                                if (is_array($habs)) {
                                    $habilidades_display = implode(', ', array_slice($habs, 0, 3));
                                    if (count($habs) > 3) {
                                        $habilidades_display .= ' +' . (count($habs) - 3);
                                    }
                                }
                            }

                            // Formatear tarifa
                            $tarifa = '';
                            if ($ap->tarifa_hora_min && $ap->tarifa_hora_max) {
                                $tarifa = '$' . $ap->tarifa_hora_min . '-$' . $ap->tarifa_hora_max;
                            } elseif ($ap->tarifa_hora_min) {
                                $tarifa = 'Desde $' . $ap->tarifa_hora_min;
                            }
                        ?>
                            <tr data-id="<?php echo esc_attr($ap->id); ?>"
                                data-estado="<?php echo esc_attr($ap->estado); ?>"
                                data-tipo="<?php echo esc_attr($ap->tipo); ?>"
                                data-pais="<?php echo esc_attr($ap->pais); ?>">
                                <td>
                                    <strong>
                                        <a href="#" class="ga-btn-edit-aplicante" data-id="<?php echo esc_attr($ap->id); ?>">
                                            <?php echo esc_html($ap->nombre_completo); ?>
                                        </a>
                                    </strong>
                                    <br><small><?php echo esc_html($ap->email); ?></small>
                                    <?php if ($ap->disponible_inmediato) : ?>
                                        <br><span class="ga-badge ga-badge-success" style="font-size: 10px;">
                                            <?php esc_html_e('Disponible', 'gestionadmin-wolk'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="ga-badge ga-badge-secondary">
                                        <?php echo esc_html($tipos[$ap->tipo] ?? $ap->tipo); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($ap->pais ?: '-'); ?></td>
                                <td>
                                    <small><?php echo esc_html($habilidades_display ?: '-'); ?></small>
                                </td>
                                <td><?php echo esc_html($tarifa ?: '-'); ?></td>
                                <td>
                                    <?php if ($ap->calificacion_promedio) : ?>
                                        <span class="ga-rating"><?php echo esc_html(number_format($ap->calificacion_promedio, 1)); ?> ★</span>
                                    <?php else : ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo esc_html($ap->trabajos_completados ?: 0); ?>
                                </td>
                                <td>
                                    <span class="ga-badge <?php echo esc_attr(GA_Aplicantes::get_estado_class($ap->estado)); ?>">
                                        <?php echo esc_html($estados[$ap->estado] ?? $ap->estado); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="ga-actions">
                                        <a href="#" class="ga-btn-edit-aplicante" data-id="<?php echo esc_attr($ap->id); ?>"
                                           title="<?php esc_attr_e('Editar', 'gestionadmin-wolk'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <?php if ($ap->estado === 'PENDIENTE_VERIFICACION') : ?>
                                            <a href="#" class="ga-btn-verify-aplicante" data-id="<?php echo esc_attr($ap->id); ?>"
                                               title="<?php esc_attr_e('Verificar', 'gestionadmin-wolk'); ?>">
                                                <span class="dashicons dashicons-yes-alt"></span>
                                            </a>
                                        <?php endif; ?>
                                        <a href="#" class="ga-btn-delete-aplicante" data-id="<?php echo esc_attr($ap->id); ?>"
                                           title="<?php esc_attr_e('Eliminar', 'gestionadmin-wolk'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- =============================================================================
     MODAL: FORMULARIO DE APLICANTE
============================================================================== -->
<div id="ga-modal-aplicante" class="ga-modal" style="display: none;">
    <div class="ga-modal-content ga-modal-large">
        <div class="ga-modal-header">
            <h2 id="ga-modal-title-aplicante"><?php esc_html_e('Nuevo Aplicante', 'gestionadmin-wolk'); ?></h2>
            <button type="button" class="ga-modal-close">&times;</button>
        </div>

        <form id="ga-form-aplicante">
            <input type="hidden" name="id" id="aplicante-id" value="0">

            <div class="ga-modal-body">
                <!-- Datos personales -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Datos Personales', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-tipo">
                                    <?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="aplicante-tipo" name="tipo" class="ga-form-input">
                                    <?php foreach ($tipos as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-8">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-nombre">
                                    <?php esc_html_e('Nombre Completo', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="text" id="aplicante-nombre" name="nombre_completo" class="ga-form-input" required>
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-email">
                                    <?php esc_html_e('Email', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="email" id="aplicante-email" name="email" class="ga-form-input" required>
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-telefono">
                                    <?php esc_html_e('Teléfono', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="aplicante-telefono" name="telefono" class="ga-form-input">
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-pais">
                                    <?php esc_html_e('País', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="aplicante-pais" name="pais" class="ga-form-input">
                                    <option value=""><?php esc_html_e('Seleccionar...', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($paises as $p) : ?>
                                        <option value="<?php echo esc_attr($p->codigo_iso); ?>"><?php echo esc_html($p->nombre); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-ciudad">
                                    <?php esc_html_e('Ciudad', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="aplicante-ciudad" name="ciudad" class="ga-form-input">
                            </div>
                        </div>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-doc-tipo">
                                    <?php esc_html_e('Tipo Documento', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="aplicante-doc-tipo" name="documento_tipo" class="ga-form-input"
                                       placeholder="<?php esc_attr_e('CC, NIT, DNI...', 'gestionadmin-wolk'); ?>">
                            </div>
                        </div>
                        <div class="ga-col ga-col-8">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-doc-numero">
                                    <?php esc_html_e('Número Documento', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="aplicante-doc-numero" name="documento_numero" class="ga-form-input">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Perfil profesional -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Perfil Profesional', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="aplicante-titulo">
                            <?php esc_html_e('Título Profesional', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="text" id="aplicante-titulo" name="titulo_profesional" class="ga-form-input"
                               placeholder="<?php esc_attr_e('Ej: Desarrollador Full Stack, Diseñador UX...', 'gestionadmin-wolk'); ?>">
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="aplicante-bio">
                            <?php esc_html_e('Biografía / Descripción', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="aplicante-bio" name="bio" class="ga-form-input" rows="4"
                                  placeholder="<?php esc_attr_e('Describe tu experiencia, proyectos destacados...', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-nivel">
                                    <?php esc_html_e('Nivel de Experiencia', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="aplicante-nivel" name="nivel_experiencia" class="ga-form-input">
                                    <?php foreach ($niveles as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-anos">
                                    <?php esc_html_e('Años de Experiencia', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="aplicante-anos" name="anos_experiencia" class="ga-form-input" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="aplicante-habilidades">
                            <?php esc_html_e('Habilidades', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="text" id="aplicante-habilidades" name="habilidades" class="ga-form-input"
                               placeholder="<?php esc_attr_e('PHP, WordPress, JavaScript (separar por comas)', 'gestionadmin-wolk'); ?>">
                    </div>
                </div>

                <!-- Tarifas y disponibilidad -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Tarifas y Disponibilidad', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-tarifa-min">
                                    <?php esc_html_e('Tarifa Mínima/Hora (USD)', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="aplicante-tarifa-min" name="tarifa_hora_min"
                                       class="ga-form-input" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-tarifa-max">
                                    <?php esc_html_e('Tarifa Máxima/Hora (USD)', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="aplicante-tarifa-max" name="tarifa_hora_max"
                                       class="ga-form-input" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="ga-col ga-col-4">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-horas">
                                    <?php esc_html_e('Horas Semanales Disponibles', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="number" id="aplicante-horas" name="disponibilidad_horas"
                                       class="ga-form-input" min="1" max="168" value="40">
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label">
                            <input type="checkbox" id="aplicante-disponible" name="disponible_inmediato" value="1">
                            <?php esc_html_e('Disponible para empezar inmediatamente', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>
                </div>

                <!-- Enlaces y portafolio -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Enlaces y Portafolio', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-portfolio">
                                    <?php esc_html_e('URL Portafolio', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="url" id="aplicante-portfolio" name="portfolio_url" class="ga-form-input"
                                       placeholder="https://...">
                            </div>
                        </div>
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-linkedin">
                                    <?php esc_html_e('LinkedIn', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="url" id="aplicante-linkedin" name="linkedin_url" class="ga-form-input"
                                       placeholder="https://linkedin.com/in/...">
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="aplicante-github">
                            <?php esc_html_e('GitHub / GitLab', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="url" id="aplicante-github" name="github_url" class="ga-form-input"
                               placeholder="https://github.com/...">
                    </div>
                </div>

                <!-- Método de pago -->
                <div class="ga-form-section">
                    <h3><?php esc_html_e('Método de Pago Preferido', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="aplicante-metodo-pago">
                            <?php esc_html_e('Método', 'gestionadmin-wolk'); ?>
                        </label>
                        <select id="aplicante-metodo-pago" name="metodo_pago_preferido" class="ga-form-input">
                            <option value=""><?php esc_html_e('Seleccionar...', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($metodos_pago as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Estado (admin) -->
                <div class="ga-form-section" id="ga-section-estado-aplicante">
                    <h3><?php esc_html_e('Estado y Notas', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-row">
                        <div class="ga-col ga-col-6">
                            <div class="ga-form-group">
                                <label class="ga-form-label" for="aplicante-estado">
                                    <?php esc_html_e('Estado', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="aplicante-estado" name="estado" class="ga-form-input">
                                    <?php foreach ($estados as $key => $label) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label class="ga-form-label" for="aplicante-notas">
                            <?php esc_html_e('Notas Administrativas', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="aplicante-notas" name="notas_admin" class="ga-form-input" rows="3"
                                  placeholder="<?php esc_attr_e('Notas internas (no visibles para el aplicante)...', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>
                </div>
            </div>

            <div class="ga-modal-footer">
                <button type="button" class="ga-btn ga-modal-close-btn">
                    <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                </button>
                <button type="submit" class="ga-btn ga-btn-primary" id="ga-btn-save-aplicante">
                    <?php esc_html_e('Guardar', 'gestionadmin-wolk'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =============================================================================
     MODAL: VER APLICACIÓN
============================================================================== -->
<div id="ga-modal-aplicacion" class="ga-modal" style="display: none;">
    <div class="ga-modal-content">
        <div class="ga-modal-header">
            <h2><?php esc_html_e('Detalle de Aplicación', 'gestionadmin-wolk'); ?></h2>
            <button type="button" class="ga-modal-close">&times;</button>
        </div>
        <div class="ga-modal-body" id="ga-aplicacion-content">
            <!-- Se llena por AJAX -->
        </div>
        <div class="ga-modal-footer">
            <button type="button" class="ga-btn ga-modal-close-btn">
                <?php esc_html_e('Cerrar', 'gestionadmin-wolk'); ?>
            </button>
        </div>
    </div>
</div>

<!-- =============================================================================
     JAVASCRIPT
============================================================================== -->
<script>
jQuery(document).ready(function($) {

    // Cache de datos
    var aplicantes = <?php echo wp_json_encode(array_map(function($a) {
        return array(
            'id' => $a->id,
            'tipo' => $a->tipo,
            'nombre_completo' => $a->nombre_completo,
            'email' => $a->email,
            'telefono' => $a->telefono,
            'pais' => $a->pais,
            'ciudad' => $a->ciudad,
            'documento_tipo' => $a->documento_tipo,
            'documento_numero' => $a->documento_numero,
            'titulo_profesional' => $a->titulo_profesional,
            'bio' => $a->bio,
            'habilidades' => $a->habilidades,
            'nivel_experiencia' => $a->nivel_experiencia,
            'anos_experiencia' => $a->anos_experiencia,
            'tarifa_hora_min' => $a->tarifa_hora_min,
            'tarifa_hora_max' => $a->tarifa_hora_max,
            'disponibilidad_horas' => $a->disponibilidad_horas,
            'disponible_inmediato' => $a->disponible_inmediato,
            'portfolio_url' => $a->portfolio_url,
            'linkedin_url' => $a->linkedin_url,
            'github_url' => $a->github_url,
            'metodo_pago_preferido' => $a->metodo_pago_preferido,
            'estado' => $a->estado,
            'notas_admin' => $a->notas_admin,
        );
    }, $aplicantes)); ?>;

    // =========================================================================
    // MODAL FUNCTIONS
    // =========================================================================

    function openModal(modalId) {
        $(modalId).fadeIn(200);
    }

    function closeModal(modalId) {
        $(modalId).fadeOut(200);
    }

    function resetFormAplicante() {
        $('#ga-form-aplicante')[0].reset();
        $('#aplicante-id').val(0);
        $('#ga-modal-title-aplicante').text('<?php echo esc_js(__('Nuevo Aplicante', 'gestionadmin-wolk')); ?>');
    }

    function loadAplicante(id) {
        var a = aplicantes.find(function(item) { return item.id == id; });
        if (!a) return;

        $('#aplicante-id').val(a.id);
        $('#aplicante-tipo').val(a.tipo);
        $('#aplicante-nombre').val(a.nombre_completo);
        $('#aplicante-email').val(a.email);
        $('#aplicante-telefono').val(a.telefono);
        $('#aplicante-pais').val(a.pais);
        $('#aplicante-ciudad').val(a.ciudad);
        $('#aplicante-doc-tipo').val(a.documento_tipo);
        $('#aplicante-doc-numero').val(a.documento_numero);
        $('#aplicante-titulo').val(a.titulo_profesional);
        $('#aplicante-bio').val(a.bio);
        $('#aplicante-nivel').val(a.nivel_experiencia);
        $('#aplicante-anos').val(a.anos_experiencia);
        $('#aplicante-tarifa-min').val(a.tarifa_hora_min);
        $('#aplicante-tarifa-max').val(a.tarifa_hora_max);
        $('#aplicante-horas').val(a.disponibilidad_horas);
        $('#aplicante-disponible').prop('checked', a.disponible_inmediato == 1);
        $('#aplicante-portfolio').val(a.portfolio_url);
        $('#aplicante-linkedin').val(a.linkedin_url);
        $('#aplicante-github').val(a.github_url);
        $('#aplicante-metodo-pago').val(a.metodo_pago_preferido);
        $('#aplicante-estado').val(a.estado);
        $('#aplicante-notas').val(a.notas_admin);

        // Habilidades
        if (a.habilidades) {
            try {
                var habs = JSON.parse(a.habilidades);
                $('#aplicante-habilidades').val(Array.isArray(habs) ? habs.join(', ') : a.habilidades);
            } catch (e) {
                $('#aplicante-habilidades').val(a.habilidades);
            }
        }

        $('#ga-modal-title-aplicante').text('<?php echo esc_js(__('Editar Aplicante:', 'gestionadmin-wolk')); ?> ' + a.nombre_completo);
    }

    // =========================================================================
    // EVENT HANDLERS
    // =========================================================================

    // Nuevo aplicante
    $('#ga-btn-new-aplicante').on('click', function() {
        resetFormAplicante();
        openModal('#ga-modal-aplicante');
    });

    // Editar aplicante
    $(document).on('click', '.ga-btn-edit-aplicante', function(e) {
        e.preventDefault();
        loadAplicante($(this).data('id'));
        openModal('#ga-modal-aplicante');
    });

    // Cerrar modales
    $('.ga-modal-close, .ga-modal-close-btn').on('click', function() {
        closeModal('.ga-modal');
    });

    $('.ga-modal').on('click', function(e) {
        if ($(e.target).hasClass('ga-modal')) {
            closeModal('.ga-modal');
        }
    });

    // Guardar aplicante
    $('#ga-form-aplicante').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#ga-btn-save-aplicante');
        $btn.prop('disabled', true).text(gaAdmin.i18n.saving);

        // Procesar habilidades
        var habilidades = $('#aplicante-habilidades').val();
        if (habilidades) {
            habilidades = habilidades.split(',').map(function(h) { return h.trim(); }).filter(function(h) { return h; });
        } else {
            habilidades = [];
        }

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_save_aplicante',
            nonce: gaAdmin.nonce,
            id: $('#aplicante-id').val(),
            tipo: $('#aplicante-tipo').val(),
            nombre_completo: $('#aplicante-nombre').val(),
            email: $('#aplicante-email').val(),
            telefono: $('#aplicante-telefono').val(),
            pais: $('#aplicante-pais').val(),
            ciudad: $('#aplicante-ciudad').val(),
            documento_tipo: $('#aplicante-doc-tipo').val(),
            documento_numero: $('#aplicante-doc-numero').val(),
            titulo_profesional: $('#aplicante-titulo').val(),
            bio: $('#aplicante-bio').val(),
            habilidades: JSON.stringify(habilidades),
            nivel_experiencia: $('#aplicante-nivel').val(),
            anos_experiencia: $('#aplicante-anos').val(),
            tarifa_hora_min: $('#aplicante-tarifa-min').val(),
            tarifa_hora_max: $('#aplicante-tarifa-max').val(),
            disponibilidad_horas: $('#aplicante-horas').val(),
            disponible_inmediato: $('#aplicante-disponible').is(':checked') ? 1 : 0,
            portfolio_url: $('#aplicante-portfolio').val(),
            linkedin_url: $('#aplicante-linkedin').val(),
            github_url: $('#aplicante-github').val(),
            metodo_pago_preferido: $('#aplicante-metodo-pago').val(),
            estado: $('#aplicante-estado').val(),
            notas_admin: $('#aplicante-notas').val()
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Guardar', 'gestionadmin-wolk')); ?>');
            }
        });
    });

    // Verificar aplicante
    $(document).on('click', '.ga-btn-verify-aplicante', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('<?php echo esc_js(__('¿Verificar este aplicante? Podrá aplicar a órdenes de trabajo.', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_change_aplicante_estado',
            nonce: gaAdmin.nonce,
            id: id,
            estado: 'VERIFICADO'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Eliminar aplicante
    $(document).on('click', '.ga-btn-delete-aplicante', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('<?php echo esc_js(__('¿Eliminar este aplicante? Esta acción no se puede deshacer.', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        $.post(gaAdmin.ajaxUrl, {
            action: 'ga_delete_aplicante',
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
        var tipo = $('#ga-filter-tipo').val();
        var pais = $('#ga-filter-pais').val();

        $('#ga-aplicantes-table tbody tr').each(function() {
            var $row = $(this);
            var show = true;

            if (buscar && $row.text().toLowerCase().indexOf(buscar) === -1) {
                show = false;
            }
            if (estado && $row.data('estado') !== estado) {
                show = false;
            }
            if (tipo && $row.data('tipo') !== tipo) {
                show = false;
            }
            if (pais && $row.data('pais') !== pais) {
                show = false;
            }

            $row.toggle(show);
        });
    }

    $('#ga-btn-filter').on('click', applyFilters);
    $('#ga-filter-buscar').on('keyup', function(e) {
        if (e.keyCode === 13) applyFilters();
    });

    $('#ga-btn-clear-filter').on('click', function() {
        $('#ga-filter-buscar, #ga-filter-estado, #ga-filter-tipo, #ga-filter-pais').val('');
        $('#ga-aplicantes-table tbody tr').show();
    });

});
</script>

<style>
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

.ga-form-section {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid #eee;
}
.ga-form-section:last-child {
    border-bottom: none;
}
.ga-form-section h3 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
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
}
.ga-actions a:hover {
    color: #135e96;
}

.ga-rating {
    color: #f5a623;
    font-weight: bold;
}

.ga-btn-link {
    background: none;
    border: none;
    color: #2271b1;
    cursor: pointer;
    padding: 8px;
}
</style>
