<?php
/**
 * Vista parcial: Facturar Horas
 *
 * Permite seleccionar horas aprobadas para agregarlas a una factura.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener filtros
$filtro_cliente = isset($_GET['cliente_filter']) ? absint($_GET['cliente_filter']) : $factura->cliente_id;
$filtro_proyecto = isset($_GET['proyecto_filter']) ? absint($_GET['proyecto_filter']) : 0;

// Obtener horas facturables
$horas_facturables = $facturas->get_horas_facturables(array(
    'cliente_id'  => $filtro_cliente,
    'proyecto_id' => $filtro_proyecto,
));

// Obtener proyectos del cliente para filtrar
global $wpdb;
$proyectos = $wpdb->get_results($wpdb->prepare(
    "SELECT p.* FROM {$wpdb->prefix}ga_proyectos p
     INNER JOIN {$wpdb->prefix}ga_casos c ON p.caso_id = c.id
     WHERE c.cliente_id = %d
     ORDER BY p.nombre",
    $factura->cliente_id
));
?>

<div class="ga-facturar-horas-wrap">
    <div class="postbox">
        <h2 class="hndle">
            <span class="dashicons dashicons-clock"></span>
            <?php esc_html_e('Facturar Horas Aprobadas', 'gestionadmin-wolk'); ?>
        </h2>
        <div class="inside">
            <p><?php printf(
                esc_html__('Seleccione las horas aprobadas que desea agregar a la factura %s.', 'gestionadmin-wolk'),
                '<strong>' . esc_html($factura->numero) . '</strong>'
            ); ?></p>

            <!-- Filtros -->
            <div class="ga-filter-bar">
                <form method="get">
                    <input type="hidden" name="page" value="gestionadmin-facturas">
                    <input type="hidden" name="action" value="facturar_horas">
                    <input type="hidden" name="id" value="<?php echo esc_attr($factura->id); ?>">

                    <select name="proyecto_filter">
                        <option value=""><?php esc_html_e('— Todos los proyectos —', 'gestionadmin-wolk'); ?></option>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <option value="<?php echo esc_attr($proyecto->id); ?>" <?php selected($filtro_proyecto, $proyecto->id); ?>>
                                <?php echo esc_html($proyecto->codigo . ' - ' . $proyecto->nombre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="button"><?php esc_html_e('Filtrar', 'gestionadmin-wolk'); ?></button>
                </form>
            </div>

            <?php if (empty($horas_facturables)): ?>
                <div class="notice notice-info inline">
                    <p><?php esc_html_e('No hay horas aprobadas pendientes de facturar para este cliente.', 'gestionadmin-wolk'); ?></p>
                </div>
            <?php else: ?>
                <form method="post" id="ga-facturar-horas-form">
                    <?php wp_nonce_field('ga_factura_action', 'ga_factura_nonce'); ?>
                    <input type="hidden" name="factura_action" value="facturar_horas">

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" id="ga-select-all">
                                </th>
                                <th><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Usuario', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Tarea', 'gestionadmin-wolk'); ?></th>
                                <th><?php esc_html_e('Proyecto', 'gestionadmin-wolk'); ?></th>
                                <th style="width:100px;"><?php esc_html_e('Horas', 'gestionadmin-wolk'); ?></th>
                                <th style="width:100px;"><?php esc_html_e('Costo', 'gestionadmin-wolk'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_horas = 0;
                            $total_costo = 0;
                            foreach ($horas_facturables as $hora):
                                $horas_decimal = $hora->minutos_efectivos / 60;
                                $costo = $horas_decimal * ($hora->tarifa_hora ?: 0);
                                $total_horas += $horas_decimal;
                                $total_costo += $costo;
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="horas_ids[]" value="<?php echo esc_attr($hora->id); ?>" class="ga-hora-checkbox">
                                    </td>
                                    <td><?php echo esc_html($hora->fecha); ?></td>
                                    <td><?php echo esc_html($hora->usuario_nombre); ?></td>
                                    <td><?php echo esc_html($hora->tarea_nombre ?: '—'); ?></td>
                                    <td><?php echo esc_html($hora->proyecto_nombre ?: '—'); ?></td>
                                    <td><?php echo esc_html(number_format($horas_decimal, 2)); ?> hrs</td>
                                    <td>$<?php echo esc_html(number_format($costo, 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" style="text-align:right;"><strong><?php esc_html_e('Total:', 'gestionadmin-wolk'); ?></strong></th>
                                <th><strong><?php echo esc_html(number_format($total_horas, 2)); ?> hrs</strong></th>
                                <th><strong>$<?php echo esc_html(number_format($total_costo, 2)); ?></strong></th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="ga-facturar-footer">
                        <div class="ga-tarifa-field">
                            <label for="tarifa_hora"><?php esc_html_e('Tarifa por Hora a Facturar:', 'gestionadmin-wolk'); ?></label>
                            <input type="number" name="tarifa_hora" id="tarifa_hora" value="15.00" step="0.01" min="0.01" required>
                            <span class="description"><?php esc_html_e('Esta tarifa se aplicará a todas las horas seleccionadas.', 'gestionadmin-wolk'); ?></span>
                        </div>

                        <div class="ga-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $factura->id)); ?>" class="button">
                                <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                            </a>
                            <button type="submit" class="button button-primary" id="ga-btn-facturar" disabled>
                                <?php esc_html_e('Agregar Horas Seleccionadas', 'gestionadmin-wolk'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.ga-facturar-horas-wrap { margin-top: 20px; }
.ga-filter-bar {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
}
.ga-filter-bar form {
    display: flex;
    gap: 10px;
    align-items: center;
}
.ga-facturar-footer {
    margin-top: 20px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ga-tarifa-field {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ga-tarifa-field input {
    width: 100px;
}
.ga-actions {
    display: flex;
    gap: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('ga-select-all');
    var checkboxes = document.querySelectorAll('.ga-hora-checkbox');
    var btnFacturar = document.getElementById('ga-btn-facturar');

    function updateButton() {
        var checked = document.querySelectorAll('.ga-hora-checkbox:checked');
        btnFacturar.disabled = checked.length === 0;
        if (checked.length > 0) {
            btnFacturar.textContent = '<?php esc_html_e('Agregar', 'gestionadmin-wolk'); ?> ' + checked.length + ' <?php esc_html_e('horas seleccionadas', 'gestionadmin-wolk'); ?>';
        } else {
            btnFacturar.textContent = '<?php esc_html_e('Agregar Horas Seleccionadas', 'gestionadmin-wolk'); ?>';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
            updateButton();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateButton);
    });
});
</script>
