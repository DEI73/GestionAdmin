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

        // Insertar datos iniciales
        self::insert_initial_data($wpdb);

        // Guardar versión del plugin
        add_option('ga_version', GA_VERSION);
        update_option('ga_db_version', '1.2.0'); // Sprint 5-6: Clientes y Proyectos

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
}
