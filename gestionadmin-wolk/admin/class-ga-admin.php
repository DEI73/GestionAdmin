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

        $id = absint($_POST['id']);
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

        $result = GA_Paises::save($id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('País actualizado', 'gestionadmin-wolk')));
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
            'horas_estimadas' => floatval($_POST['horas_estimadas']),
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
                    'horas_estimadas' => floatval($subtarea['horas_estimadas']),
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
            'presupuesto_horas'    => absint($_POST['presupuesto_horas'] ?? 0) ?: null,
            'presupuesto_dinero'   => floatval($_POST['presupuesto_dinero'] ?? 0) ?: null,
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
}
