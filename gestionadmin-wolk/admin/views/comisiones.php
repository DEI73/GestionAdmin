<?php
/**
 * Vista Admin: Comisiones Generadas
 *
 * Muestra el listado de comisiones generadas automáticamente
 * cuando se pagan facturas de órdenes de trabajo.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar módulo
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-comisiones.php';
$comisiones_module = GA_Comisiones::get_instance();

// Parámetros de filtrado
$estado       = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
$aplicante_id = isset($_GET['aplicante_id']) ? absint($_GET['aplicante_id']) : '';
$orden_id     = isset($_GET['orden_id']) ? absint($_GET['orden_id']) : '';
$fecha_desde  = isset($_GET['fecha_desde']) ? sanitize_text_field($_GET['fecha_desde']) : '';
$fecha_hasta  = isset($_GET['fecha_hasta']) ? sanitize_text_field($_GET['fecha_hasta']) : '';
$orderby      = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
$order        = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
$paged        = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

// Obtener datos
$result = $comisiones_module->get_all(array(
    'aplicante_id' => $aplicante_id,
    'orden_id'     => $orden_id,
    'estado'       => $estado,
    'fecha_desde'  => $fecha_desde,
    'fecha_hasta'  => $fecha_hasta,
    'orderby'      => $orderby,
    'order'        => $order,
    'page'         => $paged,
    'per_page'     => 20,
));

$items = $result['items'];
$total = $result['total'];
$pages = $result['pages'];

// Obtener enums
$estados = GA_Comisiones::get_estados();
$tipos_origen = GA_Comisiones::get_tipos_origen();

// Obtener estadísticas
$stats = $comisiones_module->get_estadisticas();

// URL base para filtros
$base_url = admin_url('admin.php?page=ga-comisiones');

/**
 * Helper para generar URL con orden
 */
function ga_comisiones_sort_url($column, $current_orderby, $current_order) {
    $new_order = ($current_orderby === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
    $params = $_GET;
    $params['orderby'] = $column;
    $params['order'] = $new_order;
    unset($params['paged']);
    return add_query_arg($params, admin_url('admin.php'));
}

/**
 * Helper para clase de ordenamiento
 */
function ga_comisiones_sort_class($column, $current_orderby, $current_order) {
    if ($current_orderby !== $column) {
        return 'sortable desc';
    }
    return 'sorted ' . strtolower($current_order);
}
?>

<div class="wrap ga-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-money-alt"></span>
        <?php esc_html_e('Comisiones Generadas', 'gestionadmin-wolk'); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Tarjetas de estadísticas -->
    <div class="ga-stats-cards">
        <div class="ga-stat-card ga-stat-total">
            <div class="ga-stat-icon">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="ga-stat-content">
                <span class="ga-stat-number"><?php echo number_format($stats['total_comisiones']); ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Total Comisiones', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>

        <div class="ga-stat-card ga-stat-disponible">
            <div class="ga-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="ga-stat-content">
                <span class="ga-stat-number">$<?php echo number_format($stats['pendiente_pago'], 2); ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Pendiente de Pago', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>

        <div class="ga-stat-card ga-stat-pagado">
            <div class="ga-stat-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="ga-stat-content">
                <span class="ga-stat-number">$<?php echo number_format($stats['monto_total'], 2); ?></span>
                <span class="ga-stat-label"><?php esc_html_e('Monto Total', 'gestionadmin-wolk'); ?></span>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="ga-filters-bar">
        <form method="get" action="<?php echo esc_url($base_url); ?>">
            <input type="hidden" name="page" value="ga-comisiones">

            <div class="ga-filter-group">
                <select name="estado" class="ga-filter-select">
                    <option value=""><?php esc_html_e('Todos los estados', 'gestionadmin-wolk'); ?></option>
                    <?php foreach ($estados as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($estado, $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ga-filter-group">
                <input type="date" name="fecha_desde" value="<?php echo esc_attr($fecha_desde); ?>"
                       placeholder="<?php esc_attr_e('Desde', 'gestionadmin-wolk'); ?>" class="ga-filter-date">
            </div>

            <div class="ga-filter-group">
                <input type="date" name="fecha_hasta" value="<?php echo esc_attr($fecha_hasta); ?>"
                       placeholder="<?php esc_attr_e('Hasta', 'gestionadmin-wolk'); ?>" class="ga-filter-date">
            </div>

            <div class="ga-filter-actions">
                <button type="submit" class="button">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?>
                </button>
                <a href="<?php echo esc_url($base_url); ?>" class="button">
                    <?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de comisiones -->
    <table class="wp-list-table widefat fixed striped ga-table">
        <thead>
            <tr>
                <th scope="col" class="<?php echo esc_attr(ga_comisiones_sort_class('id', $orderby, $order)); ?>">
                    <a href="<?php echo esc_url(ga_comisiones_sort_url('id', $orderby, $order)); ?>">
                        <span><?php esc_html_e('ID', 'gestionadmin-wolk'); ?></span>
                        <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                    </a>
                </th>
                <th scope="col"><?php esc_html_e('Orden', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Proveedor', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Tipo Acuerdo', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Base', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('% / Fijo', 'gestionadmin-wolk'); ?></th>
                <th scope="col" class="<?php echo esc_attr(ga_comisiones_sort_class('monto_comision', $orderby, $order)); ?>">
                    <a href="<?php echo esc_url(ga_comisiones_sort_url('monto_comision', $orderby, $order)); ?>">
                        <span><?php esc_html_e('Comisión', 'gestionadmin-wolk'); ?></span>
                        <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                    </a>
                </th>
                <th scope="col" class="<?php echo esc_attr(ga_comisiones_sort_class('estado', $orderby, $order)); ?>">
                    <a href="<?php echo esc_url(ga_comisiones_sort_url('estado', $orderby, $order)); ?>">
                        <span><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></span>
                        <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                    </a>
                </th>
                <th scope="col" class="<?php echo esc_attr(ga_comisiones_sort_class('created_at', $orderby, $order)); ?>">
                    <a href="<?php echo esc_url(ga_comisiones_sort_url('created_at', $orderby, $order)); ?>">
                        <span><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></span>
                        <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)) : ?>
                <tr>
                    <td colspan="9" class="ga-no-items">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('No se encontraron comisiones con los filtros seleccionados.', 'gestionadmin-wolk'); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($items as $comision) : ?>
                    <tr>
                        <td>
                            <strong>#<?php echo esc_html($comision->id); ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($comision->orden_codigo)) : ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=ga-ordenes-trabajo&action=ver&id=' . $comision->orden_id)); ?>">
                                    <?php echo esc_html($comision->orden_codigo); ?>
                                </a>
                                <br>
                                <small class="ga-text-muted"><?php echo esc_html(wp_trim_words($comision->orden_titulo, 5)); ?></small>
                            <?php else : ?>
                                <span class="ga-text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($comision->aplicante_nombre)) : ?>
                                <?php echo esc_html($comision->aplicante_nombre . ' ' . $comision->aplicante_apellido); ?>
                                <br>
                                <small class="ga-text-muted"><?php echo esc_html($comision->aplicante_email); ?></small>
                            <?php else : ?>
                                <span class="ga-text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($comision->tipo_acuerdo)) : ?>
                                <span class="ga-badge ga-badge-outline">
                                    <?php echo esc_html($comision->tipo_acuerdo); ?>
                                </span>
                            <?php else : ?>
                                <span class="ga-text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            $<?php echo number_format($comision->monto_base, 2); ?>
                        </td>
                        <td>
                            <?php if ($comision->porcentaje_aplicado) : ?>
                                <?php echo number_format($comision->porcentaje_aplicado, 2); ?>%
                            <?php elseif ($comision->monto_fijo_aplicado) : ?>
                                $<?php echo number_format($comision->monto_fijo_aplicado, 2); ?>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong class="ga-amount">$<?php echo number_format($comision->monto_comision, 2); ?></strong>
                        </td>
                        <td>
                            <?php
                            $badge_class = 'ga-badge-secondary';
                            switch ($comision->estado) {
                                case 'DISPONIBLE':
                                    $badge_class = 'ga-badge-success';
                                    break;
                                case 'SOLICITADA':
                                    $badge_class = 'ga-badge-warning';
                                    break;
                                case 'PAGADA':
                                    $badge_class = 'ga-badge-primary';
                                    break;
                                case 'CANCELADA':
                                    $badge_class = 'ga-badge-danger';
                                    break;
                            }
                            ?>
                            <span class="ga-badge <?php echo esc_attr($badge_class); ?>">
                                <?php echo esc_html($estados[$comision->estado] ?? $comision->estado); ?>
                            </span>
                            <?php if ($comision->solicitud_id) : ?>
                                <br>
                                <small>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=ga-solicitudes-cobro&action=ver&id=' . $comision->solicitud_id)); ?>">
                                        <?php esc_html_e('Ver solicitud', 'gestionadmin-wolk'); ?>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($comision->created_at))); ?>
                            <br>
                            <small class="ga-text-muted">
                                <?php echo esc_html($tipos_origen[$comision->tipo_origen] ?? $comision->tipo_origen); ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col"><?php esc_html_e('ID', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Orden', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Proveedor', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Tipo Acuerdo', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Base', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('% / Fijo', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Comisión', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                <th scope="col"><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
            </tr>
        </tfoot>
    </table>

    <!-- Paginación -->
    <?php if ($pages > 1) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(
                        esc_html(_n('%s elemento', '%s elementos', $total, 'gestionadmin-wolk')),
                        number_format_i18n($total)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    $pagination_args = array(
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $pages,
                        'current'   => $paged,
                    );
                    echo paginate_links($pagination_args);
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.ga-stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.ga-stat-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.ga-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ga-stat-icon .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #fff;
}

.ga-stat-total .ga-stat-icon {
    background: #2271b1;
}

.ga-stat-disponible .ga-stat-icon {
    background: #dba617;
}

.ga-stat-pagado .ga-stat-icon {
    background: #00a32a;
}

.ga-stat-number {
    display: block;
    font-size: 24px;
    font-weight: 600;
    color: #1d2327;
}

.ga-stat-label {
    display: block;
    color: #646970;
    font-size: 13px;
}

.ga-filters-bar {
    background: #fff;
    border: 1px solid #c3c4c7;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.ga-filters-bar form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.ga-filter-select,
.ga-filter-date {
    min-width: 150px;
}

.ga-filter-actions {
    display: flex;
    gap: 5px;
}

.ga-table .ga-no-items {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
}

.ga-table .ga-no-items .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    display: block;
    margin: 0 auto 10px;
}

.ga-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.ga-badge-success {
    background: #d7f5dc;
    color: #006908;
}

.ga-badge-warning {
    background: #fff3cd;
    color: #856404;
}

.ga-badge-primary {
    background: #cfe2ff;
    color: #084298;
}

.ga-badge-danger {
    background: #f8d7da;
    color: #842029;
}

.ga-badge-secondary {
    background: #e9ecef;
    color: #6c757d;
}

.ga-badge-outline {
    background: transparent;
    border: 1px solid #c3c4c7;
    color: #50575e;
}

.ga-text-muted {
    color: #646970;
}

.ga-amount {
    color: #00a32a;
}
</style>
