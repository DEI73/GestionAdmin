<?php
/**
 * Sistema de Emails Profesionales
 *
 * Maneja todos los emails del sistema con templates HTML profesionales.
 * Integra colores del tema si está activo.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes
 * @since      1.6.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GA_Emails
 *
 * Sistema centralizado de emails con templates profesionales.
 *
 * @since 1.6.0
 */
class GA_Emails {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Emails
     */
    private static $instance = null;

    /**
     * Colores del tema/plugin
     *
     * @var array
     */
    private static $colors = array();

    /**
     * Información de la empresa
     *
     * @var array
     */
    private static $company = array();

    /**
     * Obtener instancia única
     *
     * @since 1.6.0
     *
     * @return GA_Emails
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.6.0
     */
    private function __construct() {
        $this->init_colors();
        $this->init_company();
    }

    /**
     * Inicializar colores desde el tema o defaults
     *
     * @since 1.6.0
     */
    private function init_colors() {
        // Intentar obtener colores del tema
        if (class_exists('GA_Theme_Integration')) {
            self::$colors = array(
                'primary'    => GA_Theme_Integration::get_color('primary', '#0056A6'),
                'secondary'  => GA_Theme_Integration::get_color('secondary', '#DC2626'),
                'accent'     => GA_Theme_Integration::get_color('accent', '#10B981'),
                'dark'       => GA_Theme_Integration::get_color('dark', '#1F2937'),
                'light'      => '#F3F4F6',
                'white'      => '#FFFFFF',
                'text'       => '#374151',
                'text_muted' => '#6B7280',
                'border'     => '#E5E7EB',
                'success'    => '#10B981',
                'warning'    => '#F59E0B',
                'danger'     => '#EF4444',
            );
        } else {
            // Defaults del plugin
            self::$colors = array(
                'primary'    => '#0056A6',
                'secondary'  => '#DC2626',
                'accent'     => '#10B981',
                'dark'       => '#1F2937',
                'light'      => '#F3F4F6',
                'white'      => '#FFFFFF',
                'text'       => '#374151',
                'text_muted' => '#6B7280',
                'border'     => '#E5E7EB',
                'success'    => '#10B981',
                'warning'    => '#F59E0B',
                'danger'     => '#EF4444',
            );
        }
    }

    /**
     * Inicializar información de la empresa
     *
     * @since 1.6.0
     */
    private function init_company() {
        // Intentar obtener del tema
        if (class_exists('GA_Theme_Integration')) {
            self::$company = array(
                'name'    => GA_Theme_Integration::get_company_name(),
                'logo'    => GA_Theme_Integration::get_logo_url(),
                'email'   => '',
                'phone'   => '',
                'address' => '',
                'website' => home_url(),
            );

            // Obtener info de contacto
            $contact = GA_Theme_Integration::get_contact_info();
            if (!empty($contact)) {
                self::$company['email']   = $contact['email'] ?? '';
                self::$company['phone']   = $contact['phone'] ?? '';
                self::$company['address'] = $contact['address'] ?? '';
            }
        } else {
            // Defaults
            self::$company = array(
                'name'    => get_bloginfo('name'),
                'logo'    => '',
                'email'   => get_option('admin_email'),
                'phone'   => '',
                'address' => '',
                'website' => home_url(),
            );
        }
    }

    /**
     * Enviar email con template profesional
     *
     * @since 1.6.0
     *
     * @param string $to          Email del destinatario.
     * @param string $subject     Asunto del email.
     * @param string $content     Contenido HTML del cuerpo.
     * @param array  $args        Argumentos adicionales:
     *                            - 'preheader'   Texto de preview
     *                            - 'cta_text'    Texto del botón CTA
     *                            - 'cta_url'     URL del botón CTA
     *                            - 'footer_text' Texto adicional del footer
     *                            - 'attachments' Archivos adjuntos
     *                            - 'cc'          Email CC
     *                            - 'bcc'         Email BCC
     *                            - 'reply_to'    Email de respuesta
     *
     * @return bool True si se envió correctamente.
     */
    public static function send($to, $subject, $content, $args = array()) {
        // Asegurar que la instancia está inicializada
        self::get_instance();

        // Defaults
        $defaults = array(
            'preheader'   => '',
            'cta_text'    => '',
            'cta_url'     => '',
            'footer_text' => '',
            'attachments' => array(),
            'cc'          => '',
            'bcc'         => '',
            'reply_to'    => '',
        );

        $args = wp_parse_args($args, $defaults);

        // Construir el HTML completo
        $html = self::build_template($subject, $content, $args);

        // Headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        // From
        $from_name  = self::$company['name'] ?: get_bloginfo('name');
        $from_email = self::$company['email'] ?: get_option('admin_email');
        $headers[]  = 'From: ' . $from_name . ' <' . $from_email . '>';

        // CC
        if (!empty($args['cc'])) {
            $headers[] = 'Cc: ' . $args['cc'];
        }

        // BCC
        if (!empty($args['bcc'])) {
            $headers[] = 'Bcc: ' . $args['bcc'];
        }

        // Reply-To
        if (!empty($args['reply_to'])) {
            $headers[] = 'Reply-To: ' . $args['reply_to'];
        }

        // Enviar
        $sent = wp_mail($to, $subject, $html, $headers, $args['attachments']);

        // Log si falla
        if (!$sent) {
            error_log('GA_Emails: Error enviando email a ' . $to . ' - Asunto: ' . $subject);
        }

        return $sent;
    }

    /**
     * Construir template HTML completo
     *
     * @since 1.6.0
     *
     * @param string $subject Asunto.
     * @param string $content Contenido.
     * @param array  $args    Argumentos.
     *
     * @return string HTML del email.
     */
    private static function build_template($subject, $content, $args) {
        $colors  = self::$colors;
        $company = self::$company;

        // Preheader (texto de preview en clientes de email)
        $preheader = !empty($args['preheader']) ? $args['preheader'] : wp_strip_all_tags($subject);

        // Logo
        $logo_html = '';
        if (!empty($company['logo'])) {
            $logo_html = '<img src="' . esc_url($company['logo']) . '" alt="' . esc_attr($company['name']) . '" style="max-width: 180px; max-height: 60px; height: auto;">';
        } else {
            $logo_html = '<span style="font-size: 24px; font-weight: 700; color: ' . esc_attr($colors['primary']) . ';">' . esc_html($company['name']) . '</span>';
        }

        // CTA Button
        $cta_html = '';
        if (!empty($args['cta_text']) && !empty($args['cta_url'])) {
            $cta_html = '
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 30px auto;">
                <tr>
                    <td style="border-radius: 6px; background-color: ' . esc_attr($colors['primary']) . ';">
                        <a href="' . esc_url($args['cta_url']) . '" target="_blank" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; font-size: 16px; font-weight: 600; color: ' . esc_attr($colors['white']) . '; text-decoration: none; border-radius: 6px;">
                            ' . esc_html($args['cta_text']) . '
                        </a>
                    </td>
                </tr>
            </table>';
        }

        // Footer text adicional
        $footer_extra = '';
        if (!empty($args['footer_text'])) {
            $footer_extra = '<p style="margin: 0 0 10px; color: ' . esc_attr($colors['text_muted']) . '; font-size: 13px;">' . wp_kses_post($args['footer_text']) . '</p>';
        }

        // Año actual
        $year = date('Y');

        // Construir HTML
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>' . esc_html($subject) . '</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .fluid { max-width: 100% !important; height: auto !important; margin-left: auto !important; margin-right: auto !important; }
            .stack-column { display: block !important; width: 100% !important; max-width: 100% !important; }
            .center-on-narrow { text-align: center !important; display: block !important; margin-left: auto !important; margin-right: auto !important; float: none !important; }
            table.center-on-narrow { display: inline-block !important; }
            .padding-mobile { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: ' . esc_attr($colors['light']) . '; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;">

    <!-- Preheader (texto oculto para preview) -->
    <div style="display: none; font-size: 1px; color: ' . esc_attr($colors['light']) . '; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        ' . esc_html($preheader) . '
    </div>

    <!-- Email Container -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: ' . esc_attr($colors['light']) . ';">
        <tr>
            <td style="padding: 40px 20px;">

                <!-- Main Content Container -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" class="email-container" style="margin: 0 auto; background-color: ' . esc_attr($colors['white']) . '; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, ' . esc_attr($colors['primary']) . ' 0%, ' . esc_attr($colors['dark']) . ' 100%); padding: 30px 40px; text-align: center;">
                            ' . $logo_html . '
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px; color: ' . esc_attr($colors['text']) . '; font-size: 16px; line-height: 1.6;" class="padding-mobile">
                            ' . wp_kses_post($content) . '
                            ' . $cta_html . '
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: ' . esc_attr($colors['light']) . '; padding: 30px 40px; text-align: center; border-top: 1px solid ' . esc_attr($colors['border']) . ';" class="padding-mobile">
                            ' . $footer_extra . '
                            <p style="margin: 0 0 10px; color: ' . esc_attr($colors['text_muted']) . '; font-size: 13px;">
                                ' . esc_html($company['name']) . '
                            </p>';

        // Información de contacto
        $contact_parts = array();
        if (!empty($company['phone'])) {
            $contact_parts[] = esc_html($company['phone']);
        }
        if (!empty($company['email'])) {
            $contact_parts[] = '<a href="mailto:' . esc_attr($company['email']) . '" style="color: ' . esc_attr($colors['primary']) . '; text-decoration: none;">' . esc_html($company['email']) . '</a>';
        }
        if (!empty($contact_parts)) {
            $html .= '<p style="margin: 0 0 10px; color: ' . esc_attr($colors['text_muted']) . '; font-size: 13px;">' . implode(' | ', $contact_parts) . '</p>';
        }

        if (!empty($company['address'])) {
            $html .= '<p style="margin: 0 0 10px; color: ' . esc_attr($colors['text_muted']) . '; font-size: 12px;">' . esc_html($company['address']) . '</p>';
        }

        $html .= '
                            <p style="margin: 15px 0 0; color: ' . esc_attr($colors['text_muted']) . '; font-size: 11px;">
                                &copy; ' . esc_html($year) . ' ' . esc_html($company['name']) . '. ' . esc_html__('Todos los derechos reservados.', 'gestionadmin-wolk') . '
                            </p>
                            <p style="margin: 10px 0 0;">
                                <a href="' . esc_url($company['website']) . '" style="color: ' . esc_attr($colors['primary']) . '; text-decoration: none; font-size: 12px;">' . esc_html__('Visitar sitio web', 'gestionadmin-wolk') . '</a>
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- End Main Content Container -->

            </td>
        </tr>
    </table>
    <!-- End Email Container -->

</body>
</html>';

        return $html;
    }

    // =========================================================================
    // MÉTODOS DE CONVENIENCIA PARA TIPOS ESPECÍFICOS DE EMAIL
    // =========================================================================

    /**
     * Email de bienvenida para nuevo aplicante
     *
     * @since 1.6.0
     *
     * @param string $email         Email del aplicante.
     * @param string $nombre        Nombre del aplicante.
     * @param string $password_temp Contraseña temporal (opcional).
     *
     * @return bool
     */
    public static function send_bienvenida_aplicante($email, $nombre, $password_temp = '') {
        $subject = sprintf(__('¡Bienvenido a %s!', 'gestionadmin-wolk'), self::$company['name'] ?: get_bloginfo('name'));

        $content = '
        <h2 style="margin: 0 0 20px; color: #1F2937; font-size: 24px;">¡Hola ' . esc_html($nombre) . '!</h2>
        <p>Tu cuenta ha sido creada exitosamente. Ahora puedes acceder a nuestra plataforma de oportunidades laborales.</p>';

        if (!empty($password_temp)) {
            $content .= '
            <div style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; font-weight: 600; color: #92400E;">Tu contraseña temporal:</p>
                <p style="margin: 10px 0 0; font-family: monospace; font-size: 18px; color: #1F2937;">' . esc_html($password_temp) . '</p>
                <p style="margin: 10px 0 0; font-size: 13px; color: #92400E;">Por seguridad, te recomendamos cambiarla después de iniciar sesión.</p>
            </div>';
        }

        $content .= '
        <p>Desde tu panel podrás:</p>
        <ul style="padding-left: 20px; margin: 15px 0;">
            <li style="margin-bottom: 8px;">Explorar oportunidades de trabajo</li>
            <li style="margin-bottom: 8px;">Aplicar a proyectos que se ajusten a tu perfil</li>
            <li style="margin-bottom: 8px;">Gestionar tus aplicaciones</li>
            <li style="margin-bottom: 8px;">Actualizar tu perfil profesional</li>
        </ul>
        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>';

        return self::send($email, $subject, $content, array(
            'preheader' => __('Tu cuenta ha sido creada exitosamente', 'gestionadmin-wolk'),
            'cta_text'  => __('Acceder a Mi Cuenta', 'gestionadmin-wolk'),
            'cta_url'   => home_url('/mi-cuenta/'),
        ));
    }

    /**
     * Email de confirmación de aplicación
     *
     * @since 1.6.0
     *
     * @param string $email        Email del aplicante.
     * @param string $nombre       Nombre del aplicante.
     * @param object $orden        Objeto de la orden de trabajo.
     * @param string $codigo_app   Código de la aplicación.
     *
     * @return bool
     */
    public static function send_confirmacion_aplicacion($email, $nombre, $orden, $codigo_app) {
        $subject = sprintf(__('Aplicación recibida: %s', 'gestionadmin-wolk'), $orden->titulo);

        $content = '
        <h2 style="margin: 0 0 20px; color: #1F2937; font-size: 24px;">¡Gracias por tu aplicación!</h2>
        <p>Hola ' . esc_html($nombre) . ',</p>
        <p>Hemos recibido tu aplicación para la siguiente oportunidad:</p>

        <div style="background-color: #F3F4F6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 10px; font-weight: 600; font-size: 18px; color: #1F2937;">' . esc_html($orden->titulo) . '</p>
            <p style="margin: 0; color: #6B7280; font-size: 14px;">
                <strong>Código de orden:</strong> ' . esc_html($orden->codigo) . '<br>
                <strong>Tu aplicación:</strong> ' . esc_html($codigo_app) . '
            </p>
        </div>

        <p>El equipo revisará tu perfil y te contactaremos pronto con novedades sobre el proceso de selección.</p>
        <p>Puedes consultar el estado de todas tus aplicaciones desde tu panel de control.</p>';

        return self::send($email, $subject, $content, array(
            'preheader' => __('Tu aplicación ha sido recibida correctamente', 'gestionadmin-wolk'),
            'cta_text'  => __('Ver Mis Aplicaciones', 'gestionadmin-wolk'),
            'cta_url'   => home_url('/mi-cuenta/aplicaciones/'),
        ));
    }

    /**
     * Email de cambio de estado de aplicación
     *
     * @since 1.6.0
     *
     * @param string $email       Email del aplicante.
     * @param string $nombre      Nombre del aplicante.
     * @param object $orden       Objeto de la orden.
     * @param string $nuevo_estado Nuevo estado de la aplicación.
     * @param string $mensaje     Mensaje adicional (opcional).
     *
     * @return bool
     */
    public static function send_cambio_estado_aplicacion($email, $nombre, $orden, $nuevo_estado, $mensaje = '') {
        // Mapear estados a textos amigables y colores
        $estados_info = array(
            'PENDIENTE'    => array('text' => __('Pendiente de revisión', 'gestionadmin-wolk'), 'color' => '#F59E0B'),
            'EN_REVISION'  => array('text' => __('En revisión', 'gestionadmin-wolk'), 'color' => '#3B82F6'),
            'PRESELECCIONADO' => array('text' => __('Preseleccionado', 'gestionadmin-wolk'), 'color' => '#8B5CF6'),
            'ENTREVISTA'   => array('text' => __('Citado a entrevista', 'gestionadmin-wolk'), 'color' => '#06B6D4'),
            'CONTRATADO'   => array('text' => __('¡Contratado!', 'gestionadmin-wolk'), 'color' => '#10B981'),
            'RECHAZADO'    => array('text' => __('No seleccionado', 'gestionadmin-wolk'), 'color' => '#EF4444'),
            'RETIRADO'     => array('text' => __('Retirado', 'gestionadmin-wolk'), 'color' => '#6B7280'),
        );

        $estado_info = $estados_info[$nuevo_estado] ?? array('text' => $nuevo_estado, 'color' => '#6B7280');

        $subject = sprintf(__('Actualización de tu aplicación: %s', 'gestionadmin-wolk'), $orden->titulo);

        $content = '
        <h2 style="margin: 0 0 20px; color: #1F2937; font-size: 24px;">Actualización de tu aplicación</h2>
        <p>Hola ' . esc_html($nombre) . ',</p>
        <p>Hay novedades sobre tu aplicación a:</p>

        <div style="background-color: #F3F4F6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 15px; font-weight: 600; font-size: 18px; color: #1F2937;">' . esc_html($orden->titulo) . '</p>
            <p style="margin: 0;">
                <span style="display: inline-block; padding: 6px 16px; background-color: ' . esc_attr($estado_info['color']) . '; color: #FFFFFF; border-radius: 20px; font-weight: 600; font-size: 14px;">
                    ' . esc_html($estado_info['text']) . '
                </span>
            </p>
        </div>';

        if (!empty($mensaje)) {
            $content .= '
            <div style="background-color: #EFF6FF; border-left: 4px solid #3B82F6; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; font-weight: 600; color: #1E40AF;">Mensaje del equipo:</p>
                <p style="margin: 10px 0 0; color: #1F2937;">' . wp_kses_post($mensaje) . '</p>
            </div>';
        }

        if ($nuevo_estado === 'CONTRATADO') {
            $content .= '<p style="font-size: 18px; text-align: center;">🎉 ¡Felicitaciones! 🎉</p>';
        }

        $content .= '<p>Puedes ver más detalles en tu panel de control.</p>';

        return self::send($email, $subject, $content, array(
            'preheader' => sprintf(__('Estado: %s', 'gestionadmin-wolk'), $estado_info['text']),
            'cta_text'  => __('Ver Detalles', 'gestionadmin-wolk'),
            'cta_url'   => home_url('/mi-cuenta/aplicaciones/'),
        ));
    }

    /**
     * Email de nueva orden de trabajo (para aplicantes interesados)
     *
     * @since 1.6.0
     *
     * @param string $email  Email del aplicante.
     * @param string $nombre Nombre del aplicante.
     * @param object $orden  Objeto de la orden.
     *
     * @return bool
     */
    public static function send_nueva_orden($email, $nombre, $orden) {
        $subject = sprintf(__('Nueva oportunidad: %s', 'gestionadmin-wolk'), $orden->titulo);

        // Formatear presupuesto
        $presupuesto = '';
        if ($orden->presupuesto_min && $orden->presupuesto_max) {
            $presupuesto = '$' . number_format($orden->presupuesto_min, 0) . ' - $' . number_format($orden->presupuesto_max, 0);
        } elseif ($orden->presupuesto_max) {
            $presupuesto = __('Hasta', 'gestionadmin-wolk') . ' $' . number_format($orden->presupuesto_max, 0);
        } else {
            $presupuesto = __('A convenir', 'gestionadmin-wolk');
        }

        $content = '
        <h2 style="margin: 0 0 20px; color: #1F2937; font-size: 24px;">Nueva oportunidad disponible</h2>
        <p>Hola ' . esc_html($nombre) . ',</p>
        <p>Hay una nueva oportunidad que podría interesarte:</p>

        <div style="background-color: #F3F4F6; padding: 25px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px; color: #1F2937; font-size: 20px;">' . esc_html($orden->titulo) . '</h3>
            <p style="margin: 0 0 15px; color: #6B7280; font-size: 14px; line-height: 1.6;">' . esc_html(wp_trim_words($orden->descripcion, 40)) . '</p>
            <table style="width: 100%; font-size: 14px;">
                <tr>
                    <td style="padding: 5px 0; color: #6B7280; width: 120px;"><strong>' . esc_html__('Categoría:', 'gestionadmin-wolk') . '</strong></td>
                    <td style="padding: 5px 0; color: #1F2937;">' . esc_html($orden->categoria) . '</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6B7280;"><strong>' . esc_html__('Modalidad:', 'gestionadmin-wolk') . '</strong></td>
                    <td style="padding: 5px 0; color: #1F2937;">' . esc_html($orden->modalidad) . '</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6B7280;"><strong>' . esc_html__('Presupuesto:', 'gestionadmin-wolk') . '</strong></td>
                    <td style="padding: 5px 0; color: #10B981; font-weight: 600;">' . esc_html($presupuesto) . '</td>
                </tr>
            </table>
        </div>';

        return self::send($email, $subject, $content, array(
            'preheader' => sprintf(__('Nueva oportunidad: %s', 'gestionadmin-wolk'), $orden->titulo),
            'cta_text'  => __('Ver Oportunidad', 'gestionadmin-wolk'),
            'cta_url'   => home_url('/trabajo/' . $orden->codigo . '/'),
        ));
    }

    /**
     * Email de recuperación de contraseña
     *
     * @since 1.6.0
     *
     * @param string $email     Email del usuario.
     * @param string $nombre    Nombre del usuario.
     * @param string $reset_url URL de recuperación.
     *
     * @return bool
     */
    public static function send_recuperar_password($email, $nombre, $reset_url) {
        $subject = __('Recuperar contraseña', 'gestionadmin-wolk');

        $content = '
        <h2 style="margin: 0 0 20px; color: #1F2937; font-size: 24px;">Recuperar contraseña</h2>
        <p>Hola ' . esc_html($nombre) . ',</p>
        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta.</p>
        <p>Haz clic en el botón de abajo para crear una nueva contraseña:</p>

        <div style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; font-size: 13px; color: #92400E;">
                <strong>Importante:</strong> Este enlace expirará en 24 horas. Si no solicitaste este cambio, puedes ignorar este mensaje.
            </p>
        </div>

        <p style="color: #6B7280; font-size: 14px;">Si tienes problemas con el botón, copia y pega esta URL en tu navegador:</p>
        <p style="word-break: break-all; font-size: 12px; color: #6B7280; background: #F3F4F6; padding: 10px; border-radius: 4px;">' . esc_url($reset_url) . '</p>';

        return self::send($email, $subject, $content, array(
            'preheader' => __('Solicitud de recuperación de contraseña', 'gestionadmin-wolk'),
            'cta_text'  => __('Restablecer Contraseña', 'gestionadmin-wolk'),
            'cta_url'   => $reset_url,
        ));
    }

    /**
     * Email de notificación a admin (nueva aplicación recibida)
     *
     * @since 1.6.0
     *
     * @param object $orden     Orden de trabajo.
     * @param object $aplicante Aplicante.
     * @param object $aplicacion Aplicación.
     *
     * @return bool
     */
    public static function send_notificacion_admin_nueva_aplicacion($orden, $aplicante, $aplicacion) {
        $admin_email = get_option('admin_email');
        $subject     = sprintf(__('[Nueva Aplicación] %s - %s', 'gestionadmin-wolk'), $orden->codigo, $aplicante->nombre_completo);

        $content = '
        <h2 style="margin: 0 0 20px; color: #1F2937; font-size: 24px;">Nueva aplicación recibida</h2>

        <div style="background-color: #F3F4F6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px; color: #1F2937;">Orden de Trabajo</h3>
            <table style="width: 100%; font-size: 14px;">
                <tr>
                    <td style="padding: 5px 0; color: #6B7280; width: 100px;"><strong>Código:</strong></td>
                    <td style="padding: 5px 0; color: #1F2937;">' . esc_html($orden->codigo) . '</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6B7280;"><strong>Título:</strong></td>
                    <td style="padding: 5px 0; color: #1F2937;">' . esc_html($orden->titulo) . '</td>
                </tr>
            </table>
        </div>

        <div style="background-color: #EFF6FF; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px; color: #1F2937;">Aplicante</h3>
            <table style="width: 100%; font-size: 14px;">
                <tr>
                    <td style="padding: 5px 0; color: #6B7280; width: 100px;"><strong>Nombre:</strong></td>
                    <td style="padding: 5px 0; color: #1F2937;">' . esc_html($aplicante->nombre_completo) . '</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6B7280;"><strong>Email:</strong></td>
                    <td style="padding: 5px 0; color: #1F2937;">' . esc_html($aplicante->email) . '</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6B7280;"><strong>Teléfono:</strong></td>
                    <td style="padding: 5px 0; color: #1F2937;">' . esc_html($aplicante->telefono ?: '-') . '</td>
                </tr>
            </table>
        </div>';

        if (!empty($aplicacion->propuesta_monto)) {
            $content .= '
            <p><strong>Propuesta económica:</strong> $' . number_format($aplicacion->propuesta_monto, 2) . '</p>';
        }

        return self::send($admin_email, $subject, $content, array(
            'preheader' => sprintf(__('Nueva aplicación de %s', 'gestionadmin-wolk'), $aplicante->nombre_completo),
            'cta_text'  => __('Ver en Panel Admin', 'gestionadmin-wolk'),
            'cta_url'   => admin_url('admin.php?page=ga-aplicaciones'),
        ));
    }

    /**
     * Email genérico/personalizado
     *
     * @since 1.6.0
     *
     * @param string $to       Destinatario.
     * @param string $subject  Asunto.
     * @param string $titulo   Título del email (H2).
     * @param string $mensaje  Mensaje del cuerpo.
     * @param array  $args     Argumentos adicionales.
     *
     * @return bool
     */
    public static function send_custom($to, $subject, $titulo, $mensaje, $args = array()) {
        $content = '
        <h2 style="margin: 0 0 20px; color: #1F2937; font-size: 24px;">' . esc_html($titulo) . '</h2>
        ' . wp_kses_post($mensaje);

        return self::send($to, $subject, $content, $args);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Obtener colores actuales
     *
     * @since 1.6.0
     *
     * @return array
     */
    public static function get_colors() {
        self::get_instance();
        return self::$colors;
    }

    /**
     * Obtener información de la empresa
     *
     * @since 1.6.0
     *
     * @return array
     */
    public static function get_company_info() {
        self::get_instance();
        return self::$company;
    }
}
