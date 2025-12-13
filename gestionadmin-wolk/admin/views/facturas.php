<?php
/**
 * Vista Admin: Facturas
 *
 * Panel de administración para gestión de facturas.
 * Incluye listado con filtros, formulario de creación/edición,
 * facturación de horas y vista previa.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.4.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar módulo de facturas
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-facturas.php';
$facturas = GA_Facturas::get_instance();

// Determinar acción actual
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$factura_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_factura_nonce'])) {
    if (!wp_verify_nonce($_POST['ga_factura_nonce'], 'ga_factura_action')) {
        wp_die(__('Error de seguridad', 'gestionadmin-wolk'));
    }

    $post_action = isset($_POST['factura_action']) ? sanitize_text_field($_POST['factura_action']) : '';

    switch ($post_action) {
        case 'crear':
            $result = $facturas->crear($_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $result . '&message=created'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'actualizar':
            $result = $facturas->actualizar($factura_id, $_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $factura_id . '&message=updated'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'agregar_linea':
            $result = $facturas->agregar_linea($factura_id, $_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $factura_id . '&message=line_added'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'eliminar_linea':
            $linea_id = isset($_POST['linea_id']) ? absint($_POST['linea_id']) : 0;
            $result = $facturas->eliminar_linea($linea_id);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $factura_id . '&message=line_deleted'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'enviar':
            $result = $facturas->enviar($factura_id);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $factura_id . '&message=sent'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'anular':
            $motivo = isset($_POST['motivo_anulacion']) ? sanitize_textarea_field($_POST['motivo_anulacion']) : '';
            $result = $facturas->anular($factura_id, $motivo);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&message=cancelled'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'registrar_pago':
            $monto = isset($_POST['monto_pago']) ? floatval($_POST['monto_pago']) : 0;
            $result = $facturas->registrar_pago($factura_id, $monto);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $factura_id . '&message=payment_added'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'facturar_horas':
            $horas_ids = isset($_POST['horas_ids']) ? array_map('absint', $_POST['horas_ids']) : array();
            $tarifa = isset($_POST['tarifa_hora']) ? floatval($_POST['tarifa_hora']) : 0;
            $result = $facturas->facturar_horas($factura_id, $horas_ids, $tarifa);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $factura_id . '&message=hours_added&count=' . $result));
                exit;
            }
            $error_message = $result->get_error_message();
            break;
    }
}

// Mensajes de éxito
$messages = array(
    'created'       => __('Factura creada exitosamente.', 'gestionadmin-wolk'),
    'updated'       => __('Factura actualizada.', 'gestionadmin-wolk'),
    'line_added'    => __('Línea agregada.', 'gestionadmin-wolk'),
    'line_deleted'  => __('Línea eliminada.', 'gestionadmin-wolk'),
    'sent'          => __('Factura enviada.', 'gestionadmin-wolk'),
    'cancelled'     => __('Factura anulada.', 'gestionadmin-wolk'),
    'payment_added' => __('Pago registrado.', 'gestionadmin-wolk'),
    'hours_added'   => __('Horas facturadas.', 'gestionadmin-wolk'),
    'deleted'       => __('Factura eliminada.', 'gestionadmin-wolk'),
);

$success_message = '';
if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    $success_message = $messages[$_GET['message']];
    if ($_GET['message'] === 'hours_added' && isset($_GET['count'])) {
        $success_message .= ' (' . absint($_GET['count']) . ' ' . __('líneas', 'gestionadmin-wolk') . ')';
    }
}

// Obtener clientes para selects
global $wpdb;
$clientes = $wpdb->get_results("SELECT id, codigo, nombre_comercial FROM {$wpdb->prefix}ga_clientes WHERE activo = 1 ORDER BY nombre_comercial");
$paises = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ga_paises_config WHERE activo = 1 ORDER BY nombre");
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-media-text"></span>
        <?php esc_html_e('Facturas', 'gestionadmin-wolk'); ?>
    </h1>

    <?php if ($action === 'list'): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Nueva Factura', 'gestionadmin-wolk'); ?>
        </a>
    <?php else: ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas')); ?>" class="page-title-action">
            <?php esc_html_e('← Volver al Listado', 'gestionadmin-wolk'); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php if (!empty($success_message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>

    <?php
    // Mostrar vista según acción
    switch ($action):
        case 'new':
            include 'facturas-form.php';
            break;

        case 'edit':
            $factura = $facturas->get($factura_id, true);
            if (!$factura) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Factura no encontrada.', 'gestionadmin-wolk') . '</p></div>';
            } else {
                include 'facturas-form.php';
            }
            break;

        case 'preview':
            $factura = $facturas->get($factura_id, true);
            if ($factura) {
                echo $facturas->generar_html_preview($factura_id);
            }
            break;

        case 'facturar_horas':
            $factura = $facturas->get($factura_id);
            if ($factura && $factura->estado === 'BORRADOR') {
                include 'facturas-horas.php';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__('Solo facturas en borrador pueden agregar horas.', 'gestionadmin-wolk') . '</p></div>';
            }
            break;

        default:
            // Listado de facturas
            include 'facturas-list.php';
            break;
    endswitch;
    ?>
</div>

<style>
.ga-admin-wrap h1 .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    margin-right: 10px;
    vertical-align: middle;
}
.ga-stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.ga-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}
.ga-stat-card .ga-stat-number {
    font-size: 28px;
    font-weight: 600;
    color: #1d2327;
}
.ga-stat-card .ga-stat-label {
    color: #666;
    font-size: 13px;
}
.ga-stat-card.ga-stat-success { border-left: 4px solid #28a745; }
.ga-stat-card.ga-stat-warning { border-left: 4px solid #ffc107; }
.ga-stat-card.ga-stat-danger { border-left: 4px solid #dc3545; }
.ga-stat-card.ga-stat-info { border-left: 4px solid #17a2b8; }

.ga-factura-estado {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.ga-estado-borrador { background: #f0f0f0; color: #666; }
.ga-estado-enviada { background: #cce5ff; color: #004085; }
.ga-estado-parcial { background: #fff3cd; color: #856404; }
.ga-estado-pagada { background: #d4edda; color: #155724; }
.ga-estado-vencida { background: #f8d7da; color: #721c24; }
.ga-estado-anulada { background: #d6d8db; color: #383d41; }

.ga-factura-preview {
    max-width: 800px;
    margin: 20px auto;
    padding: 40px;
    background: #fff;
    border: 1px solid #ddd;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.ga-factura-preview table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
.ga-factura-preview th,
.ga-factura-preview td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}
.ga-factura-preview th {
    background: #f5f5f5;
}
.ga-factura-totales {
    text-align: right;
}
.ga-factura-totales table {
    width: auto;
    margin-left: auto;
}
.ga-factura-totales td {
    border: none;
    padding: 5px 15px;
}
.ga-factura-totales .ga-total td,
.ga-factura-totales .ga-total-pagar td {
    border-top: 2px solid #333;
    font-size: 16px;
}
@media print {
    .ga-admin-wrap > *:not(.ga-factura-preview) { display: none !important; }
    .ga-factura-preview { box-shadow: none; border: none; margin: 0; }
}
</style>
