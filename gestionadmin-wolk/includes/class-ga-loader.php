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
        $this->define_admin_hooks();
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
        // Cargar clases principales
        // require_once GA_PLUGIN_DIR . 'admin/class-ga-admin.php';
        // require_once GA_PLUGIN_DIR . 'public/class-ga-public.php';

        // Cargar módulos (se agregarán en sprints futuros)
        // require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-departamentos.php';
        // require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-usuarios.php';
        // require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';
    }

    /**
     * Registrar todos los hooks relacionados con el área de administración
     */
    private function define_admin_hooks() {
        // Agregar menús de administración
        $this->add_action('admin_menu', $this, 'add_admin_menu');

        // Cargar scripts y estilos del admin
        $this->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_assets');
    }

    /**
     * Registrar todos los hooks relacionados con el área pública
     */
    private function define_public_hooks() {
        // Cargar scripts y estilos públicos
        $this->add_action('wp_enqueue_scripts', $this, 'enqueue_public_assets');
    }

    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            __('GestionAdmin', 'gestionadmin-wolk'),
            __('GestionAdmin', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin',
            array($this, 'render_admin_page'),
            'dashicons-businessperson',
            30
        );
    }

    /**
     * Renderizar página principal de administración
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('GestionAdmin by Wolk', 'gestionadmin-wolk') . '</h1>';
        echo '<p>' . esc_html__('Sistema integral de gestión empresarial', 'gestionadmin-wolk') . '</p>';
        echo '<p><strong>' . esc_html__('Versión:', 'gestionadmin-wolk') . '</strong> ' . esc_html(GA_VERSION) . '</p>';
        echo '</div>';
    }

    /**
     * Cargar assets del admin
     *
     * @param string $hook_suffix
     */
    public function enqueue_admin_assets($hook_suffix) {
        if (strpos($hook_suffix, 'gestionadmin') === false) {
            return;
        }

        wp_enqueue_style(
            'ga-admin-styles',
            GA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            GA_VERSION
        );

        wp_enqueue_script(
            'ga-admin-scripts',
            GA_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            GA_VERSION,
            true
        );

        // Pasar datos al JavaScript
        wp_localize_script('ga-admin-scripts', 'gaAdminData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ga_ajax_nonce'),
            'i18n' => array(
                'error' => __('Error al procesar la solicitud', 'gestionadmin-wolk'),
                'success' => __('Operación completada exitosamente', 'gestionadmin-wolk'),
            )
        ));
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
