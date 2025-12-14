<?php
/**
 * Debug de Rutas - GestionAdmin
 *
 * INSTRUCCIONES:
 * 1. Agregar al final de functions.php del tema:
 *    require_once WP_PLUGIN_DIR . '/gestionadmin-wolk/debug-routes.php';
 *
 * 2. O crear como mu-plugin en wp-content/mu-plugins/ga-debug.php
 *
 * 3. Visitar cualquier URL del sitio como administrador
 *
 * 4. ELIMINAR DESPUÉS DE DEBUGGEAR
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', 'ga_debug_routes_output');
add_action('admin_footer', 'ga_debug_routes_output');

function ga_debug_routes_output() {
    if (!current_user_can('administrator')) {
        return;
    }

    global $wp_rewrite, $wp_query, $wp;

    echo '<div style="position:fixed;bottom:0;left:0;right:0;background:#1a1a2e;color:#0f0;padding:20px;font-family:monospace;font-size:11px;max-height:50vh;overflow:auto;z-index:999999;border-top:3px solid #e94560;">';
    echo '<h3 style="color:#e94560;margin:0 0 15px;">DEBUG RUTAS GESTIONADMIN</h3>';

    // URL actual
    echo '<p><strong style="color:#fff;">URL Actual:</strong> ' . esc_html(home_url($wp->request)) . '</p>';

    // Query vars del plugin
    echo '<p><strong style="color:#fff;">ga_portal:</strong> <span style="color:#0ff;">' . esc_html(get_query_var('ga_portal', '(vacío)')) . '</span></p>';
    echo '<p><strong style="color:#fff;">ga_section:</strong> <span style="color:#0ff;">' . esc_html(get_query_var('ga_section', '(vacío)')) . '</span></p>';
    echo '<p><strong style="color:#fff;">ga_codigo:</strong> <span style="color:#0ff;">' . esc_html(get_query_var('ga_codigo', '(vacío)')) . '</span></p>';
    echo '<p><strong style="color:#fff;">ga_action:</strong> <span style="color:#0ff;">' . esc_html(get_query_var('ga_action', '(vacío)')) . '</span></p>';

    // Regla que matcheó
    echo '<p><strong style="color:#fff;">Matched Rule:</strong> <span style="color:#ff0;">' . esc_html($wp->matched_rule ?: '(ninguna)') . '</span></p>';
    echo '<p><strong style="color:#fff;">Matched Query:</strong> <span style="color:#ff0;">' . esc_html($wp->matched_query ?: '(ninguno)') . '</span></p>';

    // Es página?
    echo '<p><strong style="color:#fff;">is_page():</strong> ' . (is_page() ? '<span style="color:#0f0;">SI</span>' : '<span style="color:#f00;">NO</span>') . '</p>';
    echo '<p><strong style="color:#fff;">is_singular():</strong> ' . (is_singular() ? '<span style="color:#0f0;">SI</span>' : '<span style="color:#f00;">NO</span>') . '</p>';
    echo '<p><strong style="color:#fff;">is_404():</strong> ' . (is_404() ? '<span style="color:#f00;">SI - PROBLEMA!</span>' : '<span style="color:#0f0;">NO</span>') . '</p>';

    // Queried object
    $obj = get_queried_object();
    if ($obj) {
        echo '<p><strong style="color:#fff;">Queried Object:</strong> ';
        if (isset($obj->post_title)) {
            echo 'Página: ' . esc_html($obj->post_title) . ' (ID: ' . $obj->ID . ')';
        } elseif (isset($obj->name)) {
            echo 'Term: ' . esc_html($obj->name);
        } else {
            echo esc_html(get_class($obj));
        }
        echo '</p>';
    } else {
        echo '<p><strong style="color:#fff;">Queried Object:</strong> <span style="color:#f00;">(null)</span></p>';
    }

    // Verificar si GA_Public está cargado
    echo '<hr style="border-color:#333;margin:10px 0;">';
    echo '<p><strong style="color:#fff;">GA_Public cargado:</strong> ' . (class_exists('GA_Public') ? '<span style="color:#0f0;">SI</span>' : '<span style="color:#f00;">NO - PROBLEMA!</span>') . '</p>';

    // Listar rewrite rules que contienen "trabajo" o "portal"
    echo '<hr style="border-color:#333;margin:10px 0;">';
    echo '<p><strong style="color:#e94560;">Rewrite Rules relevantes:</strong></p>';
    echo '<ul style="margin:5px 0;padding-left:20px;max-height:150px;overflow:auto;">';

    $rules = get_option('rewrite_rules', array());
    $found = false;
    if (is_array($rules)) {
        foreach ($rules as $pattern => $query) {
            if (strpos($pattern, 'trabajo') !== false ||
                strpos($pattern, 'portal') !== false ||
                strpos($pattern, 'mi-cuenta') !== false ||
                strpos($pattern, 'empleado') !== false ||
                strpos($pattern, 'cliente') !== false ||
                strpos($query, 'ga_portal') !== false) {
                echo '<li><code style="color:#0ff;">' . esc_html($pattern) . '</code> → <code style="color:#ff0;">' . esc_html($query) . '</code></li>';
                $found = true;
            }
        }
    }

    if (!$found) {
        echo '<li style="color:#f00;">NO SE ENCONTRARON REGLAS DEL PLUGIN - Regenerar permalinks!</li>';
    }

    echo '</ul>';

    // Verificar reglas esperadas
    $expected_patterns = array('trabajo', 'portal-empleado', 'mi-cuenta', 'cliente', 'registro-aplicante');
    echo '<p style="margin-top:10px;"><strong style="color:#e94560;">Rutas esperadas:</strong></p>';
    echo '<ul style="margin:5px 0;padding-left:20px;">';
    foreach ($expected_patterns as $expected) {
        $found_expected = false;
        if (is_array($rules)) {
            foreach ($rules as $pattern => $query) {
                if (strpos($pattern, $expected) !== false) {
                    $found_expected = true;
                    break;
                }
            }
        }
        $status = $found_expected ? '<span style="color:#0f0;">OK</span>' : '<span style="color:#f00;">FALTA</span>';
        echo '<li>' . esc_html($expected) . ': ' . $status . '</li>';
    }
    echo '</ul>';

    // Versión del plugin
    echo '<p><strong style="color:#fff;">GA_VERSION:</strong> <span style="color:#0ff;">' . (defined('GA_VERSION') ? GA_VERSION : 'NO DEFINIDO') . '</span></p>';
    echo '<p><strong style="color:#fff;">ga_rewrite_rules_version:</strong> <span style="color:#0ff;">' . esc_html(get_option('ga_rewrite_rules_version', 'NO EXISTE')) . '</span></p>';

    // Verificar páginas del plugin
    if (class_exists('GA_Pages_Manager')) {
        echo '<hr style="border-color:#333;margin:10px 0;">';
        echo '<p><strong style="color:#e94560;">Páginas del Plugin (GA_Pages_Manager):</strong></p>';
        $pm = GA_Pages_Manager::get_instance();
        $detected = $pm->detect_current_page();
        echo '<p><strong style="color:#fff;">Página detectada:</strong> <span style="color:#0ff;">' . esc_html($detected ?: '(ninguna)') . '</span></p>';
    }

    echo '<p style="margin-top:15px;color:#888;font-size:10px;">Este panel solo es visible para administradores. Eliminar debug-routes.php después de usar.</p>';
    echo '</div>';
}
