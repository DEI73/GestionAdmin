<?php
/**
 * Vista Parcial: Formulario de Cotización
 *
 * Formulario para crear/editar cotizaciones con gestión de líneas de detalle.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Determinar si es edición o creación
$es_edicion = isset($cotizacion) && $cotizacion;
$estado_actual = $es_edicion ? $cotizacion->estado : 'BORRADOR';

// Datos del formulario (valores por defecto o existentes)
$form_data = array(
    'cliente_id'      => $es_edicion ? $cotizacion->cliente_id : '',
    'titulo'          => $es_edicion ? $cotizacion->titulo : '',
    'fecha'           => $es_edicion ? $cotizacion->fecha : date('Y-m-d'),
    'fecha_validez'   => $es_edicion ? $cotizacion->fecha_validez : date('Y-m-d', strtotime('+30 days')),
    'notas'           => $es_edicion ? $cotizacion->notas : '',
    'terminos'        => $es_edicion ? $cotizacion->terminos : '',
    'descuento_tipo'  => $es_edicion ? $cotizacion->descuento_tipo : 'PORCENTAJE',
    'descuento_valor' => $es_edicion ? $cotizacion->descuento_valor : 0,
);

// Obtener líneas de detalle si es edición
$lineas = array();
if ($es_edicion && isset($cotizacion->lineas)) {
    $lineas = $cotizacion->lineas;
}
?>

<div class="ga-cotizacion-form-wrap">
    <?php if ($es_edicion): ?>
        <!-- Barra de Estado -->
        <div class="ga-status-bar ga-status-<?php echo esc_attr(strtolower($estado_actual)); ?>">
            <div class="ga-status-info">
                <span class="ga-status-label"><?php esc_html_e('Cotización:', 'gestionadmin-wolk'); ?></span>
                <span class="ga-status-numero"><?php echo esc_html($cotizacion->numero); ?></span>
                <span class="ga-cotizacion-estado ga-estado-<?php echo esc_attr(strtolower($estado_actual)); ?>">
                    <?php
                    $estados_labels = array(
                        'BORRADOR'  => __('Borrador', 'gestionadmin-wolk'),
                        'ENVIADA'   => __('Enviada', 'gestionadmin-wolk'),
                        'APROBADA'  => __('Aprobada', 'gestionadmin-wolk'),
                        'RECHAZADA' => __('Rechazada', 'gestionadmin-wolk'),
                        'FACTURADA' => __('Facturada', 'gestionadmin-wolk'),
                        'VENCIDA'   => __('Vencida', 'gestionadmin-wolk'),
                        'CANCELADA' => __('Cancelada', 'gestionadmin-wolk'),
                    );
                    echo esc_html($estados_labels[$estado_actual] ?? $estado_actual);
                    ?>
                </span>
            </div>
            <div class="ga-status-actions">
                <?php if ($estado_actual === 'BORRADOR'): ?>
                    <!-- Enviar al cliente -->
                    <form method="post" style="display:inline;">
                        <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                        <input type="hidden" name="cotizacion_action" value="enviar">
                        <button type="submit" class="button button-primary"
                                onclick="return confirm('<?php esc_attr_e('¿Enviar cotización al cliente?', 'gestionadmin-wolk'); ?>');">
                            <span class="dashicons dashicons-email-alt"></span>
                            <?php esc_html_e('Enviar al Cliente', 'gestionadmin-wolk'); ?>
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($estado_actual === 'ENVIADA'): ?>
                    <!-- Aprobar -->
                    <form method="post" style="display:inline;">
                        <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                        <input type="hidden" name="cotizacion_action" value="aprobar">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Marcar Aprobada', 'gestionadmin-wolk'); ?>
                        </button>
                    </form>
                    <!-- Rechazar -->
                    <button type="button" class="button" id="ga-btn-rechazar">
                        <span class="dashicons dashicons-no-alt"></span>
                        <?php esc_html_e('Marcar Rechazada', 'gestionadmin-wolk'); ?>
                    </button>
                <?php endif; ?>

                <?php if ($estado_actual === 'APROBADA'): ?>
                    <!-- Convertir a Factura -->
                    <button type="button" class="button button-primary" id="ga-btn-convertir-factura">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php esc_html_e('Convertir a Factura', 'gestionadmin-wolk'); ?>
                    </button>
                <?php endif; ?>

                <?php if (!in_array($estado_actual, array('FACTURADA', 'CANCELADA'))): ?>
                    <!-- Cancelar -->
                    <form method="post" style="display:inline;">
                        <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                        <input type="hidden" name="cotizacion_action" value="cancelar">
                        <button type="submit" class="button"
                                onclick="return confirm('<?php esc_attr_e('¿Está seguro de cancelar esta cotización?', 'gestionadmin-wolk'); ?>');">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php esc_html_e('Cancelar Cotización', 'gestionadmin-wolk'); ?>
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Vista Previa / Imprimir -->
                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones&action=preview&id=' . $cotizacion->id)); ?>"
                   class="button" target="_blank">
                    <span class="dashicons dashicons-printer"></span>
                    <?php esc_html_e('Imprimir', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="ga-form-columns">
        <!-- Columna Principal -->
        <div class="ga-form-main">
            <form method="post" id="ga-cotizacion-form">
                <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                <input type="hidden" name="cotizacion_action" value="<?php echo $es_edicion ? 'actualizar' : 'crear'; ?>">

                <!-- Datos Generales -->
                <div class="postbox">
                    <h2 class="hndle">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e('Datos Generales', 'gestionadmin-wolk'); ?>
                    </h2>
                    <div class="inside">
                        <div class="ga-form-row">
                            <div class="ga-form-group ga-col-6">
                                <label for="cliente_id"><?php esc_html_e('Cliente:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                                <select name="cliente_id" id="cliente_id" required <?php echo ($es_edicion && $estado_actual !== 'BORRADOR') ? 'disabled' : ''; ?>>
                                    <option value=""><?php esc_html_e('— Seleccione cliente —', 'gestionadmin-wolk'); ?></option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo esc_attr($cliente->id); ?>"
                                                <?php selected($form_data['cliente_id'], $cliente->id); ?>>
                                            <?php echo esc_html($cliente->codigo . ' - ' . $cliente->nombre_comercial); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="ga-form-group ga-col-6">
                                <label for="titulo"><?php esc_html_e('Título/Referencia:', 'gestionadmin-wolk'); ?></label>
                                <input type="text" name="titulo" id="titulo"
                                       value="<?php echo esc_attr($form_data['titulo']); ?>"
                                       placeholder="<?php esc_attr_e('Ej: Desarrollo sitio web, Consultoría...', 'gestionadmin-wolk'); ?>">
                            </div>
                        </div>

                        <div class="ga-form-row">
                            <div class="ga-form-group ga-col-6">
                                <label for="fecha"><?php esc_html_e('Fecha Cotización:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                                <input type="date" name="fecha" id="fecha"
                                       value="<?php echo esc_attr($form_data['fecha']); ?>" required>
                            </div>
                            <div class="ga-form-group ga-col-6">
                                <label for="fecha_validez"><?php esc_html_e('Válida Hasta:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                                <input type="date" name="fecha_validez" id="fecha_validez"
                                       value="<?php echo esc_attr($form_data['fecha_validez']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Líneas de Detalle -->
                <?php if ($es_edicion): ?>
                    <div class="postbox">
                        <h2 class="hndle">
                            <span class="dashicons dashicons-list-view"></span>
                            <?php esc_html_e('Líneas de Detalle', 'gestionadmin-wolk'); ?>
                        </h2>
                        <div class="inside">
                            <table class="wp-list-table widefat fixed striped" id="ga-lineas-table">
                                <thead>
                                    <tr>
                                        <th style="width:50%;"><?php esc_html_e('Descripción', 'gestionadmin-wolk'); ?></th>
                                        <th style="width:10%;"><?php esc_html_e('Cantidad', 'gestionadmin-wolk'); ?></th>
                                        <th style="width:15%;"><?php esc_html_e('Precio Unit.', 'gestionadmin-wolk'); ?></th>
                                        <th style="width:15%;"><?php esc_html_e('Total', 'gestionadmin-wolk'); ?></th>
                                        <th style="width:10%;"><?php esc_html_e('Acciones', 'gestionadmin-wolk'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($lineas)): ?>
                                        <tr class="ga-empty-row">
                                            <td colspan="5"><?php esc_html_e('No hay líneas agregadas.', 'gestionadmin-wolk'); ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($lineas as $linea): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo esc_html($linea->descripcion); ?></strong>
                                                    <?php if ($linea->codigo_item): ?>
                                                        <br><small>Código: <?php echo esc_html($linea->codigo_item); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo esc_html(number_format($linea->cantidad, 2) . ' ' . $linea->unidad); ?></td>
                                                <td>$<?php echo esc_html(number_format($linea->precio_unitario, 2)); ?></td>
                                                <td><strong>$<?php echo esc_html(number_format($linea->total_linea, 2)); ?></strong></td>
                                                <td>
                                                    <?php if ($estado_actual === 'BORRADOR'): ?>
                                                        <form method="post" style="display:inline;">
                                                            <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                                                            <input type="hidden" name="cotizacion_action" value="eliminar_linea">
                                                            <input type="hidden" name="linea_id" value="<?php echo esc_attr($linea->id); ?>">
                                                            <button type="submit" class="button button-small"
                                                                    onclick="return confirm('<?php esc_attr_e('¿Eliminar esta línea?', 'gestionadmin-wolk'); ?>');">
                                                                <span class="dashicons dashicons-trash"></span>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <?php if ($estado_actual === 'BORRADOR'): ?>
                                <!-- Agregar línea -->
                                <div class="ga-add-line-form">
                                    <h4><?php esc_html_e('Agregar Línea', 'gestionadmin-wolk'); ?></h4>
                                    <div class="ga-form-row">
                                        <div class="ga-form-group ga-col-5">
                                            <input type="text" name="linea_descripcion" id="linea_descripcion"
                                                   placeholder="<?php esc_attr_e('Descripción del servicio/producto', 'gestionadmin-wolk'); ?>">
                                        </div>
                                        <div class="ga-form-group ga-col-2">
                                            <input type="number" name="linea_cantidad" id="linea_cantidad"
                                                   placeholder="<?php esc_attr_e('Cant.', 'gestionadmin-wolk'); ?>"
                                                   step="0.01" min="0.01" value="1">
                                        </div>
                                        <div class="ga-form-group ga-col-2">
                                            <input type="number" name="linea_precio" id="linea_precio"
                                                   placeholder="<?php esc_attr_e('Precio', 'gestionadmin-wolk'); ?>"
                                                   step="0.01" min="0">
                                        </div>
                                        <div class="ga-form-group ga-col-3">
                                            <button type="button" class="button button-primary" id="ga-btn-agregar-linea">
                                                <span class="dashicons dashicons-plus-alt"></span>
                                                <?php esc_html_e('Agregar', 'gestionadmin-wolk'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Notas y Términos -->
                <div class="postbox">
                    <h2 class="hndle">
                        <span class="dashicons dashicons-editor-paragraph"></span>
                        <?php esc_html_e('Notas y Términos', 'gestionadmin-wolk'); ?>
                    </h2>
                    <div class="inside">
                        <div class="ga-form-row">
                            <div class="ga-form-group ga-col-6">
                                <label for="notas"><?php esc_html_e('Notas para el Cliente:', 'gestionadmin-wolk'); ?></label>
                                <textarea name="notas" id="notas" rows="4"
                                          placeholder="<?php esc_attr_e('Observaciones visibles en la cotización...', 'gestionadmin-wolk'); ?>"><?php echo esc_textarea($form_data['notas']); ?></textarea>
                            </div>
                            <div class="ga-form-group ga-col-6">
                                <label for="terminos"><?php esc_html_e('Términos y Condiciones:', 'gestionadmin-wolk'); ?></label>
                                <textarea name="terminos" id="terminos" rows="4"
                                          placeholder="<?php esc_attr_e('Condiciones de pago, garantías, etc...', 'gestionadmin-wolk'); ?>"><?php echo esc_textarea($form_data['terminos']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Guardar -->
                <div class="ga-form-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-cotizaciones')); ?>" class="button button-large">
                        <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                    </a>
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span>
                        <?php echo $es_edicion ? esc_html__('Guardar Cambios', 'gestionadmin-wolk') : esc_html__('Crear Cotización', 'gestionadmin-wolk'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar con Totales -->
        <?php if ($es_edicion): ?>
            <div class="ga-form-sidebar">
                <!-- Resumen de Totales -->
                <div class="postbox ga-totales-box">
                    <h2 class="hndle">
                        <span class="dashicons dashicons-calculator"></span>
                        <?php esc_html_e('Resumen', 'gestionadmin-wolk'); ?>
                    </h2>
                    <div class="inside">
                        <table class="ga-totales-table">
                            <tr>
                                <td><?php esc_html_e('Subtotal:', 'gestionadmin-wolk'); ?></td>
                                <td class="ga-total-value">$<?php echo esc_html(number_format($cotizacion->subtotal, 2)); ?></td>
                            </tr>
                            <?php if ($cotizacion->descuento > 0): ?>
                                <tr>
                                    <td><?php esc_html_e('Descuento:', 'gestionadmin-wolk'); ?></td>
                                    <td class="ga-total-value ga-negative">-$<?php echo esc_html(number_format($cotizacion->descuento, 2)); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="ga-total-final">
                                <td><strong><?php esc_html_e('TOTAL:', 'gestionadmin-wolk'); ?></strong></td>
                                <td class="ga-total-value"><strong>$<?php echo esc_html(number_format($cotizacion->total, 2)); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Descuento -->
                <?php if ($estado_actual === 'BORRADOR'): ?>
                    <div class="postbox">
                        <h2 class="hndle">
                            <span class="dashicons dashicons-tag"></span>
                            <?php esc_html_e('Descuento', 'gestionadmin-wolk'); ?>
                        </h2>
                        <div class="inside">
                            <form method="post" id="ga-descuento-form">
                                <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                                <input type="hidden" name="cotizacion_action" value="actualizar">

                                <div class="ga-form-group">
                                    <label><?php esc_html_e('Tipo:', 'gestionadmin-wolk'); ?></label>
                                    <select name="descuento_tipo">
                                        <option value="PORCENTAJE" <?php selected($form_data['descuento_tipo'], 'PORCENTAJE'); ?>>
                                            <?php esc_html_e('Porcentaje (%)', 'gestionadmin-wolk'); ?>
                                        </option>
                                        <option value="MONTO" <?php selected($form_data['descuento_tipo'], 'MONTO'); ?>>
                                            <?php esc_html_e('Monto Fijo ($)', 'gestionadmin-wolk'); ?>
                                        </option>
                                    </select>
                                </div>
                                <div class="ga-form-group">
                                    <label><?php esc_html_e('Valor:', 'gestionadmin-wolk'); ?></label>
                                    <input type="number" name="descuento_valor" step="0.01" min="0"
                                           value="<?php echo esc_attr($form_data['descuento_valor']); ?>">
                                </div>
                                <button type="submit" class="button">
                                    <?php esc_html_e('Aplicar Descuento', 'gestionadmin-wolk'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Información Adicional -->
                <div class="postbox">
                    <h2 class="hndle">
                        <span class="dashicons dashicons-info-outline"></span>
                        <?php esc_html_e('Información', 'gestionadmin-wolk'); ?>
                    </h2>
                    <div class="inside ga-info-list">
                        <p><strong><?php esc_html_e('Creada:', 'gestionadmin-wolk'); ?></strong><br>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($cotizacion->created_at))); ?>
                        </p>
                        <?php if ($cotizacion->fecha_envio): ?>
                            <p><strong><?php esc_html_e('Enviada:', 'gestionadmin-wolk'); ?></strong><br>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($cotizacion->fecha_envio))); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($cotizacion->fecha_respuesta): ?>
                            <p><strong><?php esc_html_e('Respuesta:', 'gestionadmin-wolk'); ?></strong><br>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($cotizacion->fecha_respuesta))); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($cotizacion->factura_id): ?>
                            <p><strong><?php esc_html_e('Factura:', 'gestionadmin-wolk'); ?></strong><br>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=gestionadmin-facturas&action=edit&id=' . $cotizacion->factura_id)); ?>">
                                    <?php esc_html_e('Ver factura generada', 'gestionadmin-wolk'); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        <?php if ($cotizacion->motivo_rechazo): ?>
                            <p><strong><?php esc_html_e('Motivo Rechazo:', 'gestionadmin-wolk'); ?></strong><br>
                                <span class="ga-motivo-rechazo"><?php echo esc_html($cotizacion->motivo_rechazo); ?></span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Rechazar Cotización -->
<?php if ($es_edicion && $estado_actual === 'ENVIADA'): ?>
    <div id="ga-modal-rechazar" class="ga-modal" style="display:none;">
        <div class="ga-modal-content">
            <div class="ga-modal-header">
                <h2><?php esc_html_e('Marcar Cotización como Rechazada', 'gestionadmin-wolk'); ?></h2>
                <button type="button" class="ga-modal-close">&times;</button>
            </div>
            <form method="post">
                <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                <input type="hidden" name="cotizacion_action" value="rechazar">

                <div class="ga-modal-body">
                    <div class="ga-form-group">
                        <label for="motivo_rechazo"><?php esc_html_e('Motivo del Rechazo:', 'gestionadmin-wolk'); ?></label>
                        <textarea name="motivo_rechazo" id="motivo_rechazo" rows="4"
                                  placeholder="<?php esc_attr_e('Indique el motivo por el cual el cliente rechazó la cotización...', 'gestionadmin-wolk'); ?>"></textarea>
                    </div>
                </div>

                <div class="ga-modal-footer">
                    <button type="button" class="button ga-modal-close"><?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?></button>
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-no-alt"></span>
                        <?php esc_html_e('Marcar como Rechazada', 'gestionadmin-wolk'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Convertir a Factura -->
<?php if ($es_edicion && $estado_actual === 'APROBADA'): ?>
    <div id="ga-modal-convertir" class="ga-modal" style="display:none;">
        <div class="ga-modal-content">
            <div class="ga-modal-header">
                <h2><?php esc_html_e('Convertir a Factura', 'gestionadmin-wolk'); ?></h2>
                <button type="button" class="ga-modal-close">&times;</button>
            </div>
            <form method="post">
                <?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce'); ?>
                <input type="hidden" name="cotizacion_action" value="convertir_factura">

                <div class="ga-modal-body">
                    <p><?php esc_html_e('Se creará una nueva factura con todos los datos de esta cotización.', 'gestionadmin-wolk'); ?></p>

                    <div class="ga-form-group">
                        <label for="pais_facturacion"><?php esc_html_e('País de Facturación:', 'gestionadmin-wolk'); ?> <span class="required">*</span></label>
                        <select name="pais_facturacion" id="pais_facturacion" required>
                            <option value=""><?php esc_html_e('— Seleccione país —', 'gestionadmin-wolk'); ?></option>
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?php echo esc_attr($pais->pais_iso); ?>">
                                    <?php echo esc_html($pais->nombre . ' (' . $pais->moneda . ' - IVA: ' . $pais->iva_porcentaje . '%)'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Esto determinará la numeración y configuración de impuestos.', 'gestionadmin-wolk'); ?></p>
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
<?php endif; ?>

<style>
.ga-cotizacion-form-wrap { margin-top: 20px; }

/* Barra de estado */
.ga-status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}
.ga-status-bar.ga-status-borrador { border-left: 4px solid #6c757d; }
.ga-status-bar.ga-status-enviada { border-left: 4px solid #007bff; }
.ga-status-bar.ga-status-aprobada { border-left: 4px solid #28a745; }
.ga-status-bar.ga-status-rechazada { border-left: 4px solid #dc3545; }
.ga-status-bar.ga-status-facturada { border-left: 4px solid #17a2b8; }
.ga-status-bar.ga-status-vencida { border-left: 4px solid #ffc107; }
.ga-status-bar.ga-status-cancelada { border-left: 4px solid #6c757d; }

.ga-status-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.ga-status-numero {
    font-size: 18px;
    font-weight: 600;
}
.ga-status-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.ga-status-actions .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

/* Layout columnas */
.ga-form-columns {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
}
@media (max-width: 1200px) {
    .ga-form-columns {
        grid-template-columns: 1fr;
    }
}

/* Formulario */
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
.ga-form-group input[type="number"],
.ga-form-group input[type="date"],
.ga-form-group select,
.ga-form-group textarea {
    width: 100%;
}
.ga-col-2 { flex: 0 0 calc(16.66% - 17px); }
.ga-col-3 { flex: 0 0 calc(25% - 15px); }
.ga-col-5 { flex: 0 0 calc(41.66% - 12px); }
.ga-col-6 { flex: 0 0 calc(50% - 10px); }

/* Tabla líneas */
#ga-lineas-table .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Agregar línea */
.ga-add-line-form {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
}
.ga-add-line-form h4 {
    margin: 0 0 15px 0;
}

/* Sidebar */
.ga-totales-box {
    background: #fff;
}
.ga-totales-table {
    width: 100%;
}
.ga-totales-table td {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.ga-totales-table tr:last-child td {
    border-bottom: none;
}
.ga-total-value {
    text-align: right;
    font-weight: 500;
}
.ga-total-value.ga-negative {
    color: #dc3545;
}
.ga-total-final {
    background: #f5f5f5;
}
.ga-total-final td {
    padding: 12px 8px;
    font-size: 16px;
}
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
.ga-motivo-rechazo {
    color: #dc3545;
    font-style: italic;
}

/* Acciones */
.ga-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
.ga-form-actions .dashicons {
    margin-right: 5px;
    vertical-align: middle;
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

.required { color: #dc3545; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal rechazar
    var modalRechazar = document.getElementById('ga-modal-rechazar');
    var btnRechazar = document.getElementById('ga-btn-rechazar');

    if (btnRechazar && modalRechazar) {
        var closeBtns = modalRechazar.querySelectorAll('.ga-modal-close');

        btnRechazar.addEventListener('click', function() {
            modalRechazar.style.display = 'flex';
        });

        closeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                modalRechazar.style.display = 'none';
            });
        });

        modalRechazar.addEventListener('click', function(e) {
            if (e.target === modalRechazar) {
                modalRechazar.style.display = 'none';
            }
        });
    }

    // Modal convertir a factura
    var modalConvertir = document.getElementById('ga-modal-convertir');
    var btnConvertir = document.getElementById('ga-btn-convertir-factura');

    if (btnConvertir && modalConvertir) {
        var closeBtns = modalConvertir.querySelectorAll('.ga-modal-close');

        btnConvertir.addEventListener('click', function() {
            modalConvertir.style.display = 'flex';
        });

        closeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                modalConvertir.style.display = 'none';
            });
        });

        modalConvertir.addEventListener('click', function(e) {
            if (e.target === modalConvertir) {
                modalConvertir.style.display = 'none';
            }
        });
    }

    // Agregar línea via AJAX
    var btnAgregarLinea = document.getElementById('ga-btn-agregar-linea');
    if (btnAgregarLinea) {
        btnAgregarLinea.addEventListener('click', function() {
            var descripcion = document.getElementById('linea_descripcion').value.trim();
            var cantidad = document.getElementById('linea_cantidad').value;
            var precio = document.getElementById('linea_precio').value;

            if (!descripcion) {
                alert('<?php esc_html_e('Ingrese una descripción', 'gestionadmin-wolk'); ?>');
                return;
            }
            if (!cantidad || cantidad <= 0) {
                alert('<?php esc_html_e('Ingrese una cantidad válida', 'gestionadmin-wolk'); ?>');
                return;
            }
            if (!precio || precio < 0) {
                alert('<?php esc_html_e('Ingrese un precio válido', 'gestionadmin-wolk'); ?>');
                return;
            }

            // Crear form y enviar
            var form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<?php wp_nonce_field('ga_cotizacion_action', 'ga_cotizacion_nonce', true, false); ?>' +
                '<input type="hidden" name="cotizacion_action" value="agregar_linea">' +
                '<input type="hidden" name="descripcion" value="' + descripcion + '">' +
                '<input type="hidden" name="cantidad" value="' + cantidad + '">' +
                '<input type="hidden" name="precio_unitario" value="' + precio + '">';
            document.body.appendChild(form);
            form.submit();
        });
    }
});
</script>
