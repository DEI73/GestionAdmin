<?php
/**
 * Clase que maneja la activación del plugin
 *
 * Esta clase define todo el código necesario que se ejecuta durante la activación del plugin.
 * Crea las tablas de base de datos e inserta datos iniciales.
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Activator {

    /**
     * Código que se ejecuta durante la activación del plugin
     *
     * Crea las tablas iniciales del sistema:
     * - wp_ga_departamentos
     * - wp_ga_puestos
     * - wp_ga_puestos_escalas
     * - wp_ga_usuarios
     * - wp_ga_supervisiones
     * - wp_ga_paises_config
     *
     * @since 1.0.0
     */
    public static function activate() {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        // Crear tablas
        self::create_departamentos_table($wpdb, $charset_collate);
        self::create_puestos_table($wpdb, $charset_collate);
        self::create_puestos_escalas_table($wpdb, $charset_collate);
        self::create_usuarios_table($wpdb, $charset_collate);
        self::create_supervisiones_table($wpdb, $charset_collate);
        self::create_paises_config_table($wpdb, $charset_collate);

        // Insertar datos iniciales
        self::insert_initial_data($wpdb);

        // Guardar versión del plugin
        add_option('ga_version', GA_VERSION);
        add_option('ga_db_version', '1.0.0');

        // Crear roles personalizados
        self::create_custom_roles();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Crear tabla wp_ga_departamentos
     */
    private static function create_departamentos_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_departamentos';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(20) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            tipo ENUM('OPERACION_FIJA', 'PROYECTOS', 'SOPORTE', 'COMERCIAL') DEFAULT 'PROYECTOS',
            jefe_id BIGINT UNSIGNED,
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_codigo (codigo),
            INDEX idx_activo (activo),
            INDEX idx_jefe (jefe_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_puestos
     */
    private static function create_puestos_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_puestos';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            departamento_id INT NOT NULL,
            codigo VARCHAR(20) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            nivel_jerarquico INT DEFAULT 4 COMMENT '1=Socio, 2=Director, 3=Jefe, 4=Empleado',
            reporta_a_puesto_id INT,
            capacidad_horas_semana INT DEFAULT 40,
            requiere_qa TINYINT(1) DEFAULT 0,
            flujo_revision_default ENUM('SOLO_JEFE', 'QA_JEFE', 'QA_JEFE_DIRECTOR') DEFAULT 'SOLO_JEFE',
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_departamento (departamento_id),
            INDEX idx_nivel (nivel_jerarquico),
            INDEX idx_codigo (codigo),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_puestos_escalas
     */
    private static function create_puestos_escalas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_puestos_escalas';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            puesto_id INT NOT NULL,
            anio_antiguedad INT NOT NULL COMMENT '1, 2, 3, 4, 5+',
            tarifa_hora DECIMAL(10,2) NOT NULL,
            incremento_porcentaje DECIMAL(5,2) DEFAULT 0,
            requiere_aprobacion_jefe TINYINT(1) DEFAULT 1,
            requiere_aprobacion_director TINYINT(1) DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_puesto_anio (puesto_id, anio_antiguedad),
            INDEX idx_puesto (puesto_id),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_usuarios
     */
    private static function create_usuarios_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_usuarios';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_wp_id BIGINT UNSIGNED NOT NULL UNIQUE,
            puesto_id INT,
            departamento_id INT,
            codigo_empleado VARCHAR(20) UNIQUE,
            fecha_ingreso DATE,
            nivel_jerarquico INT DEFAULT 4,
            es_jefe_de_jefes TINYINT(1) DEFAULT 0,
            puede_ver_departamentos JSON,
            metodo_pago_preferido ENUM('BINANCE', 'WISE', 'PAYPAL', 'PAYONEER', 'TRANSFERENCIA', 'EFECTIVO') DEFAULT 'TRANSFERENCIA',
            datos_pago_binance JSON,
            datos_pago_wise JSON,
            datos_pago_paypal JSON,
            datos_pago_banco JSON,
            pais_residencia VARCHAR(2),
            identificacion_fiscal VARCHAR(50),
            activo TINYINT(1) DEFAULT 1,
            fecha_baja DATE,
            motivo_baja TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_usuario_wp (usuario_wp_id),
            INDEX idx_puesto (puesto_id),
            INDEX idx_departamento (departamento_id),
            INDEX idx_codigo (codigo_empleado),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_supervisiones
     */
    private static function create_supervisiones_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_supervisiones';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supervisor_id BIGINT UNSIGNED NOT NULL,
            supervisado_id BIGINT UNSIGNED NOT NULL,
            tipo_supervision ENUM('DIRECTA', 'PROYECTO', 'DEPARTAMENTO') DEFAULT 'DIRECTA',
            proyecto_id INT,
            departamento_id INT,
            fecha_inicio DATE NOT NULL,
            fecha_fin DATE,
            activo TINYINT(1) DEFAULT 1,
            created_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_supervisor (supervisor_id),
            INDEX idx_supervisado (supervisado_id),
            INDEX idx_tipo (tipo_supervision),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_paises_config
     */
    private static function create_paises_config_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_paises_config';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_iso VARCHAR(2) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            moneda_codigo VARCHAR(3) NOT NULL,
            moneda_simbolo VARCHAR(5),
            impuesto_nombre VARCHAR(50),
            impuesto_porcentaje DECIMAL(5,2) DEFAULT 0,
            retencion_default DECIMAL(5,2) DEFAULT 0,
            formato_factura VARCHAR(20),
            requiere_electronica TINYINT(1) DEFAULT 0,
            proveedor_electronica VARCHAR(50),
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_codigo (codigo_iso),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Insertar datos iniciales
     */
    private static function insert_initial_data($wpdb) {
        // Insertar países iniciales (CO, US, MX)
        $table_paises = $wpdb->prefix . 'ga_paises_config';

        $paises_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_paises}");

        if ($paises_count == 0) {
            $paises = array(
                array(
                    'codigo_iso' => 'US',
                    'nombre' => 'Estados Unidos',
                    'moneda_codigo' => 'USD',
                    'moneda_simbolo' => '$',
                    'impuesto_nombre' => 'Sales Tax',
                    'impuesto_porcentaje' => 0.00,
                    'retencion_default' => 0.00,
                    'formato_factura' => 'INV-US-{YYYY}-{NNNN}',
                    'requiere_electronica' => 0,
                    'proveedor_electronica' => '',
                    'activo' => 1
                ),
                array(
                    'codigo_iso' => 'CO',
                    'nombre' => 'Colombia',
                    'moneda_codigo' => 'COP',
                    'moneda_simbolo' => '$',
                    'impuesto_nombre' => 'IVA',
                    'impuesto_porcentaje' => 19.00,
                    'retencion_default' => 11.00,
                    'formato_factura' => 'FV-CO-{YYYY}-{NNNN}',
                    'requiere_electronica' => 1,
                    'proveedor_electronica' => 'DIAN',
                    'activo' => 1
                ),
                array(
                    'codigo_iso' => 'MX',
                    'nombre' => 'México',
                    'moneda_codigo' => 'MXN',
                    'moneda_simbolo' => '$',
                    'impuesto_nombre' => 'IVA',
                    'impuesto_porcentaje' => 16.00,
                    'retencion_default' => 10.00,
                    'formato_factura' => 'FAC-MX-{YYYY}-{NNNN}',
                    'requiere_electronica' => 1,
                    'proveedor_electronica' => 'SAT',
                    'activo' => 1
                )
            );

            foreach ($paises as $pais) {
                $wpdb->insert($table_paises, $pais);
            }
        }
    }

    /**
     * Crear roles personalizados de WordPress
     */
    private static function create_custom_roles() {
        // Capacidades base para cada rol
        $capabilities_socio = array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'ga_manage_all' => true,
            'ga_view_all' => true,
            'ga_approve_payments' => true,
            'ga_manage_users' => true,
        );

        $capabilities_director = array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'ga_manage_department' => true,
            'ga_view_department' => true,
            'ga_approve_tasks' => true,
            'ga_manage_projects' => true,
        );

        $capabilities_jefe = array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'ga_manage_team' => true,
            'ga_view_team' => true,
            'ga_approve_tasks' => true,
            'ga_assign_tasks' => true,
        );

        $capabilities_empleado = array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'ga_view_own' => true,
            'ga_submit_tasks' => true,
            'ga_track_time' => true,
        );

        $capabilities_cliente = array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'ga_view_own_projects' => true,
            'ga_submit_tickets' => true,
        );

        $capabilities_aplicante = array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'ga_apply_jobs' => true,
            'ga_view_marketplace' => true,
        );

        // Crear roles si no existen
        if (!get_role('ga_socio')) {
            add_role('ga_socio', __('Socio', 'gestionadmin-wolk'), $capabilities_socio);
        }

        if (!get_role('ga_director')) {
            add_role('ga_director', __('Director', 'gestionadmin-wolk'), $capabilities_director);
        }

        if (!get_role('ga_jefe')) {
            add_role('ga_jefe', __('Jefe de Equipo', 'gestionadmin-wolk'), $capabilities_jefe);
        }

        if (!get_role('ga_empleado')) {
            add_role('ga_empleado', __('Empleado', 'gestionadmin-wolk'), $capabilities_empleado);
        }

        if (!get_role('ga_cliente')) {
            add_role('ga_cliente', __('Cliente', 'gestionadmin-wolk'), $capabilities_cliente);
        }

        if (!get_role('ga_aplicante')) {
            add_role('ga_aplicante', __('Aplicante', 'gestionadmin-wolk'), $capabilities_aplicante);
        }
    }
}
