<?php
/**
 * Template: Mi Perfil
 *
 * Permite al aplicante ver y editar su información de perfil.
 * Incluye datos personales, habilidades, experiencia y método de pago.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalAplicante
 * @since      1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar que el usuario está logueado
if (!is_user_logged_in()) {
    include GA_PLUGIN_DIR . 'templates/portal-aplicante/login-required.php';
    return;
}

// Obtener aplicante actual
$ga_public = GA_Public::get_instance();
$aplicante = $ga_public->get_current_aplicante();

// Verificar que es un aplicante registrado
if (!$aplicante) {
    include GA_PLUGIN_DIR . 'templates/portal-aplicante/no-aplicante.php';
    return;
}

// Usuario de WordPress asociado
$wp_user = get_userdata($aplicante->user_id);

// Constantes para formularios
$aplicantes_module = GA_Aplicantes::get_instance();
$tipos = $aplicantes_module::TIPOS;
$metodos_pago = $aplicantes_module::METODOS_PAGO;
$niveles = $aplicantes_module::NIVELES;

// Decodificar datos JSON
$habilidades = !empty($aplicante->habilidades) ? json_decode($aplicante->habilidades, true) : array();
$experiencia = !empty($aplicante->experiencia) ? json_decode($aplicante->experiencia, true) : array();
$portfolio = !empty($aplicante->portfolio) ? json_decode($aplicante->portfolio, true) : array();

// Mensajes de éxito/error
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_perfil_nonce'])) {
    if (wp_verify_nonce($_POST['ga_perfil_nonce'], 'ga_actualizar_perfil')) {
        // Recoger datos del formulario
        $datos = array(
            'id'              => $aplicante->id,
            'nombre'          => sanitize_text_field($_POST['nombre'] ?? ''),
            'telefono'        => sanitize_text_field($_POST['telefono'] ?? ''),
            'pais'            => sanitize_text_field($_POST['pais'] ?? ''),
            'ciudad'          => sanitize_text_field($_POST['ciudad'] ?? ''),
            'direccion'       => sanitize_textarea_field($_POST['direccion'] ?? ''),
            'especialidad'    => sanitize_text_field($_POST['especialidad'] ?? ''),
            'tarifa_hora'     => floatval($_POST['tarifa_hora'] ?? 0),
            'disponibilidad'  => sanitize_text_field($_POST['disponibilidad'] ?? ''),
            'bio'             => sanitize_textarea_field($_POST['bio'] ?? ''),
            'metodo_pago'     => sanitize_text_field($_POST['metodo_pago'] ?? ''),
            'datos_pago'      => sanitize_textarea_field($_POST['datos_pago'] ?? ''),
            'nivel'           => sanitize_text_field($_POST['nivel'] ?? 'junior')
        );

        // Habilidades (textarea separado por comas)
        if (isset($_POST['habilidades'])) {
            $hab_array = array_map('trim', explode(',', sanitize_text_field($_POST['habilidades'])));
            $hab_array = array_filter($hab_array);
            $datos['habilidades'] = wp_json_encode(array_values($hab_array));
        }

        // Guardar
        $resultado = $aplicantes_module->save($datos);

        if (is_wp_error($resultado)) {
            $mensaje = $resultado->get_error_message();
            $tipo_mensaje = 'danger';
        } else {
            $mensaje = __('Perfil actualizado correctamente.', 'gestionadmin-wolk');
            $tipo_mensaje = 'success';

            // Recargar datos del aplicante
            $aplicante = $aplicantes_module->get($aplicante->id);
            $habilidades = !empty($aplicante->habilidades) ? json_decode($aplicante->habilidades, true) : array();
        }
    } else {
        $mensaje = __('Error de seguridad. Por favor, recarga la página.', 'gestionadmin-wolk');
        $tipo_mensaje = 'danger';
    }
}

get_header();
?>

<div class="ga-public-container ga-perfil-page">
    <div class="ga-container">

        <!-- Navegación del Dashboard -->
        <nav class="ga-dashboard-nav">
            <a href="<?php echo esc_url($ga_public->get_cuenta_url()); ?>">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($ga_public->get_cuenta_url('aplicaciones')); ?>">
                <span class="dashicons dashicons-portfolio"></span>
                <?php esc_html_e('Mis Aplicaciones', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($ga_public->get_cuenta_url('perfil')); ?>" class="active">
                <span class="dashicons dashicons-admin-users"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($ga_public->get_trabajo_url()); ?>">
                <span class="dashicons dashicons-search"></span>
                <?php esc_html_e('Buscar Trabajo', 'gestionadmin-wolk'); ?>
            </a>
        </nav>

        <!-- Mensajes -->
        <?php if ($mensaje) : ?>
            <div class="ga-alert ga-alert-<?php echo esc_attr($tipo_mensaje); ?>">
                <?php echo esc_html($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Header del Perfil -->
        <div class="ga-perfil-header">
            <div class="ga-perfil-avatar">
                <?php echo get_avatar($aplicante->user_id, 120); ?>
            </div>
            <div class="ga-perfil-info">
                <h1><?php echo esc_html($aplicante->nombre); ?></h1>
                <p class="email"><?php echo esc_html($aplicante->email); ?></p>
                <div class="ga-perfil-badges">
                    <span class="ga-badge ga-badge-<?php echo $aplicante->estado === 'verificado' ? 'success' : 'warning'; ?>">
                        <?php echo $aplicante->estado === 'verificado'
                            ? esc_html__('Verificado', 'gestionadmin-wolk')
                            : esc_html__('Pendiente de verificación', 'gestionadmin-wolk'); ?>
                    </span>
                    <span class="ga-badge ga-badge-secondary">
                        <?php echo esc_html(ucfirst($aplicante->tipo)); ?>
                    </span>
                    <?php if ($aplicante->nivel) : ?>
                        <span class="ga-badge ga-badge-primary">
                            <?php echo esc_html(ucfirst($aplicante->nivel)); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Formulario de Perfil -->
        <form method="post" id="ga-form-perfil" class="ga-perfil-form">
            <?php wp_nonce_field('ga_actualizar_perfil', 'ga_perfil_nonce'); ?>

            <!-- Información Personal -->
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Información Personal', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-card-body">
                    <div class="ga-form-row">
                        <div class="ga-form-group">
                            <label for="nombre">
                                <?php esc_html_e('Nombre completo', 'gestionadmin-wolk'); ?>
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   id="nombre"
                                   name="nombre"
                                   class="ga-form-control"
                                   value="<?php echo esc_attr($aplicante->nombre); ?>"
                                   required>
                        </div>

                        <div class="ga-form-group">
                            <label for="email">
                                <?php esc_html_e('Email', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="email"
                                   id="email"
                                   class="ga-form-control"
                                   value="<?php echo esc_attr($aplicante->email); ?>"
                                   disabled>
                            <span class="ga-form-hint">
                                <?php esc_html_e('El email no se puede cambiar desde aquí.', 'gestionadmin-wolk'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="ga-form-row">
                        <div class="ga-form-group">
                            <label for="telefono">
                                <?php esc_html_e('Teléfono', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="tel"
                                   id="telefono"
                                   name="telefono"
                                   class="ga-form-control"
                                   value="<?php echo esc_attr($aplicante->telefono); ?>"
                                   placeholder="+1 234 567 8900">
                        </div>

                        <div class="ga-form-group">
                            <label for="pais">
                                <?php esc_html_e('País', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="pais" name="pais" class="ga-form-control">
                                <option value=""><?php esc_html_e('Seleccionar país', 'gestionadmin-wolk'); ?></option>
                                <option value="CO" <?php selected($aplicante->pais, 'CO'); ?>>Colombia</option>
                                <option value="MX" <?php selected($aplicante->pais, 'MX'); ?>>México</option>
                                <option value="US" <?php selected($aplicante->pais, 'US'); ?>>Estados Unidos</option>
                                <option value="ES" <?php selected($aplicante->pais, 'ES'); ?>>España</option>
                                <option value="AR" <?php selected($aplicante->pais, 'AR'); ?>>Argentina</option>
                                <option value="CL" <?php selected($aplicante->pais, 'CL'); ?>>Chile</option>
                                <option value="PE" <?php selected($aplicante->pais, 'PE'); ?>>Perú</option>
                                <option value="EC" <?php selected($aplicante->pais, 'EC'); ?>>Ecuador</option>
                                <option value="VE" <?php selected($aplicante->pais, 'VE'); ?>>Venezuela</option>
                                <option value="OTHER" <?php selected($aplicante->pais, 'OTHER'); ?>>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="ga-form-row">
                        <div class="ga-form-group">
                            <label for="ciudad">
                                <?php esc_html_e('Ciudad', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="text"
                                   id="ciudad"
                                   name="ciudad"
                                   class="ga-form-control"
                                   value="<?php echo esc_attr($aplicante->ciudad); ?>">
                        </div>

                        <div class="ga-form-group">
                            <label for="direccion">
                                <?php esc_html_e('Dirección', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="text"
                                   id="direccion"
                                   name="direccion"
                                   class="ga-form-control"
                                   value="<?php echo esc_attr($aplicante->direccion); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Profesional -->
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Información Profesional', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-card-body">
                    <div class="ga-form-row">
                        <div class="ga-form-group">
                            <label for="especialidad">
                                <?php esc_html_e('Especialidad / Profesión', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="text"
                                   id="especialidad"
                                   name="especialidad"
                                   class="ga-form-control"
                                   value="<?php echo esc_attr($aplicante->especialidad); ?>"
                                   placeholder="<?php esc_attr_e('Ej: Desarrollador Web, Diseñador, Contador', 'gestionadmin-wolk'); ?>">
                        </div>

                        <div class="ga-form-group">
                            <label for="nivel">
                                <?php esc_html_e('Nivel de experiencia', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="nivel" name="nivel" class="ga-form-control">
                                <?php foreach ($niveles as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"
                                            <?php selected($aplicante->nivel, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="ga-form-row">
                        <div class="ga-form-group">
                            <label for="tarifa_hora">
                                <?php esc_html_e('Tarifa por hora (USD)', 'gestionadmin-wolk'); ?>
                            </label>
                            <input type="number"
                                   id="tarifa_hora"
                                   name="tarifa_hora"
                                   class="ga-form-control"
                                   value="<?php echo esc_attr($aplicante->tarifa_hora); ?>"
                                   min="0"
                                   step="0.01"
                                   placeholder="0.00">
                        </div>

                        <div class="ga-form-group">
                            <label for="disponibilidad">
                                <?php esc_html_e('Disponibilidad', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="disponibilidad" name="disponibilidad" class="ga-form-control">
                                <option value="tiempo_completo" <?php selected($aplicante->disponibilidad, 'tiempo_completo'); ?>>
                                    <?php esc_html_e('Tiempo completo', 'gestionadmin-wolk'); ?>
                                </option>
                                <option value="medio_tiempo" <?php selected($aplicante->disponibilidad, 'medio_tiempo'); ?>>
                                    <?php esc_html_e('Medio tiempo', 'gestionadmin-wolk'); ?>
                                </option>
                                <option value="por_proyecto" <?php selected($aplicante->disponibilidad, 'por_proyecto'); ?>>
                                    <?php esc_html_e('Por proyecto', 'gestionadmin-wolk'); ?>
                                </option>
                                <option value="fines_semana" <?php selected($aplicante->disponibilidad, 'fines_semana'); ?>>
                                    <?php esc_html_e('Fines de semana', 'gestionadmin-wolk'); ?>
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label for="habilidades">
                            <?php esc_html_e('Habilidades', 'gestionadmin-wolk'); ?>
                        </label>
                        <input type="text"
                               id="habilidades"
                               name="habilidades"
                               class="ga-form-control"
                               value="<?php echo esc_attr(implode(', ', $habilidades)); ?>"
                               placeholder="<?php esc_attr_e('PHP, JavaScript, WordPress, Excel (separadas por coma)', 'gestionadmin-wolk'); ?>">
                        <span class="ga-form-hint">
                            <?php esc_html_e('Separa cada habilidad con una coma', 'gestionadmin-wolk'); ?>
                        </span>
                    </div>

                    <?php if (!empty($habilidades)) : ?>
                        <div class="ga-habilidades-preview">
                            <?php foreach ($habilidades as $hab) : ?>
                                <span class="ga-habilidad-tag"><?php echo esc_html($hab); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="ga-form-group">
                        <label for="bio">
                            <?php esc_html_e('Biografía / Acerca de ti', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="bio"
                                  name="bio"
                                  class="ga-form-control"
                                  rows="4"
                                  placeholder="<?php esc_attr_e('Cuéntanos sobre tu experiencia y qué tipo de trabajo buscas...', 'gestionadmin-wolk'); ?>"><?php echo esc_textarea($aplicante->bio); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Información de Pago -->
            <div class="ga-card">
                <div class="ga-card-header">
                    <h2><?php esc_html_e('Información de Pago', 'gestionadmin-wolk'); ?></h2>
                </div>
                <div class="ga-card-body">
                    <div class="ga-alert ga-alert-info">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('Esta información se usará para procesarte pagos cuando completes trabajos.', 'gestionadmin-wolk'); ?>
                    </div>

                    <div class="ga-form-row">
                        <div class="ga-form-group">
                            <label for="metodo_pago">
                                <?php esc_html_e('Método de pago preferido', 'gestionadmin-wolk'); ?>
                            </label>
                            <select id="metodo_pago" name="metodo_pago" class="ga-form-control">
                                <option value=""><?php esc_html_e('Seleccionar método', 'gestionadmin-wolk'); ?></option>
                                <?php foreach ($metodos_pago as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"
                                            <?php selected($aplicante->metodo_pago, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="ga-form-group">
                        <label for="datos_pago">
                            <?php esc_html_e('Datos de pago', 'gestionadmin-wolk'); ?>
                        </label>
                        <textarea id="datos_pago"
                                  name="datos_pago"
                                  class="ga-form-control"
                                  rows="3"
                                  placeholder="<?php esc_attr_e('Email de PayPal, dirección de wallet, número de cuenta, etc.', 'gestionadmin-wolk'); ?>"><?php echo esc_textarea($aplicante->datos_pago); ?></textarea>
                        <span class="ga-form-hint">
                            <?php esc_html_e('Proporciona la información necesaria según tu método de pago elegido.', 'gestionadmin-wolk'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="ga-form-actions">
                <button type="submit" class="ga-btn ga-btn-primary ga-btn-lg">
                    <span class="dashicons dashicons-saved"></span>
                    <?php esc_html_e('Guardar Cambios', 'gestionadmin-wolk'); ?>
                </button>

                <a href="<?php echo esc_url($ga_public->get_cuenta_url()); ?>" class="ga-btn ga-btn-secondary ga-btn-lg">
                    <?php esc_html_e('Cancelar', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </form>

        <!-- Estadísticas del Perfil -->
        <div class="ga-card ga-mt-xl">
            <div class="ga-card-header">
                <h2><?php esc_html_e('Estadísticas de tu Perfil', 'gestionadmin-wolk'); ?></h2>
            </div>
            <div class="ga-card-body">
                <div class="ga-perfil-stats">
                    <div class="ga-perfil-stat">
                        <span class="number"><?php echo esc_html($aplicante->total_aplicaciones ?? 0); ?></span>
                        <span class="label"><?php esc_html_e('Aplicaciones', 'gestionadmin-wolk'); ?></span>
                    </div>
                    <div class="ga-perfil-stat">
                        <span class="number"><?php echo esc_html($aplicante->trabajos_completados ?? 0); ?></span>
                        <span class="label"><?php esc_html_e('Trabajos Completados', 'gestionadmin-wolk'); ?></span>
                    </div>
                    <div class="ga-perfil-stat">
                        <span class="number">
                            <?php
                            $rating = floatval($aplicante->calificacion_promedio ?? 0);
                            echo $rating > 0 ? number_format($rating, 1) : '-';
                            ?>
                        </span>
                        <span class="label"><?php esc_html_e('Calificación', 'gestionadmin-wolk'); ?></span>
                    </div>
                    <div class="ga-perfil-stat">
                        <span class="number">
                            <?php
                            $fecha = new DateTime($aplicante->fecha_registro);
                            echo esc_html($fecha->format('M Y'));
                            ?>
                        </span>
                        <span class="label"><?php esc_html_e('Miembro desde', 'gestionadmin-wolk'); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* Estilos adicionales para el perfil */
.ga-perfil-page .ga-perfil-header {
    margin-bottom: var(--ga-spacing-xl);
}

.ga-perfil-form .ga-card {
    margin-bottom: var(--ga-spacing-lg);
}

.ga-habilidades-preview {
    display: flex;
    flex-wrap: wrap;
    gap: var(--ga-spacing-sm);
    margin-top: var(--ga-spacing-sm);
}

.ga-form-actions {
    display: flex;
    gap: var(--ga-spacing-md);
    margin-top: var(--ga-spacing-xl);
}

.ga-form-actions .ga-btn {
    min-width: 180px;
}

.ga-form-actions .ga-btn .dashicons {
    margin-right: var(--ga-spacing-xs);
}

.ga-perfil-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--ga-spacing-lg);
    text-align: center;
}

.ga-perfil-stat {
    padding: var(--ga-spacing-lg);
    background: var(--ga-bg-light);
    border-radius: var(--ga-radius-md);
}

.ga-perfil-stat .number {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--ga-primary);
    line-height: 1;
    margin-bottom: var(--ga-spacing-sm);
}

.ga-perfil-stat .label {
    font-size: 13px;
    color: var(--ga-text-muted);
}

@media (max-width: 768px) {
    .ga-form-actions {
        flex-direction: column;
    }

    .ga-form-actions .ga-btn {
        width: 100%;
        justify-content: center;
    }

    .ga-perfil-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php get_footer(); ?>
