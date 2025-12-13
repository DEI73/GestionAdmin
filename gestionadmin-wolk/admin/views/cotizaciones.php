<?php
/**
 * Vista Admin: Cotizaciones
 *
 * Panel de administración para gestión de cotizaciones/presupuestos.
 * Incluye listado, formulario crear/editar y conversión a factura.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.4.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar módulo de cotizaciones
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-cotizaciones.php';
$cotizaciones = GA_Cotizaciones::get_instance();

// Determinar acción actual
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$cotizacion_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_cotizacion_nonce'])) {
    if (!wp_verify_nonce($_POST['ga_cotizacion_nonce'], 'ga_cotizacion_action')) {
        wp_die(__('Error de seguridad', 'gestionadmin-wolk'));
    }

    $post_action = isset($_POST['cotizacion_action']) ? sanitize_text_field($_POST['cotizacion_action']) : '';

    switch ($post_action) {
        case 'crear':
            $result = $cotizaciones->crear($_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $result . '&message=created'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'actualizar':
            $result = $cotizaciones->actualizar($cotizacion_id, $_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $cotizacion_id . '&message=updated'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'agregar_linea':
            $result = $cotizaciones->agregar_linea($cotizacion_id, $_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $cotizacion_id . '&message=line_added'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'eliminar_linea':
            $linea_id = isset($_POST['linea_id']) ? absint($_POST['linea_id']) : 0;
            $result = $cotizaciones->eliminar_linea($linea_id);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $cotizacion_id . '&message=line_deleted'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'enviar':
            $result = $cotizaciones->enviar($cotizacion_id);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $cotizacion_id . '&message=sent'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'aprobar':
            $result = $cotizaciones->aprobar($cotizacion_id);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $cotizacion_id . '&message=approved'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'rechazar':
            $motivo = isset($_POST['motivo_rechazo']) ? sanitize_textarea_field($_POST['motivo_rechazo']) : '';
            $result = $cotizaciones->rechazar($cotizacion_id, $motivo);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $cotizacion_id . '&message=rejected'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'cancelar':
            $result = $cotizaciones->cancelar($cotizacion_id);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-cotizaciones&message=cancelled'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'convertir_factura':
            $pais = isset($_POST['pais_facturacion']) ? sanitize_text_field($_POST['pais_facturacion']) : '';
            $result = $cotizaciones->convertir_a_factura($cotizacion_id, $pais);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $result . '&message=created'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;
    }
}

// Mensajes de éxito
$messages = array(
    'created'      => __('Cotización creada exitosamente.', 'gestionadmin-wolk'),
    'updated'      => __('Cotización actualizada.', 'gestionadmin-wolk'),
    'line_added'   => __('Línea agregada.', 'gestionadmin-wolk'),
    'line_deleted' => __('Línea eliminada.', 'gestionadmin-wolk'),
    'sent'         => __('Cotización enviada al cliente.', 'gestionadmin-wolk'),
    'approved'     => __('Cotización marcada como aprobada.', 'gestionadmin-wolk'),
    'rejected'     => __('Cotización marcada como rechazada.', 'gestionadmin-wolk'),
    'cancelled'    => __('Cotización cancelada.', 'gestionadmin-wolk'),
    'deleted'      => __('Cotización eliminada.', 'gestionadmin-wolk'),
);

$success_message = '';
if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    $success_message = $messages[$_GET['message']];
}

// Obtener clientes y países para selects
global $wpdb;
$clientes = $wpdb->get_results("SELECT id, codigo, nombre_comercial FROM {$wpdb->prefix}ga_clientes WHERE activo = 1 ORDER BY nombre_comercial");
$paises = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ga_paises_config WHERE activo = 1 ORDER BY nombre");
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-clipboard"></span>
        <?php esc_html_e('Cotizaciones', 'gestionadmin-wolk'); ?>
    </h1>

    <?php if ($action === 'list'): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Nueva Cotización', 'gestionadmin-wolk'); ?>
        </a>
    <?php else: ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones')); ?>" class="page-title-action">
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
            include 'cotizaciones-form.php';
            break;

        case 'edit':
            $cotizacion = $cotizaciones->get($cotizacion_id, true);
            if (!$cotizacion) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Cotización no encontrada.', 'gestionadmin-wolk') . '</p></div>';
            } else {
                include 'cotizaciones-form.php';
            }
            break;

        case 'preview':
            $cotizacion = $cotizaciones->get($cotizacion_id, true);
            if ($cotizacion) {
                echo $cotizaciones->generar_html_preview($cotizacion_id);
            }
            break;

        default:
            // Listado
            include 'cotizaciones-list.php';
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
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
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
    font-size: 26px;
    font-weight: 600;
    color: #1d2327;
}
.ga-stat-card .ga-stat-label {
    color: #666;
    font-size: 12px;
}
.ga-stat-card.ga-stat-success { border-left: 4px solid #28a745; }
.ga-stat-card.ga-stat-warning { border-left: 4px solid #ffc107; }
.ga-stat-card.ga-stat-danger { border-left: 4px solid #dc3545; }
.ga-stat-card.ga-stat-info { border-left: 4px solid #17a2b8; }

.ga-cotizacion-estado {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.ga-estado-borrador { background: #f0f0f0; color: #666; }
.ga-estado-enviada { background: #cce5ff; color: #004085; }
.ga-estado-aprobada { background: #d4edda; color: #155724; }
.ga-estado-rechazada { background: #f8d7da; color: #721c24; }
.ga-estado-facturada { background: #d1ecf1; color: #0c5460; }
.ga-estado-vencida { background: #fff3cd; color: #856404; }
.ga-estado-cancelada { background: #d6d8db; color: #383d41; }

.ga-cotizacion-preview {
    max-width: 800px;
    margin: 20px auto;
    padding: 40px;
    background: #fff;
    border: 1px solid #ddd;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.ga-cotizacion-preview table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
.ga-cotizacion-preview th,
.ga-cotizacion-preview td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}
.ga-cotizacion-preview th {
    background: #f5f5f5;
}
@media print {
    .ga-admin-wrap > *:not(.ga-cotizacion-preview) { display: none !important; }
    .ga-cotizacion-preview { box-shadow: none; border: none; margin: 0; }
}
</style>
