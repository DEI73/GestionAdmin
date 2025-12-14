<?php
/**
 * Gestor de Páginas del Plugin
 *
 * Maneja la creación, verificación y gestión de todas las páginas
 * necesarias para el funcionamiento de los portales públicos.
 *
 * Páginas gestionadas:
 * - Marketplace (/trabajo/)
 * - Portal Aplicantes (/mi-cuenta/, /registro-aplicante/)
 * - Portal Empleados (/empleado/)
 * - Portal Clientes (/cliente/)
 * - Login personalizado (/acceso/)
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GA_Pages_Manager
 *
 * Gestiona todas las páginas del plugin de forma centralizada.
 *
 * @since 1.3.0
 */
class GA_Pages_Manager {

    /**
     * Prefijo para opciones de WordPress
     *
     * @var string
     */
    const OPTION_PREFIX = 'ga_page_';

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Pages_Manager
     */
    private static $instance = null;

    /**
     * Configuración de todas las páginas del plugin
     *
     * @var array
     */
    private $pages_config = array();

    /**
     * Obtener instancia única
     *
     * @since 1.3.0
     *
     * @return GA_Pages_Manager
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
     * Define la configuración de todas las páginas del plugin.
     *
     * @since 1.3.0
     */
    private function __construct() {
        $this->pages_config = array(
            // =========================================================================
            // MARKETPLACE - Portal público de órdenes de trabajo
            // =========================================================================
            'marketplace' => array(
                'title'       => __('Marketplace', 'gestionadmin-wolk'),
                'slug'        => 'trabajo',
                'template'    => 'portal-trabajo/archive-ordenes.php',
                'parent'      => null,
                'description' => __('Listado público de órdenes de trabajo disponibles', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-store',
                'portal'      => 'trabajo',
            ),

            // =========================================================================
            // PORTAL APLICANTES - Freelancers y empresas externas
            // =========================================================================
            'registro_aplicante' => array(
                'title'       => __('Registro Aplicante', 'gestionadmin-wolk'),
                'slug'        => 'registro-aplicante',
                'template'    => 'portal-aplicante/registro.php',
                'parent'      => null,
                'description' => __('Formulario de registro para nuevos aplicantes', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-admin-users',
                'portal'      => 'aplicante',
            ),
            'mi_cuenta' => array(
                'title'       => __('Mi Cuenta', 'gestionadmin-wolk'),
                'slug'        => 'mi-cuenta',
                'template'    => 'portal-aplicante/dashboard.php',
                'parent'      => null,
                'description' => __('Dashboard principal del aplicante', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-dashboard',
                'portal'      => 'aplicante',
            ),
            'mis_aplicaciones' => array(
                'title'       => __('Mis Aplicaciones', 'gestionadmin-wolk'),
                'slug'        => 'aplicaciones',
                'template'    => 'portal-aplicante/mis-aplicaciones.php',
                'parent'      => 'mi_cuenta',
                'description' => __('Historial de postulaciones del aplicante', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-clipboard',
                'portal'      => 'aplicante',
            ),
            'mi_perfil_aplicante' => array(
                'title'       => __('Mi Perfil', 'gestionadmin-wolk'),
                'slug'        => 'perfil',
                'template'    => 'portal-aplicante/mi-perfil.php',
                'parent'      => 'mi_cuenta',
                'description' => __('Perfil y configuración del aplicante', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-id',
                'portal'      => 'aplicante',
            ),
            // =========================================================================
            // NOTA: "Mis Pagos" NO aplica para aplicantes
            // =========================================================================
            // Los aplicantes NO tienen pagos. Cuando un aplicante es aceptado en una
            // orden de trabajo, se convierte en EMPLEADO y gestiona sus pagos desde
            // el Portal Empleado (/empleado/pagos/).
            //
            // El archivo mis-pagos.php existe como placeholder que redirige a
            // /mi-cuenta/aplicaciones/ para evitar errores 404 en enlaces antiguos.
            // =========================================================================

            // =========================================================================
            // PORTAL EMPLEADOS - Trabajadores internos
            // =========================================================================
            // NOTA: Slug es 'portal-empleado' para coincidir con los templates
            // existentes que usan /portal-empleado/ en sus enlaces internos
            // =========================================================================
            'empleado_dashboard' => array(
                'title'       => __('Portal Empleado', 'gestionadmin-wolk'),
                'slug'        => 'portal-empleado',
                'template'    => 'portal-empleado/dashboard.php',
                'parent'      => null,
                'description' => __('Dashboard principal del empleado', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-businessman',
                'portal'      => 'empleado',
            ),
            'empleado_tareas' => array(
                'title'       => __('Mis Tareas', 'gestionadmin-wolk'),
                'slug'        => 'mis-tareas',
                'template'    => 'portal-empleado/mis-tareas.php',
                'parent'      => 'empleado_dashboard',
                'description' => __('Tareas asignadas al empleado', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-list-view',
                'portal'      => 'empleado',
            ),
            'empleado_timer' => array(
                'title'       => __('Mi Timer', 'gestionadmin-wolk'),
                'slug'        => 'mi-timer',
                'template'    => 'portal-empleado/mi-timer.php',
                'parent'      => 'empleado_dashboard',
                'description' => __('Timer para registro de horas', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-clock',
                'portal'      => 'empleado',
            ),
            'empleado_horas' => array(
                'title'       => __('Mis Horas', 'gestionadmin-wolk'),
                'slug'        => 'mis-horas',
                'template'    => 'portal-empleado/mis-horas.php',
                'parent'      => 'empleado_dashboard',
                'description' => __('Historial de horas trabajadas', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-backup',
                'portal'      => 'empleado',
            ),
            'empleado_perfil' => array(
                'title'       => __('Mi Perfil', 'gestionadmin-wolk'),
                'slug'        => 'mi-perfil',
                'template'    => 'portal-empleado/mi-perfil.php',
                'parent'      => 'empleado_dashboard',
                'description' => __('Perfil del empleado', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-id',
                'portal'      => 'empleado',
            ),

            // =========================================================================
            // PORTAL CLIENTES - Clientes externos
            // =========================================================================
            'cliente_dashboard' => array(
                'title'       => __('Portal Cliente', 'gestionadmin-wolk'),
                'slug'        => 'cliente',
                'template'    => 'portal-cliente/dashboard.php',
                'parent'      => null,
                'description' => __('Dashboard principal del cliente', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-groups',
                'portal'      => 'cliente',
            ),
            'cliente_casos' => array(
                'title'       => __('Mis Casos', 'gestionadmin-wolk'),
                'slug'        => 'casos',
                'template'    => 'portal-cliente/mis-casos.php',
                'parent'      => 'cliente_dashboard',
                'description' => __('Casos y proyectos del cliente', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-portfolio',
                'portal'      => 'cliente',
            ),
            'cliente_facturas' => array(
                'title'       => __('Mis Facturas', 'gestionadmin-wolk'),
                'slug'        => 'facturas',
                'template'    => 'portal-cliente/mis-facturas.php',
                'parent'      => 'cliente_dashboard',
                'description' => __('Facturas del cliente', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-media-text',
                'portal'      => 'cliente',
            ),
            'cliente_perfil' => array(
                'title'       => __('Mi Perfil', 'gestionadmin-wolk'),
                'slug'        => 'perfil',
                'template'    => 'portal-cliente/mi-perfil.php',
                'parent'      => 'cliente_dashboard',
                'description' => __('Perfil y datos del cliente', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-id',
                'portal'      => 'cliente',
            ),

            // =========================================================================
            // GENERAL - Páginas comunes
            // =========================================================================
            'login' => array(
                'title'       => __('Acceso GestionAdmin', 'gestionadmin-wolk'),
                'slug'        => 'acceso',
                'template'    => 'general/login.php',
                'parent'      => null,
                'description' => __('Página de login personalizada', 'gestionadmin-wolk'),
                'icon'        => 'dashicons-unlock',
                'portal'      => 'general',
            ),
        );
    }

    // =========================================================================
    // GETTERS
    // =========================================================================

    /**
     * Obtiene la configuración de todas las páginas
     *
     * @since 1.3.0
     *
     * @return array Configuración completa de páginas
     */
    public function get_pages_config() {
        return $this->pages_config;
    }

    /**
     * Obtiene la configuración de una página específica
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return array|null Configuración de la página o null si no existe
     */
    public function get_page_config($key) {
        return isset($this->pages_config[$key]) ? $this->pages_config[$key] : null;
    }

    /**
     * Obtiene el ID de una página guardado en opciones
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return int|false ID de la página o false si no existe
     */
    public function get_page_id($key) {
        $page_id = get_option(self::OPTION_PREFIX . $key, false);

        // Verificar que la página realmente existe
        if ($page_id && get_post_status($page_id) !== false) {
            return (int) $page_id;
        }

        return false;
    }

    /**
     * Obtiene la URL de una página
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return string|false URL de la página o false si no existe
     */
    public function get_page_url($key) {
        $page_id = $this->get_page_id($key);
        if ($page_id) {
            return get_permalink($page_id);
        }
        return false;
    }

    /**
     * Obtiene el slug completo de una página (incluyendo parent)
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return string Slug completo
     */
    public function get_full_slug($key) {
        $config = $this->get_page_config($key);
        if (!$config) {
            return '';
        }

        $slug = $config['slug'];

        if ($config['parent']) {
            $parent_config = $this->get_page_config($config['parent']);
            if ($parent_config) {
                $slug = $parent_config['slug'] . '/' . $slug;
            }
        }

        return $slug;
    }

    // =========================================================================
    // STATUS CHECKS
    // =========================================================================

    /**
     * Verifica el estado de una página específica
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return array Estado de la página
     */
    public function get_page_status($key) {
        $config = $this->get_page_config($key);
        if (!$config) {
            return array(
                'exists'           => false,
                'status'           => 'invalid',
                'message'          => __('Configuración no encontrada', 'gestionadmin-wolk'),
                'page_id'          => 0,
                'has_template'     => false,
                'template_exists'  => false,
            );
        }

        $page_id = $this->get_page_id($key);
        $template_path = GA_PLUGIN_DIR . 'templates/' . $config['template'];
        $template_exists = file_exists($template_path);

        if (!$page_id) {
            return array(
                'exists'           => false,
                'status'           => 'not_created',
                'message'          => __('Página no creada', 'gestionadmin-wolk'),
                'page_id'          => 0,
                'has_template'     => false,
                'template_exists'  => $template_exists,
            );
        }

        $post = get_post($page_id);
        if (!$post || $post->post_status === 'trash') {
            // Limpiar la opción si la página fue eliminada
            delete_option(self::OPTION_PREFIX . $key);
            return array(
                'exists'           => false,
                'status'           => 'deleted',
                'message'          => __('Página eliminada', 'gestionadmin-wolk'),
                'page_id'          => 0,
                'has_template'     => false,
                'template_exists'  => $template_exists,
            );
        }

        // Página existe
        $status = 'ok';
        $message = __('Página activa', 'gestionadmin-wolk');

        if (!$template_exists) {
            $status = 'no_template';
            $message = __('Template no encontrado', 'gestionadmin-wolk');
        } elseif ($post->post_status !== 'publish') {
            $status = 'draft';
            $message = sprintf(__('Estado: %s', 'gestionadmin-wolk'), $post->post_status);
        }

        return array(
            'exists'           => true,
            'status'           => $status,
            'message'          => $message,
            'page_id'          => $page_id,
            'post_status'      => $post->post_status,
            'has_template'     => true,
            'template_exists'  => $template_exists,
            'url'              => get_permalink($page_id),
            'edit_url'         => get_edit_post_link($page_id),
        );
    }

    /**
     * Obtiene el estado de todas las páginas
     *
     * @since 1.3.0
     *
     * @return array Estados de todas las páginas
     */
    public function get_all_status() {
        $statuses = array();

        foreach ($this->pages_config as $key => $config) {
            $statuses[$key] = array_merge(
                array('key' => $key, 'config' => $config),
                $this->get_page_status($key)
            );
        }

        return $statuses;
    }

    /**
     * Obtiene resumen de estados
     *
     * @since 1.3.0
     *
     * @return array Resumen con contadores
     */
    public function get_status_summary() {
        $statuses = $this->get_all_status();

        $summary = array(
            'total'        => count($statuses),
            'ok'           => 0,
            'not_created'  => 0,
            'no_template'  => 0,
            'draft'        => 0,
            'deleted'      => 0,
            'invalid'      => 0,
        );

        foreach ($statuses as $status) {
            if (isset($summary[$status['status']])) {
                $summary[$status['status']]++;
            }
        }

        return $summary;
    }

    // =========================================================================
    // PAGE CREATION
    // =========================================================================

    /**
     * Crea una página específica
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página a crear
     *
     * @return array Resultado de la operación
     */
    public function create_page($key) {
        $config = $this->get_page_config($key);
        if (!$config) {
            return array(
                'success' => false,
                'message' => __('Configuración de página no encontrada', 'gestionadmin-wolk'),
            );
        }

        // Verificar si ya existe
        $existing_id = $this->get_page_id($key);
        if ($existing_id && get_post_status($existing_id) !== false) {
            return array(
                'success' => false,
                'message' => __('La página ya existe', 'gestionadmin-wolk'),
                'page_id' => $existing_id,
            );
        }

        // Determinar página padre
        $parent_id = 0;
        if ($config['parent']) {
            $parent_id = $this->get_page_id($config['parent']);
            if (!$parent_id) {
                // Crear padre primero
                $parent_result = $this->create_page($config['parent']);
                if ($parent_result['success']) {
                    $parent_id = $parent_result['page_id'];
                }
            }
        }

        // Crear la página
        $page_data = array(
            'post_title'     => $config['title'],
            'post_name'      => $config['slug'],
            'post_content'   => $this->get_page_content($key, $config),
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_parent'    => $parent_id,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        );

        $page_id = wp_insert_post($page_data);

        if (is_wp_error($page_id)) {
            return array(
                'success' => false,
                'message' => $page_id->get_error_message(),
            );
        }

        // Guardar ID en opciones
        update_option(self::OPTION_PREFIX . $key, $page_id);

        // Agregar meta para identificar como página del plugin
        update_post_meta($page_id, '_ga_page_key', $key);
        update_post_meta($page_id, '_ga_page_template', $config['template']);
        update_post_meta($page_id, '_ga_page_portal', $config['portal']);

        return array(
            'success' => true,
            'message' => sprintf(__('Página "%s" creada correctamente', 'gestionadmin-wolk'), $config['title']),
            'page_id' => $page_id,
            'url'     => get_permalink($page_id),
        );
    }

    /**
     * Crea todas las páginas que faltan
     *
     * @since 1.3.0
     *
     * @return array Resultado de la operación
     */
    public function create_all_pages() {
        $results = array(
            'created'  => array(),
            'existing' => array(),
            'errors'   => array(),
        );

        // Primero crear páginas padre (sin parent)
        foreach ($this->pages_config as $key => $config) {
            if ($config['parent'] === null) {
                $result = $this->create_page($key);
                $this->categorize_result($results, $key, $result);
            }
        }

        // Luego crear páginas hijas
        foreach ($this->pages_config as $key => $config) {
            if ($config['parent'] !== null) {
                $result = $this->create_page($key);
                $this->categorize_result($results, $key, $result);
            }
        }

        return array(
            'success'  => empty($results['errors']),
            'message'  => sprintf(
                __('Creadas: %d | Existentes: %d | Errores: %d', 'gestionadmin-wolk'),
                count($results['created']),
                count($results['existing']),
                count($results['errors'])
            ),
            'details'  => $results,
        );
    }

    /**
     * Categoriza el resultado de creación
     *
     * @param array  &$results Referencia al array de resultados
     * @param string $key      Clave de la página
     * @param array  $result   Resultado de la operación
     */
    private function categorize_result(&$results, $key, $result) {
        if ($result['success']) {
            $results['created'][$key] = $result;
        } elseif (isset($result['page_id']) && $result['page_id']) {
            $results['existing'][$key] = $result;
        } else {
            $results['errors'][$key] = $result;
        }
    }

    /**
     * Genera el contenido de la página
     *
     * @since 1.3.0
     *
     * @param string $key    Clave de la página
     * @param array  $config Configuración de la página
     *
     * @return string Contenido HTML de la página
     */
    private function get_page_content($key, $config) {
        // Contenido mínimo - el template real se carga via template_include
        $content = sprintf(
            '<!-- GestionAdmin Page: %s -->
<!-- Template: %s -->
<!-- Portal: %s -->
<!-- No editar este contenido - Se renderiza dinámicamente -->',
            esc_html($key),
            esc_html($config['template']),
            esc_html($config['portal'])
        );

        return $content;
    }

    // =========================================================================
    // PAGE DELETION
    // =========================================================================

    /**
     * Elimina una página
     *
     * @since 1.3.0
     *
     * @param string $key   Clave de la página
     * @param bool   $force Eliminar permanentemente (true) o enviar a papelera (false)
     *
     * @return array Resultado de la operación
     */
    public function delete_page($key, $force = false) {
        $page_id = $this->get_page_id($key);

        if (!$page_id) {
            return array(
                'success' => false,
                'message' => __('La página no existe', 'gestionadmin-wolk'),
            );
        }

        $config = $this->get_page_config($key);

        // Verificar si tiene páginas hijas
        $children = $this->get_child_pages($key);
        if (!empty($children) && !$force) {
            return array(
                'success' => false,
                'message' => __('Esta página tiene subpáginas. Elimínalas primero.', 'gestionadmin-wolk'),
                'children' => $children,
            );
        }

        // Eliminar páginas hijas primero si es forzado
        if ($force && !empty($children)) {
            foreach ($children as $child_key) {
                $this->delete_page($child_key, true);
            }
        }

        // Eliminar la página
        $result = wp_delete_post($page_id, $force);

        if (!$result) {
            return array(
                'success' => false,
                'message' => __('Error al eliminar la página', 'gestionadmin-wolk'),
            );
        }

        // Limpiar opción
        delete_option(self::OPTION_PREFIX . $key);

        return array(
            'success' => true,
            'message' => sprintf(
                __('Página "%s" eliminada correctamente', 'gestionadmin-wolk'),
                $config['title']
            ),
        );
    }

    /**
     * Obtiene las páginas hijas de una página
     *
     * @since 1.3.0
     *
     * @param string $parent_key Clave de la página padre
     *
     * @return array Claves de las páginas hijas
     */
    public function get_child_pages($parent_key) {
        $children = array();

        foreach ($this->pages_config as $key => $config) {
            if ($config['parent'] === $parent_key) {
                $children[] = $key;
            }
        }

        return $children;
    }

    // =========================================================================
    // TEMPLATE LOADING
    // =========================================================================

    /**
     * Detecta si estamos en una página del plugin
     *
     * @since 1.3.0
     *
     * @return string|false Clave de la página o false
     */
    public function detect_current_page() {
        if (!is_page()) {
            return false;
        }

        $current_page_id = get_queried_object_id();

        foreach ($this->pages_config as $key => $config) {
            $page_id = $this->get_page_id($key);
            if ($page_id && $page_id === $current_page_id) {
                return $key;
            }
        }

        // También verificar por meta
        $page_key = get_post_meta($current_page_id, '_ga_page_key', true);
        if ($page_key && isset($this->pages_config[$page_key])) {
            return $page_key;
        }

        return false;
    }

    /**
     * Obtiene el path del template para una página
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return string|false Path al template o false
     */
    public function get_template_path($key) {
        $config = $this->get_page_config($key);
        if (!$config) {
            return false;
        }

        $template_path = GA_PLUGIN_DIR . 'templates/' . $config['template'];

        if (file_exists($template_path)) {
            return $template_path;
        }

        // Intentar template placeholder
        $placeholder_path = GA_PLUGIN_DIR . 'templates/general/placeholder.php';
        if (file_exists($placeholder_path)) {
            return $placeholder_path;
        }

        return false;
    }

    /**
     * Obtiene el portal de una página
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return string|false Portal o false
     */
    public function get_page_portal($key) {
        $config = $this->get_page_config($key);
        return $config ? $config['portal'] : false;
    }

    // =========================================================================
    // UTILITIES
    // =========================================================================

    /**
     * Obtiene páginas agrupadas por portal
     *
     * @since 1.3.0
     *
     * @return array Páginas agrupadas
     */
    public function get_pages_by_portal() {
        $grouped = array();

        foreach ($this->pages_config as $key => $config) {
            $portal = $config['portal'];
            if (!isset($grouped[$portal])) {
                $grouped[$portal] = array();
            }
            $grouped[$portal][$key] = $config;
        }

        return $grouped;
    }

    /**
     * Verifica si todas las páginas necesarias están creadas
     *
     * @since 1.3.0
     *
     * @return bool
     */
    public function all_pages_exist() {
        foreach ($this->pages_config as $key => $config) {
            if (!$this->get_page_id($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtiene las páginas faltantes
     *
     * @since 1.3.0
     *
     * @return array Claves de páginas faltantes
     */
    public function get_missing_pages() {
        $missing = array();

        foreach ($this->pages_config as $key => $config) {
            if (!$this->get_page_id($key)) {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    /**
     * Recrear una página (eliminar y crear de nuevo)
     *
     * @since 1.3.0
     *
     * @param string $key Clave de la página
     *
     * @return array Resultado de la operación
     */
    public function recreate_page($key) {
        // Eliminar si existe
        $this->delete_page($key, true);

        // Crear de nuevo
        return $this->create_page($key);
    }
}
