<?php
/**
 * Módulo: Aplicantes (Freelancers/Empresas Externas)
 *
 * Gestión completa de aplicantes en el Marketplace.
 * Los aplicantes son personas naturales o empresas que se registran
 * para postularse a órdenes de trabajo publicadas.
 *
 * Funcionalidades principales:
 * - CRUD completo de aplicantes
 * - Vinculación opcional con usuarios WordPress
 * - Gestión de verificación y estados
 * - Perfiles con habilidades, portafolio, datos bancarios
 * - Estadísticas de rendimiento
 *
 * Tabla: wp_ga_aplicantes
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Modules
 * @since      1.3.0
 * @author     Wolk
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GA_Aplicantes
 *
 * Maneja todas las operaciones relacionadas con aplicantes.
 * Implementa el patrón estático para acceso global sin instanciación.
 *
 * @since 1.3.0
 */
class GA_Aplicantes {

    // =========================================================================
    // CONSTANTES DE ENUMERACIÓN
    // =========================================================================

    /**
     * Tipos de aplicante
     *
     * Define la naturaleza jurídica del aplicante:
     * - PERSONA_NATURAL: Individuo/freelancer
     * - EMPRESA: Empresa o persona jurídica
     *
     * @var array
     */
    const TIPOS = array(
        'PERSONA_NATURAL' => 'Persona Natural',
        'EMPRESA'         => 'Empresa',
    );

    /**
     * Estados posibles de un aplicante
     *
     * Flujo típico:
     * PENDIENTE_VERIFICACION → VERIFICADO (puede trabajar)
     *                       → RECHAZADO (no cumple requisitos)
     * VERIFICADO → SUSPENDIDO (por incumplimiento)
     *
     * @var array
     */
    const ESTADOS = array(
        'PENDIENTE_VERIFICACION' => 'Pendiente de Verificación',
        'VERIFICADO'             => 'Verificado',
        'RECHAZADO'              => 'Rechazado',
        'SUSPENDIDO'             => 'Suspendido',
    );

    /**
     * Métodos de pago disponibles
     *
     * Define cómo el aplicante prefiere recibir pagos.
     *
     * @var array
     */
    const METODOS_PAGO = array(
        'BINANCE'      => 'Binance (Crypto)',
        'WISE'         => 'Wise',
        'PAYPAL'       => 'PayPal',
        'PAYONEER'     => 'Payoneer',
        'STRIPE'       => 'Stripe',
        'TRANSFERENCIA'=> 'Transferencia Bancaria',
    );

    /**
     * Niveles de experiencia
     *
     * @var array
     */
    const NIVELES = array(
        'JUNIOR'   => 'Junior (0-2 años)',
        'SEMI_SR'  => 'Semi Senior (2-4 años)',
        'SENIOR'   => 'Senior (4-7 años)',
        'EXPERTO'  => 'Experto (7+ años)',
    );

    // =========================================================================
    // MÉTODOS DE LECTURA (GET)
    // =========================================================================

    /**
     * Obtiene todos los aplicantes con filtros opcionales
     *
     * @since 1.3.0
     *
     * @param array $args {
     *     Argumentos opcionales para filtrar y ordenar.
     *
     *     @type string $estado           Filtrar por estado específico.
     *     @type string $tipo             Filtrar por tipo (PERSONA_NATURAL, EMPRESA).
     *     @type string $metodo_pago      Filtrar por método de pago preferido.
     *     @type string $pais             Filtrar por país.
     *     @type bool   $solo_verificados Solo aplicantes verificados.
     *     @type string $buscar           Término de búsqueda.
     *     @type string $orderby          Campo para ordenar.
     *     @type string $order            Dirección: 'ASC' o 'DESC'.
     *     @type int    $limit            Límite de resultados.
     *     @type int    $offset           Desplazamiento para paginación.
     * }
     *
     * @return array Lista de objetos de aplicantes.
     */
    public static function get_all($args = array()) {
        global $wpdb;

        $defaults = array(
            'estado'           => '',
            'tipo'             => '',
            'metodo_pago'      => '',
            'pais'             => '',
            'solo_verificados' => false,
            'buscar'           => '',
            'orderby'          => 'created_at',
            'order'            => 'DESC',
            'limit'            => 0,
            'offset'           => 0,
        );

        $args = wp_parse_args($args, $defaults);

        $table = $wpdb->prefix . 'ga_aplicantes';

        $sql = "SELECT * FROM {$table} WHERE 1=1";
        $params = array();

        // -------------------------------------------------------------------------
        // Filtros
        // -------------------------------------------------------------------------

        if (!empty($args['estado'])) {
            $sql .= " AND estado = %s";
            $params[] = $args['estado'];
        }

        if ($args['solo_verificados']) {
            $sql .= " AND estado = 'VERIFICADO'";
        }

        if (!empty($args['tipo'])) {
            $sql .= " AND tipo = %s";
            $params[] = $args['tipo'];
        }

        if (!empty($args['metodo_pago'])) {
            $sql .= " AND metodo_pago_preferido = %s";
            $params[] = $args['metodo_pago'];
        }

        if (!empty($args['pais'])) {
            $sql .= " AND pais = %s";
            $params[] = $args['pais'];
        }

        if (!empty($args['buscar'])) {
            $buscar = '%' . $wpdb->esc_like($args['buscar']) . '%';
            $sql .= " AND (nombre_completo LIKE %s OR email LIKE %s OR telefono LIKE %s OR documento_numero LIKE %s)";
            $params[] = $buscar;
            $params[] = $buscar;
            $params[] = $buscar;
            $params[] = $buscar;
        }

        // -------------------------------------------------------------------------
        // Ordenamiento
        // -------------------------------------------------------------------------

        $allowed_orderby = array(
            'id', 'nombre_completo', 'email', 'tipo', 'estado', 'pais',
            'calificacion_promedio', 'trabajos_completados', 'created_at'
        );

        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY {$orderby} {$order}";

        // -------------------------------------------------------------------------
        // Límite y paginación
        // -------------------------------------------------------------------------

        if (!empty($args['limit'])) {
            $sql .= " LIMIT %d";
            $params[] = absint($args['limit']);

            if (!empty($args['offset'])) {
                $sql .= " OFFSET %d";
                $params[] = absint($args['offset']);
            }
        }

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Obtiene un aplicante por ID
     *
     * @since 1.3.0
     *
     * @param int $id ID del aplicante.
     *
     * @return object|null Objeto del aplicante o null si no existe.
     */
    public static function get($id) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicantes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            absint($id)
        ));
    }

    /**
     * Obtiene un aplicante por ID de usuario WordPress
     *
     * Útil para obtener el perfil de aplicante del usuario logueado.
     *
     * @since 1.3.0
     *
     * @param int $wp_user_id ID del usuario WordPress.
     *
     * @return object|null Objeto del aplicante o null si no existe.
     */
    public static function get_by_wp_user($wp_user_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicantes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE usuario_wp_id = %d",
            absint($wp_user_id)
        ));
    }

    /**
     * Obtiene un aplicante por email
     *
     * @since 1.3.0
     *
     * @param string $email Email del aplicante.
     *
     * @return object|null Objeto del aplicante o null si no existe.
     */
    public static function get_by_email($email) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicantes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE email = %s",
            sanitize_email($email)
        ));
    }

    // =========================================================================
    // MÉTODOS DE ESCRITURA (SAVE/DELETE)
    // =========================================================================

    /**
     * Guarda un aplicante (crear o actualizar)
     *
     * @since 1.3.0
     *
     * @param array $data Datos del aplicante.
     *
     * @return array Resultado de la operación.
     */
    public static function save($data) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicantes';
        $id = isset($data['id']) ? absint($data['id']) : 0;

        // -------------------------------------------------------------------------
        // Validaciones
        // -------------------------------------------------------------------------

        if (empty($data['nombre_completo'])) {
            return array(
                'success' => false,
                'message' => __('El nombre es obligatorio.', 'gestionadmin-wolk'),
            );
        }

        if (empty($data['email'])) {
            return array(
                'success' => false,
                'message' => __('El email es obligatorio.', 'gestionadmin-wolk'),
            );
        }

        // Validar email único
        $existing = self::get_by_email($data['email']);
        if ($existing && $existing->id != $id) {
            return array(
                'success' => false,
                'message' => __('Ya existe un aplicante con ese email.', 'gestionadmin-wolk'),
            );
        }

        // -------------------------------------------------------------------------
        // Preparar datos
        // -------------------------------------------------------------------------

        // Procesar arrays como JSON
        $habilidades = '';
        if (!empty($data['habilidades'])) {
            $habilidades = is_array($data['habilidades'])
                ? wp_json_encode($data['habilidades'])
                : $data['habilidades'];
        }

        $certificaciones = '';
        if (!empty($data['certificaciones'])) {
            $certificaciones = is_array($data['certificaciones'])
                ? wp_json_encode($data['certificaciones'])
                : $data['certificaciones'];
        }

        $datos_pago = '';
        if (!empty($data['datos_pago'])) {
            $datos_pago = is_array($data['datos_pago'])
                ? wp_json_encode($data['datos_pago'])
                : $data['datos_pago'];
        }

        $record = array(
            'usuario_wp_id'         => !empty($data['usuario_wp_id']) ? absint($data['usuario_wp_id']) : null,
            'tipo'                  => isset($data['tipo']) ? sanitize_text_field($data['tipo']) : 'PERSONA_NATURAL',
            'nombre_completo'       => sanitize_text_field($data['nombre_completo']),
            'nombre_comercial'      => isset($data['nombre_comercial']) ? sanitize_text_field($data['nombre_comercial']) : '',
            'email'                 => sanitize_email($data['email']),
            'telefono'              => isset($data['telefono']) ? sanitize_text_field($data['telefono']) : '',
            'pais'                  => isset($data['pais']) ? sanitize_text_field($data['pais']) : '',
            'ciudad'                => isset($data['ciudad']) ? sanitize_text_field($data['ciudad']) : '',
            'direccion'             => isset($data['direccion']) ? sanitize_textarea_field($data['direccion']) : '',
            'documento_tipo'        => isset($data['documento_tipo']) ? sanitize_text_field($data['documento_tipo']) : '',
            'documento_numero'      => isset($data['documento_numero']) ? sanitize_text_field($data['documento_numero']) : '',
            'titulo_profesional'    => isset($data['titulo_profesional']) ? sanitize_text_field($data['titulo_profesional']) : '',
            'bio'                   => isset($data['bio']) ? wp_kses_post($data['bio']) : '',
            'habilidades'           => $habilidades,
            'nivel_experiencia'     => isset($data['nivel_experiencia']) ? sanitize_text_field($data['nivel_experiencia']) : 'JUNIOR',
            'anos_experiencia'      => isset($data['anos_experiencia']) ? absint($data['anos_experiencia']) : 0,
            'tarifa_hora_min'       => isset($data['tarifa_hora_min']) ? floatval($data['tarifa_hora_min']) : null,
            'tarifa_hora_max'       => isset($data['tarifa_hora_max']) ? floatval($data['tarifa_hora_max']) : null,
            'disponibilidad_horas'  => isset($data['disponibilidad_horas']) ? absint($data['disponibilidad_horas']) : 40,
            'disponible_inmediato'  => isset($data['disponible_inmediato']) ? 1 : 0,
            'portfolio_url'         => isset($data['portfolio_url']) ? esc_url_raw($data['portfolio_url']) : '',
            'linkedin_url'          => isset($data['linkedin_url']) ? esc_url_raw($data['linkedin_url']) : '',
            'github_url'            => isset($data['github_url']) ? esc_url_raw($data['github_url']) : '',
            'cv_archivo'            => isset($data['cv_archivo']) ? sanitize_text_field($data['cv_archivo']) : '',
            'certificaciones'       => $certificaciones,
            'metodo_pago_preferido' => isset($data['metodo_pago_preferido']) ? sanitize_text_field($data['metodo_pago_preferido']) : '',
            'datos_pago'            => $datos_pago,
            'estado'                => isset($data['estado']) ? sanitize_text_field($data['estado']) : 'PENDIENTE_VERIFICACION',
            'notas_admin'           => isset($data['notas_admin']) ? sanitize_textarea_field($data['notas_admin']) : '',
            'updated_at'            => current_time('mysql'),
        );

        $format = array(
            '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%f', '%d', '%d',
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
        );

        // -------------------------------------------------------------------------
        // Insertar o actualizar
        // -------------------------------------------------------------------------

        if ($id > 0) {
            $result = $wpdb->update(
                $table,
                $record,
                array('id' => $id),
                $format,
                array('%d')
            );

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Error al actualizar el aplicante.', 'gestionadmin-wolk'),
                );
            }

        } else {
            $record['created_at'] = current_time('mysql');
            $format[] = '%s';

            $result = $wpdb->insert($table, $record, $format);

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Error al crear el aplicante.', 'gestionadmin-wolk'),
                );
            }

            $id = $wpdb->insert_id;
        }

        return array(
            'success' => true,
            'id'      => $id,
            'message' => __('Aplicante guardado correctamente.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Elimina un aplicante
     *
     * Solo permite eliminar si no tiene aplicaciones activas.
     *
     * @since 1.3.0
     *
     * @param int $id ID del aplicante a eliminar.
     *
     * @return array Resultado de la operación.
     */
    public static function delete($id) {
        global $wpdb;

        $id = absint($id);
        $aplicante = self::get($id);

        if (!$aplicante) {
            return array(
                'success' => false,
                'message' => __('Aplicante no encontrado.', 'gestionadmin-wolk'),
            );
        }

        // Verificar aplicaciones activas
        $table_apps = $wpdb->prefix . 'ga_aplicaciones_orden';
        $apps_activas = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_apps}
             WHERE aplicante_id = %d
             AND estado NOT IN ('RECHAZADA', 'RETIRADA')",
            $id
        ));

        if ($apps_activas > 0) {
            return array(
                'success' => false,
                'message' => __('No se puede eliminar: tiene aplicaciones activas.', 'gestionadmin-wolk'),
            );
        }

        // Eliminar
        $table = $wpdb->prefix . 'ga_aplicantes';
        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al eliminar el aplicante.', 'gestionadmin-wolk'),
            );
        }

        return array(
            'success' => true,
            'message' => __('Aplicante eliminado correctamente.', 'gestionadmin-wolk'),
        );
    }

    // =========================================================================
    // MÉTODOS DE ESTADO Y VERIFICACIÓN
    // =========================================================================

    /**
     * Cambia el estado de un aplicante
     *
     * @since 1.3.0
     *
     * @param int    $id          ID del aplicante.
     * @param string $nuevo_estado Nuevo estado.
     * @param string $notas       Notas del admin (opcional).
     *
     * @return array Resultado de la operación.
     */
    public static function cambiar_estado($id, $nuevo_estado, $notas = '') {
        global $wpdb;

        $aplicante = self::get($id);

        if (!$aplicante) {
            return array(
                'success' => false,
                'message' => __('Aplicante no encontrado.', 'gestionadmin-wolk'),
            );
        }

        if (!array_key_exists($nuevo_estado, self::ESTADOS)) {
            return array(
                'success' => false,
                'message' => __('Estado no válido.', 'gestionadmin-wolk'),
            );
        }

        $table = $wpdb->prefix . 'ga_aplicantes';
        $update_data = array(
            'estado'     => $nuevo_estado,
            'updated_at' => current_time('mysql'),
        );

        // Agregar fecha de verificación si aplica
        if ($nuevo_estado === 'VERIFICADO' && empty($aplicante->fecha_verificacion)) {
            $update_data['fecha_verificacion'] = current_time('mysql');
            $update_data['verificado_por'] = get_current_user_id();
        }

        // Agregar notas si se proporcionan
        if (!empty($notas)) {
            $notas_actuales = $aplicante->notas_admin ? $aplicante->notas_admin . "\n\n" : '';
            $notas_actuales .= '[' . current_time('mysql') . '] ' . $notas;
            $update_data['notas_admin'] = $notas_actuales;
        }

        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => $id)
        );

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al cambiar el estado.', 'gestionadmin-wolk'),
            );
        }

        return array(
            'success' => true,
            'message' => __('Estado actualizado correctamente.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Verifica si un aplicante puede aplicar a órdenes
     *
     * @since 1.3.0
     *
     * @param int $id ID del aplicante.
     *
     * @return bool True si puede aplicar.
     */
    public static function puede_aplicar($id) {
        $aplicante = self::get($id);
        return $aplicante && $aplicante->estado === 'VERIFICADO';
    }

    // =========================================================================
    // MÉTODOS DE ESTADÍSTICAS
    // =========================================================================

    /**
     * Actualiza las estadísticas de un aplicante
     *
     * Recalcula calificación promedio y contadores.
     *
     * @since 1.3.0
     *
     * @param int $id ID del aplicante.
     *
     * @return bool True si se actualizó correctamente.
     */
    public static function actualizar_estadisticas($id) {
        global $wpdb;

        $table_apps = $wpdb->prefix . 'ga_aplicaciones_orden';
        $table = $wpdb->prefix . 'ga_aplicantes';

        // Contar trabajos completados
        $completados = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_apps}
             WHERE aplicante_id = %d AND estado = 'CONTRATADO'",
            $id
        ));

        // Calcular promedio de calificaciones
        $promedio = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(calificacion_cliente) FROM {$table_apps}
             WHERE aplicante_id = %d AND calificacion_cliente IS NOT NULL",
            $id
        ));

        // Actualizar
        return $wpdb->update(
            $table,
            array(
                'trabajos_completados'   => absint($completados),
                'calificacion_promedio'  => $promedio ? round($promedio, 2) : null,
                'updated_at'             => current_time('mysql'),
            ),
            array('id' => $id)
        ) !== false;
    }

    /**
     * Obtiene estadísticas generales de aplicantes
     *
     * @since 1.3.0
     *
     * @return array Estadísticas.
     */
    public static function get_estadisticas() {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicantes';

        $por_estado = $wpdb->get_results(
            "SELECT estado, COUNT(*) as total
             FROM {$table}
             GROUP BY estado",
            OBJECT_K
        );

        $por_tipo = $wpdb->get_results(
            "SELECT tipo, COUNT(*) as total
             FROM {$table}
             GROUP BY tipo",
            OBJECT_K
        );

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $verificados = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE estado = 'VERIFICADO'");

        return array(
            'total'       => (int) $total,
            'verificados' => (int) $verificados,
            'por_estado'  => $por_estado,
            'por_tipo'    => $por_tipo,
        );
    }

    /**
     * Obtiene el conteo de aplicaciones de un aplicante
     *
     * @since 1.3.0
     *
     * @param int    $id     ID del aplicante.
     * @param string $estado Estado específico (opcional).
     *
     * @return int Número de aplicaciones.
     */
    public static function count_aplicaciones($id, $estado = '') {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';

        $sql = "SELECT COUNT(*) FROM {$table} WHERE aplicante_id = %d";
        $params = array(absint($id));

        if (!empty($estado)) {
            $sql .= " AND estado = %s";
            $params[] = sanitize_text_field($estado);
        }

        return (int) $wpdb->get_var($wpdb->prepare($sql, $params));
    }

    // =========================================================================
    // MÉTODOS DE REGISTRO DESDE FRONTEND
    // =========================================================================

    /**
     * Registra un nuevo aplicante desde el frontend
     *
     * Crea el usuario WordPress y el perfil de aplicante.
     *
     * @since 1.3.0
     *
     * @param array $data Datos del registro.
     *
     * @return array Resultado de la operación.
     */
    public static function registrar($data) {
        // Validar datos requeridos
        if (empty($data['email']) || empty($data['password']) || empty($data['nombre_completo'])) {
            return array(
                'success' => false,
                'message' => __('Todos los campos son obligatorios.', 'gestionadmin-wolk'),
            );
        }

        $email = sanitize_email($data['email']);

        // Verificar si ya existe
        if (email_exists($email)) {
            return array(
                'success' => false,
                'message' => __('Ya existe una cuenta con ese email.', 'gestionadmin-wolk'),
            );
        }

        // Crear usuario WordPress
        $username = sanitize_user(strstr($email, '@', true));
        $username = self::generate_unique_username($username);

        $wp_user_id = wp_create_user($username, $data['password'], $email);

        if (is_wp_error($wp_user_id)) {
            return array(
                'success' => false,
                'message' => $wp_user_id->get_error_message(),
            );
        }

        // Asignar rol de aplicante
        $user = new WP_User($wp_user_id);
        $user->set_role('ga_aplicante');

        // Actualizar nombre
        wp_update_user(array(
            'ID'           => $wp_user_id,
            'display_name' => sanitize_text_field($data['nombre_completo']),
            'first_name'   => sanitize_text_field($data['nombre_completo']),
        ));

        // Crear perfil de aplicante
        $data['usuario_wp_id'] = $wp_user_id;
        $data['estado'] = 'PENDIENTE_VERIFICACION';

        $result = self::save($data);

        if (!$result['success']) {
            // Rollback: eliminar usuario WordPress
            wp_delete_user($wp_user_id);
            return $result;
        }

        return array(
            'success'     => true,
            'id'          => $result['id'],
            'wp_user_id'  => $wp_user_id,
            'message'     => __('Registro exitoso. Tu cuenta está pendiente de verificación.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Genera un username único
     *
     * @since 1.3.0
     *
     * @param string $base Base del username.
     *
     * @return string Username único.
     */
    private static function generate_unique_username($base) {
        $username = $base;
        $i = 1;

        while (username_exists($username)) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }

    // =========================================================================
    // MÉTODOS HELPER PARA ENUMS
    // =========================================================================

    /**
     * Obtiene todos los tipos
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de tipos.
     */
    public static function get_tipos() {
        return self::TIPOS;
    }

    /**
     * Obtiene todos los estados
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de estados.
     */
    public static function get_estados() {
        return self::ESTADOS;
    }

    /**
     * Obtiene todos los métodos de pago
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de métodos de pago.
     */
    public static function get_metodos_pago() {
        return self::METODOS_PAGO;
    }

    /**
     * Obtiene todos los niveles de experiencia
     *
     * @since 1.3.0
     *
     * @return array Array asociativo de niveles.
     */
    public static function get_niveles() {
        return self::NIVELES;
    }

    /**
     * Obtiene la etiqueta de un estado
     *
     * @since 1.3.0
     *
     * @param string $estado Código del estado.
     *
     * @return string Etiqueta legible.
     */
    public static function get_estado_label($estado) {
        return isset(self::ESTADOS[$estado]) ? self::ESTADOS[$estado] : $estado;
    }

    /**
     * Obtiene la clase CSS para un estado
     *
     * @since 1.3.0
     *
     * @param string $estado Código del estado.
     *
     * @return string Clase CSS.
     */
    public static function get_estado_class($estado) {
        $clases = array(
            'PENDIENTE_VERIFICACION' => 'ga-badge-warning',
            'VERIFICADO'             => 'ga-badge-success',
            'RECHAZADO'              => 'ga-badge-danger',
            'SUSPENDIDO'             => 'ga-badge-danger',
        );

        return isset($clases[$estado]) ? $clases[$estado] : 'ga-badge-secondary';
    }
}
