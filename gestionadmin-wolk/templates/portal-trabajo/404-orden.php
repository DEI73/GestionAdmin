<?php
/**
 * Template: Orden No Encontrada
 *
 * Se muestra cuando la orden no existe o no está disponible.
 * Diseño limpio y centrado con colores heredados del tema.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalTrabajo
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
$color_error     = '#DC2626';

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
    <title><?php esc_html_e('Orden No Encontrada', 'gestionadmin-wolk'); ?> - <?php echo esc_html($company_name); ?></title>
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

        body.ga-404-body {
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
        .ga-404-wrapper {
            width: 100%;
            max-width: 480px;
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
        .ga-404-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        /* Header con logo */
        .ga-404-header {
            background: <?php echo esc_attr($color_primary); ?>;
            padding: 28px 40px;
        }

        .ga-404-logo img {
            max-height: 45px;
            max-width: 160px;
            height: auto;
        }

        .ga-404-logo-text {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Cuerpo */
        .ga-404-body {
            padding: 56px 40px;
        }

        .ga-404-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 28px;
            background: linear-gradient(135deg, <?php echo esc_attr($color_error); ?>10, <?php echo esc_attr($color_error); ?>20);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .ga-404-icon::before {
            content: '404';
            font-size: 1.75rem;
            font-weight: 800;
            color: <?php echo esc_attr($color_error); ?>;
        }

        .ga-404-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: <?php echo esc_attr($color_dark); ?>;
            margin-bottom: 16px;
        }

        .ga-404-message {
            font-size: 1rem;
            color: #6B7280;
            margin-bottom: 36px;
            max-width: 360px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
        }

        /* Botones */
        .ga-404-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ga-404-btn {
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

        .ga-404-btn-primary {
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
            border-color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-404-btn-primary:hover {
            background: <?php echo esc_attr($color_dark); ?>;
            border-color: <?php echo esc_attr($color_dark); ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px <?php echo esc_attr($color_primary); ?>40;
        }

        .ga-404-btn-outline {
            background: transparent;
            color: <?php echo esc_attr($color_primary); ?>;
            border-color: <?php echo esc_attr($color_primary); ?>;
        }

        .ga-404-btn-outline:hover {
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
        }

        .ga-404-btn .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
        }

        /* Sugerencias */
        .ga-404-suggestions {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #E5E7EB;
        }

        .ga-404-suggestions-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
        }

        .ga-404-suggestions-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
        }

        .ga-404-suggestion-link {
            display: inline-block;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 500;
            color: <?php echo esc_attr($color_primary); ?>;
            background: <?php echo esc_attr($color_primary); ?>08;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .ga-404-suggestion-link:hover {
            background: <?php echo esc_attr($color_primary); ?>;
            color: #ffffff;
        }

        /* Footer */
        .ga-404-footer {
            padding: 20px 40px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
        }

        .ga-404-footer-text {
            font-size: 0.875rem;
            color: #6B7280;
        }

        .ga-404-footer-link {
            color: <?php echo esc_attr($color_primary); ?>;
            text-decoration: none;
            font-weight: 600;
        }

        .ga-404-footer-link:hover {
            text-decoration: underline;
        }

        /* Enlace volver */
        .ga-404-back {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .ga-404-back a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .ga-404-back a:hover {
            color: #ffffff;
        }

        .ga-404-back .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 520px) {
            body.ga-404-body {
                padding: 16px;
            }

            .ga-404-header {
                padding: 20px 24px;
            }

            .ga-404-body {
                padding: 40px 24px;
            }

            .ga-404-footer {
                padding: 16px 24px;
            }

            .ga-404-title {
                font-size: 1.35rem;
            }

            .ga-404-icon {
                width: 80px;
                height: 80px;
            }

            .ga-404-icon::before {
                font-size: 1.5rem;
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
<body class="ga-404-body">
    <?php wp_body_open(); ?>

    <div class="ga-404-wrapper">
        <div class="ga-404-card">
            <!-- Header con Logo -->
            <div class="ga-404-header">
                <div class="ga-404-logo">
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>">
                    <?php else : ?>
                        <span class="ga-404-logo-text"><?php echo esc_html($company_name); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cuerpo -->
            <div class="ga-404-body">
                <div class="ga-404-icon"></div>

                <h1 class="ga-404-title"><?php esc_html_e('Orden No Encontrada', 'gestionadmin-wolk'); ?></h1>

                <p class="ga-404-message">
                    <?php esc_html_e('La orden de trabajo que buscas no existe, fue eliminada o ya no está disponible. Explora otras oportunidades activas.', 'gestionadmin-wolk'); ?>
                </p>

                <div class="ga-404-actions">
                    <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" class="ga-404-btn ga-404-btn-primary">
                        <span class="dashicons dashicons-search"></span>
                        <?php esc_html_e('Ver Oportunidades', 'gestionadmin-wolk'); ?>
                    </a>
                </div>

                <!-- Sugerencias -->
                <div class="ga-404-suggestions">
                    <div class="ga-404-suggestions-title"><?php esc_html_e('Categorías populares', 'gestionadmin-wolk'); ?></div>
                    <div class="ga-404-suggestions-list">
                        <a href="<?php echo esc_url(home_url('/trabajo/desarrollo/')); ?>" class="ga-404-suggestion-link">
                            <?php esc_html_e('Desarrollo', 'gestionadmin-wolk'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/trabajo/diseno/')); ?>" class="ga-404-suggestion-link">
                            <?php esc_html_e('Diseño', 'gestionadmin-wolk'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/trabajo/marketing/')); ?>" class="ga-404-suggestion-link">
                            <?php esc_html_e('Marketing', 'gestionadmin-wolk'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/trabajo/contabilidad/')); ?>" class="ga-404-suggestion-link">
                            <?php esc_html_e('Contabilidad', 'gestionadmin-wolk'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="ga-404-footer">
                <p class="ga-404-footer-text">
                    <?php esc_html_e('¿Necesitas ayuda?', 'gestionadmin-wolk'); ?>
                    <a href="<?php echo esc_url(home_url('/contacto/')); ?>" class="ga-404-footer-link">
                        <?php esc_html_e('Contáctanos', 'gestionadmin-wolk'); ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Enlace volver -->
        <div class="ga-404-back">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php esc_html_e('Volver al inicio', 'gestionadmin-wolk'); ?>
            </a>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
