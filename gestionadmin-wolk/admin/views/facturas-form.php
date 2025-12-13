<?php
/**
 * Vista parcial: Formulario de Factura
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_new = empty($factura);
$is_editable = $is_new || ($factura->estado === 'BORRADOR');
?>

<div class="ga-factura-form-wrap">
    <?php if (!$is_new): ?>
        <!-- Barra de estado -->
        <div class="ga-factura-status-bar">
            <div class="ga-status-info">
                <strong><?php echo esc_html($factura->numero); ?></strong>
                <span class="ga-factura-estado ga-estado-<?php echo esc_attr(strtolower($factura->estado)); ?>">
                    <?php echo esc_html($factura->estado); ?>
                </span>
            </div>
            <div class="ga-status-actions">
                <?php if ($factura->estado === 'BORRADOR'): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=facturar_horas&id=' . $factura->id)); ?>" class="button">
                        <span class="dashicons dashicons-clock"></span>
                        <?php esc_html_e('Facturar Horas', 'gestionadmin-wolk'); ?>
                    </a>
                    <form method="post" style="display:inline;">
                        <?php wp_nonce_field('ga_factura_action', 'ga_factura_nonce'); ?>
                        <input type="hidden" name="factura_action" value="enviar">
                        <button type="submit" class="button button-primary" onclick="return confirm('<?php esc_attr_e('¿Enviar factura al cliente?', 'gestionadmin-wolk'); ?>')">
                            <span class="dashicons dashicons-email"></span>
                            <?php esc_html_e('Enviar', 'gestionadmin-wolk'); ?>
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (in_array($factura->estado, array('ENVIADA', 'PARCIAL', 'VENCIDA'))): ?>
                    <button type="button" class="button" onclick="document.getElementById('ga-pago-modal').style.display='block'">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php esc_html_e('Registrar Pago', 'gestionadmin-wolk'); ?>
                    </button>
                <?php endif; ?>

                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=preview&id=' . $factura->id)); ?>" class="button" target="_blank">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php esc_html_e('Vista Previa', 'gestionadmin-wolk'); ?>
                </a>

                <?php if (!in_array($factura->estado, array('PAGADA', 'ANULADA'))): ?>
                    <button type="button" class="button" style="color:#dc3545;" onclick="document.getElementById('ga-anular-modal').style.display='block'">
                        <?php esc_html_e('Anular', 'gestionadmin-wolk'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="ga-factura-columns">
        <!-- Columna izquierda: Datos de factura -->
        <div class="ga-factura-main">
            <form method="post" id="ga-factura-form">
                <?php wp_nonce_field('ga_factura_action', 'ga_factura_nonce'); ?>
                <input type="hidden" name="factura_action" value="<?php echo $is_new ? 'crear' : 'actualizar'; ?>">

                <div class="postbox">
                    <h2 class="hndle"><?php esc_html_e('Datos de Facturación', 'gestionadmin-wolk'); ?></h2>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th><label for="cliente_id"><?php esc_html_e('Cliente', 'gestionadmin-wolk'); ?> *</label></th>
                                <td>
                                    <select name="cliente_id" id="cliente_id" required <?php disabled(!$is_editable || !$is_new); ?>>
                                        <option value=""><?php esc_html_e('— Seleccionar —', 'gestionadmin-wolk'); ?></option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo esc_attr($cliente->id); ?>"
                                                <?php if (!$is_new) selected($factura->cliente_id, $cliente->id); ?>>
                                                <?php echo esc_html($cliente->codigo . ' - ' . $cliente->nombre_comercial); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="pais_facturacion"><?php esc_html_e('País de Facturación', 'gestionadmin-wolk'); ?> *</label></th>
                                <td>
                                    <select name="pais_facturacion" id="pais_facturacion" required <?php disabled(!$is_editable || !$is_new); ?>>
                                        <option value=""><?php esc_html_e('— Seleccionar —', 'gestionadmin-wolk'); ?></option>
                                        <?php foreach ($paises as $pais): ?>
                                            <option value="<?php echo esc_attr($pais->codigo_iso); ?>"
                                                data-impuesto="<?php echo esc_attr($pais->impuesto_porcentaje); ?>"
                                                data-impuesto-nombre="<?php echo esc_attr($pais->impuesto_nombre); ?>"
                                                <?php if (!$is_new) selected($factura->pais_facturacion, $pais->codigo_iso); ?>>
                                                <?php echo esc_html($pais->nombre . ' (' . $pais->impuesto_nombre . ' ' . $pais->impuesto_porcentaje . '%)'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="fecha_emision"><?php esc_html_e('Fecha de Emisión', 'gestionadmin-wolk'); ?></label></th>
                                <td>
                                    <input type="date" name="fecha_emision" id="fecha_emision"
                                        value="<?php echo esc_attr($is_new ? date('Y-m-d') : $factura->fecha_emision); ?>"
                                        <?php disabled(!$is_editable); ?>>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="dias_credito"><?php esc_html_e('Días de Crédito', 'gestionadmin-wolk'); ?></label></th>
                                <td>
                                    <input type="number" name="dias_credito" id="dias_credito" min="0" max="365"
                                        value="<?php echo esc_attr($is_new ? 30 : $factura->dias_credito); ?>"
                                        <?php disabled(!$is_editable); ?>>
                                    <p class="description"><?php esc_html_e('La fecha de vencimiento se calculará automáticamente.', 'gestionadmin-wolk'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="concepto_general"><?php esc_html_e('Concepto General', 'gestionadmin-wolk'); ?></label></th>
                                <td>
                                    <textarea name="concepto_general" id="concepto_general" rows="3" class="large-text"
                                        <?php disabled(!$is_editable); ?>><?php echo $is_new ? '' : esc_textarea($factura->concepto_general); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="notas"><?php esc_html_e('Notas (visibles)', 'gestionadmin-wolk'); ?></label></th>
                                <td>
                                    <textarea name="notas" id="notas" rows="2" class="large-text"
                                        <?php disabled(!$is_editable); ?>><?php echo $is_new ? '' : esc_textarea($factura->notas); ?></textarea>
                                </td>
                            </tr>
                        </table>

                        <?php if ($is_editable): ?>
                            <p class="submit">
                                <button type="submit" class="button button-primary">
                                    <?php echo $is_new ? esc_html__('Crear Factura', 'gestionadmin-wolk') : esc_html__('Guardar Cambios', 'gestionadmin-wolk'); ?>
                                </button>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <?php if (!$is_new): ?>
                <!-- Líneas de detalle -->
                <div class="postbox">
                    <h2 class="hndle"><?php esc_html_e('Líneas de Factura', 'gestionadmin-wolk'); ?></h2>
                    <div class="inside">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?></th>
                                    <th style="width:80px;"><?php esc_html_e('Cant.', 'gestionadmin-wolk'); ?></th>
                                    <th style="width:100px;"><?php esc_html_e('Precio', 'gestionadmin-wolk'); ?></th>
                                    <th style="width:100px;"><?php esc_html_e('Subtotal', 'gestionadmin-wolk'); ?></th>
                                    <?php if ($is_editable): ?>
                                        <th style="width:50px;"></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($factura->detalle)): ?>
                                    <tr>
                                        <td colspan="5"><?php esc_html_e('No hay líneas. Agregue conceptos o facture horas.', 'gestionadmin-wolk'); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($factura->detalle as $linea): ?>
                                        <tr>
                                            <td>
                                                <?php if ($linea->tipo === 'HORA'): ?>
                                                    <span class="dashicons dashicons-clock" style="color:#666;"></span>
                                                <?php endif; ?>
                                                <?php echo esc_html($linea->descripcion); ?>
                                            </td>
                                            <td><?php echo esc_html(number_format($linea->cantidad, 2)); ?> <?php echo esc_html($linea->unidad); ?></td>
                                            <td><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($linea->precio_unitario, 2)); ?></td>
                                            <td><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($linea->subtotal, 2)); ?></td>
                                            <?php if ($is_editable): ?>
                                                <td>
                                                    <form method="post" style="display:inline;">
                                                        <?php wp_nonce_field('ga_factura_action', 'ga_factura_nonce'); ?>
                                                        <input type="hidden" name="factura_action" value="eliminar_linea">
                                                        <input type="hidden" name="linea_id" value="<?php echo esc_attr($linea->id); ?>">
                                                        <button type="submit" class="button-link" style="color:#dc3545;" onclick="return confirm('<?php esc_attr_e('¿Eliminar esta línea?', 'gestionadmin-wolk'); ?>')">
                                                            <span class="dashicons dashicons-trash"></span>
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <?php if ($is_editable): ?>
                            <!-- Formulario para agregar línea -->
                            <h4 style="margin-top:20px;"><?php esc_html_e('Agregar Concepto', 'gestionadmin-wolk'); ?></h4>
                            <form method="post" class="ga-add-line-form">
                                <?php wp_nonce_field('ga_factura_action', 'ga_factura_nonce'); ?>
                                <input type="hidden" name="factura_action" value="agregar_linea">

                                <div class="ga-line-fields">
                                    <div class="ga-field">
                                        <label><?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?></label>
                                        <textarea name="descripcion" required rows="2"></textarea>
                                    </div>
                                    <div class="ga-field ga-field-small">
                                        <label><?php esc_html_e('Cantidad', 'gestionadmin-wolk'); ?></label>
                                        <input type="number" name="cantidad" value="1" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="ga-field ga-field-small">
                                        <label><?php esc_html_e('Unidad', 'gestionadmin-wolk'); ?></label>
                                        <select name="unidad">
                                            <option value="UNIDAD"><?php esc_html_e('Unidad', 'gestionadmin-wolk'); ?></option>
                                            <option value="HORA"><?php esc_html_e('Hora', 'gestionadmin-wolk'); ?></option>
                                            <option value="MES"><?php esc_html_e('Mes', 'gestionadmin-wolk'); ?></option>
                                        </select>
                                    </div>
                                    <div class="ga-field ga-field-small">
                                        <label><?php esc_html_e('Precio Unitario', 'gestionadmin-wolk'); ?></label>
                                        <input type="number" name="precio_unitario" value="0" step="0.01" min="0" required>
                                    </div>
                                    <div class="ga-field ga-field-small">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="button button-primary">
                                            <?php esc_html_e('Agregar', 'gestionadmin-wolk'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna derecha: Resumen -->
        <?php if (!$is_new): ?>
            <div class="ga-factura-sidebar">
                <div class="postbox">
                    <h2 class="hndle"><?php esc_html_e('Resumen', 'gestionadmin-wolk'); ?></h2>
                    <div class="inside">
                        <table class="ga-summary-table">
                            <tr>
                                <td><?php esc_html_e('Subtotal:', 'gestionadmin-wolk'); ?></td>
                                <td class="ga-amount"><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->subtotal, 2)); ?></td>
                            </tr>
                            <?php if ($factura->descuento_monto > 0): ?>
                                <tr>
                                    <td><?php esc_html_e('Descuento:', 'gestionadmin-wolk'); ?></td>
                                    <td class="ga-amount">-<?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->descuento_monto, 2)); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($factura->impuesto_monto > 0): ?>
                                <tr>
                                    <td><?php echo esc_html($factura->impuesto_nombre); ?> (<?php echo esc_html($factura->impuesto_porcentaje); ?>%):</td>
                                    <td class="ga-amount"><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->impuesto_monto, 2)); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="ga-total-row">
                                <td><strong><?php esc_html_e('Total:', 'gestionadmin-wolk'); ?></strong></td>
                                <td class="ga-amount"><strong><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->total, 2)); ?></strong></td>
                            </tr>
                            <?php if ($factura->retencion_monto > 0): ?>
                                <tr>
                                    <td><?php esc_html_e('Retención:', 'gestionadmin-wolk'); ?> (<?php echo esc_html($factura->retencion_porcentaje); ?>%)</td>
                                    <td class="ga-amount">-<?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->retencion_monto, 2)); ?></td>
                                </tr>
                                <tr class="ga-total-row">
                                    <td><strong><?php esc_html_e('Total a Pagar:', 'gestionadmin-wolk'); ?></strong></td>
                                    <td class="ga-amount"><strong><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->total_a_pagar, 2)); ?></strong></td>
                                </tr>
                            <?php endif; ?>
                        </table>

                        <hr>

                        <table class="ga-summary-table">
                            <tr>
                                <td><?php esc_html_e('Pagado:', 'gestionadmin-wolk'); ?></td>
                                <td class="ga-amount" style="color:#28a745;"><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->monto_pagado, 2)); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Saldo:', 'gestionadmin-wolk'); ?></td>
                                <td class="ga-amount" style="color:<?php echo $factura->saldo_pendiente > 0 ? '#dc3545' : '#28a745'; ?>">
                                    <?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->saldo_pendiente, 2)); ?>
                                </td>
                            </tr>
                        </table>

                        <?php if ($factura->utilidad_neta != 0): ?>
                            <hr>
                            <table class="ga-summary-table">
                                <tr>
                                    <td><?php esc_html_e('Costo:', 'gestionadmin-wolk'); ?></td>
                                    <td class="ga-amount"><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->costo_horas, 2)); ?></td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Utilidad:', 'gestionadmin-wolk'); ?></td>
                                    <td class="ga-amount"><?php echo esc_html($factura->moneda); ?> <?php echo esc_html(number_format($factura->utilidad_neta, 2)); ?> (<?php echo esc_html(number_format($factura->margen_porcentaje, 1)); ?>%)</td>
                                </tr>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="postbox">
                    <h2 class="hndle"><?php esc_html_e('Información', 'gestionadmin-wolk'); ?></h2>
                    <div class="inside">
                        <p><strong><?php esc_html_e('Cliente:', 'gestionadmin-wolk'); ?></strong><br><?php echo esc_html($factura->cliente_nombre); ?></p>
                        <p><strong><?php esc_html_e('Documento:', 'gestionadmin-wolk'); ?></strong><br><?php echo esc_html($factura->cliente_documento); ?></p>
                        <p><strong><?php esc_html_e('Vencimiento:', 'gestionadmin-wolk'); ?></strong><br><?php echo esc_html($factura->fecha_vencimiento); ?></p>
                        <?php if ($factura->cotizacion_origen_id): ?>
                            <p><strong><?php esc_html_e('Cotización:', 'gestionadmin-wolk'); ?></strong><br>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones&action=edit&id=' . $factura->cotizacion_origen_id)); ?>">
                                    <?php esc_html_e('Ver cotización origen', 'gestionadmin-wolk'); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!$is_new): ?>
    <!-- Modal: Registrar Pago -->
    <div id="ga-pago-modal" class="ga-modal" style="display:none;">
        <div class="ga-modal-content">
            <span class="ga-modal-close" onclick="document.getElementById('ga-pago-modal').style.display='none'">&times;</span>
            <h3><?php esc_html_e('Registrar Pago', 'gestionadmin-wolk'); ?></h3>
            <form method="post">
                <?php wp_nonce_field('ga_factura_action', 'ga_factura_nonce'); ?>
                <input type="hidden" name="factura_action" value="registrar_pago">
                <p>
                    <label><?php esc_html_e('Monto del Pago', 'gestionadmin-wolk'); ?></label>
                    <input type="number" name="monto_pago" step="0.01" min="0.01" max="<?php echo esc_attr($factura->saldo_pendiente); ?>" value="<?php echo esc_attr($factura->saldo_pendiente); ?>" required>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Registrar Pago', 'gestionadmin-wolk'); ?></button>
                </p>
            </form>
        </div>
    </div>

    <!-- Modal: Anular Factura -->
    <div id="ga-anular-modal" class="ga-modal" style="display:none;">
        <div class="ga-modal-content">
            <span class="ga-modal-close" onclick="document.getElementById('ga-anular-modal').style.display='none'">&times;</span>
            <h3><?php esc_html_e('Anular Factura', 'gestionadmin-wolk'); ?></h3>
            <form method="post">
                <?php wp_nonce_field('ga_factura_action', 'ga_factura_nonce'); ?>
                <input type="hidden" name="factura_action" value="anular">
                <p>
                    <label><?php esc_html_e('Motivo de Anulación', 'gestionadmin-wolk'); ?></label>
                    <textarea name="motivo_anulacion" rows="3" class="large-text" required></textarea>
                </p>
                <p>
                    <button type="submit" class="button" style="color:#fff;background:#dc3545;border-color:#dc3545;">
                        <?php esc_html_e('Confirmar Anulación', 'gestionadmin-wolk'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>
<?php endif; ?>

<style>
.ga-factura-form-wrap { margin-top: 20px; }
.ga-factura-status-bar {
    background: #fff;
    border: 1px solid #ddd;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ga-status-info { font-size: 16px; }
.ga-status-actions { display: flex; gap: 10px; }
.ga-status-actions .dashicons { vertical-align: middle; margin-right: 3px; }

.ga-factura-columns {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
}
@media (max-width: 1200px) {
    .ga-factura-columns { grid-template-columns: 1fr; }
}

.ga-add-line-form { margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px; }
.ga-line-fields { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
.ga-field { flex: 2; }
.ga-field-small { flex: 1; min-width: 100px; }
.ga-field label { display: block; margin-bottom: 5px; font-weight: 500; }
.ga-field input, .ga-field textarea, .ga-field select { width: 100%; }

.ga-summary-table { width: 100%; }
.ga-summary-table td { padding: 8px 0; }
.ga-summary-table .ga-amount { text-align: right; font-family: monospace; }
.ga-summary-table .ga-total-row { border-top: 2px solid #333; }

.ga-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ga-modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    position: relative;
}
.ga-modal-close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}
</style>
