<?php
/**
 * Clase principal que coordina todos los módulos del plugin
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Loader {

    /**
     * La única instancia de la clase
     *
     * @var GA_Loader
     */
    protected static $instance = null;

    /**
     * Array de acciones registradas
     *
     * @var array
     */
    protected $actions;

    /**
     * Array de filtros registrados
     *
     * @var array
     */
    protected $filters;

    /**
     * Constructor
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();

        $this->load_dependencies();
        $this->define_public_hooks();
    }

    /**
     * Obtener la única instancia de la clase
     *
     * @return GA_Loader
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Cargar las dependencias requeridas para este plugin
     */
    private function load_dependencies() {
        // Cargar integración con tema (disponible en admin y frontend)
        require_once GA_PLUGIN_DIR . 'includes/class-ga-theme-integration.php';
        GA_Theme_Integration::get_instance();

        // Cargar sistema de emails profesionales (disponible en admin y frontend)
        require_once GA_PLUGIN_DIR . 'includes/class-ga-emails.php';
        GA_Emails::get_instance();

        // Cargar sistema de notificaciones por email
        require_once GA_PLUGIN_DIR . 'includes/class-ga-notificaciones.php';
        GA_Notificaciones::get_instance();

        // Cargar control de acceso (bloquea wp-admin para roles restringidos)
        require_once GA_PLUGIN_DIR . 'includes/class-ga-access-control.php';
        GA_Access_Control::get_instance();

        // Cargar Timer REST API (disponible en frontend y admin)
        require_once GA_PLUGIN_DIR . 'api/class-ga-timer-api.php';
        GA_Timer_API::get_instance();

        // Cargar clase de administración (carga todos los módulos y menús)
        if (is_admin()) {
            require_once GA_PLUGIN_DIR . 'admin/class-ga-admin.php';
            GA_Admin::get_instance();
        } else {
            // Frontend: Cargar módulos necesarios para templates públicos
            require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-ordenes-trabajo.php';
            require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-aplicantes.php';
            require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-aplicaciones.php';

            // Cargar clase pública (rewrite rules, templates, AJAX público)
            require_once GA_PLUGIN_DIR . 'public/class-ga-public.php';
            GA_Public::get_instance();
        }

        // AJAX handler para obtener escalas (disponible en admin)
        add_action('wp_ajax_ga_get_escalas', array($this, 'ajax_get_escalas'));
    }

    /**
     * Registrar todos los hooks relacionados con el área pública
     */
    private function define_public_hooks() {
        // Cargar scripts y estilos públicos
        $this->add_action('wp_enqueue_scripts', $this, 'enqueue_public_assets');
    }

    /**
     * Cargar assets públicos
     */
    public function enqueue_public_assets() {
        wp_enqueue_style(
            'ga-public-styles',
            GA_PLUGIN_URL . 'assets/css/public.css',
            array(),
            GA_VERSION
        );

        wp_enqueue_script(
            'ga-public-scripts',
            GA_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            GA_VERSION,
            true
        );
    }

    /**
     * AJAX: Obtener escalas de un puesto
     */
    public function ajax_get_escalas() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $puesto_id = absint($_GET['puesto_id']);

        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-puestos.php';
        $escalas = GA_Puestos::get_escalas($puesto_id);

        wp_send_json_success($escalas);
    }

    /**
     * Agregar una acción al array de acciones
     *
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int $priority
     * @param int $accepted_args
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Agregar un filtro al array de filtros
     *
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int $priority
     * @param int $accepted_args
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Agregar hook al array de hooks
     *
     * @param array $hooks
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int $priority
     * @param int $accepted_args
     * @return array
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );
        return $hooks;
    }

    /**
     * Registrar los filtros y acciones con WordPress
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}
