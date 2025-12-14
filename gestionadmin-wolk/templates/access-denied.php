<?php
/**
 * Template: Acceso Denegado
 *
 * Se muestra cuando un usuario intenta acceder a un portal
 * para el cual no tiene permisos.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates
 * @since      1.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener colores del tema
$color_primary = GA_Theme_Integration::get_color('primary', '#0056A6');
$color_dark    = GA_Theme_Integration::get_color('dark', '#1F2937');
$color_error   = '#DC2626';

// Obtener logo y nombre
$logo_url     = GA_Theme_Integration::get_logo_url();
$company_name = GA_Theme_Integration::get_company_name();

// Obtener URL de redirección correcta para el usuario
$redirect_url = home_url('/');
if (function_exists('ga_get_user_dashboard_url')) {
    $redirect_url = ga_get_user_dashboard_url();
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php esc_html_e('Acceso Denegado', 'gestionadmin-wolk'); ?> - <?php echo esc_html($company_name); ?></title>
    <?php wp_head(); ?>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body.ga-access-denied-body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, <?php echo esc_attr($color_primary); ?> 0%, <?php echo esc_attr($color_dark); ?> 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
        }

        .ga-denied-wrapper {
            width: 100%;
            max-width: 480px;
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ga-denied-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        .ga-denied-header {
            background: <?php echo esc_attr($color_primary); ?>;
            padding: 28px 40px;
        }

        .ga-denied-logo img {
            max-height: 45px;
            max-width: 160px;
            height: auto;
        }

        .ga-denied-logo-text {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .ga-denied-body {
            padding: 56px 40px;
        }

        .ga-denied-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 28px;
            background: linear-gradient(135deg, <?php echo esc_attr($color_error); ?>10, <?php echo esc_attr($color_error); ?>20);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ga-denied-icon::before {
            content: '\1F6AB';
            font-size: 2.5rem;
        }

        .ga-denied-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: <?php echo esc_attr($color_dark); ?>;
            margin-bottom: 16px;
        }

        .ga-denied-message {
            font-size: 1rem;
            color: #6B7280;
            margin-bottom: 36px;
            line-height: 1.7;
        }

        .ga-denied-btn {
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
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
            border: none;
            cursor: pointer;
        }

        .ga-denied-btn:hover {
            background: <?php echo esc_attr($color_dark); ?>;
            transform: translateY(-2px);
        }

        .ga-denied-footer {
            padding: 20px 40px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
        }

        .ga-denied-footer-text {
            font-size: 0.875rem;
            color: #6B7280;
        }

        .ga-denied-footer-link {
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 600;
        }

        .ga-denied-back {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .ga-denied-back a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .ga-denied-back a:hover {
            color: #ffffff;
        }

        #wpadminbar { display: none !important; }
        html { margin-top: 0 !important; }

        @media (max-width: 520px) {
            .ga-denied-header, .ga-denied-body, .ga-denied-footer {
                padding-left: 24px;
                padding-right: 24px;
            }
            .ga-denied-body { padding-top: 40px; padding-bottom: 40px; }
            .ga-denied-title { font-size: 1.35rem; }
        }
    </style>
</head>
<body class="ga-access-denied-body">
    <?php wp_body_open(); ?>

    <div class="ga-denied-wrapper">
        <div class="ga-denied-card">
            <div class="ga-denied-header">
                <div class="ga-denied-logo">
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>">
                    <?php else : ?>
                        <span class="ga-denied-logo-text"><?php echo esc_html($company_name); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ga-denied-body">
                <div class="ga-denied-icon"></div>

                <h1 class="ga-denied-title"><?php esc_html_e('Acceso Denegado', 'gestionadmin-wolk'); ?></h1>

                <p class="ga-denied-message">
                    <?php esc_html_e('No tienes permisos para acceder a esta sección. Si crees que esto es un error, contacta al administrador.', 'gestionadmin-wolk'); ?>
                </p>

                <a href="<?php echo esc_url($redirect_url); ?>" class="ga-denied-btn">
                    <?php esc_html_e('Ir a Mi Portal', 'gestionadmin-wolk'); ?>
                </a>
            </div>

            <div class="ga-denied-footer">
                <p class="ga-denied-footer-text">
                    <?php esc_html_e('¿Necesitas ayuda?', 'gestionadmin-wolk'); ?>
                    <a href="<?php echo esc_url(home_url('/contacto/')); ?>" class="ga-denied-footer-link">
                        <?php esc_html_e('Contáctanos', 'gestionadmin-wolk'); ?>
                    </a>
                </p>
            </div>
        </div>

        <div class="ga-denied-back">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                &larr; <?php esc_html_e('Volver al inicio', 'gestionadmin-wolk'); ?>
            </a>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
