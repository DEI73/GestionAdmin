<?php
/**
 * Template: Detalle de Orden de Trabajo
 *
 * Muestra todos los detalles de una orden de trabajo específica.
 * Incluye botón para aplicar si el usuario está verificado.
 * Integrado con tema GestionAdmin Theme.
 *
 * URL: /trabajo/{codigo}/
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalTrabajo
 * @since      1.3.0
 * @updated    1.6.0 - Integración con tema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener código de la orden
$codigo = get_query_var('ga_codigo');
$orden = GA_Ordenes_Trabajo::get_by_codigo($codigo);

// Si no existe o no está publicada, redirigir
if (!$orden || $orden->estado !== 'PUBLICADA') {
    wp_redirect(home_url('/trabajo/'));
    exit;
}

// Obtener datos completos
$orden = GA_Ordenes_Trabajo::get($orden->id);

// Enums
$categorias = GA_Ordenes_Trabajo::get_categorias();
$modalidades = GA_Ordenes_Trabajo::get_modalidades();
$tipos_pago = GA_Ordenes_Trabajo::get_tipos_pago();
$niveles = GA_Ordenes_Trabajo::get_niveles_experiencia();

// Cargar acuerdos económicos
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-ordenes-acuerdos.php';
$acuerdos_instance = GA_Ordenes_Acuerdos::get_instance();
$acuerdos = $acuerdos_instance->get_para_portal($orden->id);

// Habilidades
$habilidades = array();
if (!empty($orden->habilidades_requeridas)) {
    $habilidades = json_decode($orden->habilidades_requeridas, true);
    if (!is_array($habilidades)) {
        $habilidades = array();
    }
}

// Presupuesto formateado
$presupuesto = '';
if ($orden->presupuesto_min && $orden->presupuesto_max) {
    $presupuesto = '$' . number_format($orden->presupuesto_min, 0) . ' - $' . number_format($orden->presupuesto_max, 0) . ' USD';
} elseif ($orden->presupuesto_max) {
    $presupuesto = 'Hasta $' . number_format($orden->presupuesto_max, 0) . ' USD';
} elseif ($orden->presupuesto_min) {
    $presupuesto = 'Desde $' . number_format($orden->presupuesto_min, 0) . ' USD';
} else {
    $presupuesto = $tipos_pago[$orden->tipo_pago] ?? 'A convenir';
}

// Estado del usuario
$aplicante = GA_Public::get_current_aplicante();
$puede_aplicar = $aplicante && $aplicante->estado === 'VERIFICADO';
$ya_aplico = false;

if ($aplicante) {
    $aplicacion_existente = GA_Aplicaciones::get_by_orden_aplicante($orden->id, $aplicante->id);
    $ya_aplico = !empty($aplicacion_existente);
}

// Tiempo desde publicación
$tiempo = human_time_diff(strtotime($orden->created_at), current_time('timestamp'));

// Número de aplicaciones
$num_apps = GA_Ordenes_Trabajo::count_aplicaciones($orden->id);

// Usar header del tema (o fallback del plugin si no está activo)
get_header();

// Imprimir estilos del portal (heredan colores del tema si está activo)
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container">
    <!-- Breadcrumb -->
    <nav class="ga-breadcrumb">
        <div class="ga-container">
            <a href="<?php echo esc_url(home_url('/trabajo/')); ?>">
                <?php esc_html_e('Oportunidades', 'gestionadmin-wolk'); ?>
            </a>
            <span class="ga-breadcrumb-sep">/</span>
            <span class="ga-breadcrumb-current"><?php echo esc_html($orden->codigo); ?></span>
        </div>
    </nav>

    <div class="ga-container ga-single-order">
        <!-- =========================================================================
             CONTENIDO PRINCIPAL
        ========================================================================== -->
        <main class="ga-single-order-main">
            <!-- Header de la orden -->
            <header class="ga-order-header">
                <div class="ga-order-header-meta">
                    <span class="ga-order-category">
                        <?php echo esc_html($categorias[$orden->categoria] ?? $orden->categoria); ?>
                    </span>
                    <span class="ga-order-code"><?php echo esc_html($orden->codigo); ?></span>
                    <?php if ($orden->prioridad === 'URGENTE') : ?>
                        <span class="ga-badge ga-badge-urgent"><?php esc_html_e('Urgente', 'gestionadmin-wolk'); ?></span>
                    <?php elseif ($orden->prioridad === 'ALTA') : ?>
                        <span class="ga-badge ga-badge-high"><?php esc_html_e('Prioridad Alta', 'gestionadmin-wolk'); ?></span>
                    <?php endif; ?>
                </div>

                <h1 class="ga-order-title"><?php echo esc_html($orden->titulo); ?></h1>

                <div class="ga-order-meta-row">
                    <span class="ga-meta-item">
                        <span class="dashicons dashicons-clock"></span>
                        <?php printf(esc_html__('Publicado hace %s', 'gestionadmin-wolk'), $tiempo); ?>
                    </span>
                    <span class="ga-meta-item">
                        <span class="dashicons dashicons-groups"></span>
                        <?php printf(esc_html(_n('%d aplicación', '%d aplicaciones', $num_apps, 'gestionadmin-wolk')), $num_apps); ?>
                    </span>
                    <?php if ($orden->fecha_limite_aplicacion) : ?>
                        <span class="ga-meta-item">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php printf(
                                esc_html__('Cierre: %s', 'gestionadmin-wolk'),
                                date_i18n('d M Y', strtotime($orden->fecha_limite_aplicacion))
                            ); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Descripción -->
            <section class="ga-order-section">
                <h2><?php esc_html_e('Descripción del Trabajo', 'gestionadmin-wolk'); ?></h2>
                <div class="ga-order-description">
                    <?php echo wp_kses_post(nl2br($orden->descripcion)); ?>
                </div>
            </section>

            <!-- Requisitos -->
            <?php if (!empty($orden->requisitos_adicionales) || !empty($habilidades)) : ?>
                <section class="ga-order-section">
                    <h2><?php esc_html_e('Requisitos', 'gestionadmin-wolk'); ?></h2>

                    <?php if (!empty($habilidades)) : ?>
                        <div class="ga-skills-list">
                            <h4><?php esc_html_e('Habilidades Requeridas:', 'gestionadmin-wolk'); ?></h4>
                            <div class="ga-order-skills">
                                <?php foreach ($habilidades as $skill) : ?>
                                    <span class="ga-skill-tag"><?php echo esc_html($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($orden->requisitos_adicionales)) : ?>
                        <div class="ga-additional-reqs">
                            <h4><?php esc_html_e('Requisitos Adicionales:', 'gestionadmin-wolk'); ?></h4>
                            <div class="ga-req-content">
                                <?php echo wp_kses_post(nl2br($orden->requisitos_adicionales)); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- Cliente (si está visible) -->
            <?php if (!empty($orden->cliente_nombre)) : ?>
                <section class="ga-order-section">
                    <h2><?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?></h2>
                    <div class="ga-client-info">
                        <strong><?php echo esc_html($orden->cliente_nombre); ?></strong>
                    </div>
                </section>
            <?php endif; ?>
        </main>

        <!-- =========================================================================
             SIDEBAR
        ========================================================================== -->
        <aside class="ga-single-order-sidebar">
            <!-- Card de aplicación -->
            <div class="ga-apply-card">
                <div class="ga-apply-card-header">
                    <div class="ga-budget-display">
                        <span class="ga-budget-label"><?php esc_html_e('Presupuesto', 'gestionadmin-wolk'); ?></span>
                        <span class="ga-budget-value"><?php echo esc_html($presupuesto); ?></span>
                    </div>
                </div>

                <div class="ga-apply-card-body">
                    <!-- Detalles rápidos -->
                    <ul class="ga-order-quick-details">
                        <li>
                            <span class="ga-detail-label"><?php esc_html_e('Modalidad', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-detail-value"><?php echo esc_html($modalidades[$orden->modalidad] ?? $orden->modalidad); ?></span>
                        </li>
                        <li>
                            <span class="ga-detail-label"><?php esc_html_e('Tipo de Pago', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-detail-value"><?php echo esc_html($tipos_pago[$orden->tipo_pago] ?? $orden->tipo_pago); ?></span>
                        </li>
                        <li>
                            <span class="ga-detail-label"><?php esc_html_e('Experiencia', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-detail-value"><?php echo esc_html($niveles[$orden->nivel_experiencia] ?? $orden->nivel_experiencia); ?></span>
                        </li>
                        <?php if (!empty($orden->ubicacion_requerida)) : ?>
                            <li>
                                <span class="ga-detail-label"><?php esc_html_e('Ubicación', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-detail-value"><?php echo esc_html($orden->ubicacion_requerida); ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if ($orden->duracion_estimada_dias) : ?>
                            <li>
                                <span class="ga-detail-label"><?php esc_html_e('Duración', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-detail-value"><?php printf(esc_html__('%d días', 'gestionadmin-wolk'), $orden->duracion_estimada_dias); ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if ($orden->fecha_inicio_estimada) : ?>
                            <li>
                                <span class="ga-detail-label"><?php esc_html_e('Inicio Estimado', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-detail-value"><?php echo esc_html(date_i18n('d M Y', strtotime($orden->fecha_inicio_estimada))); ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if (!empty($acuerdos)) : ?>
                        <!-- Compensación / Acuerdos Económicos -->
                        <div class="ga-compensation-section">
                            <h4 class="ga-compensation-title">
                                <span class="dashicons dashicons-money-alt"></span>
                                <?php esc_html_e('Compensación', 'gestionadmin-wolk'); ?>
                            </h4>
                            <ul class="ga-compensation-list">
                                <?php foreach ($acuerdos as $acuerdo) : ?>
                                    <li class="ga-compensation-item">
                                        <span class="ga-compensation-type"><?php echo esc_html($acuerdo['tipo_label']); ?></span>
                                        <span class="ga-compensation-value"><?php echo esc_html($acuerdo['valor_formateado']); ?></span>
                                        <span class="ga-compensation-freq"><?php echo esc_html($acuerdo['frecuencia']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="ga-apply-card-footer">
                    <?php if ($ya_aplico) : ?>
                        <div class="ga-already-applied">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Ya aplicaste a esta orden', 'gestionadmin-wolk'); ?>
                        </div>
                        <a href="<?php echo esc_url(home_url('/mi-cuenta/aplicaciones/')); ?>" class="ga-btn ga-btn-outline ga-btn-block">
                            <?php esc_html_e('Ver mis aplicaciones', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php elseif ($puede_aplicar) : ?>
                        <a href="<?php echo esc_url(GA_Public::get_aplicar_url($orden->codigo)); ?>" class="ga-btn ga-btn-primary ga-btn-block ga-btn-large">
                            <?php esc_html_e('Aplicar Ahora', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php elseif (is_user_logged_in()) : ?>
                        <?php if (!$aplicante) : ?>
                            <p class="ga-apply-notice">
                                <?php esc_html_e('Necesitas un perfil de aplicante para postularte.', 'gestionadmin-wolk'); ?>
                            </p>
                            <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>" class="ga-btn ga-btn-primary ga-btn-block">
                                <?php esc_html_e('Completar Registro', 'gestionadmin-wolk'); ?>
                            </a>
                        <?php else : ?>
                            <p class="ga-apply-notice ga-notice-warning">
                                <?php esc_html_e('Tu cuenta está pendiente de verificación.', 'gestionadmin-wolk'); ?>
                            </p>
                        <?php endif; ?>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="ga-btn ga-btn-primary ga-btn-block ga-btn-large">
                            <?php esc_html_e('Iniciar Sesión para Aplicar', 'gestionadmin-wolk'); ?>
                        </a>
                        <p class="ga-apply-notice">
                            <?php esc_html_e('¿No tienes cuenta?', 'gestionadmin-wolk'); ?>
                            <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>">
                                <?php esc_html_e('Regístrate gratis', 'gestionadmin-wolk'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Compartir -->
            <div class="ga-share-card">
                <h4><?php esc_html_e('Compartir', 'gestionadmin-wolk'); ?></h4>
                <div class="ga-share-buttons">
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>"
                       target="_blank" rel="noopener" class="ga-share-btn ga-share-linkedin" title="LinkedIn">
                        <span class="dashicons dashicons-linkedin"></span>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode($orden->titulo); ?>"
                       target="_blank" rel="noopener" class="ga-share-btn ga-share-twitter" title="Twitter">
                        <span class="dashicons dashicons-twitter"></span>
                    </a>
                    <button type="button" class="ga-share-btn ga-share-copy" title="<?php esc_attr_e('Copiar enlace', 'gestionadmin-wolk'); ?>"
                            data-url="<?php echo esc_url(get_permalink()); ?>">
                        <span class="dashicons dashicons-admin-links"></span>
                    </button>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php get_footer(); ?>
