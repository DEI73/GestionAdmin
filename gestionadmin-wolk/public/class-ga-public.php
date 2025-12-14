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

        // Flush automático de rewrite rules cuando se actualiza el plugin
        add_action('init', array($this, 'maybe_flush_rewrite_rules'), 20);

        // Template override
        add_filter('template_include', array($this, 'load_template'));

        // Assets públicos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // AJAX handlers públicos (para usuarios no logueados también)
        add_action('wp_ajax_ga_public_aplicar', array($this, 'ajax_aplicar'));
        add_action('wp_ajax_nopriv_ga_public_aplicar', array($this, 'ajax_aplicar'));
        add_action('wp_ajax_ga_public_registro_aplicante', array($this, 'ajax_registro_aplicante'));
        add_action('wp_ajax_nopriv_ga_public_registro_aplicante', array($this, 'ajax_registro_aplicante'));

        // AJAX handlers Portal Empleado (solo usuarios logueados)
        add_action('wp_ajax_ga_update_perfil_empleado', array($this, 'ajax_update_perfil_empleado'));
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

        // /trabajo/{CODIGO}/ → Detalle de orden
        // Acepta formatos: OT-2024-0001, TEST-OT-001, o cualquier código alfanumérico con guiones
        // IMPORTANTE: Incluye a-z (minúsculas) porque WordPress normaliza URLs
        add_rewrite_rule(
            '^trabajo/([a-zA-Z0-9]+-[a-zA-Z0-9-]+)/?$',
            'index.php?ga_portal=trabajo&ga_section=single&ga_codigo=$matches[1]',
            'top'
        );

        // /trabajo/{CODIGO}/aplicar/ → Formulario de aplicación
        add_rewrite_rule(
            '^trabajo/([a-zA-Z0-9]+-[a-zA-Z0-9-]+)/aplicar/?$',
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

        // =====================================================================
        // PORTAL DE EMPLEADO (Panel privado para trabajadores)
        // =====================================================================

        // /portal-empleado/ → Dashboard del empleado
        add_rewrite_rule(
            '^portal-empleado/?$',
            'index.php?ga_portal=empleado&ga_section=dashboard',
            'top'
        );

        // /portal-empleado/mis-tareas/ → Tareas asignadas
        add_rewrite_rule(
            '^portal-empleado/mis-tareas/?$',
            'index.php?ga_portal=empleado&ga_section=tareas',
            'top'
        );

        // /portal-empleado/mi-timer/ → Timer de trabajo
        add_rewrite_rule(
            '^portal-empleado/mi-timer/?$',
            'index.php?ga_portal=empleado&ga_section=timer',
            'top'
        );

        // /portal-empleado/mis-horas/ → Historial de horas
        add_rewrite_rule(
            '^portal-empleado/mis-horas/?$',
            'index.php?ga_portal=empleado&ga_section=horas',
            'top'
        );

        // /portal-empleado/mi-perfil/ → Perfil del empleado
        add_rewrite_rule(
            '^portal-empleado/mi-perfil/?$',
            'index.php?ga_portal=empleado&ga_section=perfil',
            'top'
        );

        // =====================================================================
        // PORTAL DE CLIENTE (Panel privado para clientes)
        // =====================================================================

        // /cliente/ → Dashboard del cliente
        add_rewrite_rule(
            '^cliente/?$',
            'index.php?ga_portal=cliente&ga_section=dashboard',
            'top'
        );

        // /cliente/mis-casos/ → Casos del cliente
        add_rewrite_rule(
            '^cliente/mis-casos/?$',
            'index.php?ga_portal=cliente&ga_section=casos',
            'top'
        );

        // /cliente/mis-facturas/ → Facturas del cliente
        add_rewrite_rule(
            '^cliente/mis-facturas/?$',
            'index.php?ga_portal=cliente&ga_section=facturas',
            'top'
        );

        // /cliente/mi-perfil/ → Perfil del cliente
        add_rewrite_rule(
            '^cliente/mi-perfil/?$',
            'index.php?ga_portal=cliente&ga_section=perfil',
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

    /**
     * Verifica si se necesita hacer flush de rewrite rules
     *
     * Compara la versión del plugin con la versión guardada.
     * Si son diferentes, hace flush y actualiza la versión.
     *
     * @since 1.6.0
     */
    public function maybe_flush_rewrite_rules() {
        $saved_version = get_option('ga_rewrite_rules_version', '0');

        // Si la versión cambió o se activó el flag de flush, regenerar reglas
        if ($saved_version !== GA_VERSION || get_option('ga_flush_rewrite_rules')) {
            flush_rewrite_rules();
            update_option('ga_rewrite_rules_version', GA_VERSION);
            delete_option('ga_flush_rewrite_rules');
        }
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

                case 'empleado':
                    $template_file = $this->get_empleado_template($section);
                    break;

                case 'cliente':
                    $template_file = $this->get_cliente_template($section);
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

    /**
     * Obtiene el template para el portal de empleado
     *
     * @since 1.6.0
     *
     * @param string $section Sección del portal.
     *
     * @return string Path al template o string vacío si no existe.
     */
    private function get_empleado_template($section) {
        $template_dir = GA_PLUGIN_DIR . 'templates/portal-empleado/';

        // Requiere estar logueado
        if (!is_user_logged_in()) {
            // Redirigir al login
            wp_redirect(wp_login_url(home_url($_SERVER['REQUEST_URI'])));
            exit;
        }

        // Verificar que el usuario tiene rol de empleado o admin
        $user = wp_get_current_user();
        $allowed_roles = array('ga_empleado', 'ga_socio', 'ga_director', 'ga_jefe', 'administrator');
        if (!array_intersect($allowed_roles, (array) $user->roles)) {
            // No tiene permisos - mostrar mensaje de acceso denegado
            return GA_PLUGIN_DIR . 'templates/access-denied.php';
        }

        // Mapeo de secciones a templates
        $templates = array(
            'dashboard' => 'dashboard.php',
            'tareas'    => 'mis-tareas.php',
            'timer'     => 'mi-timer.php',
            'horas'     => 'mis-horas.php',
            'perfil'    => 'mi-perfil.php',
        );

        $template = isset($templates[$section]) ? $templates[$section] : 'dashboard.php';
        $template_path = $template_dir . $template;

        if (file_exists($template_path)) {
            return $template_path;
        }

        // Fallback al dashboard
        return $template_dir . 'dashboard.php';
    }

    /**
     * Obtiene el template para el portal de cliente
     *
     * @since 1.6.0
     *
     * @param string $section Sección del portal.
     *
     * @return string Path al template o string vacío si no existe.
     */
    private function get_cliente_template($section) {
        $template_dir = GA_PLUGIN_DIR . 'templates/portal-cliente/';

        // Requiere estar logueado
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url($_SERVER['REQUEST_URI'])));
            exit;
        }

        // Verificar que el usuario tiene rol de cliente o admin
        $user = wp_get_current_user();
        $allowed_roles = array('ga_cliente', 'administrator');
        if (!array_intersect($allowed_roles, (array) $user->roles)) {
            return GA_PLUGIN_DIR . 'templates/access-denied.php';
        }

        // Mapeo de secciones a templates
        $templates = array(
            'dashboard' => 'dashboard.php',
            'casos'     => 'mis-casos.php',
            'facturas'  => 'mis-facturas.php',
            'perfil'    => 'mi-perfil.php',
        );

        $template = isset($templates[$section]) ? $templates[$section] : 'dashboard.php';
        $template_path = $template_dir . $template;

        if (file_exists($template_path)) {
            return $template_path;
        }

        // Fallback al dashboard
        return $template_dir . 'dashboard.php';
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
    // AJAX: ACTUALIZAR PERFIL EMPLEADO
    // =========================================================================

    /**
     * Actualiza el perfil del empleado
     *
     * Actualiza datos personales, documentos y método de pago en wp_ga_aplicantes.
     * Registra todos los cambios en wp_ga_cambios_log para auditoría.
     *
     * @since 1.17.0
     */
    public function ajax_update_perfil_empleado() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['ga_perfil_nonce'] ?? '', 'ga_update_perfil_empleado')) {
            wp_send_json_error(array(
                'message' => __('Error de seguridad. Recarga la página e intenta de nuevo.', 'gestionadmin-wolk'),
            ));
        }

        // Verificar que está logueado
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('Debes iniciar sesión para actualizar tu perfil.', 'gestionadmin-wolk'),
            ));
        }

        $wp_user_id = get_current_user_id();

        // Verificar que tiene rol de empleado
        if (!current_user_can('ga_empleado') && !current_user_can('administrator')) {
            wp_send_json_error(array(
                'message' => __('No tienes permisos para realizar esta acción.', 'gestionadmin-wolk'),
            ));
        }

        // Obtener aplicante_id del formulario
        $aplicante_id = absint($_POST['aplicante_id'] ?? 0);
        if (!$aplicante_id) {
            wp_send_json_error(array(
                'message' => __('Perfil de aplicante no encontrado.', 'gestionadmin-wolk'),
            ));
        }

        global $wpdb;
        $table_aplicantes = $wpdb->prefix . 'ga_aplicantes';
        $table_log = $wpdb->prefix . 'ga_cambios_log';

        // Verificar que el aplicante pertenece al usuario actual
        $aplicante = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_aplicantes} WHERE id = %d AND usuario_wp_id = %d",
            $aplicante_id,
            $wp_user_id
        ));

        if (!$aplicante) {
            wp_send_json_error(array(
                'message' => __('No se encontró tu perfil de aplicante.', 'gestionadmin-wolk'),
            ));
        }

        // =====================================================================
        // PREPARAR DATOS A ACTUALIZAR
        // =====================================================================

        // Campos de texto permitidos
        $campos_texto = array(
            'nombre_completo'    => 'sanitize_text_field',
            'documento_tipo'     => 'sanitize_text_field',
            'documento_numero'   => 'sanitize_text_field',
            'telefono'           => 'sanitize_text_field',
            'pais'               => 'sanitize_text_field',
            'ciudad'             => 'sanitize_text_field',
            'direccion'          => 'sanitize_textarea_field',
            'metodo_pago_preferido' => 'sanitize_text_field',
        );

        $update_data = array();
        $cambios = array();

        // Procesar campos de texto
        foreach ($campos_texto as $campo => $sanitize_func) {
            if (isset($_POST[$campo])) {
                $valor_nuevo = call_user_func($sanitize_func, $_POST[$campo]);
                $valor_anterior = $aplicante->$campo ?? '';

                if ($valor_nuevo !== $valor_anterior) {
                    $update_data[$campo] = $valor_nuevo;
                    $cambios[] = array(
                        'campo' => $campo,
                        'anterior' => $valor_anterior,
                        'nuevo' => $valor_nuevo,
                    );
                }
            }
        }

        // =====================================================================
        // PROCESAR DATOS DE PAGO (JSON)
        // =====================================================================
        $metodo_pago = sanitize_text_field($_POST['metodo_pago_preferido'] ?? 'TRANSFERENCIA');
        $datos_pago_json = null;

        switch ($metodo_pago) {
            case 'BINANCE':
                $datos_pago_json = wp_json_encode(array(
                    'email' => sanitize_email($_POST['binance_email'] ?? ''),
                    'id'    => sanitize_text_field($_POST['binance_id'] ?? ''),
                ));
                $campo_pago = 'datos_pago_binance';
                break;

            case 'WISE':
                $datos_pago_json = wp_json_encode(array(
                    'email' => sanitize_email($_POST['wise_email'] ?? ''),
                ));
                $campo_pago = 'datos_pago_wise';
                break;

            case 'PAYPAL':
                $datos_pago_json = wp_json_encode(array(
                    'email' => sanitize_email($_POST['paypal_email'] ?? ''),
                ));
                $campo_pago = 'datos_pago_paypal';
                break;

            case 'PAYONEER':
                $datos_pago_json = wp_json_encode(array(
                    'email' => sanitize_email($_POST['payoneer_email'] ?? ''),
                ));
                $campo_pago = 'datos_pago_wise'; // Reutiliza campo
                break;

            case 'STRIPE':
                $datos_pago_json = wp_json_encode(array(
                    'email' => sanitize_email($_POST['stripe_email'] ?? ''),
                ));
                $campo_pago = 'datos_pago_wise'; // Reutiliza campo
                break;

            case 'TRANSFERENCIA':
                $datos_pago_json = wp_json_encode(array(
                    'banco'         => sanitize_text_field($_POST['banco_nombre'] ?? ''),
                    'tipo_cuenta'   => sanitize_text_field($_POST['banco_tipo_cuenta'] ?? ''),
                    'numero_cuenta' => sanitize_text_field($_POST['banco_numero_cuenta'] ?? ''),
                    'titular'       => sanitize_text_field($_POST['banco_titular'] ?? ''),
                ));
                $campo_pago = 'datos_pago_banco';
                break;
        }

        // Verificar si cambió el dato de pago
        if (isset($campo_pago) && $datos_pago_json) {
            $valor_anterior_pago = $aplicante->$campo_pago ?? '';
            if ($datos_pago_json !== $valor_anterior_pago) {
                $update_data[$campo_pago] = $datos_pago_json;
                $cambios[] = array(
                    'campo' => $campo_pago,
                    'anterior' => '[DATOS DE PAGO]', // No logueamos datos sensibles
                    'nuevo' => '[DATOS DE PAGO ACTUALIZADOS]',
                );
            }
        }

        // =====================================================================
        // PROCESAR ARCHIVOS
        // =====================================================================
        $campos_archivo = array(
            'documento_identidad'   => 'documento_identidad_url',
            'rut'                   => 'rut_url',
            'certificado_bancario'  => 'certificado_bancario_url',
            'cv'                    => 'cv_url',
        );

        foreach ($campos_archivo as $input_name => $db_field) {
            if (!empty($_FILES[$input_name]['name'])) {
                $upload_result = $this->handle_file_upload($input_name, $wp_user_id);
                if ($upload_result['success']) {
                    $valor_anterior = $aplicante->$db_field ?? '';
                    $update_data[$db_field] = $upload_result['url'];
                    $cambios[] = array(
                        'campo' => $db_field,
                        'anterior' => $valor_anterior ? basename($valor_anterior) : '',
                        'nuevo' => basename($upload_result['url']),
                    );
                }
            }
        }

        // =====================================================================
        // EJECUTAR ACTUALIZACIÓN
        // =====================================================================
        if (empty($update_data)) {
            wp_send_json_success(array(
                'message' => __('No se detectaron cambios en tu perfil.', 'gestionadmin-wolk'),
            ));
        }

        // Agregar timestamp
        $update_data['updated_at'] = current_time('mysql');

        // Actualizar en la base de datos
        $result = $wpdb->update(
            $table_aplicantes,
            $update_data,
            array('id' => $aplicante_id),
            null,
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array(
                'message' => __('Error al guardar los cambios. Intenta de nuevo.', 'gestionadmin-wolk'),
            ));
        }

        // =====================================================================
        // REGISTRAR CAMBIOS EN LOG
        // =====================================================================
        $ip_address = $this->get_client_ip();
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

        foreach ($cambios as $cambio) {
            $wpdb->insert(
                $table_log,
                array(
                    'tabla'          => 'ga_aplicantes',
                    'registro_id'    => $aplicante_id,
                    'campo'          => $cambio['campo'],
                    'valor_anterior' => $cambio['anterior'],
                    'valor_nuevo'    => $cambio['nuevo'],
                    'modificado_por' => $wp_user_id,
                    'ip_address'     => $ip_address,
                    'user_agent'     => substr($user_agent, 0, 255),
                    'accion'         => 'UPDATE',
                    'created_at'     => current_time('mysql'),
                ),
                array('%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
            );
        }

        // También actualizar display_name en WordPress si cambió el nombre
        if (isset($update_data['nombre_completo'])) {
            wp_update_user(array(
                'ID'           => $wp_user_id,
                'display_name' => $update_data['nombre_completo'],
            ));
        }

        wp_send_json_success(array(
            'message' => sprintf(
                __('Perfil actualizado correctamente. Se registraron %d cambios.', 'gestionadmin-wolk'),
                count($cambios)
            ),
        ));
    }

    /**
     * Maneja la subida de archivos
     *
     * @since 1.17.0
     *
     * @param string $field_name Nombre del campo del formulario.
     * @param int    $user_id    ID del usuario.
     *
     * @return array Array con 'success' y 'url' o 'error'.
     */
    private function handle_file_upload($field_name, $user_id) {
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $file = $_FILES[$field_name];

        // Validar tamaño (5MB máximo)
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return array(
                'success' => false,
                'error'   => __('El archivo es demasiado grande. Máximo 5MB.', 'gestionadmin-wolk'),
            );
        }

        // Validar tipo
        $allowed_types = array(
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );

        if (!in_array($file['type'], $allowed_types, true)) {
            return array(
                'success' => false,
                'error'   => __('Tipo de archivo no permitido.', 'gestionadmin-wolk'),
            );
        }

        // Renombrar archivo para evitar conflictos
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = sprintf('ga_doc_%d_%s_%s.%s', $user_id, $field_name, wp_generate_uuid4(), $ext);

        $upload_overrides = array(
            'test_form' => false,
            'unique_filename_callback' => function($dir, $name, $ext) use ($new_name) {
                return $new_name;
            },
        );

        $uploaded = wp_handle_upload($file, $upload_overrides);

        if (isset($uploaded['error'])) {
            return array(
                'success' => false,
                'error'   => $uploaded['error'],
            );
        }

        return array(
            'success' => true,
            'url'     => $uploaded['url'],
            'path'    => $uploaded['file'],
        );
    }

    /**
     * Obtiene la IP del cliente
     *
     * @since 1.17.0
     *
     * @return string IP del cliente.
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Si hay múltiples IPs, tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
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
