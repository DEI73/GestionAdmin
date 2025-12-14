<?php
/**
 * Template: Portal Empleado - Mi Perfil
 *
 * Muestra informacion personal y laboral del empleado.
 * Los datos laborales son solo lectura (gestionados por RRHH).
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
 * @since      1.3.0
 * @updated    1.10.0 - Perfil funcional completo
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar autenticacion
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// Obtener usuario actual
$wp_user_id = get_current_user_id();
$wp_user = wp_get_current_user();

// Cargar modulos necesarios
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-usuarios.php';

// Verificar que es un empleado registrado
$usuario_ga = GA_Usuarios::get_by_wp_id($wp_user_id);
if (!$usuario_ga) {
    wp_redirect(home_url('/portal-empleado/'));
    exit;
}

global $wpdb;

// =========================================================================
// OBTENER DATOS COMPLETOS DEL EMPLEADO
// =========================================================================

// Datos de WordPress
$nombre_completo = $wp_user->display_name;
$email = $wp_user->user_email;
$fecha_registro_wp = $wp_user->user_registered;

// Obtener puesto
$puesto = null;
if ($usuario_ga->puesto_id) {
    $puesto = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ga_puestos WHERE id = %d",
        $usuario_ga->puesto_id
    ));
}

// Obtener departamento
$departamento = null;
if ($usuario_ga->departamento_id) {
    $departamento = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ga_departamentos WHERE id = %d",
        $usuario_ga->departamento_id
    ));
}

// Obtener escala y tarifa actual
$escala = null;
$tarifa_hora = 0;
if ($usuario_ga->escala_id) {
    $escala = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ga_puestos_escalas WHERE id = %d",
        $usuario_ga->escala_id
    ));
    if ($escala) {
        $tarifa_hora = floatval($escala->tarifa_hora);
    }
}

// =========================================================================
// CALCULAR ANTIGUEDAD
// =========================================================================
$antiguedad_texto = __('No disponible', 'gestionadmin-wolk');
$antiguedad_meses = 0;

if ($usuario_ga->fecha_ingreso) {
    $fecha_ingreso = new DateTime($usuario_ga->fecha_ingreso);
    $hoy = new DateTime();
    $diferencia = $fecha_ingreso->diff($hoy);

    $antiguedad_meses = ($diferencia->y * 12) + $diferencia->m;

    if ($diferencia->y > 0) {
        if ($diferencia->m > 0) {
            $antiguedad_texto = sprintf(
                _n('%d año', '%d años', $diferencia->y, 'gestionadmin-wolk'),
                $diferencia->y
            ) . ' ' . sprintf(
                _n('%d mes', '%d meses', $diferencia->m, 'gestionadmin-wolk'),
                $diferencia->m
            );
        } else {
            $antiguedad_texto = sprintf(
                _n('%d año', '%d años', $diferencia->y, 'gestionadmin-wolk'),
                $diferencia->y
            );
        }
    } else {
        $antiguedad_texto = sprintf(
            _n('%d mes', '%d meses', $diferencia->m, 'gestionadmin-wolk'),
            $diferencia->m
        );
    }
}

// =========================================================================
// METODOS DE PAGO
// =========================================================================
$metodos_pago = GA_Usuarios::get_metodos_pago();
$metodo_pago_actual = $usuario_ga->metodo_pago_preferido ?? '';
$metodo_pago_label = isset($metodos_pago[$metodo_pago_actual]) ? $metodos_pago[$metodo_pago_actual] : __('No configurado', 'gestionadmin-wolk');

// Datos de pago (enmascarados por seguridad)
$datos_pago_display = '';
if (!empty($usuario_ga->datos_pago)) {
    // Mostrar solo ultimos 4 caracteres
    $datos = $usuario_ga->datos_pago;
    if (strlen($datos) > 4) {
        $datos_pago_display = '****' . substr($datos, -4);
    } else {
        $datos_pago_display = '****';
    }
}

// =========================================================================
// NIVELES JERARQUICOS
// =========================================================================
$niveles = array(
    1 => __('Socio', 'gestionadmin-wolk'),
    2 => __('Director', 'gestionadmin-wolk'),
    3 => __('Jefe/Gerente', 'gestionadmin-wolk'),
    4 => __('Empleado', 'gestionadmin-wolk'),
    5 => __('Practicante', 'gestionadmin-wolk'),
);
$nivel_label = isset($niveles[$usuario_ga->nivel_jerarquico]) ? $niveles[$usuario_ga->nivel_jerarquico] : __('No definido', 'gestionadmin-wolk');

// URL para cambio de contrasena
$reset_password_url = wp_lostpassword_url(home_url('/portal-empleado/'));

// Usar header del tema
get_header();

// Imprimir estilos del portal
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-empleado">
    <div class="ga-container">
        <!-- =========================================================================
             HEADER
        ========================================================================== -->
        <div class="ga-portal-header">
            <div class="ga-welcome-content">
                <h1>
                    <span class="dashicons dashicons-id"></span>
                    <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
                </h1>
                <p class="ga-portal-subtitle">
                    <?php esc_html_e('Informacion personal y laboral', 'gestionadmin-wolk'); ?>
                </p>
            </div>
        </div>

        <!-- =========================================================================
             NAVEGACION DEL PORTAL
        ========================================================================== -->
        <nav class="ga-dashboard-nav">
            <a href="<?php echo esc_url(home_url('/portal-empleado/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mis-tareas/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('Mis Tareas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mi-timer/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e('Mi Timer', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mis-horas/')); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-backup"></span>
                <?php esc_html_e('Mis Horas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/portal-empleado/mi-perfil/')); ?>" class="ga-nav-item ga-nav-active">
                <span class="dashicons dashicons-id"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </a>
        </nav>

        <!-- =========================================================================
             CONTENIDO DEL PERFIL
        ========================================================================== -->
        <div class="ga-perfil-content">

            <!-- =========================================================================
                 TARJETA DE PERFIL PRINCIPAL
            ========================================================================== -->
            <div class="ga-perfil-card ga-perfil-main">
                <div class="ga-perfil-avatar">
                    <?php echo get_avatar($wp_user_id, 120); ?>
                </div>
                <div class="ga-perfil-info-main">
                    <h2 class="ga-perfil-nombre"><?php echo esc_html($nombre_completo); ?></h2>
                    <p class="ga-perfil-email">
                        <span class="dashicons dashicons-email"></span>
                        <?php echo esc_html($email); ?>
                    </p>
                    <?php if ($puesto): ?>
                        <p class="ga-perfil-puesto">
                            <span class="dashicons dashicons-businessman"></span>
                            <?php echo esc_html($puesto->nombre); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($departamento): ?>
                        <p class="ga-perfil-departamento">
                            <span class="dashicons dashicons-building"></span>
                            <?php echo esc_html($departamento->nombre); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="ga-perfil-badge">
                    <?php if ($usuario_ga->activo): ?>
                        <span class="ga-badge ga-badge-success">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e('Activo', 'gestionadmin-wolk'); ?>
                        </span>
                    <?php else: ?>
                        <span class="ga-badge ga-badge-danger">
                            <span class="dashicons dashicons-no"></span>
                            <?php esc_html_e('Inactivo', 'gestionadmin-wolk'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ga-perfil-grid">
                <!-- =========================================================================
                     INFORMACION LABORAL
                ========================================================================== -->
                <div class="ga-perfil-card">
                    <div class="ga-card-header">
                        <span class="dashicons dashicons-portfolio"></span>
                        <h3><?php esc_html_e('Informacion Laboral', 'gestionadmin-wolk'); ?></h3>
                    </div>
                    <div class="ga-card-body">
                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Codigo Empleado', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value ga-info-code">
                                <?php echo esc_html($usuario_ga->codigo_empleado ?: __('No asignado', 'gestionadmin-wolk')); ?>
                            </span>
                        </div>

                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Departamento', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value">
                                <?php echo esc_html($departamento ? $departamento->nombre : __('No asignado', 'gestionadmin-wolk')); ?>
                            </span>
                        </div>

                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Puesto', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value">
                                <?php echo esc_html($puesto ? $puesto->nombre : __('No asignado', 'gestionadmin-wolk')); ?>
                            </span>
                        </div>

                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Nivel Jerarquico', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value">
                                <?php echo esc_html($nivel_label); ?>
                                <?php if ($usuario_ga->nivel_jerarquico): ?>
                                    <span class="ga-nivel-badge"><?php echo esc_html($usuario_ga->nivel_jerarquico); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Fecha de Ingreso', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value">
                                <?php
                                if ($usuario_ga->fecha_ingreso) {
                                    echo esc_html(date_i18n(get_option('date_format'), strtotime($usuario_ga->fecha_ingreso)));
                                } else {
                                    esc_html_e('No registrada', 'gestionadmin-wolk');
                                }
                                ?>
                            </span>
                        </div>

                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Antiguedad', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value ga-info-highlight">
                                <?php echo esc_html($antiguedad_texto); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- =========================================================================
                     TARIFA Y COMPENSACION
                ========================================================================== -->
                <div class="ga-perfil-card">
                    <div class="ga-card-header">
                        <span class="dashicons dashicons-money-alt"></span>
                        <h3><?php esc_html_e('Tarifa y Compensacion', 'gestionadmin-wolk'); ?></h3>
                    </div>
                    <div class="ga-card-body">
                        <?php if ($escala): ?>
                            <div class="ga-tarifa-display">
                                <span class="ga-tarifa-valor">$<?php echo esc_html(number_format($tarifa_hora, 2)); ?></span>
                                <span class="ga-tarifa-label"><?php esc_html_e('USD / hora', 'gestionadmin-wolk'); ?></span>
                            </div>

                            <div class="ga-info-row">
                                <span class="ga-info-label"><?php esc_html_e('Escala', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value"><?php echo esc_html($escala->nombre); ?></span>
                            </div>

                            <?php if ($escala->meses_min || $escala->meses_max): ?>
                                <div class="ga-info-row">
                                    <span class="ga-info-label"><?php esc_html_e('Rango de Antiguedad', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-info-value">
                                        <?php
                                        if ($escala->meses_max) {
                                            printf(
                                                esc_html__('%d - %d meses', 'gestionadmin-wolk'),
                                                $escala->meses_min,
                                                $escala->meses_max
                                            );
                                        } else {
                                            printf(
                                                esc_html__('%d+ meses', 'gestionadmin-wolk'),
                                                $escala->meses_min
                                            );
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="ga-no-data">
                                <span class="dashicons dashicons-info"></span>
                                <p><?php esc_html_e('Tarifa no configurada. Contacta a RRHH.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- =========================================================================
                     METODO DE PAGO
                ========================================================================== -->
                <div class="ga-perfil-card">
                    <div class="ga-card-header">
                        <span class="dashicons dashicons-bank"></span>
                        <h3><?php esc_html_e('Metodo de Pago', 'gestionadmin-wolk'); ?></h3>
                    </div>
                    <div class="ga-card-body">
                        <?php if ($metodo_pago_actual): ?>
                            <div class="ga-pago-display">
                                <span class="ga-pago-metodo"><?php echo esc_html($metodo_pago_label); ?></span>
                                <?php if ($datos_pago_display): ?>
                                    <span class="ga-pago-datos"><?php echo esc_html($datos_pago_display); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="ga-pago-info">
                                <span class="dashicons dashicons-lock"></span>
                                <p><?php esc_html_e('Para cambiar tu metodo de pago, contacta al departamento de RRHH.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="ga-no-data">
                                <span class="dashicons dashicons-warning"></span>
                                <p><?php esc_html_e('Metodo de pago no configurado. Contacta a RRHH para configurar tu forma de pago.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- =========================================================================
                     SEGURIDAD
                ========================================================================== -->
                <div class="ga-perfil-card">
                    <div class="ga-card-header">
                        <span class="dashicons dashicons-shield"></span>
                        <h3><?php esc_html_e('Seguridad', 'gestionadmin-wolk'); ?></h3>
                    </div>
                    <div class="ga-card-body">
                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Email de acceso', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value"><?php echo esc_html($email); ?></span>
                        </div>

                        <div class="ga-info-row">
                            <span class="ga-info-label"><?php esc_html_e('Miembro desde', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-info-value">
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($fecha_registro_wp))); ?>
                            </span>
                        </div>

                        <div class="ga-security-action">
                            <a href="<?php echo esc_url($reset_password_url); ?>" class="ga-btn ga-btn-outline">
                                <span class="dashicons dashicons-admin-network"></span>
                                <?php esc_html_e('Cambiar Contrasena', 'gestionadmin-wolk'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =========================================================================
                 NOTA INFORMATIVA
            ========================================================================== -->
            <div class="ga-perfil-note">
                <span class="dashicons dashicons-info-outline"></span>
                <p>
                    <?php esc_html_e('Los datos laborales son gestionados por el departamento de Recursos Humanos. Si necesitas actualizar alguna informacion, por favor contacta a tu supervisor o al equipo de RRHH.', 'gestionadmin-wolk'); ?>
                </p>
            </div>
        </div>

        <div class="ga-portal-footer">
            <p>
                <?php esc_html_e('Disenado y desarrollado por', 'gestionadmin-wolk'); ?>
                <a href="https://wolksoftcr.com" target="_blank">Wolksoftcr.com</a>
            </p>
        </div>
    </div>
</div>

<style>
/* =========================================================================
   PORTAL EMPLEADO - MI PERFIL
   ========================================================================== */
.ga-portal-empleado {
    min-height: 80vh;
    padding: 30px 20px;
    background: #f5f7fa;
}

/* Header */
.ga-portal-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
}
.ga-portal-header h1 {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    font-size: 28px;
    color: #ffffff;
    margin: 0 0 10px 0;
}
.ga-portal-header h1 .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    color: #ffffff;
}
.ga-portal-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    margin: 0;
}

/* Navegacion */
.ga-dashboard-nav {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    padding: 15px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow-x: auto;
}
.ga-nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #555;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    transition: all 0.2s;
}
.ga-nav-item:hover {
    background: #f0f2f5;
    color: #333;
}
.ga-nav-item.ga-nav-active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.ga-nav-item .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Perfil Content */
.ga-perfil-content {
    max-width: 1000px;
    margin: 0 auto;
}

/* Tarjeta Principal */
.ga-perfil-main {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 30px;
    margin-bottom: 25px;
    position: relative;
}
.ga-perfil-avatar {
    flex-shrink: 0;
}
.ga-perfil-avatar img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.ga-perfil-info-main {
    flex-grow: 1;
}
.ga-perfil-nombre {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin: 0 0 10px;
}
.ga-perfil-email,
.ga-perfil-puesto,
.ga-perfil-departamento {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 5px 0;
    color: #666;
    font-size: 14px;
}
.ga-perfil-email .dashicons,
.ga-perfil-puesto .dashicons,
.ga-perfil-departamento .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: #667eea;
}
.ga-perfil-badge {
    position: absolute;
    top: 20px;
    right: 20px;
}
.ga-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.ga-badge .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}
.ga-badge-success {
    background: #d4edda;
    color: #155724;
}
.ga-badge-danger {
    background: #f8d7da;
    color: #721c24;
}

/* Grid de tarjetas */
.ga-perfil-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

/* Tarjetas */
.ga-perfil-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}
.ga-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}
.ga-card-header .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    color: #667eea;
}
.ga-card-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}
.ga-card-body {
    padding: 20px;
}

/* Filas de informacion */
.ga-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}
.ga-info-row:last-child {
    border-bottom: none;
}
.ga-info-label {
    font-size: 13px;
    color: #666;
}
.ga-info-value {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    text-align: right;
}
.ga-info-code {
    font-family: 'SF Mono', monospace;
    background: #f0f2f5;
    padding: 4px 10px;
    border-radius: 4px;
    color: #667eea;
}
.ga-info-highlight {
    color: #667eea;
    font-weight: 600;
}
.ga-nivel-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: #667eea;
    color: #fff;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
}

/* Tarifa Display */
.ga-tarifa-display {
    text-align: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    margin-bottom: 20px;
}
.ga-tarifa-valor {
    display: block;
    font-size: 36px;
    font-weight: 700;
    color: #fff;
    line-height: 1;
}
.ga-tarifa-label {
    display: block;
    font-size: 13px;
    color: rgba(255,255,255,0.8);
    margin-top: 5px;
}

/* Pago Display */
.ga-pago-display {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 15px;
}
.ga-pago-metodo {
    display: block;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}
.ga-pago-datos {
    display: block;
    font-size: 14px;
    color: #666;
    font-family: 'SF Mono', monospace;
    margin-top: 5px;
}
.ga-pago-info {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 15px;
    background: #fff3cd;
    border-radius: 8px;
    font-size: 13px;
    color: #856404;
}
.ga-pago-info .dashicons {
    flex-shrink: 0;
    margin-top: 2px;
}
.ga-pago-info p {
    margin: 0;
}

/* No Data */
.ga-no-data {
    text-align: center;
    padding: 20px;
    color: #888;
}
.ga-no-data .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    margin-bottom: 10px;
    color: #ccc;
}
.ga-no-data p {
    margin: 0;
    font-size: 14px;
}

/* Security Action */
.ga-security-action {
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
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.ga-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}
.ga-btn-outline {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
}
.ga-btn-outline:hover {
    background: #667eea;
    color: #fff;
}

/* Nota informativa */
.ga-perfil-note {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px 20px;
    background: #e8f4fd;
    border-radius: 10px;
    margin-top: 25px;
    border-left: 4px solid #667eea;
}
.ga-perfil-note .dashicons {
    flex-shrink: 0;
    font-size: 20px;
    width: 20px;
    height: 20px;
    color: #667eea;
    margin-top: 2px;
}
.ga-perfil-note p {
    margin: 0;
    font-size: 13px;
    color: #555;
    line-height: 1.5;
}

/* Footer */
.ga-portal-footer {
    text-align: center;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    color: #999;
    font-size: 13px;
}
.ga-portal-footer a {
    color: #667eea;
    text-decoration: none;
}

/* Responsive */
@media (max-width: 768px) {
    .ga-perfil-main {
        flex-direction: column;
        text-align: center;
    }
    .ga-perfil-badge {
        position: static;
        margin-top: 15px;
    }
    .ga-perfil-email,
    .ga-perfil-puesto,
    .ga-perfil-departamento {
        justify-content: center;
    }
    .ga-perfil-grid {
        grid-template-columns: 1fr;
    }
    .ga-info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    .ga-info-value {
        text-align: left;
    }
}
@media (max-width: 600px) {
    .ga-portal-header h1 {
        font-size: 22px;
    }
    .ga-dashboard-nav {
        gap: 5px;
        padding: 10px;
    }
    .ga-nav-item {
        padding: 8px 12px;
        font-size: 13px;
    }
    .ga-perfil-avatar img {
        width: 100px;
        height: 100px;
    }
    .ga-perfil-nombre {
        font-size: 20px;
    }
    .ga-tarifa-valor {
        font-size: 28px;
    }
}
</style>

<?php get_footer(); ?>
