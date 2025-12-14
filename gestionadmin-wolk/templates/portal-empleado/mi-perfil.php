<?php
/**
 * Template: Portal Empleado - Mi Perfil
 *
 * Muestra información personal y laboral del empleado.
 * - Secciones 1-3: Datos personales, documentos, pago (editables desde wp_ga_aplicantes)
 * - Sección 4: Datos laborales (solo lectura desde wp_ga_usuarios)
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalEmpleado
 * @since      1.3.0
 * @updated    1.17.0 - Perfil editable con log de cambios
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

// Obtener usuario actual
$wp_user_id = get_current_user_id();
$wp_user = wp_get_current_user();

// Cargar módulos necesarios
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-usuarios.php';

// Verificar que es un empleado registrado
$usuario_ga = GA_Usuarios::get_by_wp_id($wp_user_id);
if (!$usuario_ga) {
    wp_redirect(home_url('/portal-empleado/'));
    exit;
}

global $wpdb;

// =========================================================================
// OBTENER DATOS DEL APLICANTE (si existe)
// Un empleado puede haber sido contratado desde el portal de aplicantes
// o haber sido dado de alta directamente por RRHH
// =========================================================================
$aplicante = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}ga_aplicantes WHERE usuario_wp_id = %d",
    $wp_user_id
));

// Flag para saber si tiene registro de aplicante
$tiene_registro_aplicante = !empty($aplicante);

// =========================================================================
// DATOS DE WORDPRESS
// =========================================================================
$nombre_completo = $wp_user->display_name;
$email = $wp_user->user_email;
$fecha_registro_wp = $wp_user->user_registered;

// =========================================================================
// DATOS PERSONALES (de wp_ga_aplicantes o vacíos)
// =========================================================================
$datos_personales = array(
    'nombre_completo'    => $aplicante->nombre_completo ?? $nombre_completo,
    'documento_tipo'     => $aplicante->documento_tipo ?? '',
    'documento_numero'   => $aplicante->documento_numero ?? '',
    'email'              => $aplicante->email ?? $email,
    'telefono'           => $aplicante->telefono ?? '',
    'pais'               => $aplicante->pais ?? '',
    'ciudad'             => $aplicante->ciudad ?? '',
    'direccion'          => $aplicante->direccion ?? '',
);

// =========================================================================
// DOCUMENTOS (de wp_ga_aplicantes)
// =========================================================================
$documentos = array(
    'documento_identidad_url'   => $aplicante->documento_identidad_url ?? '',
    'rut_url'                   => $aplicante->rut_url ?? '',
    'certificado_bancario_url'  => $aplicante->certificado_bancario_url ?? '',
    'cv_url'                    => $aplicante->cv_url ?? '',
);

// =========================================================================
// MÉTODO DE PAGO (de wp_ga_aplicantes)
// =========================================================================
$metodos_pago_opciones = array(
    'BINANCE'       => 'Binance Pay',
    'WISE'          => 'Wise (TransferWise)',
    'PAYPAL'        => 'PayPal',
    'PAYONEER'      => 'Payoneer',
    'STRIPE'        => 'Stripe',
    'TRANSFERENCIA' => 'Transferencia Bancaria',
);

$metodo_pago = array(
    'metodo_pago_preferido' => $aplicante->metodo_pago_preferido ?? 'TRANSFERENCIA',
    'datos_pago_binance'    => $aplicante->datos_pago_binance ?? '',
    'datos_pago_wise'       => $aplicante->datos_pago_wise ?? '',
    'datos_pago_paypal'     => $aplicante->datos_pago_paypal ?? '',
    'datos_pago_banco'      => $aplicante->datos_pago_banco ?? '',
);

// =========================================================================
// DATOS LABORALES (de wp_ga_usuarios - SOLO LECTURA)
// =========================================================================

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
// CALCULAR ANTIGÜEDAD
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
// NIVELES JERÁRQUICOS
// =========================================================================
$niveles = array(
    1 => __('Socio', 'gestionadmin-wolk'),
    2 => __('Director', 'gestionadmin-wolk'),
    3 => __('Jefe/Gerente', 'gestionadmin-wolk'),
    4 => __('Empleado', 'gestionadmin-wolk'),
    5 => __('Practicante', 'gestionadmin-wolk'),
);
$nivel_label = isset($niveles[$usuario_ga->nivel_jerarquico]) ? $niveles[$usuario_ga->nivel_jerarquico] : __('No definido', 'gestionadmin-wolk');

// Lista de países
$paises = array(
    'CO' => 'Colombia',
    'MX' => 'México',
    'US' => 'Estados Unidos',
    'AR' => 'Argentina',
    'CL' => 'Chile',
    'PE' => 'Perú',
    'EC' => 'Ecuador',
    'CR' => 'Costa Rica',
    'PA' => 'Panamá',
    'ES' => 'España',
);

// Tipos de documento según país
$tipos_documento = array(
    'CC'        => 'Cédula de Ciudadanía (CO)',
    'CE'        => 'Cédula de Extranjería',
    'NIT'       => 'NIT (CO)',
    'RFC'       => 'RFC (MX)',
    'CURP'      => 'CURP (MX)',
    'DNI'       => 'DNI',
    'EIN'       => 'EIN (US)',
    'SSN'       => 'SSN (US)',
    'PASAPORTE' => 'Pasaporte',
);

// URL para cambio de contraseña
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
                    <?php esc_html_e('Información personal y laboral', 'gestionadmin-wolk'); ?>
                </p>
            </div>
        </div>

        <!-- =========================================================================
             NAVEGACIÓN DEL PORTAL
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
             MENSAJES DE ESTADO
        ========================================================================== -->
        <div id="ga-perfil-messages" class="ga-messages" style="display: none;"></div>

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
                    <h2 class="ga-perfil-nombre"><?php echo esc_html($datos_personales['nombre_completo']); ?></h2>
                    <p class="ga-perfil-email">
                        <span class="dashicons dashicons-email"></span>
                        <?php echo esc_html($datos_personales['email']); ?>
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

            <!-- =========================================================================
                 FORMULARIO EDITABLE (si tiene registro de aplicante)
            ========================================================================== -->
            <?php if ($tiene_registro_aplicante): ?>
                <form id="ga-form-perfil-empleado" class="ga-form-perfil" enctype="multipart/form-data">
                    <?php wp_nonce_field('ga_update_perfil_empleado', 'ga_perfil_nonce'); ?>
                    <input type="hidden" name="action" value="ga_update_perfil_empleado">
                    <input type="hidden" name="aplicante_id" value="<?php echo esc_attr($aplicante->id); ?>">

                    <div class="ga-perfil-grid">
                        <!-- =========================================================================
                             SECCIÓN 1: DATOS PERSONALES (EDITABLE)
                        ========================================================================== -->
                        <div class="ga-perfil-card ga-perfil-card-full">
                            <div class="ga-card-header">
                                <span class="dashicons dashicons-admin-users"></span>
                                <h3><?php esc_html_e('Datos Personales', 'gestionadmin-wolk'); ?></h3>
                                <span class="ga-badge ga-badge-edit">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php esc_html_e('Editable', 'gestionadmin-wolk'); ?>
                                </span>
                            </div>
                            <div class="ga-card-body">
                                <div class="ga-form-grid">
                                    <!-- Nombre completo -->
                                    <div class="ga-form-group ga-form-full">
                                        <label for="nombre_completo"><?php esc_html_e('Nombre Completo', 'gestionadmin-wolk'); ?> *</label>
                                        <input type="text" id="nombre_completo" name="nombre_completo"
                                               value="<?php echo esc_attr($datos_personales['nombre_completo']); ?>"
                                               class="ga-form-control" required>
                                    </div>

                                    <!-- Tipo y número de documento -->
                                    <div class="ga-form-group">
                                        <label for="documento_tipo"><?php esc_html_e('Tipo de Documento', 'gestionadmin-wolk'); ?></label>
                                        <select id="documento_tipo" name="documento_tipo" class="ga-form-control">
                                            <option value=""><?php esc_html_e('Seleccionar...', 'gestionadmin-wolk'); ?></option>
                                            <?php foreach ($tipos_documento as $key => $label): ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php selected($datos_personales['documento_tipo'], $key); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="ga-form-group">
                                        <label for="documento_numero"><?php esc_html_e('Número de Documento', 'gestionadmin-wolk'); ?></label>
                                        <input type="text" id="documento_numero" name="documento_numero"
                                               value="<?php echo esc_attr($datos_personales['documento_numero']); ?>"
                                               class="ga-form-control">
                                    </div>

                                    <!-- Teléfono -->
                                    <div class="ga-form-group">
                                        <label for="telefono"><?php esc_html_e('Teléfono', 'gestionadmin-wolk'); ?></label>
                                        <input type="tel" id="telefono" name="telefono"
                                               value="<?php echo esc_attr($datos_personales['telefono']); ?>"
                                               class="ga-form-control" placeholder="+57 300 123 4567">
                                    </div>

                                    <!-- País -->
                                    <div class="ga-form-group">
                                        <label for="pais"><?php esc_html_e('País', 'gestionadmin-wolk'); ?></label>
                                        <select id="pais" name="pais" class="ga-form-control">
                                            <option value=""><?php esc_html_e('Seleccionar...', 'gestionadmin-wolk'); ?></option>
                                            <?php foreach ($paises as $code => $name): ?>
                                                <option value="<?php echo esc_attr($code); ?>" <?php selected($datos_personales['pais'], $code); ?>>
                                                    <?php echo esc_html($name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Ciudad -->
                                    <div class="ga-form-group">
                                        <label for="ciudad"><?php esc_html_e('Ciudad', 'gestionadmin-wolk'); ?></label>
                                        <input type="text" id="ciudad" name="ciudad"
                                               value="<?php echo esc_attr($datos_personales['ciudad']); ?>"
                                               class="ga-form-control">
                                    </div>

                                    <!-- Dirección -->
                                    <div class="ga-form-group ga-form-full">
                                        <label for="direccion"><?php esc_html_e('Dirección Completa', 'gestionadmin-wolk'); ?></label>
                                        <textarea id="direccion" name="direccion" rows="2"
                                                  class="ga-form-control"><?php echo esc_textarea($datos_personales['direccion']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- =========================================================================
                             SECCIÓN 2: DOCUMENTOS (EDITABLE)
                        ========================================================================== -->
                        <div class="ga-perfil-card ga-perfil-card-full">
                            <div class="ga-card-header">
                                <span class="dashicons dashicons-media-document"></span>
                                <h3><?php esc_html_e('Documentos', 'gestionadmin-wolk'); ?></h3>
                                <span class="ga-badge ga-badge-edit">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php esc_html_e('Editable', 'gestionadmin-wolk'); ?>
                                </span>
                            </div>
                            <div class="ga-card-body">
                                <div class="ga-form-grid">
                                    <!-- Documento de identidad -->
                                    <div class="ga-form-group">
                                        <label for="documento_identidad"><?php esc_html_e('Documento de Identidad', 'gestionadmin-wolk'); ?></label>
                                        <div class="ga-file-upload">
                                            <input type="file" id="documento_identidad" name="documento_identidad"
                                                   accept=".pdf,.jpg,.jpeg,.png" class="ga-file-input">
                                            <label for="documento_identidad" class="ga-file-label">
                                                <span class="dashicons dashicons-upload"></span>
                                                <span><?php esc_html_e('Seleccionar archivo', 'gestionadmin-wolk'); ?></span>
                                            </label>
                                            <?php if (!empty($documentos['documento_identidad_url'])): ?>
                                                <div class="ga-file-current">
                                                    <span class="dashicons dashicons-yes-alt"></span>
                                                    <a href="<?php echo esc_url($documentos['documento_identidad_url']); ?>" target="_blank">
                                                        <?php esc_html_e('Ver documento actual', 'gestionadmin-wolk'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="ga-form-help"><?php esc_html_e('PDF, JPG o PNG. Máximo 5MB.', 'gestionadmin-wolk'); ?></p>
                                    </div>

                                    <!-- RUT/RFC -->
                                    <div class="ga-form-group">
                                        <label for="rut"><?php esc_html_e('RUT / RFC / Documento Fiscal', 'gestionadmin-wolk'); ?></label>
                                        <div class="ga-file-upload">
                                            <input type="file" id="rut" name="rut"
                                                   accept=".pdf,.jpg,.jpeg,.png" class="ga-file-input">
                                            <label for="rut" class="ga-file-label">
                                                <span class="dashicons dashicons-upload"></span>
                                                <span><?php esc_html_e('Seleccionar archivo', 'gestionadmin-wolk'); ?></span>
                                            </label>
                                            <?php if (!empty($documentos['rut_url'])): ?>
                                                <div class="ga-file-current">
                                                    <span class="dashicons dashicons-yes-alt"></span>
                                                    <a href="<?php echo esc_url($documentos['rut_url']); ?>" target="_blank">
                                                        <?php esc_html_e('Ver documento actual', 'gestionadmin-wolk'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Certificado bancario -->
                                    <div class="ga-form-group">
                                        <label for="certificado_bancario"><?php esc_html_e('Certificación Bancaria', 'gestionadmin-wolk'); ?></label>
                                        <div class="ga-file-upload">
                                            <input type="file" id="certificado_bancario" name="certificado_bancario"
                                                   accept=".pdf,.jpg,.jpeg,.png" class="ga-file-input">
                                            <label for="certificado_bancario" class="ga-file-label">
                                                <span class="dashicons dashicons-upload"></span>
                                                <span><?php esc_html_e('Seleccionar archivo', 'gestionadmin-wolk'); ?></span>
                                            </label>
                                            <?php if (!empty($documentos['certificado_bancario_url'])): ?>
                                                <div class="ga-file-current">
                                                    <span class="dashicons dashicons-yes-alt"></span>
                                                    <a href="<?php echo esc_url($documentos['certificado_bancario_url']); ?>" target="_blank">
                                                        <?php esc_html_e('Ver documento actual', 'gestionadmin-wolk'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- CV -->
                                    <div class="ga-form-group">
                                        <label for="cv"><?php esc_html_e('Hoja de Vida / CV', 'gestionadmin-wolk'); ?></label>
                                        <div class="ga-file-upload">
                                            <input type="file" id="cv" name="cv"
                                                   accept=".pdf,.doc,.docx" class="ga-file-input">
                                            <label for="cv" class="ga-file-label">
                                                <span class="dashicons dashicons-upload"></span>
                                                <span><?php esc_html_e('Seleccionar archivo', 'gestionadmin-wolk'); ?></span>
                                            </label>
                                            <?php if (!empty($documentos['cv_url'])): ?>
                                                <div class="ga-file-current">
                                                    <span class="dashicons dashicons-yes-alt"></span>
                                                    <a href="<?php echo esc_url($documentos['cv_url']); ?>" target="_blank">
                                                        <?php esc_html_e('Ver CV actual', 'gestionadmin-wolk'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="ga-form-help"><?php esc_html_e('PDF, DOC o DOCX. Máximo 5MB.', 'gestionadmin-wolk'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- =========================================================================
                             SECCIÓN 3: MÉTODO DE PAGO (EDITABLE)
                        ========================================================================== -->
                        <div class="ga-perfil-card ga-perfil-card-full">
                            <div class="ga-card-header">
                                <span class="dashicons dashicons-bank"></span>
                                <h3><?php esc_html_e('Método de Pago', 'gestionadmin-wolk'); ?></h3>
                                <span class="ga-badge ga-badge-edit">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php esc_html_e('Editable', 'gestionadmin-wolk'); ?>
                                </span>
                            </div>
                            <div class="ga-card-body">
                                <div class="ga-form-grid">
                                    <!-- Método preferido -->
                                    <div class="ga-form-group ga-form-full">
                                        <label for="metodo_pago_preferido"><?php esc_html_e('Método de Pago Preferido', 'gestionadmin-wolk'); ?> *</label>
                                        <select id="metodo_pago_preferido" name="metodo_pago_preferido" class="ga-form-control" required>
                                            <?php foreach ($metodos_pago_opciones as $key => $label): ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php selected($metodo_pago['metodo_pago_preferido'], $key); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Campos dinámicos según método de pago -->
                                    <div id="ga-pago-fields" class="ga-form-full">
                                        <!-- BINANCE -->
                                        <div class="ga-pago-section" data-method="BINANCE" style="display: none;">
                                            <h4><?php esc_html_e('Datos de Binance Pay', 'gestionadmin-wolk'); ?></h4>
                                            <div class="ga-form-grid">
                                                <div class="ga-form-group">
                                                    <label for="binance_email"><?php esc_html_e('Email Binance', 'gestionadmin-wolk'); ?></label>
                                                    <input type="email" id="binance_email" name="binance_email"
                                                           class="ga-form-control" placeholder="usuario@email.com">
                                                </div>
                                                <div class="ga-form-group">
                                                    <label for="binance_id"><?php esc_html_e('Binance Pay ID', 'gestionadmin-wolk'); ?></label>
                                                    <input type="text" id="binance_id" name="binance_id"
                                                           class="ga-form-control" placeholder="123456789">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- WISE -->
                                        <div class="ga-pago-section" data-method="WISE" style="display: none;">
                                            <h4><?php esc_html_e('Datos de Wise', 'gestionadmin-wolk'); ?></h4>
                                            <div class="ga-form-grid">
                                                <div class="ga-form-group">
                                                    <label for="wise_email"><?php esc_html_e('Email Wise', 'gestionadmin-wolk'); ?></label>
                                                    <input type="email" id="wise_email" name="wise_email"
                                                           class="ga-form-control" placeholder="usuario@email.com">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PAYPAL -->
                                        <div class="ga-pago-section" data-method="PAYPAL" style="display: none;">
                                            <h4><?php esc_html_e('Datos de PayPal', 'gestionadmin-wolk'); ?></h4>
                                            <div class="ga-form-grid">
                                                <div class="ga-form-group ga-form-full">
                                                    <label for="paypal_email"><?php esc_html_e('Email PayPal', 'gestionadmin-wolk'); ?></label>
                                                    <input type="email" id="paypal_email" name="paypal_email"
                                                           class="ga-form-control" placeholder="usuario@email.com">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PAYONEER -->
                                        <div class="ga-pago-section" data-method="PAYONEER" style="display: none;">
                                            <h4><?php esc_html_e('Datos de Payoneer', 'gestionadmin-wolk'); ?></h4>
                                            <div class="ga-form-grid">
                                                <div class="ga-form-group ga-form-full">
                                                    <label for="payoneer_email"><?php esc_html_e('Email Payoneer', 'gestionadmin-wolk'); ?></label>
                                                    <input type="email" id="payoneer_email" name="payoneer_email"
                                                           class="ga-form-control" placeholder="usuario@email.com">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- STRIPE -->
                                        <div class="ga-pago-section" data-method="STRIPE" style="display: none;">
                                            <h4><?php esc_html_e('Datos de Stripe', 'gestionadmin-wolk'); ?></h4>
                                            <div class="ga-form-grid">
                                                <div class="ga-form-group ga-form-full">
                                                    <label for="stripe_email"><?php esc_html_e('Email Stripe', 'gestionadmin-wolk'); ?></label>
                                                    <input type="email" id="stripe_email" name="stripe_email"
                                                           class="ga-form-control" placeholder="usuario@email.com">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- TRANSFERENCIA BANCARIA -->
                                        <div class="ga-pago-section" data-method="TRANSFERENCIA" style="display: none;">
                                            <h4><?php esc_html_e('Datos Bancarios', 'gestionadmin-wolk'); ?></h4>
                                            <div class="ga-form-grid">
                                                <div class="ga-form-group">
                                                    <label for="banco_nombre"><?php esc_html_e('Banco', 'gestionadmin-wolk'); ?></label>
                                                    <input type="text" id="banco_nombre" name="banco_nombre"
                                                           class="ga-form-control" placeholder="<?php esc_attr_e('Nombre del banco', 'gestionadmin-wolk'); ?>">
                                                </div>
                                                <div class="ga-form-group">
                                                    <label for="banco_tipo_cuenta"><?php esc_html_e('Tipo de Cuenta', 'gestionadmin-wolk'); ?></label>
                                                    <select id="banco_tipo_cuenta" name="banco_tipo_cuenta" class="ga-form-control">
                                                        <option value=""><?php esc_html_e('Seleccionar...', 'gestionadmin-wolk'); ?></option>
                                                        <option value="AHORROS"><?php esc_html_e('Ahorros', 'gestionadmin-wolk'); ?></option>
                                                        <option value="CORRIENTE"><?php esc_html_e('Corriente', 'gestionadmin-wolk'); ?></option>
                                                    </select>
                                                </div>
                                                <div class="ga-form-group">
                                                    <label for="banco_numero_cuenta"><?php esc_html_e('Número de Cuenta', 'gestionadmin-wolk'); ?></label>
                                                    <input type="text" id="banco_numero_cuenta" name="banco_numero_cuenta"
                                                           class="ga-form-control" placeholder="0000000000">
                                                </div>
                                                <div class="ga-form-group">
                                                    <label for="banco_titular"><?php esc_html_e('Titular de la Cuenta', 'gestionadmin-wolk'); ?></label>
                                                    <input type="text" id="banco_titular" name="banco_titular"
                                                           class="ga-form-control" placeholder="<?php esc_attr_e('Nombre como aparece en el banco', 'gestionadmin-wolk'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ga-pago-note">
                                    <span class="dashicons dashicons-shield"></span>
                                    <p><?php esc_html_e('Tus datos de pago están protegidos y solo serán utilizados para procesar tus pagos.', 'gestionadmin-wolk'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- =========================================================================
                             SECCIÓN 4: DATOS LABORALES (SOLO LECTURA)
                        ========================================================================== -->
                        <div class="ga-perfil-card">
                            <div class="ga-card-header">
                                <span class="dashicons dashicons-portfolio"></span>
                                <h3><?php esc_html_e('Información Laboral', 'gestionadmin-wolk'); ?></h3>
                                <span class="ga-badge ga-badge-readonly">
                                    <span class="dashicons dashicons-lock"></span>
                                    <?php esc_html_e('Solo lectura', 'gestionadmin-wolk'); ?>
                                </span>
                            </div>
                            <div class="ga-card-body">
                                <div class="ga-info-row">
                                    <span class="ga-info-label"><?php esc_html_e('Código Empleado', 'gestionadmin-wolk'); ?></span>
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
                                    <span class="ga-info-label"><?php esc_html_e('Nivel Jerárquico', 'gestionadmin-wolk'); ?></span>
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
                                    <span class="ga-info-label"><?php esc_html_e('Antigüedad', 'gestionadmin-wolk'); ?></span>
                                    <span class="ga-info-value ga-info-highlight">
                                        <?php echo esc_html($antiguedad_texto); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- =========================================================================
                             TARIFA Y COMPENSACIÓN (SOLO LECTURA)
                        ========================================================================== -->
                        <div class="ga-perfil-card">
                            <div class="ga-card-header">
                                <span class="dashicons dashicons-money-alt"></span>
                                <h3><?php esc_html_e('Tarifa y Compensación', 'gestionadmin-wolk'); ?></h3>
                                <span class="ga-badge ga-badge-readonly">
                                    <span class="dashicons dashicons-lock"></span>
                                    <?php esc_html_e('Solo lectura', 'gestionadmin-wolk'); ?>
                                </span>
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
                                            <span class="ga-info-label"><?php esc_html_e('Rango de Antigüedad', 'gestionadmin-wolk'); ?></span>
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
                    </div>

                    <!-- =========================================================================
                         BOTÓN GUARDAR
                    ========================================================================== -->
                    <div class="ga-form-actions">
                        <button type="submit" class="ga-btn ga-btn-primary ga-btn-large" id="ga-btn-guardar">
                            <span class="dashicons dashicons-saved"></span>
                            <?php esc_html_e('Guardar Cambios', 'gestionadmin-wolk'); ?>
                        </button>
                    </div>
                </form>

            <?php else: ?>
                <!-- =========================================================================
                     SIN REGISTRO DE APLICANTE - SOLO VISTA DE DATOS LABORALES
                ========================================================================== -->
                <div class="ga-perfil-grid">
                    <!-- Mensaje informativo -->
                    <div class="ga-perfil-card ga-perfil-card-full">
                        <div class="ga-notice ga-notice-info">
                            <span class="dashicons dashicons-info-outline"></span>
                            <div>
                                <strong><?php esc_html_e('Perfil básico', 'gestionadmin-wolk'); ?></strong>
                                <p><?php esc_html_e('Tu cuenta fue creada directamente por RRHH. Si necesitas actualizar tu información personal, documentos o método de pago, por favor contacta al departamento de Recursos Humanos.', 'gestionadmin-wolk'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información Laboral -->
                    <div class="ga-perfil-card">
                        <div class="ga-card-header">
                            <span class="dashicons dashicons-portfolio"></span>
                            <h3><?php esc_html_e('Información Laboral', 'gestionadmin-wolk'); ?></h3>
                        </div>
                        <div class="ga-card-body">
                            <div class="ga-info-row">
                                <span class="ga-info-label"><?php esc_html_e('Código Empleado', 'gestionadmin-wolk'); ?></span>
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
                                <span class="ga-info-label"><?php esc_html_e('Nivel Jerárquico', 'gestionadmin-wolk'); ?></span>
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
                                <span class="ga-info-label"><?php esc_html_e('Antigüedad', 'gestionadmin-wolk'); ?></span>
                                <span class="ga-info-value ga-info-highlight">
                                    <?php echo esc_html($antiguedad_texto); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tarifa y Compensación -->
                    <div class="ga-perfil-card">
                        <div class="ga-card-header">
                            <span class="dashicons dashicons-money-alt"></span>
                            <h3><?php esc_html_e('Tarifa y Compensación', 'gestionadmin-wolk'); ?></h3>
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
                            <?php else: ?>
                                <div class="ga-no-data">
                                    <span class="dashicons dashicons-info"></span>
                                    <p><?php esc_html_e('Tarifa no configurada. Contacta a RRHH.', 'gestionadmin-wolk'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- =========================================================================
                 SEGURIDAD (siempre visible)
            ========================================================================== -->
            <div class="ga-perfil-card ga-perfil-security">
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
                            <?php esc_html_e('Cambiar Contraseña', 'gestionadmin-wolk'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- =========================================================================
                 NOTA INFORMATIVA
            ========================================================================== -->
            <div class="ga-perfil-note">
                <span class="dashicons dashicons-info-outline"></span>
                <p>
                    <?php esc_html_e('Los datos laborales (departamento, puesto, tarifa, etc.) son gestionados por el departamento de Recursos Humanos. Si necesitas actualizar esta información, por favor contacta a tu supervisor o al equipo de RRHH.', 'gestionadmin-wolk'); ?>
                </p>
            </div>
        </div>

        <div class="ga-portal-footer">
            <p>
                <?php esc_html_e('Diseñado y desarrollado por', 'gestionadmin-wolk'); ?>
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

/* Navegación */
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

/* Messages */
.ga-messages {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ga-messages.ga-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.ga-messages.ga-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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

/* Badges */
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
.ga-badge-edit {
    background: #e3f2fd;
    color: #1565c0;
}
.ga-badge-readonly {
    background: #f5f5f5;
    color: #666;
}

/* Grid de tarjetas */
.ga-perfil-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

/* Tarjetas */
.ga-perfil-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}
.ga-perfil-card-full {
    grid-column: 1 / -1;
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
    flex-grow: 1;
}
.ga-card-body {
    padding: 20px;
}

/* Formulario */
.ga-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
.ga-form-group {
    margin-bottom: 0;
}
.ga-form-group.ga-form-full {
    grid-column: 1 / -1;
}
.ga-form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #555;
}
.ga-form-control {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.ga-form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.ga-form-control::placeholder {
    color: #aaa;
}
.ga-form-help {
    margin: 6px 0 0;
    font-size: 12px;
    color: #888;
}

/* File Upload */
.ga-file-upload {
    position: relative;
}
.ga-file-input {
    position: absolute;
    width: 0;
    height: 0;
    opacity: 0;
}
.ga-file-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: #f5f7fa;
    border: 2px dashed #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.ga-file-label:hover {
    border-color: #667eea;
    background: #f0f4ff;
}
.ga-file-label .dashicons {
    color: #667eea;
}
.ga-file-current {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 13px;
    color: #28a745;
}
.ga-file-current a {
    color: #667eea;
    text-decoration: none;
}
.ga-file-current a:hover {
    text-decoration: underline;
}

/* Pago sections */
.ga-pago-section {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}
.ga-pago-section h4 {
    margin: 0 0 15px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}
.ga-pago-note {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 20px;
    padding: 12px 15px;
    background: #e8f4fd;
    border-radius: 8px;
    font-size: 13px;
    color: #1565c0;
}
.ga-pago-note .dashicons {
    flex-shrink: 0;
    margin-top: 2px;
}
.ga-pago-note p {
    margin: 0;
}

/* Filas de información (solo lectura) */
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

/* Notice */
.ga-notice {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
}
.ga-notice .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    flex-shrink: 0;
    margin-top: 2px;
}
.ga-notice-info {
    background: #e3f2fd;
    color: #1565c0;
}
.ga-notice strong {
    display: block;
    margin-bottom: 5px;
}
.ga-notice p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}

/* Security Action */
.ga-security-action {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: center;
}

/* Form Actions */
.ga-form-actions {
    text-align: center;
    padding: 30px 0;
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
.ga-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.ga-btn-primary:hover {
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transform: translateY(-1px);
}
.ga-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
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
.ga-btn-large {
    padding: 14px 30px;
    font-size: 16px;
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

/* Security Card */
.ga-perfil-security {
    margin-top: 20px;
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
    .ga-form-grid {
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

<script>
jQuery(document).ready(function($) {
    // =========================================================================
    // TOGGLE DE SECCIONES DE PAGO
    // =========================================================================
    function togglePagoFields() {
        var metodo = $('#metodo_pago_preferido').val();
        $('.ga-pago-section').hide();
        $('.ga-pago-section[data-method="' + metodo + '"]').show();
    }

    // Inicializar al cargar
    togglePagoFields();

    // Cambio de método de pago
    $('#metodo_pago_preferido').on('change', togglePagoFields);

    // =========================================================================
    // MOSTRAR NOMBRE DE ARCHIVO SELECCIONADO
    // =========================================================================
    $('.ga-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.ga-file-label').find('span:last').text(fileName);
        }
    });

    // =========================================================================
    // ENVÍO DEL FORMULARIO
    // =========================================================================
    $('#ga-form-perfil-empleado').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#ga-btn-guardar');
        var $messages = $('#ga-perfil-messages');
        var formData = new FormData(this);

        // Deshabilitar botón
        $btn.prop('disabled', true).find('span:last').text('<?php echo esc_js(__('Guardando...', 'gestionadmin-wolk')); ?>');
        $messages.hide();

        $.ajax({
            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $messages.removeClass('ga-error').addClass('ga-success')
                        .html('<span class="dashicons dashicons-yes-alt"></span>' + response.data.message)
                        .show();

                    // Scroll al mensaje
                    $('html, body').animate({
                        scrollTop: $messages.offset().top - 100
                    }, 300);

                    // Actualizar nombre en la tarjeta principal si cambió
                    if (formData.get('nombre_completo')) {
                        $('.ga-perfil-nombre').text(formData.get('nombre_completo'));
                    }
                } else {
                    $messages.removeClass('ga-success').addClass('ga-error')
                        .html('<span class="dashicons dashicons-warning"></span>' + response.data.message)
                        .show();
                }
            },
            error: function() {
                $messages.removeClass('ga-success').addClass('ga-error')
                    .html('<span class="dashicons dashicons-warning"></span><?php echo esc_js(__('Error de conexión. Inténtalo de nuevo.', 'gestionadmin-wolk')); ?>')
                    .show();
            },
            complete: function() {
                $btn.prop('disabled', false).find('span:last').text('<?php echo esc_js(__('Guardar Cambios', 'gestionadmin-wolk')); ?>');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
