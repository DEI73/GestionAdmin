<?php
/**
 * Template: No es Aplicante
 *
 * Se muestra cuando un usuario logueado no tiene perfil de aplicante.
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
$color_warning   = '#F59E0B';

// Obtener logo y nombre
$logo_url     = GA_Theme_Integration::get_logo_url();
$company_name = GA_Theme_Integration::get_company_name();

// Obtener datos del usuario actual
$current_user = wp_get_current_user();
$user_name = $current_user->display_name ?: $current_user->user_login;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php esc_html_e('Completa tu Perfil', 'gestionadmin-wolk'); ?> - <?php echo esc_html($company_name); ?></title>
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

        body.ga-no-aplicante-body {
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
        .ga-no-aplicante-wrapper {
            width: 100%;
            max-width: 460px;
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
        .ga-no-aplicante-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        /* Header con logo */
        .ga-no-aplicante-header {
            background: <?php echo esc_attr($color_primary); ?>;
            padding: 28px 40px;
        }

        .ga-no-aplicante-logo img {
            max-height: 45px;
            max-width: 160px;
            height: auto;
        }

        .ga-no-aplicante-logo-text {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Cuerpo */
        .ga-no-aplicante-body {
            padding: 48px 40px;
        }

        .ga-no-aplicante-icon {
            width: 88px;
            height: 88px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, <?php echo esc_attr($color_warning); ?>15, <?php echo esc_attr($color_warning); ?>30);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ga-no-aplicante-icon .dashicons {
            font-size: 40px;
            width: 40px;
            height: 40px;
            color: <?php echo esc_attr($color_warning); ?>;
        }

        .ga-no-aplicante-greeting {
            font-size: 1rem;
            color: #6B7280;
            margin-bottom: 8px;
        }

        .ga-no-aplicante-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: <?php echo esc_attr($color_dark); ?>;
            margin-bottom: 16px;
        }

        .ga-no-aplicante-message {
            font-size: 1rem;
            color: #6B7280;
            margin-bottom: 32px;
            max-width: 340px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
        }

        /* Botones */
        .ga-no-aplicante-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ga-no-aplicante-btn {
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

        .ga-no-aplicante-btn-primary {
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
            border-color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-no-aplicante-btn-primary:hover {
            background: <?php echo esc_attr($color_dark); ?>;
            border-color: <?php echo esc_attr($color_dark); ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px <?php echo esc_attr($color_primary); ?>40;
        }

        .ga-no-aplicante-btn-outline {
            background: transparent;
            color: <?php echo esc_attr($color_primary); ?>;
            border-color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-no-aplicante-btn-outline:hover {
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
        }

        .ga-no-aplicante-btn .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
        }

        /* Footer */
        .ga-no-aplicante-footer {
            padding: 20px 40px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
        }

        .ga-no-aplicante-footer-text {
            font-size: 0.875rem;
            color: #6B7280;
        }

        .ga-no-aplicante-footer-link {
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 600;
        }

        .ga-no-aplicante-footer-link:hover {
            text-decoration: underline;
        }

        /* Enlace volver */
        .ga-no-aplicante-back {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .ga-no-aplicante-back a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .ga-no-aplicante-back a:hover {
            color: #ffffff;
        }

        .ga-no-aplicante-back .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 480px) {
            body.ga-no-aplicante-body {
                padding: 16px;
            }

            .ga-no-aplicante-header {
                padding: 20px 24px;
            }

            .ga-no-aplicante-body {
                padding: 36px 24px;
            }

            .ga-no-aplicante-footer {
                padding: 16px 24px;
            }

            .ga-no-aplicante-title {
                font-size: 1.25rem;
            }

            .ga-no-aplicante-icon {
                width: 72px;
                height: 72px;
            }

            .ga-no-aplicante-icon .dashicons {
                font-size: 32px;
                width: 32px;
                height: 32px;
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
<body class="ga-no-aplicante-body">
    <?php wp_body_open(); ?>

    <div class="ga-no-aplicante-wrapper">
        <div class="ga-no-aplicante-card">
            <!-- Header con Logo -->
            <div class="ga-no-aplicante-header">
                <div class="ga-no-aplicante-logo">
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>">
                    <?php else : ?>
                        <span class="ga-no-aplicante-logo-text"><?php echo esc_html($company_name); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cuerpo -->
            <div class="ga-no-aplicante-body">
                <div class="ga-no-aplicante-icon">
                    <span class="dashicons dashicons-info-outline"></span>
                </div>

                <p class="ga-no-aplicante-greeting">
                    <?php printf(esc_html__('Hola, %s', 'gestionadmin-wolk'), esc_html($user_name)); ?>
                </p>

                <h1 class="ga-no-aplicante-title"><?php esc_html_e('Completa tu Perfil', 'gestionadmin-wolk'); ?></h1>

                <p class="ga-no-aplicante-message">
                    <?php esc_html_e('Tu cuenta no tiene un perfil de aplicante asociado. Completa tu registro para acceder al portal de oportunidades.', 'gestionadmin-wolk'); ?>
                </p>

                <div class="ga-no-aplicante-actions">
                    <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>" class="ga-no-aplicante-btn ga-no-aplicante-btn-primary">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php esc_html_e('Completar Registro', 'gestionadmin-wolk'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" class="ga-no-aplicante-btn ga-no-aplicante-btn-outline">
                        <?php esc_html_e('Explorar Oportunidades', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="ga-no-aplicante-footer">
                <p class="ga-no-aplicante-footer-text">
                    <?php esc_html_e('¿Necesitas ayuda?', 'gestionadmin-wolk'); ?>
                    <a href="<?php echo esc_url(home_url('/contacto/')); ?>" class="ga-no-aplicante-footer-link">
                        <?php esc_html_e('Contáctanos', 'gestionadmin-wolk'); ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Enlace volver -->
        <div class="ga-no-aplicante-back">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php esc_html_e('Volver al inicio', 'gestionadmin-wolk'); ?>
            </a>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
