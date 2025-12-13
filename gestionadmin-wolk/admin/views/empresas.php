<?php
/**
 * Vista Admin: Empresas Propias
 *
 * Gestión del catálogo de empresas propias de la organización.
 * Cada empresa puede ser la entidad pagadora en órdenes de trabajo.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar módulo de empresas
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-empresas.php';
$empresas = GA_Empresas::get_instance();

// Determinar acción actual
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$empresa_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_empresa_nonce'])) {
    if (!wp_verify_nonce($_POST['ga_empresa_nonce'], 'ga_empresa_action')) {
        wp_die(__('Error de seguridad', 'gestionadmin-wolk'));
    }

    $post_action = isset($_POST['empresa_action']) ? sanitize_text_field($_POST['empresa_action']) : '';

    $data = array(
        'nombre'              => sanitize_text_field($_POST['nombre'] ?? ''),
        'razon_social'        => sanitize_text_field($_POST['razon_social'] ?? ''),
        'identificacion_tipo' => sanitize_text_field($_POST['identificacion_tipo'] ?? ''),
        'identificacion_fiscal' => sanitize_text_field($_POST['identificacion_fiscal'] ?? ''),
        'pais_iso'            => sanitize_text_field($_POST['pais_iso'] ?? ''),
        'direccion'           => sanitize_textarea_field($_POST['direccion'] ?? ''),
        'ciudad'              => sanitize_text_field($_POST['ciudad'] ?? ''),
        'codigo_postal'       => sanitize_text_field($_POST['codigo_postal'] ?? ''),
        'telefono'            => sanitize_text_field($_POST['telefono'] ?? ''),
        'email'               => sanitize_email($_POST['email'] ?? ''),
        'sitio_web'           => esc_url_raw($_POST['sitio_web'] ?? ''),
        'logo_url'            => esc_url_raw($_POST['logo_url'] ?? ''),
        'color_primario'      => sanitize_hex_color($_POST['color_primario'] ?? '#0073aa'),
        'prefijo_factura'     => sanitize_text_field($_POST['prefijo_factura'] ?? 'FAC'),
        'pie_factura'         => sanitize_textarea_field($_POST['pie_factura'] ?? ''),
        'es_principal'        => isset($_POST['es_principal']) ? 1 : 0,
        'activo'              => isset($_POST['activo']) ? 1 : 0,
    );

    switch ($post_action) {
        case 'crear':
            $result = $empresas->crear($data);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-empresas&message=created'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'actualizar':
            $result = $empresas->actualizar($empresa_id, $data);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-empresas&message=updated'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;
    }
}

// Procesar eliminación via GET
if ($action === 'delete' && $empresa_id > 0) {
    if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'ga_delete_empresa_' . $empresa_id)) {
        wp_die(__('Error de seguridad', 'gestionadmin-wolk'));
    }

    $result = $empresas->eliminar($empresa_id);
    if (!is_wp_error($result)) {
        wp_redirect(admin_url('admin.php?page=gestionadmin-empresas&message=deleted'));
        exit;
    }
    $error_message = $result->get_error_message();
    $action = 'list';
}

// Mensajes de éxito
$messages = array(
    'created' => __('Empresa creada exitosamente.', 'gestionadmin-wolk'),
    'updated' => __('Empresa actualizada.', 'gestionadmin-wolk'),
    'deleted' => __('Empresa eliminada.', 'gestionadmin-wolk'),
);

$success_message = '';
if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    $success_message = $messages[$_GET['message']];
}

// Obtener países para select
global $wpdb;
$paises = $wpdb->get_results("SELECT codigo_iso, nombre FROM {$wpdb->prefix}ga_paises_config WHERE activo = 1 ORDER BY nombre");
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-building"></span>
        <?php esc_html_e('Mis Empresas', 'gestionadmin-wolk'); ?>
    </h1>

    <?php if ($action === 'list'): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-empresas&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Nueva Empresa', 'gestionadmin-wolk'); ?>
        </a>
    <?php else: ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-empresas')); ?>" class="page-title-action">
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
        case 'edit':
            $empresa = null;
            if ($action === 'edit' && $empresa_id > 0) {
                $empresa = $empresas->get($empresa_id);
                if (!$empresa) {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Empresa no encontrada.', 'gestionadmin-wolk') . '</p></div>';
                    break;
                }
            }
            ?>
            <div class="ga-form-wrap">
                <form method="post" id="ga-empresa-form">
                    <?php wp_nonce_field('ga_empresa_action', 'ga_empresa_nonce'); ?>
                    <input type="hidden" name="empresa_action" value="<?php echo $empresa ? 'actualizar' : 'crear'; ?>">

                    <div class="ga-form-columns">
                        <div class="ga-form-main">
                            <!-- Datos Básicos -->
                            <div class="postbox">
                                <h2 class="hndle"><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e('Datos Básicos', 'gestionadmin-wolk'); ?></h2>
                                <div class="inside">
                                    <div class="ga-form-row">
                                        <div class="ga-form-group ga-col-6">
                                            <label for="nombre"><?php esc_html_e('Nombre Comercial:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                                            <input type="text" name="nombre" id="nombre" value="<?php echo esc_attr($empresa->nombre ?? ''); ?>" required>
                                        </div>
                                        <div class="ga-form-group ga-col-6">
                                            <label for="razon_social"><?php esc_html_e('Razón Social:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                                            <input type="text" name="razon_social" id="razon_social" value="<?php echo esc_attr($empresa->razon_social ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="ga-form-row">
                                        <div class="ga-form-group ga-col-4">
                                            <label for="pais_iso"><?php esc_html_e('País:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                                            <select name="pais_iso" id="pais_iso" required>
                                                <option value=""><?php esc_html_e('— Seleccione —', 'gestionadmin-wolk'); ?></option>
                                                <?php foreach ($paises as $pais): ?>
                                                    <option value="<?php echo esc_attr($pais->codigo_iso); ?>" <?php selected($empresa->pais_iso ?? '', $pais->codigo_iso); ?>>
                                                        <?php echo esc_html($pais->nombre); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="ga-form-group ga-col-4">
                                            <label for="identificacion_tipo"><?php esc_html_e('Tipo Identificación:', 'gestionadmin-wolk'); ?></label>
                                            <select name="identificacion_tipo" id="identificacion_tipo">
                                                <option value=""><?php esc_html_e('— Seleccione —', 'gestionadmin-wolk'); ?></option>
                                                <option value="NIT" <?php selected($empresa->identificacion_tipo ?? '', 'NIT'); ?>>NIT</option>
                                                <option value="CEDULA_JURIDICA" <?php selected($empresa->identificacion_tipo ?? '', 'CEDULA_JURIDICA'); ?>><?php esc_html_e('Cédula Jurídica', 'gestionadmin-wolk'); ?></option>
                                                <option value="RUT" <?php selected($empresa->identificacion_tipo ?? '', 'RUT'); ?>>RUT</option>
                                                <option value="RFC" <?php selected($empresa->identificacion_tipo ?? '', 'RFC'); ?>>RFC</option>
                                                <option value="EIN" <?php selected($empresa->identificacion_tipo ?? '', 'EIN'); ?>>EIN</option>
                                            </select>
                                        </div>
                                        <div class="ga-form-group ga-col-4">
                                            <label for="identificacion_fiscal"><?php esc_html_e('Número Identificación:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                                            <input type="text" name="identificacion_fiscal" id="identificacion_fiscal" value="<?php echo esc_attr($empresa->identificacion_fiscal ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contacto y Ubicación -->
                            <div class="postbox">
                                <h2 class="hndle"><span class="dashicons dashicons-location"></span> <?php esc_html_e('Contacto y Ubicación', 'gestionadmin-wolk'); ?></h2>
                                <div class="inside">
                                    <div class="ga-form-row">
                                        <div class="ga-form-group ga-col-6">
                                            <label for="email"><?php esc_html_e('Email Corporativo:', 'gestionadmin-wolk'); ?></label>
                                            <input type="email" name="email" id="email" value="<?php echo esc_attr($empresa->email ?? ''); ?>">
                                        </div>
                                        <div class="ga-form-group ga-col-6">
                                            <label for="telefono"><?php esc_html_e('Teléfono:', 'gestionadmin-wolk'); ?></label>
                                            <input type="text" name="telefono" id="telefono" value="<?php echo esc_attr($empresa->telefono ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="ga-form-row">
                                        <div class="ga-form-group ga-col-6">
                                            <label for="ciudad"><?php esc_html_e('Ciudad:', 'gestionadmin-wolk'); ?></label>
                                            <input type="text" name="ciudad" id="ciudad" value="<?php echo esc_attr($empresa->ciudad ?? ''); ?>">
                                        </div>
                                        <div class="ga-form-group ga-col-6">
                                            <label for="codigo_postal"><?php esc_html_e('Código Postal:', 'gestionadmin-wolk'); ?></label>
                                            <input type="text" name="codigo_postal" id="codigo_postal" value="<?php echo esc_attr($empresa->codigo_postal ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="ga-form-group">
                                        <label for="direccion"><?php esc_html_e('Dirección:', 'gestionadmin-wolk'); ?></label>
                                        <textarea name="direccion" id="direccion" rows="2"><?php echo esc_textarea($empresa->direccion ?? ''); ?></textarea>
                                    </div>

                                    <div class="ga-form-group">
                                        <label for="sitio_web"><?php esc_html_e('Sitio Web:', 'gestionadmin-wolk'); ?></label>
                                        <input type="url" name="sitio_web" id="sitio_web" value="<?php echo esc_url($empresa->sitio_web ?? ''); ?>" placeholder="https://">
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración de Facturación -->
                            <div class="postbox">
                                <h2 class="hndle"><span class="dashicons dashicons-media-document"></span> <?php esc_html_e('Configuración de Facturación', 'gestionadmin-wolk'); ?></h2>
                                <div class="inside">
                                    <div class="ga-form-row">
                                        <div class="ga-form-group ga-col-4">
                                            <label for="prefijo_factura"><?php esc_html_e('Prefijo Facturas:', 'gestionadmin-wolk'); ?></label>
                                            <input type="text" name="prefijo_factura" id="prefijo_factura" value="<?php echo esc_attr($empresa->prefijo_factura ?? 'FAC'); ?>" maxlength="10">
                                        </div>
                                        <div class="ga-form-group ga-col-4">
                                            <label for="logo_url"><?php esc_html_e('URL Logo:', 'gestionadmin-wolk'); ?></label>
                                            <div class="ga-url-upload-wrap" style="display: flex; gap: 8px; align-items: flex-start;">
                                                <input type="url" name="logo_url" id="logo_url" value="<?php echo esc_url($empresa->logo_url ?? ''); ?>" style="flex: 1;" placeholder="https://...">
                                                <button type="button" class="button" id="btn-subir-logo" title="<?php esc_attr_e('Subir imagen', 'gestionadmin-wolk'); ?>">
                                                    <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                                                </button>
                                            </div>
                                            <div id="logo-preview" style="margin-top: 8px;">
                                                <?php if (!empty($empresa->logo_url)): ?>
                                                    <img src="<?php echo esc_url($empresa->logo_url); ?>" style="max-width: 150px; max-height: 80px; border: 1px solid #ddd; padding: 4px; border-radius: 4px; background: #fff;">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="ga-form-group ga-col-4">
                                            <label for="color_primario"><?php esc_html_e('Color Primario:', 'gestionadmin-wolk'); ?></label>
                                            <input type="color" name="color_primario" id="color_primario" value="<?php echo esc_attr($empresa->color_primario ?? '#0073aa'); ?>">
                                        </div>
                                    </div>

                                    <div class="ga-form-group">
                                        <label for="pie_factura"><?php esc_html_e('Pie de Página en Facturas:', 'gestionadmin-wolk'); ?></label>
                                        <textarea name="pie_factura" id="pie_factura" rows="3" placeholder="<?php esc_attr_e('Texto que aparecerá al final de las facturas...', 'gestionadmin-wolk'); ?>"><?php echo esc_textarea($empresa->pie_factura ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="ga-form-sidebar">
                            <div class="postbox">
                                <h2 class="hndle"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e('Configuración', 'gestionadmin-wolk'); ?></h2>
                                <div class="inside">
                                    <div class="ga-form-group">
                                        <label>
                                            <input type="checkbox" name="es_principal" value="1" <?php checked($empresa->es_principal ?? 0, 1); ?>>
                                            <?php esc_html_e('Empresa Principal', 'gestionadmin-wolk'); ?>
                                        </label>
                                        <p class="description"><?php esc_html_e('Será la empresa por defecto en nuevas órdenes.', 'gestionadmin-wolk'); ?></p>
                                    </div>

                                    <div class="ga-form-group">
                                        <label>
                                            <input type="checkbox" name="activo" value="1" <?php checked($empresa->activo ?? 1, 1); ?>>
                                            <?php esc_html_e('Empresa Activa', 'gestionadmin-wolk'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php if ($empresa): ?>
                                <div class="postbox">
                                    <h2 class="hndle"><span class="dashicons dashicons-info-outline"></span> <?php esc_html_e('Información', 'gestionadmin-wolk'); ?></h2>
                                    <div class="inside ga-info-list">
                                        <p><strong><?php esc_html_e('Código:', 'gestionadmin-wolk'); ?></strong><br><?php echo esc_html($empresa->codigo); ?></p>
                                        <p><strong><?php esc_html_e('Creada:', 'gestionadmin-wolk'); ?></strong><br>
                                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($empresa->created_at))); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="ga-form-actions">
                                <button type="submit" class="button button-primary button-large">
                                    <span class="dashicons dashicons-saved"></span>
                                    <?php echo $empresa ? esc_html__('Guardar Cambios', 'gestionadmin-wolk') : esc_html__('Crear Empresa', 'gestionadmin-wolk'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php
            break;

        default:
            // Listado
            $filtro_pais = isset($_GET['pais']) ? sanitize_text_field($_GET['pais']) : '';
            $filtro_busqueda = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
            $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

            $resultado = $empresas->listar(array(
                'pais_iso' => $filtro_pais,
                'busqueda' => $filtro_busqueda,
                'page'     => $paged,
                'per_page' => 20,
            ));
            $items = $resultado['items'];
            $total_items = $resultado['total'];
            ?>

            <!-- Filtros -->
            <div class="tablenav top">
                <form method="get" class="ga-filter-form">
                    <input type="hidden" name="page" value="gestionadmin-empresas">

                    <select name="pais">
                        <option value=""><?php esc_html_e('— Todos los países —', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($paises as $pais): ?>
                            <option value="<?php echo esc_attr($pais->codigo_iso); ?>" <?php selected($filtro_pais, $pais->codigo_iso); ?>>
                                <?php echo esc_html($pais->nombre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="search" name="s" value="<?php echo esc_attr($filtro_busqueda); ?>" placeholder="<?php esc_attr_e('Buscar...', 'gestionadmin-wolk'); ?>">

                    <button type="submit" class="button"><?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?></button>
                </form>

                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf(_n('%s empresa', '%s empresas', $total_items, 'gestionadmin-wolk'), number_format_i18n($total_items)); ?>
                    </span>
                </div>
            </div>

            <!-- Tabla -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:80px;"><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('País', 'gestionadmin-wolk'); ?></th>
                        <th><?php esc_html_e('Identificación', 'gestionadmin-wolk'); ?></th>
                        <th style="width:80px;"><?php esc_html_e('Principal', 'gestionadmin-wolk'); ?></th>
                        <th style="width:80px;"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                        <th style="width:120px;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="7"><?php esc_html_e('No se encontraron empresas.', 'gestionadmin-wolk'); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><code><?php echo esc_html($item->codigo); ?></code></td>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-empresas&action=edit&id=' . $item->id)); ?>">
                                            <?php echo esc_html($item->nombre); ?>
                                        </a>
                                    </strong>
                                    <br><small><?php echo esc_html($item->razon_social); ?></small>
                                </td>
                                <td><?php echo esc_html($item->pais_nombre ?? $item->pais_iso); ?></td>
                                <td><?php echo esc_html($item->identificacion_fiscal); ?></td>
                                <td>
                                    <?php if ($item->es_principal): ?>
                                        <span class="ga-badge ga-badge-primary">
                                            <span class="dashicons dashicons-star-filled"></span>
                                        </span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="ga-estado-badge <?php echo $item->activo ? 'ga-estado-activo' : 'ga-estado-inactivo'; ?>">
                                        <?php echo $item->activo ? esc_html__('Activa', 'gestionadmin-wolk') : esc_html__('Inactiva', 'gestionadmin-wolk'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-empresas&action=edit&id=' . $item->id)); ?>" class="button button-small" title="<?php esc_attr_e('Editar', 'gestionadmin-wolk'); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <?php if (!$item->es_principal): ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=gestionadmin-empresas&action=delete&id=' . $item->id), 'ga_delete_empresa_' . $item->id)); ?>" class="button button-small" title="<?php esc_attr_e('Eliminar', 'gestionadmin-wolk'); ?>" onclick="return confirm('<?php esc_attr_e('¿Eliminar esta empresa?', 'gestionadmin-wolk'); ?>');">
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php
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
.ga-form-wrap { margin-top: 20px; }
.ga-form-columns {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
}
@media (max-width: 1100px) {
    .ga-form-columns { grid-template-columns: 1fr; }
}
.ga-form-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.ga-form-group {
    margin-bottom: 15px;
}
.ga-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}
.ga-form-group input[type="text"],
.ga-form-group input[type="email"],
.ga-form-group input[type="url"],
.ga-form-group select,
.ga-form-group textarea {
    width: 100%;
}
.ga-col-4 { flex: 0 0 calc(33.33% - 14px); }
.ga-col-6 { flex: 0 0 calc(50% - 10px); }

.ga-info-list p {
    margin: 0 0 12px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}
.ga-info-list p:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.ga-form-actions {
    text-align: center;
    padding-top: 15px;
}
.ga-form-actions .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

.ga-filter-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.ga-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
}
.ga-badge-primary {
    background: #0073aa;
    color: #fff;
}
.ga-badge-primary .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}
.ga-estado-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
}
.ga-estado-activo { background: #d4edda; color: #155724; }
.ga-estado-inactivo { background: #f8d7da; color: #721c24; }

.required { color: #dc3545; }
</style>

<script>
jQuery(document).ready(function($) {
    // =========================================================================
    // MEDIA UPLOADER PARA LOGO
    // =========================================================================
    var logoUploader;

    $('#btn-subir-logo').on('click', function(e) {
        e.preventDefault();

        // Si ya existe el uploader, abrirlo
        if (logoUploader) {
            logoUploader.open();
            return;
        }

        // Crear nuevo media uploader
        logoUploader = wp.media({
            title: '<?php echo esc_js(__('Seleccionar Logo de Empresa', 'gestionadmin-wolk')); ?>',
            button: {
                text: '<?php echo esc_js(__('Usar este logo', 'gestionadmin-wolk')); ?>'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });

        // Cuando se seleccione un archivo
        logoUploader.on('select', function() {
            var attachment = logoUploader.state().get('selection').first().toJSON();
            $('#logo_url').val(attachment.url);
            updateLogoPreview(attachment.url);
        });

        logoUploader.open();
    });

    // Preview al cambiar URL manualmente
    $('#logo_url').on('change blur', function() {
        updateLogoPreview($(this).val());
    });

    /**
     * Actualizar preview del logo
     */
    function updateLogoPreview(url) {
        var $preview = $('#logo-preview');
        if (url && url.match(/\.(jpg|jpeg|png|gif|svg|webp)$/i)) {
            $preview.html('<img src="' + url + '" style="max-width: 150px; max-height: 80px; border: 1px solid #ddd; padding: 4px; border-radius: 4px; background: #fff;" onerror="this.style.display=\'none\'">');
        } else if (url) {
            // URL sin extensión reconocida, intentar mostrar igual
            $preview.html('<img src="' + url + '" style="max-width: 150px; max-height: 80px; border: 1px solid #ddd; padding: 4px; border-radius: 4px; background: #fff;" onerror="this.style.display=\'none\'">');
        } else {
            $preview.html('');
        }
    }
});
</script>
