<?php
/**
 * Template: Login Requerido
 *
 * Se muestra cuando un usuario no autenticado intenta acceder
 * a una sección que requiere login.
 * Diseño limpio y centrado con colores heredados del tema.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 * @updated    1.6.0 - Integración con tema, diseño profesional
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener colores del tema
$color_primary   = GA_Theme_Integration::get_color('primary', '#0056A6');
$color_secondary = GA_Theme_Integration::get_color('secondary', '#0891B2');
$color_dark      = GA_Theme_Integration::get_color('dark', '#1F2937');
$color_accent    = GA_Theme_Integration::get_color('accent', '#10B981');

// Obtener logo y nombre
$logo_url     = GA_Theme_Integration::get_logo_url();
$company_name = GA_Theme_Integration::get_company_name();

// URL actual para redirección después del login
$current_url = home_url($_SERVER['REQUEST_URI']);
$login_url   = home_url('/acceso/');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php esc_html_e('Acceso Requerido', 'gestionadmin-wolk'); ?> - <?php echo esc_html($company_name); ?></title>
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

        body.ga-auth-required-body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, <?php echo esc_attr($color_primary); ?> 0%, <?php echo esc_attr($color_dark); ?> 100%);
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
            max-width: 440px;
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
           TARJETA
           ============================================================ */
        .ga-auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        /* Header con logo */
        .ga-auth-header {
            background: <?php echo esc_attr($color_primary); ?>;
            padding: 32px 40px;
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

        /* Cuerpo */
        .ga-auth-body {
            padding: 50px 40px;
        }

        .ga-auth-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, <?php echo esc_attr($color_primary); ?>15, <?php echo esc_attr($color_primary); ?>30);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ga-auth-icon .dashicons {
            font-size: 36px;
            width: 36px;
            height: 36px;
            color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: <?php echo esc_attr($color_dark); ?>;
            margin-bottom: 12px;
        }

        .ga-auth-message {
            font-size: 1rem;
            color: #6B7280;
            margin-bottom: 32px;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Botones */
        .ga-auth-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ga-auth-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 32px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .ga-auth-btn-primary {
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
            border-color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-auth-btn-primary:hover {
            background: <?php echo esc_attr($color_dark); ?>;
            border-color: <?php echo esc_attr($color_dark); ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px <?php echo esc_attr($color_primary); ?>40;
        }

        .ga-auth-btn-outline {
            background: transparent;
            color: <?php echo esc_attr($color_primary); ?>;
            border-color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-auth-btn-outline:hover {
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
        }

        .ga-auth-btn .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
        }

        /* Footer */
        .ga-auth-footer {
            padding: 20px 40px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
        }

        .ga-auth-footer-text {
            font-size: 0.875rem;
            color: #6B7280;
        }

        .ga-auth-footer-link {
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 600;
        }

        .ga-auth-footer-link:hover {
            text-decoration: underline;
        }

        /* Enlace volver */
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
            body.ga-auth-required-body {
                padding: 16px;
            }

            .ga-auth-header {
                padding: 24px;
            }

            .ga-auth-body {
                padding: 32px 24px;
            }

            .ga-auth-footer {
                padding: 16px 24px;
            }

            .ga-auth-title {
                font-size: 1.25rem;
            }

            .ga-auth-icon {
                width: 64px;
                height: 64px;
            }

            .ga-auth-icon .dashicons {
                font-size: 28px;
                width: 28px;
                height: 28px;
            }
        }

        /* Ocultar elementos de WordPress */
        #wpadminbar {
            display: none !important;
        }

        html {
            margin-top: 0 !important;
        }
    </style>
</head>
<body class="ga-auth-required-body">
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

            <!-- Cuerpo -->
            <div class="ga-auth-body">
                <div class="ga-auth-icon">
                    <span class="dashicons dashicons-lock"></span>
                </div>

                <h1 class="ga-auth-title"><?php esc_html_e('Acceso Restringido', 'gestionadmin-wolk'); ?></h1>

                <p class="ga-auth-message">
                    <?php esc_html_e('Debes iniciar sesión para acceder a esta sección.', 'gestionadmin-wolk'); ?>
                </p>

                <div class="ga-auth-actions">
                    <a href="<?php echo esc_url($login_url); ?>" class="ga-auth-btn ga-auth-btn-primary">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php esc_html_e('Iniciar Sesión', 'gestionadmin-wolk'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>" class="ga-auth-btn ga-auth-btn-outline">
                        <?php esc_html_e('Crear una cuenta', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="ga-auth-footer">
                <p class="ga-auth-footer-text">
                    <?php esc_html_e('¿Necesitas ayuda?', 'gestionadmin-wolk'); ?>
                    <a href="<?php echo esc_url(home_url('/contacto/')); ?>" class="ga-auth-footer-link">
                        <?php esc_html_e('Contáctanos', 'gestionadmin-wolk'); ?>
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
