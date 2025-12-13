<?php
/**
 * Módulo: Aplicaciones a Órdenes de Trabajo
 *
 * Gestión de la relación M:N entre Aplicantes y Órdenes de Trabajo.
 * Cada aplicación representa una postulación de un aplicante a una orden.
 *
 * Funcionalidades principales:
 * - CRUD de aplicaciones
 * - Gestión de flujo de estados (postulación → evaluación → contratación)
 * - Mensajes entre aplicante y cliente
 * - Sistema de calificaciones bidireccional
 * - Notificaciones de cambios de estado
 *
 * Tabla: wp_ga_aplicaciones_orden
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Modules
 * @since      1.3.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class GA_Aplicaciones
 *
 * Maneja las postulaciones de aplicantes a órdenes de trabajo.
 *
 * @since 1.3.0
 */
class GA_Aplicaciones {

    // =========================================================================
    // CONSTANTES DE ENUMERACIÓN
    // =========================================================================

    /**
     * Estados posibles de una aplicación
     *
     * Flujo típico:
     * PENDIENTE → EN_REVISION → PRESELECCIONADO → ENTREVISTA → ACEPTADA/RECHAZADA
     *                                                       → CONTRATADO (trabajo asignado)
     *
     * @var array
     */
    const ESTADOS = array(
        'PENDIENTE'      => 'Pendiente',         // Recién aplicó, esperando revisión
        'EN_REVISION'    => 'En Revisión',       // El cliente está evaluando
        'PRESELECCIONADO'=> 'Preseleccionado',   // Pasó primera revisión
        'ENTREVISTA'     => 'Entrevista',        // Citado a entrevista/llamada
        'ACEPTADA'       => 'Aceptada',          // Aprobado, pendiente formalizar
        'RECHAZADA'      => 'Rechazada',         // No seleccionado
        'CONTRATADO'     => 'Contratado',        // Formalizado y trabajando
        'RETIRADA'       => 'Retirada',          // El aplicante retiró su postulación
    );

    // =========================================================================
    // MÉTODOS DE LECTURA (GET)
    // =========================================================================

    /**
     * Obtiene todas las aplicaciones con filtros
     *
     * @since 1.3.0
     *
     * @param array $args {
     *     Argumentos de filtrado.
     *
     *     @type int    $orden_id     Filtrar por orden de trabajo.
     *     @type int    $aplicante_id Filtrar por aplicante.
     *     @type string $estado       Filtrar por estado.
     *     @type string $orderby      Campo para ordenar.
     *     @type string $order        Dirección: 'ASC' o 'DESC'.
     *     @type int    $limit        Límite de resultados.
     *     @type int    $offset       Desplazamiento.
     * }
     *
     * @return array Lista de aplicaciones con datos relacionados.
     */
    public static function get_all($args = array()) {
        global $wpdb;

        $defaults = array(
            'orden_id'     => 0,
            'aplicante_id' => 0,
            'estado'       => '',
            'orderby'      => 'created_at',
            'order'        => 'DESC',
            'limit'        => 0,
            'offset'       => 0,
        );

        $args = wp_parse_args($args, $defaults);

        $table_apps = $wpdb->prefix . 'ga_aplicaciones_orden';
        $table_ordenes = $wpdb->prefix . 'ga_ordenes_trabajo';
        $table_aplicantes = $wpdb->prefix . 'ga_aplicantes';

        // Query con JOINs para obtener información relacionada
        $sql = "SELECT a.*,
                       o.codigo as orden_codigo,
                       o.titulo as orden_titulo,
                       o.estado as orden_estado,
                       o.categoria as orden_categoria,
                       o.tipo_pago as orden_tipo_pago,
                       ap.nombre_completo as aplicante_nombre,
                       ap.email as aplicante_email,
                       ap.tipo as aplicante_tipo,
                       ap.calificacion_promedio as aplicante_rating
                FROM {$table_apps} a
                LEFT JOIN {$table_ordenes} o ON a.orden_trabajo_id = o.id
                LEFT JOIN {$table_aplicantes} ap ON a.aplicante_id = ap.id
                WHERE 1=1";

        $params = array();

        // -------------------------------------------------------------------------
        // Filtros
        // -------------------------------------------------------------------------

        if (!empty($args['orden_id'])) {
            $sql .= " AND a.orden_trabajo_id = %d";
            $params[] = absint($args['orden_id']);
        }

        if (!empty($args['aplicante_id'])) {
            $sql .= " AND a.aplicante_id = %d";
            $params[] = absint($args['aplicante_id']);
        }

        if (!empty($args['estado'])) {
            $sql .= " AND a.estado = %s";
            $params[] = sanitize_text_field($args['estado']);
        }

        // -------------------------------------------------------------------------
        // Ordenamiento
        // -------------------------------------------------------------------------

        $allowed_orderby = array(
            'id', 'estado', 'propuesta_monto', 'created_at', 'updated_at'
        );

        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY a.{$orderby} {$order}";

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
     * Obtiene una aplicación por ID
     *
     * Incluye información de la orden y el aplicante.
     *
     * @since 1.3.0
     *
     * @param int $id ID de la aplicación.
     *
     * @return object|null Objeto de la aplicación o null.
     */
    public static function get($id) {
        global $wpdb;

        $table_apps = $wpdb->prefix . 'ga_aplicaciones_orden';
        $table_ordenes = $wpdb->prefix . 'ga_ordenes_trabajo';
        $table_aplicantes = $wpdb->prefix . 'ga_aplicantes';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT a.*,
                    o.codigo as orden_codigo,
                    o.titulo as orden_titulo,
                    o.estado as orden_estado,
                    o.cliente_id,
                    ap.nombre_completo as aplicante_nombre,
                    ap.email as aplicante_email,
                    ap.usuario_wp_id as aplicante_wp_id
             FROM {$table_apps} a
             LEFT JOIN {$table_ordenes} o ON a.orden_trabajo_id = o.id
             LEFT JOIN {$table_aplicantes} ap ON a.aplicante_id = ap.id
             WHERE a.id = %d",
            absint($id)
        ));
    }

    /**
     * Obtiene una aplicación específica por orden y aplicante
     *
     * Útil para verificar si un aplicante ya aplicó a una orden.
     *
     * @since 1.3.0
     *
     * @param int $orden_id     ID de la orden.
     * @param int $aplicante_id ID del aplicante.
     *
     * @return object|null Objeto de la aplicación o null.
     */
    public static function get_by_orden_aplicante($orden_id, $aplicante_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE orden_trabajo_id = %d AND aplicante_id = %d",
            absint($orden_id),
            absint($aplicante_id)
        ));
    }

    /**
     * Obtiene las aplicaciones de un aplicante
     *
     * @since 1.3.0
     *
     * @param int    $aplicante_id ID del aplicante.
     * @param string $estado       Filtrar por estado (opcional).
     *
     * @return array Lista de aplicaciones.
     */
    public static function get_by_aplicante($aplicante_id, $estado = '') {
        return self::get_all(array(
            'aplicante_id' => $aplicante_id,
            'estado'       => $estado,
        ));
    }

    /**
     * Obtiene las aplicaciones para una orden
     *
     * @since 1.3.0
     *
     * @param int    $orden_id ID de la orden.
     * @param string $estado   Filtrar por estado (opcional).
     *
     * @return array Lista de aplicaciones.
     */
    public static function get_by_orden($orden_id, $estado = '') {
        return self::get_all(array(
            'orden_id' => $orden_id,
            'estado'   => $estado,
        ));
    }

    // =========================================================================
    // MÉTODOS DE ESCRITURA
    // =========================================================================

    /**
     * Crea una nueva aplicación (postulación)
     *
     * @since 1.3.0
     *
     * @param array $data {
     *     Datos de la aplicación.
     *
     *     @type int    $orden_trabajo_id ID de la orden (requerido).
     *     @type int    $aplicante_id     ID del aplicante (requerido).
     *     @type string $carta_presentacion Carta de presentación.
     *     @type float  $propuesta_monto    Monto propuesto.
     *     @type string $propuesta_tiempo   Tiempo estimado.
     *     @type string $disponibilidad     Disponibilidad para iniciar.
     * }
     *
     * @return array Resultado de la operación.
     */
    public static function aplicar($data) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';

        // -------------------------------------------------------------------------
        // Validaciones
        // -------------------------------------------------------------------------

        if (empty($data['orden_trabajo_id']) || empty($data['aplicante_id'])) {
            return array(
                'success' => false,
                'message' => __('Orden y aplicante son obligatorios.', 'gestionadmin-wolk'),
            );
        }

        $orden_id = absint($data['orden_trabajo_id']);
        $aplicante_id = absint($data['aplicante_id']);

        // Verificar que la orden existe y acepta aplicaciones
        $orden = GA_Ordenes_Trabajo::get($orden_id);
        if (!$orden) {
            return array(
                'success' => false,
                'message' => __('Orden de trabajo no encontrada.', 'gestionadmin-wolk'),
            );
        }

        if ($orden->estado !== 'PUBLICADA') {
            return array(
                'success' => false,
                'message' => __('Esta orden no está aceptando aplicaciones.', 'gestionadmin-wolk'),
            );
        }

        // Verificar fecha límite de aplicación
        if (!empty($orden->fecha_limite_aplicacion)) {
            $limite = strtotime($orden->fecha_limite_aplicacion);
            if (time() > $limite) {
                return array(
                    'success' => false,
                    'message' => __('El plazo para aplicar ha vencido.', 'gestionadmin-wolk'),
                );
            }
        }

        // Verificar que el aplicante existe y está verificado
        $aplicante = GA_Aplicantes::get($aplicante_id);
        if (!$aplicante) {
            return array(
                'success' => false,
                'message' => __('Aplicante no encontrado.', 'gestionadmin-wolk'),
            );
        }

        if ($aplicante->estado !== 'VERIFICADO') {
            return array(
                'success' => false,
                'message' => __('Tu cuenta debe estar verificada para aplicar.', 'gestionadmin-wolk'),
            );
        }

        // Verificar que no haya aplicado ya
        $existente = self::get_by_orden_aplicante($orden_id, $aplicante_id);
        if ($existente) {
            return array(
                'success' => false,
                'message' => __('Ya has aplicado a esta orden.', 'gestionadmin-wolk'),
            );
        }

        // -------------------------------------------------------------------------
        // Crear aplicación
        // -------------------------------------------------------------------------

        $record = array(
            'orden_trabajo_id'    => $orden_id,
            'aplicante_id'        => $aplicante_id,
            'estado'              => 'PENDIENTE',
            'carta_presentacion'  => isset($data['carta_presentacion']) ? wp_kses_post($data['carta_presentacion']) : '',
            'propuesta_monto'     => isset($data['propuesta_monto']) ? floatval($data['propuesta_monto']) : null,
            'propuesta_tiempo'    => isset($data['propuesta_tiempo']) ? sanitize_text_field($data['propuesta_tiempo']) : '',
            'disponibilidad'      => isset($data['disponibilidad']) ? sanitize_text_field($data['disponibilidad']) : '',
            'archivos_adjuntos'   => '', // JSON de archivos subidos
            'created_at'          => current_time('mysql'),
            'updated_at'          => current_time('mysql'),
        );

        $result = $wpdb->insert($table, $record, array(
            '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s'
        ));

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al crear la aplicación.', 'gestionadmin-wolk'),
            );
        }

        $id = $wpdb->insert_id;

        // TODO: Enviar notificación al cliente/admin

        return array(
            'success' => true,
            'id'      => $id,
            'message' => __('Aplicación enviada correctamente.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Actualiza una aplicación existente
     *
     * @since 1.3.0
     *
     * @param int   $id   ID de la aplicación.
     * @param array $data Datos a actualizar.
     *
     * @return array Resultado de la operación.
     */
    public static function update($id, $data) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';
        $id = absint($id);

        $aplicacion = self::get($id);
        if (!$aplicacion) {
            return array(
                'success' => false,
                'message' => __('Aplicación no encontrada.', 'gestionadmin-wolk'),
            );
        }

        // Solo se puede editar si está en estado PENDIENTE
        if ($aplicacion->estado !== 'PENDIENTE') {
            return array(
                'success' => false,
                'message' => __('No se puede modificar una aplicación en proceso.', 'gestionadmin-wolk'),
            );
        }

        $update = array(
            'updated_at' => current_time('mysql'),
        );

        if (isset($data['carta_presentacion'])) {
            $update['carta_presentacion'] = wp_kses_post($data['carta_presentacion']);
        }

        if (isset($data['propuesta_monto'])) {
            $update['propuesta_monto'] = floatval($data['propuesta_monto']);
        }

        if (isset($data['propuesta_tiempo'])) {
            $update['propuesta_tiempo'] = sanitize_text_field($data['propuesta_tiempo']);
        }

        if (isset($data['disponibilidad'])) {
            $update['disponibilidad'] = sanitize_text_field($data['disponibilidad']);
        }

        $result = $wpdb->update($table, $update, array('id' => $id));

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al actualizar la aplicación.', 'gestionadmin-wolk'),
            );
        }

        return array(
            'success' => true,
            'message' => __('Aplicación actualizada correctamente.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Retira una aplicación
     *
     * El aplicante puede retirar su postulación si aún no fue contratado.
     *
     * @since 1.3.0
     *
     * @param int    $id     ID de la aplicación.
     * @param string $motivo Motivo del retiro (opcional).
     *
     * @return array Resultado de la operación.
     */
    public static function retirar($id, $motivo = '') {
        global $wpdb;

        $aplicacion = self::get($id);
        if (!$aplicacion) {
            return array(
                'success' => false,
                'message' => __('Aplicación no encontrada.', 'gestionadmin-wolk'),
            );
        }

        // No se puede retirar si ya fue contratado
        if ($aplicacion->estado === 'CONTRATADO') {
            return array(
                'success' => false,
                'message' => __('No puedes retirarte de un trabajo ya asignado.', 'gestionadmin-wolk'),
            );
        }

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';
        $result = $wpdb->update(
            $table,
            array(
                'estado'        => 'RETIRADA',
                'notas_internas'=> $motivo ? 'Motivo de retiro: ' . sanitize_textarea_field($motivo) : '',
                'updated_at'    => current_time('mysql'),
            ),
            array('id' => $id)
        );

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al retirar la aplicación.', 'gestionadmin-wolk'),
            );
        }

        return array(
            'success' => true,
            'message' => __('Aplicación retirada correctamente.', 'gestionadmin-wolk'),
        );
    }

    // =========================================================================
    // MÉTODOS DE GESTIÓN DE ESTADO (para admin/cliente)
    // =========================================================================

    /**
     * Cambia el estado de una aplicación
     *
     * @since 1.3.0
     *
     * @param int    $id          ID de la aplicación.
     * @param string $nuevo_estado Nuevo estado.
     * @param string $notas       Notas internas (opcional).
     *
     * @return array Resultado de la operación.
     */
    public static function cambiar_estado($id, $nuevo_estado, $notas = '') {
        global $wpdb;

        $aplicacion = self::get($id);
        if (!$aplicacion) {
            return array(
                'success' => false,
                'message' => __('Aplicación no encontrada.', 'gestionadmin-wolk'),
            );
        }

        if (!array_key_exists($nuevo_estado, self::ESTADOS)) {
            return array(
                'success' => false,
                'message' => __('Estado no válido.', 'gestionadmin-wolk'),
            );
        }

        // Validar transiciones permitidas
        $transiciones_validas = self::get_transiciones_validas($aplicacion->estado);
        if (!in_array($nuevo_estado, $transiciones_validas)) {
            return array(
                'success' => false,
                'message' => sprintf(
                    __('No se puede cambiar de "%s" a "%s".', 'gestionadmin-wolk'),
                    self::ESTADOS[$aplicacion->estado],
                    self::ESTADOS[$nuevo_estado]
                ),
            );
        }

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';

        $update_data = array(
            'estado'     => $nuevo_estado,
            'updated_at' => current_time('mysql'),
        );

        // Agregar notas si se proporcionan
        if (!empty($notas)) {
            $notas_actuales = $aplicacion->notas_internas ? $aplicacion->notas_internas . "\n\n" : '';
            $notas_actuales .= '[' . current_time('mysql') . '] ' . sanitize_textarea_field($notas);
            $update_data['notas_internas'] = $notas_actuales;
        }

        // Si se contrata, registrar fecha
        if ($nuevo_estado === 'CONTRATADO') {
            $update_data['fecha_contratacion'] = current_time('mysql');

            // También actualizar la orden de trabajo
            GA_Ordenes_Trabajo::cambiar_estado($aplicacion->orden_trabajo_id, 'ASIGNADA');
        }

        $result = $wpdb->update($table, $update_data, array('id' => $id));

        if ($result === false) {
            return array(
                'success' => false,
                'message' => __('Error al cambiar el estado.', 'gestionadmin-wolk'),
            );
        }

        // TODO: Enviar notificación al aplicante

        return array(
            'success'       => true,
            'estado_previo' => $aplicacion->estado,
            'estado_nuevo'  => $nuevo_estado,
            'message'       => __('Estado actualizado correctamente.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtiene las transiciones de estado válidas
     *
     * @since 1.3.0
     *
     * @param string $estado_actual Estado actual.
     *
     * @return array Lista de estados válidos para transición.
     */
    public static function get_transiciones_validas($estado_actual) {
        $transiciones = array(
            'PENDIENTE'      => array('EN_REVISION', 'RECHAZADA', 'RETIRADA'),
            'EN_REVISION'    => array('PRESELECCIONADO', 'RECHAZADA', 'RETIRADA'),
            'PRESELECCIONADO'=> array('ENTREVISTA', 'ACEPTADA', 'RECHAZADA', 'RETIRADA'),
            'ENTREVISTA'     => array('ACEPTADA', 'RECHAZADA', 'RETIRADA'),
            'ACEPTADA'       => array('CONTRATADO', 'RECHAZADA', 'RETIRADA'),
            'RECHAZADA'      => array(), // Estado final
            'CONTRATADO'     => array(), // Estado final
            'RETIRADA'       => array(), // Estado final
        );

        return isset($transiciones[$estado_actual]) ? $transiciones[$estado_actual] : array();
    }

    /**
     * Rechaza múltiples aplicaciones
     *
     * Útil cuando se selecciona un aplicante y se rechazan los demás.
     *
     * @since 1.3.0
     *
     * @param int   $orden_id      ID de la orden.
     * @param int   $excepto_id    ID de aplicación a excluir (el contratado).
     * @param string $motivo       Motivo del rechazo.
     *
     * @return int Número de aplicaciones rechazadas.
     */
    public static function rechazar_otras($orden_id, $excepto_id, $motivo = '') {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';
        $motivo_texto = $motivo ? $motivo : __('Orden asignada a otro aplicante.', 'gestionadmin-wolk');

        return $wpdb->query($wpdb->prepare(
            "UPDATE {$table}
             SET estado = 'RECHAZADA',
                 notas_internas = CONCAT(IFNULL(notas_internas, ''), %s),
                 updated_at = %s
             WHERE orden_trabajo_id = %d
             AND id != %d
             AND estado NOT IN ('RECHAZADA', 'RETIRADA', 'CONTRATADO')",
            "\n\n[" . current_time('mysql') . '] ' . $motivo_texto,
            current_time('mysql'),
            absint($orden_id),
            absint($excepto_id)
        ));
    }

    // =========================================================================
    // MÉTODOS DE CALIFICACIÓN
    // =========================================================================

    /**
     * Califica a un aplicante (el cliente califica al trabajador)
     *
     * @since 1.3.0
     *
     * @param int    $id           ID de la aplicación.
     * @param int    $calificacion Calificación (1-5).
     * @param string $comentario   Comentario del cliente.
     *
     * @return array Resultado de la operación.
     */
    public static function calificar_aplicante($id, $calificacion, $comentario = '') {
        global $wpdb;

        $aplicacion = self::get($id);
        if (!$aplicacion || $aplicacion->estado !== 'CONTRATADO') {
            return array(
                'success' => false,
                'message' => __('Solo se pueden calificar trabajos completados.', 'gestionadmin-wolk'),
            );
        }

        $calificacion = max(1, min(5, intval($calificacion)));

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';
        $result = $wpdb->update(
            $table,
            array(
                'calificacion_cliente'   => $calificacion,
                'comentario_cliente'     => sanitize_textarea_field($comentario),
                'fecha_calificacion_cli' => current_time('mysql'),
                'updated_at'             => current_time('mysql'),
            ),
            array('id' => $id)
        );

        if ($result !== false) {
            // Actualizar estadísticas del aplicante
            GA_Aplicantes::actualizar_estadisticas($aplicacion->aplicante_id);
        }

        return array(
            'success' => $result !== false,
            'message' => $result !== false
                ? __('Calificación registrada.', 'gestionadmin-wolk')
                : __('Error al registrar calificación.', 'gestionadmin-wolk'),
        );
    }

    /**
     * Califica al cliente (el aplicante califica al cliente)
     *
     * @since 1.3.0
     *
     * @param int    $id           ID de la aplicación.
     * @param int    $calificacion Calificación (1-5).
     * @param string $comentario   Comentario del aplicante.
     *
     * @return array Resultado de la operación.
     */
    public static function calificar_cliente($id, $calificacion, $comentario = '') {
        global $wpdb;

        $aplicacion = self::get($id);
        if (!$aplicacion || $aplicacion->estado !== 'CONTRATADO') {
            return array(
                'success' => false,
                'message' => __('Solo se pueden calificar trabajos completados.', 'gestionadmin-wolk'),
            );
        }

        $calificacion = max(1, min(5, intval($calificacion)));

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';
        $result = $wpdb->update(
            $table,
            array(
                'calificacion_aplicante'   => $calificacion,
                'comentario_aplicante'     => sanitize_textarea_field($comentario),
                'fecha_calificacion_apl'   => current_time('mysql'),
                'updated_at'               => current_time('mysql'),
            ),
            array('id' => $id)
        );

        return array(
            'success' => $result !== false,
            'message' => $result !== false
                ? __('Calificación registrada.', 'gestionadmin-wolk')
                : __('Error al registrar calificación.', 'gestionadmin-wolk'),
        );
    }

    // =========================================================================
    // MÉTODOS DE ESTADÍSTICAS
    // =========================================================================

    /**
     * Cuenta aplicaciones con filtros
     *
     * @since 1.3.0
     *
     * @param array $args Filtros.
     *
     * @return int Número de aplicaciones.
     */
    public static function count($args = array()) {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';

        $sql = "SELECT COUNT(*) FROM {$table} WHERE 1=1";
        $params = array();

        if (!empty($args['orden_id'])) {
            $sql .= " AND orden_trabajo_id = %d";
            $params[] = absint($args['orden_id']);
        }

        if (!empty($args['aplicante_id'])) {
            $sql .= " AND aplicante_id = %d";
            $params[] = absint($args['aplicante_id']);
        }

        if (!empty($args['estado'])) {
            $sql .= " AND estado = %s";
            $params[] = sanitize_text_field($args['estado']);
        }

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Obtiene estadísticas generales
     *
     * @since 1.3.0
     *
     * @return array Estadísticas.
     */
    public static function get_estadisticas() {
        global $wpdb;

        $table = $wpdb->prefix . 'ga_aplicaciones_orden';

        $por_estado = $wpdb->get_results(
            "SELECT estado, COUNT(*) as total
             FROM {$table}
             GROUP BY estado",
            OBJECT_K
        );

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $contratados = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE estado = 'CONTRATADO'");
        $pendientes = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE estado = 'PENDIENTE'");

        return array(
            'total'       => (int) $total,
            'contratados' => (int) $contratados,
            'pendientes'  => (int) $pendientes,
            'por_estado'  => $por_estado,
        );
    }

    // =========================================================================
    // MÉTODOS HELPER PARA ENUMS
    // =========================================================================

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
            'PENDIENTE'      => 'ga-badge-secondary',
            'EN_REVISION'    => 'ga-badge-info',
            'PRESELECCIONADO'=> 'ga-badge-warning',
            'ENTREVISTA'     => 'ga-badge-warning',
            'ACEPTADA'       => 'ga-badge-success',
            'RECHAZADA'      => 'ga-badge-danger',
            'CONTRATADO'     => 'ga-badge-primary',
            'RETIRADA'       => 'ga-badge-secondary',
        );

        return isset($clases[$estado]) ? $clases[$estado] : 'ga-badge-secondary';
    }
}
