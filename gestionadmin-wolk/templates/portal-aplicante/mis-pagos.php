<?php
/**
 * Template: Portal Aplicante - Mis Pagos
 *
 * Vista completa de comisiones disponibles y solicitudes de cobro
 * del aplicante/proveedor.
 *
 * FUNCIONALIDADES:
 * - Ver comisiones disponibles (generadas de facturas pagadas)
 * - Crear solicitud de cobro seleccionando comisiones
 * - Ver historial de solicitudes de cobro
 * - Ver estadísticas de ganancias
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar autenticación
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// Obtener aplicante actual
$aplicante = GA_Public::get_current_aplicante();

if (!$aplicante) {
    include GA_PLUGIN_DIR . 'templates/portal-aplicante/no-aplicante.php';
    exit;
}

// Cargar módulos
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-comisiones.php';
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-solicitudes-cobro.php';

$comisiones_module = GA_Comisiones::get_instance();
$solicitudes_module = GA_Solicitudes_Cobro::get_instance();

// Procesar formulario de nueva solicitud
$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_crear_solicitud'])) {
    if (!wp_verify_nonce($_POST['ga_nonce'], 'ga_crear_solicitud')) {
        $mensaje_error = __('Error de seguridad. Por favor, recarga la página.', 'gestionadmin-wolk');
    } else {
        // Preparar datos de la solicitud
        $comisiones_seleccionadas = isset($_POST['comisiones']) ? array_map('absint', $_POST['comisiones']) : array();

        if (empty($comisiones_seleccionadas)) {
            $mensaje_error = __('Debes seleccionar al menos una comisión.', 'gestionadmin-wolk');
        } elseif (empty($_POST['metodo_pago'])) {
            $mensaje_error = __('Debes seleccionar un método de pago.', 'gestionadmin-wolk');
        } else {
            // Construir array de comisiones
            $comisiones_data = array();
            foreach ($comisiones_seleccionadas as $comision_id) {
                $comisiones_data[] = array('id' => $comision_id);
            }

            // Preparar datos de pago según método
            $datos_pago = array();
            $metodo_pago = sanitize_text_field($_POST['metodo_pago']);

            switch ($metodo_pago) {
                case 'BINANCE':
                    $datos_pago['wallet'] = sanitize_text_field($_POST['binance_wallet'] ?? '');
                    $datos_pago['email'] = sanitize_email($_POST['binance_email'] ?? '');
                    break;
                case 'WISE':
                    $datos_pago['email'] = sanitize_email($_POST['wise_email'] ?? '');
                    break;
                case 'PAYPAL':
                    $datos_pago['email'] = sanitize_email($_POST['paypal_email'] ?? '');
                    break;
                case 'TRANSFERENCIA_LOCAL':
                    $datos_pago['banco'] = sanitize_text_field($_POST['banco'] ?? '');
                    $datos_pago['cuenta'] = sanitize_text_field($_POST['cuenta'] ?? '');
                    $datos_pago['tipo_cuenta'] = sanitize_text_field($_POST['tipo_cuenta'] ?? '');
                    $datos_pago['titular'] = sanitize_text_field($_POST['titular'] ?? '');
                    break;
                case 'OTRO':
                    $datos_pago['instrucciones'] = sanitize_textarea_field($_POST['instrucciones_pago'] ?? '');
                    break;
            }

            $result = $solicitudes_module->crear(array(
                'aplicante_id'      => $aplicante->id,
                'comisiones'        => $comisiones_data,
                'metodo_pago'       => $metodo_pago,
                'datos_pago'        => $datos_pago,
                'notas_solicitante' => sanitize_textarea_field($_POST['notas'] ?? ''),
            ));

            if (is_wp_error($result)) {
                $mensaje_error = $result->get_error_message();
            } else {
                $mensaje_exito = __('Solicitud de cobro creada exitosamente. Te notificaremos cuando sea procesada.', 'gestionadmin-wolk');
            }
        }
    }
}

// Determinar vista actual
$vista = isset($_GET['vista']) ? sanitize_text_field($_GET['vista']) : 'disponibles';

// Obtener datos según vista
$comisiones_disponibles = $comisiones_module->get_disponibles($aplicante->id);
$total_disponible = $comisiones_module->get_total_disponible($aplicante->id);
$estadisticas = $comisiones_module->get_estadisticas_aplicante($aplicante->id);
$solicitudes = $solicitudes_module->get_por_aplicante($aplicante->id, array('per_page' => 50));

// Enums
$estados_solicitud = GA_Solicitudes_Cobro::get_estados();
$metodos_pago = GA_Solicitudes_Cobro::get_metodos_pago();

get_header();
?>

<div class="ga-public-container ga-portal-aplicante ga-portal-pagos">
    <div class="ga-container">
        <!-- Header -->
        <div class="ga-portal-header">
            <h1>
                <span class="dashicons dashicons-money-alt"></span>
                <?php esc_html_e('Mis Pagos', 'gestionadmin-wolk'); ?>
            </h1>
            <p><?php esc_html_e('Gestiona tus comisiones y solicita retiros', 'gestionadmin-wolk'); ?></p>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje_exito) : ?>
            <div class="ga-alert ga-alert-success">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php echo esc_html($mensaje_exito); ?>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_error) : ?>
            <div class="ga-alert ga-alert-error">
                <span class="dashicons dashicons-warning"></span>
                <?php echo esc_html($mensaje_error); ?>
            </div>
        <?php endif; ?>

        <!-- Tarjetas de resumen -->
        <div class="ga-stats-row">
            <div class="ga-stat-card ga-stat-disponible">
                <div class="ga-stat-icon">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value">$<?php echo number_format($total_disponible, 2); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Disponible para Retirar', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card ga-stat-solicitado">
                <div class="ga-stat-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value">$<?php echo number_format($estadisticas['solicitado'] ?? 0, 2); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('En Proceso', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card ga-stat-pagado">
                <div class="ga-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value">$<?php echo number_format($estadisticas['pagado'] ?? 0, 2); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Total Pagado', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>

            <div class="ga-stat-card ga-stat-total">
                <div class="ga-stat-icon">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <div class="ga-stat-content">
                    <span class="ga-stat-value">$<?php echo number_format($estadisticas['monto_total'] ?? 0, 2); ?></span>
                    <span class="ga-stat-label"><?php esc_html_e('Total Generado', 'gestionadmin-wolk'); ?></span>
                </div>
            </div>
        </div>

        <!-- Tabs de navegación -->
        <div class="ga-tabs">
            <a href="?vista=disponibles" class="ga-tab <?php echo $vista === 'disponibles' ? 'ga-tab-active' : ''; ?>">
                <span class="dashicons dashicons-money-alt"></span>
                <?php esc_html_e('Disponibles', 'gestionadmin-wolk'); ?>
                <?php if (count($comisiones_disponibles) > 0) : ?>
                    <span class="ga-tab-badge"><?php echo count($comisiones_disponibles); ?></span>
                <?php endif; ?>
            </a>
            <a href="?vista=solicitudes" class="ga-tab <?php echo $vista === 'solicitudes' ? 'ga-tab-active' : ''; ?>">
                <span class="dashicons dashicons-clipboard"></span>
                <?php esc_html_e('Mis Solicitudes', 'gestionadmin-wolk'); ?>
            </a>
            <a href="?vista=nueva" class="ga-tab <?php echo $vista === 'nueva' ? 'ga-tab-active' : ''; ?>">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php esc_html_e('Solicitar Retiro', 'gestionadmin-wolk'); ?>
            </a>
        </div>

        <!-- Contenido según vista -->
        <div class="ga-tab-content">
            <?php if ($vista === 'disponibles') : ?>
                <!-- Vista: Comisiones Disponibles -->
                <div class="ga-card">
                    <div class="ga-card-header">
                        <h3>
                            <span class="dashicons dashicons-money-alt"></span>
                            <?php esc_html_e('Comisiones Disponibles', 'gestionadmin-wolk'); ?>
                        </h3>
                    </div>
                    <div class="ga-card-body">
                        <?php if (empty($comisiones_disponibles)) : ?>
                            <div class="ga-empty-state">
                                <span class="dashicons dashicons-info"></span>
                                <p><?php esc_html_e('No tienes comisiones disponibles en este momento.', 'gestionadmin-wolk'); ?></p>
                                <small><?php esc_html_e('Las comisiones se generan cuando se pagan las facturas de tus trabajos.', 'gestionadmin-wolk'); ?></small>
                            </div>
                        <?php else : ?>
                            <table class="ga-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Orden', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Tipo', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Base', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('% / Fijo', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Comisión', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comisiones_disponibles as $comision) : ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($comision->orden_codigo); ?></strong>
                                                <br>
                                                <small class="ga-text-muted"><?php echo esc_html(wp_trim_words($comision->orden_titulo, 6)); ?></small>
                                            </td>
                                            <td>
                                                <span class="ga-badge ga-badge-outline">
                                                    <?php echo esc_html($comision->tipo_acuerdo ?? '-'); ?>
                                                </span>
                                            </td>
                                            <td>$<?php echo number_format($comision->monto_base, 2); ?></td>
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
                                                <strong class="ga-amount-success">
                                                    $<?php echo number_format($comision->monto_comision, 2); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php echo esc_html(date_i18n('d M Y', strtotime($comision->created_at))); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" style="text-align: right;"><?php esc_html_e('Total Disponible:', 'gestionadmin-wolk'); ?></th>
                                        <th colspan="2" class="ga-amount-success">$<?php echo number_format($total_disponible, 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>

                            <?php if ($total_disponible > 0) : ?>
                                <div class="ga-action-bar">
                                    <a href="?vista=nueva" class="ga-btn ga-btn-primary ga-btn-large">
                                        <span class="dashicons dashicons-plus-alt"></span>
                                        <?php esc_html_e('Solicitar Retiro', 'gestionadmin-wolk'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($vista === 'solicitudes') : ?>
                <!-- Vista: Historial de Solicitudes -->
                <div class="ga-card">
                    <div class="ga-card-header">
                        <h3>
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php esc_html_e('Historial de Solicitudes', 'gestionadmin-wolk'); ?>
                        </h3>
                    </div>
                    <div class="ga-card-body">
                        <?php if (empty($solicitudes)) : ?>
                            <div class="ga-empty-state">
                                <span class="dashicons dashicons-info"></span>
                                <p><?php esc_html_e('No has realizado solicitudes de cobro.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        <?php else : ?>
                            <table class="ga-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Solicitud', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Método', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Monto', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Estado', 'gestionadmin-wolk'); ?></th>
                                        <th><?php esc_html_e('Fecha', 'gestionadmin-wolk'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitudes as $solicitud) : ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($solicitud->numero_solicitud); ?></strong>
                                            </td>
                                            <td>
                                                <span class="ga-badge ga-badge-outline">
                                                    <?php echo esc_html($metodos_pago[$solicitud->metodo_pago] ?? $solicitud->metodo_pago); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong>$<?php echo number_format($solicitud->monto_solicitado, 2); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_class = GA_Solicitudes_Cobro::get_estado_badge_class($solicitud->estado);
                                                ?>
                                                <span class="ga-badge <?php echo esc_attr($badge_class); ?>">
                                                    <?php echo esc_html($estados_solicitud[$solicitud->estado] ?? $solicitud->estado); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo esc_html(date_i18n('d M Y', strtotime($solicitud->created_at))); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($vista === 'nueva') : ?>
                <!-- Vista: Nueva Solicitud -->
                <div class="ga-card">
                    <div class="ga-card-header">
                        <h3>
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php esc_html_e('Solicitar Retiro', 'gestionadmin-wolk'); ?>
                        </h3>
                    </div>
                    <div class="ga-card-body">
                        <?php if ($total_disponible <= 0) : ?>
                            <div class="ga-empty-state">
                                <span class="dashicons dashicons-warning"></span>
                                <p><?php esc_html_e('No tienes saldo disponible para retirar.', 'gestionadmin-wolk'); ?></p>
                                <a href="?vista=disponibles" class="ga-btn ga-btn-secondary">
                                    <?php esc_html_e('Ver Comisiones', 'gestionadmin-wolk'); ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <form method="post" class="ga-form ga-form-solicitud">
                                <?php wp_nonce_field('ga_crear_solicitud', 'ga_nonce'); ?>
                                <input type="hidden" name="ga_crear_solicitud" value="1">

                                <!-- Paso 1: Seleccionar comisiones -->
                                <div class="ga-form-section">
                                    <h4>
                                        <span class="ga-step-number">1</span>
                                        <?php esc_html_e('Selecciona las comisiones a incluir', 'gestionadmin-wolk'); ?>
                                    </h4>

                                    <div class="ga-select-all-wrap">
                                        <label class="ga-checkbox-label">
                                            <input type="checkbox" id="ga-select-all-comisiones">
                                            <?php esc_html_e('Seleccionar todas', 'gestionadmin-wolk'); ?>
                                        </label>
                                        <span class="ga-total-selected">
                                            <?php esc_html_e('Total seleccionado:', 'gestionadmin-wolk'); ?>
                                            <strong id="ga-total-seleccionado">$0.00</strong>
                                        </span>
                                    </div>

                                    <div class="ga-comisiones-list">
                                        <?php foreach ($comisiones_disponibles as $comision) : ?>
                                            <label class="ga-comision-item">
                                                <input type="checkbox" name="comisiones[]"
                                                       value="<?php echo esc_attr($comision->id); ?>"
                                                       data-monto="<?php echo esc_attr($comision->monto_comision); ?>">
                                                <div class="ga-comision-info">
                                                    <span class="ga-comision-orden"><?php echo esc_html($comision->orden_codigo); ?></span>
                                                    <span class="ga-comision-tipo"><?php echo esc_html($comision->tipo_acuerdo ?? '-'); ?></span>
                                                    <span class="ga-comision-monto">$<?php echo number_format($comision->monto_comision, 2); ?></span>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Paso 2: Método de pago -->
                                <div class="ga-form-section">
                                    <h4>
                                        <span class="ga-step-number">2</span>
                                        <?php esc_html_e('Elige tu método de pago', 'gestionadmin-wolk'); ?>
                                    </h4>

                                    <div class="ga-metodos-pago">
                                        <?php foreach ($metodos_pago as $key => $label) : ?>
                                            <label class="ga-metodo-option">
                                                <input type="radio" name="metodo_pago" value="<?php echo esc_attr($key); ?>" required>
                                                <span class="ga-metodo-label"><?php echo esc_html($label); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Campos dinámicos según método -->
                                    <div class="ga-pago-fields" id="ga-pago-binance" style="display:none;">
                                        <div class="ga-form-row">
                                            <label><?php esc_html_e('Wallet Binance (USDT TRC20)', 'gestionadmin-wolk'); ?></label>
                                            <input type="text" name="binance_wallet" placeholder="T...">
                                        </div>
                                        <div class="ga-form-row">
                                            <label><?php esc_html_e('Email Binance (opcional)', 'gestionadmin-wolk'); ?></label>
                                            <input type="email" name="binance_email">
                                        </div>
                                    </div>

                                    <div class="ga-pago-fields" id="ga-pago-wise" style="display:none;">
                                        <div class="ga-form-row">
                                            <label><?php esc_html_e('Email de Wise', 'gestionadmin-wolk'); ?></label>
                                            <input type="email" name="wise_email" placeholder="tu@email.com">
                                        </div>
                                    </div>

                                    <div class="ga-pago-fields" id="ga-pago-paypal" style="display:none;">
                                        <div class="ga-form-row">
                                            <label><?php esc_html_e('Email de PayPal', 'gestionadmin-wolk'); ?></label>
                                            <input type="email" name="paypal_email" placeholder="tu@email.com">
                                        </div>
                                    </div>

                                    <div class="ga-pago-fields" id="ga-pago-transferencia_local" style="display:none;">
                                        <div class="ga-form-row">
                                            <label><?php esc_html_e('Banco', 'gestionadmin-wolk'); ?></label>
                                            <input type="text" name="banco">
                                        </div>
                                        <div class="ga-form-row-inline">
                                            <div class="ga-form-row">
                                                <label><?php esc_html_e('Tipo de Cuenta', 'gestionadmin-wolk'); ?></label>
                                                <select name="tipo_cuenta">
                                                    <option value="AHORROS"><?php esc_html_e('Ahorros', 'gestionadmin-wolk'); ?></option>
                                                    <option value="CORRIENTE"><?php esc_html_e('Corriente', 'gestionadmin-wolk'); ?></option>
                                                </select>
                                            </div>
                                            <div class="ga-form-row">
                                                <label><?php esc_html_e('Número de Cuenta', 'gestionadmin-wolk'); ?></label>
                                                <input type="text" name="cuenta">
                                            </div>
                                        </div>
                                        <div class="ga-form-row">
                                            <label><?php esc_html_e('Titular', 'gestionadmin-wolk'); ?></label>
                                            <input type="text" name="titular">
                                        </div>
                                    </div>

                                    <div class="ga-pago-fields" id="ga-pago-otro" style="display:none;">
                                        <div class="ga-form-row">
                                            <label><?php esc_html_e('Instrucciones de Pago', 'gestionadmin-wolk'); ?></label>
                                            <textarea name="instrucciones_pago" rows="3" placeholder="<?php esc_attr_e('Describe cómo quieres recibir el pago...', 'gestionadmin-wolk'); ?>"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paso 3: Notas -->
                                <div class="ga-form-section">
                                    <h4>
                                        <span class="ga-step-number">3</span>
                                        <?php esc_html_e('Notas adicionales (opcional)', 'gestionadmin-wolk'); ?>
                                    </h4>
                                    <div class="ga-form-row">
                                        <textarea name="notas" rows="3" placeholder="<?php esc_attr_e('Agrega cualquier comentario para el equipo de finanzas...', 'gestionadmin-wolk'); ?>"></textarea>
                                    </div>
                                </div>

                                <!-- Resumen y enviar -->
                                <div class="ga-form-submit">
                                    <div class="ga-submit-summary">
                                        <span><?php esc_html_e('Total a solicitar:', 'gestionadmin-wolk'); ?></span>
                                        <strong id="ga-total-submit">$0.00</strong>
                                    </div>
                                    <button type="submit" class="ga-btn ga-btn-primary ga-btn-large" id="ga-btn-solicitar" disabled>
                                        <span class="dashicons dashicons-yes"></span>
                                        <?php esc_html_e('Enviar Solicitud', 'gestionadmin-wolk'); ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="ga-portal-footer">
            <a href="<?php echo esc_url(home_url('/aplicante/')); ?>" class="ga-btn-back">
                <span class="dashicons dashicons-arrow-left-alt"></span>
                <?php esc_html_e('Volver al Dashboard', 'gestionadmin-wolk'); ?>
            </a>
        </div>
    </div>
</div>

<style>
/* Base styles */
.ga-portal-pagos {
    min-height: 80vh;
    padding: 40px 20px;
    background: #f5f7fa;
}

.ga-portal-header {
    text-align: center;
    margin-bottom: 30px;
}

.ga-portal-header h1 {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 28px;
    margin: 0 0 10px;
}

.ga-portal-header h1 .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #28a745;
}

.ga-portal-header p {
    color: #666;
    margin: 0;
}

/* Alerts */
.ga-alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.ga-alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.ga-alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Stats row */
.ga-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.ga-stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ga-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
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

.ga-stat-disponible .ga-stat-icon { background: linear-gradient(135deg, #28a745, #20c997); }
.ga-stat-solicitado .ga-stat-icon { background: linear-gradient(135deg, #ffc107, #fd7e14); }
.ga-stat-pagado .ga-stat-icon { background: linear-gradient(135deg, #007bff, #6610f2); }
.ga-stat-total .ga-stat-icon { background: linear-gradient(135deg, #6c757d, #495057); }

.ga-stat-value {
    display: block;
    font-size: 22px;
    font-weight: 700;
    color: #1a1a2e;
}

.ga-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
}

/* Tabs */
.ga-tabs {
    display: flex;
    gap: 5px;
    margin-bottom: 20px;
    background: #fff;
    border-radius: 10px;
    padding: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ga-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    text-decoration: none;
    color: #666;
    border-radius: 8px;
    transition: all 0.3s;
}

.ga-tab:hover {
    background: #f5f7fa;
    color: #333;
}

.ga-tab-active {
    background: #28a745;
    color: #fff;
}

.ga-tab-active:hover {
    background: #218838;
    color: #fff;
}

.ga-tab-badge {
    background: rgba(255,255,255,0.3);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
}

.ga-tab-active .ga-tab-badge {
    background: rgba(0,0,0,0.2);
}

/* Cards */
.ga-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ga-card-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.ga-card-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
}

.ga-card-header .dashicons {
    color: #28a745;
}

.ga-card-body {
    padding: 20px;
}

/* Table */
.ga-table {
    width: 100%;
    border-collapse: collapse;
}

.ga-table th,
.ga-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.ga-table th {
    background: #f9f9f9;
    font-weight: 600;
    font-size: 13px;
    color: #666;
}

.ga-table tfoot th {
    background: #f0f0f0;
}

/* Empty state */
.ga-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.ga-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #ccc;
    display: block;
    margin: 0 auto 15px;
}

/* Badges */
.ga-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.ga-badge-outline { background: transparent; border: 1px solid #ddd; color: #666; }
.ga-badge-success { background: #d4edda; color: #155724; }
.ga-badge-warning { background: #fff3cd; color: #856404; }
.ga-badge-primary { background: #cce5ff; color: #004085; }
.ga-badge-info { background: #d1ecf1; color: #0c5460; }
.ga-badge-danger { background: #f8d7da; color: #721c24; }
.ga-badge-secondary { background: #e9ecef; color: #6c757d; }

.ga-text-muted { color: #999; }
.ga-amount-success { color: #28a745; }

/* Action bar */
.ga-action-bar {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: center;
}

/* Buttons */
.ga-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.3s;
}

.ga-btn-primary {
    background: #28a745;
    color: #fff;
}

.ga-btn-primary:hover {
    background: #218838;
    color: #fff;
}

.ga-btn-primary:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.ga-btn-secondary {
    background: #6c757d;
    color: #fff;
}

.ga-btn-large {
    padding: 14px 28px;
    font-size: 16px;
}

/* Form styles */
.ga-form-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.ga-form-section:last-of-type {
    border-bottom: none;
}

.ga-form-section h4 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 20px;
    font-size: 16px;
}

.ga-step-number {
    width: 28px;
    height: 28px;
    background: #28a745;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

/* Comisiones list */
.ga-select-all-wrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 10px 15px;
    background: #f9f9f9;
    border-radius: 6px;
}

.ga-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.ga-total-selected {
    color: #666;
}

.ga-total-selected strong {
    color: #28a745;
    font-size: 18px;
}

.ga-comisiones-list {
    display: grid;
    gap: 10px;
}

.ga-comision-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.ga-comision-item:hover {
    background: #e9f7ef;
}

.ga-comision-item input:checked + .ga-comision-info {
    color: #28a745;
}

.ga-comision-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ga-comision-orden {
    font-weight: 600;
}

.ga-comision-tipo {
    color: #666;
    font-size: 13px;
}

.ga-comision-monto {
    font-weight: 700;
    color: #28a745;
}

/* Metodos pago */
.ga-metodos-pago {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.ga-metodo-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: #f9f9f9;
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.ga-metodo-option:hover {
    background: #e9f7ef;
}

.ga-metodo-option input:checked + .ga-metodo-label {
    color: #28a745;
    font-weight: 600;
}

.ga-metodo-option:has(input:checked) {
    border-color: #28a745;
    background: #e9f7ef;
}

/* Form fields */
.ga-pago-fields {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 15px;
}

.ga-form-row {
    margin-bottom: 15px;
}

.ga-form-row:last-child {
    margin-bottom: 0;
}

.ga-form-row label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.ga-form-row input,
.ga-form-row select,
.ga-form-row textarea {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.ga-form-row-inline {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Submit section */
.ga-form-submit {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    margin-top: 20px;
}

.ga-submit-summary span {
    display: block;
    font-size: 13px;
    color: #666;
}

.ga-submit-summary strong {
    font-size: 24px;
    color: #28a745;
}

/* Footer */
.ga-portal-footer {
    text-align: center;
    margin-top: 40px;
}

.ga-btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #666;
    text-decoration: none;
}

.ga-btn-back:hover {
    color: #28a745;
}

/* Responsive */
@media (max-width: 768px) {
    .ga-tabs {
        flex-direction: column;
    }

    .ga-form-row-inline {
        grid-template-columns: 1fr;
    }

    .ga-form-submit {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todas las comisiones
    const selectAll = document.getElementById('ga-select-all-comisiones');
    const checkboxes = document.querySelectorAll('input[name="comisiones[]"]');
    const totalDisplay = document.getElementById('ga-total-seleccionado');
    const totalSubmit = document.getElementById('ga-total-submit');
    const btnSolicitar = document.getElementById('ga-btn-solicitar');

    function updateTotal() {
        let total = 0;
        let count = 0;
        checkboxes.forEach(function(cb) {
            if (cb.checked) {
                total += parseFloat(cb.dataset.monto);
                count++;
            }
        });

        const formatted = '$' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (totalDisplay) totalDisplay.textContent = formatted;
        if (totalSubmit) totalSubmit.textContent = formatted;
        if (btnSolicitar) btnSolicitar.disabled = count === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
            updateTotal();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateTotal);
    });

    // Mostrar/ocultar campos de método de pago
    const metodoRadios = document.querySelectorAll('input[name="metodo_pago"]');
    const pagoFields = document.querySelectorAll('.ga-pago-fields');

    metodoRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            pagoFields.forEach(function(field) {
                field.style.display = 'none';
            });

            const targetId = 'ga-pago-' + this.value.toLowerCase();
            const target = document.getElementById(targetId);
            if (target) {
                target.style.display = 'block';
            }
        });
    });
});
</script>

<?php get_footer(); ?>
