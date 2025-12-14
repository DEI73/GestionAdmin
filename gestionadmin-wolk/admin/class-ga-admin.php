<?php
/**
 * Clase principal de administración
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Admin {

    /**
     * Instancia única
     */
    private static $instance = null;

    /**
     * Obtener instancia
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_modules();
        add_action('admin_menu', array($this, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        // AJAX handlers - Sprint 1-2
        add_action('wp_ajax_ga_save_departamento', array($this, 'ajax_save_departamento'));
        add_action('wp_ajax_ga_delete_departamento', array($this, 'ajax_delete_departamento'));
        add_action('wp_ajax_ga_save_puesto', array($this, 'ajax_save_puesto'));
        add_action('wp_ajax_ga_delete_puesto', array($this, 'ajax_delete_puesto'));
        add_action('wp_ajax_ga_save_escala', array($this, 'ajax_save_escala'));
        add_action('wp_ajax_ga_delete_escala', array($this, 'ajax_delete_escala'));
        add_action('wp_ajax_ga_save_usuario', array($this, 'ajax_save_usuario'));
        add_action('wp_ajax_ga_save_pais', array($this, 'ajax_save_pais'));
        add_action('wp_ajax_ga_delete_pais', array($this, 'ajax_delete_pais'));

        // AJAX handlers - Sprint 3-4 (Tareas y Timer)
        add_action('wp_ajax_ga_save_tarea', array($this, 'ajax_save_tarea'));
        add_action('wp_ajax_ga_delete_tarea', array($this, 'ajax_delete_tarea'));
        add_action('wp_ajax_ga_timer_start', array($this, 'ajax_timer_start'));
        add_action('wp_ajax_ga_timer_pause', array($this, 'ajax_timer_pause'));
        add_action('wp_ajax_ga_timer_resume', array($this, 'ajax_timer_resume'));
        add_action('wp_ajax_ga_timer_stop', array($this, 'ajax_timer_stop'));
        add_action('wp_ajax_ga_get_timer_status', array($this, 'ajax_get_timer_status'));

        // AJAX handlers - Sprint 5-6 (Clientes, Casos, Proyectos)
        add_action('wp_ajax_ga_save_cliente', array($this, 'ajax_save_cliente'));
        add_action('wp_ajax_ga_delete_cliente', array($this, 'ajax_delete_cliente'));
        add_action('wp_ajax_ga_save_caso', array($this, 'ajax_save_caso'));
        add_action('wp_ajax_ga_delete_caso', array($this, 'ajax_delete_caso'));
        add_action('wp_ajax_ga_get_casos_by_cliente', array($this, 'ajax_get_casos_by_cliente'));
        add_action('wp_ajax_ga_save_proyecto', array($this, 'ajax_save_proyecto'));
        add_action('wp_ajax_ga_delete_proyecto', array($this, 'ajax_delete_proyecto'));
        add_action('wp_ajax_ga_get_proyectos_by_caso', array($this, 'ajax_get_proyectos_by_caso'));

        // AJAX handlers - Sprint 7-8 (Marketplace: Órdenes de Trabajo, Aplicantes)
        add_action('wp_ajax_ga_save_orden_trabajo', array($this, 'ajax_save_orden_trabajo'));
        add_action('wp_ajax_ga_delete_orden_trabajo', array($this, 'ajax_delete_orden_trabajo'));
        add_action('wp_ajax_ga_change_orden_estado', array($this, 'ajax_change_orden_estado'));
        add_action('wp_ajax_ga_save_aplicante', array($this, 'ajax_save_aplicante'));
        add_action('wp_ajax_ga_delete_aplicante', array($this, 'ajax_delete_aplicante'));
        add_action('wp_ajax_ga_change_aplicante_estado', array($this, 'ajax_change_aplicante_estado'));
        add_action('wp_ajax_ga_save_aplicacion', array($this, 'ajax_save_aplicacion'));
        add_action('wp_ajax_ga_change_aplicacion_estado', array($this, 'ajax_change_aplicacion_estado'));

        // AJAX handlers - Páginas del Plugin (Configuración)
        add_action('wp_ajax_ga_create_page', array($this, 'ajax_create_page'));
        add_action('wp_ajax_ga_recreate_page', array($this, 'ajax_recreate_page'));
        add_action('wp_ajax_ga_create_all_pages', array($this, 'ajax_create_all_pages'));

        // AJAX handlers - Notificaciones
        add_action('wp_ajax_ga_save_notificaciones_config', array($this, 'ajax_save_notificaciones_config'));
    }

    /**
     * Cargar módulos
     *
     * Carga todos los módulos necesarios para el funcionamiento del admin.
     * Organizados por sprint de desarrollo.
     */
    private function load_modules() {
        // Sprint 1-2: Fundamentos
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-departamentos.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-puestos.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-usuarios.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-paises.php';

        // Sprint 3-4: Core Operativo
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';

        // Sprint 5-6: Clientes y Proyectos
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-clientes.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-casos.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-proyectos.php';

        // Sprint 7-8: Marketplace y Órdenes de Trabajo
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-ordenes-trabajo.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-aplicantes.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-aplicaciones.php';

        // Sprint 9-10: Facturación
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-facturas.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-cotizaciones.php';

        // Sprint 11-12: Acuerdos Económicos
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-empresas.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-catalogo-bonos.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-ordenes-acuerdos.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-ordenes-bonos.php';

        // Sprint 13-14: Catálogos de Configuración
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-metodos-pago.php';
    }

    /**
     * Agregar menús de administración
     */
    public function add_admin_menus() {
        // Menú principal
        add_menu_page(
            __('GestionAdmin', 'gestionadmin-wolk'),
            __('GestionAdmin', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin',
            array($this, 'render_dashboard'),
            'dashicons-businessperson',
            30
        );

        // Dashboard (mismo que principal)
        add_submenu_page(
            'gestionadmin',
            __('Dashboard', 'gestionadmin-wolk'),
            __('Dashboard', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin',
            array($this, 'render_dashboard')
        );

        // Departamentos
        add_submenu_page(
            'gestionadmin',
            __('Departamentos', 'gestionadmin-wolk'),
            __('Departamentos', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-departamentos',
            array($this, 'render_departamentos')
        );

        // Puestos
        add_submenu_page(
            'gestionadmin',
            __('Puestos', 'gestionadmin-wolk'),
            __('Puestos', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-puestos',
            array($this, 'render_puestos')
        );

        // Usuarios
        add_submenu_page(
            'gestionadmin',
            __('Usuarios GA', 'gestionadmin-wolk'),
            __('Usuarios GA', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-usuarios',
            array($this, 'render_usuarios')
        );

        // Países
        add_submenu_page(
            'gestionadmin',
            __('Países', 'gestionadmin-wolk'),
            __('Países', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-paises',
            array($this, 'render_paises')
        );

        // Tareas
        add_submenu_page(
            'gestionadmin',
            __('Tareas', 'gestionadmin-wolk'),
            __('Tareas', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-tareas',
            array($this, 'render_tareas')
        );

        // Separador visual (Sprint 5-6)
        add_submenu_page(
            'gestionadmin',
            '',
            '── ' . __('Clientes', 'gestionadmin-wolk') . ' ──',
            'manage_options',
            '#ga-separator-clientes',
            '__return_false'
        );

        // Clientes
        add_submenu_page(
            'gestionadmin',
            __('Clientes', 'gestionadmin-wolk'),
            __('Clientes', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-clientes',
            array($this, 'render_clientes')
        );

        // Casos
        add_submenu_page(
            'gestionadmin',
            __('Casos', 'gestionadmin-wolk'),
            __('Casos', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-casos',
            array($this, 'render_casos')
        );

        // Proyectos
        add_submenu_page(
            'gestionadmin',
            __('Proyectos', 'gestionadmin-wolk'),
            __('Proyectos', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-proyectos',
            array($this, 'render_proyectos')
        );

        // Separador visual (Sprint 7-8: Marketplace)
        add_submenu_page(
            'gestionadmin',
            '',
            '── ' . __('Marketplace', 'gestionadmin-wolk') . ' ──',
            'manage_options',
            '#ga-separator-marketplace',
            '__return_false'
        );

        // Órdenes de Trabajo
        add_submenu_page(
            'gestionadmin',
            __('Órdenes de Trabajo', 'gestionadmin-wolk'),
            __('Órdenes de Trabajo', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-ordenes',
            array($this, 'render_ordenes_trabajo')
        );

        // Aplicantes
        add_submenu_page(
            'gestionadmin',
            __('Aplicantes', 'gestionadmin-wolk'),
            __('Aplicantes', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-aplicantes',
            array($this, 'render_aplicantes')
        );

        // Separador visual (Sprint 9-10: Facturación)
        add_submenu_page(
            'gestionadmin',
            '',
            '── ' . __('Facturación', 'gestionadmin-wolk') . ' ──',
            'manage_options',
            '#ga-separator-facturacion',
            '__return_false'
        );

        // Facturas
        add_submenu_page(
            'gestionadmin',
            __('Facturas', 'gestionadmin-wolk'),
            __('Facturas', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-facturas',
            array($this, 'render_facturas')
        );

        // Cotizaciones
        add_submenu_page(
            'gestionadmin',
            __('Cotizaciones', 'gestionadmin-wolk'),
            __('Cotizaciones', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-cotizaciones',
            array($this, 'render_cotizaciones')
        );

        // Separador visual (Sprint 11-12: Configuración Económica)
        add_submenu_page(
            'gestionadmin',
            '',
            '── ' . __('Config. Económica', 'gestionadmin-wolk') . ' ──',
            'manage_options',
            '#ga-separator-economica',
            '__return_false'
        );

        // Mis Empresas (Sprint 11-12)
        add_submenu_page(
            'gestionadmin',
            __('Mis Empresas', 'gestionadmin-wolk'),
            __('Mis Empresas', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-empresas',
            array($this, 'render_empresas')
        );

        // Catálogo de Bonos (Sprint 11-12)
        add_submenu_page(
            'gestionadmin',
            __('Catálogo Bonos', 'gestionadmin-wolk'),
            __('Catálogo Bonos', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-catalogo-bonos',
            array($this, 'render_catalogo_bonos')
        );

        // Comisiones Generadas (Sprint 11-12 Parte B)
        add_submenu_page(
            'gestionadmin',
            __('Comisiones', 'gestionadmin-wolk'),
            __('Comisiones', 'gestionadmin-wolk'),
            'manage_options',
            'ga-comisiones',
            array($this, 'render_comisiones')
        );

        // Solicitudes de Cobro (Sprint 11-12 Parte B)
        add_submenu_page(
            'gestionadmin',
            __('Solicitudes Cobro', 'gestionadmin-wolk'),
            __('Solicitudes Cobro', 'gestionadmin-wolk'),
            'manage_options',
            'ga-solicitudes-cobro',
            array($this, 'render_solicitudes_cobro')
        );

        // Separador visual (Configuración)
        add_submenu_page(
            'gestionadmin',
            '',
            '── ' . __('Configuración', 'gestionadmin-wolk') . ' ──',
            'manage_options',
            '#ga-separator-config',
            '__return_false'
        );

        // Métodos de Pago (Sprint 13-14)
        add_submenu_page(
            'gestionadmin',
            __('Métodos de Pago', 'gestionadmin-wolk'),
            __('Métodos de Pago', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-metodos-pago',
            array($this, 'render_metodos_pago')
        );

        // Páginas del Plugin
        add_submenu_page(
            'gestionadmin',
            __('Páginas', 'gestionadmin-wolk'),
            __('Páginas', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-paginas',
            array($this, 'render_paginas')
        );

        // Notificaciones por Email
        add_submenu_page(
            'gestionadmin',
            __('Notificaciones', 'gestionadmin-wolk'),
            __('Notificaciones', 'gestionadmin-wolk'),
            'manage_options',
            'gestionadmin-notificaciones',
            array($this, 'render_notificaciones')
        );
    }

    /**
     * Cargar assets
     */
    public function enqueue_assets($hook_suffix) {
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

        wp_localize_script('ga-admin-scripts', 'gaAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ga_admin_nonce'),
            'i18n' => array(
                'confirmDelete' => __('¿Estás seguro de eliminar este registro?', 'gestionadmin-wolk'),
                'error' => __('Error al procesar la solicitud', 'gestionadmin-wolk'),
                'success' => __('Operación completada exitosamente', 'gestionadmin-wolk'),
                'saving' => __('Guardando...', 'gestionadmin-wolk'),
                'pauseReason' => __('Motivo de la pausa:', 'gestionadmin-wolk'),
            )
        ));

        // Cargar media uploader para páginas que lo necesitan
        if (strpos($hook_suffix, 'ordenes') !== false || strpos($hook_suffix, 'empresas') !== false || strpos($hook_suffix, 'clientes') !== false) {
            wp_enqueue_media();
        }
    }

    /**
     * Renderizar Dashboard
     */
    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Renderizar Departamentos
     */
    public function render_departamentos() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/departamentos.php';
    }

    /**
     * Renderizar Puestos
     */
    public function render_puestos() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/puestos.php';
    }

    /**
     * Renderizar Usuarios
     */
    public function render_usuarios() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/usuarios.php';
    }

    /**
     * Renderizar Países
     */
    public function render_paises() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/paises.php';
    }

    /**
     * Renderizar Tareas
     */
    public function render_tareas() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/tareas.php';
    }

    /**
     * Renderizar Clientes
     *
     * Página de gestión de clientes con CRUD completo.
     * Soporta personas naturales y empresas.
     */
    public function render_clientes() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/clientes.php';
    }

    /**
     * Renderizar Casos
     *
     * Página de gestión de casos/expedientes.
     * Los casos agrupan proyectos de un cliente.
     */
    public function render_casos() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/casos.php';
    }

    /**
     * Renderizar Proyectos
     *
     * Página de gestión de proyectos.
     * Los proyectos pertenecen a un caso específico.
     */
    public function render_proyectos() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/proyectos.php';
    }

    // =========================================================================
    // AJAX HANDLERS - DEPARTAMENTOS
    // =========================================================================

    public function ajax_save_departamento() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $data = array(
            'codigo' => sanitize_text_field($_POST['codigo']),
            'nombre' => sanitize_text_field($_POST['nombre']),
            'descripcion' => sanitize_textarea_field($_POST['descripcion']),
            'tipo' => sanitize_text_field($_POST['tipo']),
            'jefe_id' => absint($_POST['jefe_id']),
            'activo' => absint($_POST['activo']),
        );

        $result = GA_Departamentos::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Departamento guardado', 'gestionadmin-wolk'), 'id' => $result));
    }

    public function ajax_delete_departamento() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $result = GA_Departamentos::delete($id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Departamento eliminado', 'gestionadmin-wolk')));
    }

    // =========================================================================
    // AJAX HANDLERS - PUESTOS
    // =========================================================================

    public function ajax_save_puesto() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $data = array(
            'departamento_id' => absint($_POST['departamento_id']),
            'codigo' => sanitize_text_field($_POST['codigo']),
            'nombre' => sanitize_text_field($_POST['nombre']),
            'descripcion' => sanitize_textarea_field($_POST['descripcion']),
            'nivel_jerarquico' => absint($_POST['nivel_jerarquico']),
            'capacidad_horas_semana' => absint($_POST['capacidad_horas_semana']),
            'requiere_qa' => absint($_POST['requiere_qa']),
            'flujo_revision_default' => sanitize_text_field($_POST['flujo_revision_default']),
            'activo' => absint($_POST['activo']),
        );

        $result = GA_Puestos::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Puesto guardado', 'gestionadmin-wolk'), 'id' => $result));
    }

    public function ajax_delete_puesto() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $result = GA_Puestos::delete($id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Puesto eliminado', 'gestionadmin-wolk')));
    }

    // =========================================================================
    // AJAX HANDLERS - ESCALAS
    // =========================================================================

    public function ajax_save_escala() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $data = array(
            'puesto_id' => absint($_POST['puesto_id']),
            'anio_antiguedad' => absint($_POST['anio_antiguedad']),
            'tarifa_hora' => floatval($_POST['tarifa_hora']),
            'incremento_porcentaje' => floatval($_POST['incremento_porcentaje']),
            'requiere_aprobacion_jefe' => absint($_POST['requiere_aprobacion_jefe']),
            'requiere_aprobacion_director' => absint($_POST['requiere_aprobacion_director']),
            'activo' => 1,
        );

        $result = GA_Puestos::save_escala($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Escala guardada', 'gestionadmin-wolk'), 'id' => $result));
    }

    public function ajax_delete_escala() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $result = GA_Puestos::delete_escala($id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Escala eliminada', 'gestionadmin-wolk')));
    }

    // =========================================================================
    // AJAX HANDLERS - USUARIOS
    // =========================================================================

    public function ajax_save_usuario() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $data = array(
            'usuario_wp_id' => absint($_POST['usuario_wp_id']),
            'puesto_id' => absint($_POST['puesto_id']),
            'departamento_id' => absint($_POST['departamento_id']),
            'codigo_empleado' => sanitize_text_field($_POST['codigo_empleado']),
            'fecha_ingreso' => sanitize_text_field($_POST['fecha_ingreso']),
            'nivel_jerarquico' => absint($_POST['nivel_jerarquico']),
            'metodo_pago_preferido' => sanitize_text_field($_POST['metodo_pago_preferido']),
            'pais_residencia' => sanitize_text_field($_POST['pais_residencia']),
            'activo' => absint($_POST['activo']),
        );

        $result = GA_Usuarios::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Usuario guardado', 'gestionadmin-wolk'), 'id' => $result));
    }

    // =========================================================================
    // AJAX HANDLERS - PAÍSES
    // =========================================================================

    public function ajax_save_pais() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) && !empty($_POST['id']) ? absint($_POST['id']) : 0;
        $data = array(
            'nombre' => sanitize_text_field($_POST['nombre']),
            'moneda_codigo' => sanitize_text_field($_POST['moneda_codigo']),
            'moneda_simbolo' => sanitize_text_field($_POST['moneda_simbolo']),
            'impuesto_nombre' => sanitize_text_field($_POST['impuesto_nombre']),
            'impuesto_porcentaje' => floatval($_POST['impuesto_porcentaje']),
            'retencion_default' => floatval($_POST['retencion_default']),
            'requiere_electronica' => absint($_POST['requiere_electronica']),
            'proveedor_electronica' => sanitize_text_field($_POST['proveedor_electronica']),
            'activo' => absint($_POST['activo']),
        );

        // Para nuevos países, incluir código ISO
        if ($id === 0 && !empty($_POST['codigo_iso'])) {
            $data['codigo_iso'] = strtoupper(sanitize_text_field($_POST['codigo_iso']));

            // Validar formato (2 letras)
            if (!preg_match('/^[A-Z]{2}$/', $data['codigo_iso'])) {
                wp_send_json_error(array('message' => __('El código ISO debe ser exactamente 2 letras', 'gestionadmin-wolk')));
            }
        }

        $result = GA_Paises::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        $message = $id > 0 ? __('País actualizado', 'gestionadmin-wolk') : __('País creado', 'gestionadmin-wolk');
        wp_send_json_success(array('message' => $message));
    }

    public function ajax_delete_pais() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);

        if (!$id) {
            wp_send_json_error(array('message' => __('ID inválido', 'gestionadmin-wolk')));
        }

        $result = GA_Paises::delete($id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('País eliminado', 'gestionadmin-wolk')));
    }

    // =========================================================================
    // AJAX HANDLERS - TAREAS
    // =========================================================================

    public function ajax_save_tarea() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $data = array(
            'nombre' => sanitize_text_field($_POST['nombre']),
            'descripcion' => sanitize_textarea_field($_POST['descripcion']),
            'asignado_a' => absint($_POST['asignado_a']),
            'supervisor_id' => absint($_POST['supervisor_id']),
            'minutos_estimados' => absint($_POST['minutos_estimados']),
            'fecha_inicio' => sanitize_text_field($_POST['fecha_inicio']),
            'fecha_limite' => sanitize_text_field($_POST['fecha_limite']),
            'prioridad' => sanitize_text_field($_POST['prioridad']),
            'estado' => sanitize_text_field($_POST['estado']),
            // Sprint 5-6: Campos de proyecto y caso
            'proyecto_id' => !empty($_POST['proyecto_id']) ? absint($_POST['proyecto_id']) : null,
            'caso_id' => !empty($_POST['caso_id']) ? absint($_POST['caso_id']) : null,
        );

        // Procesar subtareas
        $subtareas = array();
        if (!empty($_POST['subtareas']) && is_array($_POST['subtareas'])) {
            foreach ($_POST['subtareas'] as $subtarea) {
                $subtareas[] = array(
                    'id' => isset($subtarea['id']) ? absint($subtarea['id']) : 0,
                    'nombre' => sanitize_text_field($subtarea['nombre']),
                    'descripcion' => isset($subtarea['descripcion']) ? sanitize_textarea_field($subtarea['descripcion']) : '',
                    'minutos_estimados' => absint($subtarea['minutos_estimados'] ?? 15),
                    'orden' => absint($subtarea['orden']),
                );
            }
        }

        $result = GA_Tareas::save($id, $data, $subtareas);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Tarea guardada', 'gestionadmin-wolk'), 'id' => $result));
    }

    public function ajax_delete_tarea() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $result = GA_Tareas::delete($id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Tarea eliminada', 'gestionadmin-wolk')));
    }

    // =========================================================================
    // AJAX HANDLERS - TIMER
    // =========================================================================

    public function ajax_timer_start() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        $tarea_id = absint($_POST['tarea_id']);
        $subtarea_id = isset($_POST['subtarea_id']) ? absint($_POST['subtarea_id']) : 0;
        $usuario_id = get_current_user_id();

        $result = GA_Tareas::timer_start($tarea_id, $subtarea_id, $usuario_id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Timer iniciado', 'gestionadmin-wolk'),
            'registro_id' => $result,
            'hora_inicio' => current_time('mysql')
        ));
    }

    public function ajax_timer_pause() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        $registro_id = absint($_POST['registro_id']);
        $motivo = sanitize_text_field($_POST['motivo']);
        $nota = sanitize_text_field($_POST['nota']);

        $result = GA_Tareas::timer_pause($registro_id, $motivo, $nota);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Timer pausado', 'gestionadmin-wolk')));
    }

    public function ajax_timer_resume() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        $registro_id = absint($_POST['registro_id']);

        $result = GA_Tareas::timer_resume($registro_id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Timer reanudado', 'gestionadmin-wolk')));
    }

    public function ajax_timer_stop() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        $registro_id = absint($_POST['registro_id']);
        $descripcion = sanitize_textarea_field($_POST['descripcion']);

        $result = GA_Tareas::timer_stop($registro_id, $descripcion);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Timer detenido', 'gestionadmin-wolk'),
            'minutos_totales' => $result['minutos_totales'],
            'minutos_efectivos' => $result['minutos_efectivos']
        ));
    }

    public function ajax_get_timer_status() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        $usuario_id = get_current_user_id();
        $result = GA_Tareas::get_active_timer($usuario_id);

        wp_send_json_success($result);
    }

    // =========================================================================
    // AJAX HANDLERS - CLIENTES (Sprint 5-6)
    // =========================================================================

    /**
     * AJAX: Guardar cliente
     *
     * Crea o actualiza un cliente con todos sus datos fiscales.
     */
    public function ajax_save_cliente() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        // Obtener ID (0 para nuevo cliente)
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        // Sanitizar todos los campos del formulario
        $data = array(
            'codigo'              => sanitize_text_field($_POST['codigo'] ?? ''),
            'tipo'                => sanitize_text_field($_POST['tipo'] ?? 'EMPRESA'),
            'nombre_comercial'    => sanitize_text_field($_POST['nombre_comercial']),
            'razon_social'        => sanitize_text_field($_POST['razon_social'] ?? ''),
            'documento_tipo'      => sanitize_text_field($_POST['documento_tipo'] ?? ''),
            'documento_numero'    => sanitize_text_field($_POST['documento_numero'] ?? ''),
            'email'               => sanitize_email($_POST['email'] ?? ''),
            'telefono'            => sanitize_text_field($_POST['telefono'] ?? ''),
            'pais'                => sanitize_text_field($_POST['pais'] ?? ''),
            'ciudad'              => sanitize_text_field($_POST['ciudad'] ?? ''),
            'direccion'           => sanitize_textarea_field($_POST['direccion'] ?? ''),
            'regimen_fiscal'      => sanitize_text_field($_POST['regimen_fiscal'] ?? ''),
            'retencion_default'   => floatval($_POST['retencion_default'] ?? 0),
            'contacto_nombre'     => sanitize_text_field($_POST['contacto_nombre'] ?? ''),
            'contacto_cargo'      => sanitize_text_field($_POST['contacto_cargo'] ?? ''),
            'contacto_email'      => sanitize_email($_POST['contacto_email'] ?? ''),
            'contacto_telefono'   => sanitize_text_field($_POST['contacto_telefono'] ?? ''),
            'metodo_pago_preferido' => sanitize_text_field($_POST['metodo_pago_preferido'] ?? 'TRANSFERENCIA'),
            'url_logo'            => esc_url_raw($_POST['url_logo'] ?? ''),
            'notas'               => sanitize_textarea_field($_POST['notas'] ?? ''),
            'activo'              => absint($_POST['activo'] ?? 1),
        );

        // Guardar cliente
        $result = GA_Clientes::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Cliente guardado correctamente', 'gestionadmin-wolk'),
            'id' => $result
        ));
    }

    /**
     * AJAX: Eliminar cliente (soft delete)
     */
    public function ajax_delete_cliente() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);

        // Verificar que no tenga casos activos
        $casos_count = GA_Clientes::count_casos($id);
        if ($casos_count > 0) {
            wp_send_json_error(array(
                'message' => sprintf(
                    __('No se puede eliminar: el cliente tiene %d caso(s) activo(s)', 'gestionadmin-wolk'),
                    $casos_count
                )
            ));
        }

        $result = GA_Clientes::delete($id);

        if (!$result) {
            wp_send_json_error(array('message' => __('Error al eliminar cliente', 'gestionadmin-wolk')));
        }

        wp_send_json_success(array('message' => __('Cliente eliminado', 'gestionadmin-wolk')));
    }

    // =========================================================================
    // AJAX HANDLERS - CASOS (Sprint 5-6)
    // =========================================================================

    /**
     * AJAX: Guardar caso
     *
     * Crea o actualiza un caso/expediente de cliente.
     */
    public function ajax_save_caso() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        $data = array(
            'cliente_id'           => absint($_POST['cliente_id']),
            'titulo'               => sanitize_text_field($_POST['titulo']),
            'descripcion'          => sanitize_textarea_field($_POST['descripcion'] ?? ''),
            'tipo'                 => sanitize_text_field($_POST['tipo'] ?? 'PROYECTO'),
            'estado'               => sanitize_text_field($_POST['estado'] ?? 'ABIERTO'),
            'prioridad'            => sanitize_text_field($_POST['prioridad'] ?? 'MEDIA'),
            'fecha_apertura'       => sanitize_text_field($_POST['fecha_apertura'] ?? ''),
            'fecha_cierre_estimada' => sanitize_text_field($_POST['fecha_cierre_estimada'] ?? '') ?: null,
            'responsable_id'       => absint($_POST['responsable_id'] ?? 0) ?: null,
            'presupuesto_horas'    => absint($_POST['presupuesto_horas'] ?? 0) ?: null,
            'presupuesto_dinero'   => floatval($_POST['presupuesto_dinero'] ?? 0) ?: null,
            'notas'                => sanitize_textarea_field($_POST['notas'] ?? ''),
        );

        $result = GA_Casos::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Caso guardado correctamente', 'gestionadmin-wolk'),
            'id' => $result
        ));
    }

    /**
     * AJAX: Eliminar caso (cambiar estado a cancelado)
     */
    public function ajax_delete_caso() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);

        // Verificar que no tenga proyectos activos
        $proyectos_count = GA_Casos::count_proyectos($id);
        if ($proyectos_count > 0) {
            wp_send_json_error(array(
                'message' => sprintf(
                    __('No se puede cancelar: el caso tiene %d proyecto(s)', 'gestionadmin-wolk'),
                    $proyectos_count
                )
            ));
        }

        $result = GA_Casos::change_estado($id, 'CANCELADO');

        if (!$result) {
            wp_send_json_error(array('message' => __('Error al cancelar caso', 'gestionadmin-wolk')));
        }

        wp_send_json_success(array('message' => __('Caso cancelado', 'gestionadmin-wolk')));
    }

    /**
     * AJAX: Obtener casos por cliente
     *
     * Retorna lista de casos para dropdown dinámico.
     */
    public function ajax_get_casos_by_cliente() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        $cliente_id = absint($_GET['cliente_id'] ?? 0);
        $casos = GA_Casos::get_for_dropdown($cliente_id);

        wp_send_json_success($casos);
    }

    // =========================================================================
    // AJAX HANDLERS - PROYECTOS (Sprint 5-6)
    // =========================================================================

    /**
     * AJAX: Guardar proyecto
     *
     * Crea o actualiza un proyecto dentro de un caso.
     */
    public function ajax_save_proyecto() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        $data = array(
            'caso_id'              => absint($_POST['caso_id']),
            'codigo'               => sanitize_text_field($_POST['codigo'] ?? ''),
            'nombre'               => sanitize_text_field($_POST['nombre']),
            'descripcion'          => sanitize_textarea_field($_POST['descripcion'] ?? ''),
            'fecha_inicio'         => sanitize_text_field($_POST['fecha_inicio'] ?? '') ?: null,
            'fecha_fin_estimada'   => sanitize_text_field($_POST['fecha_fin_estimada'] ?? '') ?: null,
            'estado'               => sanitize_text_field($_POST['estado'] ?? 'PLANIFICACION'),
            'responsable_id'       => absint($_POST['responsable_id'] ?? 0) ?: null,
            'presupuesto_horas'    => floatval($_POST['presupuesto_horas'] ?? 0),
            'tarifa_hora'          => floatval($_POST['tarifa_hora'] ?? 0),
            'descuento_porcentaje' => floatval($_POST['descuento_porcentaje'] ?? 0),
            'descuento_monto'      => floatval($_POST['descuento_monto'] ?? 0),
            'subtotal'             => floatval($_POST['subtotal'] ?? 0),
            'total'                => floatval($_POST['total'] ?? 0),
            'presupuesto_dinero'   => floatval($_POST['presupuesto_dinero'] ?? 0),
            'mostrar_ranking'      => absint($_POST['mostrar_ranking'] ?? 0),
            'mostrar_tareas_equipo' => absint($_POST['mostrar_tareas_equipo'] ?? 1),
            'mostrar_horas_equipo' => absint($_POST['mostrar_horas_equipo'] ?? 0),
            'notas'                => sanitize_textarea_field($_POST['notas'] ?? ''),
        );

        $result = GA_Proyectos::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Proyecto guardado correctamente', 'gestionadmin-wolk'),
            'id' => $result
        ));
    }

    /**
     * AJAX: Eliminar proyecto (cambiar estado a cancelado)
     */
    public function ajax_delete_proyecto() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $result = GA_Proyectos::change_estado($id, 'CANCELADO');

        if (!$result) {
            wp_send_json_error(array('message' => __('Error al cancelar proyecto', 'gestionadmin-wolk')));
        }

        wp_send_json_success(array('message' => __('Proyecto cancelado', 'gestionadmin-wolk')));
    }

    /**
     * AJAX: Obtener proyectos por caso
     *
     * Retorna lista de proyectos para dropdown dinámico.
     */
    public function ajax_get_proyectos_by_caso() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        $caso_id = absint($_GET['caso_id'] ?? 0);
        $proyectos = GA_Proyectos::get_for_dropdown($caso_id);

        wp_send_json_success($proyectos);
    }

    // =========================================================================
    // RENDER PAGES - SPRINT 7-8 (MARKETPLACE)
    // =========================================================================

    /**
     * Renderizar Órdenes de Trabajo
     *
     * Página de gestión de órdenes de trabajo del Marketplace.
     * Permite crear, editar, publicar y gestionar órdenes.
     *
     * @since 1.3.0
     */
    public function render_ordenes_trabajo() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/ordenes-trabajo.php';
    }

    /**
     * Renderizar Aplicantes
     *
     * Página de gestión de aplicantes (freelancers/empresas).
     * Permite verificar, gestionar y revisar perfiles.
     *
     * @since 1.3.0
     */
    public function render_aplicantes() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/aplicantes.php';
    }

    // =========================================================================
    // RENDER PAGES - SPRINT 9-10 (FACTURACIÓN)
    // =========================================================================

    /**
     * Renderizar Facturas
     *
     * Página de gestión de facturas con soporte multi-país.
     * Permite crear, editar, enviar y gestionar pagos de facturas.
     *
     * @since 1.4.0
     */
    public function render_facturas() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/facturas.php';
    }

    /**
     * Renderizar Cotizaciones
     *
     * Página de gestión de cotizaciones/presupuestos.
     * Permite crear, enviar, aprobar/rechazar y convertir a factura.
     *
     * @since 1.4.0
     */
    public function render_cotizaciones() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/cotizaciones.php';
    }

    // =========================================================================
    // RENDER PAGES - SPRINT 11-12 (CONFIG. ECONÓMICA)
    // =========================================================================

    /**
     * Renderizar Mis Empresas
     *
     * Página de gestión de empresas propias (emisoras de facturas).
     * Permite administrar múltiples empresas por país.
     *
     * @since 1.5.0
     */
    public function render_empresas() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/empresas.php';
    }

    /**
     * Renderizar Catálogo de Bonos
     *
     * Página de gestión del catálogo de bonos predefinidos.
     * Los bonos se pueden asignar a órdenes de trabajo.
     *
     * @since 1.5.0
     */
    public function render_catalogo_bonos() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/catalogo-bonos.php';
    }

    /**
     * Renderizar página de Comisiones Generadas
     *
     * Muestra listado de comisiones calculadas automáticamente
     * cuando se pagan facturas de órdenes de trabajo.
     *
     * @since 1.5.0
     */
    public function render_comisiones() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/comisiones.php';
    }

    /**
     * Renderizar página de Solicitudes de Cobro
     *
     * Gestiona solicitudes de pago de los proveedores.
     * Permite aprobar, rechazar y marcar como pagadas.
     *
     * @since 1.5.0
     */
    public function render_solicitudes_cobro() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/solicitudes-cobro.php';
    }

    /**
     * Renderizar página de Métodos de Pago
     *
     * Gestiona el catálogo de cuentas bancarias, wallets digitales
     * y otros métodos de pago de la empresa.
     *
     * @since 1.6.0
     */
    public function render_metodos_pago() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/metodos-pago.php';
    }

    // =========================================================================
    // AJAX HANDLERS - ÓRDENES DE TRABAJO (Sprint 7-8)
    // =========================================================================

    /**
     * AJAX: Guardar orden de trabajo
     *
     * Crea o actualiza una orden de trabajo del Marketplace.
     * Genera código automático OT-YYYY-NNNN para nuevas órdenes.
     * Sprint 11-12: Incluye empresa_id y acuerdos económicos.
     *
     * @since 1.3.0
     * @updated 1.5.0 - Agregado soporte para empresa_id y acuerdos
     */
    public function ajax_save_orden_trabajo() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        // Procesar habilidades (viene como JSON string)
        $habilidades = array();
        if (!empty($_POST['habilidades_requeridas'])) {
            $habilidades = json_decode(stripslashes($_POST['habilidades_requeridas']), true);
            if (!is_array($habilidades)) {
                $habilidades = array();
            }
        }

        $data = array(
            'id'                      => $id,
            'titulo'                  => sanitize_text_field($_POST['titulo']),
            'descripcion'             => wp_kses_post($_POST['descripcion'] ?? ''),
            'categoria'               => sanitize_text_field($_POST['categoria'] ?? 'OTRO'),
            'tipo_pago'               => sanitize_text_field($_POST['tipo_pago'] ?? 'A_CONVENIR'),
            'tarifa_hora_min'         => floatval($_POST['tarifa_hora_min'] ?? 0) ?: null,
            'tarifa_hora_max'         => floatval($_POST['tarifa_hora_max'] ?? 0) ?: null,
            'presupuesto_fijo'        => floatval($_POST['presupuesto_fijo'] ?? 0) ?: null,
            'modalidad'               => sanitize_text_field($_POST['modalidad'] ?? 'REMOTO'),
            'ubicacion_requerida'     => sanitize_text_field($_POST['ubicacion_requerida'] ?? ''),
            'nivel_experiencia'       => sanitize_text_field($_POST['nivel_experiencia'] ?? 'CUALQUIERA'),
            'habilidades_requeridas'  => $habilidades,
            'requisitos_adicionales'  => wp_kses_post($_POST['requisitos_adicionales'] ?? ''),
            'url_manual'              => esc_url_raw($_POST['url_manual'] ?? ''),
            'fecha_limite_aplicacion' => sanitize_text_field($_POST['fecha_limite_aplicacion'] ?? '') ?: null,
            'fecha_inicio_estimada'   => sanitize_text_field($_POST['fecha_inicio_estimada'] ?? '') ?: null,
            'duracion_estimada_dias'  => absint($_POST['duracion_estimada_dias'] ?? 0) ?: null,
            'estado'                  => sanitize_text_field($_POST['estado'] ?? 'BORRADOR'),
            'prioridad'               => sanitize_text_field($_POST['prioridad'] ?? 'NORMAL'),
            'cliente_id'              => absint($_POST['cliente_id'] ?? 0) ?: null,
            'caso_id'                 => absint($_POST['caso_id'] ?? 0) ?: null,
            'proyecto_id'             => absint($_POST['proyecto_id'] ?? 0) ?: null,
            // Sprint 11-12: Empresa pagadora
            'empresa_id'              => absint($_POST['empresa_id'] ?? 0) ?: null,
        );

        $result = GA_Ordenes_Trabajo::save($data);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        $orden_id = $result['id'];

        // Sprint 11-12: Guardar acuerdos económicos
        if (!empty($_POST['acuerdos'])) {
            $acuerdos_data = json_decode(stripslashes($_POST['acuerdos']), true);
            if (is_array($acuerdos_data) && !empty($acuerdos_data)) {
                $acuerdos_instance = GA_Ordenes_Acuerdos::get_instance();
                $acuerdos_result = $acuerdos_instance->guardar_acuerdos($orden_id, $acuerdos_data);
                if (is_wp_error($acuerdos_result)) {
                    // No fallamos la operación principal, pero agregamos mensaje
                    $result['message'] .= ' ' . __('Nota: Error al guardar algunos acuerdos.', 'gestionadmin-wolk');
                }
            }
        }

        // Guardar bonos disponibles
        if (isset($_POST['bonos'])) {
            $bonos_data = json_decode(stripslashes($_POST['bonos']), true);
            if (is_array($bonos_data)) {
                GA_Ordenes_Bonos::guardar_bonos($orden_id, $bonos_data);
            }
        }

        wp_send_json_success(array(
            'message' => $result['message'],
            'id'      => $orden_id,
            'codigo'  => $result['codigo'] ?? '',
        ));
    }

    /**
     * AJAX: Eliminar orden de trabajo
     *
     * Solo permite eliminar órdenes en estado BORRADOR o CANCELADA
     * y que no tengan aplicaciones.
     *
     * @since 1.3.0
     */
    public function ajax_delete_orden_trabajo() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $result = GA_Ordenes_Trabajo::delete($id);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        wp_send_json_success(array('message' => $result['message']));
    }

    /**
     * AJAX: Cambiar estado de orden de trabajo
     *
     * Valida transiciones permitidas antes de cambiar el estado.
     *
     * @since 1.3.0
     */
    public function ajax_change_orden_estado() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $estado = sanitize_text_field($_POST['estado']);

        $result = GA_Ordenes_Trabajo::cambiar_estado($id, $estado);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        wp_send_json_success(array('message' => $result['message']));
    }

    // =========================================================================
    // AJAX HANDLERS - APLICANTES (Sprint 7-8)
    // =========================================================================

    /**
     * AJAX: Guardar aplicante
     *
     * Crea o actualiza un aplicante (freelancer/empresa).
     *
     * @since 1.3.0
     */
    public function ajax_save_aplicante() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        // Procesar habilidades (viene como JSON string)
        $habilidades = array();
        if (!empty($_POST['habilidades'])) {
            $habilidades = json_decode(stripslashes($_POST['habilidades']), true);
            if (!is_array($habilidades)) {
                $habilidades = array();
            }
        }

        $data = array(
            'id'                    => $id,
            'tipo'                  => sanitize_text_field($_POST['tipo'] ?? 'PERSONA_NATURAL'),
            'nombre_completo'       => sanitize_text_field($_POST['nombre_completo']),
            'email'                 => sanitize_email($_POST['email']),
            'telefono'              => sanitize_text_field($_POST['telefono'] ?? ''),
            'pais'                  => sanitize_text_field($_POST['pais'] ?? ''),
            'ciudad'                => sanitize_text_field($_POST['ciudad'] ?? ''),
            'documento_tipo'        => sanitize_text_field($_POST['documento_tipo'] ?? ''),
            'documento_numero'      => sanitize_text_field($_POST['documento_numero'] ?? ''),
            'titulo_profesional'    => sanitize_text_field($_POST['titulo_profesional'] ?? ''),
            'bio'                   => wp_kses_post($_POST['bio'] ?? ''),
            'habilidades'           => $habilidades,
            'nivel_experiencia'     => sanitize_text_field($_POST['nivel_experiencia'] ?? 'JUNIOR'),
            'anos_experiencia'      => absint($_POST['anos_experiencia'] ?? 0),
            'tarifa_hora_min'       => floatval($_POST['tarifa_hora_min'] ?? 0) ?: null,
            'tarifa_hora_max'       => floatval($_POST['tarifa_hora_max'] ?? 0) ?: null,
            'disponibilidad_horas'  => absint($_POST['disponibilidad_horas'] ?? 40),
            'disponible_inmediato'  => absint($_POST['disponible_inmediato'] ?? 0),
            'portfolio_url'         => esc_url_raw($_POST['portfolio_url'] ?? ''),
            'linkedin_url'          => esc_url_raw($_POST['linkedin_url'] ?? ''),
            'github_url'            => esc_url_raw($_POST['github_url'] ?? ''),
            'metodo_pago_preferido' => sanitize_text_field($_POST['metodo_pago_preferido'] ?? ''),
            'estado'                => sanitize_text_field($_POST['estado'] ?? 'PENDIENTE_VERIFICACION'),
            'notas_admin'           => sanitize_textarea_field($_POST['notas_admin'] ?? ''),
        );

        $result = GA_Aplicantes::save($data);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        wp_send_json_success(array(
            'message' => $result['message'],
            'id'      => $result['id'],
        ));
    }

    /**
     * AJAX: Eliminar aplicante
     *
     * Solo permite eliminar si no tiene aplicaciones activas.
     *
     * @since 1.3.0
     */
    public function ajax_delete_aplicante() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $result = GA_Aplicantes::delete($id);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        wp_send_json_success(array('message' => $result['message']));
    }

    /**
     * AJAX: Cambiar estado de aplicante
     *
     * Permite verificar, rechazar o suspender aplicantes.
     *
     * @since 1.3.0
     */
    public function ajax_change_aplicante_estado() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $estado = sanitize_text_field($_POST['estado']);
        $notas = sanitize_textarea_field($_POST['notas'] ?? '');

        $result = GA_Aplicantes::cambiar_estado($id, $estado, $notas);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        wp_send_json_success(array('message' => $result['message']));
    }

    // =========================================================================
    // AJAX HANDLERS - APLICACIONES (Sprint 7-8)
    // =========================================================================

    /**
     * AJAX: Guardar aplicación (postulación)
     *
     * Crea una nueva aplicación de un aplicante a una orden.
     *
     * @since 1.3.0
     */
    public function ajax_save_aplicacion() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        // Las aplicaciones pueden ser creadas por aplicantes verificados
        $data = array(
            'orden_trabajo_id'   => absint($_POST['orden_trabajo_id']),
            'aplicante_id'       => absint($_POST['aplicante_id']),
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
            'message' => $result['message'],
            'id'      => $result['id'],
        ));
    }

    /**
     * AJAX: Cambiar estado de aplicación
     *
     * Permite avanzar en el flujo de evaluación de aplicaciones.
     *
     * @since 1.3.0
     */
    public function ajax_change_aplicacion_estado() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $id = absint($_POST['id']);
        $estado = sanitize_text_field($_POST['estado']);
        $notas = sanitize_textarea_field($_POST['notas'] ?? '');

        $result = GA_Aplicaciones::cambiar_estado($id, $estado, $notas);

        if (!$result['success']) {
            wp_send_json_error(array('message' => $result['message']));
        }

        // NOTA: La lógica de CONTRATADO (rechazar otras, transición aplicante→empleado)
        // se maneja internamente en GA_Aplicaciones::cambiar_estado()

        wp_send_json_success(array('message' => $result['message']));
    }

    // =========================================================================
    // PÁGINAS DEL PLUGIN (Configuración)
    // =========================================================================

    /**
     * Renderizar Gestión de Páginas
     *
     * Panel para crear, verificar y gestionar las páginas
     * de los portales públicos del plugin.
     *
     * @since 1.3.0
     */
    public function render_paginas() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/paginas.php';
    }

    // =========================================================================
    // AJAX HANDLERS - PÁGINAS DEL PLUGIN
    // =========================================================================

    /**
     * AJAX: Crear una página específica
     *
     * @since 1.3.0
     */
    public function ajax_create_page() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $page_key = sanitize_text_field($_POST['page_key'] ?? '');

        if (empty($page_key)) {
            wp_send_json_error(array('message' => __('Clave de página no especificada', 'gestionadmin-wolk')));
        }

        require_once GA_PLUGIN_DIR . 'includes/class-ga-pages-manager.php';
        $pages_manager = GA_Pages_Manager::get_instance();

        $result = $pages_manager->create_page($page_key);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX: Recrear una página
     *
     * @since 1.3.0
     */
    public function ajax_recreate_page() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        $page_key = sanitize_text_field($_POST['page_key'] ?? '');

        if (empty($page_key)) {
            wp_send_json_error(array('message' => __('Clave de página no especificada', 'gestionadmin-wolk')));
        }

        require_once GA_PLUGIN_DIR . 'includes/class-ga-pages-manager.php';
        $pages_manager = GA_Pages_Manager::get_instance();

        $result = $pages_manager->recreate_page($page_key);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX: Crear todas las páginas faltantes
     *
     * @since 1.3.0
     */
    public function ajax_create_all_pages() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        require_once GA_PLUGIN_DIR . 'includes/class-ga-pages-manager.php';
        $pages_manager = GA_Pages_Manager::get_instance();

        $result = $pages_manager->create_all_pages();

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    // =========================================================================
    // NOTIFICACIONES POR EMAIL
    // =========================================================================

    /**
     * Renderizar página de configuración de Notificaciones
     *
     * Panel para activar/desactivar notificaciones por email
     * para diferentes eventos del sistema.
     *
     * @since 1.6.0
     */
    public function render_notificaciones() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'gestionadmin-wolk'));
        }
        include GA_PLUGIN_DIR . 'admin/views/notificaciones-config.php';
    }

    /**
     * AJAX: Guardar configuración de notificaciones
     *
     * @since 1.6.0
     */
    public function ajax_save_notificaciones_config() {
        check_ajax_referer('ga_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'gestionadmin-wolk')));
        }

        // Obtener configuración actual
        $config = get_option('ga_notificaciones_config', array());

        // Procesar cada tipo de notificación
        $tipos_post = isset($_POST['tipos']) ? (array) $_POST['tipos'] : array();

        // Obtener todos los tipos disponibles
        require_once GA_PLUGIN_DIR . 'includes/class-ga-notificaciones.php';
        $todos_tipos = array_keys(GA_Notificaciones::TIPOS);

        // Actualizar configuración
        foreach ($todos_tipos as $tipo) {
            $config[$tipo] = in_array($tipo, $tipos_post) ? 1 : 0;
        }

        // Guardar
        update_option('ga_notificaciones_config', $config);

        wp_send_json_success(array(
            'message' => __('Configuración guardada correctamente', 'gestionadmin-wolk')
        ));
    }
}
