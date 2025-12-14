# Diccionario de Datos - GestionAdmin Wolk

> **Versión:** 1.6.1
> **Actualizado:** Diciembre 2024
> **Base de datos:** MySQL/MariaDB con prefijo `wp_ga_`

---

## Resumen de Tablas

| # | Tabla | Descripción | Sprint |
|---|-------|-------------|--------|
| 1 | `wp_ga_departamentos` | Departamentos de la empresa | 1-2 |
| 2 | `wp_ga_puestos` | Puestos de trabajo por departamento | 1-2 |
| 3 | `wp_ga_puestos_escalas` | Escalas salariales por antigüedad | 1-2 |
| 4 | `wp_ga_usuarios` | Empleados y datos laborales | 1-2 |
| 5 | `wp_ga_supervisiones` | Relaciones de supervisión | 1-2 |
| 6 | `wp_ga_paises_config` | Configuración fiscal por país | 1-2 |
| 7 | `wp_ga_catalogo_tareas` | Catálogo de tareas predefinidas | 3-4 |
| 8 | `wp_ga_tareas` | Tareas asignadas a usuarios | 3-4 |
| 9 | `wp_ga_subtareas` | Subtareas de cada tarea | 3-4 |
| 10 | `wp_ga_registro_horas` | Timer y registro de tiempo | 3-4 |
| 11 | `wp_ga_pausas_timer` | Pausas durante tracking | 3-4 |
| 12 | `wp_ga_clientes` | Clientes de la empresa | 5-6 |
| 13 | `wp_ga_casos` | Casos/expedientes de clientes | 5-6 |
| 14 | `wp_ga_proyectos` | Proyectos dentro de casos | 5-6 |
| 15 | `wp_ga_aplicantes` | Freelancers y empresas externas | 7-8 |
| 16 | `wp_ga_ordenes_trabajo` | Ofertas de trabajo (marketplace) | 7-8 |
| 17 | `wp_ga_aplicaciones_orden` | Aplicaciones a órdenes | 7-8 |
| 18 | `wp_ga_facturas` | Facturas emitidas | 9-10 |
| 19 | `wp_ga_facturas_detalle` | Líneas de factura | 9-10 |
| 20 | `wp_ga_cotizaciones` | Cotizaciones a clientes | 9-10 |
| 21 | `wp_ga_cotizaciones_detalle` | Líneas de cotización | 9-10 |
| 22 | `wp_ga_empresas` | Empresas propias (multi-entidad) | 11-12 |
| 23 | `wp_ga_catalogo_bonos` | Catálogo de bonos disponibles | 11-12 |
| 24 | `wp_ga_ordenes_acuerdos` | Acuerdos económicos por orden | 11-12 |
| 25 | `wp_ga_ordenes_bonos` | Bonos asignados a órdenes | 11-12 |
| 26 | `wp_ga_comisiones_generadas` | Comisiones calculadas | 11-12 |
| 27 | `wp_ga_solicitudes_cobro` | Solicitudes de pago | 11-12 |
| 28 | `wp_ga_solicitudes_cobro_detalle` | Detalle de solicitudes | 11-12 |
| 29 | `wp_ga_metodos_pago` | Métodos de pago configurados | 13-14 |

---

## Sprint 1-2: Fundamentos

### 1. wp_ga_departamentos

**Propósito:** Almacena los departamentos de la organización.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `codigo` | VARCHAR(20) | NO | - | Código único (ej: DEP-001) |
| `nombre` | VARCHAR(100) | NO | - | Nombre del departamento |
| `descripcion` | TEXT | SI | NULL | Descripción detallada |
| `tipo` | ENUM | SI | 'PROYECTOS' | Valores: OPERACION_FIJA, PROYECTOS, SOPORTE, COMERCIAL |
| `jefe_id` | BIGINT UNSIGNED | SI | NULL | FK → wp_users (jefe del depto) |
| `activo` | TINYINT(1) | SI | 1 | 1=Activo, 0=Inactivo |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | Fecha creación |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- `PRIMARY KEY (id)`
- `idx_codigo (codigo)` - UNIQUE
- `idx_activo (activo)`
- `idx_jefe (jefe_id)`

---

### 2. wp_ga_puestos

**Propósito:** Define los puestos de trabajo dentro de cada departamento.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `departamento_id` | INT | NO | - | FK → wp_ga_departamentos |
| `codigo` | VARCHAR(20) | NO | - | Código único (ej: PUE-DEV-001) |
| `nombre` | VARCHAR(100) | NO | - | Nombre del puesto |
| `descripcion` | TEXT | SI | NULL | Descripción y responsabilidades |
| `nivel_jerarquico` | INT | SI | 4 | 1=Socio, 2=Director, 3=Jefe, 4=Empleado |
| `reporta_a_puesto_id` | INT | SI | NULL | FK → wp_ga_puestos (puesto superior) |
| `capacidad_horas_semana` | INT | SI | 40 | Horas semanales esperadas |
| `requiere_qa` | TINYINT(1) | SI | 0 | 1=Las tareas pasan por QA |
| `flujo_revision_default` | ENUM | SI | 'SOLO_JEFE' | SOLO_JEFE, QA_JEFE, QA_JEFE_DIRECTOR |
| `activo` | TINYINT(1) | SI | 1 | Estado del puesto |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Índices:**
- `PRIMARY KEY (id)`
- `idx_departamento (departamento_id)`
- `idx_nivel (nivel_jerarquico)`
- `idx_codigo (codigo)` - UNIQUE
- `idx_activo (activo)`

---

### 3. wp_ga_puestos_escalas

**Propósito:** Define las tarifas por hora según años de antigüedad en cada puesto.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `puesto_id` | INT | NO | - | FK → wp_ga_puestos |
| `anio_antiguedad` | INT | NO | - | Año de antigüedad (1, 2, 3, 4, 5+) |
| `tarifa_hora` | DECIMAL(10,2) | NO | - | Tarifa por hora en USD |
| `incremento_porcentaje` | DECIMAL(5,2) | SI | 0 | Incremento % sobre año anterior |
| `requiere_aprobacion_jefe` | TINYINT(1) | SI | 1 | Tareas requieren aprobación jefe |
| `requiere_aprobacion_director` | TINYINT(1) | SI | 0 | Requiere también director |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Índices:**
- `PRIMARY KEY (id)`
- `uk_puesto_anio (puesto_id, anio_antiguedad)` - UNIQUE
- `idx_puesto (puesto_id)`
- `idx_activo (activo)`

---

### 4. wp_ga_usuarios

**Propósito:** Extiende wp_users con datos laborales y de pago.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `usuario_wp_id` | BIGINT UNSIGNED | NO | - | FK → wp_users (UNIQUE) |
| `puesto_id` | INT | SI | NULL | FK → wp_ga_puestos |
| `departamento_id` | INT | SI | NULL | FK → wp_ga_departamentos |
| `codigo_empleado` | VARCHAR(20) | SI | NULL | Código único (ej: EMP-001) |
| `fecha_ingreso` | DATE | SI | NULL | Fecha de inicio laboral |
| `nivel_jerarquico` | INT | SI | 4 | Nivel heredado del puesto |
| `es_jefe_de_jefes` | TINYINT(1) | SI | 0 | Supervisa otros jefes |
| `puede_ver_departamentos` | JSON | SI | NULL | Array de IDs de deptos visibles |
| `metodo_pago_preferido` | ENUM | SI | 'TRANSFERENCIA' | BINANCE, WISE, PAYPAL, PAYONEER, STRIPE, TRANSFERENCIA, EFECTIVO |
| `datos_pago_binance` | JSON | SI | NULL | Datos Binance Pay |
| `datos_pago_wise` | JSON | SI | NULL | Datos Wise |
| `datos_pago_paypal` | JSON | SI | NULL | Datos PayPal |
| `datos_pago_stripe` | JSON | SI | NULL | Datos Stripe |
| `datos_pago_banco` | JSON | SI | NULL | Datos bancarios |
| `pais_residencia` | VARCHAR(2) | SI | NULL | Código ISO país |
| `identificacion_fiscal` | VARCHAR(50) | SI | NULL | NIT, RFC, EIN, etc. |
| `activo` | TINYINT(1) | SI | 1 | - |
| `fecha_baja` | DATE | SI | NULL | Si fue dado de baja |
| `motivo_baja` | TEXT | SI | NULL | Razón de salida |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Índices:**
- `PRIMARY KEY (id)`
- `idx_usuario_wp (usuario_wp_id)` - UNIQUE
- `idx_puesto (puesto_id)`
- `idx_departamento (departamento_id)`
- `idx_codigo (codigo_empleado)` - UNIQUE
- `idx_activo (activo)`

---

### 5. wp_ga_supervisiones

**Propósito:** Define relaciones de supervisión entre usuarios.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `supervisor_id` | BIGINT UNSIGNED | NO | - | FK → wp_users (quien supervisa) |
| `supervisado_id` | BIGINT UNSIGNED | NO | - | FK → wp_users (quien es supervisado) |
| `tipo_supervision` | ENUM | SI | 'DIRECTA' | DIRECTA, PROYECTO, DEPARTAMENTO |
| `proyecto_id` | INT | SI | NULL | FK → wp_ga_proyectos (si es por proyecto) |
| `departamento_id` | INT | SI | NULL | FK → wp_ga_departamentos |
| `fecha_inicio` | DATE | NO | - | Inicio de la supervisión |
| `fecha_fin` | DATE | SI | NULL | Fin (NULL=vigente) |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_by` | BIGINT UNSIGNED | SI | NULL | Quién creó |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Índices:**
- `PRIMARY KEY (id)`
- `idx_supervisor (supervisor_id)`
- `idx_supervisado (supervisado_id)`
- `idx_tipo (tipo_supervision)`
- `idx_activo (activo)`

---

### 6. wp_ga_paises_config

**Propósito:** Configuración fiscal y de facturación por país.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `codigo_iso` | VARCHAR(2) | NO | - | Código ISO 2 letras (CO, US, MX, CR) |
| `nombre` | VARCHAR(100) | NO | - | Nombre del país |
| `moneda_codigo` | VARCHAR(3) | NO | - | Código ISO moneda (USD, COP, MXN) |
| `moneda_simbolo` | VARCHAR(5) | SI | NULL | Símbolo ($, ₡, etc.) |
| `impuesto_nombre` | VARCHAR(50) | SI | NULL | Nombre del impuesto (IVA, Sales Tax) |
| `impuesto_porcentaje` | DECIMAL(5,2) | SI | 0 | Porcentaje de impuesto |
| `retencion_default` | DECIMAL(5,2) | SI | 0 | Retención por defecto |
| `formato_factura` | VARCHAR(20) | SI | NULL | Formato numeración (FAC-CO-{YYYY}-{NNNN}) |
| `requiere_electronica` | TINYINT(1) | SI | 0 | 1=Requiere facturación electrónica |
| `proveedor_electronica` | VARCHAR(50) | SI | NULL | DIAN, SAT, Ministerio Hacienda |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Datos Iniciales:**

| País | Código | Moneda | Impuesto | Factura Electrónica |
|------|--------|--------|----------|---------------------|
| Estados Unidos | US | USD | 0% | No |
| Colombia | CO | COP | IVA 19% | Sí (DIAN) |
| México | MX | MXN | IVA 16% | Sí (SAT) |
| Costa Rica | CR | CRC | IVA 13% | Sí (Ministerio Hacienda) |

---

## Sprint 3-4: Core Operativo

### 7. wp_ga_catalogo_tareas

**Propósito:** Plantillas de tareas reutilizables.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `codigo` | VARCHAR(20) | NO | - | Código único |
| `nombre` | VARCHAR(200) | NO | - | Nombre de la tarea |
| `descripcion` | TEXT | SI | NULL | Descripción detallada |
| `departamento_id` | INT | SI | NULL | FK departamento |
| `puesto_id` | INT | SI | NULL | FK puesto sugerido |
| `horas_estimadas` | DECIMAL(10,2) | SI | NULL | Horas típicas |
| `frecuencia` | ENUM | SI | 'POR_SOLICITUD' | POR_SOLICITUD, DIARIA, SEMANAL, QUINCENAL, MENSUAL, TRIMESTRAL, SEMESTRAL |
| `frecuencia_dias` | INT | SI | NULL | Días entre repeticiones |
| `url_instrucciones` | VARCHAR(500) | SI | NULL | Link a manual |
| `instrucciones_texto` | TEXT | SI | NULL | Instrucciones inline |
| `flujo_revision` | ENUM | SI | 'DEFAULT_PUESTO' | DEFAULT_PUESTO, PERSONALIZADO |
| `revisor_tipo` | ENUM | SI | NULL | NINGUNO, QA_DEPARTAMENTO, USUARIO_ESPECIFICO, PAR |
| `revisor_usuario_id` | BIGINT UNSIGNED | SI | NULL | Usuario específico |
| `aprobador_tipo` | ENUM | SI | NULL | JEFE_DIRECTO, JEFE_DEPARTAMENTO, USUARIO_ESPECIFICO, AUTO |
| `aprobador_usuario_id` | BIGINT UNSIGNED | SI | NULL | - |
| `requiere_segundo_aprobador` | TINYINT(1) | SI | 0 | - |
| `segundo_aprobador_nivel` | INT | SI | NULL | Nivel jerárquico |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 8. wp_ga_tareas

**Propósito:** Tareas asignadas a usuarios con tracking de tiempo.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `numero` | VARCHAR(20) | NO | - | Número único (TAR-YYYY-NNNN) |
| `catalogo_tarea_id` | INT | SI | NULL | FK catálogo (si viene de plantilla) |
| `nombre` | VARCHAR(200) | NO | - | Nombre de la tarea |
| `descripcion` | TEXT | SI | NULL | Descripción detallada |
| `proyecto_id` | INT | SI | NULL | FK proyecto |
| `caso_id` | INT | SI | NULL | FK caso |
| `asignado_a` | BIGINT UNSIGNED | NO | - | FK wp_users (responsable) |
| `contrato_trabajo_id` | INT | SI | NULL | FK contrato |
| `supervisor_id` | BIGINT UNSIGNED | SI | NULL | FK wp_users |
| `aprobador_id` | BIGINT UNSIGNED | SI | NULL | FK wp_users |
| `minutos_estimados` | INT | SI | 60 | Tiempo estimado en MINUTOS |
| `minutos_reales` | INT | SI | 0 | Tiempo real en MINUTOS |
| `fecha_inicio` | DATE | SI | NULL | Fecha inicio planificada |
| `fecha_limite` | DATE | SI | NULL | Fecha límite |
| `fecha_completada` | DATETIME | SI | NULL | Cuándo se completó |
| `estado` | ENUM | SI | 'PENDIENTE' | Ver estados abajo |
| `prioridad` | ENUM | SI | 'MEDIA' | BAJA, MEDIA, ALTA, URGENTE |
| `url_instrucciones` | VARCHAR(500) | SI | NULL | Link a instrucciones |
| `instrucciones_texto` | TEXT | SI | NULL | Instrucciones |
| `porcentaje_avance` | INT | SI | 0 | 0-100 |
| `timedoctor_task_id` | VARCHAR(50) | SI | NULL | ID en TimeDoctor |
| `created_by` | BIGINT UNSIGNED | SI | NULL | Quién creó |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Estados de Tarea:**
```
PENDIENTE → EN_PROGRESO → PAUSADA
                ↓
         COMPLETADA → EN_QA → APROBADA_QA → EN_REVISION → APROBADA → PAGADA
                              ↓                            ↓
                          RECHAZADA                    RECHAZADA
```

---

### 9. wp_ga_subtareas

**Propósito:** División de tareas en pasos más pequeños.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `tarea_id` | INT | NO | - | FK tarea padre |
| `codigo` | VARCHAR(20) | SI | NULL | Código opcional |
| `nombre` | VARCHAR(200) | NO | - | Nombre subtarea |
| `descripcion` | TEXT | SI | NULL | Descripción/instrucciones |
| `orden` | INT | SI | 0 | Orden de ejecución |
| `minutos_estimados` | INT | SI | 15 | Tiempo estimado en MINUTOS |
| `minutos_reales` | INT | SI | 0 | Tiempo real en MINUTOS |
| `estado` | ENUM | SI | 'PENDIENTE' | PENDIENTE, EN_PROGRESO, COMPLETADA |
| `fecha_inicio` | DATETIME | SI | NULL | Cuándo inició |
| `fecha_fin` | DATETIME | SI | NULL | Cuándo terminó |
| `notas` | TEXT | SI | NULL | Notas adicionales |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 10. wp_ga_registro_horas

**Propósito:** Registro de tiempo trabajado (timer).

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `usuario_id` | BIGINT UNSIGNED | NO | - | FK wp_users |
| `tarea_id` | INT | NO | - | FK tarea |
| `subtarea_id` | INT | SI | NULL | FK subtarea |
| `proyecto_id` | INT | SI | NULL | FK proyecto |
| `contrato_trabajo_id` | INT | SI | NULL | FK contrato |
| `fecha` | DATE | NO | - | Fecha del registro |
| `hora_inicio` | DATETIME | NO | - | Cuándo inició |
| `hora_fin` | DATETIME | SI | NULL | Cuándo terminó |
| `minutos_totales` | INT | SI | 0 | Minutos totales |
| `minutos_pausas` | INT | SI | 0 | Minutos en pausa |
| `minutos_efectivos` | INT | SI | 0 | Minutos trabajados |
| `descripcion` | TEXT | SI | NULL | Qué se hizo |
| `estado` | ENUM | SI | 'ACTIVO' | Ver estados |
| `aprobado_qa_por` | BIGINT UNSIGNED | SI | NULL | QA que aprobó |
| `fecha_aprobacion_qa` | DATETIME | SI | NULL | - |
| `aprobado_por` | BIGINT UNSIGNED | SI | NULL | Jefe que aprobó |
| `fecha_aprobacion` | DATETIME | SI | NULL | - |
| `motivo_rechazo` | TEXT | SI | NULL | Si fue rechazado |
| `tarifa_hora` | DECIMAL(10,2) | SI | NULL | Tarifa aplicada |
| `monto_calculado` | DECIMAL(12,2) | SI | NULL | Monto a pagar |
| `incluido_en_cobro_id` | INT | SI | NULL | FK solicitud cobro |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Estados:**
- `ACTIVO`: Timer corriendo
- `BORRADOR`: Pendiente de enviar
- `ENVIADO`: Enviado para revisión
- `EN_QA`: En control de calidad
- `APROBADO_QA`: QA aprobó
- `APROBADO`: Jefe aprobó
- `RECHAZADO`: Rechazado
- `PAGADO`: Ya se pagó

---

### 11. wp_ga_pausas_timer

**Propósito:** Registra pausas durante el tracking de tiempo.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `registro_hora_id` | INT | NO | - | FK registro_horas |
| `hora_pausa` | DATETIME | NO | - | Cuándo pausó |
| `hora_reanudacion` | DATETIME | SI | NULL | Cuándo reanudó |
| `minutos` | INT | SI | 0 | Duración de pausa |
| `motivo` | ENUM | SI | 'OTRO' | ALMUERZO, REUNION, EMERGENCIA, DESCANSO, OTRO |
| `nota` | VARCHAR(200) | SI | NULL | Nota opcional |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

## Sprint 5-6: Clientes y Proyectos

### 12. wp_ga_clientes

**Propósito:** Clientes de la empresa con datos fiscales y de contacto.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `usuario_wp_id` | BIGINT UNSIGNED | SI | NULL | FK wp_users (para portal cliente) |
| `codigo` | VARCHAR(20) | NO | - | Código único (CLI-001) |
| `tipo` | ENUM | SI | 'EMPRESA' | PERSONA_NATURAL, EMPRESA |
| `nombre_comercial` | VARCHAR(200) | NO | - | Nombre comercial |
| `razon_social` | VARCHAR(200) | SI | NULL | Razón social legal |
| `documento_tipo` | VARCHAR(20) | SI | NULL | NIT, CC, RFC, EIN |
| `documento_numero` | VARCHAR(50) | SI | NULL | Número documento |
| `email` | VARCHAR(200) | SI | NULL | Email facturación |
| `telefono` | VARCHAR(50) | SI | NULL | Teléfono |
| `pais` | VARCHAR(2) | SI | NULL | Código ISO |
| `metodo_pago_id` | BIGINT UNSIGNED | SI | NULL | FK métodos_pago |
| `ciudad` | VARCHAR(100) | SI | NULL | Ciudad |
| `direccion` | TEXT | SI | NULL | Dirección fiscal |
| `url_logo` | VARCHAR(500) | SI | NULL | Logo del cliente |
| `regimen_fiscal` | VARCHAR(50) | SI | NULL | Régimen tributario |
| `retencion_default` | DECIMAL(5,2) | SI | 0 | Retención por defecto |
| `contacto_nombre` | VARCHAR(200) | SI | NULL | Nombre contacto |
| `contacto_cargo` | VARCHAR(100) | SI | NULL | Cargo contacto |
| `contacto_email` | VARCHAR(200) | SI | NULL | Email contacto |
| `contacto_telefono` | VARCHAR(50) | SI | NULL | Teléfono contacto |
| `stripe_customer_id` | VARCHAR(50) | SI | NULL | ID Stripe |
| `paypal_email` | VARCHAR(200) | SI | NULL | Email PayPal |
| `metodo_pago_preferido` | ENUM | SI | 'TRANSFERENCIA' | TRANSFERENCIA, STRIPE, PAYPAL, EFECTIVO |
| `notas` | TEXT | SI | NULL | Notas internas |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 13. wp_ga_casos

**Propósito:** Expedientes o casos que agrupan proyectos para un cliente.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `numero` | VARCHAR(30) | NO | - | Formato: CASO-CLI001-2024-0001 |
| `cliente_id` | INT | NO | - | FK clientes |
| `titulo` | VARCHAR(200) | NO | - | Título del caso |
| `descripcion` | TEXT | SI | NULL | Descripción detallada |
| `tipo` | ENUM | SI | 'PROYECTO' | PROYECTO, LEGAL, SOPORTE, CONSULTORIA, OTRO |
| `estado` | ENUM | SI | 'ABIERTO' | ABIERTO, EN_PROGRESO, EN_ESPERA, CERRADO, CANCELADO |
| `prioridad` | ENUM | SI | 'MEDIA' | BAJA, MEDIA, ALTA, URGENTE |
| `fecha_apertura` | DATE | NO | - | Fecha apertura |
| `fecha_cierre_estimada` | DATE | SI | NULL | Cierre estimado |
| `fecha_cierre_real` | DATETIME | SI | NULL | Cierre real |
| `responsable_id` | BIGINT UNSIGNED | SI | NULL | FK wp_users |
| `presupuesto_horas` | INT | SI | NULL | Horas presupuestadas |
| `presupuesto_dinero` | DECIMAL(12,2) | SI | NULL | Monto en USD |
| `notas` | TEXT | SI | NULL | Notas internas |
| `created_by` | BIGINT UNSIGNED | SI | NULL | Quién creó |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 14. wp_ga_proyectos

**Propósito:** Proyectos específicos dentro de un caso.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `caso_id` | INT | NO | - | FK casos |
| `codigo` | VARCHAR(20) | NO | - | Código único (PRY-001) |
| `nombre` | VARCHAR(200) | NO | - | Nombre proyecto |
| `descripcion` | TEXT | SI | NULL | Descripción |
| `fecha_inicio` | DATE | SI | NULL | Inicio planificado |
| `fecha_fin_estimada` | DATE | SI | NULL | Fin estimado |
| `fecha_fin_real` | DATE | SI | NULL | Fin real |
| `estado` | ENUM | SI | 'PLANIFICACION' | PLANIFICACION, EN_PROGRESO, PAUSADO, COMPLETADO, CANCELADO |
| `responsable_id` | BIGINT UNSIGNED | SI | NULL | FK wp_users |
| `presupuesto_horas` | INT | SI | NULL | Horas presupuestadas |
| `tarifa_hora` | DECIMAL(10,2) | SI | 0 | Tarifa por hora USD |
| `descuento_porcentaje` | DECIMAL(5,2) | SI | 0 | Descuento % |
| `descuento_monto` | DECIMAL(10,2) | SI | 0 | Descuento fijo |
| `subtotal` | DECIMAL(12,2) | SI | 0 | Antes de descuento |
| `total` | DECIMAL(12,2) | SI | 0 | Total a cobrar |
| `presupuesto_dinero` | DECIMAL(12,2) | SI | NULL | Presupuesto USD |
| `horas_consumidas` | DECIMAL(10,2) | SI | 0 | Horas usadas |
| `porcentaje_avance` | INT | SI | 0 | 0-100 |
| `timedoctor_project_id` | VARCHAR(50) | SI | NULL | ID TimeDoctor |
| `mostrar_ranking` | TINYINT(1) | SI | 0 | Mostrar en portal |
| `mostrar_tareas_equipo` | TINYINT(1) | SI | 1 | - |
| `mostrar_horas_equipo` | TINYINT(1) | SI | 0 | - |
| `notas` | TEXT | SI | NULL | Notas |
| `created_by` | BIGINT UNSIGNED | SI | NULL | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

## Sprint 7-8: Marketplace

### 15. wp_ga_aplicantes

**Propósito:** Freelancers y empresas externas que aplican a órdenes de trabajo.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `usuario_wp_id` | BIGINT UNSIGNED | SI | NULL | FK wp_users (login portal) |
| `tipo` | ENUM | SI | 'PERSONA_NATURAL' | PERSONA_NATURAL, EMPRESA |
| `nombre_completo` | VARCHAR(200) | NO | - | Nombre o razón social |
| `nombre_comercial` | VARCHAR(200) | SI | NULL | Nombre comercial (empresas) |
| `documento_tipo` | VARCHAR(20) | SI | NULL | CC, NIT, RFC, EIN, PASAPORTE |
| `documento_numero` | VARCHAR(50) | SI | NULL | Número documento |
| `email` | VARCHAR(200) | NO | - | Email contacto |
| `telefono` | VARCHAR(50) | SI | NULL | Teléfono |
| `pais` | VARCHAR(2) | SI | NULL | Código ISO |
| `ciudad` | VARCHAR(100) | SI | NULL | Ciudad |
| `direccion` | TEXT | SI | NULL | Dirección |
| `habilidades` | JSON | SI | NULL | Array de skills ["PHP", "WordPress"] |
| `experiencia_anios` | INT | SI | 0 | Años experiencia |
| `portafolio_url` | VARCHAR(500) | SI | NULL | URL portafolio |
| `cv_url` | VARCHAR(500) | SI | NULL | URL CV |
| `descripcion_perfil` | TEXT | SI | NULL | Bio/descripción |
| `metodo_pago_preferido` | ENUM | SI | 'TRANSFERENCIA' | Ver métodos |
| `datos_pago_binance` | JSON | SI | NULL | Datos Binance |
| `datos_pago_wise` | JSON | SI | NULL | Datos Wise |
| `datos_pago_paypal` | JSON | SI | NULL | Datos PayPal |
| `datos_pago_banco` | JSON | SI | NULL | Datos bancarios |
| `documento_identidad_url` | VARCHAR(500) | SI | NULL | Scan documento |
| `rut_url` | VARCHAR(500) | SI | NULL | RUT/RFC |
| `certificado_bancario_url` | VARCHAR(500) | SI | NULL | Certificación bancaria |
| `estado` | ENUM | SI | 'PENDIENTE_VERIFICACION' | Ver estados |
| `fecha_verificacion` | DATETIME | SI | NULL | Cuándo se verificó |
| `verificado_por` | BIGINT UNSIGNED | SI | NULL | Quién verificó |
| `notas_verificacion` | TEXT | SI | NULL | Notas |
| `total_aplicaciones` | INT | SI | 0 | Total apps |
| `aplicaciones_aceptadas` | INT | SI | 0 | Apps aceptadas |
| `calificacion_promedio` | DECIMAL(3,2) | SI | 0 | Rating 0-5 |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Estados de Aplicante:**
- `PENDIENTE_VERIFICACION`: Recién registrado
- `VERIFICADO`: Documentos validados
- `RECHAZADO`: Documentos inválidos
- `SUSPENDIDO`: Cuenta suspendida

---

### 16. wp_ga_ordenes_trabajo

**Propósito:** Ofertas de trabajo publicadas en el marketplace.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `codigo` | VARCHAR(20) | NO | - | Formato: OT-YYYY-NNNN |
| `titulo` | VARCHAR(200) | NO | - | Título de la orden |
| `descripcion` | TEXT | SI | NULL | Descripción detallada |
| `requisitos` | TEXT | SI | NULL | Requisitos para aplicar |
| `url_manual` | VARCHAR(500) | SI | NULL | URL manual del puesto |
| `categoria` | ENUM | SI | 'OTRO' | DESARROLLO, DISENO, MARKETING, LEGAL, CONTABILIDAD, ADMINISTRATIVO, SOPORTE, CONSULTORIA, OTRO |
| `departamento_id` | INT | SI | NULL | FK departamento |
| `puesto_requerido_id` | INT | SI | NULL | FK puesto |
| `tipo_pago` | ENUM | SI | 'POR_HORA' | POR_HORA, PRECIO_FIJO, A_CONVENIR |
| `tarifa_hora_min` | DECIMAL(10,2) | SI | NULL | Tarifa mínima USD |
| `tarifa_hora_max` | DECIMAL(10,2) | SI | NULL | Tarifa máxima USD |
| `presupuesto_fijo` | DECIMAL(12,2) | SI | NULL | Precio fijo USD |
| `tarifa_negociable` | TINYINT(1) | SI | 1 | Acepta propuestas |
| `horas_estimadas` | INT | SI | NULL | Horas totales |
| `duracion_estimada` | VARCHAR(100) | SI | NULL | "2 semanas", "1 mes" |
| `dedicacion` | ENUM | SI | 'POR_HORAS' | TIEMPO_COMPLETO, MEDIO_TIEMPO, POR_HORAS, PROYECTO |
| `fecha_publicacion` | DATE | SI | NULL | Cuándo se publicó |
| `fecha_cierre_aplicaciones` | DATE | SI | NULL | Hasta cuándo recibe apps |
| `fecha_inicio_estimada` | DATE | SI | NULL | Inicio trabajo |
| `fecha_fin_estimada` | DATE | SI | NULL | Fin trabajo |
| `max_aplicantes` | INT | SI | 0 | 0=Sin límite |
| `total_aplicantes` | INT | SI | 0 | Contador |
| `estado` | ENUM | SI | 'BORRADOR' | Ver estados |
| `modalidad` | ENUM | SI | 'REMOTO' | REMOTO, PRESENCIAL, HIBRIDO |
| `ubicacion` | VARCHAR(200) | SI | NULL | Si es presencial |
| `zona_horaria` | VARCHAR(50) | SI | NULL | Zona horaria requerida |
| `caso_id` | INT | SI | NULL | FK caso |
| `proyecto_id` | INT | SI | NULL | FK proyecto |
| `cliente_id` | INT | SI | NULL | FK cliente |
| `empresa_id` | INT | SI | NULL | FK empresa pagadora |
| `responsable_id` | BIGINT UNSIGNED | SI | NULL | FK wp_users gestor |
| `es_publica` | TINYINT(1) | SI | 1 | Visible en portal |
| `requiere_nda` | TINYINT(1) | SI | 0 | Requiere NDA |
| `habilidades_requeridas` | JSON | SI | NULL | Array de skills |
| `experiencia_minima` | INT | SI | 0 | Años mínimos |
| `archivos_adjuntos` | JSON | SI | NULL | URLs adjuntos |
| `notas_internas` | TEXT | SI | NULL | Notas admin |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_by` | BIGINT UNSIGNED | SI | NULL | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Estados de Orden:**
```
BORRADOR → PUBLICADA → ASIGNADA → EN_PROGRESO → COMPLETADA
              ↓                        ↓
           CERRADA                 CANCELADA
```

---

### 17. wp_ga_aplicaciones_orden

**Propósito:** Registra las aplicaciones de aplicantes a órdenes.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `orden_trabajo_id` | INT | NO | - | FK orden |
| `aplicante_id` | INT | NO | - | FK aplicante |
| `fecha_aplicacion` | DATETIME | SI | CURRENT_TIMESTAMP | Cuándo aplicó |
| `carta_presentacion` | TEXT | SI | NULL | Mensaje/propuesta |
| `tarifa_solicitada` | DECIMAL(10,2) | SI | NULL | Tarifa propuesta |
| `disponibilidad` | VARCHAR(200) | SI | NULL | "Inmediata", etc. |
| `horas_disponibles_semana` | INT | SI | NULL | Horas/semana |
| `archivos_adjuntos` | JSON | SI | NULL | URLs adjuntos |
| `estado` | ENUM | SI | 'PENDIENTE' | Ver estados |
| `puntuacion` | INT | SI | NULL | 1-10 interno |
| `notas_evaluacion` | TEXT | SI | NULL | Notas evaluador |
| `evaluado_por` | BIGINT UNSIGNED | SI | NULL | Quién evaluó |
| `fecha_evaluacion` | DATETIME | SI | NULL | - |
| `motivo_rechazo` | ENUM | SI | NULL | Ver motivos |
| `detalle_rechazo` | TEXT | SI | NULL | Explicación |
| `contrato_generado_id` | INT | SI | NULL | FK contrato |
| `fecha_contratacion` | DATETIME | SI | NULL | - |
| `tarifa_acordada` | DECIMAL(10,2) | SI | NULL | Tarifa final |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Estados de Aplicación:**
```
PENDIENTE → EN_REVISION → PRESELECCIONADO → ENTREVISTA → ACEPTADA → CONTRATADO
                ↓              ↓                ↓           ↓
            RECHAZADA      RECHAZADA        RECHAZADA   RETIRADA
```

**Motivos de Rechazo:**
- `PERFIL_NO_ADECUADO`
- `TARIFA_ALTA`
- `DISPONIBILIDAD`
- `DOCUMENTOS_INCOMPLETOS`
- `OTRO_CANDIDATO`
- `ORDEN_CANCELADA`
- `OTRO`

**Constraint:** `UNIQUE KEY uk_orden_aplicante (orden_trabajo_id, aplicante_id)`

---

## Sprint 9-10: Facturación

### 18. wp_ga_facturas

**Propósito:** Facturas emitidas a clientes.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `numero` | VARCHAR(30) | NO | - | FAC-XX-YYYY-NNNN |
| `cliente_id` | INT | NO | - | FK cliente |
| `cliente_nombre` | VARCHAR(200) | SI | NULL | Snapshot nombre |
| `cliente_documento` | VARCHAR(50) | SI | NULL | Snapshot documento |
| `cliente_direccion` | TEXT | SI | NULL | Snapshot dirección |
| `cliente_email` | VARCHAR(200) | SI | NULL | Email envío |
| `caso_id` | INT | SI | NULL | FK caso |
| `proyecto_id` | INT | SI | NULL | FK proyecto |
| `cotizacion_origen_id` | INT | SI | NULL | FK cotización |
| `pais_facturacion` | VARCHAR(2) | NO | - | Código ISO |
| `moneda` | VARCHAR(3) | SI | 'USD' | Código moneda |
| `tasa_cambio` | DECIMAL(12,4) | SI | 1.0000 | Tasa al facturar |
| `impuesto_nombre` | VARCHAR(50) | SI | NULL | IVA, etc. |
| `impuesto_porcentaje` | DECIMAL(5,2) | SI | 0.00 | % impuesto |
| `retencion_nombre` | VARCHAR(50) | SI | NULL | Retención |
| `retencion_porcentaje` | DECIMAL(5,2) | SI | 0.00 | % retención |
| `subtotal` | DECIMAL(14,2) | SI | 0.00 | Suma líneas |
| `descuento_porcentaje` | DECIMAL(5,2) | SI | 0.00 | Descuento % |
| `descuento_monto` | DECIMAL(14,2) | SI | 0.00 | Monto descuento |
| `base_impuesto` | DECIMAL(14,2) | SI | 0.00 | Base impuesto |
| `impuesto_monto` | DECIMAL(14,2) | SI | 0.00 | Monto impuesto |
| `total` | DECIMAL(14,2) | SI | 0.00 | Total con impuesto |
| `retencion_monto` | DECIMAL(14,2) | SI | 0.00 | Monto retención |
| `total_a_pagar` | DECIMAL(14,2) | SI | 0.00 | Total neto |
| `monto_pagado` | DECIMAL(14,2) | SI | 0.00 | Pagado hasta ahora |
| `saldo_pendiente` | DECIMAL(14,2) | SI | 0.00 | Saldo por cobrar |
| `fecha_emision` | DATE | SI | NULL | Fecha emisión |
| `fecha_vencimiento` | DATE | SI | NULL | Fecha límite pago |
| `dias_credito` | INT | SI | 30 | Días crédito |
| `estado` | ENUM | SI | 'BORRADOR' | Ver estados |
| `numero_documento_pos` | VARCHAR(50) | SI | NULL | # sistema POS |
| `consecutivo_dian` | VARCHAR(100) | SI | NULL | Consecutivo DIAN/SAT |
| `cufe` | VARCHAR(200) | SI | NULL | Código único FE |
| `qr_code` | TEXT | SI | NULL | QR validación |
| `url_pdf` | VARCHAR(500) | SI | NULL | PDF firmado |
| `url_xml` | VARCHAR(500) | SI | NULL | XML firmado |
| `concepto_general` | TEXT | SI | NULL | Descripción general |
| `notas` | TEXT | SI | NULL | Notas cliente |
| `notas_internas` | TEXT | SI | NULL | Notas internas |
| `terminos` | TEXT | SI | NULL | Términos |
| `costo_horas` | DECIMAL(14,2) | SI | 0.00 | Costo horas |
| `comisiones_total` | DECIMAL(14,2) | SI | 0.00 | Total comisiones |
| `utilidad_bruta` | DECIMAL(14,2) | SI | 0.00 | Utilidad bruta |
| `utilidad_neta` | DECIMAL(14,2) | SI | 0.00 | Utilidad neta |
| `margen_porcentaje` | DECIMAL(5,2) | SI | 0.00 | Margen % |
| `creado_por` | BIGINT UNSIGNED | SI | NULL | - |
| `enviado_por` | BIGINT UNSIGNED | SI | NULL | - |
| `fecha_envio` | DATETIME | SI | NULL | - |
| `anulado_por` | BIGINT UNSIGNED | SI | NULL | - |
| `fecha_anulacion` | DATETIME | SI | NULL | - |
| `motivo_anulacion` | TEXT | SI | NULL | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Estados de Factura:**
```
BORRADOR → ENVIADA → PAGADA
              ↓
           PARCIAL → PAGADA
              ↓
           VENCIDA → PAGADA (pago tardío)

BORRADOR → ANULADA
```

---

### 19. wp_ga_facturas_detalle

**Propósito:** Líneas de detalle de facturas.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `factura_id` | INT | NO | - | FK factura |
| `orden` | INT | SI | 0 | Orden en factura |
| `tipo` | ENUM | SI | 'SERVICIO' | SERVICIO, HORA, PRODUCTO, DESCUENTO, AJUSTE |
| `codigo` | VARCHAR(50) | SI | NULL | Código concepto |
| `descripcion` | TEXT | NO | - | Descripción |
| `cantidad` | DECIMAL(10,2) | SI | 1.00 | Cantidad |
| `unidad` | VARCHAR(20) | SI | 'UNIDAD' | HORA, UNIDAD, etc. |
| `precio_unitario` | DECIMAL(14,4) | SI | 0.0000 | Precio/unidad |
| `descuento_porcentaje` | DECIMAL(5,2) | SI | 0.00 | Descuento % línea |
| `descuento_monto` | DECIMAL(14,2) | SI | 0.00 | Monto descuento |
| `subtotal` | DECIMAL(14,2) | SI | 0.00 | Cant * Precio - Desc |
| `aplica_impuesto` | TINYINT(1) | SI | 1 | 1=Grava |
| `impuesto_porcentaje` | DECIMAL(5,2) | SI | 0.00 | % impuesto línea |
| `impuesto_monto` | DECIMAL(14,2) | SI | 0.00 | Monto impuesto |
| `total_linea` | DECIMAL(14,2) | SI | 0.00 | Subtotal + Impuesto |
| `registro_hora_id` | INT | SI | NULL | FK registro_horas |
| `tarea_id` | INT | SI | NULL | FK tarea |
| `fecha_servicio` | DATE | SI | NULL | Fecha del servicio |
| `costo_unitario` | DECIMAL(14,4) | SI | 0.0000 | Costo real |
| `costo_total` | DECIMAL(14,2) | SI | 0.00 | Costo línea |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 20. wp_ga_cotizaciones

**Propósito:** Cotizaciones/presupuestos antes de facturar.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `numero` | VARCHAR(30) | NO | - | COT-YYYY-NNNN |
| `cliente_id` | INT | NO | - | FK cliente |
| `cliente_nombre` | VARCHAR(200) | SI | NULL | Snapshot |
| `cliente_email` | VARCHAR(200) | SI | NULL | Email envío |
| `contacto_nombre` | VARCHAR(200) | SI | NULL | Contacto |
| `caso_id` | INT | SI | NULL | FK caso |
| `proyecto_id` | INT | SI | NULL | FK proyecto |
| `titulo` | VARCHAR(200) | SI | NULL | Título |
| `descripcion` | TEXT | SI | NULL | Descripción |
| `moneda` | VARCHAR(3) | SI | 'USD' | Código moneda |
| `pais_destino` | VARCHAR(2) | SI | NULL | País cliente |
| `impuesto_nombre` | VARCHAR(50) | SI | NULL | IVA, etc. |
| `impuesto_porcentaje` | DECIMAL(5,2) | SI | 0.00 | - |
| `subtotal` | DECIMAL(14,2) | SI | 0.00 | Suma líneas |
| `descuento_porcentaje` | DECIMAL(5,2) | SI | 0.00 | - |
| `descuento_monto` | DECIMAL(14,2) | SI | 0.00 | - |
| `impuesto_monto` | DECIMAL(14,2) | SI | 0.00 | - |
| `total` | DECIMAL(14,2) | SI | 0.00 | Total cotizado |
| `fecha_emision` | DATE | SI | NULL | Fecha emisión |
| `fecha_vigencia` | DATE | SI | NULL | Válida hasta |
| `dias_vigencia` | INT | SI | 30 | Días vigencia |
| `estado` | ENUM | SI | 'BORRADOR' | Ver estados |
| `factura_generada_id` | INT | SI | NULL | FK factura |
| `fecha_conversion` | DATETIME | SI | NULL | Cuándo se convirtió |
| `convertido_por` | BIGINT UNSIGNED | SI | NULL | - |
| `notas` | TEXT | SI | NULL | Notas cliente |
| `notas_internas` | TEXT | SI | NULL | Notas internas |
| `terminos` | TEXT | SI | NULL | Términos |
| `forma_pago` | TEXT | SI | NULL | Forma de pago |
| `fecha_respuesta` | DATETIME | SI | NULL | Cuándo respondió |
| `motivo_rechazo` | TEXT | SI | NULL | Si rechazó |
| `creado_por` | BIGINT UNSIGNED | SI | NULL | - |
| `enviado_por` | BIGINT UNSIGNED | SI | NULL | - |
| `fecha_envio` | DATETIME | SI | NULL | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Estados de Cotización:**
```
BORRADOR → ENVIADA → APROBADA → FACTURADA
              ↓         ↓
          RECHAZADA  VENCIDA

BORRADOR → CANCELADA
```

---

### 21. wp_ga_cotizaciones_detalle

**Propósito:** Líneas de detalle de cotizaciones.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `cotizacion_id` | INT | NO | - | FK cotización |
| `orden` | INT | SI | 0 | Orden en cotización |
| `tipo` | ENUM | SI | 'SERVICIO' | SERVICIO, HORA, PRODUCTO, DESCUENTO |
| `codigo` | VARCHAR(50) | SI | NULL | Código |
| `descripcion` | TEXT | NO | - | Descripción |
| `cantidad` | DECIMAL(10,2) | SI | 1.00 | Cantidad |
| `unidad` | VARCHAR(20) | SI | 'UNIDAD' | Unidad medida |
| `precio_unitario` | DECIMAL(14,4) | SI | 0.0000 | Precio |
| `descuento_porcentaje` | DECIMAL(5,2) | SI | 0.00 | - |
| `descuento_monto` | DECIMAL(14,2) | SI | 0.00 | - |
| `subtotal` | DECIMAL(14,2) | SI | 0.00 | - |
| `aplica_impuesto` | TINYINT(1) | SI | 1 | - |
| `impuesto_porcentaje` | DECIMAL(5,2) | SI | 0.00 | - |
| `impuesto_monto` | DECIMAL(14,2) | SI | 0.00 | - |
| `total_linea` | DECIMAL(14,2) | SI | 0.00 | - |
| `horas_estimadas` | DECIMAL(10,2) | SI | NULL | Horas estimadas |
| `tarifa_hora` | DECIMAL(14,4) | SI | NULL | Tarifa/hora |
| `notas` | TEXT | SI | NULL | Notas línea |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

## Sprint 11-12: Acuerdos Económicos

### 22. wp_ga_empresas

**Propósito:** Catálogo de empresas propias (multi-entidad).

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `codigo` | VARCHAR(20) | NO | - | WOLK-CR, WOLK-US |
| `nombre` | VARCHAR(200) | NO | - | Nombre comercial |
| `razon_social` | VARCHAR(200) | NO | - | Razón social legal |
| `identificacion_tipo` | VARCHAR(20) | SI | NULL | NIT, EIN, Cédula Jurídica |
| `identificacion_fiscal` | VARCHAR(50) | NO | - | Número fiscal |
| `pais_id` | INT | SI | NULL | FK paises_config |
| `pais_iso` | VARCHAR(2) | NO | - | Código ISO |
| `direccion` | TEXT | SI | NULL | Dirección fiscal |
| `ciudad` | VARCHAR(100) | SI | NULL | Ciudad |
| `codigo_postal` | VARCHAR(20) | SI | NULL | CP |
| `telefono` | VARCHAR(50) | SI | NULL | Teléfono |
| `email` | VARCHAR(100) | SI | NULL | Email corporativo |
| `sitio_web` | VARCHAR(200) | SI | NULL | URL web |
| `logo_url` | VARCHAR(500) | SI | NULL | Logo para docs |
| `color_primario` | VARCHAR(7) | SI | '#0073aa' | Color hex |
| `prefijo_factura` | VARCHAR(10) | SI | 'FAC' | Prefijo facturas |
| `consecutivo_factura` | INT | SI | 0 | Último consecutivo |
| `pie_factura` | TEXT | SI | NULL | Pie de página |
| `datos_bancarios` | JSON | SI | NULL | Array cuentas |
| `es_principal` | TINYINT(1) | SI | 0 | 1=Default |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 23. wp_ga_catalogo_bonos

**Propósito:** Catálogo de bonos disponibles para órdenes.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `codigo` | VARCHAR(30) | NO | - | BONO-CAM, BONO-PUNT |
| `nombre` | VARCHAR(100) | NO | - | Nombre del bono |
| `descripcion` | TEXT | SI | NULL | Descripción detallada |
| `tipo_valor` | ENUM | SI | 'FIJO' | FIJO, PORCENTAJE |
| `valor_default` | DECIMAL(10,2) | SI | 0.00 | Valor sugerido |
| `frecuencia` | ENUM | SI | 'MENSUAL' | UNICO, SEMANAL, QUINCENAL, MENSUAL |
| `condicion_descripcion` | TEXT | SI | NULL | Condiciones |
| `categoria` | ENUM | SI | 'OTRO' | PRODUCTIVIDAD, ASISTENCIA, CALIDAD, COMUNICACION, METAS, OTRO |
| `icono` | VARCHAR(50) | SI | 'dashicons-awards' | Clase icono |
| `activo` | TINYINT(1) | SI | 1 | - |
| `orden` | INT | SI | 0 | Orden aparición |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 24. wp_ga_ordenes_acuerdos

**Propósito:** Acuerdos económicos específicos por orden de trabajo.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT | NO | AUTO_INCREMENT | PK |
| `orden_id` | INT | NO | - | FK orden_trabajo |
| `tipo_acuerdo` | ENUM | NO | - | Ver tipos |
| `valor` | DECIMAL(10,2) | NO | 0.00 | Monto o porcentaje |
| `es_porcentaje` | TINYINT(1) | SI | 0 | 1=Es porcentaje |
| `bono_id` | INT | SI | NULL | FK catalogo_bonos |
| `condicion` | VARCHAR(255) | SI | NULL | Ej: "rentabilidad > 50%" |
| `condicion_valor` | DECIMAL(10,2) | SI | NULL | Valor numérico |
| `descripcion` | TEXT | SI | NULL | Descripción |
| `notas_internas` | TEXT | SI | NULL | Notas admin |
| `frecuencia_pago` | ENUM | SI | 'MENSUAL' | POR_EVENTO, SEMANAL, QUINCENAL, MENSUAL, AL_FINALIZAR |
| `activo` | TINYINT(1) | SI | 1 | - |
| `orden` | INT | SI | 0 | Orden aparición |
| `created_by` | BIGINT UNSIGNED | SI | NULL | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Tipos de Acuerdo:**
- `HORA_REPORTADA`: Pago por hora reportada
- `HORA_APROBADA`: Pago por hora aprobada
- `TRABAJO_COMPLETADO`: Pago fijo al completar
- `COMISION_FACTURA`: % de facturas pagadas
- `COMISION_HORAS_SUPERVISADAS`: % de horas supervisadas
- `META_RENTABILIDAD`: Bono por rentabilidad
- `BONO`: Bono del catálogo

---

### 25. wp_ga_ordenes_bonos

**Propósito:** Bonos asignados a órdenes de trabajo.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT(20) | NO | AUTO_INCREMENT | PK |
| `orden_id` | BIGINT(20) | NO | - | FK orden_trabajo |
| `bono_id` | BIGINT(20) | NO | - | FK catalogo_bonos |
| `detalle` | TEXT | SI | NULL | Detalle específico |
| `monto_personalizado` | DECIMAL(10,2) | SI | NULL | Monto custom |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | NO | - | - |

---

### 26. wp_ga_comisiones_generadas

**Propósito:** Comisiones calculadas automáticamente.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `orden_id` | BIGINT UNSIGNED | NO | - | FK orden_trabajo |
| `acuerdo_id` | BIGINT UNSIGNED | NO | - | FK ordenes_acuerdos |
| `aplicante_id` | BIGINT UNSIGNED | NO | - | FK aplicantes (quien recibe) |
| `pago_origen_id` | BIGINT UNSIGNED | SI | NULL | ID factura/documento |
| `tipo_origen` | ENUM | SI | 'FACTURA' | FACTURA, PAGO_MANUAL, OTRO |
| `monto_base` | DECIMAL(12,2) | NO | - | Base del cálculo |
| `porcentaje_aplicado` | DECIMAL(5,2) | SI | NULL | % si aplica |
| `monto_fijo_aplicado` | DECIMAL(12,2) | SI | NULL | Monto fijo si aplica |
| `monto_comision` | DECIMAL(12,2) | NO | - | Monto final |
| `estado` | ENUM | SI | 'DISPONIBLE' | DISPONIBLE, SOLICITADA, PAGADA, CANCELADA |
| `solicitud_id` | BIGINT UNSIGNED | SI | NULL | FK solicitudes_cobro |
| `notas` | TEXT | SI | NULL | Notas |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 27. wp_ga_solicitudes_cobro

**Propósito:** Solicitudes de pago de proveedores.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `numero_solicitud` | VARCHAR(20) | NO | - | SOL-YYYY-NNNN |
| `aplicante_id` | BIGINT UNSIGNED | NO | - | FK aplicantes |
| `monto_disponible` | DECIMAL(12,2) | NO | - | Total disponible |
| `monto_solicitado` | DECIMAL(12,2) | NO | - | Cuánto solicita |
| `metodo_pago` | ENUM | NO | - | BINANCE, WISE, PAYPAL, TRANSFERENCIA_LOCAL, OTRO |
| `datos_pago` | JSON | SI | NULL | Datos del método |
| `moneda` | VARCHAR(3) | SI | 'USD' | - |
| `notas_solicitante` | TEXT | SI | NULL | Mensaje |
| `estado` | ENUM | SI | 'PENDIENTE' | PENDIENTE, EN_REVISION, APROBADA, RECHAZADA, PAGADA, CANCELADA |
| `revisado_por` | BIGINT UNSIGNED | SI | NULL | Quién revisó |
| `notas_revision` | TEXT | SI | NULL | Notas revisor |
| `fecha_revision` | DATETIME | SI | NULL | - |
| `fecha_pago` | DATETIME | SI | NULL | - |
| `comprobante_pago` | VARCHAR(500) | SI | NULL | URL/referencia |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

---

### 28. wp_ga_solicitudes_cobro_detalle

**Propósito:** Detalle de comisiones incluidas en solicitud.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `solicitud_id` | BIGINT UNSIGNED | NO | - | FK solicitudes_cobro |
| `comision_id` | BIGINT UNSIGNED | NO | - | FK comisiones_generadas |
| `monto_original` | DECIMAL(12,2) | NO | - | Monto comisión |
| `porcentaje_original` | DECIMAL(5,2) | SI | NULL | % original |
| `tipo_ajuste` | ENUM | SI | 'NINGUNO' | NINGUNO, PORCENTAJE_REDUCIDO, MONTO_FIJO |
| `porcentaje_solicitado` | DECIMAL(5,2) | SI | NULL | Nuevo % |
| `monto_solicitado` | DECIMAL(12,2) | NO | - | Monto final |
| `motivo_ajuste` | TEXT | SI | NULL | Por qué ajustó |
| `incluida` | TINYINT(1) | SI | 1 | 1=Incluida |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |

**Constraint:** `UNIQUE KEY unique_solicitud_comision (solicitud_id, comision_id)`

---

## Sprint 13-14: Catálogos

### 29. wp_ga_metodos_pago

**Propósito:** Catálogo maestro de métodos de pago.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `tipo` | ENUM | NO | - | transferencia, paypal, wise, binance, stripe, crypto, efectivo, otro |
| `pais_codigo` | VARCHAR(3) | SI | NULL | Código ISO país |
| `moneda` | VARCHAR(3) | SI | 'USD' | Código moneda |
| `nombre` | VARCHAR(100) | NO | - | Nombre descriptivo |
| `descripcion` | TEXT | SI | NULL | Notas internas |
| `banco_nombre` | VARCHAR(100) | SI | NULL | Nombre banco |
| `banco_tipo_cuenta` | ENUM | SI | NULL | ahorros, corriente, checking, savings |
| `banco_numero_cuenta` | VARCHAR(50) | SI | NULL | # cuenta |
| `banco_titular` | VARCHAR(150) | SI | NULL | Titular |
| `banco_documento` | VARCHAR(50) | SI | NULL | Doc titular |
| `banco_swift` | VARCHAR(20) | SI | NULL | SWIFT/BIC |
| `banco_iban` | VARCHAR(50) | SI | NULL | IBAN |
| `banco_routing` | VARCHAR(20) | SI | NULL | Routing (USA) |
| `banco_clabe` | VARCHAR(20) | SI | NULL | CLABE (MX) |
| `wallet_email` | VARCHAR(150) | SI | NULL | Email wallet |
| `wallet_usuario` | VARCHAR(100) | SI | NULL | Username |
| `wallet_account_id` | VARCHAR(100) | SI | NULL | ID cuenta |
| `crypto_red` | ENUM | SI | NULL | BTC, ETH, BSC, TRC20, ERC20, POLYGON, SOLANA, otro |
| `crypto_wallet_address` | VARCHAR(150) | SI | NULL | Dirección wallet |
| `crypto_token` | VARCHAR(20) | SI | NULL | USDT, USDC, etc. |
| `crypto_binance_id` | VARCHAR(50) | SI | NULL | Binance Pay ID |
| `saldo_actual` | DECIMAL(15,2) | SI | 0.00 | Saldo actual |
| `saldo_minimo` | DECIMAL(15,2) | SI | 0.00 | Alerta mínimo |
| `limite_diario` | DECIMAL(15,2) | SI | NULL | Límite diario |
| `uso_pagos_proveedores` | TINYINT(1) | SI | 1 | Para pagar |
| `uso_cobros_clientes` | TINYINT(1) | SI | 0 | Para recibir |
| `es_principal` | TINYINT(1) | SI | 0 | Cuenta principal |
| `orden_prioridad` | INT | SI | 0 | Orden preferencia |
| `activo` | TINYINT(1) | SI | 1 | - |
| `created_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `created_by` | BIGINT UNSIGNED | SI | NULL | - |
| `updated_at` | DATETIME | SI | CURRENT_TIMESTAMP | - |
| `updated_by` | BIGINT UNSIGNED | SI | NULL | - |

---

## Configuración en wp_options

El plugin almacena configuraciones globales en la tabla nativa `wp_options` de WordPress.

### ga_notificaciones_config

**Propósito:** Configuración de notificaciones por email activas/inactivas.

**Tipo de dato:** Array serializado (PHP serialize)

**Estructura:**

```php
array(
    // Tareas
    'tarea_asignada'        => 1,  // 1=Activo, 0=Inactivo
    'tarea_iniciada'        => 1,
    'tarea_enviada_qa'      => 1,
    'tarea_aprobada_qa'     => 1,
    'tarea_rechazada_qa'    => 1,
    'tarea_completada'      => 1,
    'tarea_aprobada'        => 1,
    'tarea_rechazada'       => 1,

    // Aplicantes
    'aplicante_bienvenida'      => 1,
    'aplicante_aplicacion'      => 1,
    'aplicante_estado_cambio'   => 1,

    // Órdenes de trabajo
    'orden_nueva'           => 1,
    'orden_asignada'        => 1,

    // Facturación
    'factura_enviada'       => 1,
    'factura_pagada'        => 1,
)
```

**Tipos de Notificación:**

| Clave | Descripción | Destinatario |
|-------|-------------|--------------|
| `tarea_asignada` | Tarea asignada a empleado | Empleado |
| `tarea_iniciada` | Empleado inició una tarea | Jefe |
| `tarea_enviada_qa` | Tarea enviada a revisión QA | Jefe/QA |
| `tarea_aprobada_qa` | QA aprobó la tarea | Empleado |
| `tarea_rechazada_qa` | QA rechazó la tarea | Empleado |
| `tarea_completada` | Tarea completada (pendiente aprobación) | Jefe |
| `tarea_aprobada` | Tarea aprobada por jefe | Empleado |
| `tarea_rechazada` | Tarea rechazada por jefe | Empleado |
| `aplicante_bienvenida` | Bienvenida a nuevo aplicante | Aplicante |
| `aplicante_aplicacion` | Confirmación de aplicación | Aplicante |
| `aplicante_estado_cambio` | Cambio de estado en aplicación | Aplicante |
| `orden_nueva` | Nueva orden de trabajo publicada | Aplicantes |
| `orden_asignada` | Orden asignada a aplicante | Aplicante |
| `factura_enviada` | Factura enviada al cliente | Cliente |
| `factura_pagada` | Confirmación de pago recibido | Cliente/Admin |

**Valor por defecto:** Todas las notificaciones activas (1)

**Gestión:** Panel de administración GestionAdmin → Notificaciones

---

## Roles de WordPress

El plugin crea los siguientes roles personalizados:

| Rol | Nombre | Capacidades Principales |
|-----|--------|------------------------|
| `ga_socio` | Socio | `ga_manage_all`, `ga_view_all`, `ga_approve_payments`, `ga_manage_users` |
| `ga_director` | Director | `ga_manage_department`, `ga_view_department`, `ga_approve_tasks`, `ga_manage_projects` |
| `ga_jefe` | Jefe de Equipo | `ga_manage_team`, `ga_view_team`, `ga_approve_tasks`, `ga_assign_tasks` |
| `ga_empleado` | Empleado | `ga_view_own`, `ga_submit_tasks`, `ga_track_time` |
| `ga_cliente` | Cliente | `ga_view_own_projects`, `ga_submit_tickets` |
| `ga_aplicante` | Aplicante | `ga_apply_jobs`, `ga_view_marketplace` |

---

## Convenciones de Datos

### Tiempos
- **Todos los tiempos se guardan en MINUTOS** en la base de datos
- Se convierten a horas/minutos en la capa de presentación

### Montos
- **Moneda principal:** USD
- **Precisión montos:** DECIMAL(14,2) para totales, DECIMAL(14,4) para precios unitarios
- **Tasas de cambio:** DECIMAL(12,4)

### Fechas
- **created_at / updated_at:** DATETIME con CURRENT_TIMESTAMP
- **Fechas de negocio:** DATE
- **Timestamps con hora:** DATETIME

### JSON
- Usado para: habilidades, datos de pago, archivos adjuntos, configuraciones flexibles
- Siempre validar estructura en PHP antes de guardar

### Estados
- Uso consistente de ENUM para estados
- Nombres en MAYÚSCULAS con guiones bajos
- Documentar flujo de estados en cada tabla

---

## Índices y Performance

Cada tabla incluye índices para:
1. **Primary Key:** Siempre AUTO_INCREMENT
2. **Foreign Keys:** Índice en cada FK
3. **Campos de búsqueda frecuente:** codigo, estado, activo
4. **Campos de filtro:** fecha_*, pais, tipo
5. **Unique constraints:** Para códigos y relaciones únicas

---

*Documento generado automáticamente - GestionAdmin Wolk v1.6.1*
