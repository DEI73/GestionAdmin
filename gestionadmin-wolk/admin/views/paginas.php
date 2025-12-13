<?php
/**
 * Vista: Gestión de Páginas del Plugin
 *
 * Panel de administración para crear, verificar y gestionar
 * todas las páginas necesarias para los portales públicos.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener instancia del gestor de páginas
require_once GA_PLUGIN_DIR . 'includes/class-ga-pages-manager.php';
$pages_manager = GA_Pages_Manager::get_instance();

// Obtener estados de todas las páginas
$all_status = $pages_manager->get_all_status();
$summary = $pages_manager->get_status_summary();
$pages_by_portal = $pages_manager->get_pages_by_portal();

// Labels de portales
$portal_labels = array(
    'trabajo'   => __('Marketplace', 'gestionadmin-wolk'),
    'aplicante' => __('Portal Aplicantes', 'gestionadmin-wolk'),
    'empleado'  => __('Portal Empleados', 'gestionadmin-wolk'),
    'cliente'   => __('Portal Clientes', 'gestionadmin-wolk'),
    'general'   => __('General', 'gestionadmin-wolk'),
);

// Iconos de portales
$portal_icons = array(
    'trabajo'   => 'dashicons-store',
    'aplicante' => 'dashicons-businessperson',
    'empleado'  => 'dashicons-businessman',
    'cliente'   => 'dashicons-groups',
    'general'   => 'dashicons-admin-generic',
);
?>

<div class="wrap ga-admin ga-pages-manager">
    <h1>
        <span class="dashicons dashicons-admin-page"></span>
        <?php esc_html_e('Gestión de Páginas', 'gestionadmin-wolk'); ?>
    </h1>

    <p class="description">
        <?php esc_html_e('Administra las páginas necesarias para el funcionamiento de los portales públicos del plugin.', 'gestionadmin-wolk'); ?>
    </p>

    <!-- =====================================================================
         RESUMEN DE ESTADOS
         ===================================================================== -->
    <div class="ga-pages-summary">
        <div class="ga-summary-cards">
            <div class="ga-summary-card ga-summary-total">
                <span class="ga-summary-number"><?php echo esc_html($summary['total']); ?></span>
                <span class="ga-summary-label"><?php esc_html_e('Total Páginas', 'gestionadmin-wolk'); ?></span>
            </div>
            <div class="ga-summary-card ga-summary-ok">
                <span class="ga-summary-number"><?php echo esc_html($summary['ok']); ?></span>
                <span class="ga-summary-label"><?php esc_html_e('Activas', 'gestionadmin-wolk'); ?></span>
            </div>
            <div class="ga-summary-card ga-summary-missing">
                <span class="ga-summary-number"><?php echo esc_html($summary['not_created'] + $summary['deleted']); ?></span>
                <span class="ga-summary-label"><?php esc_html_e('Faltantes', 'gestionadmin-wolk'); ?></span>
            </div>
            <div class="ga-summary-card ga-summary-warning">
                <span class="ga-summary-number"><?php echo esc_html($summary['no_template'] + $summary['draft']); ?></span>
                <span class="ga-summary-label"><?php esc_html_e('Con Problemas', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>

        <?php if ($summary['not_created'] > 0 || $summary['deleted'] > 0) : ?>
        <div class="ga-pages-actions-top">
            <button type="button" id="ga-btn-create-all-pages" class="button button-primary button-hero">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php esc_html_e('Crear Todas las Páginas Faltantes', 'gestionadmin-wolk'); ?>
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- =====================================================================
         MENSAJE DE AYUDA
         ===================================================================== -->
    <div class="ga-notice ga-notice-info">
        <p>
            <span class="dashicons dashicons-info"></span>
            <strong><?php esc_html_e('Información:', 'gestionadmin-wolk'); ?></strong>
            <?php esc_html_e('Las páginas se crean automáticamente al activar el plugin. Si alguna página fue eliminada accidentalmente, puedes recrearla desde aquí.', 'gestionadmin-wolk'); ?>
        </p>
    </div>

    <!-- =====================================================================
         TABLAS DE PÁGINAS POR PORTAL
         ===================================================================== -->
    <?php foreach ($pages_by_portal as $portal => $pages) : ?>
    <div class="ga-card ga-pages-portal-card">
        <h2 class="ga-card-title">
            <span class="dashicons <?php echo esc_attr($portal_icons[$portal] ?? 'dashicons-admin-page'); ?>"></span>
            <?php echo esc_html($portal_labels[$portal] ?? ucfirst($portal)); ?>
            <span class="ga-portal-count">(<?php echo count($pages); ?> <?php esc_html_e('páginas', 'gestionadmin-wolk'); ?>)</span>
        </h2>

        <table class="wp-list-table widefat fixed striped ga-pages-table">
            <thead>
                <tr>
                    <th class="column-title" style="width: 25%;"><?php esc_html_e('Página', 'gestionadmin-wolk'); ?></th>
                    <th class="column-slug" style="width: 20%;"><?php esc_html_e('URL / Slug', 'gestionadmin-wolk'); ?></th>
                    <th class="column-template" style="width: 25%;"><?php esc_html_e('Template', 'gestionadmin-wolk'); ?></th>
                    <th class="column-status" style="width: 15%;"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                    <th class="column-actions" style="width: 15%;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $key => $config) :
                    $status = $all_status[$key];
                    $full_slug = $pages_manager->get_full_slug($key);
                    $status_class = 'ga-status-' . $status['status'];
                ?>
                <tr data-page-key="<?php echo esc_attr($key); ?>" class="<?php echo esc_attr($status_class); ?>">
                    <!-- Página -->
                    <td class="column-title">
                        <span class="dashicons <?php echo esc_attr($config['icon']); ?>"></span>
                        <strong><?php echo esc_html($config['title']); ?></strong>
                        <?php if ($config['parent']) : ?>
                            <br><small class="ga-page-parent">
                                └ <?php esc_html_e('Subpágina de:', 'gestionadmin-wolk'); ?>
                                <?php echo esc_html($pages_manager->get_page_config($config['parent'])['title']); ?>
                            </small>
                        <?php endif; ?>
                        <br><small class="description"><?php echo esc_html($config['description']); ?></small>
                    </td>

                    <!-- URL/Slug -->
                    <td class="column-slug">
                        <code>/<?php echo esc_html($full_slug); ?>/</code>
                        <?php if ($status['exists'] && !empty($status['url'])) : ?>
                            <br><a href="<?php echo esc_url($status['url']); ?>" target="_blank" class="ga-link-preview">
                                <span class="dashicons dashicons-external"></span>
                                <?php esc_html_e('Ver', 'gestionadmin-wolk'); ?>
                            </a>
                        <?php endif; ?>
                    </td>

                    <!-- Template -->
                    <td class="column-template">
                        <code><?php echo esc_html($config['template']); ?></code>
                        <?php if ($status['template_exists']) : ?>
                            <span class="ga-template-ok" title="<?php esc_attr_e('Template existe', 'gestionadmin-wolk'); ?>">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </span>
                        <?php else : ?>
                            <span class="ga-template-missing" title="<?php esc_attr_e('Template no encontrado', 'gestionadmin-wolk'); ?>">
                                <span class="dashicons dashicons-warning"></span>
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- Estado -->
                    <td class="column-status">
                        <?php
                        $status_icons = array(
                            'ok'          => 'dashicons-yes-alt',
                            'not_created' => 'dashicons-minus',
                            'deleted'     => 'dashicons-trash',
                            'no_template' => 'dashicons-warning',
                            'draft'       => 'dashicons-hidden',
                            'invalid'     => 'dashicons-dismiss',
                        );
                        $status_colors = array(
                            'ok'          => '#00a32a',
                            'not_created' => '#d63638',
                            'deleted'     => '#d63638',
                            'no_template' => '#dba617',
                            'draft'       => '#dba617',
                            'invalid'     => '#d63638',
                        );
                        ?>
                        <span class="ga-status-badge" style="color: <?php echo esc_attr($status_colors[$status['status']] ?? '#666'); ?>">
                            <span class="dashicons <?php echo esc_attr($status_icons[$status['status']] ?? 'dashicons-marker'); ?>"></span>
                            <?php echo esc_html($status['message']); ?>
                        </span>
                        <?php if ($status['exists'] && isset($status['page_id'])) : ?>
                            <br><small>ID: <?php echo esc_html($status['page_id']); ?></small>
                        <?php endif; ?>
                    </td>

                    <!-- Acciones -->
                    <td class="column-actions">
                        <?php if ($status['exists']) : ?>
                            <?php if (!empty($status['edit_url'])) : ?>
                                <a href="<?php echo esc_url($status['edit_url']); ?>" class="button button-small" title="<?php esc_attr_e('Editar en WordPress', 'gestionadmin-wolk'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                            <?php endif; ?>
                            <button type="button" class="button button-small ga-btn-recreate-page"
                                    data-key="<?php echo esc_attr($key); ?>"
                                    data-title="<?php echo esc_attr($config['title']); ?>"
                                    title="<?php esc_attr_e('Recrear página', 'gestionadmin-wolk'); ?>">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        <?php else : ?>
                            <button type="button" class="button button-primary button-small ga-btn-create-page"
                                    data-key="<?php echo esc_attr($key); ?>"
                                    data-title="<?php echo esc_attr($config['title']); ?>">
                                <span class="dashicons dashicons-plus"></span>
                                <?php esc_html_e('Crear', 'gestionadmin-wolk'); ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- =====================================================================
         INFORMACIÓN TÉCNICA
         ===================================================================== -->
    <div class="ga-card ga-pages-technical">
        <h3>
            <span class="dashicons dashicons-info-outline"></span>
            <?php esc_html_e('Información Técnica', 'gestionadmin-wolk'); ?>
        </h3>
        <div class="ga-technical-info">
            <div class="ga-tech-item">
                <strong><?php esc_html_e('Directorio de Templates:', 'gestionadmin-wolk'); ?></strong>
                <code><?php echo esc_html(GA_PLUGIN_DIR . 'templates/'); ?></code>
            </div>
            <div class="ga-tech-item">
                <strong><?php esc_html_e('Prefijo de Opciones:', 'gestionadmin-wolk'); ?></strong>
                <code>ga_page_[key]</code>
            </div>
            <div class="ga-tech-item">
                <strong><?php esc_html_e('Meta Key de Página:', 'gestionadmin-wolk'); ?></strong>
                <code>_ga_page_key</code>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         FOOTER
         ===================================================================== -->
    <div class="ga-pages-footer">
        <p class="description">
            <?php esc_html_e('Diseñado y desarrollado por', 'gestionadmin-wolk'); ?>
            <a href="https://wolksoftcr.com" target="_blank">Wolksoftcr.com</a>
        </p>
    </div>
</div>

<!-- =========================================================================
     ESTILOS ESPECÍFICOS
     ========================================================================= -->
<style>
/* Resumen */
.ga-pages-summary {
    margin: 20px 0;
}
.ga-summary-cards {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}
.ga-summary-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    min-width: 120px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
.ga-summary-number {
    display: block;
    font-size: 36px;
    font-weight: 600;
    line-height: 1;
}
.ga-summary-label {
    display: block;
    margin-top: 8px;
    color: #50575e;
    font-size: 13px;
}
.ga-summary-total .ga-summary-number { color: #2271b1; }
.ga-summary-ok .ga-summary-number { color: #00a32a; }
.ga-summary-missing .ga-summary-number { color: #d63638; }
.ga-summary-warning .ga-summary-number { color: #dba617; }

.ga-pages-actions-top {
    margin-top: 15px;
}
.ga-pages-actions-top .button-hero {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.ga-pages-actions-top .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}

/* Notice */
.ga-notice {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-left-width: 4px;
    border-left-color: #72aee6;
    padding: 12px;
    margin: 20px 0;
}
.ga-notice-info { border-left-color: #72aee6; }
.ga-notice p {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ga-notice .dashicons {
    color: #72aee6;
}

/* Cards de Portal */
.ga-pages-portal-card {
    margin-bottom: 25px;
}
.ga-card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #dcdcde;
}
.ga-card-title .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #2271b1;
}
.ga-portal-count {
    font-size: 13px;
    font-weight: normal;
    color: #50575e;
}

/* Tabla de páginas */
.ga-pages-table .column-title .dashicons {
    color: #2271b1;
    vertical-align: middle;
    margin-right: 5px;
}
.ga-page-parent {
    color: #50575e;
    margin-left: 20px;
}
.ga-link-preview {
    text-decoration: none;
    font-size: 12px;
}
.ga-link-preview .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    vertical-align: middle;
}

/* Template status */
.ga-template-ok { color: #00a32a; }
.ga-template-missing { color: #dba617; }
.ga-template-ok .dashicons,
.ga-template-missing .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    vertical-align: middle;
}

/* Status badge */
.ga-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 500;
}
.ga-status-badge .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Row status colors */
.ga-pages-table tr.ga-status-not_created,
.ga-pages-table tr.ga-status-deleted {
    background-color: #fcf0f1;
}
.ga-pages-table tr.ga-status-no_template,
.ga-pages-table tr.ga-status-draft {
    background-color: #fcf9e8;
}

/* Actions */
.column-actions .button-small {
    padding: 0 8px;
    min-height: 28px;
}
.column-actions .button-small .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    vertical-align: middle;
    margin-top: -2px;
}

/* Technical info */
.ga-pages-technical {
    background: #f6f7f7;
}
.ga-technical-info {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.ga-tech-item {
    display: flex;
    gap: 8px;
    align-items: center;
}
.ga-tech-item code {
    background: #fff;
    padding: 4px 8px;
}

/* Footer */
.ga-pages-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #dcdcde;
}
</style>

<!-- =========================================================================
     JAVASCRIPT
     ========================================================================= -->
<script>
jQuery(document).ready(function($) {

    // Crear una página específica
    $('.ga-btn-create-page').on('click', function() {
        var $btn = $(this);
        var key = $btn.data('key');
        var title = $btn.data('title');

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update ga-spin"></span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ga_create_page',
                nonce: '<?php echo wp_create_nonce('ga_admin_nonce'); ?>',
                page_key: key
            },
            success: function(response) {
                if (response.success) {
                    // Recargar la página para ver cambios
                    location.reload();
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error al crear la página', 'gestionadmin-wolk')); ?>');
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php echo esc_js(__('Crear', 'gestionadmin-wolk')); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Error de conexión', 'gestionadmin-wolk')); ?>');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php echo esc_js(__('Crear', 'gestionadmin-wolk')); ?>');
            }
        });
    });

    // Recrear una página
    $('.ga-btn-recreate-page').on('click', function() {
        var $btn = $(this);
        var key = $btn.data('key');
        var title = $btn.data('title');

        if (!confirm('<?php echo esc_js(__('¿Recrear la página', 'gestionadmin-wolk')); ?> "' + title + '"?\n\n<?php echo esc_js(__('Esto eliminará la página actual y creará una nueva.', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update ga-spin"></span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ga_recreate_page',
                nonce: '<?php echo wp_create_nonce('ga_admin_nonce'); ?>',
                page_key: key
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error al recrear la página', 'gestionadmin-wolk')); ?>');
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Error de conexión', 'gestionadmin-wolk')); ?>');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span>');
            }
        });
    });

    // Crear todas las páginas faltantes
    $('#ga-btn-create-all-pages').on('click', function() {
        var $btn = $(this);

        if (!confirm('<?php echo esc_js(__('¿Crear todas las páginas faltantes?', 'gestionadmin-wolk')); ?>')) {
            return;
        }

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update ga-spin"></span> <?php echo esc_js(__('Creando...', 'gestionadmin-wolk')); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ga_create_all_pages',
                nonce: '<?php echo wp_create_nonce('ga_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || '<?php echo esc_js(__('Error al crear las páginas', 'gestionadmin-wolk')); ?>');
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus-alt"></span> <?php echo esc_js(__('Crear Todas las Páginas Faltantes', 'gestionadmin-wolk')); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Error de conexión', 'gestionadmin-wolk')); ?>');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus-alt"></span> <?php echo esc_js(__('Crear Todas las Páginas Faltantes', 'gestionadmin-wolk')); ?>');
            }
        });
    });

});

// CSS para animación de spin
var style = document.createElement('style');
style.textContent = '@keyframes ga-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .ga-spin { animation: ga-spin 1s linear infinite; }';
document.head.appendChild(style);
</script>
