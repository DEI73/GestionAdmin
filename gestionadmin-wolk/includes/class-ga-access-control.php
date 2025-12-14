<?php
/**
 * Control de Acceso - Restricción de wp-admin
 *
 * Bloquea el acceso a wp-admin para roles de empleados, clientes y aplicantes.
 * Redirige a los portales correspondientes según el rol del usuario.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes
 * @since      1.6.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase GA_Access_Control
 *
 * Maneja las restricciones de acceso al panel de administración
 * y las redirecciones a los portales frontend.
 */
class GA_Access_Control {

    /**
     * Roles que NO pueden acceder a wp-admin
     *
     * @var array
     */
    private static $restricted_roles = array(
        'ga_empleado',
        'ga_cliente',
        'ga_aplicante',
    );

    /**
     * Roles que SÍ pueden acceder a wp-admin
     *
     * @var array
     */
    private static $admin_roles = array(
        'administrator',
        'ga_socio',
        'ga_director',
        'ga_jefe',
    );

    /**
     * Instancia única (singleton)
     *
     * @var GA_Access_Control
     */
    private static $instance = null;

    /**
     * Obtener instancia única
     *
     * @return GA_Access_Control
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Registra los hooks
     */
    private function __construct() {
        // Ocultar barra de administración para roles restringidos
        add_filter('show_admin_bar', array($this, 'hide_admin_bar'));

        // Bloquear acceso a wp-admin
        add_action('admin_init', array($this, 'block_wp_admin'));

        // Redirigir después del login según el rol
        add_filter('login_redirect', array($this, 'redirect_after_login'), 10, 3);
    }

    /**
     * Verificar si el usuario actual tiene un rol restringido
     *
     * @param WP_User|null $user Usuario a verificar (null = usuario actual)
     * @return bool
     */
    public static function is_restricted_user($user = null) {
        if (null === $user) {
            $user = wp_get_current_user();
        }

        if (!$user || !$user->exists()) {
            return false;
        }

        foreach (self::$restricted_roles as $role) {
            if (in_array($role, (array) $user->roles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario actual puede acceder a wp-admin
     *
     * @param WP_User|null $user Usuario a verificar (null = usuario actual)
     * @return bool
     */
    public static function can_access_admin($user = null) {
        if (null === $user) {
            $user = wp_get_current_user();
        }

        if (!$user || !$user->exists()) {
            return false;
        }

        // Verificar si tiene algún rol de administración
        foreach (self::$admin_roles as $role) {
            if (in_array($role, (array) $user->roles, true)) {
                return true;
            }
        }

        // También verificar capacidad directa
        if (user_can($user, 'manage_options') || user_can($user, 'edit_posts')) {
            return true;
        }

        return false;
    }

    /**
     * Ocultar barra de administración para roles restringidos
     *
     * @param bool $show Mostrar o no la barra
     * @return bool
     */
    public function hide_admin_bar($show) {
        if (self::is_restricted_user()) {
            return false;
        }
        return $show;
    }

    /**
     * Bloquear acceso a wp-admin para roles restringidos
     *
     * Permite acceso a admin-ajax.php y admin-post.php para
     * que funcionen los formularios y llamadas AJAX.
     */
    public function block_wp_admin() {
        // No bloquear si no es área de admin
        if (!is_admin()) {
            return;
        }

        // Permitir AJAX requests
        if (wp_doing_ajax()) {
            return;
        }

        // Permitir admin-post.php (formularios)
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'admin-post.php') !== false) {
            return;
        }

        // Verificar si el usuario está restringido
        if (!self::is_restricted_user()) {
            return;
        }

        // Obtener URL de redirección según el rol
        $redirect_url = $this->get_redirect_url_for_user();

        // Redirigir de forma segura
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Redirigir después del login según el rol del usuario
     *
     * @param string           $redirect_to URL de redirección solicitada
     * @param string           $requested   URL solicitada originalmente
     * @param WP_User|WP_Error $user        Usuario que inició sesión
     * @return string URL de redirección final
     */
    public function redirect_after_login($redirect_to, $requested, $user) {
        // Verificar que sea un usuario válido
        if (!$user || is_wp_error($user) || !$user->exists()) {
            return $redirect_to;
        }

        // Si el usuario tiene rol restringido, redirigir a su portal
        if (self::is_restricted_user($user)) {
            return $this->get_redirect_url_for_user($user);
        }

        // Si el usuario puede acceder a admin, redirigir a wp-admin
        if (self::can_access_admin($user)) {
            return admin_url();
        }

        return $redirect_to;
    }

    /**
     * Obtener URL de redirección según el rol del usuario
     *
     * IMPORTANTE: Las URLs deben coincidir con las configuradas en
     * class-ga-pages-manager.php para evitar errores 404.
     *
     * Este método es público y estático para poder ser usado desde
     * cualquier parte del plugin o tema via ga_get_user_dashboard_url()
     *
     * @param WP_User|null $user Usuario (null = usuario actual)
     * @return string URL de redirección
     */
    public static function get_redirect_url_for_user($user = null) {
        if (null === $user) {
            $user = wp_get_current_user();
        }

        // Empleados → Portal del Empleado (/portal-empleado/)
        if (in_array('ga_empleado', (array) $user->roles, true)) {
            return home_url('/portal-empleado/');
        }

        // Clientes → Portal del Cliente (/cliente/)
        if (in_array('ga_cliente', (array) $user->roles, true)) {
            return home_url('/cliente/');
        }

        // Aplicantes → Portal Aplicante (/mi-cuenta/)
        if (in_array('ga_aplicante', (array) $user->roles, true)) {
            return home_url('/mi-cuenta/');
        }

        // Por defecto, ir al home
        return home_url('/');
    }

    /**
     * Obtener roles restringidos
     *
     * @return array
     */
    public static function get_restricted_roles() {
        return self::$restricted_roles;
    }

    /**
     * Obtener roles con acceso a admin
     *
     * @return array
     */
    public static function get_admin_roles() {
        return self::$admin_roles;
    }
}

// =============================================================================
// FUNCIONES HELPER GLOBALES
// =============================================================================
// Estas funciones están disponibles globalmente para uso en temas y plugins

/**
 * Obtiene la URL del dashboard según el tipo de usuario
 *
 * Función helper global para obtener la URL correcta del portal
 * de un usuario según su rol de WordPress.
 *
 * USO EN TEMAS:
 * <a href="<?php echo esc_url(ga_get_user_dashboard_url()); ?>">Mi Portal</a>
 *
 * @since 1.16.0
 *
 * @param WP_User|int|null $user Usuario, ID de usuario, o null para usuario actual
 * @return string URL del dashboard correspondiente
 */
function ga_get_user_dashboard_url($user = null) {
    // Si es un ID, obtener el objeto WP_User
    if (is_numeric($user)) {
        $user = get_user_by('ID', $user);
    }

    return GA_Access_Control::get_redirect_url_for_user($user);
}

/**
 * Obtiene el tipo de portal del usuario actual
 *
 * @since 1.16.0
 *
 * @param WP_User|null $user Usuario o null para usuario actual
 * @return string Tipo de portal: 'empleado', 'cliente', 'aplicante', 'admin', o 'none'
 */
function ga_get_user_portal_type($user = null) {
    if (null === $user) {
        $user = wp_get_current_user();
    }

    if (!$user || !$user->exists()) {
        return 'none';
    }

    $roles = (array) $user->roles;

    if (in_array('ga_empleado', $roles, true)) {
        return 'empleado';
    }

    if (in_array('ga_cliente', $roles, true)) {
        return 'cliente';
    }

    if (in_array('ga_aplicante', $roles, true)) {
        return 'aplicante';
    }

    if (array_intersect($roles, GA_Access_Control::get_admin_roles())) {
        return 'admin';
    }

    return 'none';
}

/**
 * Verifica si el usuario actual puede acceder a un portal específico
 *
 * @since 1.16.0
 *
 * @param string $portal Tipo de portal: 'empleado', 'cliente', 'aplicante'
 * @param WP_User|null $user Usuario o null para usuario actual
 * @return bool
 */
function ga_user_can_access_portal($portal, $user = null) {
    $user_portal = ga_get_user_portal_type($user);

    // Admins pueden acceder a todo
    if ($user_portal === 'admin') {
        return true;
    }

    return $user_portal === $portal;
}
