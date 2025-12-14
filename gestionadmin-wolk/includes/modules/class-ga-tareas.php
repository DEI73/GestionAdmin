<?php
/**
 * Módulo de Tareas
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Tareas {

    /**
     * Obtener todas las tareas con filtros
     *
     * Sprint 5-6: Agregado filtro por proyecto_id
     */
    public static function get_all($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';
        $table_usuarios = $wpdb->prefix . 'ga_usuarios';

        $defaults = array(
            'estado' => '',
            'asignado_a' => 0,
            'prioridad' => '',
            'proyecto_id' => 0, // Sprint 5-6: Filtro por proyecto
            'caso_id' => 0,     // Sprint 5-6: Filtro por caso
            'limit' => 50,
            'offset' => 0,
        );

        $args = wp_parse_args($args, $defaults);

        $sql = "SELECT t.*,
                       u_asig.display_name as asignado_nombre,
                       u_sup.display_name as supervisor_nombre
                FROM {$table} t
                LEFT JOIN {$wpdb->users} u_asig ON t.asignado_a = u_asig.ID
                LEFT JOIN {$wpdb->users} u_sup ON t.supervisor_id = u_sup.ID
                WHERE 1=1";

        // Filtro por estado
        if (!empty($args['estado'])) {
            $sql .= $wpdb->prepare(" AND t.estado = %s", $args['estado']);
        }

        // Filtro por usuario asignado
        if ($args['asignado_a'] > 0) {
            $sql .= $wpdb->prepare(" AND t.asignado_a = %d", $args['asignado_a']);
        }

        // Filtro por prioridad
        if (!empty($args['prioridad'])) {
            $sql .= $wpdb->prepare(" AND t.prioridad = %s", $args['prioridad']);
        }

        // Sprint 5-6: Filtro por proyecto
        if ($args['proyecto_id'] > 0) {
            $sql .= $wpdb->prepare(" AND t.proyecto_id = %d", $args['proyecto_id']);
        }

        // Sprint 5-6: Filtro por caso
        if ($args['caso_id'] > 0) {
            $sql .= $wpdb->prepare(" AND t.caso_id = %d", $args['caso_id']);
        }

        $sql .= " ORDER BY
                    CASE t.prioridad
                        WHEN 'URGENTE' THEN 1
                        WHEN 'ALTA' THEN 2
                        WHEN 'MEDIA' THEN 3
                        ELSE 4
                    END,
                    t.fecha_limite ASC,
                    t.created_at DESC";

        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);

        return $wpdb->get_results($sql);
    }

    /**
     * Obtener una tarea por ID
     */
    public static function get($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Guardar tarea con subtareas
     */
    public static function save($id, $data, $subtareas = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';
        $table_sub = $wpdb->prefix . 'ga_subtareas';

        if ($id > 0) {
            // Actualizar
            $result = $wpdb->update($table, $data, array('id' => $id));

            if ($result === false) {
                return new WP_Error('db_error', __('Error al actualizar tarea', 'gestionadmin-wolk'));
            }

            $tarea_id = $id;
        } else {
            // Insertar
            $data['numero'] = self::generate_numero();
            $data['created_by'] = get_current_user_id();
            $data['created_at'] = current_time('mysql');

            $result = $wpdb->insert($table, $data);

            if ($result === false) {
                return new WP_Error('db_error', __('Error al crear tarea', 'gestionadmin-wolk'));
            }

            $tarea_id = $wpdb->insert_id;
        }

        // Procesar subtareas
        if (!empty($subtareas)) {
            // Obtener subtareas existentes
            $existing_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT id FROM {$table_sub} WHERE tarea_id = %d",
                $tarea_id
            ));

            $updated_ids = array();

            foreach ($subtareas as $orden => $subtarea) {
                $sub_data = array(
                    'tarea_id' => $tarea_id,
                    'nombre' => sanitize_text_field($subtarea['nombre']),
                    'descripcion' => isset($subtarea['descripcion']) ? sanitize_textarea_field($subtarea['descripcion']) : null,
                    'minutos_estimados' => absint($subtarea['minutos_estimados'] ?? 15),
                    'orden' => isset($subtarea['orden']) ? absint($subtarea['orden']) : $orden,
                    'codigo' => sprintf('%d-%d', $tarea_id, $orden + 1),
                );

                if (!empty($subtarea['id']) && $subtarea['id'] > 0) {
                    // Actualizar
                    $wpdb->update($table_sub, $sub_data, array('id' => $subtarea['id']));
                    $updated_ids[] = $subtarea['id'];
                } else {
                    // Insertar
                    $sub_data['created_at'] = current_time('mysql');
                    $wpdb->insert($table_sub, $sub_data);
                    $updated_ids[] = $wpdb->insert_id;
                }
            }

            // Eliminar subtareas que ya no existen
            $to_delete = array_diff($existing_ids, $updated_ids);
            if (!empty($to_delete)) {
                $ids_str = implode(',', array_map('absint', $to_delete));
                $wpdb->query("DELETE FROM {$table_sub} WHERE id IN ({$ids_str})");
            }
        }

        // Actualizar porcentaje de avance
        self::update_porcentaje($tarea_id);

        return $tarea_id;
    }

    /**
     * Eliminar tarea
     */
    public static function delete($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';

        // Las subtareas se eliminan por CASCADE
        // Verificar si hay registros de horas
        $registros = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_registro_horas WHERE tarea_id = %d",
            $id
        ));

        if ($registros > 0) {
            return new WP_Error('has_registros', __('No se puede eliminar: tiene registros de horas', 'gestionadmin-wolk'));
        }

        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al eliminar', 'gestionadmin-wolk'));
        }

        return true;
    }

    /**
     * Obtener subtareas de una tarea
     */
    public static function get_subtareas($tarea_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_subtareas';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE tarea_id = %d ORDER BY orden ASC",
            $tarea_id
        ));
    }

    /**
     * Generar número de tarea
     */
    private static function generate_numero() {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';

        $year = date('Y');
        $last = $wpdb->get_var($wpdb->prepare(
            "SELECT numero FROM {$table} WHERE numero LIKE %s ORDER BY id DESC LIMIT 1",
            "TASK-{$year}-%"
        ));

        if ($last) {
            $parts = explode('-', $last);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }

        return sprintf('TASK-%s-%04d', $year, $num);
    }

    /**
     * Actualizar porcentaje de avance
     */
    private static function update_porcentaje($tarea_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';
        $table_sub = $wpdb->prefix . 'ga_subtareas';

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_sub} WHERE tarea_id = %d",
            $tarea_id
        ));

        if ($total > 0) {
            $completadas = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_sub} WHERE tarea_id = %d AND estado = 'COMPLETADA'",
                $tarea_id
            ));

            $porcentaje = round(($completadas / $total) * 100);
        } else {
            $porcentaje = 0;
        }

        $wpdb->update($table, array('porcentaje_avance' => $porcentaje), array('id' => $tarea_id));
    }

    /**
     * Obtener estados
     */
    public static function get_estados() {
        return array(
            'PENDIENTE' => __('Pendiente', 'gestionadmin-wolk'),
            'EN_PROGRESO' => __('En Progreso', 'gestionadmin-wolk'),
            'PAUSADA' => __('Pausada', 'gestionadmin-wolk'),
            'COMPLETADA' => __('Completada', 'gestionadmin-wolk'),
            'EN_QA' => __('En QA', 'gestionadmin-wolk'),
            'APROBADA_QA' => __('Aprobada QA', 'gestionadmin-wolk'),
            'EN_REVISION' => __('En Revisión', 'gestionadmin-wolk'),
            'APROBADA' => __('Aprobada', 'gestionadmin-wolk'),
            'RECHAZADA' => __('Rechazada', 'gestionadmin-wolk'),
            'PAGADA' => __('Pagada', 'gestionadmin-wolk'),
            'CANCELADA' => __('Cancelada', 'gestionadmin-wolk'),
        );
    }

    /**
     * Obtener prioridades
     */
    public static function get_prioridades() {
        return array(
            'BAJA' => __('Baja', 'gestionadmin-wolk'),
            'MEDIA' => __('Media', 'gestionadmin-wolk'),
            'ALTA' => __('Alta', 'gestionadmin-wolk'),
            'URGENTE' => __('Urgente', 'gestionadmin-wolk'),
        );
    }

    // =========================================================================
    // TIMER
    // =========================================================================

    /**
     * Iniciar timer
     */
    public static function timer_start($tarea_id, $subtarea_id, $usuario_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_registro_horas';

        // Verificar que no haya un timer activo
        $activo = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE usuario_id = %d AND estado = 'ACTIVO'",
            $usuario_id
        ));

        if ($activo) {
            return new WP_Error('timer_active', __('Ya tienes un timer activo', 'gestionadmin-wolk'));
        }

        // Obtener tarea para proyecto y contrato
        $tarea = self::get($tarea_id);

        $data = array(
            'usuario_id' => $usuario_id,
            'tarea_id' => $tarea_id,
            'subtarea_id' => $subtarea_id > 0 ? $subtarea_id : null,
            'proyecto_id' => $tarea ? $tarea->proyecto_id : null,
            'fecha' => current_time('Y-m-d'),
            'hora_inicio' => current_time('mysql'),
            'estado' => 'ACTIVO',
            'created_at' => current_time('mysql'),
        );

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Error al iniciar timer', 'gestionadmin-wolk'));
        }

        // Actualizar estado de tarea
        $wpdb->update(
            $wpdb->prefix . 'ga_tareas',
            array('estado' => 'EN_PROGRESO'),
            array('id' => $tarea_id)
        );

        // Actualizar estado de subtarea si aplica
        if ($subtarea_id > 0) {
            $wpdb->update(
                $wpdb->prefix . 'ga_subtareas',
                array('estado' => 'EN_PROGRESO', 'fecha_inicio' => current_time('mysql')),
                array('id' => $subtarea_id)
            );
        }

        return $wpdb->insert_id;
    }

    /**
     * Pausar timer
     */
    public static function timer_pause($registro_id, $motivo, $nota = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_registro_horas';
        $table_pausas = $wpdb->prefix . 'ga_pausas_timer';

        // Verificar que el registro esté activo
        $registro = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d AND estado = 'ACTIVO'",
            $registro_id
        ));

        if (!$registro) {
            return new WP_Error('not_active', __('El timer no está activo', 'gestionadmin-wolk'));
        }

        // Crear registro de pausa
        $result = $wpdb->insert($table_pausas, array(
            'registro_hora_id' => $registro_id,
            'hora_pausa' => current_time('mysql'),
            'motivo' => $motivo,
            'nota' => $nota,
            'created_at' => current_time('mysql'),
        ));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al pausar timer', 'gestionadmin-wolk'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Reanudar timer
     */
    public static function timer_resume($registro_id) {
        global $wpdb;
        $table_pausas = $wpdb->prefix . 'ga_pausas_timer';

        // Obtener la última pausa sin reanudación
        $pausa = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_pausas}
             WHERE registro_hora_id = %d AND hora_reanudacion IS NULL
             ORDER BY id DESC LIMIT 1",
            $registro_id
        ));

        if (!$pausa) {
            return new WP_Error('no_pause', __('No hay pausa activa', 'gestionadmin-wolk'));
        }

        $hora_reanudacion = current_time('mysql');
        $minutos = round((strtotime($hora_reanudacion) - strtotime($pausa->hora_pausa)) / 60);

        $result = $wpdb->update(
            $table_pausas,
            array(
                'hora_reanudacion' => $hora_reanudacion,
                'minutos' => $minutos,
            ),
            array('id' => $pausa->id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Error al reanudar timer', 'gestionadmin-wolk'));
        }

        return true;
    }

    /**
     * Detener timer
     */
    public static function timer_stop($registro_id, $descripcion = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_registro_horas';
        $table_pausas = $wpdb->prefix . 'ga_pausas_timer';

        // Obtener registro
        $registro = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $registro_id
        ));

        if (!$registro) {
            return new WP_Error('not_found', __('Registro no encontrado', 'gestionadmin-wolk'));
        }

        // Cerrar pausas abiertas
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table_pausas}
             SET hora_reanudacion = %s,
                 minutos = TIMESTAMPDIFF(MINUTE, hora_pausa, %s)
             WHERE registro_hora_id = %d AND hora_reanudacion IS NULL",
            current_time('mysql'),
            current_time('mysql'),
            $registro_id
        ));

        // Calcular tiempos
        $hora_fin = current_time('mysql');
        $minutos_totales = round((strtotime($hora_fin) - strtotime($registro->hora_inicio)) / 60);

        $minutos_pausas = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(minutos), 0) FROM {$table_pausas} WHERE registro_hora_id = %d",
            $registro_id
        ));

        $minutos_efectivos = max(0, $minutos_totales - $minutos_pausas);

        // Actualizar registro
        $result = $wpdb->update($table, array(
            'hora_fin' => $hora_fin,
            'minutos_totales' => $minutos_totales,
            'minutos_pausas' => $minutos_pausas,
            'minutos_efectivos' => $minutos_efectivos,
            'descripcion' => $descripcion,
            'estado' => 'BORRADOR',
        ), array('id' => $registro_id));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al detener timer', 'gestionadmin-wolk'));
        }

        // Actualizar minutos reales en tarea
        self::update_minutos_reales($registro->tarea_id);

        // Actualizar subtarea si aplica
        if ($registro->subtarea_id) {
            $wpdb->update(
                $wpdb->prefix . 'ga_subtareas',
                array('fecha_fin' => $hora_fin),
                array('id' => $registro->subtarea_id)
            );
            self::update_minutos_subtarea($registro->subtarea_id);
        }

        return array(
            'minutos_totales' => $minutos_totales,
            'minutos_efectivos' => $minutos_efectivos,
        );
    }

    /**
     * Obtener timer activo de un usuario
     */
    public static function get_active_timer($usuario_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_registro_horas';
        $table_pausas = $wpdb->prefix . 'ga_pausas_timer';

        $registro = $wpdb->get_row($wpdb->prepare(
            "SELECT r.*,
                    t.nombre as tarea_nombre,
                    t.numero as tarea_numero,
                    s.nombre as subtarea_nombre
             FROM {$table} r
             LEFT JOIN {$wpdb->prefix}ga_tareas t ON r.tarea_id = t.id
             LEFT JOIN {$wpdb->prefix}ga_subtareas s ON r.subtarea_id = s.id
             WHERE r.usuario_id = %d AND r.estado = 'ACTIVO'",
            $usuario_id
        ));

        if (!$registro) {
            return array('active' => false);
        }

        // Verificar si está en pausa
        $pausa_activa = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_pausas}
             WHERE registro_hora_id = %d AND hora_reanudacion IS NULL",
            $registro->id
        ));

        return array(
            'active' => true,
            'registro_id' => $registro->id,
            'tarea_id' => $registro->tarea_id,
            'tarea_nombre' => $registro->tarea_nombre,
            'tarea_numero' => $registro->tarea_numero,
            'subtarea_id' => $registro->subtarea_id,
            'subtarea_nombre' => $registro->subtarea_nombre,
            'hora_inicio' => $registro->hora_inicio,
            'is_paused' => !empty($pausa_activa),
            'pausa_inicio' => $pausa_activa ? $pausa_activa->hora_pausa : null,
        );
    }

    /**
     * Actualizar minutos reales de tarea
     */
    private static function update_minutos_reales($tarea_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';
        $table_reg = $wpdb->prefix . 'ga_registro_horas';

        $minutos = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(minutos_efectivos), 0) FROM {$table_reg} WHERE tarea_id = %d",
            $tarea_id
        ));

        $wpdb->update($table, array('minutos_reales' => absint($minutos)), array('id' => $tarea_id));
    }

    /**
     * Actualizar minutos reales de subtarea
     */
    private static function update_minutos_subtarea($subtarea_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_subtareas';
        $table_reg = $wpdb->prefix . 'ga_registro_horas';

        $minutos = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(minutos_efectivos), 0) FROM {$table_reg} WHERE subtarea_id = %d",
            $subtarea_id
        ));

        $wpdb->update($table, array('minutos_reales' => absint($minutos)), array('id' => $subtarea_id));
    }

    /**
     * Obtener motivos de pausa
     */
    public static function get_motivos_pausa() {
        return array(
            'ALMUERZO' => __('Almuerzo', 'gestionadmin-wolk'),
            'REUNION' => __('Reunión', 'gestionadmin-wolk'),
            'EMERGENCIA' => __('Emergencia', 'gestionadmin-wolk'),
            'DESCANSO' => __('Descanso', 'gestionadmin-wolk'),
            'OTRO' => __('Otro', 'gestionadmin-wolk'),
        );
    }

    // =========================================================================
    // CAMBIO DE ESTADO CON NOTIFICACIONES
    // =========================================================================

    /**
     * Cambiar estado de tarea
     *
     * Este método centraliza los cambios de estado para que
     * se disparen las notificaciones correspondientes.
     *
     * @param int    $tarea_id      ID de la tarea
     * @param string $nuevo_estado  Nuevo estado
     * @param string $nota          Nota opcional (ej: motivo rechazo)
     * @return bool|WP_Error
     *
     * @since 1.6.0
     */
    public static function cambiar_estado($tarea_id, $nuevo_estado, $nota = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';

        // Obtener tarea actual
        $tarea = self::get($tarea_id);
        if (!$tarea) {
            return new WP_Error('not_found', __('Tarea no encontrada', 'gestionadmin-wolk'));
        }

        $estado_anterior = $tarea->estado;

        // Si el estado es el mismo, no hacer nada
        if ($estado_anterior === $nuevo_estado) {
            return true;
        }

        // Preparar datos de actualización
        $update_data = array(
            'estado' => $nuevo_estado,
        );

        // Campos adicionales según el estado
        switch ($nuevo_estado) {
            case 'EN_QA':
                $update_data['fecha_envio_qa'] = current_time('mysql');
                break;

            case 'APROBADA_QA':
                $update_data['fecha_aprobacion_qa'] = current_time('mysql');
                $update_data['aprobado_qa_por'] = get_current_user_id();
                break;

            case 'RECHAZADA':
                if (!empty($nota)) {
                    $update_data['nota_rechazo'] = sanitize_textarea_field($nota);
                }
                break;

            case 'APROBADA':
                $update_data['fecha_aprobacion'] = current_time('mysql');
                $update_data['aprobado_por'] = get_current_user_id();
                break;

            case 'COMPLETADA':
                $update_data['fecha_completada'] = current_time('mysql');
                break;
        }

        // Actualizar en base de datos
        $result = $wpdb->update($table, $update_data, array('id' => $tarea_id));

        if ($result === false) {
            return new WP_Error('db_error', __('Error al actualizar estado', 'gestionadmin-wolk'));
        }

        /**
         * Hook: ga_tarea_estado_cambiado
         *
         * Se dispara cuando cambia el estado de una tarea.
         * Usado por GA_Notificaciones para enviar emails.
         *
         * @param int    $tarea_id        ID de la tarea
         * @param string $estado_anterior Estado antes del cambio
         * @param string $nuevo_estado    Nuevo estado
         * @param string $nota            Nota adicional (ej: motivo rechazo)
         */
        do_action('ga_tarea_estado_cambiado', $tarea_id, $estado_anterior, $nuevo_estado, $nota);

        return true;
    }

    /**
     * Asignar tarea a usuario
     *
     * @param int $tarea_id    ID de la tarea
     * @param int $usuario_id  ID del usuario WordPress
     * @return bool|WP_Error
     *
     * @since 1.6.0
     */
    public static function asignar($tarea_id, $usuario_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ga_tareas';

        // Obtener tarea actual
        $tarea = self::get($tarea_id);
        if (!$tarea) {
            return new WP_Error('not_found', __('Tarea no encontrada', 'gestionadmin-wolk'));
        }

        $asignado_anterior = $tarea->asignado_a;

        // Actualizar asignación
        $result = $wpdb->update(
            $table,
            array(
                'asignado_a' => $usuario_id,
                'fecha_asignacion' => current_time('mysql'),
            ),
            array('id' => $tarea_id)
        );

        if ($result === false) {
            return new WP_Error('db_error', __('Error al asignar tarea', 'gestionadmin-wolk'));
        }

        /**
         * Hook: ga_tarea_asignada
         *
         * Se dispara cuando se asigna una tarea a un usuario.
         * Usado por GA_Notificaciones para enviar email al empleado.
         *
         * @param int $tarea_id          ID de la tarea
         * @param int $usuario_id        Nuevo usuario asignado
         * @param int $asignado_anterior Usuario previamente asignado (0 si ninguno)
         */
        do_action('ga_tarea_asignada', $tarea_id, $usuario_id, $asignado_anterior);

        return true;
    }
}
