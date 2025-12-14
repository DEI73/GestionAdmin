<?php
/**
 * Template: Formulario de Aplicación a Orden de Trabajo
 *
 * Permite a aplicantes verificados enviar su postulación.
 * Integrado con tema GestionAdmin Theme.
 *
 * URL: /trabajo/{codigo}/aplicar/
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalTrabajo
 * @since      1.3.0
 * @updated    1.6.0 - Integración con tema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar autenticación
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(home_url($_SERVER['REQUEST_URI'])));
    exit;
}

// Obtener código de la orden
$codigo = get_query_var('ga_codigo');
$orden = GA_Ordenes_Trabajo::get_by_codigo($codigo);

// Verificar que la orden existe y está publicada
if (!$orden || $orden->estado !== 'PUBLICADA') {
    wp_redirect(home_url('/trabajo/'));
    exit;
}

// Obtener datos completos
$orden = GA_Ordenes_Trabajo::get($orden->id);

// Verificar que el usuario es aplicante verificado
$aplicante = GA_Public::get_current_aplicante();

if (!$aplicante) {
    wp_redirect(home_url('/registro-aplicante/'));
    exit;
}

if ($aplicante->estado !== 'VERIFICADO') {
    // Redirigir con mensaje de error
    wp_redirect(add_query_arg('error', 'not_verified', home_url('/mi-cuenta/')));
    exit;
}

// Verificar que no haya aplicado ya
$aplicacion_existente = GA_Aplicaciones::get_by_orden_aplicante($orden->id, $aplicante->id);
if ($aplicacion_existente) {
    wp_redirect(home_url('/mi-cuenta/aplicaciones/'));
    exit;
}

// Enums
$tipos_pago = GA_Ordenes_Trabajo::get_tipos_pago();

// Presupuesto formateado
$presupuesto = '';
if ($orden->presupuesto_min && $orden->presupuesto_max) {
    $presupuesto = '$' . number_format($orden->presupuesto_min, 0) . ' - $' . number_format($orden->presupuesto_max, 0);
} elseif ($orden->presupuesto_max) {
    $presupuesto = 'Hasta $' . number_format($orden->presupuesto_max, 0);
} elseif ($orden->presupuesto_min) {
    $presupuesto = 'Desde $' . number_format($orden->presupuesto_min, 0);
}

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
            <a href="<?php echo esc_url(GA_Public::get_orden_url($orden->codigo)); ?>">
                <?php echo esc_html($orden->codigo); ?>
            </a>
            <span class="ga-breadcrumb-sep">/</span>
            <span class="ga-breadcrumb-current"><?php esc_html_e('Aplicar', 'gestionadmin-wolk'); ?></span>
        </div>
    </nav>

    <div class="ga-container ga-apply-page">
        <div class="ga-apply-layout">
            <!-- =========================================================================
                 FORMULARIO
            ========================================================================== -->
            <main class="ga-apply-form-section">
                <header class="ga-apply-header">
                    <h1><?php esc_html_e('Enviar Aplicación', 'gestionadmin-wolk'); ?></h1>
                    <p class="ga-apply-subtitle">
                        <?php printf(
                            esc_html__('Estás aplicando a: %s', 'gestionadmin-wolk'),
                            '<strong>' . esc_html($orden->titulo) . '</strong>'
                        ); ?>
                    </p>
                </header>

                <form id="ga-form-aplicar" class="ga-form">
                    <?php wp_nonce_field('ga_public_nonce', 'nonce'); ?>
                    <input type="hidden" name="action" value="ga_public_aplicar">
                    <input type="hidden" name="orden_id" value="<?php echo esc_attr($orden->id); ?>">

                    <!-- Carta de presentación -->
                    <div class="ga-form-group">
                        <label for="carta_presentacion" class="ga-form-label">
                            <?php esc_html_e('Carta de Presentación', 'gestionadmin-wolk'); ?> *
                        </label>
                        <textarea id="carta_presentacion" name="carta_presentacion" rows="8"
                                  class="ga-form-control" required
                                  placeholder="<?php esc_attr_e('Describe por qué eres el candidato ideal para este trabajo. Incluye experiencia relevante, proyectos similares realizados y cualquier información que destaque tu perfil.', 'gestionadmin-wolk'); ?>"></textarea>
                        <p class="ga-form-help">
                            <?php esc_html_e('Mínimo 100 caracteres. Sé específico sobre tu experiencia relevante.', 'gestionadmin-wolk'); ?>
                        </p>
                    </div>

                    <!-- Propuesta económica -->
                    <?php if ($orden->tipo_pago !== 'A_CONVENIR' || $presupuesto) : ?>
                        <div class="ga-form-row">
                            <div class="ga-form-group ga-form-group-half">
                                <label for="propuesta_monto" class="ga-form-label">
                                    <?php
                                    if ($orden->tipo_pago === 'POR_HORA') {
                                        esc_html_e('Tu Tarifa por Hora (USD)', 'gestionadmin-wolk');
                                    } else {
                                        esc_html_e('Tu Propuesta (USD)', 'gestionadmin-wolk');
                                    }
                                    ?>
                                </label>
                                <div class="ga-input-group">
                                    <span class="ga-input-prefix">$</span>
                                    <input type="number" id="propuesta_monto" name="propuesta_monto"
                                           class="ga-form-control" min="0" step="0.01"
                                           placeholder="0.00">
                                </div>
                                <?php if ($presupuesto) : ?>
                                    <p class="ga-form-help">
                                        <?php printf(esc_html__('Presupuesto del cliente: %s', 'gestionadmin-wolk'), $presupuesto); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="ga-form-group ga-form-group-half">
                                <label for="propuesta_tiempo" class="ga-form-label">
                                    <?php esc_html_e('Tiempo Estimado de Entrega', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="propuesta_tiempo" name="propuesta_tiempo"
                                       class="ga-form-control"
                                       placeholder="<?php esc_attr_e('Ej: 2 semanas, 30 días', 'gestionadmin-wolk'); ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Disponibilidad -->
                    <div class="ga-form-group">
                        <label for="disponibilidad" class="ga-form-label">
                            <?php esc_html_e('¿Cuándo puedes empezar?', 'gestionadmin-wolk'); ?>
                        </label>
                        <select id="disponibilidad" name="disponibilidad" class="ga-form-control">
                            <option value="inmediato"><?php esc_html_e('Inmediatamente', 'gestionadmin-wolk'); ?></option>
                            <option value="1_semana"><?php esc_html_e('En 1 semana', 'gestionadmin-wolk'); ?></option>
                            <option value="2_semanas"><?php esc_html_e('En 2 semanas', 'gestionadmin-wolk'); ?></option>
                            <option value="1_mes"><?php esc_html_e('En 1 mes', 'gestionadmin-wolk'); ?></option>
                            <option value="a_convenir"><?php esc_html_e('A convenir', 'gestionadmin-wolk'); ?></option>
                        </select>
                    </div>

                    <!-- Información del perfil (readonly) -->
                    <div class="ga-profile-summary">
                        <h4><?php esc_html_e('Tu Perfil', 'gestionadmin-wolk'); ?></h4>
                        <div class="ga-profile-summary-content">
                            <p><strong><?php echo esc_html($aplicante->nombre_completo); ?></strong></p>
                            <p><?php echo esc_html($aplicante->titulo_profesional ?: __('Profesional', 'gestionadmin-wolk')); ?></p>
                            <?php if ($aplicante->calificacion_promedio) : ?>
                                <p class="ga-rating">
                                    <?php echo esc_html(number_format($aplicante->calificacion_promedio, 1)); ?> ★
                                    (<?php printf(esc_html__('%d trabajos', 'gestionadmin-wolk'), $aplicante->trabajos_completados); ?>)
                                </p>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo esc_url(home_url('/mi-cuenta/perfil/')); ?>" class="ga-link" target="_blank">
                            <?php esc_html_e('Editar perfil', 'gestionadmin-wolk'); ?> →
                        </a>
                    </div>

                    <!-- Botones -->
                    <div class="ga-form-actions">
                        <a href="<?php echo esc_url(GA_Public::get_orden_url($orden->codigo)); ?>" class="ga-btn ga-btn-outline">
                            <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                        </a>
                        <button type="submit" class="ga-btn ga-btn-primary ga-btn-large" id="ga-btn-aplicar">
                            <?php esc_html_e('Enviar Aplicación', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>

                    <!-- Mensajes -->
                    <div id="ga-form-messages" class="ga-form-messages" style="display: none;"></div>
                </form>
            </main>

            <!-- =========================================================================
                 RESUMEN DE LA ORDEN
            ========================================================================== -->
            <aside class="ga-apply-sidebar">
                <div class="ga-order-summary-card">
                    <h3><?php esc_html_e('Resumen de la Orden', 'gestionadmin-wolk'); ?></h3>

                    <div class="ga-order-summary-title">
                        <?php echo esc_html($orden->titulo); ?>
                    </div>

                    <ul class="ga-order-summary-details">
                        <li>
                            <span class="ga-label"><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-value"><?php echo esc_html($orden->codigo); ?></span>
                        </li>
                        <?php if ($presupuesto) : ?>
                            <li>
                                <span class="ga-label"><?php esc_html_e('Presupuesto', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-value"><?php echo esc_html($presupuesto); ?></span>
                            </li>
                        <?php endif; ?>
                        <li>
                            <span class="ga-label"><?php esc_html_e('Tipo de Pago', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-value"><?php echo esc_html($tipos_pago[$orden->tipo_pago] ?? $orden->tipo_pago); ?></span>
                        </li>
                        <?php if ($orden->duracion_estimada_dias) : ?>
                            <li>
                                <span class="ga-label"><?php esc_html_e('Duración', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-value"><?php printf(esc_html__('%d días', 'gestionadmin-wolk'), $orden->duracion_estimada_dias); ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if (!empty($orden->cliente_nombre)) : ?>
                        <div class="ga-client-badge">
                            <?php esc_html_e('Cliente:', 'gestionadmin-wolk'); ?>
                            <strong><?php echo esc_html($orden->cliente_nombre); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tips -->
                <div class="ga-tips-card">
                    <h4><?php esc_html_e('Consejos para una buena aplicación', 'gestionadmin-wolk'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Personaliza tu carta de presentación para este trabajo específico.', 'gestionadmin-wolk'); ?></li>
                        <li><?php esc_html_e('Menciona proyectos similares que hayas realizado.', 'gestionadmin-wolk'); ?></li>
                        <li><?php esc_html_e('Sé realista con tu propuesta de tiempo y precio.', 'gestionadmin-wolk'); ?></li>
                        <li><?php esc_html_e('Asegúrate de que tu perfil esté completo y actualizado.', 'gestionadmin-wolk'); ?></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ga-form-aplicar').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#ga-btn-aplicar');
        var $messages = $('#ga-form-messages');

        // Validar carta mínima
        var carta = $('#carta_presentacion').val();
        if (carta.length < 100) {
            $messages.removeClass('ga-success').addClass('ga-error')
                .text('<?php echo esc_js(__('La carta de presentación debe tener al menos 100 caracteres.', 'gestionadmin-wolk')); ?>')
                .show();
            return;
        }

        $btn.prop('disabled', true).text(gaPublic.i18n.sending);
        $messages.hide();

        $.post(gaPublic.ajaxUrl, $form.serialize(), function(response) {
            if (response.success) {
                $messages.removeClass('ga-error').addClass('ga-success')
                    .text(response.data.message).show();

                setTimeout(function() {
                    window.location.href = response.data.redirect_to;
                }, 1500);
            } else {
                $messages.removeClass('ga-success').addClass('ga-error')
                    .text(response.data.message).show();
                $btn.prop('disabled', false).text(gaPublic.i18n.aplicarBtn);
            }
        }).fail(function() {
            $messages.removeClass('ga-success').addClass('ga-error')
                .text(gaPublic.i18n.error).show();
            $btn.prop('disabled', false).text(gaPublic.i18n.aplicarBtn);
        });
    });
});
</script>

<?php get_footer(); ?>
