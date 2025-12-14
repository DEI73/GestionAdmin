<?php
/**
 * Timer REST API Endpoints
 *
 * Proporciona endpoints REST para controlar el timer desde el frontend.
 * Endpoints disponibles:
 * - POST /wp-json/gestionadmin/v1/timer/start
 * - POST /wp-json/gestionadmin/v1/timer/pause
 * - POST /wp-json/gestionadmin/v1/timer/resume
 * - POST /wp-json/gestionadmin/v1/timer/stop
 * - GET  /wp-json/gestionadmin/v1/timer/status
 *
 * @package    GestionAdmin_Wolk
 * @subpackage API
 * @since      1.8.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Timer_API {

    /**
     * Namespace de la API
     *
     * @var string
     */
    private $namespace = 'gestionadmin/v1';

    /**
     * Base de los endpoints
     *
     * @var string
     */
    private $rest_base = 'timer';

    /**
     * Instancia singleton
     *
     * @var GA_Timer_API
     */
    private static $instance = null;

    /**
     * Obtener instancia singleton
     *
     * @return GA_Timer_API
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
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Registrar rutas de la API
     */
    public function register_routes() {
        // POST /timer/start - Iniciar timer
        register_rest_route($this->namespace, '/' . $this->rest_base . '/start', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'start_timer'),
            'permission_callback' => array($this, 'check_permission'),
            'args'                => array(
                'tarea_id' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($value) {
                        return is_numeric($value) && $value > 0;
                    },
                ),
                'subtarea_id' => array(
                    'required'          => false,
                    'type'              => 'integer',
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));

        // POST /timer/pause - Pausar timer
        register_rest_route($this->namespace, '/' . $this->rest_base . '/pause', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'pause_timer'),
            'permission_callback' => array($this, 'check_permission'),
            'args'                => array(
                'registro_id' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($value) {
                        return is_numeric($value) && $value > 0;
                    },
                ),
                'motivo' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($value) {
                        $motivos_validos = array('ALMUERZO', 'REUNION', 'EMERGENCIA', 'DESCANSO', 'OTRO');
                        return in_array($value, $motivos_validos, true);
                    },
                ),
                'nota' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
            ),
        ));

        // POST /timer/resume - Reanudar timer
        register_rest_route($this->namespace, '/' . $this->rest_base . '/resume', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'resume_timer'),
            'permission_callback' => array($this, 'check_permission'),
            'args'                => array(
                'registro_id' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($value) {
                        return is_numeric($value) && $value > 0;
                    },
                ),
            ),
        ));

        // POST /timer/stop - Detener timer
        register_rest_route($this->namespace, '/' . $this->rest_base . '/stop', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'stop_timer'),
            'permission_callback' => array($this, 'check_permission'),
            'args'                => array(
                'registro_id' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($value) {
                        return is_numeric($value) && $value > 0;
                    },
                ),
                'descripcion' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
            ),
        ));

        // GET /timer/status - Obtener estado actual del timer
        register_rest_route($this->namespace, '/' . $this->rest_base . '/status', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_status'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    /**
     * Verificar permisos de usuario
     *
     * El usuario debe estar logueado y tener un registro en ga_usuarios
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function check_permission($request) {
        // Verificar que el usuario esta logueado
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_not_logged_in',
                __('Debes iniciar sesion para usar el timer.', 'gestionadmin-wolk'),
                array('status' => 401)
            );
        }

        // Verificar nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'ga_timer_nonce')) {
            return new WP_Error(
                'rest_invalid_nonce',
                __('Token de seguridad invalido. Recarga la pagina e intenta de nuevo.', 'gestionadmin-wolk'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Verificar que el registro pertenece al usuario actual
     *
     * @param int $registro_id
     * @return bool|WP_Error
     */
    private function verify_registro_ownership($registro_id) {
        global $wpdb;

        $usuario_id = get_current_user_id();
        $table = $wpdb->prefix . 'ga_registro_horas';

        $registro = $wpdb->get_row($wpdb->prepare(
            "SELECT usuario_id FROM {$table} WHERE id = %d",
            $registro_id
        ));

        if (!$registro) {
            return new WP_Error(
                'rest_not_found',
                __('Registro no encontrado.', 'gestionadmin-wolk'),
                array('status' => 404)
            );
        }

        if ((int) $registro->usuario_id !== $usuario_id) {
            return new WP_Error(
                'rest_forbidden',
                __('No tienes permiso para modificar este registro.', 'gestionadmin-wolk'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Verificar que la tarea esta asignada al usuario actual
     *
     * @param int $tarea_id
     * @return bool|WP_Error
     */
    private function verify_tarea_assignment($tarea_id) {
        global $wpdb;

        $usuario_id = get_current_user_id();
        $table = $wpdb->prefix . 'ga_tareas';

        $tarea = $wpdb->get_row($wpdb->prepare(
            "SELECT asignado_a, estado FROM {$table} WHERE id = %d",
            $tarea_id
        ));

        if (!$tarea) {
            return new WP_Error(
                'rest_not_found',
                __('Tarea no encontrada.', 'gestionadmin-wolk'),
                array('status' => 404)
            );
        }

        if ((int) $tarea->asignado_a !== $usuario_id) {
            return new WP_Error(
                'rest_forbidden',
                __('Esta tarea no te esta asignada.', 'gestionadmin-wolk'),
                array('status' => 403)
            );
        }

        // Verificar que la tarea no esta en un estado final
        $estados_bloqueados = array('COMPLETADA', 'APROBADA', 'PAGADA', 'CANCELADA');
        if (in_array($tarea->estado, $estados_bloqueados, true)) {
            return new WP_Error(
                'rest_task_closed',
                __('No puedes iniciar timer en una tarea cerrada.', 'gestionadmin-wolk'),
                array('status' => 400)
            );
        }

        return true;
    }

    /**
     * Endpoint: Iniciar timer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function start_timer($request) {
        // Cargar modulo de tareas
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';

        $tarea_id = $request->get_param('tarea_id');
        $subtarea_id = $request->get_param('subtarea_id');
        $usuario_id = get_current_user_id();

        // Verificar asignacion de tarea
        $check = $this->verify_tarea_assignment($tarea_id);
        if (is_wp_error($check)) {
            return $check;
        }

        // Verificar subtarea si se especifica
        if ($subtarea_id > 0) {
            global $wpdb;
            $subtarea = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ga_subtareas WHERE id = %d AND tarea_id = %d",
                $subtarea_id,
                $tarea_id
            ));

            if (!$subtarea) {
                return new WP_Error(
                    'rest_invalid_subtask',
                    __('La subtarea no pertenece a esta tarea.', 'gestionadmin-wolk'),
                    array('status' => 400)
                );
            }
        }

        // Intentar iniciar el timer
        $result = GA_Tareas::timer_start($tarea_id, $subtarea_id, $usuario_id);

        if (is_wp_error($result)) {
            return new WP_Error(
                'rest_timer_error',
                $result->get_error_message(),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success'     => true,
            'registro_id' => $result,
            'message'     => __('Timer iniciado correctamente.', 'gestionadmin-wolk'),
        ));
    }

    /**
     * Endpoint: Pausar timer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function pause_timer($request) {
        // Cargar modulo de tareas
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';

        $registro_id = $request->get_param('registro_id');
        $motivo = $request->get_param('motivo');
        $nota = $request->get_param('nota');

        // Verificar propiedad del registro
        $check = $this->verify_registro_ownership($registro_id);
        if (is_wp_error($check)) {
            return $check;
        }

        // Intentar pausar el timer
        $result = GA_Tareas::timer_pause($registro_id, $motivo, $nota);

        if (is_wp_error($result)) {
            return new WP_Error(
                'rest_timer_error',
                $result->get_error_message(),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success'  => true,
            'pausa_id' => $result,
            'message'  => __('Timer pausado correctamente.', 'gestionadmin-wolk'),
        ));
    }

    /**
     * Endpoint: Reanudar timer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function resume_timer($request) {
        // Cargar modulo de tareas
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';

        $registro_id = $request->get_param('registro_id');

        // Verificar propiedad del registro
        $check = $this->verify_registro_ownership($registro_id);
        if (is_wp_error($check)) {
            return $check;
        }

        // Intentar reanudar el timer
        $result = GA_Tareas::timer_resume($registro_id);

        if (is_wp_error($result)) {
            return new WP_Error(
                'rest_timer_error',
                $result->get_error_message(),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Timer reanudado correctamente.', 'gestionadmin-wolk'),
        ));
    }

    /**
     * Endpoint: Detener timer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function stop_timer($request) {
        // Cargar modulo de tareas
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';

        $registro_id = $request->get_param('registro_id');
        $descripcion = $request->get_param('descripcion');

        // Verificar propiedad del registro
        $check = $this->verify_registro_ownership($registro_id);
        if (is_wp_error($check)) {
            return $check;
        }

        // Intentar detener el timer
        $result = GA_Tareas::timer_stop($registro_id, $descripcion);

        if (is_wp_error($result)) {
            return new WP_Error(
                'rest_timer_error',
                $result->get_error_message(),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success'           => true,
            'message'           => __('Timer detenido correctamente.', 'gestionadmin-wolk'),
            'minutos_totales'   => $result['minutos_totales'],
            'minutos_efectivos' => $result['minutos_efectivos'],
        ));
    }

    /**
     * Endpoint: Obtener estado actual del timer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_status($request) {
        // Cargar modulo de tareas
        require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-tareas.php';

        $usuario_id = get_current_user_id();

        // Obtener timer activo
        $timer = GA_Tareas::get_active_timer($usuario_id);

        if (!$timer['active']) {
            return rest_ensure_response(array(
                'success' => true,
                'active'  => false,
                'message' => __('No tienes timer activo.', 'gestionadmin-wolk'),
            ));
        }

        // Calcular tiempo transcurrido
        $hora_inicio = strtotime($timer['hora_inicio']);
        $ahora = current_time('timestamp');
        $segundos_transcurridos = $ahora - $hora_inicio;

        // Si esta pausado, calcular hasta el inicio de la pausa
        if ($timer['is_paused'] && $timer['pausa_inicio']) {
            $pausa_inicio = strtotime($timer['pausa_inicio']);
            $segundos_transcurridos = $pausa_inicio - $hora_inicio;
        }

        return rest_ensure_response(array(
            'success'                => true,
            'active'                 => true,
            'registro_id'            => $timer['registro_id'],
            'tarea_id'               => $timer['tarea_id'],
            'tarea_numero'           => $timer['tarea_numero'],
            'tarea_nombre'           => $timer['tarea_nombre'],
            'subtarea_id'            => $timer['subtarea_id'],
            'subtarea_nombre'        => $timer['subtarea_nombre'],
            'hora_inicio'            => $timer['hora_inicio'],
            'is_paused'              => $timer['is_paused'],
            'pausa_inicio'           => $timer['pausa_inicio'],
            'segundos_transcurridos' => max(0, $segundos_transcurridos),
        ));
    }
}
