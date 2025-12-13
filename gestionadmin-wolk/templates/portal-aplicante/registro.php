<?php
/**
 * Template: Registro de Aplicante
 *
 * Formulario público para registrarse como aplicante.
 *
 * URL: /registro-aplicante/
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Si ya está logueado, redirigir al dashboard
if (is_user_logged_in()) {
    $aplicante = GA_Public::get_current_aplicante();
    if ($aplicante) {
        wp_redirect(home_url('/mi-cuenta/'));
    } else {
        // Tiene cuenta pero no perfil de aplicante
        // Mostrar formulario para completar perfil
    }
}

// Obtener países
global $wpdb;
$paises = $wpdb->get_results("SELECT codigo_iso, nombre FROM {$wpdb->prefix}ga_paises_config WHERE activo = 1 ORDER BY nombre");

// Tipos de aplicante
$tipos = GA_Aplicantes::get_tipos();

get_header();
?>

<div class="ga-public-container ga-registro-page">
    <div class="ga-container">
        <div class="ga-registro-layout">
            <!-- =========================================================================
                 FORMULARIO DE REGISTRO
            ========================================================================== -->
            <main class="ga-registro-main">
                <header class="ga-registro-header">
                    <h1><?php esc_html_e('Únete a nuestra red de profesionales', 'gestionadmin-wolk'); ?></h1>
                    <p><?php esc_html_e('Crea tu cuenta para acceder a oportunidades de trabajo.', 'gestionadmin-wolk'); ?></p>
                </header>

                <form id="ga-form-registro" class="ga-form">
                    <?php wp_nonce_field('ga_public_nonce', 'nonce'); ?>
                    <input type="hidden" name="action" value="ga_public_registro_aplicante">

                    <!-- Tipo de cuenta -->
                    <div class="ga-form-group">
                        <label class="ga-form-label"><?php esc_html_e('Tipo de Cuenta', 'gestionadmin-wolk'); ?></label>
                        <div class="ga-radio-group">
                            <?php foreach ($tipos as $key => $label) : ?>
                                <label class="ga-radio-card">
                                    <input type="radio" name="tipo" value="<?php echo esc_attr($key); ?>"
                                           <?php checked($key, 'PERSONA_NATURAL'); ?>>
                                    <span class="ga-radio-content">
                                        <span class="ga-radio-icon">
                                            <?php if ($key === 'PERSONA_NATURAL') : ?>
                                                <span class="dashicons dashicons-admin-users"></span>
                                            <?php else : ?>
                                                <span class="dashicons dashicons-building"></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="ga-radio-label"><?php echo esc_html($label); ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Datos básicos -->
                    <div class="ga-form-section">
                        <h3><?php esc_html_e('Información Personal', 'gestionadmin-wolk'); ?></h3>

                        <div class="ga-form-group">
                            <label for="nombre_completo" class="ga-form-label">
                                <?php esc_html_e('Nombre Completo', 'gestionadmin-wolk'); ?> *
                            </label>
                            <input type="text" id="nombre_completo" name="nombre_completo"
                                   class="ga-form-control" required
                                   placeholder="<?php esc_attr_e('Tu nombre completo', 'gestionadmin-wolk'); ?>">
                        </div>

                        <div class="ga-form-row">
                            <div class="ga-form-group ga-form-group-half">
                                <label for="email" class="ga-form-label">
                                    <?php esc_html_e('Email', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="email" id="email" name="email"
                                       class="ga-form-control" required
                                       placeholder="tu@email.com">
                            </div>

                            <div class="ga-form-group ga-form-group-half">
                                <label for="telefono" class="ga-form-label">
                                    <?php esc_html_e('Teléfono', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="tel" id="telefono" name="telefono"
                                       class="ga-form-control"
                                       placeholder="+1 234 567 8900">
                            </div>
                        </div>

                        <div class="ga-form-group">
                            <label for="pais" class="ga-form-label">
                                <?php esc_html_e('País', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="pais" name="pais" class="ga-form-control">
                                <option value=""><?php esc_html_e('Selecciona tu país', 'gestionadmin-wolk'); ?></option>
                                <?php foreach ($paises as $p) : ?>
                                    <option value="<?php echo esc_attr($p->codigo_iso); ?>">
                                        <?php echo esc_html($p->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Perfil profesional -->
                    <div class="ga-form-section">
                        <h3><?php esc_html_e('Perfil Profesional', 'gestionadmin-wolk'); ?></h3>

                        <div class="ga-form-group">
                            <label for="titulo_profesional" class="ga-form-label">
                                <?php esc_html_e('Título o Especialidad', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="text" id="titulo_profesional" name="titulo_profesional"
                                   class="ga-form-control"
                                   placeholder="<?php esc_attr_e('Ej: Desarrollador Full Stack, Diseñador UX', 'gestionadmin-wolk'); ?>">
                        </div>
                    </div>

                    <!-- Credenciales de acceso -->
                    <div class="ga-form-section">
                        <h3><?php esc_html_e('Credenciales de Acceso', 'gestionadmin-wolk'); ?></h3>

                        <div class="ga-form-row">
                            <div class="ga-form-group ga-form-group-half">
                                <label for="password" class="ga-form-label">
                                    <?php esc_html_e('Contraseña', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="password" id="password" name="password"
                                       class="ga-form-control" required minlength="8"
                                       placeholder="<?php esc_attr_e('Mínimo 8 caracteres', 'gestionadmin-wolk'); ?>">
                            </div>

                            <div class="ga-form-group ga-form-group-half">
                                <label for="password_confirm" class="ga-form-label">
                                    <?php esc_html_e('Confirmar Contraseña', 'gestionadmin-wolk'); ?> *
                                </label>
                                <input type="password" id="password_confirm" name="password_confirm"
                                       class="ga-form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Términos -->
                    <div class="ga-form-group">
                        <label class="ga-checkbox">
                            <input type="checkbox" name="acepta_terminos" required>
                            <span>
                                <?php printf(
                                    esc_html__('Acepto los %1$sTérminos de Servicio%2$s y la %3$sPolítica de Privacidad%4$s', 'gestionadmin-wolk'),
                                    '<a href="#" target="_blank">', '</a>',
                                    '<a href="#" target="_blank">', '</a>'
                                ); ?>
                            </span>
                        </label>
                    </div>

                    <!-- Botón de registro -->
                    <div class="ga-form-actions">
                        <button type="submit" class="ga-btn ga-btn-primary ga-btn-large ga-btn-block" id="ga-btn-registro">
                            <?php esc_html_e('Crear Cuenta', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>

                    <!-- Mensajes -->
                    <div id="ga-form-messages" class="ga-form-messages" style="display: none;"></div>

                    <!-- Link a login -->
                    <p class="ga-login-link">
                        <?php esc_html_e('¿Ya tienes cuenta?', 'gestionadmin-wolk'); ?>
                        <a href="<?php echo esc_url(wp_login_url(home_url('/mi-cuenta/'))); ?>">
                            <?php esc_html_e('Inicia sesión', 'gestionadmin-wolk'); ?>
                        </a>
                    </p>
                </form>
            </main>

            <!-- =========================================================================
                 SIDEBAR CON BENEFICIOS
            ========================================================================== -->
            <aside class="ga-registro-sidebar">
                <div class="ga-benefits-card">
                    <h3><?php esc_html_e('¿Por qué unirte?', 'gestionadmin-wolk'); ?></h3>

                    <ul class="ga-benefits-list">
                        <li>
                            <span class="ga-benefit-icon"><span class="dashicons dashicons-search"></span></span>
                            <div class="ga-benefit-content">
                                <strong><?php esc_html_e('Acceso a Oportunidades', 'gestionadmin-wolk'); ?></strong>
                                <p><?php esc_html_e('Miles de proyectos de empresas verificadas.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        </li>
                        <li>
                            <span class="ga-benefit-icon"><span class="dashicons dashicons-money-alt"></span></span>
                            <div class="ga-benefit-content">
                                <strong><?php esc_html_e('Pagos Seguros', 'gestionadmin-wolk'); ?></strong>
                                <p><?php esc_html_e('Recibe pagos por Binance, Wise, PayPal y más.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        </li>
                        <li>
                            <span class="ga-benefit-icon"><span class="dashicons dashicons-star-filled"></span></span>
                            <div class="ga-benefit-content">
                                <strong><?php esc_html_e('Construye Reputación', 'gestionadmin-wolk'); ?></strong>
                                <p><?php esc_html_e('Calificaciones y portafolio verificado.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        </li>
                        <li>
                            <span class="ga-benefit-icon"><span class="dashicons dashicons-location"></span></span>
                            <div class="ga-benefit-content">
                                <strong><?php esc_html_e('Trabaja Remoto', 'gestionadmin-wolk'); ?></strong>
                                <p><?php esc_html_e('Proyectos desde cualquier lugar del mundo.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Testimonial -->
                <div class="ga-testimonial-card">
                    <blockquote>
                        "Encontré proyectos increíbles y ahora trabajo con clientes de varios países."
                    </blockquote>
                    <cite>— María G., Desarrolladora</cite>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ga-form-registro').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#ga-btn-registro');
        var $messages = $('#ga-form-messages');

        // Validar contraseñas coincidan
        var pass = $('#password').val();
        var passConfirm = $('#password_confirm').val();

        if (pass !== passConfirm) {
            $messages.removeClass('ga-success').addClass('ga-error')
                .text('<?php echo esc_js(__('Las contraseñas no coinciden.', 'gestionadmin-wolk')); ?>')
                .show();
            return;
        }

        if (pass.length < 8) {
            $messages.removeClass('ga-success').addClass('ga-error')
                .text('<?php echo esc_js(__('La contraseña debe tener al menos 8 caracteres.', 'gestionadmin-wolk')); ?>')
                .show();
            return;
        }

        $btn.prop('disabled', true).text(gaPublic.i18n.loading);
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
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Crear Cuenta', 'gestionadmin-wolk')); ?>');
            }
        }).fail(function() {
            $messages.removeClass('ga-success').addClass('ga-error')
                .text(gaPublic.i18n.error).show();
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Crear Cuenta', 'gestionadmin-wolk')); ?>');
        });
    });
});
</script>

<?php get_footer(); ?>
