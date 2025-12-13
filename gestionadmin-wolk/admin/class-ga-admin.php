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

        // AJAX handlers
        add_action('wp_ajax_ga_save_departamento', array($this, 'ajax_save_departamento'));
        add_action('wp_ajax_ga_delete_departamento', array($this, 'ajax_delete_departamento'));
        add_action('wp_ajax_ga_save_puesto', array($this, 'ajax_save_puesto'));
        add_action('wp_ajax_ga_delete_puesto', array($this, 'ajax_delete_puesto'));
        add_action('wp_ajax_ga_save_escala', array($this, 'ajax_save_escala'));
        add_action('wp_ajax_ga_delete_escala', array($this, 'ajax_delete_escala'));
        add_action('wp_ajax_ga_save_usuario', array($this, 'ajax_save_usuario'));
        add_action('wp_ajax_ga_save_pais', array($this, 'ajax_save_pais'));
        add_action('wp_ajax_ga_save_tarea', array($this, 'ajax_save_tarea'));
        add_action('wp_ajax_ga_delete_tarea', array($this, 'ajax_delete_tarea'));
        add_action('wp_ajax_ga_timer_start', array($this, 'ajax_timer_start'));
        add_action('wp_ajax_ga_timer_pause', array($this, 'ajax_timer_pause'));
        add_action('wp_ajax_ga_timer_resume', array($this, 'ajax_timer_resume'));
        add_action('wp_ajax_ga_timer_stop', array($this, 'ajax_timer_stop'));
        add_action('wp_ajax_ga_get_timer_status', array($this, 'ajax_get_timer_status'));
    }

    /**
     * Cargar módulos
     */
    private function load_modules() {
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-departamentos.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-puestos.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-usuarios.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-paises.php';
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';
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
}
