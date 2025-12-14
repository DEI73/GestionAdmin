<?php
/**
 * Template: Listado de Órdenes de Trabajo (Marketplace)
 *
 * Muestra todas las órdenes de trabajo publicadas.
 * Incluye filtros por categoría, modalidad y tipo de pago.
 * Integrado con tema GestionAdmin Theme.
 *
 * URL: /trabajo/
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalTrabajo
 * @since      1.3.0
 * @updated    1.6.0 - Integración con tema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener parámetros de filtro
$categoria_filter = get_query_var('ga_action');
$buscar = isset($_GET['buscar']) ? sanitize_text_field($_GET['buscar']) : '';
$modalidad = isset($_GET['modalidad']) ? sanitize_text_field($_GET['modalidad']) : '';
$tipo_pago = isset($_GET['tipo_pago']) ? sanitize_text_field($_GET['tipo_pago']) : '';

// Preparar argumentos para la consulta
$args = array(
    'solo_activas' => true,
    'orderby'      => 'created_at',
    'order'        => 'DESC',
);

if (!empty($categoria_filter)) {
    $args['categoria'] = strtoupper($categoria_filter);
}

if (!empty($buscar)) {
    $args['buscar'] = $buscar;
}

if (!empty($modalidad)) {
    $args['modalidad'] = $modalidad;
}

if (!empty($tipo_pago)) {
    $args['tipo_pago'] = $tipo_pago;
}

// Obtener órdenes
$ordenes = GA_Ordenes_Trabajo::get_all($args);

// Obtener enums para filtros
$categorias = GA_Ordenes_Trabajo::get_categorias();
$modalidades = GA_Ordenes_Trabajo::get_modalidades();
$tipos_pago = GA_Ordenes_Trabajo::get_tipos_pago();

// Usar header del tema (o fallback del plugin si no está activo)
get_header();

// Imprimir estilos del portal (heredan colores del tema si está activo)
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container">
    <!-- =========================================================================
         HEADER DEL MARKETPLACE
    ========================================================================== -->
    <header class="ga-marketplace-header">
        <div class="ga-container">
            <h1 class="ga-marketplace-title">
                <?php esc_html_e('Oportunidades de Trabajo', 'gestionadmin-wolk'); ?>
            </h1>
            <p class="ga-marketplace-subtitle">
                <?php esc_html_e('Encuentra proyectos que se ajusten a tus habilidades y experiencia.', 'gestionadmin-wolk'); ?>
            </p>
        </div>
    </header>

    <div class="ga-container ga-marketplace-content">
        <!-- =========================================================================
             FILTROS
        ========================================================================== -->
        <aside class="ga-marketplace-sidebar">
            <form method="get" action="<?php echo esc_url(home_url('/trabajo/')); ?>" class="ga-filter-form">
                <h3 class="ga-filter-title"><?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?></h3>

                <!-- Búsqueda -->
                <div class="ga-filter-group">
                    <label for="ga-buscar"><?php esc_html_e('Buscar', 'gestionadmin-wolk'); ?></label>
                    <input type="text" id="ga-buscar" name="buscar" value="<?php echo esc_attr($buscar); ?>"
                           placeholder="<?php esc_attr_e('Palabra clave...', 'gestionadmin-wolk'); ?>">
                </div>

                <!-- Categoría -->
                <div class="ga-filter-group">
                    <label for="ga-categoria"><?php esc_html_e('Categoría', 'gestionadmin-wolk'); ?></label>
                    <select id="ga-categoria" name="categoria">
                        <option value=""><?php esc_html_e('Todas', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($categorias as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($categoria_filter, strtolower($key)); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Modalidad -->
                <div class="ga-filter-group">
                    <label for="ga-modalidad"><?php esc_html_e('Modalidad', 'gestionadmin-wolk'); ?></label>
                    <select id="ga-modalidad" name="modalidad">
                        <option value=""><?php esc_html_e('Todas', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($modalidades as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($modalidad, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de Pago -->
                <div class="ga-filter-group">
                    <label for="ga-tipo-pago"><?php esc_html_e('Tipo de Pago', 'gestionadmin-wolk'); ?></label>
                    <select id="ga-tipo-pago" name="tipo_pago">
                        <option value=""><?php esc_html_e('Todos', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($tipos_pago as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($tipo_pago, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="ga-btn ga-btn-primary ga-btn-block">
                    <?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?>
                </button>

                <?php if (!empty($buscar) || !empty($modalidad) || !empty($tipo_pago) || !empty($categoria_filter)) : ?>
                    <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" class="ga-btn ga-btn-link ga-btn-block">
                        <?php esc_html_e('Limpiar filtros', 'gestionadmin-wolk'); ?>
                    </a>
                <?php endif; ?>
            </form>

            <!-- CTA para aplicantes -->
            <?php if (!is_user_logged_in()) : ?>
                <div class="ga-sidebar-cta">
                    <h4><?php esc_html_e('¿Eres profesional?', 'gestionadmin-wolk'); ?></h4>
                    <p><?php esc_html_e('Regístrate para aplicar a estas oportunidades.', 'gestionadmin-wolk'); ?></p>
                    <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>" class="ga-btn ga-btn-secondary ga-btn-block">
                        <?php esc_html_e('Registrarse', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </aside>

        <!-- =========================================================================
             LISTADO DE ÓRDENES
        ========================================================================== -->
        <main class="ga-marketplace-main">
            <!-- Resultados -->
            <div class="ga-results-header">
                <span class="ga-results-count">
                    <?php
                    printf(
                        esc_html(_n('%d oportunidad encontrada', '%d oportunidades encontradas', count($ordenes), 'gestionadmin-wolk')),
                        count($ordenes)
                    );
                    ?>
                </span>
            </div>

            <?php if (empty($ordenes)) : ?>
                <!-- Sin resultados -->
                <div class="ga-no-results">
                    <div class="ga-no-results-icon">
                        <span class="dashicons dashicons-search"></span>
                    </div>
                    <h3><?php esc_html_e('No hay órdenes disponibles', 'gestionadmin-wolk'); ?></h3>
                    <p><?php esc_html_e('No encontramos oportunidades que coincidan con tus criterios. Intenta con otros filtros.', 'gestionadmin-wolk'); ?></p>
                </div>
            <?php else : ?>
                <!-- Grid de órdenes -->
                <div class="ga-orders-grid">
                    <?php foreach ($ordenes as $orden) :
                        // Formatear presupuesto
                        $presupuesto = '';
                        if ($orden->presupuesto_min && $orden->presupuesto_max) {
                            $presupuesto = '$' . number_format($orden->presupuesto_min, 0) . ' - $' . number_format($orden->presupuesto_max, 0);
                        } elseif ($orden->presupuesto_max) {
                            $presupuesto = 'Hasta $' . number_format($orden->presupuesto_max, 0);
                        } elseif ($orden->presupuesto_min) {
                            $presupuesto = 'Desde $' . number_format($orden->presupuesto_min, 0);
                        }

                        // Habilidades
                        $habilidades = array();
                        if (!empty($orden->habilidades_requeridas)) {
                            $habilidades = json_decode($orden->habilidades_requeridas, true);
                            if (!is_array($habilidades)) {
                                $habilidades = array();
                            }
                        }

                        // Tiempo desde publicación
                        $tiempo = human_time_diff(strtotime($orden->created_at), current_time('timestamp'));

                        // Número de aplicaciones
                        $num_apps = GA_Ordenes_Trabajo::count_aplicaciones($orden->id);
                    ?>
                        <article class="ga-order-card">
                            <!-- Header -->
                            <header class="ga-order-card-header">
                                <div class="ga-order-meta">
                                    <span class="ga-order-category">
                                        <?php echo esc_html($categorias[$orden->categoria] ?? $orden->categoria); ?>
                                    </span>
                                    <span class="ga-order-time">
                                        <?php printf(esc_html__('Hace %s', 'gestionadmin-wolk'), $tiempo); ?>
                                    </span>
                                </div>
                                <?php if ($orden->prioridad === 'URGENTE') : ?>
                                    <span class="ga-badge ga-badge-urgent"><?php esc_html_e('Urgente', 'gestionadmin-wolk'); ?></span>
                                <?php endif; ?>
                            </header>

                            <!-- Contenido -->
                            <div class="ga-order-card-body">
                                <h3 class="ga-order-title">
                                    <a href="<?php echo esc_url(GA_Public::get_orden_url($orden->codigo)); ?>">
                                        <?php echo esc_html($orden->titulo); ?>
                                    </a>
                                </h3>

                                <p class="ga-order-description">
                                    <?php echo esc_html(wp_trim_words(strip_tags($orden->descripcion), 30)); ?>
                                </p>

                                <!-- Tags de habilidades -->
                                <?php if (!empty($habilidades)) : ?>
                                    <div class="ga-order-skills">
                                        <?php foreach (array_slice($habilidades, 0, 4) as $skill) : ?>
                                            <span class="ga-skill-tag"><?php echo esc_html($skill); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($habilidades) > 4) : ?>
                                            <span class="ga-skill-tag ga-skill-more">+<?php echo count($habilidades) - 4; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer -->
                            <footer class="ga-order-card-footer">
                                <div class="ga-order-details">
                                    <span class="ga-order-detail">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php echo esc_html($modalidades[$orden->modalidad] ?? $orden->modalidad); ?>
                                    </span>
                                    <span class="ga-order-detail">
                                        <span class="dashicons dashicons-money-alt"></span>
                                        <?php echo esc_html($presupuesto ?: $tipos_pago[$orden->tipo_pago] ?? ''); ?>
                                    </span>
                                    <?php if ($num_apps > 0) : ?>
                                        <span class="ga-order-detail">
                                            <span class="dashicons dashicons-groups"></span>
                                            <?php printf(esc_html__('%d aplicantes', 'gestionadmin-wolk'), $num_apps); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo esc_url(GA_Public::get_orden_url($orden->codigo)); ?>" class="ga-btn ga-btn-outline">
                                    <?php esc_html_e('Ver Detalles', 'gestionadmin-wolk'); ?>
                                </a>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>
