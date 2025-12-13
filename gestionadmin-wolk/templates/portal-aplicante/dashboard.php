<?php
/**
 * Template: Dashboard del Aplicante
 *
 * Panel principal del aplicante con resumen de actividad.
 *
 * URL: /mi-cuenta/
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener aplicante actual
$aplicante = GA_Public::get_current_aplicante();

// Si no tiene perfil de aplicante, mostrar mensaje
if (!$aplicante) {
    include GA_PLUGIN_DIR . 'templates/portal-aplicante/no-aplicante.php';
    return;
}

// Estadísticas
$total_aplicaciones = GA_Aplicantes::count_aplicaciones($aplicante->id);
$pendientes = GA_Aplicantes::count_aplicaciones($aplicante->id, 'PENDIENTE');
$en_revision = GA_Aplicantes::count_aplicaciones($aplicante->id, 'EN_REVISION');
$contratados = GA_Aplicantes::count_aplicaciones($aplicante->id, 'CONTRATADO');

// Últimas aplicaciones
$ultimas_apps = GA_Aplicaciones::get_all(array(
    'aplicante_id' => $aplicante->id,
    'limit'        => 5,
));

// Órdenes recomendadas (basadas en categorías populares)
$ordenes_nuevas = GA_Ordenes_Trabajo::get_recientes(3, true);

// Estados
$estados_aplicacion = GA_Aplicaciones::get_estados();

get_header();
?>

<div class="ga-public-container ga-dashboard">
    <div class="ga-container">
        <!-- =========================================================================
             HEADER DEL DASHBOARD
        ========================================================================== -->
        <header class="ga-dashboard-header">
            <div class="ga-user-welcome">
                <h1><?php printf(esc_html__('Hola, %s', 'gestionadmin-wolk'), esc_html($aplicante->nombre_completo)); ?></h1>
                <p class="ga-welcome-subtitle">
                    <?php if ($aplicante->estado === 'VERIFICADO') : ?>
                        <?php esc_html_e('Tu cuenta está verificada. Puedes aplicar a trabajos.', 'gestionadmin-wolk'); ?>
                    <?php elseif ($aplicante->estado === 'PENDIENTE_VERIFICACION') : ?>
                        <span class="ga-notice-warning">
                            <?php esc_html_e('Tu cuenta está pendiente de verificación. No podrás aplicar hasta que sea aprobada.', 'gestionadmin-wolk'); ?>
                        </span>
                    <?php else : ?>
                        <span class="ga-notice-error">
                            <?php esc_html_e('Tu cuenta tiene restricciones. Contacta al soporte.', 'gestionadmin-wolk'); ?>
                        </span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Navegación del dashboard -->
            <nav class="ga-dashboard-nav">
                <a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" class="ga-nav-item ga-nav-active">
                    <span class="dashicons dashicons-dashboard"></span>
                    <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/mi-cuenta/aplicaciones/')); ?>" class="ga-nav-item">
                    <span class="dashicons dashicons-portfolio"></span>
                    <?php esc_html_e('Mis Aplicaciones', 'gestionadmin-wolk'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/mi-cuenta/perfil/')); ?>" class="ga-nav-item">
                    <span class="dashicons dashicons-admin-users"></span>
                    <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" class="ga-nav-item">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e('Buscar Trabajo', 'gestionadmin-wolk'); ?>
                </a>
            </nav>
        </header>

        <div class="ga-dashboard-content">
            <!-- =========================================================================
                 ESTADÍSTICAS
            ========================================================================== -->
            <section class="ga-dashboard-stats">
                <div class="ga-stat-card">
                    <span class="ga-stat-icon"><span class="dashicons dashicons-portfolio"></span></span>
                    <div class="ga-stat-content">
                        <span class="ga-stat-number"><?php echo esc_html($total_aplicaciones); ?></span>
                        <span class="ga-stat-label"><?php esc_html_e('Total Aplicaciones', 'gestionadmin-wolk'); ?></span>
                    </div>
                </div>

                <div class="ga-stat-card">
                    <span class="ga-stat-icon ga-stat-pending"><span class="dashicons dashicons-clock"></span></span>
                    <div class="ga-stat-content">
                        <span class="ga-stat-number"><?php echo esc_html($pendientes + $en_revision); ?></span>
                        <span class="ga-stat-label"><?php esc_html_e('En Proceso', 'gestionadmin-wolk'); ?></span>
                    </div>
                </div>

                <div class="ga-stat-card">
                    <span class="ga-stat-icon ga-stat-success"><span class="dashicons dashicons-yes-alt"></span></span>
                    <div class="ga-stat-content">
                        <span class="ga-stat-number"><?php echo esc_html($contratados); ?></span>
                        <span class="ga-stat-label"><?php esc_html_e('Trabajos Ganados', 'gestionadmin-wolk'); ?></span>
                    </div>
                </div>

                <div class="ga-stat-card">
                    <span class="ga-stat-icon ga-stat-rating"><span class="dashicons dashicons-star-filled"></span></span>
                    <div class="ga-stat-content">
                        <span class="ga-stat-number">
                            <?php echo $aplicante->calificacion_promedio
                                ? esc_html(number_format($aplicante->calificacion_promedio, 1))
                                : '-'; ?>
                        </span>
                        <span class="ga-stat-label"><?php esc_html_e('Calificación', 'gestionadmin-wolk'); ?></span>
                    </div>
                </div>
            </section>

            <div class="ga-dashboard-grid">
                <!-- =========================================================================
                     ÚLTIMAS APLICACIONES
                ========================================================================== -->
                <section class="ga-dashboard-section">
                    <header class="ga-section-header">
                        <h2><?php esc_html_e('Últimas Aplicaciones', 'gestionadmin-wolk'); ?></h2>
                        <a href="<?php echo esc_url(home_url('/mi-cuenta/aplicaciones/')); ?>" class="ga-link">
                            <?php esc_html_e('Ver todas', 'gestionadmin-wolk'); ?> →
                        </a>
                    </header>

                    <?php if (empty($ultimas_apps)) : ?>
                        <div class="ga-empty-state">
                            <span class="dashicons dashicons-portfolio"></span>
                            <p><?php esc_html_e('Aún no has aplicado a ningún trabajo.', 'gestionadmin-wolk'); ?></p>
                            <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" class="ga-btn ga-btn-primary">
                                <?php esc_html_e('Explorar Oportunidades', 'gestionadmin-wolk'); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="ga-applications-list">
                            <?php foreach ($ultimas_apps as $app) : ?>
                                <div class="ga-application-item">
                                    <div class="ga-application-info">
                                        <h4>
                                            <a href="<?php echo esc_url(GA_Public::get_orden_url($app->orden_codigo)); ?>">
                                                <?php echo esc_html($app->orden_titulo); ?>
                                            </a>
                                        </h4>
                                        <span class="ga-application-meta">
                                            <?php echo esc_html($app->orden_codigo); ?> ·
                                            <?php echo esc_html(human_time_diff(strtotime($app->created_at))); ?>
                                        </span>
                                    </div>
                                    <span class="ga-badge <?php echo esc_attr(GA_Aplicaciones::get_estado_class($app->estado)); ?>">
                                        <?php echo esc_html($estados_aplicacion[$app->estado] ?? $app->estado); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- =========================================================================
                     NUEVAS OPORTUNIDADES
                ========================================================================== -->
                <section class="ga-dashboard-section">
                    <header class="ga-section-header">
                        <h2><?php esc_html_e('Nuevas Oportunidades', 'gestionadmin-wolk'); ?></h2>
                        <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" class="ga-link">
                            <?php esc_html_e('Ver todas', 'gestionadmin-wolk'); ?> →
                        </a>
                    </header>

                    <?php if (empty($ordenes_nuevas)) : ?>
                        <div class="ga-empty-state">
                            <span class="dashicons dashicons-search"></span>
                            <p><?php esc_html_e('No hay oportunidades disponibles en este momento.', 'gestionadmin-wolk'); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="ga-opportunities-list">
                            <?php
                            $categorias = GA_Ordenes_Trabajo::get_categorias();
                            foreach ($ordenes_nuevas as $orden) :
                            ?>
                                <div class="ga-opportunity-item">
                                    <div class="ga-opportunity-info">
                                        <span class="ga-opportunity-category">
                                            <?php echo esc_html($categorias[$orden->categoria] ?? $orden->categoria); ?>
                                        </span>
                                        <h4>
                                            <a href="<?php echo esc_url(GA_Public::get_orden_url($orden->codigo)); ?>">
                                                <?php echo esc_html($orden->titulo); ?>
                                            </a>
                                        </h4>
                                        <span class="ga-opportunity-meta">
                                            <?php
                                            if ($orden->presupuesto_max) {
                                                echo '$' . number_format($orden->presupuesto_max, 0);
                                            } else {
                                                esc_html_e('A convenir', 'gestionadmin-wolk');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <a href="<?php echo esc_url(GA_Public::get_orden_url($orden->codigo)); ?>" class="ga-btn ga-btn-outline ga-btn-sm">
                                        <?php esc_html_e('Ver', 'gestionadmin-wolk'); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- =========================================================================
                 PERFIL RÁPIDO
            ========================================================================== -->
            <section class="ga-dashboard-profile-summary">
                <header class="ga-section-header">
                    <h2><?php esc_html_e('Tu Perfil', 'gestionadmin-wolk'); ?></h2>
                    <a href="<?php echo esc_url(home_url('/mi-cuenta/perfil/')); ?>" class="ga-link">
                        <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?> →
                    </a>
                </header>

                <div class="ga-profile-quick">
                    <div class="ga-profile-quick-item">
                        <span class="ga-label"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-badge <?php echo esc_attr(GA_Aplicantes::get_estado_class($aplicante->estado)); ?>">
                            <?php echo esc_html(GA_Aplicantes::get_estado_label($aplicante->estado)); ?>
                        </span>
                    </div>
                    <div class="ga-profile-quick-item">
                        <span class="ga-label"><?php esc_html_e('Email', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-value"><?php echo esc_html($aplicante->email); ?></span>
                    </div>
                    <?php if ($aplicante->titulo_profesional) : ?>
                        <div class="ga-profile-quick-item">
                            <span class="ga-label"><?php esc_html_e('Título', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-value"><?php echo esc_html($aplicante->titulo_profesional); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($aplicante->tarifa_hora_min || $aplicante->tarifa_hora_max) : ?>
                        <div class="ga-profile-quick-item">
                            <span class="ga-label"><?php esc_html_e('Tarifa', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-value">
                                <?php
                                if ($aplicante->tarifa_hora_min && $aplicante->tarifa_hora_max) {
                                    echo '$' . $aplicante->tarifa_hora_min . ' - $' . $aplicante->tarifa_hora_max . '/h';
                                } elseif ($aplicante->tarifa_hora_min) {
                                    echo 'Desde $' . $aplicante->tarifa_hora_min . '/h';
                                }
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php get_footer(); ?>
