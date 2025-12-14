<?php
/**
 * Template: Registro de Aplicante
 *
 * Formulario público para registrarse como aplicante.
 * Diseño limpio y centrado con colores heredados del tema.
 *
 * URL: /registro-aplicante/
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 * @updated    1.6.0 - Integración con tema, diseño profesional
 */

if (!defined('ABSPATH')) {
    exit;
}

// Si ya está logueado y tiene perfil aplicante, redirigir al dashboard
if (is_user_logged_in()) {
    $aplicante = GA_Public::get_current_aplicante();
    if ($aplicante) {
        wp_redirect(home_url('/mi-cuenta/'));
        exit;
    }
}

// Obtener países
global $wpdb;
$paises = $wpdb->get_results("SELECT codigo_iso, nombre FROM {$wpdb->prefix}ga_paises_config WHERE activo = 1 ORDER BY nombre");

// Tipos de aplicante
$tipos = GA_Aplicantes::get_tipos();

// Obtener colores del tema
$color_primary   = GA_Theme_Integration::get_color('primary', '#0056A6');
$color_secondary = GA_Theme_Integration::get_color('secondary', '#0891B2');
$color_dark      = GA_Theme_Integration::get_color('dark', '#1F2937');
$color_accent    = GA_Theme_Integration::get_color('accent', '#10B981');

// Obtener logo y nombre
$logo_url     = GA_Theme_Integration::get_logo_url();
$company_name = GA_Theme_Integration::get_company_name();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php esc_html_e('Registro de Aplicante', 'gestionadmin-wolk'); ?> - <?php echo esc_html($company_name); ?></title>
    <?php wp_head(); ?>
    <style>
        /* ============================================================
           RESET Y BASE
           ============================================================ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body.ga-registro-body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, <?php echo esc_attr($color_primary); ?> 0%, <?php echo esc_attr($color_dark); ?> 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           CONTENEDOR PRINCIPAL
           ============================================================ */
        .ga-registro-wrapper {
            width: 100%;
            max-width: 520px;
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================================
           TARJETA DE REGISTRO
           ============================================================ */
        .ga-registro-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        /* Header con logo */
        .ga-registro-header {
            background: <?php echo esc_attr($color_primary); ?>;
            padding: 28px 40px;
            text-align: center;
        }

        .ga-registro-logo img {
            max-height: 45px;
            max-width: 160px;
            height: auto;
        }

        .ga-registro-logo-text {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Cuerpo del formulario */
        .ga-registro-body {
            padding: 36px 40px;
        }

        .ga-registro-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: <?php echo esc_attr($color_dark); ?>;
            margin-bottom: 6px;
            text-align: center;
        }

        .ga-registro-subtitle {
            font-size: 0.9rem;
            color: #6B7280;
            margin-bottom: 28px;
            text-align: center;
        }

        /* ============================================================
           ALERTAS
           ============================================================ */
        .ga-registro-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .ga-registro-alert-error {
            background: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .ga-registro-alert-success {
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }

        .ga-registro-alert .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* ============================================================
           FORMULARIO
           ============================================================ */
        .ga-registro-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Sección del formulario */
        .ga-form-section {
            border-top: 1px solid #E5E7EB;
            padding-top: 20px;
            margin-top: 4px;
        }

        .ga-form-section-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: <?php echo esc_attr($color_primary); ?>;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
        }

        .ga-registro-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .ga-registro-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: <?php echo esc_attr($color_dark); ?>;
        }

        .ga-registro-label .required {
            color: #DC2626;
        }

        .ga-registro-input,
        .ga-registro-select {
            width: 100%;
            padding: 12px 14px;
            font-size: 0.95rem;
            color: <?php echo esc_attr($color_dark); ?>;
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            transition: all 0.2s ease;
            outline: none;
        }

        .ga-registro-input:focus,
        .ga-registro-select:focus {
            background: #ffffff;
            border-color: <?php echo esc_attr($color_primary); ?>;
            box-shadow: 0 0 0 4px <?php echo esc_attr($color_primary); ?>1A;
        }

        .ga-registro-input::placeholder {
            color: #9CA3AF;
        }

        /* Filas de campos */
        .ga-registro-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Tipo de cuenta (radio cards) */
        .ga-tipo-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .ga-tipo-card {
            position: relative;
            cursor: pointer;
        }

        .ga-tipo-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .ga-tipo-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px;
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .ga-tipo-card input:checked + .ga-tipo-content {
            background: <?php echo esc_attr($color_primary); ?>10;
            border-color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-tipo-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: <?php echo esc_attr($color_primary); ?>15;
            border-radius: 50%;
            color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-tipo-icon .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
        }

        .ga-tipo-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: <?php echo esc_attr($color_dark); ?>;
            text-align: center;
        }

        /* Checkbox términos */
        .ga-registro-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            font-size: 0.85rem;
            color: #6B7280;
            line-height: 1.5;
        }

        .ga-registro-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: <?php echo esc_attr($color_primary); ?>;
            cursor: pointer;
            flex-shrink: 0;
        }

        .ga-registro-checkbox a {
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 500;
        }

        .ga-registro-checkbox a:hover {
            text-decoration: underline;
        }

        /* Botón submit */
        .ga-registro-submit {
            width: 100%;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background: <?php echo esc_attr($color_primary); ?>;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }

        .ga-registro-submit:hover {
            background: <?php echo esc_attr($color_dark); ?>;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px <?php echo esc_attr($color_primary); ?>40;
        }

        .ga-registro-submit:active {
            transform: translateY(0);
        }

        .ga-registro-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        .ga-registro-footer {
            padding: 20px 40px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            text-align: center;
        }

        .ga-registro-footer-text {
            font-size: 0.9rem;
            color: #6B7280;
        }

        .ga-registro-footer-link {
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        .ga-registro-footer-link:hover {
            text-decoration: underline;
        }

        /* ============================================================
           ENLACE VOLVER
           ============================================================ */
        .ga-registro-back {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .ga-registro-back a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .ga-registro-back a:hover {
            color: #ffffff;
        }

        .ga-registro-back .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 560px) {
            body.ga-registro-body {
                padding: 24px 16px;
            }

            .ga-registro-header {
                padding: 20px 24px;
            }

            .ga-registro-body {
                padding: 28px 24px;
            }

            .ga-registro-footer {
                padding: 16px 24px;
            }

            .ga-registro-title {
                font-size: 1.2rem;
            }

            .ga-registro-row {
                grid-template-columns: 1fr;
            }

            .ga-tipo-selector {
                grid-template-columns: 1fr;
            }
        }

        /* Ocultar elementos de WordPress si aparecen */
        #wpadminbar {
            display: none !important;
        }

        html {
            margin-top: 0 !important;
        }
    </style>
</head>
<body class="ga-registro-body">
    <?php wp_body_open(); ?>

    <div class="ga-registro-wrapper">
        <div class="ga-registro-card">
            <!-- Header con Logo -->
            <div class="ga-registro-header">
                <div class="ga-registro-logo">
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>">
                    <?php else : ?>
                        <span class="ga-registro-logo-text"><?php echo esc_html($company_name); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cuerpo del formulario -->
            <div class="ga-registro-body">
                <h1 class="ga-registro-title"><?php esc_html_e('Crea tu Cuenta', 'gestionadmin-wolk'); ?></h1>
                <p class="ga-registro-subtitle"><?php esc_html_e('Únete y accede a oportunidades de trabajo', 'gestionadmin-wolk'); ?></p>

                <!-- Mensajes -->
                <div id="ga-form-messages" class="ga-registro-alert" style="display: none;"></div>

                <form id="ga-form-registro" class="ga-registro-form">
                    <?php wp_nonce_field('ga_public_nonce', 'nonce'); ?>
                    <input type="hidden" name="action" value="ga_public_registro_aplicante">

                    <!-- Tipo de cuenta -->
                    <div class="ga-registro-field">
                        <label class="ga-registro-label"><?php esc_html_e('Tipo de Cuenta', 'gestionadmin-wolk'); ?></label>
                        <div class="ga-tipo-selector">
                            <?php foreach ($tipos as $key => $label) : ?>
                                <label class="ga-tipo-card">
                                    <input type="radio" name="tipo" value="<?php echo esc_attr($key); ?>"
                                           <?php checked($key, 'PERSONA_NATURAL'); ?>>
                                    <span class="ga-tipo-content">
                                        <span class="ga-tipo-icon">
                                            <?php if ($key === 'PERSONA_NATURAL') : ?>
                                                <span class="dashicons dashicons-admin-users"></span>
                                            <?php else : ?>
                                                <span class="dashicons dashicons-building"></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="ga-tipo-label"><?php echo esc_html($label); ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div class="ga-form-section">
                        <div class="ga-form-section-title"><?php esc_html_e('Información Personal', 'gestionadmin-wolk'); ?></div>

                        <div class="ga-registro-field" style="margin-bottom: 16px;">
                            <label for="nombre_completo" class="ga-registro-label">
                                <?php esc_html_e('Nombre Completo', 'gestionadmin-wolk'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" id="nombre_completo" name="nombre_completo"
                                   class="ga-registro-input" required
                                   placeholder="<?php esc_attr_e('Tu nombre completo', 'gestionadmin-wolk'); ?>">
                        </div>

                        <div class="ga-registro-row" style="margin-bottom: 16px;">
                            <div class="ga-registro-field">
                                <label for="email" class="ga-registro-label">
                                    <?php esc_html_e('Email', 'gestionadmin-wolk'); ?> <span class="required">*</span>
                                </label>
                                <input type="email" id="email" name="email"
                                       class="ga-registro-input" required
                                       placeholder="tu@email.com"
                                       autocomplete="email">
                            </div>

                            <div class="ga-registro-field">
                                <label for="telefono" class="ga-registro-label">
                                    <?php esc_html_e('Teléfono', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="tel" id="telefono" name="telefono"
                                       class="ga-registro-input"
                                       placeholder="+1 234 567 8900">
                            </div>
                        </div>

                        <div class="ga-registro-row">
                            <div class="ga-registro-field">
                                <label for="pais" class="ga-registro-label">
                                    <?php esc_html_e('País', 'gestionadmin-wolk'); ?>
                                </label>
                                <select id="pais" name="pais" class="ga-registro-select">
                                    <option value=""><?php esc_html_e('Selecciona', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($paises as $p) : ?>
                                        <option value="<?php echo esc_attr($p->codigo_iso); ?>">
                                            <?php echo esc_html($p->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="ga-registro-field">
                                <label for="titulo_profesional" class="ga-registro-label">
                                    <?php esc_html_e('Especialidad', 'gestionadmin-wolk'); ?>
                                </label>
                                <input type="text" id="titulo_profesional" name="titulo_profesional"
                                       class="ga-registro-input"
                                       placeholder="<?php esc_attr_e('Ej: Desarrollador', 'gestionadmin-wolk'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Credenciales de Acceso -->
                    <div class="ga-form-section">
                        <div class="ga-form-section-title"><?php esc_html_e('Credenciales de Acceso', 'gestionadmin-wolk'); ?></div>

                        <div class="ga-registro-row">
                            <div class="ga-registro-field">
                                <label for="password" class="ga-registro-label">
                                    <?php esc_html_e('Contraseña', 'gestionadmin-wolk'); ?> <span class="required">*</span>
                                </label>
                                <input type="password" id="password" name="password"
                                       class="ga-registro-input" required minlength="8"
                                       placeholder="<?php esc_attr_e('Mín. 8 caracteres', 'gestionadmin-wolk'); ?>"
                                       autocomplete="new-password">
                            </div>

                            <div class="ga-registro-field">
                                <label for="password_confirm" class="ga-registro-label">
                                    <?php esc_html_e('Confirmar', 'gestionadmin-wolk'); ?> <span class="required">*</span>
                                </label>
                                <input type="password" id="password_confirm" name="password_confirm"
                                       class="ga-registro-input" required
                                       placeholder="<?php esc_attr_e('Repetir contraseña', 'gestionadmin-wolk'); ?>"
                                       autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <!-- Términos -->
                    <label class="ga-registro-checkbox">
                        <input type="checkbox" name="acepta_terminos" required>
                        <span>
                            <?php printf(
                                esc_html__('Acepto los %1$sTérminos%2$s y la %3$sPolítica de Privacidad%4$s', 'gestionadmin-wolk'),
                                '<a href="' . esc_url(home_url('/terminos/')) . '" target="_blank">', '</a>',
                                '<a href="' . esc_url(home_url('/privacidad/')) . '" target="_blank">', '</a>'
                            ); ?>
                        </span>
                    </label>

                    <!-- Botón de registro -->
                    <button type="submit" class="ga-registro-submit" id="ga-btn-registro">
                        <?php esc_html_e('Crear Cuenta', 'gestionadmin-wolk'); ?>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="ga-registro-footer">
                <p class="ga-registro-footer-text">
                    <?php esc_html_e('¿Ya tienes cuenta?', 'gestionadmin-wolk'); ?>
                    <a href="<?php echo esc_url(home_url('/acceso/')); ?>" class="ga-registro-footer-link">
                        <?php esc_html_e('Inicia sesión', 'gestionadmin-wolk'); ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Enlace volver -->
        <div class="ga-registro-back">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php esc_html_e('Volver al inicio', 'gestionadmin-wolk'); ?>
            </a>
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
                $messages.removeClass('ga-registro-alert-success').addClass('ga-registro-alert-error')
                    .html('<span class="dashicons dashicons-warning"></span><span><?php echo esc_js(__('Las contraseñas no coinciden.', 'gestionadmin-wolk')); ?></span>')
                    .show();
                return;
            }

            if (pass.length < 8) {
                $messages.removeClass('ga-registro-alert-success').addClass('ga-registro-alert-error')
                    .html('<span class="dashicons dashicons-warning"></span><span><?php echo esc_js(__('La contraseña debe tener al menos 8 caracteres.', 'gestionadmin-wolk')); ?></span>')
                    .show();
                return;
            }

            var originalText = $btn.text();
            $btn.prop('disabled', true).text('<?php echo esc_js(__('Creando cuenta...', 'gestionadmin-wolk')); ?>');
            $messages.hide();

            $.post(gaPublic.ajaxUrl, $form.serialize(), function(response) {
                if (response.success) {
                    $messages.removeClass('ga-registro-alert-error').addClass('ga-registro-alert-success')
                        .html('<span class="dashicons dashicons-yes-alt"></span><span>' + response.data.message + '</span>').show();

                    setTimeout(function() {
                        window.location.href = response.data.redirect_to;
                    }, 1500);
                } else {
                    $messages.removeClass('ga-registro-alert-success').addClass('ga-registro-alert-error')
                        .html('<span class="dashicons dashicons-warning"></span><span>' + response.data.message + '</span>').show();
                    $btn.prop('disabled', false).text(originalText);
                }
            }).fail(function() {
                $messages.removeClass('ga-registro-alert-success').addClass('ga-registro-alert-error')
                    .html('<span class="dashicons dashicons-warning"></span><span><?php echo esc_js(__('Error de conexión. Intenta de nuevo.', 'gestionadmin-wolk')); ?></span>').show();
                $btn.prop('disabled', false).text(originalText);
            });
        });
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
