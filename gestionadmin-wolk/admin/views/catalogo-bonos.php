<?php
/**
 * Vista Admin: Catálogo de Bonos
 *
 * Panel de administración para gestión del catálogo de bonos predefinidos.
 * Los bonos son incentivos que se pueden ofrecer en órdenes de trabajo.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar módulo de catálogo de bonos
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-catalogo-bonos.php';
$catalogo_bonos = GA_Catalogo_Bonos::get_instance();

// Determinar acción actual
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$bono_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_bono_nonce'])) {
    if (!wp_verify_nonce($_POST['ga_bono_nonce'], 'ga_bono_action')) {
        wp_die(__('Error de seguridad', 'gestionadmin-wolk'));
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para realizar esta acción.', 'gestionadmin-wolk'));
    }

    $post_action = isset($_POST['bono_action']) ? sanitize_text_field($_POST['bono_action']) : '';

    switch ($post_action) {
        case 'crear':
            $result = $catalogo_bonos->crear($_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-catalogo-bonos&action=edit&id=' . $result . '&message=created'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'actualizar':
            $result = $catalogo_bonos->actualizar($bono_id, $_POST);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-catalogo-bonos&action=edit&id=' . $bono_id . '&message=updated'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'eliminar':
            $result = $catalogo_bonos->eliminar($bono_id);
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=gestionadmin-catalogo-bonos&message=deleted'));
                exit;
            }
            $error_message = $result->get_error_message();
            break;

        case 'toggle_activo':
            $bono = $catalogo_bonos->get($bono_id);
            if ($bono) {
                $nuevo_estado = $bono->activo ? 0 : 1;
                $result = $catalogo_bonos->actualizar($bono_id, array('activo' => $nuevo_estado));
                if (!is_wp_error($result)) {
                    $msg = $nuevo_estado ? 'activated' : 'deactivated';
                    wp_redirect(admin_url('admin.php?page=gestionadmin-catalogo-bonos&message=' . $msg));
                    exit;
                }
                $error_message = $result->get_error_message();
            }
            break;
    }
}

// Mensajes de éxito
$messages = array(
    'created'     => __('Bono creado exitosamente.', 'gestionadmin-wolk'),
    'updated'     => __('Bono actualizado.', 'gestionadmin-wolk'),
    'deleted'     => __('Bono desactivado.', 'gestionadmin-wolk'),
    'activated'   => __('Bono activado.', 'gestionadmin-wolk'),
    'deactivated' => __('Bono desactivado.', 'gestionadmin-wolk'),
);

$success_message = '';
if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    $success_message = $messages[$_GET['message']];
}

// Obtener bono si estamos editando
$bono = null;
if ($action === 'edit' && $bono_id > 0) {
    $bono = $catalogo_bonos->get($bono_id);
    if (!$bono) {
        $action = 'list';
    }
}

// Obtener estadísticas para el listado
$stats = $catalogo_bonos->get_estadisticas();
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-awards"></span>
        <?php esc_html_e('Catálogo de Bonos', 'gestionadmin-wolk'); ?>
    </h1>

    <?php if ($action === 'list'): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-catalogo-bonos&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Nuevo Bono', 'gestionadmin-wolk'); ?>
        </a>
    <?php else: ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-catalogo-bonos')); ?>" class="page-title-action">
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

    <?php if ($action === 'list'): ?>
        <!-- Tarjetas de estadísticas -->
        <div class="ga-stats-cards">
            <div class="ga-stat-card">
                <div class="ga-stat-number"><?php echo esc_html($stats['total']); ?></div>
                <div class="ga-stat-label"><?php esc_html_e('Total Bonos', 'gestionadmin-wolk'); ?></div>
            </div>
            <div class="ga-stat-card ga-stat-success">
                <div class="ga-stat-number"><?php echo esc_html($stats['activos']); ?></div>
                <div class="ga-stat-label"><?php esc_html_e('Activos', 'gestionadmin-wolk'); ?></div>
            </div>
            <?php foreach ($stats['por_categoria'] as $cat_stat): ?>
                <div class="ga-stat-card ga-stat-info">
                    <div class="ga-stat-number"><?php echo esc_html($cat_stat->total); ?></div>
                    <div class="ga-stat-label"><?php echo esc_html(GA_Catalogo_Bonos::$categorias[$cat_stat->categoria] ?? $cat_stat->categoria); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filtros -->
        <div class="ga-filter-bar">
            <form method="get">
                <input type="hidden" name="page" value="gestionadmin-catalogo-bonos">

                <select name="categoria">
                    <option value=""><?php esc_html_e('— Todas las categorías —', 'gestionadmin-wolk'); ?></option>
                    <?php foreach (GA_Catalogo_Bonos::$categorias as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected(isset($_GET['categoria']) ? $_GET['categoria'] : '', $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="activo">
                    <option value=""><?php esc_html_e('— Todos los estados —', 'gestionadmin-wolk'); ?></option>
                    <option value="1" <?php selected(isset($_GET['activo']) ? $_GET['activo'] : '', '1'); ?>><?php esc_html_e('Activos', 'gestionadmin-wolk'); ?></option>
                    <option value="0" <?php selected(isset($_GET['activo']) ? $_GET['activo'] : '', '0'); ?>><?php esc_html_e('Inactivos', 'gestionadmin-wolk'); ?></option>
                </select>

                <input type="text" name="busqueda" placeholder="<?php esc_attr_e('Buscar...', 'gestionadmin-wolk'); ?>" value="<?php echo esc_attr(isset($_GET['busqueda']) ? $_GET['busqueda'] : ''); ?>">

                <button type="submit" class="button"><?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?></button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-catalogo-bonos')); ?>" class="button"><?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?></a>
            </form>
        </div>

        <?php
        // Obtener listado con filtros
        $lista = $catalogo_bonos->listar(array(
            'categoria' => isset($_GET['categoria']) ? sanitize_text_field($_GET['categoria']) : '',
            'activo'    => isset($_GET['activo']) && $_GET['activo'] !== '' ? absint($_GET['activo']) : '',
            'busqueda'  => isset($_GET['busqueda']) ? sanitize_text_field($_GET['busqueda']) : '',
            'page'      => isset($_GET['paged']) ? absint($_GET['paged']) : 1,
        ));
        ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:50px;"><?php esc_html_e('Orden', 'gestionadmin-wolk'); ?></th>
                    <th style="width:40px;"></th>
                    <th style="width:120px;"><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></th>
                    <th><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?></th>
                    <th style="width:120px;"><?php esc_html_e('Categoría', 'gestionadmin-wolk'); ?></th>
                    <th style="width:120px;"><?php esc_html_e('Valor Default', 'gestionadmin-wolk'); ?></th>
                    <th style="width:100px;"><?php esc_html_e('Frecuencia', 'gestionadmin-wolk'); ?></th>
                    <th style="width:80px;"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                    <th style="width:120px;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista['items'])): ?>
                    <tr>
                        <td colspan="9"><?php esc_html_e('No se encontraron bonos.', 'gestionadmin-wolk'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lista['items'] as $item): ?>
                        <tr class="<?php echo $item->activo ? '' : 'ga-row-inactive'; ?>">
                            <td><?php echo esc_html($item->orden); ?></td>
                            <td>
                                <span class="dashicons <?php echo esc_attr($item->icono ?: 'dashicons-awards'); ?>" style="color:#0073aa;"></span>
                            </td>
                            <td><code><?php echo esc_html($item->codigo); ?></code></td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-catalogo-bonos&action=edit&id=' . $item->id)); ?>">
                                        <?php echo esc_html($item->nombre); ?>
                                    </a>
                                </strong>
                                <?php if ($item->descripcion): ?>
                                    <br><small class="description"><?php echo esc_html(wp_trim_words($item->descripcion, 10)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="ga-categoria-badge ga-cat-<?php echo esc_attr(strtolower($item->categoria)); ?>">
                                    <?php echo esc_html(GA_Catalogo_Bonos::$categorias[$item->categoria] ?? $item->categoria); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($item->tipo_valor === 'PORCENTAJE'): ?>
                                    <?php echo esc_html(number_format($item->valor_default, 2)); ?>%
                                <?php else: ?>
                                    $<?php echo esc_html(number_format($item->valor_default, 2)); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(GA_Catalogo_Bonos::$frecuencias[$item->frecuencia] ?? $item->frecuencia); ?></td>
                            <td>
                                <?php if ($item->activo): ?>
                                    <span class="ga-estado-badge ga-estado-activo"><?php esc_html_e('Activo', 'gestionadmin-wolk'); ?></span>
                                <?php else: ?>
                                    <span class="ga-estado-badge ga-estado-inactivo"><?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-catalogo-bonos&action=edit&id=' . $item->id)); ?>" class="button button-small">
                                    <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                                </a>
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('ga_bono_action', 'ga_bono_nonce'); ?>
                                    <input type="hidden" name="bono_action" value="toggle_activo">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($item->id); ?>">
                                    <button type="submit" class="button button-small" title="<?php echo $item->activo ? esc_attr__('Desactivar', 'gestionadmin-wolk') : esc_attr__('Activar', 'gestionadmin-wolk'); ?>">
                                        <span class="dashicons dashicons-<?php echo $item->activo ? 'hidden' : 'visibility'; ?>"></span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($lista['pages'] > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
                    echo paginate_links(array(
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $lista['pages'],
                        'current'   => $current_page,
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Formulario Crear/Editar -->
        <div class="ga-form-wrap">
            <form method="post" class="ga-form">
                <?php wp_nonce_field('ga_bono_action', 'ga_bono_nonce'); ?>
                <input type="hidden" name="bono_action" value="<?php echo $bono ? 'actualizar' : 'crear'; ?>">

                <div class="ga-form-columns">
                    <div class="ga-form-main">
                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Información del Bono', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="nombre"><?php esc_html_e('Nombre', 'gestionadmin-wolk'); ?> <span class="required">*</span></label></th>
                                        <td>
                                            <input type="text" id="nombre" name="nombre" class="regular-text" required
                                                   value="<?php echo $bono ? esc_attr($bono->nombre) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="codigo"><?php esc_html_e('Código', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="text" id="codigo" name="codigo" class="regular-text"
                                                   value="<?php echo $bono ? esc_attr($bono->codigo) : ''; ?>"
                                                   placeholder="<?php esc_attr_e('Se genera automáticamente', 'gestionadmin-wolk'); ?>"
                                                   <?php echo $bono ? 'readonly' : ''; ?>>
                                            <p class="description"><?php esc_html_e('Si lo dejas vacío, se generará automáticamente.', 'gestionadmin-wolk'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="descripcion"><?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <textarea id="descripcion" name="descripcion" rows="3" class="large-text"><?php echo $bono ? esc_textarea($bono->descripcion) : ''; ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="condicion_descripcion"><?php esc_html_e('Condición para Obtenerlo', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <textarea id="condicion_descripcion" name="condicion_descripcion" rows="3" class="large-text"
                                                      placeholder="<?php esc_attr_e('Ej: Completar todas las tareas asignadas antes de la fecha límite', 'gestionadmin-wolk'); ?>"><?php echo $bono ? esc_textarea($bono->condicion_descripcion) : ''; ?></textarea>
                                            <p class="description"><?php esc_html_e('Describe qué debe cumplir el aplicante para recibir este bono.', 'gestionadmin-wolk'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Valor y Frecuencia', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="tipo_valor"><?php esc_html_e('Tipo de Valor', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="tipo_valor" name="tipo_valor">
                                                <option value="FIJO" <?php selected($bono ? $bono->tipo_valor : '', 'FIJO'); ?>><?php esc_html_e('Monto Fijo ($)', 'gestionadmin-wolk'); ?></option>
                                                <option value="PORCENTAJE" <?php selected($bono ? $bono->tipo_valor : '', 'PORCENTAJE'); ?>><?php esc_html_e('Porcentaje (%)', 'gestionadmin-wolk'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="valor_default"><?php esc_html_e('Valor por Defecto', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="number" id="valor_default" name="valor_default" step="0.01" min="0" class="small-text"
                                                   value="<?php echo $bono ? esc_attr($bono->valor_default) : '0'; ?>">
                                            <span id="valor_suffix">$</span>
                                            <p class="description"><?php esc_html_e('Este valor se usará como sugerencia al agregar el bono a una orden.', 'gestionadmin-wolk'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="frecuencia"><?php esc_html_e('Frecuencia de Pago', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="frecuencia" name="frecuencia">
                                                <?php foreach (GA_Catalogo_Bonos::$frecuencias as $key => $label): ?>
                                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($bono ? $bono->frecuencia : '', $key); ?>>
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="ga-form-sidebar">
                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Clasificación', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="categoria"><?php esc_html_e('Categoría', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="categoria" name="categoria">
                                                <?php foreach (GA_Catalogo_Bonos::$categorias as $key => $label): ?>
                                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($bono ? $bono->categoria : '', $key); ?>>
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="icono"><?php esc_html_e('Icono', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <select id="icono" name="icono">
                                                <?php
                                                $iconos = array(
                                                    'dashicons-awards'      => __('Premio', 'gestionadmin-wolk'),
                                                    'dashicons-star-filled' => __('Estrella', 'gestionadmin-wolk'),
                                                    'dashicons-money-alt'   => __('Dinero', 'gestionadmin-wolk'),
                                                    'dashicons-chart-line'  => __('Gráfico', 'gestionadmin-wolk'),
                                                    'dashicons-clock'       => __('Reloj', 'gestionadmin-wolk'),
                                                    'dashicons-yes-alt'     => __('Check', 'gestionadmin-wolk'),
                                                    'dashicons-thumbs-up'   => __('Pulgar arriba', 'gestionadmin-wolk'),
                                                    'dashicons-heart'       => __('Corazón', 'gestionadmin-wolk'),
                                                    'dashicons-flag'        => __('Bandera', 'gestionadmin-wolk'),
                                                    'dashicons-megaphone'   => __('Megáfono', 'gestionadmin-wolk'),
                                                    'dashicons-lightbulb'   => __('Idea', 'gestionadmin-wolk'),
                                                    'dashicons-shield'      => __('Escudo', 'gestionadmin-wolk'),
                                                );
                                                $selected_icon = $bono ? $bono->icono : 'dashicons-awards';
                                                foreach ($iconos as $icon_class => $icon_label):
                                                ?>
                                                    <option value="<?php echo esc_attr($icon_class); ?>" <?php selected($selected_icon, $icon_class); ?>>
                                                        <?php echo esc_html($icon_label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span id="icono-preview" class="dashicons <?php echo esc_attr($selected_icon); ?>" style="margin-left:10px;font-size:20px;color:#0073aa;"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="orden"><?php esc_html_e('Orden', 'gestionadmin-wolk'); ?></label></th>
                                        <td>
                                            <input type="number" id="orden" name="orden" min="1" class="small-text"
                                                   value="<?php echo $bono ? esc_attr($bono->orden) : ''; ?>"
                                                   placeholder="Auto">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="postbox">
                            <h2 class="hndle"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></h2>
                            <div class="inside">
                                <label>
                                    <input type="checkbox" name="activo" value="1" <?php checked(!$bono || $bono->activo); ?>>
                                    <?php esc_html_e('Bono activo', 'gestionadmin-wolk'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Solo los bonos activos aparecen al crear acuerdos.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        </div>

                        <div class="ga-form-actions">
                            <button type="submit" class="button button-primary button-large">
                                <?php echo $bono ? esc_html__('Actualizar Bono', 'gestionadmin-wolk') : esc_html__('Crear Bono', 'gestionadmin-wolk'); ?>
                            </button>

                            <?php if ($bono): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-catalogo-bonos&action=new')); ?>" class="button">
                                    <?php esc_html_e('Crear Nuevo', 'gestionadmin-wolk'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
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
.ga-stat-card.ga-stat-info { border-left: 4px solid #17a2b8; }

.ga-filter-bar {
    background: #fff;
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}
.ga-filter-bar form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}
.ga-filter-bar select,
.ga-filter-bar input[type="text"] {
    min-width: 150px;
}

.ga-row-inactive {
    opacity: 0.6;
    background: #f9f9f9;
}

.ga-categoria-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}
.ga-cat-productividad { background: #d4edda; color: #155724; }
.ga-cat-asistencia { background: #cce5ff; color: #004085; }
.ga-cat-calidad { background: #fff3cd; color: #856404; }
.ga-cat-comunicacion { background: #d1ecf1; color: #0c5460; }
.ga-cat-metas { background: #e2d5f1; color: #4a235a; }
.ga-cat-otro { background: #e2e3e5; color: #383d41; }

.ga-estado-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}
.ga-estado-activo { background: #d4edda; color: #155724; }
.ga-estado-inactivo { background: #f8d7da; color: #721c24; }

/* Formulario */
.ga-form-wrap {
    margin-top: 20px;
}
.ga-form-columns {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
}
.ga-form-main .postbox,
.ga-form-sidebar .postbox {
    margin-bottom: 20px;
}
.ga-form-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}
.ga-form-actions .button {
    text-align: center;
}

@media screen and (max-width: 1200px) {
    .ga-form-columns {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar preview de icono
    var iconoSelect = document.getElementById('icono');
    var iconoPreview = document.getElementById('icono-preview');

    if (iconoSelect && iconoPreview) {
        iconoSelect.addEventListener('change', function() {
            iconoPreview.className = 'dashicons ' + this.value;
        });
    }

    // Actualizar sufijo del valor según tipo
    var tipoValor = document.getElementById('tipo_valor');
    var valorSuffix = document.getElementById('valor_suffix');

    if (tipoValor && valorSuffix) {
        tipoValor.addEventListener('change', function() {
            valorSuffix.textContent = this.value === 'PORCENTAJE' ? '%' : '$';
        });
        // Trigger inicial
        valorSuffix.textContent = tipoValor.value === 'PORCENTAJE' ? '%' : '$';
    }
});
</script>
