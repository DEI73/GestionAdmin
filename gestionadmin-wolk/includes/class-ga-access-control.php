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
     * @param WP_User|null $user Usuario (null = usuario actual)
     * @return string URL de redirección
     */
    private function get_redirect_url_for_user($user = null) {
        if (null === $user) {
            $user = wp_get_current_user();
        }

        // Empleados → Portal del Empleado
        if (in_array('ga_empleado', (array) $user->roles, true)) {
            return home_url('/portal-empleado/');
        }

        // Clientes → Portal del Cliente
        if (in_array('ga_cliente', (array) $user->roles, true)) {
            return home_url('/portal-cliente/');
        }

        // Aplicantes → Portal de Trabajo
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
