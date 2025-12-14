<?php
/**
 * ==========================================================================
 * GESTIONADMIN - INTEGRACIÓN CON TEMA
 * ==========================================================================
 *
 * Detecta si el tema GestionAdmin Theme está activo y proporciona
 * funciones para integrar los templates del plugin con el tema.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Theme_Integration
 * @since      1.6.0
 */

// SEGURIDAD: Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GA_Theme_Integration
 *
 * Proporciona integración entre el plugin y el tema GestionAdmin Theme.
 * Permite heredar colores, logo, tipografía y estilos del tema.
 *
 * @since 1.6.0
 */
class GA_Theme_Integration {

    /**
     * Nombre del tema compatible
     *
     * @var string
     */
    const THEME_NAME = 'gestionadmin-theme';

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Theme_Integration
     */
    private static $instance = null;

    /**
     * Cache de colores
     *
     * @var array
     */
    private static $colors_cache = null;

    /**
     * Obtener instancia única
     *
     * @since 1.6.0
     *
     * @return GA_Theme_Integration
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.6.0
     */
    public function __construct() {
        // Hook para agregar estilos del portal
        add_action('wp_enqueue_scripts', array($this, 'enqueue_portal_styles'), 20);
    }

    /**
     * Verificar si el tema GestionAdmin está activo
     *
     * @since 1.6.0
     *
     * @return bool
     */
    public static function is_theme_active() {
        $theme = wp_get_theme();
        return (
            $theme->get_stylesheet() === self::THEME_NAME ||
            $theme->get_template() === self::THEME_NAME ||
            strpos($theme->get_stylesheet(), 'gestionadmin') !== false
        );
    }

    /**
     * Obtener color del tema o valor por defecto
     *
     * @since 1.6.0
     *
     * @param string $color_name Nombre del color: primary, secondary, accent, dark
     * @param string $default    Valor por defecto
     *
     * @return string Código hex del color
     */
    public static function get_color($color_name, $default = '') {
        // Si el tema está activo y existe la función helper
        if (self::is_theme_active() && function_exists('ga_get_color')) {
            return ga_get_color($color_name, $default);
        }

        // Intentar obtener del customizer del tema
        if (self::is_theme_active()) {
            $theme_mod = get_theme_mod('ga_color_' . $color_name);
            if (!empty($theme_mod)) {
                return $theme_mod;
            }
        }

        // Valores por defecto del plugin
        $defaults = array(
            'primary'   => '#0056A6',
            'secondary' => '#DC2626',
            'accent'    => '#10B981',
            'dark'      => '#1F2937',
            'light'     => '#F3F4F6',
            'text'      => '#374151',
            'border'    => '#E5E7EB',
            'success'   => '#10B981',
            'warning'   => '#F59E0B',
            'danger'    => '#DC2626',
            'info'      => '#3B82F6',
        );

        return !empty($default) ? $default : ($defaults[$color_name] ?? '#000000');
    }

    /**
     * Obtener todos los colores
     *
     * @since 1.6.0
     *
     * @return array
     */
    public static function get_all_colors() {
        if (self::$colors_cache !== null) {
            return self::$colors_cache;
        }

        self::$colors_cache = array(
            'primary'   => self::get_color('primary'),
            'secondary' => self::get_color('secondary'),
            'accent'    => self::get_color('accent'),
            'dark'      => self::get_color('dark'),
            'light'     => self::get_color('light'),
            'text'      => self::get_color('text'),
            'border'    => self::get_color('border'),
            'success'   => self::get_color('success'),
            'warning'   => self::get_color('warning'),
            'danger'    => self::get_color('danger'),
            'info'      => self::get_color('info'),
        );

        return self::$colors_cache;
    }

    /**
     * Obtener URL del logo
     *
     * @since 1.6.0
     *
     * @return string URL del logo o vacío
     */
    public static function get_logo_url() {
        // Si el tema tiene función helper
        if (self::is_theme_active() && function_exists('ga_get_logo_url')) {
            return ga_get_logo_url();
        }

        // Intentar del customizer del tema
        if (self::is_theme_active()) {
            $logo_url = get_theme_mod('ga_logo_url');
            if (!empty($logo_url)) {
                return $logo_url;
            }
        }

        // Fallback: custom logo de WordPress
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            return $logo ? $logo[0] : '';
        }

        return '';
    }

    /**
     * Obtener nombre de la empresa
     *
     * @since 1.6.0
     *
     * @return string
     */
    public static function get_company_name() {
        // Si el tema tiene función helper
        if (self::is_theme_active() && function_exists('ga_company_name')) {
            return ga_company_name();
        }

        // Intentar del customizer del tema
        if (self::is_theme_active()) {
            $company = get_theme_mod('ga_company_name');
            if (!empty($company)) {
                return $company;
            }
        }

        return get_bloginfo('name');
    }

    /**
     * Obtener info de contacto
     *
     * @since 1.6.0
     *
     * @return array
     */
    public static function get_contact_info() {
        // Si el tema tiene función helper
        if (self::is_theme_active() && function_exists('ga_contact_info')) {
            return ga_contact_info();
        }

        // Intentar del customizer del tema
        if (self::is_theme_active()) {
            return array(
                'email'   => get_theme_mod('ga_contact_email', get_option('admin_email')),
                'phone'   => get_theme_mod('ga_contact_phone', ''),
                'address' => get_theme_mod('ga_contact_address', ''),
                'whatsapp' => get_theme_mod('ga_contact_whatsapp', ''),
            );
        }

        return array(
            'email'   => get_option('admin_email'),
            'phone'   => '',
            'address' => '',
            'whatsapp' => '',
        );
    }

    /**
     * Obtener redes sociales
     *
     * @since 1.6.0
     *
     * @return array
     */
    public static function get_social_links() {
        // Si el tema tiene función helper
        if (self::is_theme_active() && function_exists('ga_social_links')) {
            return ga_social_links();
        }

        // Intentar del customizer del tema
        if (self::is_theme_active()) {
            return array(
                'facebook'  => get_theme_mod('ga_social_facebook', ''),
                'instagram' => get_theme_mod('ga_social_instagram', ''),
                'linkedin'  => get_theme_mod('ga_social_linkedin', ''),
                'twitter'   => get_theme_mod('ga_social_twitter', ''),
                'youtube'   => get_theme_mod('ga_social_youtube', ''),
            );
        }

        return array();
    }

    /**
     * Renderizar header del portal
     *
     * Si el tema está activo, usa get_header() del tema.
     * Si no, renderiza header propio del plugin.
     *
     * @since 1.6.0
     *
     * @param string $page_title Título de la página
     *
     * @return void
     */
    public static function render_header($page_title = '') {
        if (self::is_theme_active()) {
            // Usar header del tema
            get_header();

            // Agregar título de página si existe
            if (!empty($page_title)) {
                ?>
                <div class="ga-page-header" style="background: <?php echo esc_attr(self::get_color('primary')); ?>; padding: 40px 0; text-align: center;">
                    <div class="ga-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
                        <h1 class="ga-page-title" style="color: #fff; margin: 0; font-size: 2rem; font-weight: 700;">
                            <?php echo esc_html($page_title); ?>
                        </h1>
                    </div>
                </div>
                <?php
            }
        } else {
            // Header propio del plugin
            self::render_plugin_header($page_title);
        }
    }

    /**
     * Renderizar footer del portal
     *
     * Si el tema está activo, usa get_footer() del tema.
     * Si no, renderiza footer propio del plugin.
     *
     * @since 1.6.0
     *
     * @return void
     */
    public static function render_footer() {
        if (self::is_theme_active()) {
            get_footer();
        } else {
            // Footer propio del plugin
            self::render_plugin_footer();
        }
    }

    /**
     * Header propio del plugin (fallback cuando el tema no está activo)
     *
     * @since 1.6.0
     *
     * @param string $page_title Título de la página
     */
    private static function render_plugin_header($page_title = '') {
        $logo_url     = self::get_logo_url();
        $company_name = self::get_company_name();
        $primary      = self::get_color('primary');
        $dark         = self::get_color('dark');
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($page_title ? $page_title . ' - ' : ''); ?><?php echo esc_html($company_name); ?></title>
            <?php wp_head(); ?>
        </head>
        <body <?php body_class('ga-plugin-page'); ?>>
        <?php wp_body_open(); ?>

        <header class="ga-plugin-header" style="background: <?php echo esc_attr($primary); ?>; padding: 15px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div class="ga-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
                <a href="<?php echo esc_url(home_url('/')); ?>" style="display: flex; align-items: center; text-decoration: none;">
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>" style="max-height: 50px;">
                    <?php else : ?>
                        <span style="color: #fff; font-size: 1.5rem; font-weight: bold;"><?php echo esc_html($company_name); ?></span>
                    <?php endif; ?>
                </a>
                <nav style="display: flex; gap: 20px; align-items: center;">
                    <a href="<?php echo esc_url(home_url('/')); ?>" style="color: #fff; text-decoration: none; font-weight: 500;">
                        <?php esc_html_e('Inicio', 'gestionadmin-wolk'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" style="color: #fff; text-decoration: none; font-weight: 500;">
                        <?php esc_html_e('Trabajos', 'gestionadmin-wolk'); ?>
                    </a>
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" style="color: #fff; text-decoration: none; font-weight: 500;">
                            <?php esc_html_e('Mi Cuenta', 'gestionadmin-wolk'); ?>
                        </a>
                        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" style="color: rgba(255,255,255,0.8); text-decoration: none;">
                            <?php esc_html_e('Salir', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url(home_url('/mi-cuenta/'))); ?>" style="color: #fff; text-decoration: none; font-weight: 500; padding: 8px 16px; border: 2px solid #fff; border-radius: 6px;">
                            <?php esc_html_e('Ingresar', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <?php if (!empty($page_title)) : ?>
        <div class="ga-page-header" style="background: linear-gradient(135deg, <?php echo esc_attr($primary); ?>, <?php echo esc_attr($dark); ?>); padding: 50px 0; text-align: center;">
            <div class="ga-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
                <h1 style="color: #fff; margin: 0; font-size: 2.5rem; font-weight: 700;"><?php echo esc_html($page_title); ?></h1>
            </div>
        </div>
        <?php endif; ?>

        <main class="ga-plugin-main" style="padding: 40px 0; min-height: 60vh; background: <?php echo esc_attr(self::get_color('light')); ?>;">
        <?php
    }

    /**
     * Footer propio del plugin (fallback cuando el tema no está activo)
     *
     * @since 1.6.0
     */
    private static function render_plugin_footer() {
        $company_name = self::get_company_name();
        $contact      = self::get_contact_info();
        $social       = self::get_social_links();
        $dark         = self::get_color('dark');
        $secondary    = self::get_color('secondary');
        ?>
        </main>

        <footer class="ga-plugin-footer">
            <!-- Línea decorativa -->
            <div style="background: <?php echo esc_attr($secondary); ?>; height: 5px;"></div>

            <!-- Footer principal -->
            <div style="background: <?php echo esc_attr($dark); ?>; padding: 40px 0; color: #fff;">
                <div class="ga-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
                        <!-- Columna 1: Empresa -->
                        <div>
                            <h4 style="font-size: 1.1rem; margin: 0 0 15px 0; font-weight: 600;"><?php echo esc_html($company_name); ?></h4>
                            <?php if (!empty($contact['address'])) : ?>
                                <p style="margin: 0 0 10px 0; font-size: 0.9rem; opacity: 0.8;"><?php echo esc_html($contact['address']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Columna 2: Contacto -->
                        <div>
                            <h4 style="font-size: 1.1rem; margin: 0 0 15px 0; font-weight: 600;"><?php esc_html_e('Contacto', 'gestionadmin-wolk'); ?></h4>
                            <?php if (!empty($contact['email'])) : ?>
                                <p style="margin: 0 0 8px 0; font-size: 0.9rem;">
                                    <a href="mailto:<?php echo esc_attr($contact['email']); ?>" style="color: #fff; text-decoration: none; opacity: 0.8;">
                                        <?php echo esc_html($contact['email']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($contact['phone'])) : ?>
                                <p style="margin: 0 0 8px 0; font-size: 0.9rem; opacity: 0.8;"><?php echo esc_html($contact['phone']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Columna 3: Enlaces -->
                        <div>
                            <h4 style="font-size: 1.1rem; margin: 0 0 15px 0; font-weight: 600;"><?php esc_html_e('Enlaces', 'gestionadmin-wolk'); ?></h4>
                            <p style="margin: 0 0 8px 0;">
                                <a href="<?php echo esc_url(home_url('/trabajo/')); ?>" style="color: #fff; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">
                                    <?php esc_html_e('Oportunidades de Trabajo', 'gestionadmin-wolk'); ?>
                                </a>
                            </p>
                            <p style="margin: 0 0 8px 0;">
                                <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>" style="color: #fff; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">
                                    <?php esc_html_e('Registrarse como Proveedor', 'gestionadmin-wolk'); ?>
                                </a>
                            </p>
                        </div>

                        <!-- Columna 4: Redes Sociales -->
                        <?php if (!empty(array_filter($social))) : ?>
                        <div>
                            <h4 style="font-size: 1.1rem; margin: 0 0 15px 0; font-weight: 600;"><?php esc_html_e('Síguenos', 'gestionadmin-wolk'); ?></h4>
                            <div style="display: flex; gap: 15px;">
                                <?php foreach ($social as $network => $url) : ?>
                                    <?php if (!empty($url)) : ?>
                                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" style="color: #fff; font-size: 1.2rem; opacity: 0.8;">
                                            <span class="dashicons dashicons-<?php echo esc_attr($network); ?>"></span>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Copyright -->
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
                        <p style="margin: 0; font-size: 0.85rem; opacity: 0.6;">
                            &copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html($company_name); ?>. <?php esc_html_e('Todos los derechos reservados.', 'gestionadmin-wolk'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }

    /**
     * Encolar estilos del portal
     *
     * @since 1.6.0
     */
    public function enqueue_portal_styles() {
        // Solo en páginas del plugin
        $portal = get_query_var('ga_portal');
        if (empty($portal) && !is_page()) {
            return;
        }

        // Agregar CSS variables inline
        $css = self::get_css_variables();
        wp_add_inline_style('ga-public-styles', $css);
    }

    /**
     * Obtener CSS variables para usar en templates
     *
     * @since 1.6.0
     *
     * @return string CSS con variables
     */
    public static function get_css_variables() {
        $colors = self::get_all_colors();

        return '
            :root {
                --ga-color-primary: ' . esc_attr($colors['primary']) . ';
                --ga-color-secondary: ' . esc_attr($colors['secondary']) . ';
                --ga-color-accent: ' . esc_attr($colors['accent']) . ';
                --ga-color-dark: ' . esc_attr($colors['dark']) . ';
                --ga-color-light: ' . esc_attr($colors['light']) . ';
                --ga-color-text: ' . esc_attr($colors['text']) . ';
                --ga-color-border: ' . esc_attr($colors['border']) . ';
                --ga-color-success: ' . esc_attr($colors['success']) . ';
                --ga-color-warning: ' . esc_attr($colors['warning']) . ';
                --ga-color-danger: ' . esc_attr($colors['danger']) . ';
                --ga-color-info: ' . esc_attr($colors['info']) . ';
                --ga-color-white: #FFFFFF;
                --ga-font-primary: "Montserrat", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                --ga-radius-sm: 4px;
                --ga-radius-md: 8px;
                --ga-radius-lg: 12px;
                --ga-radius-xl: 20px;
                --ga-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
                --ga-shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
                --ga-shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            }
        ';
    }

    /**
     * Imprimir estilos base del portal que heredan del tema
     *
     * @since 1.6.0
     *
     * @return void
     */
    public static function print_portal_styles() {
        ?>
        <style id="ga-portal-integration-styles">
            <?php echo self::get_css_variables(); ?>

            /* ===== PORTAL BASE STYLES ===== */
            .ga-portal-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .ga-portal-card {
                background: var(--ga-color-white);
                border-radius: var(--ga-radius-lg);
                box-shadow: var(--ga-shadow-md);
                padding: 25px;
                margin-bottom: 20px;
            }

            .ga-portal-card-header {
                border-bottom: 2px solid var(--ga-color-border);
                padding-bottom: 15px;
                margin-bottom: 20px;
            }

            .ga-portal-card-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--ga-color-dark);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            /* Botones */
            .ga-portal-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 12px 24px;
                font-size: 0.95rem;
                font-weight: 600;
                text-decoration: none;
                border: 2px solid transparent;
                border-radius: var(--ga-radius-md);
                cursor: pointer;
                transition: all 0.2s ease;
                font-family: var(--ga-font-primary);
            }

            .ga-portal-btn-primary {
                background: var(--ga-color-primary);
                color: var(--ga-color-white);
                border-color: var(--ga-color-primary);
            }

            .ga-portal-btn-primary:hover {
                background: transparent;
                color: var(--ga-color-primary);
            }

            .ga-portal-btn-secondary {
                background: var(--ga-color-secondary);
                color: var(--ga-color-white);
                border-color: var(--ga-color-secondary);
            }

            .ga-portal-btn-secondary:hover {
                background: transparent;
                color: var(--ga-color-secondary);
            }

            .ga-portal-btn-outline {
                background: transparent;
                color: var(--ga-color-primary);
                border-color: var(--ga-color-primary);
            }

            .ga-portal-btn-outline:hover {
                background: var(--ga-color-primary);
                color: var(--ga-color-white);
            }

            .ga-portal-btn-sm {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .ga-portal-btn-lg {
                padding: 16px 32px;
                font-size: 1.1rem;
            }

            /* Formularios */
            .ga-portal-form-group {
                margin-bottom: 20px;
            }

            .ga-portal-label {
                display: block;
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--ga-color-dark);
                margin-bottom: 6px;
            }

            .ga-portal-input,
            .ga-portal-select,
            .ga-portal-textarea {
                width: 100%;
                padding: 12px 15px;
                font-size: 1rem;
                color: var(--ga-color-text);
                background: var(--ga-color-white);
                border: 2px solid var(--ga-color-border);
                border-radius: var(--ga-radius-md);
                transition: border-color 0.2s, box-shadow 0.2s;
                font-family: var(--ga-font-primary);
            }

            .ga-portal-input:focus,
            .ga-portal-select:focus,
            .ga-portal-textarea:focus {
                outline: none;
                border-color: var(--ga-color-primary);
                box-shadow: 0 0 0 3px rgba(0, 86, 166, 0.1);
            }

            /* Tablas */
            .ga-portal-table {
                width: 100%;
                border-collapse: collapse;
            }

            .ga-portal-table th,
            .ga-portal-table td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid var(--ga-color-border);
            }

            .ga-portal-table th {
                background: var(--ga-color-light);
                font-weight: 600;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: var(--ga-color-dark);
            }

            .ga-portal-table tr:hover {
                background: var(--ga-color-light);
            }

            /* Badges */
            .ga-portal-badge {
                display: inline-block;
                padding: 4px 12px;
                font-size: 0.75rem;
                font-weight: 600;
                border-radius: 20px;
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }

            .ga-portal-badge-success {
                background: #D1FAE5;
                color: #065F46;
            }

            .ga-portal-badge-warning {
                background: #FEF3C7;
                color: #92400E;
            }

            .ga-portal-badge-danger {
                background: #FEE2E2;
                color: #991B1B;
            }

            .ga-portal-badge-info {
                background: #DBEAFE;
                color: #1E40AF;
            }

            .ga-portal-badge-primary {
                background: var(--ga-color-primary);
                color: var(--ga-color-white);
            }

            /* Alertas */
            .ga-portal-alert {
                padding: 15px 20px;
                border-radius: var(--ga-radius-md);
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .ga-portal-alert-success {
                background: #D1FAE5;
                color: #065F46;
                border: 1px solid #10B981;
            }

            .ga-portal-alert-error {
                background: #FEE2E2;
                color: #991B1B;
                border: 1px solid #DC2626;
            }

            .ga-portal-alert-warning {
                background: #FEF3C7;
                color: #92400E;
                border: 1px solid #F59E0B;
            }

            .ga-portal-alert-info {
                background: #DBEAFE;
                color: #1E40AF;
                border: 1px solid #3B82F6;
            }

            /* Stats Cards */
            .ga-portal-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .ga-portal-stat {
                background: var(--ga-color-white);
                border-radius: var(--ga-radius-lg);
                padding: 25px;
                box-shadow: var(--ga-shadow-md);
                text-align: center;
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .ga-portal-stat:hover {
                transform: translateY(-2px);
                box-shadow: var(--ga-shadow-lg);
            }

            .ga-portal-stat-value {
                font-size: 2.5rem;
                font-weight: 700;
                color: var(--ga-color-primary);
                line-height: 1;
            }

            .ga-portal-stat-label {
                font-size: 0.9rem;
                color: var(--ga-color-text);
                margin-top: 8px;
                font-weight: 500;
            }

            /* Grid Layout */
            .ga-portal-grid {
                display: grid;
                gap: 20px;
            }

            .ga-portal-grid-2 {
                grid-template-columns: repeat(2, 1fr);
            }

            .ga-portal-grid-3 {
                grid-template-columns: repeat(3, 1fr);
            }

            .ga-portal-grid-4 {
                grid-template-columns: repeat(4, 1fr);
            }

            @media (max-width: 992px) {
                .ga-portal-grid-4 {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (max-width: 768px) {
                .ga-portal-grid-2,
                .ga-portal-grid-3,
                .ga-portal-grid-4 {
                    grid-template-columns: 1fr;
                }

                .ga-portal-stats {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            /* Empty State */
            .ga-portal-empty {
                text-align: center;
                padding: 60px 20px;
                color: var(--ga-color-text);
            }

            .ga-portal-empty-icon {
                font-size: 4rem;
                margin-bottom: 20px;
                opacity: 0.4;
            }

            .ga-portal-empty h3 {
                font-size: 1.25rem;
                margin: 0 0 10px 0;
                color: var(--ga-color-dark);
            }

            .ga-portal-empty p {
                margin: 0 0 20px 0;
                opacity: 0.7;
            }

            /* Loading */
            .ga-portal-loading {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 40px;
            }

            .ga-portal-spinner {
                width: 40px;
                height: 40px;
                border: 3px solid var(--ga-color-border);
                border-top-color: var(--ga-color-primary);
                border-radius: 50%;
                animation: ga-spin 0.8s linear infinite;
            }

            @keyframes ga-spin {
                to { transform: rotate(360deg); }
            }

            /* Section Headers */
            .ga-portal-section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid var(--ga-color-border);
            }

            .ga-portal-section-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--ga-color-dark);
                margin: 0;
            }

            .ga-portal-section-link {
                color: var(--ga-color-primary);
                text-decoration: none;
                font-weight: 500;
                font-size: 0.9rem;
            }

            .ga-portal-section-link:hover {
                text-decoration: underline;
            }

            /* Responsive adjustments */
            @media (max-width: 576px) {
                .ga-portal-container {
                    padding: 15px;
                }

                .ga-portal-card {
                    padding: 20px;
                }

                .ga-portal-stats {
                    grid-template-columns: 1fr;
                }

                .ga-portal-stat-value {
                    font-size: 2rem;
                }
            }
        </style>
        <?php
    }

    /**
     * Verificar si estamos en una página de portal del plugin
     *
     * @since 1.6.0
     *
     * @return bool
     */
    public static function is_portal_page() {
        return !empty(get_query_var('ga_portal'));
    }

    /**
     * Obtener el portal actual
     *
     * @since 1.6.0
     *
     * @return string|null
     */
    public static function get_current_portal() {
        return get_query_var('ga_portal') ?: null;
    }
}
