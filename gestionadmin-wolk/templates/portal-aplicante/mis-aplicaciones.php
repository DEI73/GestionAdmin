<?php
/**
 * Template: Mis Aplicaciones
 *
 * Historial de aplicaciones del aplicante a órdenes de trabajo.
 * Muestra estado, mensajes y acciones disponibles.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar que el usuario está logueado
if (!is_user_logged_in()) {
    include GA_PLUGIN_DIR . 'templates/portal-aplicante/login-required.php';
    return;
}

// Obtener aplicante actual
$ga_public = GA_Public::get_instance();
$aplicante = $ga_public->get_current_aplicante();

// Verificar que es un aplicante registrado
if (!$aplicante) {
    include GA_PLUGIN_DIR . 'templates/portal-aplicante/no-aplicante.php';
    return;
}

// Obtener aplicaciones del aplicante
$aplicaciones_module = GA_Aplicaciones::get_instance();
$aplicaciones = $aplicaciones_module->get_all(array(
    'aplicante_id' => $aplicante->id,
    'orderby'      => 'fecha_aplicacion',
    'order'        => 'DESC'
));

// Filtrar por estado si se especifica
$estado_filtro = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';

// Estadísticas
$stats = array(
    'total'      => 0,
    'pendientes' => 0,
    'aceptadas'  => 0,
    'rechazadas' => 0,
    'en_proceso' => 0
);

foreach ($aplicaciones as $app) {
    $stats['total']++;
    switch ($app->estado) {
        case 'pendiente':
        case 'en_revision':
            $stats['pendientes']++;
            break;
        case 'aceptada':
        case 'contratada':
            $stats['aceptadas']++;
            break;
        case 'rechazada':
        case 'descartada':
            $stats['rechazadas']++;
            break;
        case 'en_proceso':
        case 'completada':
            $stats['en_proceso']++;
            break;
    }
}

// Usar header del tema (o fallback del plugin si no está activo)
get_header();

// Imprimir estilos del portal (heredan colores del tema si está activo)
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-dashboard-page">
    <div class="ga-container">

        <!-- Navegación del Dashboard -->
        <nav class="ga-dashboard-nav">
            <a href="<?php echo esc_url($ga_public->get_cuenta_url()); ?>">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($ga_public->get_cuenta_url('aplicaciones')); ?>" class="active">
                <span class="dashicons dashicons-portfolio"></span>
                <?php esc_html_e('Mis Aplicaciones', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($ga_public->get_cuenta_url('perfil')); ?>">
                <span class="dashicons dashicons-admin-users"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($ga_public->get_trabajo_url()); ?>">
                <span class="dashicons dashicons-search"></span>
                <?php esc_html_e('Buscar Trabajo', 'gestionadmin-wolk'); ?>
            </a>
        </nav>

        <!-- Header -->
        <div class="ga-dashboard-header">
            <div class="ga-dashboard-welcome">
                <h1><?php esc_html_e('Mis Aplicaciones', 'gestionadmin-wolk'); ?></h1>
                <p><?php esc_html_e('Historial y estado de tus aplicaciones a órdenes de trabajo', 'gestionadmin-wolk'); ?></p>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="ga-stats-grid ga-stats-small">
            <div class="ga-stat-card">
                <div class="ga-stat-card-content">
                    <span class="number"><?php echo esc_html($stats['total']); ?></span>
                    <span class="label"><?php esc_html_e('Total', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
            <div class="ga-stat-card">
                <div class="ga-stat-card-content">
                    <span class="number"><?php echo esc_html($stats['pendientes']); ?></span>
                    <span class="label"><?php esc_html_e('Pendientes', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
            <div class="ga-stat-card">
                <div class="ga-stat-card-content">
                    <span class="number"><?php echo esc_html($stats['aceptadas']); ?></span>
                    <span class="label"><?php esc_html_e('Aceptadas', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
            <div class="ga-stat-card">
                <div class="ga-stat-card-content">
                    <span class="number"><?php echo esc_html($stats['en_proceso']); ?></span>
                    <span class="label"><?php esc_html_e('En Proceso', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="ga-filters-inline">
            <a href="<?php echo esc_url($ga_public->get_cuenta_url('aplicaciones')); ?>"
               class="ga-filter-pill <?php echo empty($estado_filtro) ? 'active' : ''; ?>">
                <?php esc_html_e('Todas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg('estado', 'pendiente', $ga_public->get_cuenta_url('aplicaciones'))); ?>"
               class="ga-filter-pill <?php echo $estado_filtro === 'pendiente' ? 'active' : ''; ?>">
                <?php esc_html_e('Pendientes', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg('estado', 'aceptada', $ga_public->get_cuenta_url('aplicaciones'))); ?>"
               class="ga-filter-pill <?php echo $estado_filtro === 'aceptada' ? 'active' : ''; ?>">
                <?php esc_html_e('Aceptadas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg('estado', 'rechazada', $ga_public->get_cuenta_url('aplicaciones'))); ?>"
               class="ga-filter-pill <?php echo $estado_filtro === 'rechazada' ? 'active' : ''; ?>">
                <?php esc_html_e('Rechazadas', 'gestionadmin-wolk'); ?>
            </a>
        </div>

        <!-- Lista de Aplicaciones -->
        <div class="ga-card">
            <div class="ga-card-body">
                <?php if (empty($aplicaciones)) : ?>
                    <div class="ga-empty-state">
                        <span class="dashicons dashicons-portfolio"></span>
                        <h3><?php esc_html_e('No tienes aplicaciones', 'gestionadmin-wolk'); ?></h3>
                        <p><?php esc_html_e('Aún no has aplicado a ninguna orden de trabajo.', 'gestionadmin-wolk'); ?></p>
                        <a href="<?php echo esc_url($ga_public->get_trabajo_url()); ?>" class="ga-btn ga-btn-primary">
                            <?php esc_html_e('Explorar Oportunidades', 'gestionadmin-wolk'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="ga-aplicaciones-list">
                        <?php
                        $ordenes_module = GA_Ordenes_Trabajo::get_instance();

                        foreach ($aplicaciones as $aplicacion) :
                            // Filtrar si hay estado seleccionado
                            if ($estado_filtro && strpos($aplicacion->estado, $estado_filtro) === false) {
                                continue;
                            }

                            $orden = $ordenes_module->get($aplicacion->orden_id);
                            if (!$orden) continue;

                            // Determinar clase de estado
                            $estado_class = 'ga-estado-' . $aplicacion->estado;

                            // Etiqueta de estado legible
                            $estados_labels = array(
                                'pendiente'   => __('Pendiente', 'gestionadmin-wolk'),
                                'en_revision' => __('En Revisión', 'gestionadmin-wolk'),
                                'aceptada'    => __('Aceptada', 'gestionadmin-wolk'),
                                'rechazada'   => __('Rechazada', 'gestionadmin-wolk'),
                                'contratada'  => __('Contratada', 'gestionadmin-wolk'),
                                'en_proceso'  => __('En Proceso', 'gestionadmin-wolk'),
                                'completada'  => __('Completada', 'gestionadmin-wolk'),
                                'cancelada'   => __('Cancelada', 'gestionadmin-wolk'),
                                'descartada'  => __('Descartada', 'gestionadmin-wolk')
                            );
                            $estado_label = isset($estados_labels[$aplicacion->estado])
                                ? $estados_labels[$aplicacion->estado]
                                : ucfirst($aplicacion->estado);
                        ?>
                            <div class="ga-aplicacion-item">
                                <div class="ga-aplicacion-header">
                                    <div class="ga-aplicacion-orden">
                                        <span class="ga-orden-codigo"><?php echo esc_html($orden->codigo); ?></span>
                                        <h3 class="ga-orden-titulo">
                                            <a href="<?php echo esc_url($ga_public->get_orden_url($orden->codigo)); ?>">
                                                <?php echo esc_html($orden->titulo); ?>
                                            </a>
                                        </h3>
                                    </div>
                                    <span class="ga-badge <?php echo esc_attr($estado_class); ?>">
                                        <?php echo esc_html($estado_label); ?>
                                    </span>
                                </div>

                                <div class="ga-aplicacion-body">
                                    <div class="ga-aplicacion-meta">
                                        <div class="ga-meta-item">
                                            <span class="label"><?php esc_html_e('Fecha de aplicación:', 'gestionadmin-wolk'); ?></span>
                                            <span class="value">
                                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($aplicacion->fecha_aplicacion))); ?>
                                            </span>
                                        </div>

                                        <?php if (!empty($aplicacion->propuesta_economica)) : ?>
                                            <div class="ga-meta-item">
                                                <span class="label"><?php esc_html_e('Tu propuesta:', 'gestionadmin-wolk'); ?></span>
                                                <span class="value ga-text-success">
                                                    $<?php echo number_format($aplicacion->propuesta_economica, 2); ?> USD
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($aplicacion->tiempo_entrega)) : ?>
                                            <div class="ga-meta-item">
                                                <span class="label"><?php esc_html_e('Tiempo de entrega:', 'gestionadmin-wolk'); ?></span>
                                                <span class="value">
                                                    <?php echo esc_html($aplicacion->tiempo_entrega); ?> <?php esc_html_e('días', 'gestionadmin-wolk'); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="ga-meta-item">
                                            <span class="label"><?php esc_html_e('Presupuesto orden:', 'gestionadmin-wolk'); ?></span>
                                            <span class="value">
                                                <?php if ($orden->presupuesto_min && $orden->presupuesto_max) : ?>
                                                    $<?php echo number_format($orden->presupuesto_min, 0); ?> -
                                                    $<?php echo number_format($orden->presupuesto_max, 0); ?> USD
                                                <?php elseif ($orden->presupuesto_max) : ?>
                                                    <?php esc_html_e('Hasta', 'gestionadmin-wolk'); ?>
                                                    $<?php echo number_format($orden->presupuesto_max, 0); ?> USD
                                                <?php else : ?>
                                                    <?php esc_html_e('A convenir', 'gestionadmin-wolk'); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <?php if (!empty($aplicacion->mensaje)) : ?>
                                        <div class="ga-aplicacion-mensaje">
                                            <strong><?php esc_html_e('Tu mensaje:', 'gestionadmin-wolk'); ?></strong>
                                            <p><?php echo esc_html(wp_trim_words($aplicacion->mensaje, 30)); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($aplicacion->notas_admin)) : ?>
                                        <div class="ga-aplicacion-feedback ga-alert ga-alert-info">
                                            <strong><?php esc_html_e('Feedback:', 'gestionadmin-wolk'); ?></strong>
                                            <p><?php echo esc_html($aplicacion->notas_admin); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="ga-aplicacion-footer">
                                    <a href="<?php echo esc_url($ga_public->get_orden_url($orden->codigo)); ?>"
                                       class="ga-btn ga-btn-sm ga-btn-secondary">
                                        <?php esc_html_e('Ver Orden', 'gestionadmin-wolk'); ?>
                                    </a>

                                    <?php if ($aplicacion->estado === 'contratada' || $aplicacion->estado === 'en_proceso') : ?>
                                        <a href="<?php echo esc_url(add_query_arg('trabajo', $aplicacion->id, $ga_public->get_cuenta_url('trabajo'))); ?>"
                                           class="ga-btn ga-btn-sm ga-btn-primary">
                                            <?php esc_html_e('Ir al Trabajo', 'gestionadmin-wolk'); ?>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($aplicacion->estado === 'pendiente') : ?>
                                        <button type="button"
                                                class="ga-btn ga-btn-sm ga-btn-outline ga-btn-cancelar-aplicacion"
                                                data-id="<?php echo esc_attr($aplicacion->id); ?>"
                                                data-confirm="<?php esc_attr_e('¿Estás seguro de cancelar esta aplicación?', 'gestionadmin-wolk'); ?>">
                                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<style>
/* Estilos adicionales para la lista de aplicaciones */
.ga-stats-small .ga-stat-card {
    padding: var(--ga-spacing-md);
}

.ga-stats-small .ga-stat-card .number {
    font-size: 1.5rem;
}

.ga-filters-inline {
    display: flex;
    gap: var(--ga-spacing-sm);
    margin-bottom: var(--ga-spacing-lg);
    flex-wrap: wrap;
}

.ga-filter-pill {
    padding: var(--ga-spacing-xs) var(--ga-spacing-md);
    background: var(--ga-bg-light);
    border: 1px solid var(--ga-border);
    border-radius: var(--ga-radius-full);
    font-size: 13px;
    color: var(--ga-text-secondary);
    transition: all var(--ga-transition);
}

.ga-filter-pill:hover,
.ga-filter-pill.active {
    background: var(--ga-primary);
    border-color: var(--ga-primary);
    color: #fff;
}

.ga-aplicaciones-list {
    display: flex;
    flex-direction: column;
    gap: var(--ga-spacing-md);
}

.ga-aplicacion-item {
    border: 1px solid var(--ga-border);
    border-radius: var(--ga-radius-md);
    overflow: hidden;
    transition: all var(--ga-transition);
}

.ga-aplicacion-item:hover {
    border-color: var(--ga-primary-light);
    box-shadow: var(--ga-shadow);
}

.ga-aplicacion-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: var(--ga-spacing-md);
    background: var(--ga-bg-light);
    border-bottom: 1px solid var(--ga-border-light);
}

.ga-aplicacion-orden .ga-orden-codigo {
    display: block;
    margin-bottom: var(--ga-spacing-xs);
}

.ga-aplicacion-orden .ga-orden-titulo {
    margin: 0;
    font-size: 1rem;
}

.ga-aplicacion-body {
    padding: var(--ga-spacing-md);
}

.ga-aplicacion-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--ga-spacing-md);
    margin-bottom: var(--ga-spacing-md);
}

.ga-meta-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ga-meta-item .label {
    font-size: 12px;
    color: var(--ga-text-muted);
}

.ga-meta-item .value {
    font-weight: 500;
}

.ga-aplicacion-mensaje {
    padding: var(--ga-spacing-md);
    background: var(--ga-bg-light);
    border-radius: var(--ga-radius-sm);
    margin-top: var(--ga-spacing-md);
}

.ga-aplicacion-mensaje p {
    margin: var(--ga-spacing-sm) 0 0;
    color: var(--ga-text-secondary);
}

.ga-aplicacion-feedback {
    margin-top: var(--ga-spacing-md);
}

.ga-aplicacion-feedback p {
    margin: var(--ga-spacing-sm) 0 0;
}

.ga-aplicacion-footer {
    display: flex;
    gap: var(--ga-spacing-sm);
    padding: var(--ga-spacing-md);
    background: var(--ga-bg-light);
    border-top: 1px solid var(--ga-border-light);
}

/* Estados específicos */
.ga-estado-pendiente { background: #fef8e8; color: #996800; }
.ga-estado-en_revision { background: #e8f4fc; color: #2271b1; }
.ga-estado-aceptada { background: #d7f4d7; color: #00a32a; }
.ga-estado-rechazada { background: #fcebeb; color: #d63638; }
.ga-estado-contratada { background: #c3e6cd; color: #007017; }
.ga-estado-en_proceso { background: #e8f4fc; color: #2271b1; }
.ga-estado-completada { background: #c3e6cd; color: #007017; }
.ga-estado-cancelada { background: #f0f0f1; color: #50575e; }
.ga-estado-descartada { background: #f0f0f1; color: #50575e; }

@media (max-width: 768px) {
    .ga-aplicacion-header {
        flex-direction: column;
        gap: var(--ga-spacing-sm);
    }

    .ga-aplicacion-meta {
        grid-template-columns: 1fr;
    }

    .ga-aplicacion-footer {
        flex-direction: column;
    }

    .ga-aplicacion-footer .ga-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php get_footer(); ?>
