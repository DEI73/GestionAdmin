<?php
/**
 * Clase Frontend Público del Plugin
 *
 * Maneja toda la funcionalidad pública del plugin:
 * - Registro de custom rewrite rules para URLs amigables
 * - Carga de templates personalizados
 * - Portal de trabajo (Marketplace público)
 * - Portal de aplicantes (Panel del freelancer)
 *
 * URLs del sistema:
 * - /trabajo/                    → Listado de órdenes de trabajo
 * - /trabajo/OT-2024-0001/       → Detalle de orden específica
 * - /trabajo/OT-2024-0001/aplicar/ → Formulario de aplicación
 * - /mi-cuenta/                  → Dashboard del aplicante
 * - /mi-cuenta/aplicaciones/     → Mis aplicaciones
 * - /mi-cuenta/perfil/           → Mi perfil
 * - /registro-aplicante/         → Registro de nuevo aplicante
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Public
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GA_Public
 *
 * Controlador principal del frontend público.
 *
 * @since 1.3.0
 */
class GA_Public {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Public
     */
    private static $instance = null;

    /**
     * Query vars personalizados
     *
     * @var array
     */
    private $query_vars = array(
        'ga_portal',
        'ga_section',
        'ga_codigo',
        'ga_action',
    );

    /**
     * Obtener instancia única
     *
     * @since 1.3.0
     *
     * @return GA_Public
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
     * Registra todos los hooks necesarios para el frontend.
     *
     * @since 1.3.0
     */
    public function __construct() {
        // Rewrite rules
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'register_query_vars'));

        // Template override
        add_filter('template_include', array($this, 'load_template'));

        // Assets públicos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // AJAX handlers públicos (para usuarios no logueados también)
        add_action('wp_ajax_ga_public_aplicar', array($this, 'ajax_aplicar'));
        add_action('wp_ajax_nopriv_ga_public_aplicar', array($this, 'ajax_aplicar'));
        add_action('wp_ajax_ga_public_registro_aplicante', array($this, 'ajax_registro_aplicante'));
        add_action('wp_ajax_nopriv_ga_public_registro_aplicante', array($this, 'ajax_registro_aplicante'));
    }

    // =========================================================================
    // REWRITE RULES
    // =========================================================================

    /**
     * Registra las reglas de reescritura de URLs
     *
     * Crea URLs amigables para el portal público.
     *
     * @since 1.3.0
     */
    public function add_rewrite_rules() {
        // =====================================================================
        // PORTAL DE TRABAJO (Marketplace público)
        // =====================================================================

        // /trabajo/ → Listado de órdenes
        add_rewrite_rule(
            '^trabajo/?$',
            'index.php?ga_portal=trabajo&ga_section=archive',
            'top'
        );

        // /trabajo/categoria/DESARROLLO/ → Filtro por categoría
        add_rewrite_rule(
            '^trabajo/categoria/([^/]+)/?$',
            'index.php?ga_portal=trabajo&ga_section=archive&ga_action=$matches[1]',
            'top'
        );

        // /trabajo/OT-2024-0001/ → Detalle de orden
        add_rewrite_rule(
            '^trabajo/(OT-[0-9]{4}-[0-9]+)/?$',
            'index.php?ga_portal=trabajo&ga_section=single&ga_codigo=$matches[1]',
            'top'
        );

        // /trabajo/OT-2024-0001/aplicar/ → Formulario de aplicación
        add_rewrite_rule(
            '^trabajo/(OT-[0-9]{4}-[0-9]+)/aplicar/?$',
            'index.php?ga_portal=trabajo&ga_section=aplicar&ga_codigo=$matches[1]',
            'top'
        );

        // =====================================================================
        // PORTAL DE APLICANTE (Panel privado)
        // =====================================================================

        // /mi-cuenta/ → Dashboard del aplicante
        add_rewrite_rule(
            '^mi-cuenta/?$',
            'index.php?ga_portal=aplicante&ga_section=dashboard',
            'top'
        );

        // /mi-cuenta/aplicaciones/ → Mis aplicaciones
        add_rewrite_rule(
            '^mi-cuenta/aplicaciones/?$',
            'index.php?ga_portal=aplicante&ga_section=aplicaciones',
            'top'
        );

        // /mi-cuenta/perfil/ → Mi perfil
        add_rewrite_rule(
            '^mi-cuenta/perfil/?$',
            'index.php?ga_portal=aplicante&ga_section=perfil',
            'top'
        );

        // /registro-aplicante/ → Registro de nuevo aplicante
        add_rewrite_rule(
            '^registro-aplicante/?$',
            'index.php?ga_portal=aplicante&ga_section=registro',
            'top'
        );
    }

    /**
     * Registra las query vars personalizadas
     *
     * @since 1.3.0
     *
     * @param array $vars Query vars existentes.
     *
     * @return array Query vars actualizadas.
     */
    public function register_query_vars($vars) {
        return array_merge($vars, $this->query_vars);
    }

    /**
     * Limpia y regenera las rewrite rules
     *
     * @since 1.3.0
     */
    public function flush_rewrite_rules() {
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }

    // =========================================================================
    // TEMPLATE LOADER
    // =========================================================================

    /**
     * Carga el template apropiado según la URL
     *
     * Intercepta la carga de templates de WordPress y carga
     * los templates personalizados del plugin cuando corresponde.
     *
     * Funciona de dos formas:
     * 1. Por rewrite rules (query vars ga_portal, ga_section, ga_codigo)
     * 2. Por páginas creadas con GA_Pages_Manager (detección por ID)
     *
     * @since 1.3.0
     *
     * @param string $template Template original.
     *
     * @return string Path al template a cargar.
     */
    public function load_template($template) {
        // Método 1: Detectar por rewrite rules
        $portal = get_query_var('ga_portal');

        if (!empty($portal)) {
            $section = get_query_var('ga_section');
            $codigo = get_query_var('ga_codigo');

            $template_file = '';

            switch ($portal) {
                case 'trabajo':
                    $template_file = $this->get_trabajo_template($section, $codigo);
                    break;

                case 'aplicante':
                    $template_file = $this->get_aplicante_template($section);
                    break;
            }

            if ($template_file && file_exists($template_file)) {
                return $template_file;
            }
        }

        // Método 2: Detectar por páginas del plugin (GA_Pages_Manager)
        if (is_page()) {
            require_once GA_PLUGIN_DIR . 'includes/class-ga-pages-manager.php';
            $pages_manager = GA_Pages_Manager::get_instance();
            $page_key = $pages_manager->detect_current_page();

            if ($page_key) {
                $template_path = $pages_manager->get_template_path($page_key);
                if ($template_path && file_exists($template_path)) {
                    return $template_path;
                }
            }
        }

        return $template;
    }

    /**
     * Obtiene el template para el portal de trabajo
     *
     * @since 1.3.0
     *
     * @param string $section Sección del portal.
     * @param string $codigo  Código de la orden (si aplica).
     *
     * @return string Path al template.
     */
    private function get_trabajo_template($section, $codigo = '') {
        $template_dir = GA_PLUGIN_DIR . 'templates/portal-trabajo/';

        switch ($section) {
            case 'archive':
                return $template_dir . 'archive-ordenes.php';

            case 'single':
                // Verificar que la orden existe
                $orden = GA_Ordenes_Trabajo::get_by_codigo($codigo);
                if ($orden && $orden->estado === 'PUBLICADA') {
                    return $template_dir . 'single-orden.php';
                }
                return $template_dir . '404-orden.php';

            case 'aplicar':
                // Verificar que el usuario puede aplicar
                return $template_dir . 'form-aplicar.php';

            default:
                return $template_dir . 'archive-ordenes.php';
        }
    }

    /**
     * Obtiene el template para el portal de aplicante
     *
     * @since 1.3.0
     *
     * @param string $section Sección del portal.
     *
     * @return string Path al template.
     */
    private function get_aplicante_template($section) {
        $template_dir = GA_PLUGIN_DIR . 'templates/portal-aplicante/';

        // El registro es público
        if ($section === 'registro') {
            return $template_dir . 'registro.php';
        }

        // Las demás secciones requieren estar logueado
        if (!is_user_logged_in()) {
            return $template_dir . 'login-required.php';
        }

        // Verificar que el usuario es un aplicante
        $aplicante = GA_Aplicantes::get_by_wp_user(get_current_user_id());
        if (!$aplicante) {
            return $template_dir . 'no-aplicante.php';
        }

        switch ($section) {
            case 'dashboard':
                return $template_dir . 'dashboard.php';

            case 'aplicaciones':
                return $template_dir . 'mis-aplicaciones.php';

            case 'perfil':
                return $template_dir . 'mi-perfil.php';

            default:
                return $template_dir . 'dashboard.php';
        }
    }

    // =========================================================================
    // ASSETS
    // =========================================================================

    /**
     * Registra y encola los assets públicos
     *
     * @since 1.3.0
     */
    public function enqueue_assets() {
        $portal = get_query_var('ga_portal');

        // Solo cargar en páginas del plugin
        if (empty($portal)) {
            return;
        }

        // CSS público
        wp_enqueue_style(
            'ga-public-styles',
            GA_PLUGIN_URL . 'assets/css/public.css',
            array(),
            GA_VERSION
        );

        // JS público
        wp_enqueue_script(
            'ga-public-scripts',
            GA_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            GA_VERSION,
            true
        );

        // Variables para JavaScript
        wp_localize_script('ga-public-scripts', 'gaPublic', array(
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('ga_public_nonce'),
            'homeUrl'   => home_url('/'),
            'trabajoUrl'=> home_url('/trabajo/'),
            'cuentaUrl' => home_url('/mi-cuenta/'),
            'i18n'      => array(
                'loading'     => __('Cargando...', 'gestionadmin-wolk'),
                'error'       => __('Error al procesar la solicitud', 'gestionadmin-wolk'),
                'success'     => __('Operación exitosa', 'gestionadmin-wolk'),
                'confirm'     => __('¿Estás seguro?', 'gestionadmin-wolk'),
                'sending'     => __('Enviando...', 'gestionadmin-wolk'),
                'aplicarBtn'  => __('Enviar Aplicación', 'gestionadmin-wolk'),
            ),
        ));
    }

    // =========================================================================
    // AJAX HANDLERS PÚBLICOS
    // =========================================================================

    /**
     * AJAX: Procesar aplicación a orden de trabajo
     *
     * Recibe la postulación de un aplicante verificado.
     *
     * @since 1.3.0
     */
    public function ajax_aplicar() {
        check_ajax_referer('ga_public_nonce', 'nonce');

        // Debe estar logueado
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('Debes iniciar sesión para aplicar.', 'gestionadmin-wolk'),
                'code'    => 'not_logged_in',
            ));
        }

        // Obtener aplicante
        $aplicante = GA_Aplicantes::get_by_wp_user(get_current_user_id());
        if (!$aplicante) {
            wp_send_json_error(array(
                'message' => __('No tienes un perfil de aplicante. Regístrate primero.', 'gestionadmin-wolk'),
                'code'    => 'no_aplicante',
            ));
        }

        if ($aplicante->estado !== 'VERIFICADO') {
            wp_send_json_error(array(
                'message' => __('Tu cuenta debe estar verificada para aplicar.', 'gestionadmin-wolk'),
                'code'    => 'not_verified',
            ));
        }

        $data = array(
            'orden_trabajo_id'   => absint($_POST['orden_id']),
            'aplicante_id'       => $aplicante->id,
            'carta_presentacion' => wp_kses_post($_POST['carta_presentacion'] ?? ''),
            'propuesta_monto'    => floatval($_POST['propuesta_monto'] ?? 0) ?: null,
            'propuesta_tiempo'   => sanitize_text_field($_POST['propuesta_tiempo'] ?? ''),
            'disponibilidad'     => sanitize_text_field($_POST['disponibilidad'] ?? ''),
        );

        $result = GA_Aplicaciones::aplicar($data);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        wp_send_json_success(array(
            'message'     => $result['message'],
            'redirect_to' => home_url('/mi-cuenta/aplicaciones/'),
        ));
    }

    /**
     * AJAX: Registrar nuevo aplicante
     *
     * Crea cuenta de usuario WordPress y perfil de aplicante.
     *
     * @since 1.3.0
     */
    public function ajax_registro_aplicante() {
        check_ajax_referer('ga_public_nonce', 'nonce');

        // No debe estar logueado
        if (is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('Ya tienes una sesión activa.', 'gestionadmin-wolk'),
            ));
        }

        // Validar campos obligatorios
        $required = array('nombre_completo', 'email', 'password');
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array(
                    'message' => __('Todos los campos obligatorios deben completarse.', 'gestionadmin-wolk'),
                ));
            }
        }

        // Validar contraseña
        if (strlen($_POST['password']) < 8) {
            wp_send_json_error(array(
                'message' => __('La contraseña debe tener al menos 8 caracteres.', 'gestionadmin-wolk'),
            ));
        }

        $data = array(
            'nombre_completo'    => sanitize_text_field($_POST['nombre_completo']),
            'email'              => sanitize_email($_POST['email']),
            'password'           => $_POST['password'], // Se hashea internamente
            'telefono'           => sanitize_text_field($_POST['telefono'] ?? ''),
            'pais'               => sanitize_text_field($_POST['pais'] ?? ''),
            'tipo'               => sanitize_text_field($_POST['tipo'] ?? 'PERSONA_NATURAL'),
            'titulo_profesional' => sanitize_text_field($_POST['titulo_profesional'] ?? ''),
        );

        $result = GA_Aplicantes::registrar($data);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        // Auto-login
        wp_set_current_user($result['wp_user_id']);
        wp_set_auth_cookie($result['wp_user_id'], true);

        wp_send_json_success(array(
            'message'     => $result['message'],
            'redirect_to' => home_url('/mi-cuenta/'),
        ));
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Obtiene la URL del portal de trabajo
     *
     * @since 1.3.0
     *
     * @param string $path Path adicional.
     *
     * @return string URL completa.
     */
    public static function get_trabajo_url($path = '') {
        return home_url('/trabajo/' . ltrim($path, '/'));
    }

    /**
     * Obtiene la URL del portal de aplicante
     *
     * @since 1.3.0
     *
     * @param string $path Path adicional.
     *
     * @return string URL completa.
     */
    public static function get_cuenta_url($path = '') {
        return home_url('/mi-cuenta/' . ltrim($path, '/'));
    }

    /**
     * Obtiene la URL de una orden de trabajo
     *
     * @since 1.3.0
     *
     * @param string $codigo Código de la orden.
     *
     * @return string URL de la orden.
     */
    public static function get_orden_url($codigo) {
        return home_url('/trabajo/' . $codigo . '/');
    }

    /**
     * Obtiene la URL para aplicar a una orden
     *
     * @since 1.3.0
     *
     * @param string $codigo Código de la orden.
     *
     * @return string URL del formulario de aplicación.
     */
    public static function get_aplicar_url($codigo) {
        return home_url('/trabajo/' . $codigo . '/aplicar/');
    }

    /**
     * Verifica si estamos en una página del portal de trabajo
     *
     * @since 1.3.0
     *
     * @return bool
     */
    public static function is_trabajo_page() {
        return get_query_var('ga_portal') === 'trabajo';
    }

    /**
     * Verifica si estamos en una página del portal de aplicante
     *
     * @since 1.3.0
     *
     * @return bool
     */
    public static function is_cuenta_page() {
        return get_query_var('ga_portal') === 'aplicante';
    }

    /**
     * Obtiene el aplicante actual (si está logueado)
     *
     * @since 1.3.0
     *
     * @return object|null Objeto del aplicante o null.
     */
    public static function get_current_aplicante() {
        if (!is_user_logged_in()) {
            return null;
        }
        return GA_Aplicantes::get_by_wp_user(get_current_user_id());
    }
}

// NOTA: No auto-inicializar aquí. Se carga desde class-ga-loader.php
// para evitar conflictos durante la activación del plugin.
