<?php
/**
 * Template: Login GestionAdmin
 *
 * Página de login personalizada para el sistema.
 * Permite login a todos los tipos de usuarios: aplicantes, empleados, clientes.
 * Diseño limpio y centrado con colores heredados del tema.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/General
 * @since      1.3.0
 * @updated    1.6.0 - Integración con tema, diseño limpio
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Si ya está logueado, redirigir
if (is_user_logged_in()) {
    $redirect_to = home_url('/mi-cuenta/');

    // Detectar tipo de usuario para redirigir al portal correcto
    $user = wp_get_current_user();
    if (in_array('ga_cliente', (array) $user->roles)) {
        $redirect_to = home_url('/cliente/');
    } elseif (in_array('ga_empleado', (array) $user->roles) ||
              in_array('ga_jefe', (array) $user->roles) ||
              in_array('ga_director', (array) $user->roles)) {
        $redirect_to = home_url('/empleado/');
    }

    wp_redirect($redirect_to);
    exit;
}

// Procesar login
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_login_nonce'])) {
    if (wp_verify_nonce($_POST['ga_login_nonce'], 'ga_login_action')) {
        $creds = array(
            'user_login'    => sanitize_text_field($_POST['user_login']),
            'user_password' => $_POST['user_password'],
            'remember'      => !empty($_POST['remember']),
        );

        $user = wp_signon($creds, is_ssl());

        if (is_wp_error($user)) {
            $error_message = __('Credenciales incorrectas. Verifica tu usuario y contraseña.', 'gestionadmin-wolk');
        } else {
            // Redirigir según el tipo de usuario
            $redirect_to = home_url('/mi-cuenta/');

            if (in_array('ga_cliente', (array) $user->roles)) {
                $redirect_to = home_url('/cliente/');
            } elseif (in_array('ga_empleado', (array) $user->roles) ||
                      in_array('ga_jefe', (array) $user->roles) ||
                      in_array('ga_director', (array) $user->roles)) {
                $redirect_to = home_url('/empleado/');
            }

            wp_redirect($redirect_to);
            exit;
        }
    }
}

// Obtener colores del tema
$color_primary   = GA_Theme_Integration::get_color('primary', '#0056A6');
$color_secondary = GA_Theme_Integration::get_color('secondary', '#0891B2');
$color_dark      = GA_Theme_Integration::get_color('dark', '#1F2937');
$color_accent    = GA_Theme_Integration::get_color('accent', '#10B981');

// Obtener logo y nombre
$logo_url     = GA_Theme_Integration::get_logo_url();
$company_name = GA_Theme_Integration::get_company_name();

// Generar gradiente dinámico
$gradient_start = $color_primary;
$gradient_end   = $color_dark;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php esc_html_e('Iniciar Sesión', 'gestionadmin-wolk'); ?> - <?php echo esc_html($company_name); ?></title>
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

        body.ga-login-body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, <?php echo esc_attr($gradient_start); ?> 0%, <?php echo esc_attr($gradient_end); ?> 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           CONTENEDOR PRINCIPAL
           ============================================================ */
        .ga-auth-wrapper {
            width: 100%;
            max-width: 420px;
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
           TARJETA DE LOGIN
           ============================================================ */
        .ga-auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        /* Header con logo */
        .ga-auth-header {
            background: <?php echo esc_attr($color_primary); ?>;
            padding: 32px 40px;
            text-align: center;
        }

        .ga-auth-logo {
            margin-bottom: 0;
        }

        .ga-auth-logo img {
            max-height: 50px;
            max-width: 180px;
            height: auto;
        }

        .ga-auth-logo-text {
            color: #ffffff;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Cuerpo del formulario */
        .ga-auth-body {
            padding: 40px;
        }

        .ga-auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: <?php echo esc_attr($color_dark); ?>;
            margin-bottom: 8px;
            text-align: center;
        }

        .ga-auth-subtitle {
            font-size: 0.95rem;
            color: #6B7280;
            margin-bottom: 32px;
            text-align: center;
        }

        /* ============================================================
           ALERTAS
           ============================================================ */
        .ga-auth-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .ga-auth-alert-error {
            background: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .ga-auth-alert-success {
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }

        .ga-auth-alert .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* ============================================================
           FORMULARIO
           ============================================================ */
        .ga-auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .ga-auth-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .ga-auth-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: <?php echo esc_attr($color_dark); ?>;
        }

        .ga-auth-input {
            width: 100%;
            padding: 14px 16px;
            font-size: 1rem;
            color: <?php echo esc_attr($color_dark); ?>;
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            transition: all 0.2s ease;
            outline: none;
        }

        .ga-auth-input:focus {
            background: #ffffff;
            border-color: <?php echo esc_attr($color_primary); ?>;
            box-shadow: 0 0 0 4px <?php echo esc_attr($color_primary); ?>1A;
        }

        .ga-auth-input::placeholder {
            color: #9CA3AF;
        }

        /* Checkbox recordar */
        .ga-auth-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .ga-auth-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #6B7280;
        }

        .ga-auth-remember input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: <?php echo esc_attr($color_primary); ?>;
            cursor: pointer;
        }

        .ga-auth-forgot {
            font-size: 0.9rem;
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .ga-auth-forgot:hover {
            color: <?php echo esc_attr($color_dark); ?>;
            text-decoration: underline;
        }

        /* Botón submit */
        .ga-auth-submit {
            width: 100%;
            padding: 16px 24px;
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

        .ga-auth-submit:hover {
            background: <?php echo esc_attr($color_dark); ?>;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px <?php echo esc_attr($color_primary); ?>40;
        }

        .ga-auth-submit:active {
            transform: translateY(0);
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        .ga-auth-footer {
            padding: 24px 40px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            text-align: center;
        }

        .ga-auth-footer-text {
            font-size: 0.9rem;
            color: #6B7280;
        }

        .ga-auth-footer-link {
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        .ga-auth-footer-link:hover {
            text-decoration: underline;
        }

        /* ============================================================
           ENLACE VOLVER
           ============================================================ */
        .ga-auth-back {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .ga-auth-back a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .ga-auth-back a:hover {
            color: #ffffff;
        }

        .ga-auth-back .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 480px) {
            body.ga-login-body {
                padding: 16px;
            }

            .ga-auth-header {
                padding: 24px;
            }

            .ga-auth-body {
                padding: 24px;
            }

            .ga-auth-footer {
                padding: 20px 24px;
            }

            .ga-auth-title {
                font-size: 1.25rem;
            }

            .ga-auth-options {
                flex-direction: column;
                align-items: flex-start;
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
<body class="ga-login-body">
    <?php wp_body_open(); ?>

    <div class="ga-auth-wrapper">
        <div class="ga-auth-card">
            <!-- Header con Logo -->
            <div class="ga-auth-header">
                <div class="ga-auth-logo">
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>">
                    <?php else : ?>
                        <span class="ga-auth-logo-text"><?php echo esc_html($company_name); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cuerpo del formulario -->
            <div class="ga-auth-body">
                <h1 class="ga-auth-title"><?php esc_html_e('Bienvenido', 'gestionadmin-wolk'); ?></h1>
                <p class="ga-auth-subtitle"><?php esc_html_e('Ingresa tus credenciales para continuar', 'gestionadmin-wolk'); ?></p>

                <?php if ($error_message) : ?>
                    <div class="ga-auth-alert ga-auth-alert-error">
                        <span class="dashicons dashicons-warning"></span>
                        <span><?php echo esc_html($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success_message) : ?>
                    <div class="ga-auth-alert ga-auth-alert-success">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span><?php echo esc_html($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" class="ga-auth-form">
                    <?php wp_nonce_field('ga_login_action', 'ga_login_nonce'); ?>

                    <div class="ga-auth-field">
                        <label class="ga-auth-label" for="user_login">
                            <?php esc_html_e('Usuario o Email', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="text"
                               id="user_login"
                               name="user_login"
                               class="ga-auth-input"
                               placeholder="<?php esc_attr_e('nombre@ejemplo.com', 'gestionadmin-wolk'); ?>"
                               required
                               autocomplete="username"
                               value="<?php echo isset($_POST['user_login']) ? esc_attr($_POST['user_login']) : ''; ?>">
                    </div>

                    <div class="ga-auth-field">
                        <label class="ga-auth-label" for="user_password">
                            <?php esc_html_e('Contraseña', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="password"
                               id="user_password"
                               name="user_password"
                               class="ga-auth-input"
                               placeholder="<?php esc_attr_e('Tu contraseña', 'gestionadmin-wolk'); ?>"
                               required
                               autocomplete="current-password">
                    </div>

                    <div class="ga-auth-options">
                        <label class="ga-auth-remember">
                            <input type="checkbox" name="remember" value="1">
                            <?php esc_html_e('Recordarme', 'gestionadmin-wolk'); ?>
                        </label>
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="ga-auth-forgot">
                            <?php esc_html_e('¿Olvidaste tu contraseña?', 'gestionadmin-wolk'); ?>
                        </a>
                    </div>

                    <button type="submit" class="ga-auth-submit">
                        <?php esc_html_e('Iniciar Sesión', 'gestionadmin-wolk'); ?>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="ga-auth-footer">
                <p class="ga-auth-footer-text">
                    <?php esc_html_e('¿No tienes cuenta?', 'gestionadmin-wolk'); ?>
                    <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>" class="ga-auth-footer-link">
                        <?php esc_html_e('Regístrate aquí', 'gestionadmin-wolk'); ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Enlace volver -->
        <div class="ga-auth-back">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php esc_html_e('Volver al inicio', 'gestionadmin-wolk'); ?>
            </a>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
