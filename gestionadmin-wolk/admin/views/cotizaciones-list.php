<?php
/**
 * Vista Parcial: Listado de Cotizaciones
 *
 * Tabla con todas las cotizaciones/presupuestos con filtros y estadísticas.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener filtros actuales
$filtro_estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
$filtro_cliente = isset($_GET['cliente_id']) ? absint($_GET['cliente_id']) : 0;
$filtro_busqueda = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

// Obtener estadísticas
$stats = $cotizaciones->get_estadisticas();

// Obtener listado con filtros
$args = array(
    'estado'     => $filtro_estado,
    'cliente_id' => $filtro_cliente,
    'busqueda'   => $filtro_busqueda,
    'page'       => $paged,
    'per_page'   => 20,
);
$resultado = $cotizaciones->listar($args);
$items = $resultado['items'];
$total_items = $resultado['total'];
$total_pages = $resultado['pages'];

// Estados disponibles para filtro
$estados = array(
    'BORRADOR'   => __('Borrador', 'gestionadmin-wolk'),
    'ENVIADA'    => __('Enviada', 'gestionadmin-wolk'),
    'APROBADA'   => __('Aprobada', 'gestionadmin-wolk'),
    'RECHAZADA'  => __('Rechazada', 'gestionadmin-wolk'),
    'FACTURADA'  => __('Facturada', 'gestionadmin-wolk'),
    'VENCIDA'    => __('Vencida', 'gestionadmin-wolk'),
    'CANCELADA'  => __('Cancelada', 'gestionadmin-wolk'),
);
?>

<!-- Tarjetas de Estadísticas -->
<div class="ga-stats-cards">
    <div class="ga-stat-card ga-stat-info">
        <div class="ga-stat-number"><?php echo esc_html(number_format($stats['total_cotizado'] ?? 0, 2)); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Total Cotizado', 'gestionadmin-wolk'); ?></div>
    </div>
    <div class="ga-stat-card ga-stat-warning">
        <div class="ga-stat-number"><?php echo esc_html($stats['pendientes'] ?? 0); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Pendientes Respuesta', 'gestionadmin-wolk'); ?></div>
    </div>
    <div class="ga-stat-card ga-stat-success">
        <div class="ga-stat-number"><?php echo esc_html($stats['aprobadas'] ?? 0); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Aprobadas', 'gestionadmin-wolk'); ?></div>
    </div>
    <div class="ga-stat-card ga-stat-danger">
        <div class="ga-stat-number"><?php echo esc_html($stats['rechazadas'] ?? 0); ?></div>
        <div class="ga-stat-label"><?php esc_html_e('Rechazadas', 'gestionadmin-wolk'); ?></div>
    </div>
    <div class="ga-stat-card">
        <div class="ga-stat-number"><?php echo esc_html(number_format(($stats['tasa_conversion'] ?? 0), 1)); ?>%</div>
        <div class="ga-stat-label"><?php esc_html_e('Tasa Conversión', 'gestionadmin-wolk'); ?></div>
    </div>
</div>

<!-- Filtros -->
<div class="tablenav top">
    <form method="get" class="ga-filter-form">
        <input type="hidden" name="page" value="gestionadmin-cotizaciones">

        <select name="estado">
            <option value=""><?php esc_html_e('— Todos los estados —', 'gestionadmin-wolk'); ?></option>
            <?php foreach ($estados as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($filtro_estado, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="cliente_id">
            <option value=""><?php esc_html_e('— Todos los clientes —', 'gestionadmin-wolk'); ?></option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo esc_attr($cliente->id); ?>" <?php selected($filtro_cliente, $cliente->id); ?>>
                    <?php echo esc_html($cliente->codigo . ' - ' . $cliente->nombre_comercial); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="search" name="s" value="<?php echo esc_attr($filtro_busqueda); ?>"
               placeholder="<?php esc_attr_e('Buscar...', 'gestionadmin-wolk'); ?>">

        <button type="submit" class="button"><?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?></button>

        <?php if ($filtro_estado || $filtro_cliente || $filtro_busqueda): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones')); ?>" class="button">
                <?php esc_html_e('Limpiar', 'gestionadmin-wolk'); ?>
            </a>
        <?php endif; ?>
    </form>

    <div class="tablenav-pages">
        <span class="displaying-num">
            <?php printf(
                _n('%s cotización', '%s cotizaciones', $total_items, 'gestionadmin-wolk'),
                number_format_i18n($total_items)
            ); ?>
        </span>
    </div>
</div>

<!-- Tabla de Cotizaciones -->
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th scope="col" style="width:120px;"><?php esc_html_e('Número', 'gestionadmin-wolk'); ?></th>
            <th scope="col"><?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?></th>
            <th scope="col" style="width:100px;"><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
            <th scope="col" style="width:100px;"><?php esc_html_e('Válida Hasta', 'gestionadmin-wolk'); ?></th>
            <th scope="col" style="width:100px;"><?php esc_html_e('Total', 'gestionadmin-wolk'); ?></th>
            <th scope="col" style="width:100px;"><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
            <th scope="col" style="width:180px;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($items)): ?>
            <tr>
                <td colspan="7"><?php esc_html_e('No se encontraron cotizaciones.', 'gestionadmin-wolk'); ?></td>
            </tr>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <strong>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $item->id)); ?>">
                                <?php echo esc_html($item->numero); ?>
                            </a>
                        </strong>
                    </td>
                    <td>
                        <?php echo esc_html($item->cliente_nombre ?: $item->cliente_nombre_snapshot); ?>
                        <?php if ($item->titulo): ?>
                            <br><small class="ga-cotizacion-titulo"><?php echo esc_html($item->titulo); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($item->fecha))); ?></td>
                    <td>
                        <?php
                        $fecha_valida = strtotime($item->fecha_validez);
                        $hoy = strtotime('today');
                        $clase_vencida = ($fecha_valida < $hoy && !in_array($item->estado, array('FACTURADA', 'CANCELADA'))) ? 'ga-fecha-vencida' : '';
                        ?>
                        <span class="<?php echo esc_attr($clase_vencida); ?>">
                            <?php echo esc_html(date_i18n(get_option('date_format'), $fecha_valida)); ?>
                        </span>
                    </td>
                    <td><strong>$<?php echo esc_html(number_format($item->total, 2)); ?></strong></td>
                    <td>
                        <?php
                        $estado_clases = array(
                            'BORRADOR'  => 'ga-estado-borrador',
                            'ENVIADA'   => 'ga-estado-enviada',
                            'APROBADA'  => 'ga-estado-aprobada',
                            'RECHAZADA' => 'ga-estado-rechazada',
                            'FACTURADA' => 'ga-estado-facturada',
                            'VENCIDA'   => 'ga-estado-vencida',
                            'CANCELADA' => 'ga-estado-cancelada',
                        );
                        $clase = isset($estado_clases[$item->estado]) ? $estado_clases[$item->estado] : '';
                        ?>
                        <span class="ga-cotizacion-estado <?php echo esc_attr($clase); ?>">
                            <?php echo esc_html($estados[$item->estado] ?? $item->estado); ?>
                        </span>
                    </td>
                    <td>
                        <div class="ga-row-actions">
                            <!-- Editar -->
                            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $item->id)); ?>"
                               class="button button-small" title="<?php esc_attr_e('Editar', 'gestionadmin-wolk'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </a>

                            <!-- Vista previa -->
                            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones&action=preview&id=' . $item->id)); ?>"
                               class="button button-small" title="<?php esc_attr_e('Vista Previa', 'gestionadmin-wolk'); ?>" target="_blank">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>

                            <?php if ($item->estado === 'APROBADA'): ?>
                                <!-- Convertir a Factura -->
                                <a href="#" class="button button-small button-primary ga-btn-convertir"
                                   data-id="<?php echo esc_attr($item->id); ?>"
                                   data-numero="<?php echo esc_attr($item->numero); ?>"
                                   title="<?php esc_attr_e('Convertir a Factura', 'gestionadmin-wolk'); ?>">
                                    <span class="dashicons dashicons-money-alt"></span>
                                </a>
                            <?php endif; ?>

                            <?php if ($item->estado === 'BORRADOR'): ?>
                                <!-- Eliminar -->
                                <a href="<?php echo esc_url(wp_nonce_url(
                                    admin_url('admin.php?page=gestionadmin-cotizaciones&action=delete&id=' . $item->id),
                                    'ga_delete_cotizacion_' . $item->id
                                )); ?>"
                                   class="button button-small ga-btn-delete"
                                   title="<?php esc_attr_e('Eliminar', 'gestionadmin-wolk'); ?>"
                                   onclick="return confirm('<?php esc_attr_e('¿Está seguro de eliminar esta cotización?', 'gestionadmin-wolk'); ?>');">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Paginación -->
<?php if ($total_pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            $pagination_args = array(
                'base'      => add_query_arg('paged', '%#%'),
                'format'    => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total'     => $total_pages,
                'current'   => $paged,
            );
            echo paginate_links($pagination_args);
            ?>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Convertir a Factura -->
<div id="ga-modal-convertir" class="ga-modal" style="display:none;">
    <div class="ga-modal-content">
        <div class="ga-modal-header">
            <h2><?php esc_html_e('Convertir Cotización a Factura', 'gestionadmin-wolk'); ?></h2>
            <button type="button" class="ga-modal-close">&times;</button>
        </div>
        <form method="post" id="ga-form-convertir">
            <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
            <input type="hidden" name="cotizacion_action" value="convertir_factura">
            <input type="hidden" name="cotizacion_id" id="convertir_cotizacion_id" value="">

            <div class="ga-modal-body">
                <p id="ga-convertir-mensaje"></p>

                <div class="ga-form-group">
                    <label for="pais_facturacion"><?php esc_html_e('País de Facturación:', 'gestionadmin-wolk'); ?></label>
                    <select name="pais_facturacion" id="pais_facturacion" required>
                        <option value=""><?php esc_html_e('— Seleccione país —', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($paises as $pais): ?>
                            <option value="<?php echo esc_attr($pais->pais_iso); ?>">
                                <?php echo esc_html($pais->nombre . ' (' . $pais->moneda . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Este país determinará el consecutivo y configuración de impuestos.', 'gestionadmin-wolk'); ?></p>
                </div>
            </div>

            <div class="ga-modal-footer">
                <button type="button" class="button ga-modal-close"><?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?></button>
                <button type="submit" class="button button-primary">
                    <span class="dashicons dashicons-money-alt"></span>
                    <?php esc_html_e('Crear Factura', 'gestionadmin-wolk'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.ga-filter-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}
.ga-filter-form select,
.ga-filter-form input[type="search"] {
    max-width: 200px;
}
.ga-row-actions {
    display: flex;
    gap: 5px;
}
.ga-row-actions .button-small {
    padding: 0 6px;
    min-height: 28px;
    line-height: 26px;
}
.ga-row-actions .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    vertical-align: middle;
}
.ga-cotizacion-titulo {
    color: #666;
    font-style: italic;
}
.ga-fecha-vencida {
    color: #dc3545;
    font-weight: 600;
}

/* Modal */
.ga-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ga-modal-content {
    background: #fff;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.3);
}
.ga-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
}
.ga-modal-header h2 {
    margin: 0;
    font-size: 18px;
}
.ga-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}
.ga-modal-body {
    padding: 20px;
}
.ga-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.ga-form-group {
    margin-bottom: 15px;
}
.ga-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}
.ga-form-group select {
    width: 100%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal convertir a factura
    var modal = document.getElementById('ga-modal-convertir');
    var btnConvertir = document.querySelectorAll('.ga-btn-convertir');
    var btnClose = modal.querySelectorAll('.ga-modal-close');
    var inputId = document.getElementById('convertir_cotizacion_id');
    var mensaje = document.getElementById('ga-convertir-mensaje');

    btnConvertir.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-id');
            var numero = this.getAttribute('data-numero');
            inputId.value = id;
            mensaje.innerHTML = '<?php esc_html_e('Se creará una factura a partir de la cotización', 'gestionadmin-wolk'); ?> <strong>' + numero + '</strong>. <?php esc_html_e('Todas las líneas de detalle serán copiadas.', 'gestionadmin-wolk'); ?>';
            modal.style.display = 'flex';
        });
    });

    btnClose.forEach(function(btn) {
        btn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Actualizar action del form para incluir id
    document.getElementById('ga-form-convertir').addEventListener('submit', function() {
        var id = inputId.value;
        this.action = '<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=')); ?>' + id;
    });
});
</script>
