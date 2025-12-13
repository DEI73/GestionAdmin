<?php
/**
 * Plugin Name: GestionAdmin by Wolk
 * Plugin URI: https://wolk.com.co/gestionadmin
 * Description: Sistema integral de gestión empresarial estilo "Uber del trabajo profesional". Gestiona empleados, freelancers, tareas, facturación multi-país y pagos.
 * Version: 1.0.0
 * Author: Wolk
 * Author URI: https://wolk.com.co
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: gestionadmin-wolk
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('GA_VERSION', '1.0.0');
define('GA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GA_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Código que se ejecuta durante la activación del plugin
 */
function ga_activate_plugin() {
    require_once GA_PLUGIN_DIR . 'includes/class-ga-activator.php';
    GA_Activator::activate();
}

/**
 * Código que se ejecuta durante la desactivación del plugin
 */
function ga_deactivate_plugin() {
    require_once GA_PLUGIN_DIR . 'includes/class-ga-deactivator.php';
    GA_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'ga_activate_plugin');
register_deactivation_hook(__FILE__, 'ga_deactivate_plugin');

/**
 * Clase principal del plugin
 */
require_once GA_PLUGIN_DIR . 'includes/class-ga-loader.php';

/**
 * Inicia la ejecución del plugin
 */
function ga_run_plugin() {
    $plugin = new GA_Loader();
    $plugin->run();
}

ga_run_plugin();
