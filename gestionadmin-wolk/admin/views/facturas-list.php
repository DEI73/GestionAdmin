<?php
/**
 * Vista parcial: Listado de Facturas
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener estadísticas
$stats = $facturas->get_estadisticas();

// Filtros
$filtro_estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
$filtro_cliente = isset($_GET['cliente_id']) ? absint($_GET['cliente_id']) : 0;
$filtro_pais = isset($_GET['pais']) ? sanitize_text_field($_GET['pais']) : '';
$filtro_buscar = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

// Obtener facturas
$resultado = $facturas->listar(array(
    'estado'     => $filtro_estado,
    'cliente_id' => $filtro_cliente,
    'pais'       => $filtro_pais,
    'buscar'     => $filtro_buscar,
    'page'       => $page,
    'per_page'   => 20,
));
?>

<!-- Tarjetas de estadísticas -->
<div class="ga-stats-cards">
    <div class="ga-stat-card ga-stat-info">
        <div class="ga-stat-number"><?php echo esc_html(number_format($stats->total_facturado, 0)); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Total Facturado', 'gestionadmin-wolk'); ?></div>
    </div>
    <div class="ga-stat-card ga-stat-success">
        <div class="ga-stat-number"><?php echo esc_html(number_format($stats->total_cobrado, 0)); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Total Cobrado', 'gestionadmin-wolk'); ?></div>
    </div>
    <div class="ga-stat-card ga-stat-warning">
        <div class="ga-stat-number"><?php echo esc_html(number_format($stats->total_pendiente, 0)); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Pendiente Cobro', 'gestionadmin-wolk'); ?></div>
    </div>
    <div class="ga-stat-card ga-stat-danger">
        <div class="ga-stat-number"><?php echo esc_html($stats->vencidas); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Vencidas', 'gestionadmin-wolk'); ?></div>
    </div>
</div>

<!-- Filtros -->
<div class="tablenav top">
    <form method="get" action="">
        <input type="hidden" name="page" value="gestionadmin-facturas">

        <select name="estado">
            <option value=""><?php esc_html_e('— Estado —', 'gestionadmin-wolk'); ?></option>
            <option value="BORRADOR" <?php selected($filtro_estado, 'BORRADOR'); ?>><?php esc_html_e('Borrador', 'gestionadmin-wolk'); ?></option>
            <option value="ENVIADA" <?php selected($filtro_estado, 'ENVIADA'); ?>><?php esc_html_e('Enviada', 'gestionadmin-wolk'); ?></option>
            <option value="PARCIAL" <?php selected($filtro_estado, 'PARCIAL'); ?>><?php esc_html_e('Pago Parcial', 'gestionadmin-wolk'); ?></option>
            <option value="PAGADA" <?php selected($filtro_estado, 'PAGADA'); ?>><?php esc_html_e('Pagada', 'gestionadmin-wolk'); ?></option>
            <option value="VENCIDA" <?php selected($filtro_estado, 'VENCIDA'); ?>><?php esc_html_e('Vencida', 'gestionadmin-wolk'); ?></option>
            <option value="ANULADA" <?php selected($filtro_estado, 'ANULADA'); ?>><?php esc_html_e('Anulada', 'gestionadmin-wolk'); ?></option>
        </select>

        <select name="cliente_id">
            <option value=""><?php esc_html_e('— Cliente —', 'gestionadmin-wolk'); ?></option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo esc_attr($cliente->id); ?>" <?php selected($filtro_cliente, $cliente->id); ?>>
                    <?php echo esc_html($cliente->nombre_comercial); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="pais">
            <option value=""><?php esc_html_e('— País —', 'gestionadmin-wolk'); ?></option>
            <?php foreach ($paises as $pais): ?>
                <option value="<?php echo esc_attr($pais->codigo_iso); ?>" <?php selected($filtro_pais, $pais->codigo_iso); ?>>
                    <?php echo esc_html($pais->nombre); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="search" name="s" value="<?php echo esc_attr($filtro_buscar); ?>" placeholder="<?php esc_attr_e('Buscar...', 'gestionadmin-wolk'); ?>">

        <input type="submit" class="button" value="<?php esc_attr_e('Filtrar', 'gestionadmin-wolk'); ?>">

        <?php if ($filtro_estado || $filtro_cliente || $filtro_pais || $filtro_buscar): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas')); ?>" class="button">
                <?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?>
            </a>
        <?php endif; ?>
    </form>

    <div class="tablenav-pages">
        <span class="displaying-num">
            <?php printf(
                esc_html(_n('%s factura', '%s facturas', $resultado['total'], 'gestionadmin-wolk')),
                number_format_i18n($resultado['total'])
            ); ?>
        </span>
    </div>
</div>

<!-- Tabla de facturas -->
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th style="width: 120px;"><?php esc_html_e('Número', 'gestionadmin-wolk'); ?></th>
            <th><?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?></th>
            <th style="width: 80px;"><?php esc_html_e('País', 'gestionadmin-wolk'); ?></th>
            <th style="width: 100px;"><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
            <th style="width: 100px;"><?php esc_html_e('Vence', 'gestionadmin-wolk'); ?></th>
            <th style="width: 110px;"><?php esc_html_e('Total', 'gestionadmin-wolk'); ?></th>
            <th style="width: 110px;"><?php esc_html_e('Saldo', 'gestionadmin-wolk'); ?></th>
            <th style="width: 90px;"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
            <th style="width: 120px;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($resultado['items'])): ?>
            <tr>
                <td colspan="9"><?php esc_html_e('No se encontraron facturas.', 'gestionadmin-wolk'); ?></td>
            </tr>
        <?php else: ?>
            <?php foreach ($resultado['items'] as $fac): ?>
                <tr>
                    <td>
                        <strong>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $fac->id)); ?>">
                                <?php echo esc_html($fac->numero); ?>
                            </a>
                        </strong>
                    </td>
                    <td><?php echo esc_html($fac->cliente_nombre); ?></td>
                    <td><?php echo esc_html($fac->pais_facturacion); ?></td>
                    <td><?php echo esc_html($fac->fecha_emision); ?></td>
                    <td>
                        <?php
                        echo esc_html($fac->fecha_vencimiento);
                        // Indicador de vencido
                        if ($fac->estado === 'VENCIDA' || ($fac->fecha_vencimiento < date('Y-m-d') && !in_array($fac->estado, array('PAGADA', 'ANULADA', 'BORRADOR')))) {
                            echo ' <span style="color:#dc3545;">⚠</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <strong><?php echo esc_html($fac->moneda); ?> <?php echo esc_html(number_format($fac->total_a_pagar, 2)); ?></strong>
                    </td>
                    <td>
                        <?php if ($fac->saldo_pendiente > 0): ?>
                            <span style="color:#dc3545;">
                                <?php echo esc_html($fac->moneda); ?> <?php echo esc_html(number_format($fac->saldo_pendiente, 2)); ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#28a745;">$0.00</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="ga-factura-estado ga-estado-<?php echo esc_attr(strtolower($fac->estado)); ?>">
                            <?php echo esc_html($fac->estado); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $fac->id)); ?>" class="button button-small">
                            <?php esc_html_e('Editar', 'gestionadmin-wolk'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=preview&id=' . $fac->id)); ?>" class="button button-small" target="_blank">
                            <?php esc_html_e('Ver', 'gestionadmin-wolk'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Paginación -->
<?php if ($resultado['pages'] > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            $base_url = admin_url('admin.php?page=gestionadmin-facturas');
            if ($filtro_estado) $base_url .= '&estado=' . urlencode($filtro_estado);
            if ($filtro_cliente) $base_url .= '&cliente_id=' . $filtro_cliente;
            if ($filtro_pais) $base_url .= '&pais=' . urlencode($filtro_pais);
            if ($filtro_buscar) $base_url .= '&s=' . urlencode($filtro_buscar);

            echo paginate_links(array(
                'base'      => $base_url . '&paged=%#%',
                'format'    => '',
                'current'   => $resultado['page'],
                'total'     => $resultado['pages'],
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ));
            ?>
        </div>
    </div>
<?php endif; ?>
