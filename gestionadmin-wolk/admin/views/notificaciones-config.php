<?php
/**
 * Vista: Configuración de Notificaciones
 *
 * Panel para activar/desactivar notificaciones por email.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Admin/Views
 * @since      1.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Procesar guardado
$mensaje = '';
$tipo_mensaje = '';

if (isset($_POST['ga_save_notificaciones']) && check_admin_referer('ga_notificaciones_config')) {
    $config = isset($_POST['notificaciones']) ? $_POST['notificaciones'] : array();
    GA_Notificaciones::save_config($config);
    $mensaje = __('Configuración guardada correctamente.', 'gestionadmin-wolk');
    $tipo_mensaje = 'success';
}

// Obtener configuración actual
$config = GA_Notificaciones::get_config();
$tipos = GA_Notificaciones::get_tipos();

// Agrupar por categoría
$grupos = array(
    'tareas' => array(
        'titulo' => __('Tareas', 'gestionadmin-wolk'),
        'icono'  => 'dashicons-clipboard',
        'items'  => array(
            'tarea_asignada',
            'tarea_iniciada',
            'tarea_enviada_qa',
            'tarea_aprobada_qa',
            'tarea_rechazada_qa',
            'tarea_completada',
            'tarea_aprobada',
            'tarea_rechazada',
        )
    ),
    'aplicantes' => array(
        'titulo' => __('Aplicantes', 'gestionadmin-wolk'),
        'icono'  => 'dashicons-groups',
        'items'  => array(
            'aplicante_bienvenida',
            'aplicante_aplicacion',
            'aplicante_estado_cambio',
        )
    ),
    'ordenes' => array(
        'titulo' => __('Órdenes de Trabajo', 'gestionadmin-wolk'),
        'icono'  => 'dashicons-portfolio',
        'items'  => array(
            'orden_nueva',
            'orden_asignada',
        )
    ),
    'facturacion' => array(
        'titulo' => __('Facturación', 'gestionadmin-wolk'),
        'icono'  => 'dashicons-money-alt',
        'items'  => array(
            'factura_enviada',
            'factura_pagada',
        )
    ),
);
?>

<div class="wrap ga-admin-wrap">
    <div class="ga-admin-header">
        <h1>
            <span class="dashicons dashicons-email-alt"></span>
            <?php esc_html_e('Configuración de Notificaciones', 'gestionadmin-wolk'); ?>
        </h1>
        <p class="ga-admin-subtitle">
            <?php esc_html_e('Activa o desactiva las notificaciones automáticas por email.', 'gestionadmin-wolk'); ?>
        </p>
    </div>

    <?php if ($mensaje) : ?>
        <div class="ga-notice ga-notice-<?php echo esc_attr($tipo_mensaje); ?>">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php echo esc_html($mensaje); ?>
        </div>
    <?php endif; ?>

    <form method="post" class="ga-notificaciones-form">
        <?php wp_nonce_field('ga_notificaciones_config'); ?>

        <div class="ga-notificaciones-grid">
            <?php foreach ($grupos as $grupo_key => $grupo) : ?>
                <div class="ga-notificacion-grupo">
                    <div class="ga-grupo-header">
                        <span class="dashicons <?php echo esc_attr($grupo['icono']); ?>"></span>
                        <h2><?php echo esc_html($grupo['titulo']); ?></h2>
                        <button type="button" class="ga-toggle-all" data-grupo="<?php echo esc_attr($grupo_key); ?>">
                            <?php esc_html_e('Alternar', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>

                    <div class="ga-grupo-items">
                        <?php foreach ($grupo['items'] as $tipo) :
                            if (!isset($tipos[$tipo])) continue;
                            $activo = isset($config[$tipo]) && $config[$tipo];
                        ?>
                            <label class="ga-notificacion-item <?php echo $activo ? 'activo' : ''; ?>">
                                <span class="ga-item-info">
                                    <span class="ga-item-nombre"><?php echo esc_html($tipos[$tipo]); ?></span>
                                </span>
                                <span class="ga-toggle-switch">
                                    <input type="checkbox"
                                           name="notificaciones[<?php echo esc_attr($tipo); ?>]"
                                           value="1"
                                           data-grupo="<?php echo esc_attr($grupo_key); ?>"
                                           <?php checked($activo); ?>>
                                    <span class="ga-toggle-slider"></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="ga-form-actions">
            <button type="submit" name="ga_save_notificaciones" class="ga-btn ga-btn-primary">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e('Guardar Configuración', 'gestionadmin-wolk'); ?>
            </button>
        </div>
    </form>
</div>

<style>
/* ============================================================
   ESTILOS PANEL NOTIFICACIONES
   ============================================================ */

.ga-admin-wrap {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 20px;
}

.ga-admin-header {
    margin-bottom: 30px;
}

.ga-admin-header h1 {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 28px;
    font-weight: 600;
    color: #1F2937;
    margin: 0 0 8px;
}

.ga-admin-header h1 .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #0056A6;
}

.ga-admin-subtitle {
    color: #6B7280;
    font-size: 15px;
    margin: 0;
}

/* Notice */
.ga-notice {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-weight: 500;
}

.ga-notice-success {
    background: #ECFDF5;
    color: #065F46;
    border: 1px solid #A7F3D0;
}

.ga-notice-success .dashicons {
    color: #10B981;
}

/* Grid de grupos */
.ga-notificaciones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

/* Grupo */
.ga-notificacion-grupo {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    overflow: hidden;
}

.ga-grupo-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    background: #F9FAFB;
    border-bottom: 1px solid #E5E7EB;
}

.ga-grupo-header .dashicons {
    font-size: 22px;
    width: 22px;
    height: 22px;
    color: #0056A6;
}

.ga-grupo-header h2 {
    flex: 1;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1F2937;
}

.ga-toggle-all {
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    color: #6B7280;
    background: #fff;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.ga-toggle-all:hover {
    background: #F3F4F6;
    border-color: #9CA3AF;
}

/* Items */
.ga-grupo-items {
    padding: 8px;
}

.ga-notificacion-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.ga-notificacion-item:hover {
    background: #F9FAFB;
}

.ga-notificacion-item.activo {
    background: #EFF6FF;
}

.ga-item-info {
    flex: 1;
}

.ga-item-nombre {
    font-size: 14px;
    color: #374151;
    font-weight: 500;
}

/* Toggle Switch */
.ga-toggle-switch {
    position: relative;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.ga-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.ga-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #D1D5DB;
    transition: 0.3s;
    border-radius: 26px;
}

.ga-toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.ga-toggle-switch input:checked + .ga-toggle-slider {
    background-color: #0056A6;
}

.ga-toggle-switch input:checked + .ga-toggle-slider:before {
    transform: translateX(22px);
}

.ga-toggle-switch input:focus + .ga-toggle-slider {
    box-shadow: 0 0 0 3px rgba(0, 86, 166, 0.2);
}

/* Botón guardar */
.ga-form-actions {
    display: flex;
    justify-content: flex-end;
}

.ga-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.ga-btn-primary {
    background: #0056A6;
    color: #fff;
}

.ga-btn-primary:hover {
    background: #004080;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 86, 166, 0.3);
}

.ga-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Responsive */
@media (max-width: 768px) {
    .ga-notificaciones-grid {
        grid-template-columns: 1fr;
    }

    .ga-admin-header h1 {
        font-size: 22px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle individual
    $('.ga-toggle-switch input').on('change', function() {
        var $item = $(this).closest('.ga-notificacion-item');
        if ($(this).is(':checked')) {
            $item.addClass('activo');
        } else {
            $item.removeClass('activo');
        }
    });

    // Toggle grupo completo
    $('.ga-toggle-all').on('click', function() {
        var grupo = $(this).data('grupo');
        var $checkboxes = $('input[data-grupo="' + grupo + '"]');
        var allChecked = $checkboxes.filter(':checked').length === $checkboxes.length;

        $checkboxes.each(function() {
            $(this).prop('checked', !allChecked).trigger('change');
        });
    });
});
</script>
