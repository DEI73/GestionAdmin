<?php
/**
 * Módulo de Empresas Propias
 *
 * Gestiona el catálogo de empresas propias de la organización.
 * Cada empresa puede ser la entidad pagadora en órdenes de trabajo.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes/Modules
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Empresas {

    /**
     * Instancia única (Singleton)
     *
     * @var GA_Empresas
     */
    private static $instance = null;

    /**
     * Nombre de la tabla
     *
     * @var string
     */
    private $table_name;

    /**
     * Obtener instancia única
     *
     * @return GA_Empresas
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
        $this->table_name = $wpdb->prefix . 'ga_empresas';
    }

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    /**
     * Obtener empresa por ID
     *
     * @param int $id ID de la empresa
     * @return object|null Objeto empresa o null si no existe
     */
    public function get($id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT e.*, p.nombre as pais_nombre
             FROM {$this->table_name} e
             LEFT JOIN {$wpdb->prefix}ga_paises_config p ON e.pais_iso = p.codigo_iso
             WHERE e.id = %d",
            $id
        ));
    }

    /**
     * Obtener empresa por código
     *
     * @param string $codigo Código de la empresa
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
     * Listar empresas con filtros
     *
     * @param array $args Argumentos de filtrado
     * @return array Array con items, total y páginas
     */
    public function listar($args = array()) {
        global $wpdb;

        $defaults = array(
            'pais_iso'  => '',
            'activo'    => '',
            'busqueda'  => '',
            'orderby'   => 'nombre',
            'order'     => 'ASC',
            'page'      => 1,
            'per_page'  => 20,
        );
        $args = wp_parse_args($args, $defaults);

        // Construir WHERE
        $where = array('1=1');
        $params = array();

        if (!empty($args['pais_iso'])) {
            $where[] = 'e.pais_iso = %s';
            $params[] = $args['pais_iso'];
        }

        if ($args['activo'] !== '') {
            $where[] = 'e.activo = %d';
            $params[] = absint($args['activo']);
        }

        if (!empty($args['busqueda'])) {
            $where[] = '(e.nombre LIKE %s OR e.razon_social LIKE %s OR e.codigo LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['busqueda']) . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $where_sql = implode(' AND ', $where);

        // Ordenamiento seguro
        $allowed_orderby = array('id', 'codigo', 'nombre', 'pais_iso', 'created_at');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'nombre';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

        // Contar total
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} e WHERE {$where_sql}";
        if (!empty($params)) {
            $count_sql = $wpdb->prepare($count_sql, $params);
        }
        $total = $wpdb->get_var($count_sql);

        // Paginación
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Query principal
        $sql = "SELECT e.*, p.nombre as pais_nombre
                FROM {$this->table_name} e
                LEFT JOIN {$wpdb->prefix}ga_paises_config p ON e.pais_iso = p.codigo_iso
                WHERE {$where_sql}
                ORDER BY e.{$orderby} {$order}
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
     * Obtener empresas activas para dropdown
     *
     * @return array Lista de empresas activas
     */
    public function get_activas() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT id, codigo, nombre, pais_iso
             FROM {$this->table_name}
             WHERE activo = 1
             ORDER BY es_principal DESC, nombre ASC"
        );
    }

    /**
     * Obtener empresas por país
     *
     * @param string $pais_iso Código ISO del país
     * @return array
     */
    public function get_por_pais($pais_iso) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name}
             WHERE pais_iso = %s AND activo = 1
             ORDER BY nombre",
            $pais_iso
        ));
    }

    /**
     * Obtener empresa principal
     *
     * @return object|null
     */
    public function get_principal() {
        global $wpdb;

        return $wpdb->get_row(
            "SELECT * FROM {$this->table_name}
             WHERE es_principal = 1 AND activo = 1
             LIMIT 1"
        );
    }

    /**
     * Crear nueva empresa
     *
     * @param array $data Datos de la empresa
     * @return int|WP_Error ID de la empresa creada o error
     */
    public function crear($data) {
        global $wpdb;

        // Validar datos requeridos
        if (empty($data['nombre']) || empty($data['razon_social']) || empty($data['identificacion_fiscal'])) {
            return new WP_Error('datos_incompletos', __('Nombre, razón social e identificación fiscal son requeridos.', 'gestionadmin-wolk'));
        }

        // Generar código si no viene
        if (empty($data['codigo'])) {
            $data['codigo'] = $this->generar_codigo($data['pais_iso'] ?? 'XX');
        }

        // Verificar código único
        $existe = $this->get_por_codigo($data['codigo']);
        if ($existe) {
            return new WP_Error('codigo_duplicado', __('Ya existe una empresa con ese código.', 'gestionadmin-wolk'));
        }

        // Preparar datos para insertar
        $insert_data = array(
            'codigo'              => sanitize_text_field($data['codigo']),
            'nombre'              => sanitize_text_field($data['nombre']),
            'razon_social'        => sanitize_text_field($data['razon_social']),
            'identificacion_tipo' => sanitize_text_field($data['identificacion_tipo'] ?? ''),
            'identificacion_fiscal' => sanitize_text_field($data['identificacion_fiscal']),
            'pais_iso'            => sanitize_text_field($data['pais_iso'] ?? ''),
            'direccion'           => sanitize_textarea_field($data['direccion'] ?? ''),
            'ciudad'              => sanitize_text_field($data['ciudad'] ?? ''),
            'codigo_postal'       => sanitize_text_field($data['codigo_postal'] ?? ''),
            'telefono'            => sanitize_text_field($data['telefono'] ?? ''),
            'email'               => sanitize_email($data['email'] ?? ''),
            'sitio_web'           => esc_url_raw($data['sitio_web'] ?? ''),
            'logo_url'            => esc_url_raw($data['logo_url'] ?? ''),
            'color_primario'      => sanitize_hex_color($data['color_primario'] ?? '#0073aa'),
            'prefijo_factura'     => sanitize_text_field($data['prefijo_factura'] ?? 'FAC'),
            'pie_factura'         => sanitize_textarea_field($data['pie_factura'] ?? ''),
            'datos_bancarios'     => isset($data['datos_bancarios']) ? wp_json_encode($data['datos_bancarios']) : null,
            'es_principal'        => absint($data['es_principal'] ?? 0),
            'activo'              => 1,
        );

        // Si es principal, quitar flag a las demás
        if ($insert_data['es_principal']) {
            $wpdb->update($this->table_name, array('es_principal' => 0), array('es_principal' => 1));
        }

        $result = $wpdb->insert($this->table_name, $insert_data);

        if ($result === false) {
            return new WP_Error('db_error', __('Error al crear la empresa.', 'gestionadmin-wolk'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Actualizar empresa existente
     *
     * @param int $id ID de la empresa
     * @param array $data Datos a actualizar
     * @return bool|WP_Error True si éxito o error
     */
    public function actualizar($id, $data) {
        global $wpdb;

        $empresa = $this->get($id);
        if (!$empresa) {
            return new WP_Error('no_encontrada', __('Empresa no encontrada.', 'gestionadmin-wolk'));
        }

        // Preparar datos para actualizar
        $update_data = array();
        $update_format = array();

        $campos_texto = array('nombre', 'razon_social', 'identificacion_tipo', 'identificacion_fiscal',
                              'pais_iso', 'ciudad', 'codigo_postal', 'telefono', 'prefijo_factura');
        foreach ($campos_texto as $campo) {
            if (isset($data[$campo])) {
                $update_data[$campo] = sanitize_text_field($data[$campo]);
                $update_format[] = '%s';
            }
        }

        if (isset($data['direccion'])) {
            $update_data['direccion'] = sanitize_textarea_field($data['direccion']);
            $update_format[] = '%s';
        }

        if (isset($data['pie_factura'])) {
            $update_data['pie_factura'] = sanitize_textarea_field($data['pie_factura']);
            $update_format[] = '%s';
        }

        if (isset($data['email'])) {
            $update_data['email'] = sanitize_email($data['email']);
            $update_format[] = '%s';
        }

        if (isset($data['sitio_web'])) {
            $update_data['sitio_web'] = esc_url_raw($data['sitio_web']);
            $update_format[] = '%s';
        }

        if (isset($data['logo_url'])) {
            $update_data['logo_url'] = esc_url_raw($data['logo_url']);
            $update_format[] = '%s';
        }

        if (isset($data['color_primario'])) {
            $update_data['color_primario'] = sanitize_hex_color($data['color_primario']);
            $update_format[] = '%s';
        }

        if (isset($data['datos_bancarios'])) {
            $update_data['datos_bancarios'] = wp_json_encode($data['datos_bancarios']);
            $update_format[] = '%s';
        }

        if (isset($data['es_principal'])) {
            $es_principal = absint($data['es_principal']);
            if ($es_principal) {
                // Quitar flag a las demás
                $wpdb->update($this->table_name, array('es_principal' => 0), array('es_principal' => 1));
            }
            $update_data['es_principal'] = $es_principal;
            $update_format[] = '%d';
        }

        if (isset($data['activo'])) {
            $update_data['activo'] = absint($data['activo']);
            $update_format[] = '%d';
        }

        if (empty($update_data)) {
            return true; // Nada que actualizar
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
     * Eliminar empresa (soft delete)
     *
     * @param int $id ID de la empresa
     * @return bool|WP_Error
     */
    public function eliminar($id) {
        global $wpdb;

        $empresa = $this->get($id);
        if (!$empresa) {
            return new WP_Error('no_encontrada', __('Empresa no encontrada.', 'gestionadmin-wolk'));
        }

        // Verificar que no tenga órdenes asociadas
        $ordenes_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ga_ordenes_trabajo WHERE empresa_id = %d",
            $id
        ));

        if ($ordenes_count > 0) {
            return new WP_Error('tiene_ordenes', sprintf(
                __('No se puede eliminar: la empresa tiene %d orden(es) de trabajo asociada(s).', 'gestionadmin-wolk'),
                $ordenes_count
            ));
        }

        // Soft delete
        $result = $wpdb->update(
            $this->table_name,
            array('activo' => 0),
            array('id' => $id),
            array('%d'),
            array('%d')
        );

        return $result !== false;
    }

    // =========================================================================
    // UTILIDADES
    // =========================================================================

    /**
     * Generar código único para empresa
     *
     * Formato: EMP-XX-NNN (donde XX es código país)
     *
     * @param string $pais_iso Código ISO del país
     * @return string Código generado
     */
    private function generar_codigo($pais_iso) {
        global $wpdb;

        $pais_iso = strtoupper(substr($pais_iso, 0, 2)) ?: 'XX';

        // Obtener último consecutivo para este país
        $ultimo = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING(codigo, -3) AS UNSIGNED))
             FROM {$this->table_name}
             WHERE codigo LIKE %s",
            'EMP-' . $pais_iso . '-%'
        ));

        $consecutivo = ($ultimo ?: 0) + 1;

        return sprintf('EMP-%s-%03d', $pais_iso, $consecutivo);
    }

    /**
     * Obtener estadísticas de empresas
     *
     * @return array
     */
    public function get_estadisticas() {
        global $wpdb;

        return array(
            'total'   => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}"),
            'activas' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE activo = 1"),
            'por_pais' => $wpdb->get_results(
                "SELECT pais_iso, COUNT(*) as total
                 FROM {$this->table_name}
                 WHERE activo = 1
                 GROUP BY pais_iso"
            ),
        );
    }

    /**
     * Guardar empresa (crear o actualizar)
     *
     * Método de conveniencia que determina si crear o actualizar.
     *
     * @param array $data Datos de la empresa (incluye 'id' si es actualización)
     * @return int|WP_Error ID de la empresa o error
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
}
