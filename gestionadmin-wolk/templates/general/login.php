<?php
/**
 * Template: Login GestionAdmin
 *
 * Página de login personalizada para el sistema.
 * Permite login a todos los tipos de usuarios: aplicantes, empleados, clientes.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/General
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Si ya está logueado, redirigir
if (is_user_logged_in()) {
    $redirect_to = home_url('/mi-cuenta/');

    // Detectar tipo de usuario para redirigir al portal correcto
    $user = wp_get_current_user();
    if (in_array('ga_cliente', (array) $user->roles)) {
        $redirect_to = home_url('/cliente/');
    } elseif (in_array('ga_empleado', (array) $user->roles) ||
              in_array('ga_jefe', (array) $user->roles) ||
              in_array('ga_director', (array) $user->roles)) {
        $redirect_to = home_url('/empleado/');
    }

    wp_redirect($redirect_to);
    exit;
}

$error_message = '';
$success_message = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ga_login_nonce'])) {
    if (wp_verify_nonce($_POST['ga_login_nonce'], 'ga_login_action')) {
        $creds = array(
            'user_login'    => sanitize_text_field($_POST['user_login']),
            'user_password' => $_POST['user_password'],
            'remember'      => !empty($_POST['remember']),
        );

        $user = wp_signon($creds, is_ssl());

        if (is_wp_error($user)) {
            $error_message = __('Credenciales incorrectas. Verifica tu usuario y contraseña.', 'gestionadmin-wolk');
        } else {
            // Redirigir según el tipo de usuario
            $redirect_to = home_url('/mi-cuenta/');

            if (in_array('ga_cliente', (array) $user->roles)) {
                $redirect_to = home_url('/cliente/');
            } elseif (in_array('ga_empleado', (array) $user->roles) ||
                      in_array('ga_jefe', (array) $user->roles) ||
                      in_array('ga_director', (array) $user->roles)) {
                $redirect_to = home_url('/empleado/');
            }

            wp_redirect($redirect_to);
            exit;
        }
    }
}

get_header();
?>

<div class="ga-public-container ga-login-page">
    <div class="ga-container">
        <div class="ga-login-wrapper">
            <div class="ga-login-card">
                <div class="ga-login-header">
                    <h1><?php esc_html_e('Acceso al Sistema', 'gestionadmin-wolk'); ?></h1>
                    <p><?php esc_html_e('Ingresa tus credenciales para continuar', 'gestionadmin-wolk'); ?></p>
                </div>

                <?php if ($error_message) : ?>
                    <div class="ga-alert ga-alert-error">
                        <span class="dashicons dashicons-warning"></span>
                        <?php echo esc_html($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="ga-login-form">
                    <?php wp_nonce_field('ga_login_action', 'ga_login_nonce'); ?>

                    <div class="ga-form-group">
                        <label for="user_login"><?php esc_html_e('Usuario o Email', 'gestionadmin-wolk'); ?></label>
                        <input type="text" id="user_login" name="user_login"
                               class="ga-form-input" required autocomplete="username"
                               value="<?php echo isset($_POST['user_login']) ? esc_attr($_POST['user_login']) : ''; ?>">
                    </div>

                    <div class="ga-form-group">
                        <label for="user_password"><?php esc_html_e('Contraseña', 'gestionadmin-wolk'); ?></label>
                        <input type="password" id="user_password" name="user_password"
                               class="ga-form-input" required autocomplete="current-password">
                    </div>

                    <div class="ga-form-group ga-form-checkbox">
                        <label>
                            <input type="checkbox" name="remember" value="1">
                            <?php esc_html_e('Recordarme', 'gestionadmin-wolk'); ?>
                        </label>
                    </div>

                    <button type="submit" class="ga-btn ga-btn-primary ga-btn-block">
                        <?php esc_html_e('Iniciar Sesión', 'gestionadmin-wolk'); ?>
                    </button>
                </form>

                <div class="ga-login-footer">
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                        <?php esc_html_e('¿Olvidaste tu contraseña?', 'gestionadmin-wolk'); ?>
                    </a>
                </div>

                <div class="ga-login-divider">
                    <span><?php esc_html_e('o', 'gestionadmin-wolk'); ?></span>
                </div>

                <div class="ga-login-register">
                    <p><?php esc_html_e('¿Eres freelancer o empresa?', 'gestionadmin-wolk'); ?></p>
                    <a href="<?php echo esc_url(home_url('/registro-aplicante/')); ?>" class="ga-btn ga-btn-outline ga-btn-block">
                        <?php esc_html_e('Regístrate como Aplicante', 'gestionadmin-wolk'); ?>
                    </a>
                </div>
            </div>

            <div class="ga-login-info">
                <h2><?php esc_html_e('GestionAdmin', 'gestionadmin-wolk'); ?></h2>
                <p><?php esc_html_e('Sistema integral de gestión empresarial', 'gestionadmin-wolk'); ?></p>

                <ul class="ga-login-features">
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Marketplace de trabajo', 'gestionadmin-wolk'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Gestión de proyectos', 'gestionadmin-wolk'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Control de horas', 'gestionadmin-wolk'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Facturación multi-país', 'gestionadmin-wolk'); ?>
                    </li>
                </ul>

                <p class="ga-login-credit">
                    <?php esc_html_e('Diseñado y desarrollado por', 'gestionadmin-wolk'); ?>
                    <a href="https://wolksoftcr.com" target="_blank">Wolksoftcr.com</a>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.ga-login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 20px;
}
.ga-login-wrapper {
    display: flex;
    gap: 40px;
    max-width: 900px;
    margin: 0 auto;
    width: 100%;
}
.ga-login-card {
    background: #fff;
    border-radius: 12px;
    padding: 40px;
    flex: 1;
    max-width: 400px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.ga-login-header {
    text-align: center;
    margin-bottom: 30px;
}
.ga-login-header h1 {
    font-size: 24px;
    margin: 0 0 10px 0;
    color: #1a1a2e;
}
.ga-login-header p {
    color: #666;
    margin: 0;
}
.ga-login-form .ga-form-group {
    margin-bottom: 20px;
}
.ga-login-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}
.ga-login-form .ga-form-input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e5eb;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
}
.ga-login-form .ga-form-input:focus {
    outline: none;
    border-color: #667eea;
}
.ga-form-checkbox label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}
.ga-btn-block {
    display: block;
    width: 100%;
    text-align: center;
}
.ga-login-footer {
    text-align: center;
    margin-top: 20px;
}
.ga-login-footer a {
    color: #667eea;
    text-decoration: none;
}
.ga-login-divider {
    position: relative;
    text-align: center;
    margin: 25px 0;
}
.ga-login-divider::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    width: 100%;
    height: 1px;
    background: #e1e5eb;
}
.ga-login-divider span {
    background: #fff;
    padding: 0 15px;
    position: relative;
    color: #999;
}
.ga-login-register {
    text-align: center;
}
.ga-login-register p {
    margin-bottom: 15px;
    color: #666;
}
.ga-login-info {
    flex: 1;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.ga-login-info h2 {
    font-size: 32px;
    margin: 0 0 10px 0;
}
.ga-login-info > p {
    font-size: 18px;
    opacity: 0.9;
    margin-bottom: 30px;
}
.ga-login-features {
    list-style: none;
    padding: 0;
    margin: 0 0 30px 0;
}
.ga-login-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    font-size: 16px;
}
.ga-login-features .dashicons {
    color: #90EE90;
}
.ga-login-credit {
    font-size: 14px;
    opacity: 0.8;
}
.ga-login-credit a {
    color: #fff;
    text-decoration: underline;
}
@media (max-width: 768px) {
    .ga-login-wrapper {
        flex-direction: column-reverse;
    }
    .ga-login-card {
        max-width: 100%;
    }
    .ga-login-info {
        text-align: center;
    }
    .ga-login-features {
        display: inline-block;
        text-align: left;
    }
}
</style>

<?php get_footer(); ?>
