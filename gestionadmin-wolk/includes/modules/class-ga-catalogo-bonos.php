<?php
/**
 * Módulo de Catálogo de Bonos
 *
 * Gestiona el catálogo predefinido de bonos disponibles para órdenes de trabajo.
 * Los bonos son incentivos estandarizados que se pueden ofrecer a aplicantes.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes/Modules
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Catalogo_Bonos {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Catalogo_Bonos
     */
    private static $instance = null;

    /**
     * Nombre de la tabla
     *
     * @var string
     */
    private $table_name;

    /**
     * Categorías de bonos disponibles
     *
     * @var array
     */
    public static $categorias = array(
        'PRODUCTIVIDAD' => 'Productividad',
        'ASISTENCIA'    => 'Asistencia',
        'CALIDAD'       => 'Calidad',
        'COMUNICACION'  => 'Comunicación',
        'METAS'         => 'Metas',
        'OTRO'          => 'Otro',
    );

    /**
     * Frecuencias de pago
     *
     * @var array
     */
    public static $frecuencias = array(
        'UNICO'     => 'Único (una vez)',
        'SEMANAL'   => 'Semanal',
        'QUINCENAL' => 'Quincenal',
        'MENSUAL'   => 'Mensual',
    );

    /**
     * Obtener instancia única
     *
     * @return GA_Catalogo_Bonos
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
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ga_catalogo_bonos';
    }

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    /**
     * Obtener bono por ID
     *
     * @param int $id ID del bono
     * @return object|null
     */
    public function get($id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }

    /**
     * Obtener bono por código
     *
     * @param string $codigo Código del bono
     * @return object|null
     */
    public function get_por_codigo($codigo) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE codigo = %s",
            $codigo
        ));
    }

    /**
     * Listar bonos con filtros
     *
     * @param array $args Argumentos de filtrado
     * @return array Array con items, total y páginas
     */
    public function listar($args = array()) {
        global $wpdb;

        $defaults = array(
            'categoria' => '',
            'activo'    => '',
            'busqueda'  => '',
            'orderby'   => 'orden',
            'order'     => 'ASC',
            'page'      => 1,
            'per_page'  => 50,
        );
        $args = wp_parse_args($args, $defaults);

        // Construir WHERE
        $where = array('1=1');
        $params = array();

        if (!empty($args['categoria'])) {
            $where[] = 'categoria = %s';
            $params[] = $args['categoria'];
        }

        if ($args['activo'] !== '') {
            $where[] = 'activo = %d';
            $params[] = absint($args['activo']);
        }

        if (!empty($args['busqueda'])) {
            $where[] = '(nombre LIKE %s OR descripcion LIKE %s OR codigo LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['busqueda']) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $where_sql = implode(' AND ', $where);

        // Ordenamiento seguro
        $allowed_orderby = array('id', 'codigo', 'nombre', 'categoria', 'orden', 'created_at');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'orden';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

        // Contar total
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_sql}";
        if (!empty($params)) {
            $count_sql = $wpdb->prepare($count_sql, $params);
        }
        $total = $wpdb->get_var($count_sql);

        // Paginación
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Query principal
        $sql = "SELECT * FROM {$this->table_name}
                WHERE {$where_sql}
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d";

        $params[] = $args['per_page'];
        $params[] = $offset;

        $items = $wpdb->get_results($wpdb->prepare($sql, $params));

        return array(
            'items' => $items,
            'total' => intval($total),
            'pages' => ceil($total / $args['per_page']),
        );
    }

    /**
     * Obtener bonos activos para selección
     *
     * @return array Lista de bonos activos ordenados
     */
    public function get_activos() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT id, codigo, nombre, descripcion, tipo_valor, valor_default,
                    frecuencia, condicion_descripcion, categoria, icono
             FROM {$this->table_name}
             WHERE activo = 1
             ORDER BY categoria, orden, nombre"
        );
    }

    /**
     * Obtener bonos activos agrupados por categoría
     *
     * @return array Bonos agrupados por categoría
     */
    public function get_activos_por_categoria() {
        $bonos = $this->get_activos();
        $agrupados = array();

        foreach ($bonos as $bono) {
            $categoria = $bono->categoria ?: 'OTRO';
            if (!isset($agrupados[$categoria])) {
                $agrupados[$categoria] = array();
            }
            $agrupados[$categoria][] = $bono;
        }

        return $agrupados;
    }

    /**
     * Crear nuevo bono
     *
     * @param array $data Datos del bono
     * @return int|WP_Error ID del bono creado o error
     */
    public function crear($data) {
        global $wpdb;

        // Validar datos requeridos
        if (empty($data['nombre'])) {
            return new WP_Error('datos_incompletos', __('El nombre del bono es requerido.', 'gestionadmin-wolk'));
        }

        // Generar código si no viene
        if (empty($data['codigo'])) {
            $data['codigo'] = $this->generar_codigo($data['nombre']);
        }

        // Verificar código único
        $existe = $this->get_por_codigo($data['codigo']);
        if ($existe) {
            return new WP_Error('codigo_duplicado', __('Ya existe un bono con ese código.', 'gestionadmin-wolk'));
        }

        // Obtener siguiente orden
        $max_orden = $wpdb->get_var("SELECT MAX(orden) FROM {$this->table_name}");
        $nuevo_orden = ($max_orden ?: 0) + 1;

        // Preparar datos para insertar
        $insert_data = array(
            'codigo'               => sanitize_text_field($data['codigo']),
            'nombre'               => sanitize_text_field($data['nombre']),
            'descripcion'          => sanitize_textarea_field($data['descripcion'] ?? ''),
            'tipo_valor'           => in_array($data['tipo_valor'] ?? '', array('FIJO', 'PORCENTAJE')) ? $data['tipo_valor'] : 'FIJO',
            'valor_default'        => floatval($data['valor_default'] ?? 0),
            'frecuencia'           => in_array($data['frecuencia'] ?? '', array_keys(self::$frecuencias)) ? $data['frecuencia'] : 'MENSUAL',
            'condicion_descripcion' => sanitize_textarea_field($data['condicion_descripcion'] ?? ''),
            'categoria'            => in_array($data['categoria'] ?? '', array_keys(self::$categorias)) ? $data['categoria'] : 'OTRO',
            'icono'                => sanitize_text_field($data['icono'] ?? 'dashicons-awards'),
            'orden'                => isset($data['orden']) ? absint($data['orden']) : $nuevo_orden,
            'activo'               => 1,
        );

        $result = $wpdb->insert($this->table_name, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Error al crear el bono.', 'gestionadmin-wolk'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Actualizar bono existente
     *
     * @param int $id ID del bono
     * @param array $data Datos a actualizar
     * @return bool|WP_Error
     */
    public function actualizar($id, $data) {
        global $wpdb;

        $bono = $this->get($id);
        if (!$bono) {
            return new WP_Error('no_encontrado', __('Bono no encontrado.', 'gestionadmin-wolk'));
        }

        // Preparar datos para actualizar
        $update_data = array();
        $update_format = array();

        if (isset($data['nombre'])) {
            $update_data['nombre'] = sanitize_text_field($data['nombre']);
            $update_format[] = '%s';
        }

        if (isset($data['descripcion'])) {
            $update_data['descripcion'] = sanitize_textarea_field($data['descripcion']);
            $update_format[] = '%s';
        }

        if (isset($data['tipo_valor']) && in_array($data['tipo_valor'], array('FIJO', 'PORCENTAJE'))) {
            $update_data['tipo_valor'] = $data['tipo_valor'];
            $update_format[] = '%s';
        }

        if (isset($data['valor_default'])) {
            $update_data['valor_default'] = floatval($data['valor_default']);
            $update_format[] = '%f';
        }

        if (isset($data['frecuencia']) && in_array($data['frecuencia'], array_keys(self::$frecuencias))) {
            $update_data['frecuencia'] = $data['frecuencia'];
            $update_format[] = '%s';
        }

        if (isset($data['condicion_descripcion'])) {
            $update_data['condicion_descripcion'] = sanitize_textarea_field($data['condicion_descripcion']);
            $update_format[] = '%s';
        }

        if (isset($data['categoria']) && in_array($data['categoria'], array_keys(self::$categorias))) {
            $update_data['categoria'] = $data['categoria'];
            $update_format[] = '%s';
        }

        if (isset($data['icono'])) {
            $update_data['icono'] = sanitize_text_field($data['icono']);
            $update_format[] = '%s';
        }

        if (isset($data['orden'])) {
            $update_data['orden'] = absint($data['orden']);
            $update_format[] = '%d';
        }

        if (isset($data['activo'])) {
            $update_data['activo'] = absint($data['activo']);
            $update_format[] = '%d';
        }

        if (empty($update_data)) {
            return true;
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Eliminar bono
     *
     * @param int $id ID del bono
     * @return bool|WP_Error
     */
    public function eliminar($id) {
        global $wpdb;

        $bono = $this->get($id);
        if (!$bono) {
            return new WP_Error('no_encontrado', __('Bono no encontrado.', 'gestionadmin-wolk'));
        }

        // Verificar si está siendo usado en acuerdos
        $uso_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_ordenes_acuerdos WHERE bono_id = %d AND activo = 1",
            $id
        ));

        if ($uso_count > 0) {
            return new WP_Error('en_uso', sprintf(
                __('No se puede eliminar: el bono está siendo usado en %d acuerdo(s) activo(s).', 'gestionadmin-wolk'),
                $uso_count
            ));
        }

        // Soft delete (desactivar)
        $result = $wpdb->update(
            $this->table_name,
            array('activo' => 0),
            array('id' => $id),
            array('%d'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Eliminar bono permanentemente
     *
     * @param int $id ID del bono
     * @return bool|WP_Error
     */
    public function eliminar_permanente($id) {
        global $wpdb;

        // Verificar si está siendo usado
        $uso_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_ordenes_acuerdos WHERE bono_id = %d",
            $id
        ));

        if ($uso_count > 0) {
            return new WP_Error('en_uso', __('No se puede eliminar: el bono tiene acuerdos asociados.', 'gestionadmin-wolk'));
        }

        $result = $wpdb->delete($this->table_name, array('id' => $id), array('%d'));

        return $result !== false;
    }

    // =========================================================================
    // UTILIDADES
    // =========================================================================

    /**
     * Generar código único para bono
     *
     * Formato: BONO-XXX (basado en nombre)
     *
     * @param string $nombre Nombre del bono
     * @return string Código generado
     */
    private function generar_codigo($nombre) {
        global $wpdb;

        // Generar base del código a partir del nombre
        $palabras = preg_split('/\s+/', strtoupper($nombre));
        $base = '';
        foreach ($palabras as $palabra) {
            if (strlen($palabra) > 0) {
                $base .= substr($palabra, 0, 3);
            }
            if (strlen($base) >= 6) break;
        }
        $base = substr($base, 0, 6) ?: 'BONO';

        // Verificar unicidad
        $codigo = 'BONO-' . $base;
        $contador = 1;

        while ($this->get_por_codigo($codigo)) {
            $codigo = 'BONO-' . $base . '-' . $contador;
            $contador++;
        }

        return $codigo;
    }

    /**
     * Reordenar bonos
     *
     * @param array $orden Array de IDs en el nuevo orden
     * @return bool
     */
    public function reordenar($orden) {
        global $wpdb;

        foreach ($orden as $posicion => $id) {
            $wpdb->update(
                $this->table_name,
                array('orden' => $posicion + 1),
                array('id' => absint($id)),
                array('%d'),
                array('%d')
            );
        }

        return true;
    }

    /**
     * Obtener estadísticas de bonos
     *
     * @return array
     */
    public function get_estadisticas() {
        global $wpdb;

        return array(
            'total'   => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}"),
            'activos' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE activo = 1"),
            'por_categoria' => $wpdb->get_results(
                "SELECT categoria, COUNT(*) as total
                 FROM {$this->table_name}
                 WHERE activo = 1
                 GROUP BY categoria"
            ),
        );
    }

    /**
     * Guardar bono (crear o actualizar)
     *
     * @param array $data Datos del bono
     * @return int|WP_Error ID del bono o error
     */
    public static function save($data) {
        $instance = self::get_instance();

        $id = isset($data['id']) ? absint($data['id']) : 0;

        if ($id > 0) {
            $result = $instance->actualizar($id, $data);
            return is_wp_error($result) ? $result : $id;
        } else {
            return $instance->crear($data);
        }
    }

    /**
     * Obtener bonos para dropdown con formato específico
     *
     * @return array
     */
    public function get_para_dropdown() {
        $bonos = $this->get_activos();
        $opciones = array();

        foreach ($bonos as $bono) {
            $valor_texto = $bono->tipo_valor === 'PORCENTAJE'
                ? $bono->valor_default . '%'
                : '$' . number_format($bono->valor_default, 2);

            $opciones[] = array(
                'id'          => $bono->id,
                'codigo'      => $bono->codigo,
                'nombre'      => $bono->nombre,
                'valor'       => $bono->valor_default,
                'tipo_valor'  => $bono->tipo_valor,
                'valor_texto' => $valor_texto,
                'frecuencia'  => $bono->frecuencia,
                'categoria'   => $bono->categoria,
                'icono'       => $bono->icono,
            );
        }

        return $opciones;
    }
}
