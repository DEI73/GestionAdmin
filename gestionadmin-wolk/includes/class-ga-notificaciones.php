<?php
/**
 * Sistema de Notificaciones por Email
 *
 * Maneja el envío de notificaciones automáticas según eventos del sistema.
 * Permite configurar qué notificaciones están activas desde el admin.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes
 * @since      1.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Notificaciones {

    /**
     * Instancia única (Singleton)
     */
    private static $instance = null;

    /**
     * Configuración de notificaciones activas
     */
    private static $config = null;

    /**
     * Tipos de notificaciones disponibles
     */
    const TIPOS = array(
        // Tareas
        'tarea_asignada'        => 'Tarea asignada a empleado',
        'tarea_iniciada'        => 'Empleado inició una tarea',
        'tarea_enviada_qa'      => 'Tarea enviada a revisión QA',
        'tarea_aprobada_qa'     => 'QA aprobó la tarea',
        'tarea_rechazada_qa'    => 'QA rechazó la tarea',
        'tarea_completada'      => 'Tarea completada (pendiente aprobación)',
        'tarea_aprobada'        => 'Tarea aprobada por jefe',
        'tarea_rechazada'       => 'Tarea rechazada por jefe',

        // Aplicantes
        'aplicante_bienvenida'      => 'Bienvenida a nuevo aplicante',
        'aplicante_aplicacion'      => 'Confirmación de aplicación',
        'aplicante_estado_cambio'   => 'Cambio de estado en aplicación',

        // Órdenes de trabajo
        'orden_nueva'           => 'Nueva orden de trabajo publicada',
        'orden_asignada'        => 'Orden asignada a aplicante',

        // Facturación
        'factura_enviada'       => 'Factura enviada al cliente',
        'factura_pagada'        => 'Confirmación de pago recibido',
    );

    /**
     * Obtener instancia única
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_config();
        $this->register_hooks();
    }

    /**
     * Cargar configuración desde la BD
     */
    private function load_config() {
        $saved = get_option('ga_notificaciones_config', array());

        // Por defecto todas activas
        $defaults = array();
        foreach (array_keys(self::TIPOS) as $tipo) {
            $defaults[$tipo] = true;
        }

        self::$config = wp_parse_args($saved, $defaults);
    }

    /**
     * Verificar si una notificación está activa
     */
    public static function is_active($tipo) {
        if (null === self::$config) {
            self::get_instance();
        }
        return isset(self::$config[$tipo]) && self::$config[$tipo];
    }

    /**
     * Registrar hooks para disparar notificaciones
     */
    private function register_hooks() {
        // Hooks de tareas
        add_action('ga_tarea_asignada', array($this, 'notify_tarea_asignada'), 10, 3);
        add_action('ga_tarea_estado_cambiado', array($this, 'notify_tarea_estado_cambio'), 10, 4);

        // Hooks de aplicantes
        add_action('ga_aplicante_registrado', array($this, 'notify_aplicante_bienvenida'), 10, 2);
        add_action('ga_aplicacion_creada', array($this, 'notify_aplicacion_creada'), 10, 2);
        add_action('ga_aplicacion_estado_cambio', array($this, 'notify_aplicacion_estado'), 10, 3);
    }

    // =========================================================================
    // NOTIFICACIONES DE TAREAS
    // =========================================================================

    /**
     * Notificar: Tarea asignada a empleado
     *
     * @param int $tarea_id          ID de la tarea
     * @param int $usuario_id        Usuario asignado
     * @param int $asignado_anterior Usuario previamente asignado (0 si ninguno)
     */
    public function notify_tarea_asignada($tarea_id, $usuario_id, $asignado_anterior = 0) {
        if (!self::is_active('tarea_asignada')) {
            return;
        }

        $user = get_userdata($usuario_id);
        if (!$user) return;

        global $wpdb;
        $tarea = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ga_tareas WHERE id = %d",
            $tarea_id
        ));
        if (!$tarea) return;

        $contenido = $this->build_tarea_content($tarea, 'asignada');

        GA_Emails::send(
            $user->user_email,
            sprintf(__('Nueva tarea asignada: %s', 'gestionadmin-wolk'), $tarea->nombre),
            $contenido,
            array('tipo' => 'info')
        );
    }

    /**
     * Notificar: Cambio de estado en tarea
     *
     * @param int    $tarea_id        ID de la tarea
     * @param string $estado_anterior Estado anterior
     * @param string $estado_nuevo    Nuevo estado
     * @param string $nota            Nota adicional (ej: motivo de rechazo)
     */
    public function notify_tarea_estado_cambio($tarea_id, $estado_anterior, $estado_nuevo, $nota = '') {
        global $wpdb;

        $tarea = $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, u.display_name as empleado_nombre, u.user_email as empleado_email
             FROM {$wpdb->prefix}ga_tareas t
             LEFT JOIN {$wpdb->users} u ON t.asignado_a = u.ID
             WHERE t.id = %d",
            $tarea_id
        ));
        if (!$tarea) return;

        // Determinar qué notificación enviar según el nuevo estado
        switch ($estado_nuevo) {
            case 'EN_PROGRESO':
                $this->notify_tarea_iniciada($tarea);
                break;

            case 'EN_QA':
                $this->notify_tarea_enviada_qa($tarea);
                break;

            case 'APROBADA_QA':
                $this->notify_tarea_aprobada_qa($tarea);
                break;

            case 'RECHAZADA':
                if ($estado_anterior === 'EN_QA') {
                    $this->notify_tarea_rechazada_qa($tarea);
                } else {
                    $this->notify_tarea_rechazada($tarea);
                }
                break;

            case 'COMPLETADA':
            case 'EN_REVISION':
                $this->notify_tarea_completada($tarea);
                break;

            case 'APROBADA':
                $this->notify_tarea_aprobada($tarea);
                break;
        }
    }

    /**
     * Tarea iniciada - notificar al jefe
     */
    private function notify_tarea_iniciada($tarea) {
        if (!self::is_active('tarea_iniciada')) return;

        $jefe = $this->get_jefe_usuario($tarea->asignado_a);
        if (!$jefe) return;

        $contenido = sprintf(
            '<p><strong>%s</strong> ha comenzado a trabajar en la tarea:</p>%s',
            esc_html($tarea->empleado_nombre),
            $this->build_tarea_content($tarea, 'iniciada')
        );

        GA_Emails::send(
            $jefe->user_email,
            sprintf(__('Tarea iniciada: %s', 'gestionadmin-wolk'), $tarea->nombre),
            $contenido,
            array('tipo' => 'info')
        );
    }

    /**
     * Tarea enviada a QA - notificar al QA y jefe
     */
    private function notify_tarea_enviada_qa($tarea) {
        if (!self::is_active('tarea_enviada_qa')) return;

        $destinatarios = $this->get_destinatarios_qa($tarea);
        if (empty($destinatarios)) return;

        $contenido = sprintf(
            '<p><strong>%s</strong> ha enviado la siguiente tarea para revisión de calidad:</p>%s',
            esc_html($tarea->empleado_nombre),
            $this->build_tarea_content($tarea, 'revision')
        );

        foreach ($destinatarios as $email) {
            GA_Emails::send(
                $email,
                sprintf(__('Tarea pendiente de QA: %s', 'gestionadmin-wolk'), $tarea->nombre),
                $contenido,
                array('tipo' => 'warning')
            );
        }
    }

    /**
     * Tarea aprobada por QA - notificar al empleado
     */
    private function notify_tarea_aprobada_qa($tarea) {
        if (!self::is_active('tarea_aprobada_qa')) return;
        if (empty($tarea->empleado_email)) return;

        $contenido = sprintf(
            '<p>Tu tarea ha sido <strong style="color:#10B981;">aprobada por QA</strong> y está pendiente de aprobación final.</p>%s',
            $this->build_tarea_content($tarea, 'aprobada_qa')
        );

        GA_Emails::send(
            $tarea->empleado_email,
            sprintf(__('QA Aprobado: %s', 'gestionadmin-wolk'), $tarea->nombre),
            $contenido,
            array('tipo' => 'success')
        );
    }

    /**
     * Tarea rechazada por QA - notificar al empleado
     */
    private function notify_tarea_rechazada_qa($tarea) {
        if (!self::is_active('tarea_rechazada_qa')) return;
        if (empty($tarea->empleado_email)) return;

        $contenido = sprintf(
            '<p>Tu tarea ha sido <strong style="color:#EF4444;">rechazada por QA</strong>. Por favor revisa los comentarios y corrige.</p>%s',
            $this->build_tarea_content($tarea, 'rechazada')
        );

        GA_Emails::send(
            $tarea->empleado_email,
            sprintf(__('QA Rechazado: %s', 'gestionadmin-wolk'), $tarea->nombre),
            $contenido,
            array('tipo' => 'danger')
        );
    }

    /**
     * Tarea completada - notificar al jefe
     */
    private function notify_tarea_completada($tarea) {
        if (!self::is_active('tarea_completada')) return;

        $jefe = $this->get_jefe_usuario($tarea->asignado_a);
        if (!$jefe) return;

        $contenido = sprintf(
            '<p><strong>%s</strong> ha completado la siguiente tarea y está pendiente de tu aprobación:</p>%s',
            esc_html($tarea->empleado_nombre),
            $this->build_tarea_content($tarea, 'completada')
        );

        GA_Emails::send(
            $jefe->user_email,
            sprintf(__('Tarea pendiente de aprobación: %s', 'gestionadmin-wolk'), $tarea->nombre),
            $contenido,
            array('tipo' => 'warning')
        );
    }

    /**
     * Tarea aprobada por jefe - notificar al empleado
     */
    private function notify_tarea_aprobada($tarea) {
        if (!self::is_active('tarea_aprobada')) return;
        if (empty($tarea->empleado_email)) return;

        $contenido = sprintf(
            '<p>Tu tarea ha sido <strong style="color:#10B981;">aprobada</strong>.</p>%s',
            $this->build_tarea_content($tarea, 'aprobada')
        );

        GA_Emails::send(
            $tarea->empleado_email,
            sprintf(__('Tarea Aprobada: %s', 'gestionadmin-wolk'), $tarea->nombre),
            $contenido,
            array('tipo' => 'success')
        );
    }

    /**
     * Tarea rechazada por jefe - notificar al empleado
     */
    private function notify_tarea_rechazada($tarea) {
        if (!self::is_active('tarea_rechazada')) return;
        if (empty($tarea->empleado_email)) return;

        $contenido = sprintf(
            '<p>Tu tarea ha sido <strong style="color:#EF4444;">rechazada</strong>. Por favor revisa los comentarios.</p>%s',
            $this->build_tarea_content($tarea, 'rechazada')
        );

        GA_Emails::send(
            $tarea->empleado_email,
            sprintf(__('Tarea Rechazada: %s', 'gestionadmin-wolk'), $tarea->nombre),
            $contenido,
            array('tipo' => 'danger')
        );
    }

    // =========================================================================
    // NOTIFICACIONES DE APLICANTES
    // =========================================================================

    /**
     * Bienvenida a nuevo aplicante
     */
    public function notify_aplicante_bienvenida($aplicante_id, $password_temp = '') {
        if (!self::is_active('aplicante_bienvenida')) return;

        global $wpdb;
        $aplicante = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ga_aplicantes WHERE id = %d",
            $aplicante_id
        ));
        if (!$aplicante) return;

        GA_Emails::send_bienvenida_aplicante(
            $aplicante->email,
            $aplicante->nombre_completo,
            $password_temp
        );
    }

    /**
     * Confirmación de aplicación a orden
     */
    public function notify_aplicacion_creada($aplicacion_id, $orden_id) {
        if (!self::is_active('aplicante_aplicacion')) return;

        global $wpdb;

        $aplicacion = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, ap.nombre_completo, ap.email, o.titulo as orden_titulo, o.codigo as orden_codigo
             FROM {$wpdb->prefix}ga_aplicaciones_orden a
             JOIN {$wpdb->prefix}ga_aplicantes ap ON a.aplicante_id = ap.id
             JOIN {$wpdb->prefix}ga_ordenes_trabajo o ON a.orden_trabajo_id = o.id
             WHERE a.id = %d",
            $aplicacion_id
        ));
        if (!$aplicacion) return;

        $orden = (object) array(
            'titulo' => $aplicacion->orden_titulo,
            'codigo' => $aplicacion->orden_codigo
        );

        GA_Emails::send_confirmacion_aplicacion(
            $aplicacion->email,
            $aplicacion->nombre_completo,
            $orden,
            'APP-' . str_pad($aplicacion_id, 6, '0', STR_PAD_LEFT)
        );
    }

    /**
     * Cambio de estado en aplicación
     */
    public function notify_aplicacion_estado($aplicacion_id, $estado_anterior, $estado_nuevo) {
        if (!self::is_active('aplicante_estado_cambio')) return;

        global $wpdb;

        $aplicacion = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, ap.nombre_completo, ap.email, o.titulo as orden_titulo, o.codigo as orden_codigo
             FROM {$wpdb->prefix}ga_aplicaciones_orden a
             JOIN {$wpdb->prefix}ga_aplicantes ap ON a.aplicante_id = ap.id
             JOIN {$wpdb->prefix}ga_ordenes_trabajo o ON a.orden_trabajo_id = o.id
             WHERE a.id = %d",
            $aplicacion_id
        ));
        if (!$aplicacion) return;

        $orden = (object) array(
            'titulo' => $aplicacion->orden_titulo,
            'codigo' => $aplicacion->orden_codigo
        );

        GA_Emails::send_cambio_estado_aplicacion(
            $aplicacion->email,
            $aplicacion->nombre_completo,
            $orden,
            $estado_nuevo
        );
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Construir contenido HTML para tarea
     */
    private function build_tarea_content($tarea, $contexto = 'info') {
        $estados = array(
            'PENDIENTE'    => array('label' => 'Pendiente', 'color' => '#6B7280'),
            'EN_PROGRESO'  => array('label' => 'En Progreso', 'color' => '#3B82F6'),
            'EN_QA'        => array('label' => 'En QA', 'color' => '#F59E0B'),
            'APROBADA_QA'  => array('label' => 'QA Aprobado', 'color' => '#10B981'),
            'EN_REVISION'  => array('label' => 'En Revisión', 'color' => '#F59E0B'),
            'COMPLETADA'   => array('label' => 'Completada', 'color' => '#10B981'),
            'APROBADA'     => array('label' => 'Aprobada', 'color' => '#10B981'),
            'RECHAZADA'    => array('label' => 'Rechazada', 'color' => '#EF4444'),
        );

        $estado_info = $estados[$tarea->estado] ?? array('label' => $tarea->estado, 'color' => '#6B7280');

        $minutos = isset($tarea->minutos_estimados) ? $tarea->minutos_estimados : 60;
        $tiempo = $minutos >= 60
            ? sprintf('%dh %dm', floor($minutos/60), $minutos % 60)
            : sprintf('%d min', $minutos);

        $html = '
        <table style="width:100%; border-collapse:collapse; margin:20px 0;">
            <tr>
                <td style="padding:16px; background:#F9FAFB; border:1px solid #E5E7EB; border-radius:8px;">
                    <table style="width:100%;">
                        <tr>
                            <td style="padding:4px 0;">
                                <strong style="color:#374151;">Tarea:</strong>
                            </td>
                            <td style="padding:4px 0; color:#1F2937;">
                                ' . esc_html($tarea->nombre) . '
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:4px 0;">
                                <strong style="color:#374151;">Número:</strong>
                            </td>
                            <td style="padding:4px 0; color:#1F2937;">
                                ' . esc_html($tarea->numero) . '
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:4px 0;">
                                <strong style="color:#374151;">Estado:</strong>
                            </td>
                            <td style="padding:4px 0;">
                                <span style="display:inline-block; padding:4px 12px; background:' . $estado_info['color'] . '20; color:' . $estado_info['color'] . '; border-radius:20px; font-size:13px; font-weight:600;">
                                    ' . $estado_info['label'] . '
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:4px 0;">
                                <strong style="color:#374151;">Tiempo Estimado:</strong>
                            </td>
                            <td style="padding:4px 0; color:#1F2937;">
                                ' . $tiempo . '
                            </td>
                        </tr>';

        if (!empty($tarea->fecha_limite)) {
            $html .= '
                        <tr>
                            <td style="padding:4px 0;">
                                <strong style="color:#374151;">Fecha Límite:</strong>
                            </td>
                            <td style="padding:4px 0; color:#1F2937;">
                                ' . date_i18n('j M Y', strtotime($tarea->fecha_limite)) . '
                            </td>
                        </tr>';
        }

        $html .= '
                    </table>
                </td>
            </tr>
        </table>';

        return $html;
    }

    /**
     * Obtener jefe de un usuario
     */
    private function get_jefe_usuario($usuario_id) {
        global $wpdb;

        // Buscar en supervisiones activas
        $supervisor_id = $wpdb->get_var($wpdb->prepare(
            "SELECT supervisor_id
             FROM {$wpdb->prefix}ga_supervisiones
             WHERE supervisado_id = %d AND activo = 1 AND tipo_supervision = 'DIRECTA'
             ORDER BY fecha_inicio DESC LIMIT 1",
            $usuario_id
        ));

        if ($supervisor_id) {
            return get_userdata($supervisor_id);
        }

        // Fallback: buscar en tabla usuarios por departamento
        $usuario_ga = $wpdb->get_row($wpdb->prepare(
            "SELECT departamento_id FROM {$wpdb->prefix}ga_usuarios WHERE usuario_wp_id = %d",
            $usuario_id
        ));

        if ($usuario_ga && $usuario_ga->departamento_id) {
            $jefe_id = $wpdb->get_var($wpdb->prepare(
                "SELECT jefe_id FROM {$wpdb->prefix}ga_departamentos WHERE id = %d",
                $usuario_ga->departamento_id
            ));

            if ($jefe_id) {
                return get_userdata($jefe_id);
            }
        }

        return null;
    }

    /**
     * Obtener destinatarios para notificaciones de QA
     */
    private function get_destinatarios_qa($tarea) {
        $emails = array();

        // Jefe del empleado
        $jefe = $this->get_jefe_usuario($tarea->asignado_a);
        if ($jefe) {
            $emails[] = $jefe->user_email;
        }

        // TODO: Agregar usuario QA específico si está configurado

        return array_unique($emails);
    }

    // =========================================================================
    // CONFIGURACIÓN
    // =========================================================================

    /**
     * Obtener todos los tipos de notificaciones
     */
    public static function get_tipos() {
        return self::TIPOS;
    }

    /**
     * Obtener configuración actual
     */
    public static function get_config() {
        if (null === self::$config) {
            self::get_instance();
        }
        return self::$config;
    }

    /**
     * Guardar configuración
     */
    public static function save_config($config) {
        $clean = array();
        foreach (array_keys(self::TIPOS) as $tipo) {
            $clean[$tipo] = isset($config[$tipo]) && $config[$tipo];
        }

        update_option('ga_notificaciones_config', $clean);
        self::$config = $clean;

        return true;
    }

    // =========================================================================
    // MÉTODOS ESTÁTICOS PARA USO DIRECTO
    // =========================================================================

    /**
     * Disparar notificación de tarea asignada
     */
    public static function tarea_asignada($tarea_id, $usuario_id) {
        do_action('ga_tarea_asignada', $tarea_id, $usuario_id);
    }

    /**
     * Disparar notificación de cambio de estado en tarea
     */
    public static function tarea_estado_cambio($tarea_id, $estado_anterior, $estado_nuevo, $nota = '') {
        do_action('ga_tarea_estado_cambiado', $tarea_id, $estado_anterior, $estado_nuevo, $nota);
    }

    /**
     * Disparar notificación de aplicante registrado
     */
    public static function aplicante_registrado($aplicante_id, $password_temp = '') {
        do_action('ga_aplicante_registrado', $aplicante_id, $password_temp);
    }

    /**
     * Disparar notificación de aplicación creada
     */
    public static function aplicacion_creada($aplicacion_id, $orden_id) {
        do_action('ga_aplicacion_creada', $aplicacion_id, $orden_id);
    }

    /**
     * Disparar notificación de cambio de estado en aplicación
     */
    public static function aplicacion_estado_cambio($aplicacion_id, $estado_anterior, $estado_nuevo) {
        do_action('ga_aplicacion_estado_cambio', $aplicacion_id, $estado_anterior, $estado_nuevo);
    }
}
