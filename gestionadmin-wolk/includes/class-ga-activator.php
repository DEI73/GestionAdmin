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

        // Iniciar output buffering para capturar cualquier output inesperado
        // (dbDelta puede generar output en algunos casos)
        ob_start();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        // Crear tablas Sprint 1-2: Fundamentos
        self::create_departamentos_table($wpdb, $charset_collate);
        self::create_puestos_table($wpdb, $charset_collate);
        self::create_puestos_escalas_table($wpdb, $charset_collate);
        self::create_usuarios_table($wpdb, $charset_collate);
        self::create_supervisiones_table($wpdb, $charset_collate);
        self::create_paises_config_table($wpdb, $charset_collate);

        // Crear tablas Sprint 3-4: Core Operativo
        self::create_catalogo_tareas_table($wpdb, $charset_collate);
        self::create_tareas_table($wpdb, $charset_collate);
        self::create_subtareas_table($wpdb, $charset_collate);
        self::create_registro_horas_table($wpdb, $charset_collate);
        self::create_pausas_timer_table($wpdb, $charset_collate);

        // Crear tablas Sprint 5-6: Clientes y Proyectos
        self::create_clientes_table($wpdb, $charset_collate);
        self::create_casos_table($wpdb, $charset_collate);
        self::create_proyectos_table($wpdb, $charset_collate);

        // Crear tablas Sprint 7-8: Marketplace y Órdenes de Trabajo
        self::create_aplicantes_table($wpdb, $charset_collate);
        self::create_ordenes_trabajo_table($wpdb, $charset_collate);
        self::create_aplicaciones_orden_table($wpdb, $charset_collate);

        // Crear tablas Sprint 9-10: Facturación
        self::create_facturas_table($wpdb, $charset_collate);
        self::create_facturas_detalle_table($wpdb, $charset_collate);
        self::create_cotizaciones_table($wpdb, $charset_collate);
        self::create_cotizaciones_detalle_table($wpdb, $charset_collate);

        // Crear tablas Sprint 11-12: Acuerdos Económicos
        self::create_empresas_table($wpdb, $charset_collate);
        self::create_catalogo_bonos_table($wpdb, $charset_collate);
        self::create_ordenes_acuerdos_table($wpdb, $charset_collate);

        // Crear tablas Sprint 11-12 Parte B: Ejecución de Comisiones
        self::create_comisiones_generadas_table($wpdb, $charset_collate);
        self::create_solicitudes_cobro_table($wpdb, $charset_collate);
        self::create_solicitudes_cobro_detalle_table($wpdb, $charset_collate);

        // Insertar datos iniciales
        self::insert_initial_data($wpdb);

        // Ejecutar migraciones para instalaciones existentes
        self::run_migrations($wpdb);

        // Guardar versión del plugin
        add_option('ga_version', GA_VERSION);
        update_option('ga_db_version', '1.5.0'); // Sprint 11-12: Acuerdos Económicos

        // Crear roles personalizados
        self::create_custom_roles();

        // Crear páginas del plugin
        self::create_plugin_pages();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Finalizar output buffering y descartar cualquier output capturado
        ob_end_clean();
    }

    /**
     * Crear páginas del plugin
     *
     * Utiliza GA_Pages_Manager para crear todas las páginas
     * necesarias para los portales públicos.
     *
     * @since 1.3.0
     */
    private static function create_plugin_pages() {
        require_once GA_PLUGIN_DIR . 'includes/class-ga-pages-manager.php';
        $pages_manager = GA_Pages_Manager::get_instance();
        $pages_manager->create_all_pages();
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
            metodo_pago_preferido ENUM('BINANCE', 'WISE', 'PAYPAL', 'PAYONEER', 'STRIPE', 'TRANSFERENCIA', 'EFECTIVO') DEFAULT 'TRANSFERENCIA',
            datos_pago_binance JSON,
            datos_pago_wise JSON,
            datos_pago_paypal JSON,
            datos_pago_stripe JSON COMMENT 'CORRECCIÓN 2: Stripe agregado como método de pago',
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
                ),
                // Costa Rica - CORRECCIÓN 1: País agregado con facturación electrónica
                array(
                    'codigo_iso' => 'CR',
                    'nombre' => 'Costa Rica',
                    'moneda_codigo' => 'CRC',
                    'moneda_simbolo' => '₡',
                    'impuesto_nombre' => 'IVA',
                    'impuesto_porcentaje' => 13.00,
                    'retencion_default' => 0.00,
                    'formato_factura' => 'FE-CR-{YYYY}-{NNNN}',
                    'requiere_electronica' => 1,
                    'proveedor_electronica' => 'Ministerio de Hacienda Costa Rica',
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

    // =========================================================================
    // TABLAS SPRINT 3-4: CORE OPERATIVO
    // =========================================================================

    /**
     * Crear tabla wp_ga_catalogo_tareas
     */
    private static function create_catalogo_tareas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_catalogo_tareas';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(20) NOT NULL UNIQUE,
            nombre VARCHAR(200) NOT NULL,
            descripcion TEXT,
            departamento_id INT,
            puesto_id INT,
            horas_estimadas DECIMAL(10,2),
            frecuencia ENUM('POR_SOLICITUD', 'DIARIA', 'SEMANAL', 'QUINCENAL', 'MENSUAL', 'TRIMESTRAL', 'SEMESTRAL') DEFAULT 'POR_SOLICITUD',
            frecuencia_dias INT,
            url_instrucciones VARCHAR(500),
            instrucciones_texto TEXT,
            flujo_revision ENUM('DEFAULT_PUESTO', 'PERSONALIZADO') DEFAULT 'DEFAULT_PUESTO',
            revisor_tipo ENUM('NINGUNO', 'QA_DEPARTAMENTO', 'USUARIO_ESPECIFICO', 'PAR'),
            revisor_usuario_id BIGINT UNSIGNED,
            aprobador_tipo ENUM('JEFE_DIRECTO', 'JEFE_DEPARTAMENTO', 'USUARIO_ESPECIFICO', 'AUTO'),
            aprobador_usuario_id BIGINT UNSIGNED,
            requiere_segundo_aprobador TINYINT(1) DEFAULT 0,
            segundo_aprobador_nivel INT,
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_departamento (departamento_id),
            INDEX idx_puesto (puesto_id),
            INDEX idx_codigo (codigo),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_tareas
     */
    private static function create_tareas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_tareas';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero VARCHAR(20) NOT NULL UNIQUE,
            catalogo_tarea_id INT,
            nombre VARCHAR(200) NOT NULL,
            descripcion TEXT,
            proyecto_id INT,
            caso_id INT,
            asignado_a BIGINT UNSIGNED NOT NULL,
            contrato_trabajo_id INT,
            supervisor_id BIGINT UNSIGNED,
            aprobador_id BIGINT UNSIGNED,
            horas_estimadas DECIMAL(10,2),
            horas_reales DECIMAL(10,2) DEFAULT 0,
            fecha_inicio DATE,
            fecha_limite DATE,
            fecha_completada DATETIME,
            estado ENUM('PENDIENTE', 'EN_PROGRESO', 'PAUSADA', 'COMPLETADA', 'EN_QA', 'APROBADA_QA', 'EN_REVISION', 'APROBADA', 'RECHAZADA', 'PAGADA', 'CANCELADA') DEFAULT 'PENDIENTE',
            prioridad ENUM('BAJA', 'MEDIA', 'ALTA', 'URGENTE') DEFAULT 'MEDIA',
            url_instrucciones VARCHAR(500),
            instrucciones_texto TEXT,
            porcentaje_avance INT DEFAULT 0,
            timedoctor_task_id VARCHAR(50),
            created_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_numero (numero),
            INDEX idx_catalogo (catalogo_tarea_id),
            INDEX idx_asignado (asignado_a),
            INDEX idx_supervisor (supervisor_id),
            INDEX idx_estado (estado),
            INDEX idx_prioridad (prioridad),
            INDEX idx_fecha_limite (fecha_limite)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_subtareas
     */
    private static function create_subtareas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_subtareas';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tarea_id INT NOT NULL,
            codigo VARCHAR(20),
            nombre VARCHAR(200) NOT NULL,
            descripcion TEXT,
            orden INT DEFAULT 0,
            horas_estimadas DECIMAL(10,2),
            horas_reales DECIMAL(10,2) DEFAULT 0,
            estado ENUM('PENDIENTE', 'EN_PROGRESO', 'COMPLETADA') DEFAULT 'PENDIENTE',
            fecha_inicio DATETIME,
            fecha_fin DATETIME,
            notas TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tarea (tarea_id),
            INDEX idx_orden (orden),
            INDEX idx_estado (estado)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_registro_horas
     */
    private static function create_registro_horas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_registro_horas';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id BIGINT UNSIGNED NOT NULL,
            tarea_id INT NOT NULL,
            subtarea_id INT,
            proyecto_id INT,
            contrato_trabajo_id INT,
            fecha DATE NOT NULL,
            hora_inicio DATETIME NOT NULL,
            hora_fin DATETIME,
            minutos_totales INT DEFAULT 0,
            minutos_pausas INT DEFAULT 0,
            minutos_efectivos INT DEFAULT 0,
            descripcion TEXT,
            estado ENUM('ACTIVO', 'BORRADOR', 'ENVIADO', 'EN_QA', 'APROBADO_QA', 'APROBADO', 'RECHAZADO', 'PAGADO') DEFAULT 'ACTIVO',
            aprobado_qa_por BIGINT UNSIGNED,
            fecha_aprobacion_qa DATETIME,
            aprobado_por BIGINT UNSIGNED,
            fecha_aprobacion DATETIME,
            motivo_rechazo TEXT,
            tarifa_hora DECIMAL(10,2),
            monto_calculado DECIMAL(12,2),
            incluido_en_cobro_id INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_usuario (usuario_id),
            INDEX idx_tarea (tarea_id),
            INDEX idx_subtarea (subtarea_id),
            INDEX idx_fecha (fecha),
            INDEX idx_estado (estado)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_pausas_timer
     */
    private static function create_pausas_timer_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_pausas_timer';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            registro_hora_id INT NOT NULL,
            hora_pausa DATETIME NOT NULL,
            hora_reanudacion DATETIME,
            minutos INT DEFAULT 0,
            motivo ENUM('ALMUERZO', 'REUNION', 'EMERGENCIA', 'DESCANSO', 'OTRO') DEFAULT 'OTRO',
            nota VARCHAR(200),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_registro (registro_hora_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    // =========================================================================
    // TABLAS SPRINT 5-6: CLIENTES Y PROYECTOS
    // =========================================================================

    /**
     * Crear tabla wp_ga_clientes
     *
     * Almacena la información de clientes de la empresa.
     * Los clientes pueden tener usuario WP asociado para acceder al portal.
     * Soporta personas naturales y empresas con datos fiscales.
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_clientes_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_clientes';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_wp_id BIGINT UNSIGNED UNIQUE COMMENT 'FK wp_users - Para login portal cliente',
            codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Formato: CLI-001',
            tipo ENUM('PERSONA_NATURAL', 'EMPRESA') DEFAULT 'EMPRESA',
            nombre_comercial VARCHAR(200) NOT NULL COMMENT 'Nombre comercial o nombre completo',
            razon_social VARCHAR(200) COMMENT 'Razón social legal (si es empresa)',
            documento_tipo VARCHAR(20) COMMENT 'Tipo: NIT, CC, RFC, EIN, etc.',
            documento_numero VARCHAR(50) COMMENT 'Número de identificación fiscal',
            email VARCHAR(200) COMMENT 'Email principal de facturación',
            telefono VARCHAR(50) COMMENT 'Teléfono principal',
            pais VARCHAR(2) COMMENT 'Código ISO del país',
            ciudad VARCHAR(100),
            direccion TEXT COMMENT 'Dirección fiscal completa',
            regimen_fiscal VARCHAR(50) COMMENT 'Régimen tributario aplicable',
            retencion_default DECIMAL(5,2) DEFAULT 0 COMMENT 'Porcentaje de retención por defecto',
            contacto_nombre VARCHAR(200) COMMENT 'Nombre del contacto principal',
            contacto_cargo VARCHAR(100) COMMENT 'Cargo del contacto',
            contacto_email VARCHAR(200) COMMENT 'Email del contacto',
            contacto_telefono VARCHAR(50) COMMENT 'Teléfono del contacto',
            stripe_customer_id VARCHAR(50) COMMENT 'ID de cliente en Stripe',
            paypal_email VARCHAR(200) COMMENT 'Email de PayPal del cliente',
            metodo_pago_preferido ENUM('TRANSFERENCIA', 'STRIPE', 'PAYPAL', 'EFECTIVO') DEFAULT 'TRANSFERENCIA',
            notas TEXT COMMENT 'Notas internas sobre el cliente',
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_codigo (codigo),
            INDEX idx_pais (pais),
            INDEX idx_activo (activo),
            INDEX idx_tipo (tipo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_casos
     *
     * Representa expedientes o casos de clientes.
     * Un caso agrupa proyectos relacionados para un mismo cliente.
     * Numeración automática: CASO-[CODIGO_CLIENTE]-[AÑO]-[CONSECUTIVO]
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_casos_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_casos';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero VARCHAR(30) NOT NULL UNIQUE COMMENT 'Formato: CASO-CLI001-2024-0001',
            cliente_id INT NOT NULL COMMENT 'FK a wp_ga_clientes',
            titulo VARCHAR(200) NOT NULL COMMENT 'Título descriptivo del caso',
            descripcion TEXT COMMENT 'Descripción detallada del caso',
            tipo ENUM('PROYECTO', 'LEGAL', 'SOPORTE', 'CONSULTORIA', 'OTRO') DEFAULT 'PROYECTO',
            estado ENUM('ABIERTO', 'EN_PROGRESO', 'EN_ESPERA', 'CERRADO', 'CANCELADO') DEFAULT 'ABIERTO',
            prioridad ENUM('BAJA', 'MEDIA', 'ALTA', 'URGENTE') DEFAULT 'MEDIA',
            fecha_apertura DATE NOT NULL COMMENT 'Fecha de apertura del caso',
            fecha_cierre_estimada DATE COMMENT 'Fecha estimada de cierre',
            fecha_cierre_real DATETIME COMMENT 'Fecha real de cierre',
            responsable_id BIGINT UNSIGNED COMMENT 'Usuario WP responsable del caso',
            presupuesto_horas INT COMMENT 'Horas presupuestadas para el caso',
            presupuesto_dinero DECIMAL(12,2) COMMENT 'Monto presupuestado en USD',
            notas TEXT COMMENT 'Notas internas del caso',
            created_by BIGINT UNSIGNED COMMENT 'Usuario que creó el caso',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_numero (numero),
            INDEX idx_cliente (cliente_id),
            INDEX idx_estado (estado),
            INDEX idx_prioridad (prioridad),
            INDEX idx_responsable (responsable_id),
            INDEX idx_fecha_apertura (fecha_apertura)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_proyectos
     *
     * Proyectos específicos dentro de un caso.
     * Cada proyecto tiene su propio presupuesto y cronograma.
     * Las tareas se asignan a proyectos para tracking.
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_proyectos_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_proyectos';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            caso_id INT NOT NULL COMMENT 'FK a wp_ga_casos',
            codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Formato: PRY-001',
            nombre VARCHAR(200) NOT NULL COMMENT 'Nombre del proyecto',
            descripcion TEXT COMMENT 'Descripción detallada del proyecto',
            fecha_inicio DATE COMMENT 'Fecha de inicio planificada',
            fecha_fin_estimada DATE COMMENT 'Fecha de fin estimada',
            fecha_fin_real DATE COMMENT 'Fecha de fin real',
            estado ENUM('PLANIFICACION', 'EN_PROGRESO', 'PAUSADO', 'COMPLETADO', 'CANCELADO') DEFAULT 'PLANIFICACION',
            responsable_id BIGINT UNSIGNED COMMENT 'Usuario WP responsable del proyecto',
            presupuesto_horas INT COMMENT 'Horas presupuestadas',
            presupuesto_dinero DECIMAL(12,2) COMMENT 'Monto presupuestado en USD',
            horas_consumidas DECIMAL(10,2) DEFAULT 0 COMMENT 'Horas reales consumidas (calculado)',
            porcentaje_avance INT DEFAULT 0 COMMENT 'Porcentaje de avance 0-100',
            timedoctor_project_id VARCHAR(50) COMMENT 'ID de proyecto en TimeDoctor',
            mostrar_ranking TINYINT(1) DEFAULT 0 COMMENT 'Mostrar ranking en portal cliente',
            mostrar_tareas_equipo TINYINT(1) DEFAULT 1 COMMENT 'Mostrar tareas del equipo',
            mostrar_horas_equipo TINYINT(1) DEFAULT 0 COMMENT 'Mostrar horas del equipo',
            notas TEXT COMMENT 'Notas internas del proyecto',
            created_by BIGINT UNSIGNED COMMENT 'Usuario que creó el proyecto',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_codigo (codigo),
            INDEX idx_caso (caso_id),
            INDEX idx_estado (estado),
            INDEX idx_responsable (responsable_id),
            INDEX idx_fecha_inicio (fecha_inicio)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    // =========================================================================
    // TABLAS SPRINT 7-8: MARKETPLACE Y ÓRDENES DE TRABAJO
    // =========================================================================

    /**
     * Crear tabla wp_ga_aplicantes
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Almacena información de personas/empresas que aplican a órdenes de trabajo.
     * Pueden ser freelancers (persona natural) o empresas externas.
     *
     * Esta tabla es el CORAZÓN del marketplace - cada aplicante tiene su perfil
     * con documentos, habilidades y datos de pago para cuando se le contrate.
     *
     * =========================================================================
     * RELACIONES:
     * =========================================================================
     * - usuario_wp_id → wp_users (login al portal)
     * - pais → wp_ga_paises_config (para datos fiscales)
     * - Muchos aplicantes pueden aplicar a muchas órdenes (M:N via aplicaciones)
     *
     * =========================================================================
     * FLUJO DE USO:
     * =========================================================================
     * 1. Persona ve orden de trabajo en portal público
     * 2. Se registra como aplicante (crea cuenta WP + registro aquí)
     * 3. Completa su perfil (habilidades, documentos, datos pago)
     * 4. Ya puede aplicar a órdenes de trabajo
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_aplicantes_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_aplicantes';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * IDENTIFICACIÓN Y ACCESO
             * ───────────────────────────────────────────────────────────────── */
            usuario_wp_id BIGINT UNSIGNED UNIQUE COMMENT 'FK wp_users - Login portal aplicantes',

            /* ─────────────────────────────────────────────────────────────────
             * TIPO DE APLICANTE
             * - PERSONA_NATURAL: Freelancer individual
             * - EMPRESA: Consultora, agencia, empresa de servicios
             * ───────────────────────────────────────────────────────────────── */
            tipo ENUM('PERSONA_NATURAL', 'EMPRESA') DEFAULT 'PERSONA_NATURAL',

            /* ─────────────────────────────────────────────────────────────────
             * DATOS PERSONALES / EMPRESARIALES
             * ───────────────────────────────────────────────────────────────── */
            nombre_completo VARCHAR(200) NOT NULL COMMENT 'Nombre persona o razón social empresa',
            nombre_comercial VARCHAR(200) COMMENT 'Nombre comercial (solo empresas)',
            documento_tipo VARCHAR(20) COMMENT 'CC, NIT, RFC, EIN, PASAPORTE',
            documento_numero VARCHAR(50) COMMENT 'Número de identificación',

            /* ─────────────────────────────────────────────────────────────────
             * CONTACTO
             * ───────────────────────────────────────────────────────────────── */
            email VARCHAR(200) NOT NULL COMMENT 'Email principal de contacto',
            telefono VARCHAR(50) COMMENT 'Teléfono con código país',
            pais VARCHAR(2) COMMENT 'Código ISO del país de residencia',
            ciudad VARCHAR(100) COMMENT 'Ciudad de residencia',
            direccion TEXT COMMENT 'Dirección completa',

            /* ─────────────────────────────────────────────────────────────────
             * PERFIL PROFESIONAL
             * - habilidades: JSON con array de skills (ej: PHP, WordPress, React)
             * - experiencia_anios: Años de experiencia profesional
             * - portafolio_url: Link a portafolio o LinkedIn
             * - cv_url: Link al CV/Hoja de vida subido
             * ───────────────────────────────────────────────────────────────── */
            habilidades JSON COMMENT 'Array de habilidades/skills',
            experiencia_anios INT DEFAULT 0 COMMENT 'Años de experiencia',
            portafolio_url VARCHAR(500) COMMENT 'URL portafolio o LinkedIn',
            cv_url VARCHAR(500) COMMENT 'URL del CV subido',
            descripcion_perfil TEXT COMMENT 'Descripción/bio del aplicante',

            /* ─────────────────────────────────────────────────────────────────
             * DATOS DE PAGO
             * Cuando el aplicante es contratado, necesitamos saber cómo pagarle
             * ───────────────────────────────────────────────────────────────── */
            metodo_pago_preferido ENUM('BINANCE', 'WISE', 'PAYPAL', 'PAYONEER', 'STRIPE', 'TRANSFERENCIA') DEFAULT 'TRANSFERENCIA',
            datos_pago_binance JSON COMMENT 'Datos Binance Pay',
            datos_pago_wise JSON COMMENT 'Datos Wise/TransferWise',
            datos_pago_paypal JSON COMMENT 'Email PayPal',
            datos_pago_banco JSON COMMENT 'Datos bancarios para transferencia',

            /* ─────────────────────────────────────────────────────────────────
             * DOCUMENTOS REQUERIDOS (URLs a archivos subidos)
             * ───────────────────────────────────────────────────────────────── */
            documento_identidad_url VARCHAR(500) COMMENT 'Scan de documento de identidad',
            rut_url VARCHAR(500) COMMENT 'RUT/RFC/Documento fiscal',
            certificado_bancario_url VARCHAR(500) COMMENT 'Certificación bancaria',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO Y VERIFICACIÓN
             * ───────────────────────────────────────────────────────────────── */
            estado ENUM('PENDIENTE_VERIFICACION', 'VERIFICADO', 'RECHAZADO', 'SUSPENDIDO') DEFAULT 'PENDIENTE_VERIFICACION',
            fecha_verificacion DATETIME COMMENT 'Cuándo fue verificado',
            verificado_por BIGINT UNSIGNED COMMENT 'Quién verificó',
            notas_verificacion TEXT COMMENT 'Notas del proceso de verificación',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADÍSTICAS (se actualizan automáticamente)
             * ───────────────────────────────────────────────────────────────── */
            total_aplicaciones INT DEFAULT 0 COMMENT 'Total de aplicaciones realizadas',
            aplicaciones_aceptadas INT DEFAULT 0 COMMENT 'Aplicaciones aceptadas',
            calificacion_promedio DECIMAL(3,2) DEFAULT 0 COMMENT 'Rating promedio 0-5',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES para optimizar búsquedas frecuentes
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_usuario_wp (usuario_wp_id),
            INDEX idx_tipo (tipo),
            INDEX idx_estado (estado),
            INDEX idx_pais (pais),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_ordenes_trabajo
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Representa las "ofertas de trabajo" que la empresa publica en el portal.
     * Es el concepto central del MARKETPLACE - similar a publicar un proyecto
     * en Freelancer.com o Upwork.
     *
     * =========================================================================
     * NUMERACIÓN AUTOMÁTICA:
     * =========================================================================
     * Formato: OT-[AÑO]-[CONSECUTIVO]
     * Ejemplo: OT-2024-0001, OT-2024-0002, OT-2025-0001
     *
     * =========================================================================
     * CICLO DE VIDA DE UNA ORDEN:
     * =========================================================================
     *
     *   BORRADOR ──► PUBLICADA ──► ASIGNADA ──► EN_PROGRESO ──► COMPLETADA
     *       │            │            │
     *       │            │            └──► CANCELADA
     *       │            └──► CERRADA (sin asignar)
     *       └──► CANCELADA
     *
     * =========================================================================
     * RELACIONES:
     * =========================================================================
     * - caso_id → wp_ga_casos (opcional, si es para caso específico)
     * - proyecto_id → wp_ga_proyectos (opcional)
     * - departamento_id → wp_ga_departamentos (qué área necesita el trabajo)
     * - puesto_requerido_id → wp_ga_puestos (perfil requerido)
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_ordenes_trabajo_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_ordenes_trabajo';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * IDENTIFICACIÓN
             * El código se genera automáticamente: OT-2024-0001
             * ───────────────────────────────────────────────────────────────── */
            codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Formato: OT-YYYY-NNNN',

            /* ─────────────────────────────────────────────────────────────────
             * INFORMACIÓN BÁSICA DE LA ORDEN
             * ───────────────────────────────────────────────────────────────── */
            titulo VARCHAR(200) NOT NULL COMMENT 'Título descriptivo de la orden',
            descripcion TEXT COMMENT 'Descripción detallada del trabajo requerido',
            requisitos TEXT COMMENT 'Requisitos específicos para aplicar',

            /* ─────────────────────────────────────────────────────────────────
             * CATEGORIZACIÓN
             * ───────────────────────────────────────────────────────────────── */
            categoria ENUM('DESARROLLO', 'DISENO', 'MARKETING', 'LEGAL', 'CONTABILIDAD', 'ADMINISTRATIVO', 'SOPORTE', 'CONSULTORIA', 'OTRO') DEFAULT 'OTRO',
            departamento_id INT COMMENT 'FK departamento que solicita',
            puesto_requerido_id INT COMMENT 'FK puesto/perfil requerido',

            /* ─────────────────────────────────────────────────────────────────
             * CONDICIONES ECONÓMICAS
             * - tipo_pago: Si es por hora o precio fijo
             * - tarifa_hora: Tarifa ofrecida por hora (si aplica)
             * - presupuesto_fijo: Monto fijo del proyecto (si aplica)
             * - tarifa_negociable: Si el aplicante puede proponer otra tarifa
             * ───────────────────────────────────────────────────────────────── */
            tipo_pago ENUM('POR_HORA', 'PRECIO_FIJO', 'A_CONVENIR') DEFAULT 'POR_HORA',
            tarifa_hora_min DECIMAL(10,2) COMMENT 'Tarifa mínima por hora (USD)',
            tarifa_hora_max DECIMAL(10,2) COMMENT 'Tarifa máxima por hora (USD)',
            presupuesto_fijo DECIMAL(12,2) COMMENT 'Presupuesto fijo total (USD)',
            tarifa_negociable TINYINT(1) DEFAULT 1 COMMENT '1=Acepta propuestas de tarifa',

            /* ─────────────────────────────────────────────────────────────────
             * DURACIÓN Y DEDICACIÓN
             * ───────────────────────────────────────────────────────────────── */
            horas_estimadas INT COMMENT 'Horas estimadas totales',
            duracion_estimada VARCHAR(100) COMMENT 'Ej: 2 semanas, 1 mes',
            dedicacion ENUM('TIEMPO_COMPLETO', 'MEDIO_TIEMPO', 'POR_HORAS', 'PROYECTO') DEFAULT 'POR_HORAS',

            /* ─────────────────────────────────────────────────────────────────
             * FECHAS IMPORTANTES
             * ───────────────────────────────────────────────────────────────── */
            fecha_publicacion DATE COMMENT 'Cuándo se publicó en el portal',
            fecha_cierre_aplicaciones DATE COMMENT 'Hasta cuándo se reciben aplicaciones',
            fecha_inicio_estimada DATE COMMENT 'Cuándo debería iniciar el trabajo',
            fecha_fin_estimada DATE COMMENT 'Cuándo debería terminar',

            /* ─────────────────────────────────────────────────────────────────
             * CONTROL DE APLICACIONES
             * ───────────────────────────────────────────────────────────────── */
            max_aplicantes INT DEFAULT 0 COMMENT '0=Sin límite, >0=Límite de aplicaciones',
            total_aplicantes INT DEFAULT 0 COMMENT 'Contador de aplicaciones (se actualiza)',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO DE LA ORDEN
             * ───────────────────────────────────────────────────────────────── */
            estado ENUM('BORRADOR', 'PUBLICADA', 'CERRADA', 'ASIGNADA', 'EN_PROGRESO', 'COMPLETADA', 'CANCELADA') DEFAULT 'BORRADOR',

            /* ─────────────────────────────────────────────────────────────────
             * UBICACIÓN Y MODALIDAD
             * ───────────────────────────────────────────────────────────────── */
            modalidad ENUM('REMOTO', 'PRESENCIAL', 'HIBRIDO') DEFAULT 'REMOTO',
            ubicacion VARCHAR(200) COMMENT 'Ubicación si es presencial/híbrido',
            zona_horaria VARCHAR(50) COMMENT 'Zona horaria requerida, ej: America/Bogota',

            /* ─────────────────────────────────────────────────────────────────
             * VINCULACIÓN CON PROYECTOS/CASOS (opcional)
             * Si la orden es para un proyecto específico de un cliente
             * ───────────────────────────────────────────────────────────────── */
            caso_id INT COMMENT 'FK wp_ga_casos (si aplica)',
            proyecto_id INT COMMENT 'FK wp_ga_proyectos (si aplica)',
            cliente_id INT COMMENT 'FK wp_ga_clientes (si es para cliente)',

            /* ─────────────────────────────────────────────────────────────────
             * RESPONSABLE INTERNO
             * ───────────────────────────────────────────────────────────────── */
            responsable_id BIGINT UNSIGNED COMMENT 'Usuario WP que gestiona la orden',

            /* ─────────────────────────────────────────────────────────────────
             * CONFIGURACIÓN DE VISIBILIDAD
             * ───────────────────────────────────────────────────────────────── */
            es_publica TINYINT(1) DEFAULT 1 COMMENT '1=Visible en portal público',
            requiere_nda TINYINT(1) DEFAULT 0 COMMENT '1=Requiere firmar NDA para ver detalles',

            /* ─────────────────────────────────────────────────────────────────
             * HABILIDADES REQUERIDAS (JSON array)
             * Ejemplo: (PHP, WordPress, MySQL, React)
             * ───────────────────────────────────────────────────────────────── */
            habilidades_requeridas JSON COMMENT 'Array de skills requeridos',
            experiencia_minima INT DEFAULT 0 COMMENT 'Años mínimos de experiencia',

            /* ─────────────────────────────────────────────────────────────────
             * ARCHIVOS ADJUNTOS
             * ───────────────────────────────────────────────────────────────── */
            archivos_adjuntos JSON COMMENT 'Array de URLs de archivos adjuntos',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            notas_internas TEXT COMMENT 'Notas solo visibles para admin',
            activo TINYINT(1) DEFAULT 1,
            created_by BIGINT UNSIGNED COMMENT 'Quién creó la orden',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES para búsquedas frecuentes
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_codigo (codigo),
            INDEX idx_estado (estado),
            INDEX idx_categoria (categoria),
            INDEX idx_departamento (departamento_id),
            INDEX idx_fecha_pub (fecha_publicacion),
            INDEX idx_responsable (responsable_id),
            INDEX idx_es_publica (es_publica),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_aplicaciones_orden
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Registra cada aplicación de un aplicante a una orden de trabajo.
     * Es la tabla PUENTE entre aplicantes y órdenes (relación M:N).
     *
     * =========================================================================
     * CICLO DE VIDA DE UNA APLICACIÓN:
     * =========================================================================
     *
     *   PENDIENTE ──► EN_REVISION ──► PRESELECCIONADO ──► ACEPTADA ──► CONTRATADO
     *       │              │               │                  │
     *       │              │               │                  └──► RECHAZADA_POST
     *       │              │               └──► RECHAZADA
     *       │              └──► RECHAZADA
     *       └──► RECHAZADA
     *
     * =========================================================================
     * RELACIONES:
     * =========================================================================
     * - orden_trabajo_id → wp_ga_ordenes_trabajo
     * - aplicante_id → wp_ga_aplicantes
     * - evaluado_por → wp_users (quién revisó la aplicación)
     *
     * =========================================================================
     * CONSTRAINT ÚNICO:
     * =========================================================================
     * Un aplicante solo puede aplicar UNA VEZ a cada orden de trabajo.
     * UNIQUE KEY (orden_trabajo_id, aplicante_id)
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_aplicaciones_orden_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_aplicaciones_orden';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * RELACIONES PRINCIPALES
             * ───────────────────────────────────────────────────────────────── */
            orden_trabajo_id INT NOT NULL COMMENT 'FK wp_ga_ordenes_trabajo',
            aplicante_id INT NOT NULL COMMENT 'FK wp_ga_aplicantes',

            /* ─────────────────────────────────────────────────────────────────
             * DATOS DE LA APLICACIÓN
             * Lo que el aplicante envía al aplicar
             * ───────────────────────────────────────────────────────────────── */
            fecha_aplicacion DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo aplicó',
            carta_presentacion TEXT COMMENT 'Mensaje/propuesta del aplicante',
            tarifa_solicitada DECIMAL(10,2) COMMENT 'Tarifa que propone el aplicante',
            disponibilidad VARCHAR(200) COMMENT 'Ej: Inmediata, En 2 semanas',
            horas_disponibles_semana INT COMMENT 'Horas que puede dedicar por semana',

            /* ─────────────────────────────────────────────────────────────────
             * ARCHIVOS ADICIONALES
             * El aplicante puede adjuntar documentos específicos para esta orden
             * ───────────────────────────────────────────────────────────────── */
            archivos_adjuntos JSON COMMENT 'URLs de archivos adjuntos a la aplicación',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO DE LA APLICACIÓN
             * ───────────────────────────────────────────────────────────────── */
            estado ENUM(
                'PENDIENTE',        /* Recién aplicó, sin revisar */
                'EN_REVISION',      /* Alguien está revisando */
                'PRESELECCIONADO',  /* Pasó primera revisión */
                'ENTREVISTA',       /* Citado a entrevista */
                'ACEPTADA',         /* Aceptado para el trabajo */
                'RECHAZADA',        /* No seleccionado */
                'CONTRATADO',       /* Ya se generó contrato */
                'RETIRADA'          /* El aplicante retiró su aplicación */
            ) DEFAULT 'PENDIENTE',

            /* ─────────────────────────────────────────────────────────────────
             * EVALUACIÓN POR PARTE DE LA EMPRESA
             * ───────────────────────────────────────────────────────────────── */
            puntuacion INT COMMENT 'Puntuación interna 1-10',
            notas_evaluacion TEXT COMMENT 'Notas del evaluador',
            evaluado_por BIGINT UNSIGNED COMMENT 'Usuario WP que evaluó',
            fecha_evaluacion DATETIME COMMENT 'Cuándo se evaluó',

            /* ─────────────────────────────────────────────────────────────────
             * MOTIVO DE RECHAZO (si aplica)
             * ───────────────────────────────────────────────────────────────── */
            motivo_rechazo ENUM(
                'PERFIL_NO_ADECUADO',
                'TARIFA_ALTA',
                'DISPONIBILIDAD',
                'DOCUMENTOS_INCOMPLETOS',
                'OTRO_CANDIDATO',
                'ORDEN_CANCELADA',
                'OTRO'
            ) COMMENT 'Razón del rechazo',
            detalle_rechazo TEXT COMMENT 'Explicación adicional del rechazo',

            /* ─────────────────────────────────────────────────────────────────
             * CONTRATACIÓN (cuando se acepta)
             * ───────────────────────────────────────────────────────────────── */
            contrato_generado_id INT COMMENT 'FK wp_ga_contratos_trabajo (si se generó)',
            fecha_contratacion DATETIME COMMENT 'Cuándo se formalizó la contratación',
            tarifa_acordada DECIMAL(10,2) COMMENT 'Tarifa final acordada',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * CONSTRAINT: Un aplicante solo puede aplicar una vez por orden
             * ───────────────────────────────────────────────────────────────── */
            UNIQUE KEY uk_orden_aplicante (orden_trabajo_id, aplicante_id),

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES para búsquedas frecuentes
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_orden (orden_trabajo_id),
            INDEX idx_aplicante (aplicante_id),
            INDEX idx_estado (estado),
            INDEX idx_fecha (fecha_aplicacion)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    // =========================================================================
    // MIGRACIONES PARA INSTALACIONES EXISTENTES
    // =========================================================================

    /**
     * Ejecutar migraciones para actualizar instalaciones existentes
     *
     * Esta función se ejecuta en cada activación y agrega datos faltantes
     * sin duplicar los existentes. Útil para agregar nuevos países, etc.
     *
     * @param object $wpdb Instancia global de WordPress Database
     */
    private static function run_migrations($wpdb) {
        // Migración 1.2.1: Agregar Costa Rica si no existe
        self::migration_add_costa_rica($wpdb);

        // Migración 1.5.0: Agregar columna empresa_id a ordenes_trabajo
        self::migration_add_empresa_id_ordenes($wpdb);

        // Migración 1.5.1: Estandarizar tiempos en MINUTOS
        self::migration_tiempos_a_minutos($wpdb);
    }

    /**
     * Migración: Agregar Costa Rica a la tabla de países
     *
     * Costa Rica (CR) con facturación electrónica del Ministerio de Hacienda.
     * Solo inserta si el país no existe en la tabla.
     *
     * @param object $wpdb Instancia global de WordPress Database
     */
    private static function migration_add_costa_rica($wpdb) {
        $table_paises = $wpdb->prefix . 'ga_paises_config';

        // Verificar si Costa Rica ya existe
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_paises} WHERE codigo_iso = %s",
            'CR'
        ));

        // Solo insertar si no existe
        if ($exists == 0) {
            $wpdb->insert(
                $table_paises,
                array(
                    'codigo_iso'           => 'CR',
                    'nombre'               => 'Costa Rica',
                    'moneda_codigo'        => 'CRC',
                    'moneda_simbolo'       => '₡',
                    'impuesto_nombre'      => 'IVA',
                    'impuesto_porcentaje'  => 13.00,
                    'retencion_default'    => 0.00,
                    'formato_factura'      => 'FE-CR-{YYYY}-{NNNN}',
                    'requiere_electronica' => 1,
                    'proveedor_electronica' => 'Ministerio de Hacienda Costa Rica',
                    'activo'               => 1
                ),
                array('%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%d', '%s', '%d')
            );

        }
    }

    /**
     * Migración: Agregar columna empresa_id a wp_ga_ordenes_trabajo
     *
     * Esta columna permite asociar una empresa pagadora a cada orden de trabajo.
     * Se agrega en Sprint 11-12 para acuerdos económicos.
     *
     * @param object $wpdb Instancia global de WordPress Database
     */
    private static function migration_add_empresa_id_ordenes($wpdb) {
        $table_ordenes = $wpdb->prefix . 'ga_ordenes_trabajo';

        // Verificar si la columna ya existe
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SHOW COLUMNS FROM {$table_ordenes} LIKE %s",
            'empresa_id'
        ));

        // Solo agregar si no existe
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE {$table_ordenes} ADD COLUMN empresa_id INT COMMENT 'FK wp_ga_empresas - Empresa pagadora' AFTER cliente_id");
            $wpdb->query("ALTER TABLE {$table_ordenes} ADD INDEX idx_empresa (empresa_id)");
        }
    }

    /**
     * Migración: Estandarizar tiempos en MINUTOS
     *
     * Cambia columnas de horas_estimadas a minutos_estimados en tareas y subtareas.
     * Agrega campo descripcion a subtareas.
     * Convierte datos existentes de horas a minutos (x60).
     *
     * ESTÁNDAR: Todos los tiempos se guardan en MINUTOS en la BD.
     *
     * @param object $wpdb Instancia global de WordPress Database
     */
    private static function migration_tiempos_a_minutos($wpdb) {
        $table_tareas = $wpdb->prefix . 'ga_tareas';
        $table_subtareas = $wpdb->prefix . 'ga_subtareas';

        // =====================================================================
        // MIGRACIÓN TABLA TAREAS: horas_estimadas → minutos_estimados
        // =====================================================================

        // Verificar si la columna vieja existe
        $columna_horas_tareas = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_tareas} LIKE 'horas_estimadas'"
        );

        if (!empty($columna_horas_tareas)) {
            // 1. Crear nueva columna minutos_estimados
            $wpdb->query(
                "ALTER TABLE {$table_tareas}
                 ADD COLUMN minutos_estimados INT DEFAULT 60 COMMENT 'Tiempo estimado en MINUTOS'
                 AFTER descripcion"
            );

            // 2. Migrar datos: convertir horas a minutos (x60)
            $wpdb->query(
                "UPDATE {$table_tareas}
                 SET minutos_estimados = ROUND(COALESCE(horas_estimadas, 1) * 60)
                 WHERE minutos_estimados IS NULL OR minutos_estimados = 60"
            );

            // 3. Eliminar columna vieja
            $wpdb->query("ALTER TABLE {$table_tareas} DROP COLUMN horas_estimadas");
        }

        // Verificar si ya existe minutos_estimados pero con otro default
        $columna_minutos_tareas = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_tareas} LIKE 'minutos_estimados'"
        );

        // Si no existe la columna minutos_estimados, crearla
        if (empty($columna_minutos_tareas)) {
            $wpdb->query(
                "ALTER TABLE {$table_tareas}
                 ADD COLUMN minutos_estimados INT DEFAULT 60 COMMENT 'Tiempo estimado en MINUTOS'
                 AFTER descripcion"
            );
        }

        // =====================================================================
        // MIGRACIÓN TABLA SUBTAREAS: horas_estimadas → minutos_estimados
        // =====================================================================

        $columna_horas_subtareas = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_subtareas} LIKE 'horas_estimadas'"
        );

        if (!empty($columna_horas_subtareas)) {
            // 1. Crear nueva columna minutos_estimados
            $wpdb->query(
                "ALTER TABLE {$table_subtareas}
                 ADD COLUMN minutos_estimados INT DEFAULT 15 COMMENT 'Tiempo estimado en MINUTOS'
                 AFTER nombre"
            );

            // 2. Migrar datos: convertir horas a minutos (x60)
            $wpdb->query(
                "UPDATE {$table_subtareas}
                 SET minutos_estimados = ROUND(COALESCE(horas_estimadas, 0.25) * 60)
                 WHERE minutos_estimados IS NULL OR minutos_estimados = 15"
            );

            // 3. Eliminar columna vieja
            $wpdb->query("ALTER TABLE {$table_subtareas} DROP COLUMN horas_estimadas");
        }

        // Si no existe la columna minutos_estimados en subtareas, crearla
        $columna_minutos_subtareas = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_subtareas} LIKE 'minutos_estimados'"
        );

        if (empty($columna_minutos_subtareas)) {
            $wpdb->query(
                "ALTER TABLE {$table_subtareas}
                 ADD COLUMN minutos_estimados INT DEFAULT 15 COMMENT 'Tiempo estimado en MINUTOS'
                 AFTER nombre"
            );
        }

        // =====================================================================
        // AGREGAR COLUMNA DESCRIPCION A SUBTAREAS
        // =====================================================================

        $columna_descripcion = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_subtareas} LIKE 'descripcion'"
        );

        if (empty($columna_descripcion)) {
            $wpdb->query(
                "ALTER TABLE {$table_subtareas}
                 ADD COLUMN descripcion TEXT NULL COMMENT 'Descripción/instrucciones de la subtarea'
                 AFTER nombre"
            );
        }

        // =====================================================================
        // MIGRACIÓN TAREAS: horas_reales → minutos_reales
        // =====================================================================

        $columna_horas_reales = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_tareas} LIKE 'horas_reales'"
        );

        if (!empty($columna_horas_reales)) {
            // 1. Crear nueva columna minutos_reales
            $wpdb->query(
                "ALTER TABLE {$table_tareas}
                 ADD COLUMN minutos_reales INT DEFAULT 0 COMMENT 'Tiempo real en MINUTOS'"
            );

            // 2. Migrar datos: convertir horas a minutos (x60)
            $wpdb->query(
                "UPDATE {$table_tareas}
                 SET minutos_reales = ROUND(COALESCE(horas_reales, 0) * 60)"
            );

            // 3. Eliminar columna vieja
            $wpdb->query("ALTER TABLE {$table_tareas} DROP COLUMN horas_reales");
        }

        // Si no existe minutos_reales, crearla
        $columna_minutos_reales = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_tareas} LIKE 'minutos_reales'"
        );

        if (empty($columna_minutos_reales)) {
            $wpdb->query(
                "ALTER TABLE {$table_tareas}
                 ADD COLUMN minutos_reales INT DEFAULT 0 COMMENT 'Tiempo real en MINUTOS'"
            );
        }

        // =====================================================================
        // MIGRACIÓN SUBTAREAS: horas_reales → minutos_reales
        // =====================================================================

        $columna_horas_reales_sub = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_subtareas} LIKE 'horas_reales'"
        );

        if (!empty($columna_horas_reales_sub)) {
            // 1. Crear nueva columna minutos_reales
            $wpdb->query(
                "ALTER TABLE {$table_subtareas}
                 ADD COLUMN minutos_reales INT DEFAULT 0 COMMENT 'Tiempo real en MINUTOS'"
            );

            // 2. Migrar datos
            $wpdb->query(
                "UPDATE {$table_subtareas}
                 SET minutos_reales = ROUND(COALESCE(horas_reales, 0) * 60)"
            );

            // 3. Eliminar columna vieja
            $wpdb->query("ALTER TABLE {$table_subtareas} DROP COLUMN horas_reales");
        }

        // Si no existe minutos_reales en subtareas, crearla
        $columna_minutos_reales_sub = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table_subtareas} LIKE 'minutos_reales'"
        );

        if (empty($columna_minutos_reales_sub)) {
            $wpdb->query(
                "ALTER TABLE {$table_subtareas}
                 ADD COLUMN minutos_reales INT DEFAULT 0 COMMENT 'Tiempo real en MINUTOS'"
            );
        }
    }

    // =========================================================================
    // TABLAS SPRINT 9-10: FACTURACIÓN Y COTIZACIONES
    // =========================================================================

    /**
     * Crear tabla wp_ga_facturas
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Almacena las facturas emitidas a clientes. Es el documento fiscal principal
     * que registra la venta de servicios profesionales.
     *
     * =========================================================================
     * NUMERACIÓN AUTOMÁTICA:
     * =========================================================================
     * Formato: FAC-[PAÍS]-[AÑO]-[CONSECUTIVO]
     * Ejemplos:
     *   - FAC-CO-2024-0001 (Colombia)
     *   - FAC-US-2024-0001 (USA)
     *   - FAC-MX-2024-0001 (México)
     *
     * El consecutivo es por país y año, se reinicia cada año.
     *
     * =========================================================================
     * CICLO DE VIDA DE UNA FACTURA:
     * =========================================================================
     *
     *   BORRADOR ──► ENVIADA ──► PAGADA
     *       │           │
     *       │           └──► PARCIAL ──► PAGADA
     *       │           │
     *       │           └──► VENCIDA ──► PAGADA (pago tardío)
     *       │
     *       └──► ANULADA
     *
     * =========================================================================
     * RELACIONES:
     * =========================================================================
     * - cliente_id → wp_ga_clientes (a quién se factura)
     * - caso_id → wp_ga_casos (opcional, caso relacionado)
     * - proyecto_id → wp_ga_proyectos (opcional, proyecto relacionado)
     * - cotizacion_origen_id → wp_ga_cotizaciones (si viene de cotización)
     * - pais_id → wp_ga_paises_config (configuración fiscal)
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_facturas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_facturas';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * IDENTIFICACIÓN
             * El número se genera automáticamente según el país
             * ───────────────────────────────────────────────────────────────── */
            numero VARCHAR(30) NOT NULL UNIQUE COMMENT 'Formato: FAC-XX-YYYY-NNNN',

            /* ─────────────────────────────────────────────────────────────────
             * CLIENTE Y DATOS DE FACTURACIÓN
             * ───────────────────────────────────────────────────────────────── */
            cliente_id INT NOT NULL COMMENT 'FK wp_ga_clientes',
            cliente_nombre VARCHAR(200) COMMENT 'Snapshot: nombre al momento de facturar',
            cliente_documento VARCHAR(50) COMMENT 'Snapshot: NIT/RFC al facturar',
            cliente_direccion TEXT COMMENT 'Snapshot: dirección al facturar',
            cliente_email VARCHAR(200) COMMENT 'Email para envío de factura',

            /* ─────────────────────────────────────────────────────────────────
             * REFERENCIA A CASO/PROYECTO (opcional)
             * ───────────────────────────────────────────────────────────────── */
            caso_id INT COMMENT 'FK wp_ga_casos (si aplica)',
            proyecto_id INT COMMENT 'FK wp_ga_proyectos (si aplica)',

            /* ─────────────────────────────────────────────────────────────────
             * ORIGEN (si viene de cotización)
             * ───────────────────────────────────────────────────────────────── */
            cotizacion_origen_id INT COMMENT 'FK wp_ga_cotizaciones (si se convirtió)',

            /* ─────────────────────────────────────────────────────────────────
             * CONFIGURACIÓN FISCAL
             * ───────────────────────────────────────────────────────────────── */
            pais_facturacion VARCHAR(2) NOT NULL COMMENT 'Código ISO del país',
            moneda VARCHAR(3) DEFAULT 'USD' COMMENT 'Código ISO moneda',
            tasa_cambio DECIMAL(12,4) DEFAULT 1.0000 COMMENT 'Tasa al momento de facturar',

            /* ─────────────────────────────────────────────────────────────────
             * IMPUESTOS Y RETENCIONES
             * ───────────────────────────────────────────────────────────────── */
            impuesto_nombre VARCHAR(50) COMMENT 'IVA, Sales Tax, etc.',
            impuesto_porcentaje DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de impuesto',
            retencion_nombre VARCHAR(50) COMMENT 'Retención en la fuente, etc.',
            retencion_porcentaje DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de retención',

            /* ─────────────────────────────────────────────────────────────────
             * MONTOS (calculados automáticamente desde detalle)
             * ───────────────────────────────────────────────────────────────── */
            subtotal DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Suma de líneas sin impuesto',
            descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Descuento global %',
            descuento_monto DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Monto de descuento',
            base_impuesto DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Base para calcular impuesto',
            impuesto_monto DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Monto del impuesto',
            total DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Total con impuesto',
            retencion_monto DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Monto de retención',
            total_a_pagar DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Total neto a pagar',

            /* ─────────────────────────────────────────────────────────────────
             * PAGOS RECIBIDOS
             * ───────────────────────────────────────────────────────────────── */
            monto_pagado DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Total pagado hasta ahora',
            saldo_pendiente DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Saldo por cobrar',

            /* ─────────────────────────────────────────────────────────────────
             * FECHAS
             * ───────────────────────────────────────────────────────────────── */
            fecha_emision DATE COMMENT 'Fecha de emisión de la factura',
            fecha_vencimiento DATE COMMENT 'Fecha límite de pago',
            dias_credito INT DEFAULT 30 COMMENT 'Días de crédito otorgados',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO DE LA FACTURA
             * ───────────────────────────────────────────────────────────────── */
            estado ENUM(
                'BORRADOR',     /* En edición, no enviada */
                'ENVIADA',      /* Enviada al cliente, pendiente de pago */
                'PARCIAL',      /* Pago parcial recibido */
                'PAGADA',       /* Completamente pagada */
                'VENCIDA',      /* Pasó fecha de vencimiento sin pago total */
                'ANULADA'       /* Anulada/cancelada */
            ) DEFAULT 'BORRADOR',

            /* ─────────────────────────────────────────────────────────────────
             * DATOS DE FACTURACIÓN ELECTRÓNICA (si aplica)
             * ───────────────────────────────────────────────────────────────── */
            numero_documento_pos VARCHAR(50) COMMENT 'Número en sistema POS externo',
            consecutivo_dian VARCHAR(100) COMMENT 'Consecutivo DIAN/SAT/SII',
            cufe VARCHAR(200) COMMENT 'Código Único de Factura Electrónica',
            qr_code TEXT COMMENT 'Código QR de validación',
            url_pdf VARCHAR(500) COMMENT 'URL del PDF firmado',
            url_xml VARCHAR(500) COMMENT 'URL del XML firmado',

            /* ─────────────────────────────────────────────────────────────────
             * INFORMACIÓN ADICIONAL
             * ───────────────────────────────────────────────────────────────── */
            concepto_general TEXT COMMENT 'Descripción general de la factura',
            notas TEXT COMMENT 'Notas adicionales visibles al cliente',
            notas_internas TEXT COMMENT 'Notas solo para uso interno',
            terminos TEXT COMMENT 'Términos y condiciones',

            /* ─────────────────────────────────────────────────────────────────
             * COSTOS INTERNOS (para cálculo de rentabilidad)
             * ───────────────────────────────────────────────────────────────── */
            costo_horas DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Costo de las horas facturadas',
            comisiones_total DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Total comisiones generadas',
            utilidad_bruta DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Utilidad antes de comisiones',
            utilidad_neta DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Utilidad final',
            margen_porcentaje DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Margen de utilidad %',

            /* ─────────────────────────────────────────────────────────────────
             * RESPONSABLES
             * ───────────────────────────────────────────────────────────────── */
            creado_por BIGINT UNSIGNED COMMENT 'Usuario WP que creó la factura',
            enviado_por BIGINT UNSIGNED COMMENT 'Usuario que envió la factura',
            fecha_envio DATETIME COMMENT 'Cuándo se envió al cliente',
            anulado_por BIGINT UNSIGNED COMMENT 'Usuario que anuló (si aplica)',
            fecha_anulacion DATETIME COMMENT 'Cuándo se anuló',
            motivo_anulacion TEXT COMMENT 'Razón de anulación',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES para búsquedas frecuentes
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_numero (numero),
            INDEX idx_cliente (cliente_id),
            INDEX idx_caso (caso_id),
            INDEX idx_proyecto (proyecto_id),
            INDEX idx_estado (estado),
            INDEX idx_pais (pais_facturacion),
            INDEX idx_fecha_emision (fecha_emision),
            INDEX idx_fecha_vencimiento (fecha_vencimiento)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_facturas_detalle
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Almacena las líneas de detalle de cada factura. Cada línea representa
     * un concepto, servicio u hora facturada.
     *
     * =========================================================================
     * TIPOS DE LÍNEA:
     * =========================================================================
     * - SERVICIO: Servicio profesional genérico
     * - HORA: Horas trabajadas (puede venir de registro de horas)
     * - PRODUCTO: Producto o licencia (poco común)
     * - DESCUENTO: Línea de descuento negativa
     * - AJUSTE: Ajuste de precio
     *
     * =========================================================================
     * RELACIONES:
     * =========================================================================
     * - factura_id → wp_ga_facturas (factura padre)
     * - registro_hora_id → wp_ga_registro_horas (si es hora facturada)
     * - tarea_id → wp_ga_tareas (referencia opcional)
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_facturas_detalle_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_facturas_detalle';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * RELACIÓN CON FACTURA
             * ───────────────────────────────────────────────────────────────── */
            factura_id INT NOT NULL COMMENT 'FK wp_ga_facturas',

            /* ─────────────────────────────────────────────────────────────────
             * ORDEN DE LA LÍNEA
             * ───────────────────────────────────────────────────────────────── */
            orden INT DEFAULT 0 COMMENT 'Orden de aparición en la factura',

            /* ─────────────────────────────────────────────────────────────────
             * TIPO DE LÍNEA
             * ───────────────────────────────────────────────────────────────── */
            tipo ENUM('SERVICIO', 'HORA', 'PRODUCTO', 'DESCUENTO', 'AJUSTE') DEFAULT 'SERVICIO',

            /* ─────────────────────────────────────────────────────────────────
             * DESCRIPCIÓN DEL CONCEPTO
             * ───────────────────────────────────────────────────────────────── */
            codigo VARCHAR(50) COMMENT 'Código del concepto (opcional)',
            descripcion TEXT NOT NULL COMMENT 'Descripción del concepto/servicio',

            /* ─────────────────────────────────────────────────────────────────
             * CANTIDADES Y PRECIOS
             * ───────────────────────────────────────────────────────────────── */
            cantidad DECIMAL(10,2) DEFAULT 1.00 COMMENT 'Cantidad (horas, unidades)',
            unidad VARCHAR(20) DEFAULT 'UNIDAD' COMMENT 'Unidad de medida (HORA, UNIDAD, etc)',
            precio_unitario DECIMAL(14,4) DEFAULT 0.0000 COMMENT 'Precio por unidad',
            descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Descuento % de la línea',
            descuento_monto DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Monto descuento calculado',
            subtotal DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Cantidad * Precio - Descuento',

            /* ─────────────────────────────────────────────────────────────────
             * IMPUESTO DE LA LÍNEA (algunos países lo manejan por línea)
             * ───────────────────────────────────────────────────────────────── */
            aplica_impuesto TINYINT(1) DEFAULT 1 COMMENT '1=Grava impuesto',
            impuesto_porcentaje DECIMAL(5,2) DEFAULT 0.00 COMMENT '% impuesto si es por línea',
            impuesto_monto DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Monto impuesto',
            total_linea DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Subtotal + Impuesto',

            /* ─────────────────────────────────────────────────────────────────
             * REFERENCIA A HORAS/TAREAS (si aplica)
             * Para facturación de horas trabajadas
             * ───────────────────────────────────────────────────────────────── */
            registro_hora_id INT COMMENT 'FK wp_ga_registro_horas (si es hora)',
            tarea_id INT COMMENT 'FK wp_ga_tareas (referencia)',
            fecha_servicio DATE COMMENT 'Fecha en que se prestó el servicio',

            /* ─────────────────────────────────────────────────────────────────
             * COSTO INTERNO (para rentabilidad)
             * ───────────────────────────────────────────────────────────────── */
            costo_unitario DECIMAL(14,4) DEFAULT 0.0000 COMMENT 'Costo real por unidad',
            costo_total DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Costo total de la línea',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_factura (factura_id),
            INDEX idx_tipo (tipo),
            INDEX idx_registro_hora (registro_hora_id),
            INDEX idx_tarea (tarea_id),
            INDEX idx_orden (orden)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_cotizaciones
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Almacena cotizaciones/presupuestos enviados a clientes antes de facturar.
     * Una cotización aprobada puede convertirse en factura con un clic.
     *
     * =========================================================================
     * NUMERACIÓN AUTOMÁTICA:
     * =========================================================================
     * Formato: COT-[AÑO]-[CONSECUTIVO]
     * Ejemplos: COT-2024-0001, COT-2024-0002
     *
     * =========================================================================
     * CICLO DE VIDA DE UNA COTIZACIÓN:
     * =========================================================================
     *
     *   BORRADOR ──► ENVIADA ──► APROBADA ──► FACTURADA
     *       │           │            │
     *       │           │            └──► VENCIDA (no se facturó a tiempo)
     *       │           └──► RECHAZADA
     *       │           │
     *       │           └──► VENCIDA (pasó vigencia)
     *       │
     *       └──► CANCELADA
     *
     * =========================================================================
     * RELACIONES:
     * =========================================================================
     * - cliente_id → wp_ga_clientes
     * - caso_id → wp_ga_casos (opcional)
     * - proyecto_id → wp_ga_proyectos (opcional)
     * - factura_generada_id → wp_ga_facturas (cuando se convierte)
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_cotizaciones_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_cotizaciones';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * IDENTIFICACIÓN
             * ───────────────────────────────────────────────────────────────── */
            numero VARCHAR(30) NOT NULL UNIQUE COMMENT 'Formato: COT-YYYY-NNNN',

            /* ─────────────────────────────────────────────────────────────────
             * CLIENTE Y DATOS
             * ───────────────────────────────────────────────────────────────── */
            cliente_id INT NOT NULL COMMENT 'FK wp_ga_clientes',
            cliente_nombre VARCHAR(200) COMMENT 'Snapshot: nombre del cliente',
            cliente_email VARCHAR(200) COMMENT 'Email para envío',
            contacto_nombre VARCHAR(200) COMMENT 'Nombre del contacto',

            /* ─────────────────────────────────────────────────────────────────
             * REFERENCIA A CASO/PROYECTO (opcional)
             * ───────────────────────────────────────────────────────────────── */
            caso_id INT COMMENT 'FK wp_ga_casos',
            proyecto_id INT COMMENT 'FK wp_ga_proyectos',

            /* ─────────────────────────────────────────────────────────────────
             * INFORMACIÓN GENERAL
             * ───────────────────────────────────────────────────────────────── */
            titulo VARCHAR(200) COMMENT 'Título de la cotización',
            descripcion TEXT COMMENT 'Descripción general del servicio',

            /* ─────────────────────────────────────────────────────────────────
             * CONFIGURACIÓN MONETARIA
             * ───────────────────────────────────────────────────────────────── */
            moneda VARCHAR(3) DEFAULT 'USD' COMMENT 'Código ISO moneda',
            pais_destino VARCHAR(2) COMMENT 'País del cliente para impuestos',

            /* ─────────────────────────────────────────────────────────────────
             * IMPUESTOS (para preview)
             * ───────────────────────────────────────────────────────────────── */
            impuesto_nombre VARCHAR(50) COMMENT 'IVA, etc.',
            impuesto_porcentaje DECIMAL(5,2) DEFAULT 0.00,

            /* ─────────────────────────────────────────────────────────────────
             * MONTOS (calculados desde detalle)
             * ───────────────────────────────────────────────────────────────── */
            subtotal DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Suma de líneas',
            descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
            descuento_monto DECIMAL(14,2) DEFAULT 0.00,
            impuesto_monto DECIMAL(14,2) DEFAULT 0.00,
            total DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Total cotizado',

            /* ─────────────────────────────────────────────────────────────────
             * FECHAS
             * ───────────────────────────────────────────────────────────────── */
            fecha_emision DATE COMMENT 'Fecha de emisión',
            fecha_vigencia DATE COMMENT 'Válida hasta esta fecha',
            dias_vigencia INT DEFAULT 30 COMMENT 'Días de vigencia',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO DE LA COTIZACIÓN
             * ───────────────────────────────────────────────────────────────── */
            estado ENUM(
                'BORRADOR',     /* En edición */
                'ENVIADA',      /* Enviada al cliente */
                'APROBADA',     /* Cliente aceptó */
                'RECHAZADA',    /* Cliente rechazó */
                'FACTURADA',    /* Se generó factura */
                'VENCIDA',      /* Pasó fecha de vigencia */
                'CANCELADA'     /* Cancelada internamente */
            ) DEFAULT 'BORRADOR',

            /* ─────────────────────────────────────────────────────────────────
             * CONVERSIÓN A FACTURA
             * ───────────────────────────────────────────────────────────────── */
            factura_generada_id INT COMMENT 'FK wp_ga_facturas (cuando se convierte)',
            fecha_conversion DATETIME COMMENT 'Cuándo se convirtió a factura',
            convertido_por BIGINT UNSIGNED COMMENT 'Quién convirtió',

            /* ─────────────────────────────────────────────────────────────────
             * INFORMACIÓN ADICIONAL
             * ───────────────────────────────────────────────────────────────── */
            notas TEXT COMMENT 'Notas visibles al cliente',
            notas_internas TEXT COMMENT 'Notas internas',
            terminos TEXT COMMENT 'Términos y condiciones',
            forma_pago TEXT COMMENT 'Descripción de forma de pago',

            /* ─────────────────────────────────────────────────────────────────
             * APROBACIÓN/RECHAZO
             * ───────────────────────────────────────────────────────────────── */
            fecha_respuesta DATETIME COMMENT 'Cuándo respondió el cliente',
            motivo_rechazo TEXT COMMENT 'Si rechazó, por qué',

            /* ─────────────────────────────────────────────────────────────────
             * RESPONSABLES
             * ───────────────────────────────────────────────────────────────── */
            creado_por BIGINT UNSIGNED COMMENT 'Quién creó',
            enviado_por BIGINT UNSIGNED COMMENT 'Quién envió',
            fecha_envio DATETIME COMMENT 'Cuándo se envió',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_numero (numero),
            INDEX idx_cliente (cliente_id),
            INDEX idx_caso (caso_id),
            INDEX idx_proyecto (proyecto_id),
            INDEX idx_estado (estado),
            INDEX idx_fecha_emision (fecha_emision),
            INDEX idx_fecha_vigencia (fecha_vigencia)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_cotizaciones_detalle
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Almacena las líneas de detalle de cada cotización.
     * Estructura similar a facturas_detalle para facilitar conversión.
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_cotizaciones_detalle_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_cotizaciones_detalle';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * RELACIÓN CON COTIZACIÓN
             * ───────────────────────────────────────────────────────────────── */
            cotizacion_id INT NOT NULL COMMENT 'FK wp_ga_cotizaciones',

            /* ─────────────────────────────────────────────────────────────────
             * ORDEN Y TIPO
             * ───────────────────────────────────────────────────────────────── */
            orden INT DEFAULT 0 COMMENT 'Orden de aparición',
            tipo ENUM('SERVICIO', 'HORA', 'PRODUCTO', 'DESCUENTO') DEFAULT 'SERVICIO',

            /* ─────────────────────────────────────────────────────────────────
             * DESCRIPCIÓN
             * ───────────────────────────────────────────────────────────────── */
            codigo VARCHAR(50) COMMENT 'Código del servicio (opcional)',
            descripcion TEXT NOT NULL COMMENT 'Descripción del concepto',

            /* ─────────────────────────────────────────────────────────────────
             * CANTIDADES Y PRECIOS
             * ───────────────────────────────────────────────────────────────── */
            cantidad DECIMAL(10,2) DEFAULT 1.00,
            unidad VARCHAR(20) DEFAULT 'UNIDAD',
            precio_unitario DECIMAL(14,4) DEFAULT 0.0000,
            descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
            descuento_monto DECIMAL(14,2) DEFAULT 0.00,
            subtotal DECIMAL(14,2) DEFAULT 0.00,

            /* ─────────────────────────────────────────────────────────────────
             * IMPUESTO (si se maneja por línea)
             * ───────────────────────────────────────────────────────────────── */
            aplica_impuesto TINYINT(1) DEFAULT 1,
            impuesto_porcentaje DECIMAL(5,2) DEFAULT 0.00,
            impuesto_monto DECIMAL(14,2) DEFAULT 0.00,
            total_linea DECIMAL(14,2) DEFAULT 0.00,

            /* ─────────────────────────────────────────────────────────────────
             * ESTIMACIONES DE TIEMPO (para cotizaciones de horas)
             * ───────────────────────────────────────────────────────────────── */
            horas_estimadas DECIMAL(10,2) COMMENT 'Horas estimadas para este ítem',
            tarifa_hora DECIMAL(14,4) COMMENT 'Tarifa por hora estimada',

            /* ─────────────────────────────────────────────────────────────────
             * NOTAS
             * ───────────────────────────────────────────────────────────────── */
            notas TEXT COMMENT 'Notas adicionales de la línea',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_cotizacion (cotizacion_id),
            INDEX idx_tipo (tipo),
            INDEX idx_orden (orden)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    // =========================================================================
    // TABLAS SPRINT 11-12: ACUERDOS ECONÓMICOS
    // =========================================================================

    /**
     * Crear tabla wp_ga_empresas
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Catálogo de empresas propias de la organización. Cada empresa puede
     * tener su propia configuración fiscal y ser la entidad pagadora en
     * órdenes de trabajo.
     *
     * Ejemplos: "Wolk CR", "Wolk USA", "Wolk CO"
     *
     * =========================================================================
     * USO PRINCIPAL:
     * =========================================================================
     * - Seleccionar qué empresa paga en cada orden de trabajo
     * - Definir entidad legal para contratos
     * - Configuración fiscal por empresa/país
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_empresas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_empresas';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * IDENTIFICACIÓN
             * ───────────────────────────────────────────────────────────────── */
            codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Código corto: WOLK-CR, WOLK-US',
            nombre VARCHAR(200) NOT NULL COMMENT 'Nombre comercial: Wolk Costa Rica',
            razon_social VARCHAR(200) NOT NULL COMMENT 'Razón social legal completa',

            /* ─────────────────────────────────────────────────────────────────
             * DATOS FISCALES
             * ───────────────────────────────────────────────────────────────── */
            identificacion_tipo VARCHAR(20) COMMENT 'Tipo: NIT, EIN, RUT, Cédula Jurídica',
            identificacion_fiscal VARCHAR(50) NOT NULL COMMENT 'Número de identificación fiscal',
            pais_id INT COMMENT 'FK wp_ga_paises_config - País de la empresa',
            pais_iso VARCHAR(2) NOT NULL COMMENT 'Código ISO del país',

            /* ─────────────────────────────────────────────────────────────────
             * CONTACTO Y UBICACIÓN
             * ───────────────────────────────────────────────────────────────── */
            direccion TEXT COMMENT 'Dirección fiscal completa',
            ciudad VARCHAR(100) COMMENT 'Ciudad',
            codigo_postal VARCHAR(20) COMMENT 'Código postal',
            telefono VARCHAR(50) COMMENT 'Teléfono principal',
            email VARCHAR(100) COMMENT 'Email corporativo',
            sitio_web VARCHAR(200) COMMENT 'URL del sitio web',

            /* ─────────────────────────────────────────────────────────────────
             * BRANDING
             * ───────────────────────────────────────────────────────────────── */
            logo_url VARCHAR(500) COMMENT 'URL del logo para documentos',
            color_primario VARCHAR(7) DEFAULT '#0073aa' COMMENT 'Color hex para documentos',

            /* ─────────────────────────────────────────────────────────────────
             * CONFIGURACIÓN DE FACTURACIÓN
             * ───────────────────────────────────────────────────────────────── */
            prefijo_factura VARCHAR(10) DEFAULT 'FAC' COMMENT 'Prefijo para facturas',
            consecutivo_factura INT DEFAULT 0 COMMENT 'Último consecutivo usado',
            pie_factura TEXT COMMENT 'Texto pie de página en facturas',

            /* ─────────────────────────────────────────────────────────────────
             * DATOS BANCARIOS PARA RECIBIR PAGOS
             * ───────────────────────────────────────────────────────────────── */
            datos_bancarios JSON COMMENT 'Array de cuentas bancarias',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO Y AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            es_principal TINYINT(1) DEFAULT 0 COMMENT '1=Empresa principal/default',
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_codigo (codigo),
            INDEX idx_pais (pais_iso),
            INDEX idx_activo (activo),
            INDEX idx_principal (es_principal)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_catalogo_bonos
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Catálogo predefinido de bonos que se pueden ofrecer en órdenes de trabajo.
     * Permite estandarizar los incentivos disponibles.
     *
     * Ejemplos:
     * - Cámara encendida en reuniones: $50/mes
     * - Puntualidad en daily: $25/mes
     * - Superar 150 horas mensuales: $100
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_catalogo_bonos_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_catalogo_bonos';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * IDENTIFICACIÓN DEL BONO
             * ───────────────────────────────────────────────────────────────── */
            codigo VARCHAR(30) NOT NULL UNIQUE COMMENT 'Código corto: BONO-CAM, BONO-PUNT',
            nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del bono: Cámara en reuniones',
            descripcion TEXT COMMENT 'Descripción detallada del bono y condiciones',

            /* ─────────────────────────────────────────────────────────────────
             * TIPO DE VALOR
             * - FIJO: Monto fijo en USD (ej: $50)
             * - PORCENTAJE: Porcentaje de algo (ej: 5% de horas)
             * ───────────────────────────────────────────────────────────────── */
            tipo_valor ENUM('FIJO', 'PORCENTAJE') DEFAULT 'FIJO',
            valor_default DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor sugerido por defecto',

            /* ─────────────────────────────────────────────────────────────────
             * FRECUENCIA DE PAGO
             * ───────────────────────────────────────────────────────────────── */
            frecuencia ENUM('UNICO', 'SEMANAL', 'QUINCENAL', 'MENSUAL') DEFAULT 'MENSUAL',

            /* ─────────────────────────────────────────────────────────────────
             * CONDICIONES (texto descriptivo)
             * ───────────────────────────────────────────────────────────────── */
            condicion_descripcion TEXT COMMENT 'Ej: Mantener cámara encendida en todas las reuniones',

            /* ─────────────────────────────────────────────────────────────────
             * CATEGORÍA DEL BONO
             * ───────────────────────────────────────────────────────────────── */
            categoria ENUM('PRODUCTIVIDAD', 'ASISTENCIA', 'CALIDAD', 'COMUNICACION', 'METAS', 'OTRO') DEFAULT 'OTRO',

            /* ─────────────────────────────────────────────────────────────────
             * ÍCONO PARA MOSTRAR EN UI
             * ───────────────────────────────────────────────────────────────── */
            icono VARCHAR(50) DEFAULT 'dashicons-awards' COMMENT 'Clase dashicons o FontAwesome',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO Y AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            activo TINYINT(1) DEFAULT 1,
            orden INT DEFAULT 0 COMMENT 'Orden de aparición en listados',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_codigo (codigo),
            INDEX idx_categoria (categoria),
            INDEX idx_activo (activo),
            INDEX idx_orden (orden)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla wp_ga_ordenes_acuerdos
     *
     * =========================================================================
     * PROPÓSITO:
     * =========================================================================
     * Almacena los acuerdos económicos específicos de cada orden de trabajo.
     * Define cómo se compensará al aplicante contratado.
     *
     * =========================================================================
     * TIPOS DE ACUERDO:
     * =========================================================================
     * - HORA_REPORTADA: Pago por cada hora que reporte (sin necesidad de aprobación)
     * - HORA_APROBADA: Pago solo por horas aprobadas por supervisor
     * - TRABAJO_COMPLETADO: Pago fijo al completar el trabajo
     * - COMISION_FACTURA: Porcentaje de facturas pagadas del proyecto
     * - COMISION_HORAS_SUPERVISADAS: % de las horas que supervise
     * - META_RENTABILIDAD: Bono si la rentabilidad supera X%
     * - BONO: Bono del catálogo (referencia a wp_ga_catalogo_bonos)
     *
     * =========================================================================
     * EJEMPLO DE USO:
     * =========================================================================
     * Una orden puede tener múltiples acuerdos:
     * 1. $15/hora reportada (principal)
     * 2. 5% comisión de facturas pagadas
     * 3. Bono $50 por cámara en reuniones
     * 4. Bono $100 si supera 150 horas
     *
     * @param object $wpdb Instancia global de WordPress Database
     * @param string $charset_collate Charset y collation de la BD
     */
    private static function create_ordenes_acuerdos_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_ordenes_acuerdos';

        $sql = "CREATE TABLE {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * RELACIÓN CON ORDEN DE TRABAJO
             * ───────────────────────────────────────────────────────────────── */
            orden_id INT NOT NULL COMMENT 'FK wp_ga_ordenes_trabajo',

            /* ─────────────────────────────────────────────────────────────────
             * TIPO DE ACUERDO
             * ───────────────────────────────────────────────────────────────── */
            tipo_acuerdo ENUM(
                'HORA_REPORTADA',           /* Pago por hora reportada */
                'HORA_APROBADA',            /* Pago por hora aprobada */
                'TRABAJO_COMPLETADO',       /* Pago fijo al completar */
                'COMISION_FACTURA',         /* % de facturas pagadas */
                'COMISION_HORAS_SUPERVISADAS', /* % de horas supervisadas */
                'META_RENTABILIDAD',        /* Bono por rentabilidad */
                'BONO'                      /* Bono del catálogo */
            ) NOT NULL,

            /* ─────────────────────────────────────────────────────────────────
             * VALOR DEL ACUERDO
             * - Si es_porcentaje=0: valor es monto fijo en USD
             * - Si es_porcentaje=1: valor es porcentaje (ej: 5.00 = 5%)
             * ───────────────────────────────────────────────────────────────── */
            valor DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto o porcentaje',
            es_porcentaje TINYINT(1) DEFAULT 0 COMMENT '1=El valor es un porcentaje',

            /* ─────────────────────────────────────────────────────────────────
             * REFERENCIA A BONO DEL CATÁLOGO (solo si tipo='BONO')
             * ───────────────────────────────────────────────────────────────── */
            bono_id INT COMMENT 'FK wp_ga_catalogo_bonos (solo si tipo=BONO)',

            /* ─────────────────────────────────────────────────────────────────
             * CONDICIÓN PARA APLICAR EL ACUERDO
             * Para bonos condicionales o metas
             * ───────────────────────────────────────────────────────────────── */
            condicion VARCHAR(255) COMMENT 'Ej: rentabilidad > 50%, horas > 150',
            condicion_valor DECIMAL(10,2) COMMENT 'Valor numérico de la condición',

            /* ─────────────────────────────────────────────────────────────────
             * DESCRIPCIÓN Y NOTAS
             * ───────────────────────────────────────────────────────────────── */
            descripcion TEXT COMMENT 'Descripción adicional del acuerdo',
            notas_internas TEXT COMMENT 'Notas solo para admin',

            /* ─────────────────────────────────────────────────────────────────
             * FRECUENCIA DE PAGO (para algunos tipos)
             * ───────────────────────────────────────────────────────────────── */
            frecuencia_pago ENUM('POR_EVENTO', 'SEMANAL', 'QUINCENAL', 'MENSUAL', 'AL_FINALIZAR') DEFAULT 'MENSUAL',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO Y ORDEN
             * ───────────────────────────────────────────────────────────────── */
            activo TINYINT(1) DEFAULT 1 COMMENT '1=Acuerdo activo',
            orden INT DEFAULT 0 COMMENT 'Orden de aparición',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_by BIGINT UNSIGNED COMMENT 'Quién creó el acuerdo',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_orden (orden_id),
            INDEX idx_tipo (tipo_acuerdo),
            INDEX idx_bono (bono_id),
            INDEX idx_activo (activo)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    // =========================================================================
    // SPRINT 11-12 PARTE B: EJECUCIÓN DE COMISIONES
    // =========================================================================

    /**
     * Crear tabla de comisiones generadas
     *
     * Almacena comisiones calculadas automáticamente cuando una factura
     * se marca como pagada. El trigger está en class-ga-facturas.php
     *
     * @param wpdb   $wpdb
     * @param string $charset_collate
     */
    private static function create_comisiones_generadas_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_comisiones_generadas';

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * REFERENCIAS AL ORIGEN
             * ───────────────────────────────────────────────────────────────── */
            orden_id BIGINT UNSIGNED NOT NULL COMMENT 'FK wp_ga_ordenes_trabajo',
            acuerdo_id BIGINT UNSIGNED NOT NULL COMMENT 'FK wp_ga_ordenes_acuerdos',
            aplicante_id BIGINT UNSIGNED NOT NULL COMMENT 'FK wp_ga_aplicantes (quien recibe)',

            /* ─────────────────────────────────────────────────────────────────
             * ORIGEN DEL PAGO (qué factura/documento generó esta comisión)
             * ───────────────────────────────────────────────────────────────── */
            pago_origen_id BIGINT UNSIGNED COMMENT 'ID de la factura o documento origen',
            tipo_origen ENUM('FACTURA', 'PAGO_MANUAL', 'OTRO') DEFAULT 'FACTURA',

            /* ─────────────────────────────────────────────────────────────────
             * CÁLCULO DE LA COMISIÓN
             * ───────────────────────────────────────────────────────────────── */
            monto_base DECIMAL(12,2) NOT NULL COMMENT 'Monto sobre el cual se calculó',
            porcentaje_aplicado DECIMAL(5,2) DEFAULT NULL COMMENT 'Si fue por porcentaje',
            monto_fijo_aplicado DECIMAL(12,2) DEFAULT NULL COMMENT 'Si fue monto fijo',
            monto_comision DECIMAL(12,2) NOT NULL COMMENT 'Monto final de la comisión',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO DE LA COMISIÓN
             * ───────────────────────────────────────────────────────────────── */
            estado ENUM('DISPONIBLE', 'SOLICITADA', 'PAGADA', 'CANCELADA') DEFAULT 'DISPONIBLE',
            solicitud_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK wp_ga_solicitudes_cobro',

            /* ─────────────────────────────────────────────────────────────────
             * NOTAS Y AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            notas TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_orden (orden_id),
            INDEX idx_acuerdo (acuerdo_id),
            INDEX idx_aplicante (aplicante_id),
            INDEX idx_estado (estado),
            INDEX idx_solicitud (solicitud_id),
            INDEX idx_origen (tipo_origen, pago_origen_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla de solicitudes de cobro
     *
     * Cuando un proveedor quiere cobrar sus comisiones disponibles,
     * crea una solicitud que debe ser aprobada por finanzas.
     *
     * @param wpdb   $wpdb
     * @param string $charset_collate
     */
    private static function create_solicitudes_cobro_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_solicitudes_cobro';

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * IDENTIFICACIÓN
             * ───────────────────────────────────────────────────────────────── */
            numero_solicitud VARCHAR(20) NOT NULL UNIQUE COMMENT 'SOL-YYYY-NNNN',
            aplicante_id BIGINT UNSIGNED NOT NULL COMMENT 'FK wp_ga_aplicantes',

            /* ─────────────────────────────────────────────────────────────────
             * MONTOS
             * ───────────────────────────────────────────────────────────────── */
            monto_disponible DECIMAL(12,2) NOT NULL COMMENT 'Total disponible al momento',
            monto_solicitado DECIMAL(12,2) NOT NULL COMMENT 'Cuánto solicita el proveedor',

            /* ─────────────────────────────────────────────────────────────────
             * MÉTODO DE PAGO
             * ───────────────────────────────────────────────────────────────── */
            metodo_pago ENUM('BINANCE', 'WISE', 'PAYPAL', 'TRANSFERENCIA_LOCAL', 'OTRO') NOT NULL,
            datos_pago JSON COMMENT 'Datos según método: wallet, email, cuenta, etc.',
            moneda VARCHAR(3) DEFAULT 'USD',

            /* ─────────────────────────────────────────────────────────────────
             * NOTAS DEL SOLICITANTE
             * ───────────────────────────────────────────────────────────────── */
            notas_solicitante TEXT COMMENT 'Mensaje del proveedor',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO Y REVISIÓN
             * ───────────────────────────────────────────────────────────────── */
            estado ENUM('PENDIENTE', 'EN_REVISION', 'APROBADA', 'RECHAZADA', 'PAGADA', 'CANCELADA') DEFAULT 'PENDIENTE',
            revisado_por BIGINT UNSIGNED COMMENT 'WP User ID de quien revisó',
            notas_revision TEXT COMMENT 'Notas del revisor',
            fecha_revision DATETIME,

            /* ─────────────────────────────────────────────────────────────────
             * PAGO
             * ───────────────────────────────────────────────────────────────── */
            fecha_pago DATETIME,
            comprobante_pago VARCHAR(500) COMMENT 'URL o referencia del comprobante',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_aplicante (aplicante_id),
            INDEX idx_estado (estado),
            INDEX idx_fecha (created_at),
            INDEX idx_metodo (metodo_pago)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Crear tabla de detalle de solicitudes de cobro
     *
     * Cada fila representa una comisión incluida en una solicitud.
     * Permite ajustes de porcentaje si el proveedor negocia.
     *
     * @param wpdb   $wpdb
     * @param string $charset_collate
     */
    private static function create_solicitudes_cobro_detalle_table($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'ga_solicitudes_cobro_detalle';

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

            /* ─────────────────────────────────────────────────────────────────
             * REFERENCIAS
             * ───────────────────────────────────────────────────────────────── */
            solicitud_id BIGINT UNSIGNED NOT NULL COMMENT 'FK wp_ga_solicitudes_cobro',
            comision_id BIGINT UNSIGNED NOT NULL COMMENT 'FK wp_ga_comisiones_generadas',

            /* ─────────────────────────────────────────────────────────────────
             * MONTOS ORIGINALES (snapshot al momento de incluir)
             * ───────────────────────────────────────────────────────────────── */
            monto_original DECIMAL(12,2) NOT NULL COMMENT 'Monto de la comisión original',
            porcentaje_original DECIMAL(5,2) COMMENT 'Porcentaje original si aplicaba',

            /* ─────────────────────────────────────────────────────────────────
             * AJUSTES (si el proveedor acepta menos)
             * ───────────────────────────────────────────────────────────────── */
            tipo_ajuste ENUM('NINGUNO', 'PORCENTAJE_REDUCIDO', 'MONTO_FIJO') DEFAULT 'NINGUNO',
            porcentaje_solicitado DECIMAL(5,2) COMMENT 'Nuevo % si hay ajuste',
            monto_solicitado DECIMAL(12,2) NOT NULL COMMENT 'Monto final solicitado',
            motivo_ajuste TEXT COMMENT 'Por qué se ajustó',

            /* ─────────────────────────────────────────────────────────────────
             * ESTADO
             * ───────────────────────────────────────────────────────────────── */
            incluida TINYINT(1) DEFAULT 1 COMMENT '1=incluida en solicitud',

            /* ─────────────────────────────────────────────────────────────────
             * AUDITORÍA
             * ───────────────────────────────────────────────────────────────── */
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

            /* ─────────────────────────────────────────────────────────────────
             * ÍNDICES
             * ───────────────────────────────────────────────────────────────── */
            INDEX idx_solicitud (solicitud_id),
            INDEX idx_comision (comision_id),
            UNIQUE KEY unique_solicitud_comision (solicitud_id, comision_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }
}
