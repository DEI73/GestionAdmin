# GESTIONADMIN BY WOLK
## Documento de Visión, Análisis y Requerimientos Completo
### Versión 1.0 - Diciembre 2024

---

# ÍNDICE GENERAL

## PARTE 1: VISIÓN Y CONCEPTOS
1. [Visión del Proyecto](#1-visión-del-proyecto)
2. [Modelo de Negocio](#2-modelo-de-negocio)
3. [Arquitectura Técnica](#3-arquitectura-técnica)

## PARTE 2: ESTRUCTURA ORGANIZACIONAL
4. [Jerarquía de Roles](#4-jerarquía-de-roles)
5. [Departamentos y Puestos](#5-departamentos-y-puestos)
6. [Sistema de Supervisión Flexible](#6-sistema-de-supervisión-flexible)

## PARTE 3: MÓDULOS CORE
7. [Gestión de Tareas y Subtareas](#7-gestión-de-tareas-y-subtareas)
8. [Timer y Registro de Horas](#8-timer-y-registro-de-horas)
9. [Flujo de Revisión Configurable](#9-flujo-de-revisión-configurable)
10. [Sistema de Casos/Expedientes](#10-sistema-de-casosexpedientes)

## PARTE 4: PORTAL DE CLIENTES
11. [Acceso y Autenticación](#11-portal-cliente-acceso)
12. [Visualización de Proyectos](#12-portal-cliente-proyectos)
13. [Firma Digital](#13-firma-digital)

## PARTE 5: PORTAL DE ÓRDENES DE TRABAJO (Marketplace)
14. [Concepto Marketplace](#14-concepto-marketplace)
15. [Registro de Aplicantes](#15-registro-de-aplicantes)
16. [Flujo de Aplicación](#16-flujo-de-aplicación)
17. [Contratos Multi-Proyecto](#17-contratos-multi-proyecto)

## PARTE 6: FACTURACIÓN Y COBROS
18. [Facturación por País](#18-facturación-por-país)
19. [Flujo de Solicitud de Factura](#19-flujo-solicitud-factura)
20. [Integración con POS](#20-integración-pos)

## PARTE 7: SISTEMA DE PAGOS A PRESTADORES
21. [Botón COBRAR](#21-botón-cobrar)
22. [Procesamiento de Pagos](#22-procesamiento-pagos)
23. [Métodos de Pago](#23-métodos-de-pago)
24. [Comprobantes](#24-comprobantes)

## PARTE 8: COMPENSACIÓN Y BONIFICACIONES
25. [Escalas de Tarifa](#25-escalas-de-tarifa)
26. [Revisiones de Tarifa](#26-revisiones-de-tarifa)
27. [Sistema de Bonos](#27-sistema-de-bonos)
28. [Penalidades](#28-penalidades)
29. [Comisiones Multinivel](#29-comisiones-multinivel)

## PARTE 9: ADMINISTRACIÓN Y CONTROL
30. [Reglas de Trabajo](#30-reglas-de-trabajo)
31. [Calendario Administrativo](#31-calendario-administrativo)
32. [Sistema de Visibilidad](#32-sistema-de-visibilidad)
33. [Dashboard Inversionistas](#33-dashboard-inversionistas)

## PARTE 10: HERRAMIENTAS
34. [Carga Rápida de Tareas](#34-carga-rápida)
35. [Plantillas Excel](#35-plantillas-excel)
36. [AI Chat](#36-ai-chat)

## PARTE 11: DASHBOARDS POR ROL
37. [Dashboard Dueño/Socio](#37-dashboard-dueño)
38. [Dashboard Director](#38-dashboard-director)
39. [Dashboard Jefe/PM](#39-dashboard-jefe)
40. [Dashboard Empleado](#40-dashboard-empleado)
41. [Dashboard Cliente](#41-dashboard-cliente)
42. [Dashboard Contabilidad](#42-dashboard-contabilidad)

## PARTE 12: BASE DE DATOS
43. [Modelo de Datos Completo](#43-modelo-datos)
44. [Diccionario de Tablas](#44-diccionario-tablas)

## PARTE 13: INTEGRACIONES
45. [Wolk POS](#45-wolk-pos)
46. [Time Doctor](#46-time-doctor)
47. [Procesadores de Pago](#47-procesadores-pago)

## PARTE 14: PLAN DE TRABAJO
48. [Fases del Proyecto](#48-fases)
49. [Cronograma](#49-cronograma)
50. [Estimación de Horas](#50-estimación)

---

# PARTE 1: VISIÓN Y CONCEPTOS

---

## 1. VISIÓN DEL PROYECTO

### 1.1 ¿Qué es GestionAdmin?

GestionAdmin es un **sistema integral de gestión empresarial** diseñado para empresas de servicios que trabajan con equipos distribuidos, freelancers y proveedores externos. Funciona como un **marketplace de trabajo ordenado** (similar al modelo Uber/Freelancer) pero para gestión interna de la empresa.

### 1.2 Problema que Resuelve

```
┌─────────────────────────────────────────────────────────────────────────┐
│ PROBLEMAS ACTUALES                    │ SOLUCIÓN GESTIONADMIN           │
├───────────────────────────────────────┼─────────────────────────────────┤
│ Control de horas manual en Excel      │ Timer integrado con validación  │
│ No saber quién hace qué               │ Dashboards en tiempo real       │
│ Pagos desordenados a freelancers      │ Sistema de cobro estructurado   │
│ Clientes sin visibilidad              │ Portal de cliente con acceso    │
│ Facturación en múltiples países       │ Integración con POS por país    │
│ Contratar gente sin proceso           │ Portal de órdenes de trabajo    │
│ No saber la rentabilidad              │ Reportes de ROI y márgenes      │
│ Múltiples herramientas dispersas      │ Todo en un solo sistema         │
└───────────────────────────────────────┴─────────────────────────────────┘
```

### 1.3 Visión a Futuro

> "Cualquier persona o empresa puede entrar a nuestro portal, ver las órdenes de trabajo disponibles, aplicar, ser aceptada, trabajar de forma ordenada con instrucciones claras, y cobrar su trabajo de manera transparente. Somos como un Uber del trabajo profesional."

### 1.4 Usuarios del Sistema

| Tipo de Usuario | Descripción |
|-----------------|-------------|
| **Socios/Dueños** | Inversionistas con visión total del negocio |
| **Directores** | Jefes de jefes, supervisan áreas completas |
| **Jefes/PM** | Gestionan proyectos y equipos directos |
| **Empleados** | Ejecutan tareas y reportan horas |
| **Clientes** | Ven progreso de sus proyectos |
| **Contabilidad** | Gestiona facturas y pagos |
| **Aplicantes** | Personas o empresas que quieren trabajar |

---

## 2. MODELO DE NEGOCIO

### 2.1 Tipos de Prestadores de Servicios

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    TIPOS DE PRESTADORES DE SERVICIOS                    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  👤 PERSONA NATURAL                    🏢 EMPRESA / PERSONA JURÍDICA   │
│  ─────────────────                     ───────────────────────────────  │
│  • Freelancers                         • Consultoras                    │
│  • Empleados                           • Agencias                       │
│  • Pasantes                            • Empresas de servicios          │
│  • Profesionales externos              • Proveedores especializados     │
│    (abogados, contadores)                                               │
│                                                                         │
│  Documentos requeridos:                Documentos requeridos:           │
│  • Cédula frente y reverso             • Cámara de comercio             │
│  • Hoja de vida                        • RUT/NIT                        │
│                                        • Portafolio de servicios        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Tipos de Relación Laboral

| Tipo | Descripción | Frecuencia de Pago |
|------|-------------|-------------------|
| **Mensual** | Empleados con tareas recurrentes | Cada mes/quincena |
| **Por Proyecto** | Freelancers para proyectos específicos | Al completar hitos |
| **Por Caso** | Profesionales para casos puntuales | Al cerrar el caso |
| **Por Hora** | Trabajo por demanda | Según horas aprobadas |

### 2.3 Flujo de Dinero

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         FLUJO DE DINERO                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ENTRADA DE DINERO (Cobrar a Clientes)                                  │
│  ─────────────────────────────────────                                  │
│  Cliente ←── Factura electrónica ←── Wolk POS ←── Solicitud PM         │
│       │                                                                 │
│       └──► Pago recibido ──► Registrado en sistema                     │
│                                                                         │
│  SALIDA DE DINERO (Pagar a Prestadores)                                 │
│  ──────────────────────────────────────                                 │
│  Prestador trabaja ──► QA revisa ──► Jefe aprueba ──► Botón COBRAR     │
│       │                                                                 │
│       └──► Contabilidad paga (Binance/Wise/etc) ──► Comprobante        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 3. ARQUITECTURA TÉCNICA

### 3.1 Stack Tecnológico

| Componente | Tecnología |
|------------|------------|
| **CMS Base** | WordPress |
| **Theme** | wolk-theme (personalizado) |
| **Plugin Core** | gestionadmin-wolk |
| **Frontend** | HTML/CSS/JS + AlpineJS |
| **Base de Datos** | MySQL (tablas wp_ga_*) |
| **Integraciones** | REST API |

### 3.2 Estructura del Plugin

```
gestionadmin-wolk/
├── gestionadmin-wolk.php          # Archivo principal
├── includes/
│   ├── class-ga-loader.php        # Cargador de clases
│   ├── class-ga-roles.php         # Gestión de roles
│   ├── class-ga-tasks.php         # Gestión de tareas
│   ├── class-ga-timer.php         # Sistema de timer
│   ├── class-ga-billing.php       # Facturación
│   ├── class-ga-payments.php      # Pagos a prestadores
│   └── class-ga-reports.php       # Reportes
├── admin/
│   ├── class-ga-admin.php         # Panel de administración
│   └── views/                     # Vistas del admin
├── public/
│   ├── class-ga-public.php        # Frontend público
│   └── views/                     # Vistas públicas
├── api/
│   └── class-ga-rest-api.php      # Endpoints REST
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── templates/
    ├── dashboards/                # Templates por rol
    └── portals/                   # Portales (cliente, trabajo)
```

### 3.3 Prefijos de Base de Datos

Todas las tablas usan el prefijo `wp_ga_` (WordPress GestionAdmin):

```
wp_ga_departamentos
wp_ga_puestos
wp_ga_usuarios
wp_ga_tareas
wp_ga_subtareas
wp_ga_registro_horas
wp_ga_clientes
wp_ga_casos
wp_ga_ordenes_pago
wp_ga_solicitudes_cobro
... (30+ tablas)
```

---

# PARTE 2: ESTRUCTURA ORGANIZACIONAL

---

## 4. JERARQUÍA DE ROLES

### 4.1 Niveles Jerárquicos

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      PIRÁMIDE ORGANIZACIONAL                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  NIVEL 1: SOCIO / DUEÑO                                                 │
│  ═══════════════════════                                                │
│  • Ve TODO el sistema                                                   │
│  • Acceso a finanzas completas                                          │
│  • Dashboard de inversión y ROI                                         │
│  • Punto de equilibrio y proyecciones                                   │
│                          │                                              │
│                          ▼                                              │
│  NIVEL 2: DIRECTOR / JEFE DE JEFES                                      │
│  ═══════════════════════════════════                                    │
│  • Supervisa múltiples jefes                                            │
│  • Ve departamentos asignados                                           │
│  • Aprueba incrementos de tarifa                                        │
│  • Comisiones de segundo nivel                                          │
│                          │                                              │
│           ┌──────────────┼──────────────┐                               │
│           ▼              ▼              ▼                               │
│  NIVEL 3: JEFE / PM                                                     │
│  ══════════════════                                                     │
│  • Gestiona su equipo directo                                           │
│  • Asigna y aprueba tareas                                              │
│  • Ve solo sus proyectos                                                │
│  • Solicita facturas                                                    │
│           │              │              │                               │
│     ┌─────┴─────┐  ┌─────┴─────┐  ┌─────┴─────┐                         │
│     ▼           ▼  ▼           ▼  ▼           ▼                         │
│  NIVEL 4: EMPLEADO / PRESTADOR                                          │
│  ═════════════════════════════════                                      │
│  • Ejecuta tareas asignadas                                             │
│  • Reporta horas con timer                                              │
│  • Presiona botón COBRAR                                                │
│  • Ve sus propias métricas                                              │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Matriz de Permisos por Nivel

| Permiso | Nivel 1 | Nivel 2 | Nivel 3 | Nivel 4 |
|---------|:-------:|:-------:|:-------:|:-------:|
| Ver todos los departamentos | ✅ | Asignados | ❌ | ❌ |
| Ver todos los empleados | ✅ | De sus jefes | De su equipo | ❌ |
| Ver finanzas completas | ✅ | De su área | De su proyecto | Solo suyas |
| Aprobar horas | ✅ | ✅ | ✅ | ❌ |
| Crear órdenes de trabajo | ✅ | ✅ | ✅ | ❌ |
| Solicitar factura | ✅ | ✅ | ✅ | ❌ |
| Procesar pagos | ✅ | ❌ | ❌ | ❌ |
| Ver ROI/Inversión | ✅ | ❌ | ❌ | ❌ |
| Configurar sistema | ✅ | ❌ | ❌ | ❌ |

### 4.3 Ejemplo Real de Estructura

```
LINCY (Socia-Trabajadora) - Nivel 1
    │
    │ Ve: TODO
    │
    ├─────────────────────────────────────────────────────────┐
    │                                                         │
    ▼                                                         ▼
DEIBY                                                    KELLY
Director de Desarrollo                                   Directora de Soporte
(Nivel 2)                                               (Nivel 2)
    │                                                         │
    │ Ve: Jefes de desarrollo                                │ Ve: Jefes de soporte
    │     + Sus equipos                                      │     + Sus equipos
    │                                                         │
    ├───────────────┬───────────────┐                        ├───────────────┐
    │               │               │                        │               │
    ▼               ▼               ▼                        ▼               ▼
Hillary         Carlos          María                    Viviana         Pedro
PM Proyecto A   PM Proyecto B   PM Proyecto C           Líder Sop 1     Líder Sop 2
(Nivel 3)       (Nivel 3)       (Nivel 3)               (Nivel 3)       (Nivel 3)
    │               │               │                        │               │
    ▼               ▼               ▼                        ▼               ▼
Equipo A        Equipo B        Equipo C                Agentes 1       Agentes 2
(Nivel 4)       (Nivel 4)       (Nivel 4)               (Nivel 4)       (Nivel 4)
```

---

## 5. DEPARTAMENTOS Y PUESTOS

### 5.1 Tipos de Departamento

| Tipo | Descripción | Ejemplo |
|------|-------------|---------|
| **Operación Fija** | Tareas mensuales repetitivas | Contabilidad, RRHH |
| **Proyectos** | Trabajo por proyecto con equipos | Desarrollo, Diseño |
| **Soporte** | Atención de tickets por demanda | Soporte técnico |
| **Comercial** | Gestión de ventas | Ventas, Marketing |

### 5.2 Configuración de Puesto de Trabajo

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⚙️ CONFIGURAR PUESTO DE TRABAJO                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📋 INFORMACIÓN BÁSICA                                                   │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Nombre del puesto*: [Developer Backend_______________]              ││
│ │ Código*: [DEV-BACK__]                                               ││
│ │ Departamento: [Desarrollo ▼]                                        ││
│ │ Nivel jerárquico: [4 - Empleado ▼]                                  ││
│ │ Reporta a (puesto): [Tech Lead ▼]                                   ││
│ │ Capacidad horas/semana: [40___]                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 💰 ESCALA DE TARIFAS POR ANTIGÜEDAD                                     │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ AÑO       │ TARIFA/HORA │ INCREMENTO │ REQUIERE APROBACIÓN         ││
│ │───────────┼─────────────┼────────────┼─────────────────────────────││
│ │ Año 1     │ $5.00       │ Base       │ -                           ││
│ │ Año 2     │ $6.00       │ +20%       │ ☑️ Jefe directo              ││
│ │ Año 3     │ $7.00       │ +17%       │ ☑️ Jefe directo              ││
│ │ Año 4     │ $8.00       │ +14%       │ ☑️ Jefe + Director           ││
│ │ Año 5+    │ $9.00       │ +12%       │ ☑️ Jefe + Director           ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 🔍 FLUJO DE REVISIÓN DE TAREAS                                          │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ○ Solo el jefe directo aprueba                                      ││
│ │ ● QA revisa, luego jefe aprueba                                     ││
│ │ ○ Supervisor específico revisa, luego jefe aprueba                  ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 5.3 Tabla: wp_ga_departamentos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| codigo | VARCHAR(20) | DEV, ADMIN, SOPORTE |
| nombre | VARCHAR(100) | Nombre completo |
| descripcion | TEXT | Descripción |
| tipo | ENUM | OPERACION_FIJA, PROYECTOS, SOPORTE, COMERCIAL |
| jefe_id | BIGINT | FK usuario jefe del departamento |
| activo | TINYINT | 1=activo |
| created_at | DATETIME | Fecha creación |

### 5.4 Tabla: wp_ga_puestos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| departamento_id | INT | FK departamento |
| codigo | VARCHAR(20) | DEV-BACK, QA-SR |
| nombre | VARCHAR(100) | Developer Backend |
| descripcion | TEXT | Descripción del puesto |
| nivel_jerarquico | INT | 1-4 |
| reporta_a_puesto_id | INT | FK puesto superior |
| capacidad_horas_semana | INT | Horas semanales esperadas |
| requiere_qa | TINYINT | ¿Tareas pasan por QA? |
| activo | TINYINT | 1=activo |
| created_at | DATETIME | Fecha creación |

### 5.5 Tabla: wp_ga_puestos_escalas

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| puesto_id | INT | FK puesto |
| anio_antiguedad | INT | 1, 2, 3, 4, 5 |
| tarifa_hora | DECIMAL(10,2) | Tarifa para ese año |
| incremento_porcentaje | DECIMAL(5,2) | % incremento vs anterior |
| requiere_aprobacion_jefe | TINYINT | 1=sí |
| requiere_aprobacion_director | TINYINT | 1=sí |
| activo | TINYINT | 1=activo |

---

## 6. SISTEMA DE SUPERVISIÓN FLEXIBLE

### 6.1 Concepto

La supervisión NO es automática por departamento. Se configura manualmente para permitir:
- Un director que supervise jefes de múltiples departamentos
- Un director que supervise solo algunos jefes de un departamento
- Crecimiento flexible de la estructura

### 6.2 Tabla: wp_ga_supervisiones

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| supervisor_id | BIGINT | FK usuario que supervisa |
| supervisado_id | BIGINT | FK usuario supervisado |
| tipo_supervision | ENUM | DIRECTA, PROYECTO, DEPARTAMENTO |
| proyecto_id | INT | FK proyecto (si aplica) |
| departamento_id | INT | FK departamento (si aplica) |
| fecha_inicio | DATE | Desde cuándo |
| fecha_fin | DATE | Hasta cuándo (null=vigente) |
| activo | TINYINT | 1=activo |

### 6.3 Configuración de Supervisión

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⚙️ CONFIGURAR SUPERVISIÓN - Deiby Villalobos                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ NIVEL JERÁRQUICO:                                                       │
│ ○ Nivel 1: Socio/Dueño (ve todo)                                       │
│ ● Nivel 2: Director / Jefe de Jefes                                    │
│ ○ Nivel 3: Jefe de Proyecto / Área                                     │
│ ○ Nivel 4: Empleado / Ejecutor                                         │
│                                                                         │
│ 📁 DEPARTAMENTOS QUE PUEDE VER:                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ☑️ Desarrollo                                                        ││
│ │ ☐ Soporte                                                            ││
│ │ ☐ Comercial                                                          ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 👥 JEFES QUE SUPERVISA DIRECTAMENTE:                                    │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ☑️ Hillary López (PM - Desarrollo)                                   ││
│ │ ☑️ Carlos Ruiz (PM - Desarrollo)                                     ││
│ │ ☑️ María García (PM - Desarrollo)                                    ││
│ │ ☐ Kelly Mora (Líder Soporte) ← No marcado                           ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 💰 COMISIONES:                                                          │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ☑️ Recibe comisión por horas de sus jefes: [$0.25/hora]             ││
│ │ ☑️ Recibe comisión de segundo nivel: [2%] del facturado             ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 6.4 Matriz de Visibilidad por Nivel

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    MATRIZ DE VISIBILIDAD POR NIVEL                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ QUIÉN          │ VE A             │ VE DATOS DE      │ VE FINANZAS     │
│ ───────────────┼──────────────────┼──────────────────┼─────────────────│
│ SOCIO          │ Todos            │ Todo             │ Todo            │
│ (Lincy)        │                  │                  │                 │
│ ───────────────┼──────────────────┼──────────────────┼─────────────────│
│ DIRECTOR       │ Sus jefes        │ Proyectos de     │ Consolidado     │
│ (Deiby)        │ marcados         │ sus jefes        │ de sus áreas    │
│ ───────────────┼──────────────────┼──────────────────┼─────────────────│
│ JEFE/PM        │ Solo su          │ Solo sus         │ Solo sus        │
│ (Hillary)      │ equipo           │ proyectos        │ proyectos       │
│ ───────────────┼──────────────────┼──────────────────┼─────────────────│
│ EMPLEADO       │ Según config     │ Sus tareas       │ Solo sus        │
│ (Juan)         │ de visibilidad   │                  │ ingresos        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

# PARTE 3: MÓDULOS CORE

---

## 7. GESTIÓN DE TAREAS Y SUBTAREAS

### 7.1 Estructura de Tareas

```
TAREA PRINCIPAL
├── Código: TASK-2024-0001
├── Nombre: "Desarrollo API Login"
├── Descripción detallada
├── Instrucciones: Texto O URL (Word Online, Google Docs, Video)
├── Horas estimadas: 8
├── Fecha límite: 20/12/2024
├── Asignado a: Juan Pérez
├── Supervisor: María (QA)
├── Aprobador: Hillary (PM)
│
├── SUBTAREA 1-1: Crear endpoint
│   ├── Tiempo estimado: 2 horas
│   ├── Instrucciones específicas
│   └── Estado: COMPLETADA
│
├── SUBTAREA 1-2: Validaciones
│   ├── Tiempo estimado: 1.5 horas
│   ├── Instrucciones específicas
│   └── Estado: COMPLETADA
│
├── SUBTAREA 1-3: Tests unitarios
│   ├── Tiempo estimado: 2 horas
│   └── Estado: EN_PROGRESO ← Timer activo
│
└── SUBTAREA 1-4: Documentación
    ├── Tiempo estimado: 1.5 horas
    └── Estado: PENDIENTE
```

### 7.2 Estados de Tarea

```
PENDIENTE ──► EN_PROGRESO ──► COMPLETADA ──► EN_REVISION ──► APROBADA
    │              │              │               │              │
    │              │              │               ▼              ▼
    │              │              │          RECHAZADA      PAGADA
    │              │              │               │
    ▼              ▼              ▼               │
 CANCELADA      PAUSADA      EN_QA ◄─────────────┘
                               │
                               ▼
                          APROBADA_QA
```

### 7.3 Tabla: wp_ga_catalogo_tareas

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| codigo | VARCHAR(20) | TASK-001 |
| nombre | VARCHAR(200) | Nombre de la tarea tipo |
| descripcion | TEXT | Descripción |
| departamento_id | INT | FK departamento |
| puesto_id | INT | FK puesto que la ejecuta |
| horas_estimadas | DECIMAL(10,2) | Tiempo base |
| frecuencia | ENUM | POR_SOLICITUD, DIARIA, SEMANAL, MENSUAL |
| frecuencia_dias | INT | Cada cuántos días (si aplica) |
| url_instrucciones | VARCHAR(500) | Link a documento/video |
| instrucciones_texto | TEXT | Instrucciones inline |
| flujo_revision | ENUM | DEFAULT_PUESTO, PERSONALIZADO |
| revisor_tipo | ENUM | NINGUNO, QA_DEPARTAMENTO, USUARIO_ESPECIFICO |
| revisor_usuario_id | BIGINT | FK usuario revisor |
| aprobador_tipo | ENUM | JEFE_DIRECTO, JEFE_DEPARTAMENTO, USUARIO_ESPECIFICO |
| requiere_segundo_aprobador | TINYINT | ¿Necesita director? |
| activo | TINYINT | 1=activo |

### 7.4 Tabla: wp_ga_tareas

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| numero | VARCHAR(20) | TASK-2024-0001 |
| catalogo_tarea_id | INT | FK catálogo (opcional) |
| nombre | VARCHAR(200) | Nombre específico |
| descripcion | TEXT | Descripción |
| proyecto_id | INT | FK proyecto |
| caso_id | INT | FK caso |
| asignado_a | BIGINT | FK usuario ejecutor |
| supervisor_id | BIGINT | FK usuario que revisa |
| aprobador_id | BIGINT | FK usuario que aprueba |
| horas_estimadas | DECIMAL(10,2) | Tiempo estimado |
| horas_reales | DECIMAL(10,2) | Tiempo real (calculado) |
| fecha_inicio | DATE | Inicio planificado |
| fecha_limite | DATE | Fecha límite |
| fecha_completada | DATETIME | Cuándo se completó |
| estado | ENUM | (ver estados arriba) |
| prioridad | ENUM | BAJA, MEDIA, ALTA, URGENTE |
| url_instrucciones | VARCHAR(500) | Link instrucciones |
| instrucciones_texto | TEXT | Texto instrucciones |
| porcentaje_avance | INT | 0-100 (calculado de subtareas) |
| created_by | BIGINT | Quién la creó |
| created_at | DATETIME | Fecha creación |

### 7.5 Tabla: wp_ga_subtareas

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| tarea_id | INT | FK tarea padre |
| codigo | VARCHAR(20) | 1-1, 1-2, 1-3 |
| nombre | VARCHAR(200) | Nombre del paso |
| descripcion | TEXT | Descripción detallada |
| orden | INT | Orden de ejecución |
| horas_estimadas | DECIMAL(10,2) | Tiempo estimado |
| horas_reales | DECIMAL(10,2) | Tiempo real |
| estado | ENUM | PENDIENTE, EN_PROGRESO, COMPLETADA |
| fecha_inicio | DATETIME | Cuándo inició |
| fecha_fin | DATETIME | Cuándo terminó |
| notas | TEXT | Notas del ejecutor |

---

## 8. TIMER Y REGISTRO DE HORAS

### 8.1 Funcionamiento del Timer

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⏱️ TIMER ACTIVO - Subtarea: Tests unitarios                             │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                                                                 │   │
│  │                        01:23:45                                 │   │
│  │                                                                 │   │
│  │             [⏸️ Pausar]    [⏹️ Detener]                         │   │
│  │                                                                 │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  Tarea: TASK-2024-0001 - Desarrollo API Login                          │
│  Subtarea: 1-3 Tests unitarios                                          │
│  Estimado: 2 horas │ Transcurrido: 1h 23m                              │
│                                                                         │
│  ⚠️ Al pausar, se te pedirá el motivo                                   │
│  ⚠️ Solo puede haber UN timer activo a la vez                          │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 8.2 Reglas del Timer

1. **Solo UN timer activo** a la vez por usuario
2. **Al pausar** se requiere motivo (almuerzo, reunión, emergencia, etc.)
3. **Al detener** se guarda el registro completo
4. **Tiempo mínimo** de 5 minutos para registrar
5. **Alertas** cuando el tiempo supera el estimado
6. **Verificación opcional** con Time Doctor (Fase 2)

### 8.3 Tabla: wp_ga_registro_horas

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| usuario_id | BIGINT | FK usuario |
| tarea_id | INT | FK tarea |
| subtarea_id | INT | FK subtarea (opcional) |
| proyecto_id | INT | FK proyecto |
| contrato_trabajo_id | INT | FK contrato (para tarifa) |
| fecha | DATE | Fecha del registro |
| hora_inicio | DATETIME | Inicio del timer |
| hora_fin | DATETIME | Fin del timer |
| minutos_totales | INT | Minutos trabajados |
| minutos_pausas | INT | Minutos en pausa |
| minutos_efectivos | INT | Totales - Pausas |
| descripcion | TEXT | Qué se hizo |
| estado | ENUM | BORRADOR, ENVIADO, APROBADO_QA, APROBADO, RECHAZADO, PAGADO |
| aprobado_qa_por | BIGINT | FK usuario QA |
| fecha_aprobacion_qa | DATETIME | Cuándo aprobó QA |
| aprobado_por | BIGINT | FK usuario jefe |
| fecha_aprobacion | DATETIME | Cuándo aprobó jefe |
| motivo_rechazo | TEXT | Si fue rechazado |
| tarifa_hora | DECIMAL(10,2) | Tarifa al momento |
| monto_calculado | DECIMAL(12,2) | Horas × tarifa |
| incluido_en_cobro_id | INT | FK solicitud de cobro |
| created_at | DATETIME | Fecha creación |

### 8.4 Tabla: wp_ga_pausas_timer

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| registro_hora_id | INT | FK registro |
| hora_pausa | DATETIME | Inicio de pausa |
| hora_reanudacion | DATETIME | Fin de pausa |
| minutos | INT | Duración |
| motivo | ENUM | ALMUERZO, REUNION, EMERGENCIA, DESCANSO, OTRO |
| nota | VARCHAR(200) | Detalle opcional |

---

## 9. FLUJO DE REVISIÓN CONFIGURABLE

### 9.1 Tipos de Flujo

```
FLUJO 1: SOLO JEFE (Tareas simples)
═══════════════════════════════════
Empleado ──► Completa ──► Jefe Aprueba ──► PAGADO

FLUJO 2: QA + JEFE (Tareas técnicas)
════════════════════════════════════
Empleado ──► Completa ──► QA Revisa ──► Jefe Aprueba ──► PAGADO
                              │
                              └──► Rechaza (vuelve a empleado)

FLUJO 3: QA + JEFE + DIRECTOR (Tareas críticas)
═══════════════════════════════════════════════
Empleado ──► Completa ──► QA ──► Jefe ──► Director ──► PAGADO
```

### 9.2 Configuración por Tarea

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 🔍 FLUJO DE REVISIÓN - Tarea: Conciliación Bancaria                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ ☐ Usar configuración del puesto (default)                              │
│ ● Configuración específica para esta tarea:                            │
│                                                                         │
│ PASO 1 - ¿Quién revisa después de completar?                           │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ○ Nadie (va directo al jefe)                                        ││
│ │ ● QA / Auditor del departamento                                     ││
│ │ ○ Supervisor específico: [Seleccionar ▼]                            ││
│ │ ○ Par (compañero del mismo nivel)                                   ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ PASO 2 - ¿Quién aprueba para pago?                                     │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ● Jefe directo del empleado                                         ││
│ │ ○ Jefe del departamento                                             ││
│ │ ○ Usuario específico: [Seleccionar ▼]                               ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ PASO 3 - ¿Requiere segundo aprobador?                                  │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ☑️ Sí, requiere aprobación del Director                             ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 10. SISTEMA DE CASOS/EXPEDIENTES

### 10.1 Concepto

Un **caso** es un contenedor de trabajo para un cliente. Puede ser:
- Un proyecto de desarrollo
- Un caso legal
- Una campaña de marketing
- Un ticket de soporte escalado

### 10.2 Numeración Automática

```
Formato: CASO-[CLIENTE]-[AÑO]-[CONSECUTIVO]

Ejemplos:
• CASO-ABC-2024-0001 (Cliente ABC, primer caso del 2024)
• CASO-XYZ-2024-0015 (Cliente XYZ, caso 15 del 2024)
```

### 10.3 Tabla: wp_ga_casos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| numero | VARCHAR(30) | CASO-ABC-2024-0001 |
| cliente_id | INT | FK cliente |
| titulo | VARCHAR(200) | Título del caso |
| descripcion | TEXT | Descripción completa |
| tipo | ENUM | PROYECTO, LEGAL, SOPORTE, CONSULTORIA, OTRO |
| estado | ENUM | ABIERTO, EN_PROGRESO, EN_ESPERA, CERRADO, CANCELADO |
| prioridad | ENUM | BAJA, MEDIA, ALTA, URGENTE |
| fecha_apertura | DATE | Cuándo se abrió |
| fecha_cierre_estimada | DATE | Fecha límite |
| fecha_cierre_real | DATETIME | Cuándo se cerró |
| responsable_id | BIGINT | FK usuario principal |
| presupuesto_horas | INT | Horas vendidas |
| presupuesto_dinero | DECIMAL(12,2) | Monto vendido |
| horas_consumidas | DECIMAL(10,2) | Horas usadas (calculado) |
| costo_interno | DECIMAL(12,2) | Costo acumulado |
| porcentaje_avance | INT | 0-100 |
| created_by | BIGINT | Quién lo creó |
| created_at | DATETIME | Fecha creación |

### 10.4 Estados del Caso

```
ABIERTO ──► EN_PROGRESO ──► EN_ESPERA ──► EN_PROGRESO ──► CERRADO
    │            │              │                            │
    │            │              │                            │
    ▼            ▼              ▼                            ▼
CANCELADO   (trabajando)  (esperando    (retoma)        (completado
                           cliente)                      exitosamente)

---

# PARTE 4: PORTAL DE CLIENTES

---

## 11. PORTAL CLIENTE - ACCESO

### 11.1 Autenticación

Los clientes acceden con credenciales propias a un portal donde pueden:
- Ver progreso de sus proyectos
- Revisar facturas y pagos
- Firmar documentos digitalmente
- Agendar reuniones
- Crear solicitudes

### 11.2 Vista de Login

```
┌─────────────────────────────────────────────────────────────────────────┐
│                                                                         │
│                         🏢 PORTAL DE CLIENTES                           │
│                            [NOMBRE EMPRESA]                             │
│                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │  Email: [________________________________]                          ││
│ │                                                                     ││
│ │  Contraseña: [________________________________]                     ││
│ │                                                                     ││
│ │  [🔑 Iniciar Sesión]                                                ││
│ │                                                                     ││
│ │  ¿Olvidaste tu contraseña? [Recuperar acceso]                       ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 11.3 Tabla: wp_ga_clientes

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| usuario_wp_id | BIGINT | FK wp_users (para login) |
| codigo | VARCHAR(20) | CLI-001 |
| tipo | ENUM | PERSONA_NATURAL, EMPRESA |
| nombre_comercial | VARCHAR(200) | Nombre comercial |
| razon_social | VARCHAR(200) | Razón social (si empresa) |
| documento_tipo | VARCHAR(20) | NIT, CC, RFC |
| documento_numero | VARCHAR(50) | Número |
| email | VARCHAR(200) | Email principal |
| telefono | VARCHAR(50) | Teléfono |
| pais | VARCHAR(2) | Código ISO |
| ciudad | VARCHAR(100) | Ciudad |
| direccion | TEXT | Dirección |
| regimen_fiscal | VARCHAR(50) | Régimen tributario |
| retencion_default | DECIMAL(5,2) | % retención por defecto |
| contacto_nombre | VARCHAR(200) | Nombre del contacto |
| contacto_cargo | VARCHAR(100) | Cargo |
| contacto_email | VARCHAR(200) | Email contacto |
| contacto_telefono | VARCHAR(50) | Teléfono contacto |
| activo | TINYINT | 1=activo |
| created_at | DATETIME | Fecha creación |

---

## 12. PORTAL CLIENTE - PROYECTOS

### 12.1 Dashboard del Cliente

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 🏢 PORTAL CLIENTE - ABC Corporation                                     │
│ Bienvenido, Juan García                                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ [📊 Proyectos] [📄 Facturas] [📝 Acuerdos] [📅 Reuniones] [✉️ Soporte] │
│                                                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📊 MIS PROYECTOS ACTIVOS                                                │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │ 📱 APP MÓVIL - FASE 2                                               ││
│ │ ─────────────────────────────────────────────────────────────────── ││
│ │ Progreso general: ████████████████░░░░ 78%                          ││
│ │                                                                     ││
│ │ Sprint 3: Login y Autenticación                                     ││
│ │ ├── ✅ Diseño UI (Completado)                                       ││
│ │ ├── ✅ API Backend (Completado)                                     ││
│ │ ├── 🔄 Integración (En progreso - 60%)                              ││
│ │ └── ⏳ Testing (Pendiente)                                          ││
│ │                                                                     ││
│ │ Equipo asignado: 3 personas │ PM: Hillary López                     ││
│ │ Próxima entrega: 20 Dic 2024                                        ││
│ │                                                                     ││
│ │ [Ver detalle completo]                                              ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 💰 RESUMEN FINANCIERO                                                   │
│ ┌────────────────┬────────────────┬────────────────┬────────────────┐  │
│ │ Contratado     │ Facturado      │ Pagado         │ Pendiente      │  │
│ │ $15,000        │ $12,000        │ $10,000        │ $2,000         │  │
│ └────────────────┴────────────────┴────────────────┴────────────────┘  │
│                                                                         │
│ 📄 DOCUMENTOS PENDIENTES DE FIRMA                                       │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ⚠️ Adenda - Ampliación módulo reportes │ Vence: 20 Dic │ [Firmar]  ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 📅 PRÓXIMAS REUNIONES                                                   │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ 📅 15 Dic 10:00 │ Demo Sprint 3 │ [Unirse a Google Meet]            ││
│ │ 📅 22 Dic 15:00 │ Revisión mensual │ [Unirse a Zoom]                ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 12.2 Crear Solicitud (Cliente)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ✉️ CREAR NUEVA SOLICITUD                                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ Tipo de solicitud*: [Seleccionar ▼]                                    │
│                     ├── 🐛 Reportar problema                           │
│                     ├── ➕ Solicitar nueva función                     │
│                     ├── ❓ Consulta general                            │
│                     ├── 📅 Agendar reunión                             │
│                     └── 📄 Solicitar documento                         │
│                                                                         │
│ Proyecto relacionado: [App Móvil - Fase 2 ▼]                           │
│                                                                         │
│ Asunto*: [________________________________]                             │
│                                                                         │
│ Descripción*:                                                           │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │                                                                     ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ Prioridad: ○ Baja  ● Normal  ○ Alta  ○ Urgente                         │
│                                                                         │
│ Adjuntos: [📎 Agregar archivos]                                        │
│                                                                         │
│                                          [Cancelar] [📤 Enviar]        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 13. FIRMA DIGITAL

### 13.1 Proceso de Firma (Estilo wolksoft.com/firma-pdf/)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ✍️ FIRMAR DOCUMENTO                                                     │
│ Contrato de Servicios - Desarrollo App Móvil                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📄 DOCUMENTO A FIRMAR                                                   │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │  [Vista previa del PDF embebido]                                    ││
│ │                                                                     ││
│ │  Página 1 de 3    [◀] [▶]    [🔍+] [🔍-]                           ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 👤 DATOS DEL FIRMANTE                                                   │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Nombre completo*: [Juan García Rodríguez___________]                ││
│ │ Correo electrónico*: [juan.garcia@abccorp.com______]                ││
│ │ Documento ID: [CC 123456789] (de su perfil)                         ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ ⚙️ POSICIÓN DE FIRMA                                                    │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │  [◀ Izquierda]     [📍 Centro]     [Derecha ▶]                      ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ ✍️ TU FIRMA (Dibuja con el dedo o mouse)                                │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │  ┌─────────────────────────────────────────────────────────────┐   ││
│ │  │                                                             │   ││
│ │  │                    Firma aquí ✍️                             │   ││
│ │  │                                                             │   ││
│ │  └─────────────────────────────────────────────────────────────┘   ││
│ │                                                                     ││
│ │                         [🗑️ Borrar firma]                           ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ ☑️ He leído el documento completo                                       │
│ ☑️ Mi firma digital tiene validez legal                                 │
│                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │  [📥 Firmar y Descargar]     [📤 Firmar y Enviar]                   ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 🔒 Se registrará: fecha, hora, IP y hash de verificación               │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 13.2 Tabla: wp_ga_firmas_documentos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| documento_tipo | VARCHAR(50) | contrato, acuerdo, nda, orden_pago |
| documento_id | INT | ID del documento |
| url_documento_original | VARCHAR(500) | PDF sin firmar |
| url_documento_firmado | VARCHAR(500) | PDF con firma |
| firmante_tipo | ENUM | APLICANTE, EMPLEADO, CLIENTE, EMPRESA |
| firmante_id | INT | ID del firmante |
| firmante_nombre | VARCHAR(200) | Nombre como apareció |
| firmante_email | VARCHAR(200) | Email |
| firmante_documento | VARCHAR(50) | CC/NIT |
| firma_imagen_url | VARCHAR(500) | Imagen de la firma |
| posicion_firma | ENUM | IZQUIERDA, CENTRO, DERECHA |
| ip_firma | VARCHAR(45) | IP desde donde firmó |
| user_agent | VARCHAR(500) | Navegador/dispositivo |
| hash_documento | VARCHAR(100) | Hash SHA256 del PDF |
| fecha_firma | DATETIME | Momento exacto |
| latitud | DECIMAL(10,8) | Geolocalización |
| longitud | DECIMAL(11,8) | Geolocalización |
| created_at | DATETIME | Fecha registro |

---

# PARTE 5: PORTAL DE ÓRDENES DE TRABAJO (Marketplace)

---

## 14. CONCEPTO MARKETPLACE

### 14.1 Visión

> "Somos como un Uber o Freelancer.com pero ordenado. Cualquier persona o empresa puede ver nuestras órdenes de trabajo disponibles, aplicar, y si son aceptados, trabajar de forma estructurada y cobrar transparentemente."

### 14.2 Flujo General

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DEL PORTAL DE ÓRDENES DE TRABAJO               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  EMPRESA PUBLICA                       APLICANTE                        │
│  ═══════════════                       ═════════                        │
│                                                                         │
│  1. Crear orden de trabajo             │                                │
│     • Descripción del trabajo          │                                │
│     • Requisitos                       │                                │
│     • Tarifa ofrecida                  │                                │
│     • Duración estimada                │                                │
│              │                         │                                │
│              ▼                         │                                │
│  2. Publicar en portal ────────────────┼──► 3. Ve la orden              │
│                                        │       • Lee requisitos         │
│                                        │       • Revisa tarifa          │
│                                        │                                │
│                                        │                                │
│  4. Recibe aplicaciones ◄──────────────┼─── 4. Aplica a la orden        │
│     • Revisa perfiles                  │       • Primera vez: Registro  │
│     • Verifica documentos              │       • Ya tiene cuenta: Login │
│              │                         │                                │
│              ▼                         │                                │
│  5. Acepta/Rechaza ────────────────────┼──► 6. Recibe notificación      │
│              │                         │                                │
│              ▼                         ▼                                │
│  7. Genera contrato ◄──────────────────┼─── 8. Firma contrato           │
│              │                         │                                │
│              ▼                         ▼                                │
│  9. Asigna tareas ─────────────────────┼──► 10. Trabaja con timer       │
│              │                         │                                │
│              ▼                         ▼                                │
│  11. Aprueba trabajo ◄─────────────────┼─── 12. Completa y pide COBRAR  │
│              │                         │                                │
│              ▼                         ▼                                │
│  13. Contabilidad paga ────────────────┼──► 14. Recibe pago ✅          │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 14.3 Tipos de Aplicantes

| Tipo | Documentos Requeridos | Ejemplo |
|------|----------------------|---------|
| **Persona Natural** | Cédula (frente y reverso), CV | Freelancer, desarrollador |
| **Empresa** | Cámara de comercio, RUT | Consultora, agencia |

---

## 15. REGISTRO DE APLICANTES

### 15.1 Flujo de Registro

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE APLICACIÓN A ORDEN DE TRABAJO               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ¿Ya tiene cuenta en el sistema?                                        │
│                                                                         │
│  ┌─────────────────────────┐    ┌─────────────────────────┐            │
│  │  🆕 SOY NUEVO           │    │  🔑 YA TENGO CUENTA     │            │
│  │  Primera vez aplicando  │    │  Iniciar sesión         │            │
│  └───────────┬─────────────┘    └───────────┬─────────────┘            │
│              │                              │                           │
│              ▼                              ▼                           │
│  ┌─────────────────────────┐    ┌─────────────────────────┐            │
│  │ REGISTRO COMPLETO       │    │ LOGIN                   │            │
│  │ • Datos personales      │    │ • Email                 │            │
│  │ • Documentos (ID/Cámara)│    │ • Contraseña            │            │
│  │ • Crear contraseña      │    │ • ¿Olvidó contraseña?   │            │
│  └───────────┬─────────────┘    └───────────┬─────────────┘            │
│              │                              │                           │
│              ▼                              ▼                           │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                    APLICAR A ORDEN DE TRABAJO                    │   │
│  │  ✅ Datos ya cargados                                            │   │
│  │  ✅ Documentos ya subidos                                        │   │
│  │  ☐ Aceptar acuerdos de esta orden                               │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 15.2 Formulario Registro - Persona Natural

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 🆕 REGISTRO - PERSONA NATURAL                                           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📋 DATOS PERSONALES                                                     │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Nombre completo*: [________________________________]                 ││
│ │ Tipo documento*: [Cédula ▼]                                         ││
│ │ Número documento*: [________________________________]                ││
│ │ Email*: [________________________________]                           ││
│ │ Teléfono*: [________________________________]                        ││
│ │ Ciudad/País*: [________________________________]                     ││
│ │ LinkedIn: [________________________________]                         ││
│ │ Portafolio web: [________________________________]                   ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 🪪 DOCUMENTO DE IDENTIDAD                                    Requerido │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │  📄 FRENTE del documento                                            ││
│ │  ┌─────────────────────────────────────────────────────────────┐   ││
│ │  │  [📷 Tomar foto]  o  [📁 Seleccionar archivo]               │   ││
│ │  └─────────────────────────────────────────────────────────────┘   ││
│ │                                                                     ││
│ │  📄 REVERSO del documento                                           ││
│ │  ┌─────────────────────────────────────────────────────────────┐   ││
│ │  │  [📷 Tomar foto]  o  [📁 Seleccionar archivo]               │   ││
│ │  └─────────────────────────────────────────────────────────────┘   ││
│ │                                                                     ││
│ │  📄 Hoja de vida / CV (opcional)                                    ││
│ │  ┌─────────────────────────────────────────────────────────────┐   ││
│ │  │  [📁 Seleccionar archivo PDF]                               │   ││
│ │  └─────────────────────────────────────────────────────────────┘   ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 🔐 CREAR CUENTA                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Contraseña*: [________________________________]                      ││
│ │ Confirmar*: [________________________________]                       ││
│ │ ☐ Acepto los términos de uso y política de privacidad              ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│                                    [Cancelar] [📤 Registrarme y Aplicar]│
└─────────────────────────────────────────────────────────────────────────┘
```

### 15.3 Formulario Registro - Empresa

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 🆕 REGISTRO - EMPRESA / PERSONA JURÍDICA                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 🏢 DATOS DE LA EMPRESA                                                  │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Razón Social*: [________________________________]                    ││
│ │ Nombre Comercial: [________________________________]                 ││
│ │ NIT/RUT/RFC*: [________________________________]                     ││
│ │ Email empresa*: [________________________________]                   ││
│ │ Teléfono*: [________________________________]                        ││
│ │ Sitio web: [________________________________]                        ││
│ │ Ciudad/País*: [________________________________]                     ││
│ │ Dirección: [________________________________]                        ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 👤 REPRESENTANTE LEGAL / CONTACTO                                       │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Nombre completo*: [________________________________]                 ││
│ │ Cargo*: [________________________________]                           ││
│ │ Email*: [________________________________]                           ││
│ │ Teléfono*: [________________________________]                        ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 📄 DOCUMENTOS LEGALES                                        Requerido │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │  📄 Cámara de Comercio / Personería Jurídica*                       ││
│ │  ┌─────────────────────────────────────────────────────────────┐   ││
│ │  │  [📁 Seleccionar archivo PDF]                               │   ││
│ │  └─────────────────────────────────────────────────────────────┘   ││
│ │                                                                     ││
│ │  📄 RUT / Registro Tributario (opcional)                            ││
│ │  ┌─────────────────────────────────────────────────────────────┐   ││
│ │  │  [📁 Seleccionar archivo PDF]                               │   ││
│ │  └─────────────────────────────────────────────────────────────┘   ││
│ │                                                                     ││
│ │  📄 Portafolio de servicios (opcional)                              ││
│ │  ┌─────────────────────────────────────────────────────────────┐   ││
│ │  │  [📁 Seleccionar archivo PDF]                               │   ││
│ │  └─────────────────────────────────────────────────────────────┘   ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 🔐 CREAR CUENTA                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Contraseña*: [________________________________]                      ││
│ │ Confirmar*: [________________________________]                       ││
│ │ ☐ Acepto los términos de uso y política de privacidad              ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│                                    [Cancelar] [📤 Registrarme y Aplicar]│
└─────────────────────────────────────────────────────────────────────────┘
```

### 15.4 Tabla: wp_ga_aplicantes

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| usuario_wp_id | BIGINT | FK wp_users (para login) |
| tipo | ENUM | PERSONA_NATURAL, EMPRESA |
| email | VARCHAR(200) | Email único (login) |
| password_hash | VARCHAR(255) | Contraseña hasheada |
| --- | --- | **PERSONA NATURAL** |
| nombre_completo | VARCHAR(200) | Nombre |
| documento_tipo | VARCHAR(20) | CC, CE, Pasaporte |
| documento_numero | VARCHAR(50) | Número |
| telefono | VARCHAR(50) | Teléfono |
| ciudad | VARCHAR(100) | Ciudad |
| pais | VARCHAR(100) | País |
| linkedin | VARCHAR(500) | Perfil LinkedIn |
| portafolio_url | VARCHAR(500) | Web personal |
| cv_url | VARCHAR(500) | URL del CV |
| url_documento_frente | VARCHAR(500) | Foto ID frente |
| url_documento_reverso | VARCHAR(500) | Foto ID reverso |
| --- | --- | **EMPRESA** |
| razon_social | VARCHAR(200) | Razón social |
| nombre_comercial | VARCHAR(200) | Nombre comercial |
| nit_rut | VARCHAR(50) | NIT/RUT/RFC |
| sitio_web | VARCHAR(500) | Web |
| direccion | TEXT | Dirección |
| contacto_nombre | VARCHAR(200) | Representante |
| contacto_cargo | VARCHAR(100) | Cargo |
| contacto_email | VARCHAR(200) | Email contacto |
| contacto_telefono | VARCHAR(50) | Tel contacto |
| url_camara_comercio | VARCHAR(500) | PDF Cámara |
| url_rut | VARCHAR(500) | PDF RUT |
| url_portafolio_servicios | VARCHAR(500) | PDF Portafolio |
| --- | --- | **VERIFICACIÓN** |
| documentos_verificados | TINYINT | ¿Admin verificó? |
| verificado_por | BIGINT | Quién verificó |
| fecha_verificacion | DATETIME | Cuándo |
| --- | --- | **CONTROL** |
| activo | TINYINT | 1=activo |
| created_at | DATETIME | Registro |
| updated_at | DATETIME | Última modificación |

---

## 16. FLUJO DE APLICACIÓN

### 16.1 Vista de Órdenes Disponibles

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 📋 ÓRDENES DE TRABAJO DISPONIBLES                                       │
│ [NOMBRE EMPRESA]                                                        │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ "¿Eres una persona o empresa que brindará servicios a [NOMBRE]?        │
│  Aplica aquí a nuestras ofertas de servicio disponibles."              │
│                                                                         │
│ Filtrar: [Todas las categorías ▼]  [Cualquier duración ▼]              │
│                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ 💻 DEVELOPER BACKEND - NODE.JS                                      ││
│ │ ─────────────────────────────────────────────────────────────────── ││
│ │ Descripción: Desarrollo de APIs REST para aplicación móvil         ││
│ │                                                                     ││
│ │ Requisitos:                                                         ││
│ │ • 3+ años de experiencia en Node.js                                 ││
│ │ • Conocimiento de MongoDB y PostgreSQL                              ││
│ │ • Inglés intermedio                                                 ││
│ │                                                                     ││
│ │ 💰 Tarifa: $8-12/hora │ ⏱️ Duración: 3 meses │ 📍 Remoto           ││
│ │ 📅 Publicado: 10 Dic 2024 │ Aplicantes: 5                          ││
│ │                                                                     ││
│ │                                                    [📤 APLICAR]     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ⚖️ ABOGADO LABORAL - CASO ESPECÍFICO                                ││
│ │ ─────────────────────────────────────────────────────────────────── ││
│ │ Descripción: Representación en caso de despido injustificado        ││
│ │                                                                     ││
│ │ Requisitos:                                                         ││
│ │ • Tarjeta profesional vigente                                       ││
│ │ • Experiencia en derecho laboral                                    ││
│ │                                                                     ││
│ │ 💰 Tarifa: $25/hora │ ⏱️ Duración: 1-2 meses │ 📍 Híbrido          ││
│ │ 📅 Publicado: 8 Dic 2024 │ Aplicantes: 2                           ││
│ │                                                                     ││
│ │                                                    [📤 APLICAR]     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 16.2 Tabla: wp_ga_ordenes_trabajo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| codigo | VARCHAR(20) | OT-2024-001 |
| titulo | VARCHAR(200) | Título del puesto |
| descripcion | TEXT | Descripción completa |
| requisitos | TEXT | Lista de requisitos |
| departamento_id | INT | FK departamento |
| puesto_base_id | INT | FK puesto (plantilla) |
| tipo_contratacion | ENUM | MENSUAL, PROYECTO, CASO, POR_HORA |
| modalidad | ENUM | REMOTO, PRESENCIAL, HIBRIDO |
| tarifa_min | DECIMAL(10,2) | Tarifa mínima |
| tarifa_max | DECIMAL(10,2) | Tarifa máxima |
| moneda | VARCHAR(3) | USD, COP |
| duracion_estimada | VARCHAR(50) | "3 meses", "1 año" |
| horas_semana | INT | Horas semanales esperadas |
| fecha_publicacion | DATE | Cuándo se publicó |
| fecha_cierre | DATE | Hasta cuándo reciben |
| estado | ENUM | BORRADOR, PUBLICADA, CERRADA, CANCELADA |
| max_aplicantes | INT | Límite de aplicaciones |
| caso_id | INT | FK caso (si es para caso específico) |
| proyecto_id | INT | FK proyecto |
| responsable_id | BIGINT | Quién gestiona las aplicaciones |
| activo | TINYINT | 1=activo |
| created_by | BIGINT | Quién creó |
| created_at | DATETIME | Fecha creación |

### 16.3 Tabla: wp_ga_aplicaciones

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| orden_trabajo_id | INT | FK orden de trabajo |
| aplicante_id | INT | FK aplicante |
| fecha_aplicacion | DATETIME | Cuándo aplicó |
| carta_presentacion | TEXT | Mensaje del aplicante |
| tarifa_solicitada | DECIMAL(10,2) | Tarifa que pide |
| disponibilidad | VARCHAR(200) | "Inmediata", "2 semanas" |
| horas_disponibles | INT | Horas por semana |
| estado | ENUM | PENDIENTE, EN_REVISION, PRESELECCIONADO, ACEPTADO, RECHAZADO |
| notas_evaluador | TEXT | Notas internas |
| evaluado_por | BIGINT | Quién evaluó |
| fecha_evaluacion | DATETIME | Cuándo |
| contrato_generado_id | INT | FK contrato (si fue aceptado) |
| created_at | DATETIME | Fecha aplicación |

---

## 17. CONTRATOS MULTI-PROYECTO

### 17.1 Concepto

Un prestador puede tener **múltiples contratos activos** simultáneamente:
- Contrato 1: Developer en Proyecto ABC ($8/hora)
- Contrato 2: Consultor en Proyecto XYZ ($12/hora)
- Contrato 3: Soporte técnico ($6/hora)

### 17.2 Tabla: wp_ga_contratos_trabajo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| numero | VARCHAR(30) | CONT-2024-001 |
| aplicante_id | INT | FK aplicante |
| orden_trabajo_id | INT | FK orden original |
| puesto_id | INT | FK puesto asignado |
| proyecto_id | INT | FK proyecto |
| caso_id | INT | FK caso (si aplica) |
| tipo | ENUM | MENSUAL, PROYECTO, CASO, POR_HORA |
| tarifa_hora | DECIMAL(10,2) | Tarifa acordada |
| moneda | VARCHAR(3) | USD |
| horas_semana_acordadas | INT | Horas semanales |
| fecha_inicio | DATE | Inicio del contrato |
| fecha_fin | DATE | Fin (null=indefinido) |
| estado | ENUM | ACTIVO, PAUSADO, TERMINADO, CANCELADO |
| supervisor_id | BIGINT | FK jefe directo |
| --- | --- | **DOCUMENTOS** |
| url_contrato_pdf | VARCHAR(500) | Contrato firmado |
| url_nda_pdf | VARCHAR(500) | NDA firmado |
| fecha_firma_contrato | DATETIME | Cuándo firmó |
| fecha_firma_nda | DATETIME | Cuándo firmó NDA |
| --- | --- | **CONTROL** |
| created_by | BIGINT | Quién creó |
| created_at | DATETIME | Fecha creación |
| updated_at | DATETIME | Última modificación |
```


---

# PARTE 6: FACTURACIÓN Y COBROS

---

## 18. FACTURACIÓN POR PAÍS

### 18.1 Países Soportados

| País | Código | Moneda | Impuesto | Factura Electrónica |
|------|--------|--------|----------|---------------------|
| 🇺🇸 Estados Unidos | US | USD | Sales Tax (variable) | No requerida |
| 🇨🇴 Colombia | CO | COP/USD | IVA 19% | DIAN |
| 🇲🇽 México | MX | MXN/USD | IVA 16% | SAT (CFDI) |
| 🇨🇱 Chile | CL | CLP/USD | IVA 19% | SII |
| 🇵🇪 Perú | PE | PEN/USD | IGV 18% | SUNAT |
| 🇵🇦 Panamá | PA | PAB/USD | ITBMS 7% | DGI |
| 🇪🇸 España | ES | EUR | IVA 21% | AEAT |

### 18.2 Tabla: wp_ga_paises_config

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| codigo_iso | VARCHAR(2) | US, CO, MX |
| nombre | VARCHAR(100) | Nombre completo |
| moneda_codigo | VARCHAR(3) | USD, COP |
| moneda_simbolo | VARCHAR(5) | $, € |
| impuesto_nombre | VARCHAR(50) | IVA, Sales Tax |
| impuesto_porcentaje | DECIMAL(5,2) | 19, 16, 7 |
| retencion_default | DECIMAL(5,2) | % retención |
| formato_factura | VARCHAR(20) | Formato número |
| requiere_electronica | TINYINT | ¿Factura electrónica? |
| proveedor_electronica | VARCHAR(50) | DIAN, SAT, SII |
| activo | TINYINT | 1=activo |

---

## 19. FLUJO SOLICITUD DE FACTURA

### 19.1 Proceso

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE FACTURACIÓN - PROCESO ACTUAL                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  GESTIONADMIN                      CONTABILIDAD                WOLK POS │
│  ═════════════                     ════════════                ════════ │
│                                                                         │
│  PM/Jefe crea                                                           │
│  orden de pago ───► Solicitud ───► Contador revisa ───► Replica en POS │
│                                    y valida datos                       │
│       │                                  │                     │        │
│       │                                  │                     ▼        │
│       │                                  │              Factura elect.  │
│       │                                  │              DIAN/SAT OK     │
│       │                                  │                     │        │
│       │                                  │          ┌──────────┘        │
│       │                                  │          │                   │
│       │                                  ▼          ▼                   │
│  Estado:              ◄──────── Completa datos:                         │
│  FACTURADA                      • # Doc POS                             │
│                                 • Consecutivo                           │
│                                 • PDF/XML                               │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 19.2 Estados de Orden de Pago

```
BORRADOR ──► SOLICITUD_FACTURA ──► EN_PROCESO_POS ──► FACTURADA ──► PAGADA
                  │                      │                 │
                  ▼                      ▼                 ▼
             RECHAZADA            RECHAZADA_POS        VENCIDA
             (contador)           (error en POS)
```

### 19.3 Vista PM - Solicitar Factura

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 📄 CREAR SOLICITUD DE FACTURA                                           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📋 CLIENTE                                                              │
│ Cliente*: [ABC Corporation ▼]                                          │
│ Proyecto/Caso: [App Móvil - Sprint 3 ▼]                                │
│                                                                         │
│ 🌍 DATOS DE FACTURACIÓN                                                 │
│ País*: [🇨🇴 Colombia ▼]  Moneda*: [USD ▼]                               │
│                                                                         │
│ ⚠️ Configuración del cliente:                                          │
│    • NIT: 900.123.456-7 │ Régimen: Responsable IVA                     │
│    • Retención: 11% (Servicios)                                        │
│                                                                         │
│ 📝 CONCEPTOS                                                            │
│ ┌───────────────────────────────────────┬──────┬─────────┬────────────┐│
│ │ CONCEPTO                              │ CANT │ PRECIO  │ TOTAL      ││
│ │───────────────────────────────────────┼──────┼─────────┼────────────││
│ │ Desarrollo App Móvil - Sprint 3       │ 80   │ $15.00  │ $1,200.00  ││
│ │ QA y Testing                          │ 20   │ $12.00  │ $240.00    ││
│ │───────────────────────────────────────┼──────┼─────────┼────────────││
│ │                                       │      │Subtotal │ $1,440.00  ││
│ │                                       │      │IVA 19%  │ $273.60    ││
│ │                                       │      │TOTAL    │ $1,713.60  ││
│ │                                       │      │Ret.11%  │ -$158.40   ││
│ │                                       │      │A COBRAR │ $1,555.20  ││
│ └───────────────────────────────────────┴──────┴─────────┴────────────┘│
│                                                                         │
│ 💬 Notas para contabilidad:                                             │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Facturar antes del 15. Concepto: "Servicios de desarrollo".        ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│                        [💾 Borrador]  [📤 ENVIAR A CONTABILIDAD]       │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 19.4 Vista Contabilidad - Procesar Solicitud

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 🧾 PROCESAR SOLICITUD - SOL-2024-089                                    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📋 DATOS DE LA SOLICITUD (Solo lectura)                                 │
│ Cliente: ABC Corporation S.A.S │ NIT: 900.123.456-7                    │
│ País: 🇨🇴 Colombia │ Solicitado por: Hillary López                      │
│                                                                         │
│ Total: $1,713.60 │ Retención: $158.40 │ A cobrar: $1,555.20            │
│                                                                         │
│ ═══════════════════════════════════════════════════════════════════════│
│                                                                         │
│ 📝 COMPLETAR DATOS DEL POS                                              │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │ # Documento POS*:     [POS-2024-001542___________]                  ││
│ │                                                                     ││
│ │ Consecutivo DIAN*:    [SETP990000845______________]                 ││
│ │                                                                     ││
│ │ Fecha emisión*:       [12/12/2024]                                  ││
│ │ Fecha vencimiento*:   [12/01/2025]                                  ││
│ │                                                                     ││
│ │ 📄 Archivos:                                                        ││
│ │ PDF factura*:  [📁 Seleccionar] factura.pdf  ✅                     ││
│ │ XML firmado*:  [📁 Seleccionar] factura.xml  ✅                     ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│        [❌ Rechazar]    [💾 Guardar]    [✅ COMPLETAR FACTURA]         │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 20. INTEGRACIÓN CON POS

### 20.1 Fase 1: Manual (Actual)

- PM solicita factura en GestionAdmin
- Contador replica manualmente en Wolk POS
- Contador registra # de documento y consecutivo en GestionAdmin
- Contador sube PDF y XML

### 20.2 Fase 2: Automático (Futuro)

```
GestionAdmin ───API POST───► Wolk POS ───► Factura electrónica
      │                           │
      │◄──────Webhook─────────────┘
      │
      └──► Estado actualizado automáticamente
```

### 20.3 Tabla: wp_ga_ordenes_pago

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| numero_interno | VARCHAR(20) | SOL-2024-089 |
| cliente_id | INT | FK cliente |
| caso_id | INT | FK caso |
| proyecto_id | INT | FK proyecto |
| tipo | ENUM | SOLICITUD, FACTURA, NOTA_CREDITO |
| estado | ENUM | BORRADOR, SOLICITUD_FACTURA, EN_PROCESO_POS, FACTURADA, RECHAZADA, PAGADA, VENCIDA, PARCIAL |
| --- | --- | **FACTURACIÓN** |
| pais_facturacion | VARCHAR(2) | CO, US, MX |
| moneda | VARCHAR(3) | USD, COP |
| tasa_cambio | DECIMAL(10,4) | Tasa al momento |
| --- | --- | **DATOS POS** |
| numero_documento_pos | VARCHAR(50) | POS-2024-001542 |
| consecutivo_factura | VARCHAR(100) | SETP990000845 |
| cufe_uuid | VARCHAR(200) | Código único |
| url_pdf_factura | VARCHAR(500) | PDF firmado |
| url_xml_factura | VARCHAR(500) | XML firmado |
| fecha_emision_pos | DATE | Fecha en POS |
| --- | --- | **MONTOS** |
| subtotal | DECIMAL(12,2) | Sin impuestos |
| impuesto_porcentaje | DECIMAL(5,2) | % |
| impuesto_monto | DECIMAL(12,2) | Monto |
| total_facturado | DECIMAL(12,2) | Con impuesto |
| retencion_porcentaje | DECIMAL(5,2) | % |
| retencion_monto | DECIMAL(12,2) | Monto |
| total_a_cobrar | DECIMAL(12,2) | Neto |
| monto_cobrado | DECIMAL(12,2) | Pagado |
| --- | --- | **COSTOS** |
| costo_interno | DECIMAL(12,2) | Costo horas |
| comisiones_total | DECIMAL(12,2) | Comisiones |
| utilidad_neta | DECIMAL(12,2) | Utilidad |
| margen_porcentaje | DECIMAL(5,2) | % margen |
| --- | --- | **RESPONSABLES** |
| solicitado_por | BIGINT | PM/Jefe |
| procesado_por | BIGINT | Contador |
| fecha_procesado | DATETIME | Cuándo |
| --- | --- | **NOTAS** |
| notas_solicitante | TEXT | Del PM |
| notas_contabilidad | TEXT | Del contador |
| fecha_vencimiento | DATE | Vencimiento |
| created_at | DATETIME | Creación |

---

# PARTE 7: SISTEMA DE PAGOS A PRESTADORES

---

## 21. BOTÓN COBRAR

### 21.1 Concepto

Cuando un prestador (empleado, freelancer, empresa) tiene horas **aprobadas por QA y por el jefe**, se le habilita el botón **COBRAR** para solicitar el pago de su trabajo.

### 21.2 Flujo Completo

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUJO: PRESTADOR COBRA SU TRABAJO                    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  PRESTADOR            QA              JEFE           CONTABILIDAD       │
│  ══════════          ════            ══════          ════════════       │
│                                                                         │
│  Trabaja 80 hrs                                                         │
│  con timer      ──►  Revisa   ──►   Aprueba   ──►   [Botón habilitado]  │
│       │              trabajo         trabajo                │           │
│       │                                                     │           │
│       │◄──────────────────────────────────────────────────┘           │
│       │                                                                 │
│       │  Presiona [💰 COBRAR]                                           │
│       │                                                                 │
│       └──────────────────────────────────────────────────────────────► │
│                                                                         │
│                                                    Recibe solicitud     │
│                                                    Paga por Binance     │
│                                                    Sube comprobante     │
│                                                          │              │
│  Recibe pago ✅ ◄────────────────────────────────────────┘              │
│  Ve comprobante                                                         │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 21.3 Vista Empleado - Mis Horas Aprobadas

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 💰 MIS HORAS Y PAGOS - Juan Pérez                                       │
│ Tarifa: $5.00/hora │ Método preferido: Binance                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📊 RESUMEN DICIEMBRE 2024                                               │
│ ┌────────────────┬────────────────┬────────────────┬────────────────┐  │
│ │ Trabajadas     │ Aprobadas      │ Listas cobrar  │ Monto          │  │
│ │ 95 horas       │ 80 horas       │ 80 horas       │ $400.00        │  │
│ └────────────────┴────────────────┴────────────────┴────────────────┘  │
│                                                                         │
│ ✅ HORAS APROBADAS - LISTAS PARA COBRAR                                 │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ ☑️ Seleccionar todo                                                  ││
│ │                                                                     ││
│ │ ☑️ App Móvil ABC - Sprint 3 API (40 hrs × $5) = $200               ││
│ │    Aprobado: Hillary (PM) │ QA: María ✅                            ││
│ │                                                                     ││
│ │ ☑️ App Móvil ABC - Integración (25 hrs × $5) = $125                ││
│ │    Aprobado: Hillary (PM) │ QA: María ✅                            ││
│ │                                                                     ││
│ │ ☑️ Portal XYZ - Bugfixes (15 hrs × $5) = $75                       ││
│ │    Aprobado: Carlos (PM) │ QA: Ana ✅                               ││
│ │                                                                     ││
│ │─────────────────────────────────────────────────────────────────────││
│ │ TOTAL: 80 horas = $400.00 USD                                       ││
│ │                                                                     ││
│ │ Método de pago: [Binance (USDT) ▼]                                  ││
│ │ Binance ID: 123456789                                               ││
│ │                                                                     ││
│ │                    [💰 ENVIAR SOLICITUD DE COBRO]                   ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ ⏳ EN PROCESO (15 horas) - No disponibles para cobrar                   │
│ • Sprint 4 (10 hrs) - En revisión QA                                   │
│ • Documentación (5 hrs) - Pendiente aprobación PM                      │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 22. PROCESAMIENTO DE PAGOS

### 22.1 Vista Contabilidad - Solicitudes Pendientes

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 💳 SOLICITUDES DE PAGO A PRESTADORES                                    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📊 RESUMEN                                                              │
│ ┌────────────────┬────────────────┬────────────────┬────────────────┐  │
│ │ Pendientes     │ En proceso     │ Pagadas hoy    │ Total mes      │  │
│ │ 8 ($2,450)     │ 2 ($650)       │ 5 ($1,800)     │ $12,500        │  │
│ └────────────────┴────────────────┴────────────────┴────────────────┘  │
│                                                                         │
│ ⏳ PENDIENTES                                                           │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ # SOLICITUD  │ PRESTADOR      │ TIPO     │ MONTO   │ MÉTODO        ││
│ │──────────────┼────────────────┼──────────┼─────────┼───────────────││
│ │ COB-2024-156 │ Juan Pérez     │ Mensual  │ $400    │ Binance       ││
│ │ COB-2024-157 │ María García   │ Mensual  │ $350    │ Wise          ││
│ │ COB-2024-158 │ Dr. Rodríguez  │ Caso     │ $800    │ Transferencia ││
│ │ COB-2024-159 │ Tech Corp SAS  │ Proyecto │ $1,200  │ Transferencia ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ [☑️ Seleccionar todos]  [💳 Procesar pagos]                             │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 22.2 Vista Contabilidad - Procesar Pago Individual

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 💳 PROCESAR PAGO - COB-2024-156                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 👤 PRESTADOR: Juan Pérez │ CC: 123456789                               │
│ Tipo: Empleado mensual │ Tarifa: $5.00/hora                            │
│                                                                         │
│ 💰 DETALLE                                                              │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ App Móvil ABC - Sprint 3 API      │ 40 hrs × $5 = $200              ││
│ │ App Móvil ABC - Integración       │ 25 hrs × $5 = $125              ││
│ │ Portal XYZ - Bugfixes             │ 15 hrs × $5 = $75               ││
│ │─────────────────────────────────────────────────────────────────────││
│ │ TOTAL                             │ 80 hrs = $400.00                ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 💳 DATOS DE PAGO                                                        │
│ Método: Binance (USDT) │ ID: 123456789 │ Red: TRC20                    │
│                                                                         │
│ 📝 REGISTRAR PAGO                                                       │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Monto pagado*:      [$400.00_____] USD                              ││
│ │ # Transacción*:     [0x7a8b9c0d1e2f..._______________]              ││
│ │ Fecha del pago*:    [12/12/2024] [16:45]                            ││
│ │                                                                     ││
│ │ 📎 Comprobante*:    [📁 Seleccionar]                                ││
│ │                     comprobante_binance.png ✅                      ││
│ │                                                                     ││
│ │ Notas: [Pago realizado sin novedad______________]                   ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│        [❌ Rechazar]    [💾 Guardar]    [✅ CONFIRMAR PAGO]            │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 23. MÉTODOS DE PAGO

### 23.1 Métodos Soportados

| Método | Uso Principal | Datos Requeridos |
|--------|---------------|------------------|
| **Binance** | Crypto (USDT) | User ID, Email, Red (TRC20/BEP20) |
| **Wise** | Internacional | Email, Account holder |
| **PayPal** | Internacional | Email |
| **Payoneer** | Internacional | Email, Account ID |
| **Transferencia** | Local | Banco, Tipo cuenta, Número, Titular |
| **Efectivo** | Emergencias | N/A |

### 23.2 Configuración de Datos de Pago (Empleado)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⚙️ MIS DATOS DE PAGO                                                    │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ Método preferido*: [Binance (USDT) ▼]                                  │
│                                                                         │
│ 💰 BINANCE                                                              │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Binance User ID*: [123456789_______________]                        ││
│ │ Email Binance*:   [juan.perez@email.com____]                        ││
│ │ Red preferida*:   [TRC20 ▼]                                         ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 🏦 CUENTA BANCARIA (Alternativa)                                        │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Banco:        [Bancolombia ▼]                                       ││
│ │ Tipo cuenta:  [Ahorros ▼]                                           ││
│ │ Número:       [123-456789-00___________]                            ││
│ │ Titular:      [Juan Pérez García________]                           ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│                                              [💾 Guardar cambios]      │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 24. COMPROBANTES

### 24.1 Vista Empleado - Pago Recibido

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ✅ PAGO RECIBIDO - COB-2024-156                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ Monto: $400.00 USD (400 USDT)                                          │
│ Método: Binance (USDT) - Red TRC20                                     │
│ Fecha: 12 Diciembre 2024, 16:45                                        │
│ # Transacción: 0x7a8b9c0d1e2f3g4h5i6j7k8l9m0n                          │
│                                                                         │
│ Período: Diciembre 2024 (1-12 Dic)                                     │
│ Horas pagadas: 80                                                      │
│                                                                         │
│ [📄 Ver comprobante]  [📥 Descargar recibo PDF]                        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 24.2 Tablas de Pagos

#### wp_ga_solicitudes_cobro

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| numero | VARCHAR(20) | COB-2024-0156 |
| prestador_id | BIGINT | FK usuario/aplicante |
| prestador_tipo | ENUM | EMPLEADO, APLICANTE |
| tipo_relacion | ENUM | MENSUAL, PROYECTO, CASO |
| total_horas | DECIMAL(10,2) | Horas |
| tarifa_hora | DECIMAL(10,2) | Tarifa |
| subtotal | DECIMAL(12,2) | Horas × tarifa |
| bonificaciones | DECIMAL(12,2) | Bonos |
| deducciones | DECIMAL(12,2) | Descuentos |
| total_a_pagar | DECIMAL(12,2) | Monto final |
| metodo_pago_solicitado | ENUM | BINANCE, WISE, PAYPAL, TRANSFERENCIA |
| datos_pago | JSON | Datos del método |
| estado | ENUM | PENDIENTE, EN_PROCESO, PAGADA, RECHAZADA |
| fecha_solicitud | DATETIME | Cuándo solicitó |
| created_at | DATETIME | Creación |

#### wp_ga_pagos_prestadores

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| solicitud_cobro_id | INT | FK solicitud |
| monto_pagado | DECIMAL(12,2) | Monto |
| moneda_pago | VARCHAR(10) | USD, USDT, COP |
| metodo_pago | ENUM | BINANCE, WISE, etc |
| referencia_transaccion | VARCHAR(200) | Hash o # |
| comprobante_url | VARCHAR(500) | Imagen/PDF |
| fecha_pago | DATETIME | Cuándo se pagó |
| notas | TEXT | Observaciones |
| pagado_por | BIGINT | FK contador |
| created_at | DATETIME | Registro |

---

# PARTE 8: COMPENSACIÓN Y BONIFICACIONES

---

## 25. ESCALAS DE TARIFA

### 25.1 Concepto

Cada puesto tiene una **escala de tarifas por antigüedad**. Al cumplir años en la empresa, el prestador puede subir de tarifa previa aprobación.

### 25.2 Ejemplo de Escala

| Antigüedad | Tarifa/Hora | Incremento | Aprobación |
|------------|-------------|------------|------------|
| Año 1 | $5.00 | Base | - |
| Año 2 | $6.00 | +20% | Jefe directo |
| Año 3 | $7.00 | +17% | Jefe directo |
| Año 4 | $8.00 | +14% | Jefe + Director |
| Año 5+ | $9.00 | +12% | Jefe + Director |

---

## 26. REVISIONES DE TARIFA

### 26.1 Flujo de Aprobación

```
┌─────────────────────────────────────────────────────────────────────────┐
│ FLUJO DE REVISIÓN DE TARIFA                                             │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  Sistema detecta      Jefe recibe        Director recibe                │
│  antigüedad ────────► alerta ──────────► alerta (si aplica)            │
│  (30 días antes)      de revisión        de revisión                    │
│                            │                   │                        │
│                            ▼                   ▼                        │
│                       ¿Aprueba?           ¿Aprueba?                     │
│                        /    \              /    \                       │
│                      Sí      No          Sí      No                     │
│                      │        │          │        │                     │
│                      ▼        ▼          ▼        ▼                     │
│                   Pasa a   Rechazado   Aplicado  Rechazado              │
│                   Director                                              │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 26.2 Tabla: wp_ga_revisiones_tarifa

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| usuario_id | BIGINT | FK usuario |
| contrato_trabajo_id | INT | FK contrato |
| tarifa_anterior | DECIMAL(10,2) | Tarifa actual |
| tarifa_nueva | DECIMAL(10,2) | Tarifa propuesta |
| fecha_aplicacion | DATE | Desde cuándo aplica |
| motivo | ENUM | ANTIGUEDAD, DESEMPEÑO, PROMOCION |
| estado | ENUM | PENDIENTE, APROBADA_JEFE, APROBADA_DIRECTOR, RECHAZADA, APLICADA |
| aprobado_jefe_id | BIGINT | Quién aprobó (jefe) |
| fecha_aprobacion_jefe | DATETIME | Cuándo |
| aprobado_director_id | BIGINT | Quién aprobó (director) |
| fecha_aprobacion_director | DATETIME | Cuándo |
| notas | TEXT | Observaciones |
| created_at | DATETIME | Creación |

---

## 27. SISTEMA DE BONOS

### 27.1 Tipos de Bonos

| Bono | Descripción | Condición |
|------|-------------|-----------|
| **Productividad** | Por superar horas esperadas | ≥150 horas QA aprobadas/mes |
| **Puntualidad** | Asistencia perfecta | 0 tardanzas en el mes |
| **Calidad** | Sin errores | 0 tareas rechazadas |
| **Antigüedad** | Por años de servicio | Cada año cumplido |
| **Referido** | Por traer nuevos prestadores | Referido cumple 3 meses |

### 27.2 Tabla: wp_ga_bonos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| codigo | VARCHAR(20) | BONO-PROD-001 |
| nombre | VARCHAR(100) | "Bono Productividad" |
| tipo | ENUM | PRODUCTIVIDAD, PUNTUALIDAD, CALIDAD, ANTIGUEDAD, REFERIDO |
| valor_tipo | ENUM | MONTO_FIJO, PORCENTAJE |
| valor | DECIMAL(10,2) | Monto o % |
| condiciones | TEXT | Descripción condiciones |
| activo | TINYINT | 1=activo |

### 27.3 Tabla: wp_ga_bonos_otorgados

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| bono_id | INT | FK bono |
| usuario_id | BIGINT | Beneficiario |
| periodo | VARCHAR(20) | "2024-12" |
| monto | DECIMAL(12,2) | Monto del bono |
| motivo | TEXT | Descripción específica |
| estado | ENUM | PENDIENTE, APROBADO, PAGADO |
| aprobado_por | BIGINT | Quién aprobó |
| incluido_en_cobro_id | INT | FK solicitud cobro |
| created_at | DATETIME | Fecha |

---

## 28. PENALIDADES

### 28.1 Tipos de Penalidades

| Penalidad | Descripción | Deducción |
|-----------|-------------|-----------|
| **Tardanza** | Llegar tarde | $X por ocurrencia |
| **Incumplimiento** | No entregar a tiempo | % del valor tarea |
| **Calidad** | Trabajo rechazado múltiples veces | % del valor |
| **Ausencia** | No reportar sin aviso | Día de trabajo |

### 28.2 Tabla: wp_ga_penalidades

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| usuario_id | BIGINT | Afectado |
| tipo | ENUM | TARDANZA, INCUMPLIMIENTO, CALIDAD, AUSENCIA |
| fecha | DATE | Cuándo ocurrió |
| descripcion | TEXT | Detalle |
| monto_deduccion | DECIMAL(12,2) | Monto a descontar |
| estado | ENUM | PENDIENTE, APLICADA, APELADA, CANCELADA |
| registrado_por | BIGINT | Quién registró |
| created_at | DATETIME | Fecha |

---

## 29. COMISIONES MULTINIVEL

### 29.1 Estructura de Comisiones

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ESTRUCTURA DE COMISIONES                                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  Empleado trabaja 80 horas × $5 = $400                                  │
│       │                                                                 │
│       ├── Jefe (supervisor directo): 5% = $20                          │
│       │                                                                 │
│       └── Director (segundo nivel): 2% = $8                            │
│                                                                         │
│  TOTAL PAGADO: $400 + $20 + $8 = $428                                  │
│                                                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  TIPOS DE COMISIÓN:                                                     │
│                                                                         │
│  PORCENTAJE: 5% del valor facturado                                    │
│  MONTO FIJO: $0.50 por hora aprobada                                   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 29.2 Tabla: wp_ga_comisiones_config

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| usuario_id | BIGINT | Quién recibe |
| nivel | INT | 1=directo, 2=segundo nivel |
| tipo | ENUM | PORCENTAJE, MONTO_FIJO |
| valor | DECIMAL(10,2) | % o monto |
| aplica_a | ENUM | HORAS, FACTURADO, UTILIDAD |
| activo | TINYINT | 1=activo |

### 29.3 Tabla: wp_ga_comisiones_generadas

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| config_id | INT | FK config |
| beneficiario_id | BIGINT | Quién recibe |
| origen_usuario_id | BIGINT | De quién viene |
| registro_hora_id | INT | FK registro (si aplica) |
| orden_pago_id | INT | FK factura (si aplica) |
| monto_base | DECIMAL(12,2) | Sobre qué se calcula |
| monto_comision | DECIMAL(12,2) | Monto de comisión |
| periodo | VARCHAR(20) | "2024-12" |
| estado | ENUM | PENDIENTE, PAGADA |
| created_at | DATETIME | Fecha |

---

# PARTE 9: ADMINISTRACIÓN Y CONTROL

---

## 30. REGLAS DE TRABAJO

### 30.1 Concepto

Políticas y estándares generales que **no van en el paso a paso de cada tarea** sino que son reglas transversales.

### 30.2 Categorías

| Categoría | Ejemplos |
|-----------|----------|
| 📧 **Comunicación** | Estándar de correos, respuesta a clientes |
| 📅 **Ausencias** | Cómo pedir permiso, reportar enfermedad |
| 📋 **Procedimientos** | Cómo escalar problemas, entregar proyectos |
| 👔 **Conducta** | Código vestimenta, puntualidad |
| 🔒 **Seguridad** | Contraseñas, información sensible |
| ✅ **Calidad** | Estándares de código, revisión |

### 30.3 Vista Empleado - Reglas

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 📜 REGLAS Y ESTÁNDARES DE TRABAJO                                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ ⚠️ PENDIENTES DE ACEPTAR (2)                                            │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ 📧 Nuevo estándar para responder clientes                           ││
│ │    Actualizado: 10 Dic 2024 │ Categoría: Comunicación               ││
│ │    [Leer y Aceptar]                                                 ││
│ ├─────────────────────────────────────────────────────────────────────┤│
│ │ 📅 Nueva política de permisos de ausencia                           ││
│ │    Actualizado: 8 Dic 2024 │ Categoría: Ausencias                   ││
│ │    [Leer y Aceptar]                                                 ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 📋 TODAS LAS REGLAS                                                     │
│ ├── 📧 COMUNICACIÓN                                                     │
│ │   ├── ✅ Estándar envío correos (Aceptado 1/Nov)                    │
│ │   └── ✅ Comunicados internos (Aceptado 15/Oct)                     │
│ ├── 📅 AUSENCIAS                                                        │
│ │   └── ⏳ Política de permisos (Pendiente)                           │
│ └── 📋 PROCEDIMIENTOS                                                   │
│     └── ✅ Cómo escalar problemas (Aceptado 1/Oct)                    │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 30.4 Tabla: wp_ga_reglas_trabajo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| codigo | VARCHAR(20) | RGL-001 |
| titulo | VARCHAR(200) | Título |
| descripcion | TEXT | Descripción breve |
| categoria | ENUM | COMUNICACION, AUSENCIAS, PROCEDIMIENTOS, CONDUCTA, SEGURIDAD, CALIDAD |
| contenido | TEXT | Contenido completo |
| url_documento | VARCHAR(500) | Link externo |
| aplica_a | ENUM | TODOS, DEPARTAMENTO, PUESTO |
| aplica_a_ids | JSON | IDs específicos |
| es_obligatorio | TINYINT | ¿Debe aceptarlo? |
| activo | TINYINT | 1=activo |
| created_at | DATETIME | Creación |

---

## 31. CALENDARIO ADMINISTRATIVO

### 31.1 Propósito

Alertas y recordatorios para el área contable/administrativa sobre:
- Vencimientos de contratos
- Renovaciones
- Pagos de nómina
- Impuestos
- Seguros

### 31.2 Vista Calendario

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 📅 CALENDARIO ADMINISTRATIVO - DICIEMBRE 2024                           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│     DOM      LUN      MAR      MIÉ      JUE      VIE      SÁB          │
│ ┌────────┬────────┬────────┬────────┬────────┬────────┬────────┐       │
│ │   1    │   2    │   3    │   4    │   5    │   6    │   7    │       │
│ │        │        │        │        │🔴Vence │        │        │       │
│ │        │        │        │        │Contrato│        │        │       │
│ ├────────┼────────┼────────┼────────┼────────┼────────┼────────┤       │
│ │   8    │   9    │   10   │   11   │   12   │   13   │   14   │       │
│ │        │        │🟡Renov │        │  HOY   │        │        │       │
│ │        │        │Seguro  │        │        │        │        │       │
│ ├────────┼────────┼────────┼────────┼────────┼────────┼────────┤       │
│ │   15   │   16   │   17   │   18   │   19   │   20   │   21   │       │
│ │🔵Pago  │        │        │        │        │🟡Vence │        │       │
│ │Nómina  │        │        │        │        │NDA     │        │       │
│ └────────┴────────┴────────┴────────┴────────┴────────┴────────┘       │
│                                                                         │
│ LEYENDA:                                                                │
│ 🔴 Urgente (< 7 días) │ 🟡 Próximo (7-30 días) │ 🔵 Recurrente          │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 31.3 Tabla: wp_ga_calendario_admin

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| titulo | VARCHAR(200) | Título del evento |
| tipo | ENUM | CONTRATO_VENCE, RENOVACION, PAGO_NOMINA, IMPUESTO, SEGURO |
| categoria | ENUM | CONTRATOS, LEGAL, FINANCIERO, RRHH |
| fecha_evento | DATE | Cuándo ocurre |
| dias_anticipacion | INT | Alertar X días antes |
| es_recurrente | TINYINT | ¿Se repite? |
| frecuencia | ENUM | MENSUAL, TRIMESTRAL, ANUAL |
| monto_estimado | DECIMAL(12,2) | Costo estimado |
| responsable_id | BIGINT | Quién atiende |
| estado | ENUM | PENDIENTE, EN_GESTION, COMPLETADO |
| created_at | DATETIME | Creación |

---

## 32. SISTEMA DE VISIBILIDAD

### 32.1 Concepto

Configurar **qué información pueden ver los compañeros entre sí** para:
- Rankings de productividad
- Competencia sana
- Transparencia controlada

### 32.2 Configuración por Proyecto

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⚙️ CONFIGURAR VISIBILIDAD - App Móvil Cliente ABC                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 👥 ¿QUÉ PUEDEN VER LOS MIEMBROS DEL EQUIPO?                             │
│                                                                         │
│ ☑️ Ranking de casos completados del proyecto                            │
│ ☑️ Quién es el más eficiente del proyecto                              │
│ ☑️ Tareas de otros miembros del proyecto                               │
│ ☐ Horas trabajadas de otros miembros                                   │
│ ☐ Ingresos/pagos de otros miembros                                     │
│ ☑️ "Empleado del mes" del proyecto                                     │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 32.3 Vista de Ranking (Si está habilitado)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 🏆 RANKING DEL EQUIPO - DICIEMBRE 2024                                  │
│ Proyecto: Cliente ABC - App Móvil                                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📊 CASOS COMPLETADOS                                                    │
│ 🥇 1. Juan Pérez        │ 45 casos │ ████████████████████ 100%          │
│ 🥈 2. María García      │ 38 casos │ ████████████████░░░░ 84%           │
│ 🥉 3. Carlos Ruiz       │ 32 casos │ ██████████████░░░░░░ 71%           │
│    4. Tú (Pedro)        │ 25 casos │ ███████████░░░░░░░░░ 56%           │
│                                                                         │
│ 🌟 EMPLEADO DEL MES: Juan Pérez                                         │
│    "Mayor cantidad de casos resueltos"                                  │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 33. DASHBOARD INVERSIONISTAS

### 33.1 Vista Ejecutiva

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 💼 DASHBOARD INVERSIONISTA - Lincy Villalobos                           │
│ Participación: 60% │ Inversión inicial: $50,000                        │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 💰 ESTADO DE LA INVERSIÓN                                               │
│ ┌────────────────┬────────────────┬────────────────┐                   │
│ │ INVERSIÓN      │ RETORNO        │ ROI            │                   │
│ │ $50,000        │ $18,500 (37%)  │ ████████░░ 37% │                   │
│ └────────────────┴────────────────┴────────────────┘                   │
│                                                                         │
│ ⚖️ PUNTO DE EQUILIBRIO                                                  │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Costos fijos mensuales:  $11,000                                    ││
│ │ Punto de equilibrio:     $12,500                                    ││
│ │ Facturación actual:      $16,200 ✅ (130% del PE)                   ││
│ │                                                                     ││
│ │ Sobre punto equilibrio: +$4,200 este mes                           ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 📊 FACTURACIÓN POR PAÍS                                                 │
│ ┌───────────┬──────────┬──────────┬─────────┬──────────┬────────────┐ │
│ │ PAÍS      │ VENDIDO  │FACTURADO │ COSTO   │ COMIS.   │ UTILIDAD   │ │
│ │───────────┼──────────┼──────────┼─────────┼──────────┼────────────│ │
│ │ 🇺🇸 USA    │ $98,500  │ $85,200  │ $42,100 │ $4,260   │ $38,840    │ │
│ │ 🇨🇴 Colombia│ $52,000  │ $48,500  │ $28,400 │ $2,425   │ $17,675    │ │
│ │ 🇲🇽 México │ $25,000  │ $22,000  │ $12,800 │ $1,100   │ $8,100     │ │
│ │ 🌎 TOTAL  │ $175,500 │ $155,700 │ $83,300 │ $7,785   │ $64,615    │ │
│ └───────────┴──────────┴──────────┴─────────┴──────────┴────────────┘ │
│                                                                         │
│ 📈 PROYECCIÓN ROI 100%: Julio 2025 (7 meses más)                       │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 33.2 Tabla: wp_ga_inversionistas

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| usuario_id | BIGINT | FK wp_users |
| porcentaje_participacion | DECIMAL(5,2) | % participación |
| inversion_inicial | DECIMAL(12,2) | Monto invertido |
| fecha_inversion | DATE | Cuándo invirtió |
| moneda | VARCHAR(3) | USD |
| notas | TEXT | Condiciones |
| activo | TINYINT | 1=activo |

### 33.3 Tabla: wp_ga_costos_fijos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| concepto | VARCHAR(200) | Descripción |
| categoria | ENUM | NOMINA, SERVICIOS, SOFTWARE, INFRAESTRUCTURA |
| monto | DECIMAL(12,2) | Monto mensual |
| frecuencia | ENUM | MENSUAL, TRIMESTRAL, ANUAL |
| activo | TINYINT | 1=activo |

---

# PARTE 10: HERRAMIENTAS

---

## 34. CARGA RÁPIDA DE TAREAS

### 34.1 Propósito

Permitir a jefes crear múltiples tareas rápidamente desde una interfaz o importando desde Excel.

### 34.2 Interface de Carga

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⚡ CARGA RÁPIDA DE TAREAS                                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ Proyecto: [App Móvil Cliente ABC ▼]                                    │
│ Plantilla: [Desarrollo Feature ▼]                                      │
│                                                                         │
│ 📋 TAREAS A CREAR                                                       │
│ ┌───┬─────────────────┬────────────┬───────┬─────────┬───────────────┐ │
│ │ # │ TAREA           │ ASIGNAR A  │ HORAS │ FECHA   │ INSTRUCCIONES │ │
│ │───┼─────────────────┼────────────┼───────┼─────────┼───────────────│ │
│ │ 1 │ [Login API____] │ [Juan ▼]   │ [4]   │ [16/12] │ [📎 Adjuntar] │ │
│ │ 2 │ [UI Login_____] │ [María ▼]  │ [3]   │ [16/12] │ [📎 Adjuntar] │ │
│ │ 3 │ [Tests________] │ [Carlos ▼] │ [2]   │ [17/12] │ [📎 Adjuntar] │ │
│ │ + │ [Agregar tarea...]                                              │ │
│ └───┴─────────────────┴────────────┴───────┴─────────┴───────────────┘ │
│                                                                         │
│ 📎 INSTRUCCIONES: Texto directo O URL (Word Online, Google Docs, Video)│
│                                                                         │
│ 💰 RESUMEN DE COSTOS                                                    │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Juan Pérez   │ 4 hrs │ $3/hr │ $12.00                               ││
│ │ María García │ 3 hrs │ $8/hr │ $24.00                               ││
│ │ Carlos Ruiz  │ 2 hrs │ $3/hr │ $6.00                                ││
│ │──────────────────────────────────────────                           ││
│ │ TOTAL: 9 hrs = $42.00 + comisiones $5.00 = $47.00                   ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│             [💾 Guardar borrador]  [📤 Crear y Asignar Todas]          │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 35. PLANTILLAS EXCEL

### 35.1 Estructura del Excel de Importación

| Columna | Campo | Descripción |
|---------|-------|-------------|
| A | Cod_Tarea | Código tarea principal (1, 2, 3) |
| B | nom_Tarea | Nombre (solo en fila principal) |
| C | Cod_Subtarea | Código subtarea (1-1, 1-2) |
| D | nom_Subtarea | Nombre del paso |
| E | Descripción | Instrucciones detalladas |
| F | URL video | Link a video/documento |
| G | Horas | Tiempo en horas (0.25 = 15min) |
| H | Frecuencia | Código (0=solicitud, 1=diaria, 30=mensual) |
| I | Departamento | Código departamento |
| J | Responsable | Quién ejecuta |
| K | Supervisor | Quién revisa |
| L | Aprobador | Quién aprueba |
| M | Costo por hora | Tarifa |

### 35.2 Catálogos del Excel

**Tiempos (Horas decimales):**
- 0.25 = 15 minutos
- 0.50 = 30 minutos
- 0.75 = 45 minutos
- 1.00 = 1 hora
- 1.50 = 1.5 horas

**Frecuencias:**
- 0 = Por solicitud
- 1 = Diaria
- 5 = Semanal
- 7 = Fines de semana
- 30 = Mensual
- 60 = Trimestral
- 120 = Semestral

### 35.3 Cálculo Automático

```
TAREA: Responder WhatsApp del bot
├── Subtarea 1-1: Buscar Contacto        → 0.25 hrs
├── Subtarea 1-2: Abrir herramienta      → 0.25 hrs
├── Subtarea 1-3: Analizar y enviar      → 0.25 hrs
└── Subtarea 1-4: Validación final       → 0.25 hrs
                                         ─────────
                              TOTAL TAREA: 1.00 hr = CALCULADO POR SISTEMA
```

---

## 36. AI CHAT

### 36.1 Propósito

Asistente de IA integrado para:
- Responder preguntas de procesos
- Ayudar con tareas
- Generar reportes
- Buscar información

### 36.2 Interface

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 🤖 AI CHAT - Asistente GestionAdmin                                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │ 🤖 Hola Juan, ¿en qué puedo ayudarte hoy?                          ││
│ │                                                                     ││
│ │ 👤 ¿Cuántas horas llevo trabajadas este mes?                       ││
│ │                                                                     ││
│ │ 🤖 Este mes llevas 78 horas trabajadas:                            ││
│ │    • 45 horas aprobadas (listas para cobrar)                       ││
│ │    • 18 horas en revisión QA                                       ││
│ │    • 15 horas pendientes de enviar                                 ││
│ │                                                                     ││
│ │    Tu proyección al cierre del mes es 156 horas.                   ││
│ │                                                                     ││
│ │ 👤 ¿Cuál es el proceso para pedir vacaciones?                      ││
│ │                                                                     ││
│ │ 🤖 Según las reglas de trabajo, el proceso es:                     ││
│ │    1. Solicitar con 15 días de anticipación                        ││
│ │    2. Enviar correo a tu jefe directo                              ││
│ │    3. Esperar aprobación                                           ││
│ │    4. Registrar en el sistema                                      ││
│ │                                                                     ││
│ │    [Ver regla completa: Política de Vacaciones]                    ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ Escribe tu pregunta...                              [📤 Enviar]    ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

# PARTE 11: DASHBOARDS POR ROL

---

## 37. DASHBOARD DUEÑO/SOCIO

**Acceso:** Todo el sistema

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 👑 DASHBOARD EJECUTIVO - Lincy Villalobos (Socia)                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 📊 MÉTRICAS GLOBALES                                                    │
│ ┌────────────┬────────────┬────────────┬────────────┬────────────┐     │
│ │ Empleados  │ Proyectos  │ Clientes   │ Facturado  │ Utilidad   │     │
│ │ 25 activos │ 8 activos  │ 12 activos │ $164K      │ $67.6K     │     │
│ └────────────┴────────────┴────────────┴────────────┴────────────┘     │
│                                                                         │
│ 💰 FINANZAS                    📈 PRODUCTIVIDAD                         │
│ ├── ROI: 37%                   ├── Eficiencia: 89%                      │
│ ├── Punto equilibrio: ✅       ├── Tareas a tiempo: 92%                 │
│ └── Cartera: $18.5K            └── Capacidad usada: 78%                 │
│                                                                         │
│ 👥 DIRECTORES                                                           │
│ ├── Deiby (Desarrollo): 3 jefes, 12 empleados, $45K/mes                │
│ └── Kelly (Soporte): 2 jefes, 8 empleados, $22K/mes                    │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 38. DASHBOARD DIRECTOR

**Acceso:** Jefes asignados + sus equipos

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 👔 DASHBOARD DIRECTOR - Deiby Villalobos                                │
│ Supervisa: 3 Jefes │ 4 Proyectos                                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 👥 MIS JEFES                                                            │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ JEFE           │ PROYECTOS      │ EQUIPO │ EFICIENCIA │ ESTADO      ││
│ │────────────────┼────────────────┼────────┼────────────┼─────────────││
│ │ 🟢 Hillary     │ App ABC, Portal│ 4 pers │ 94% ✅     │ En tiempo   ││
│ │ 🟡 Carlos      │ Sistema 123    │ 3 pers │ 78%        │ ⚠️ Atraso   ││
│ │ 🟢 María       │ API Interna    │ 2 pers │ 91% ✅     │ En tiempo   ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 📁 TODOS MIS PROYECTOS                                                  │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ PROYECTO          │ PROGRESO        │ RENTAB. │ RIESGO              ││
│ │───────────────────┼─────────────────┼─────────┼─────────────────────││
│ │ App Móvil ABC     │ ████████░░ 78%  │ 28% ✅  │ 🟢 Bajo             ││
│ │ Portal XYZ        │ ██████░░░░ 58%  │ 22%     │ 🟡 Medio            ││
│ │ Sistema 123       │ ████░░░░░░ 42%  │ 15%     │ 🔴 Alto             ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 💰 Comisiones del mes: $1,240                                          │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 39. DASHBOARD JEFE/PM

**Acceso:** Su equipo + sus proyectos

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 📊 DASHBOARD JEFE - Hillary López                                       │
│ Proyectos: 2 │ Equipo: 4 personas                                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ 👥 MI EQUIPO                                                            │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ PERSONA        │ HOY          │ SEMANA    │ TAREAS    │ ESTADO      ││
│ │────────────────┼──────────────┼───────────┼───────────┼─────────────││
│ │ Juan Pérez     │ 🟢 Activo    │ 38 hrs    │ 5 pend.   │ Al día      ││
│ │ María García   │ 🟢 Activo    │ 35 hrs    │ 3 pend.   │ Al día      ││
│ │ Carlos Ruiz    │ 🟡 Pausado   │ 28 hrs    │ 8 pend.   │ ⚠️ Atraso   ││
│ │ Ana López      │ ⚫ Offline   │ 32 hrs    │ 2 pend.   │ Al día      ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 📋 TAREAS PENDIENTES DE APROBACIÓN                                      │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ • Juan Pérez: Login API (4 hrs) - QA aprobado ✅ [Aprobar] [Rechazar]││
│ │ • María: Diseño UI (3 hrs) - QA aprobado ✅ [Aprobar] [Rechazar]    ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ ⚡ ACCIONES RÁPIDAS                                                     │
│ [📋 Crear tarea] [⚡ Carga rápida] [📧 Solicitar factura] [📊 Reportes]│
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 40. DASHBOARD EMPLEADO

**Acceso:** Sus tareas + sus métricas

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 👤 MI ESPACIO - Juan Pérez                                              │
│ Developer Backend │ Proyecto: App Móvil ABC                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ ⏱️ TIMER                        📊 ESTE MES                             │
│ ┌─────────────────────┐        ┌─────────────────────────┐             │
│ │                     │        │ Horas: 78/160           │             │
│ │      02:15:30       │        │ ████████░░░░░░ 49%      │             │
│ │                     │        │                         │             │
│ │ [⏸️ Pausar]         │        │ Tareas: 12 completadas  │             │
│ └─────────────────────┘        │ A tiempo: 92% ✅        │             │
│ Tarea: Tests unitarios         └─────────────────────────┘             │
│                                                                         │
│ 📋 MIS TAREAS                                                           │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ 🔄 EN PROGRESO                                                       ││
│ │ • Tests unitarios Login (2 hrs) - Timer activo ⏱️                   ││
│ │                                                                     ││
│ │ ⏳ PENDIENTES                                                        ││
│ │ • Documentación API (1.5 hrs) - Vence: 15 Dic                       ││
│ │ • Integración pagos (4 hrs) - Vence: 18 Dic                         ││
│ │                                                                     ││
│ │ ✅ COMPLETADAS HOY                                                   ││
│ │ • Endpoint login (4 hrs) - En revisión QA                           ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 💰 LISTO PARA COBRAR: $200 (40 horas aprobadas)                        │
│ [💰 COBRAR]                                                             │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 41. DASHBOARD CLIENTE

**Acceso:** Sus proyectos + sus facturas

(Ver Parte 4: Portal de Clientes)

---

## 42. DASHBOARD CONTABILIDAD

**Acceso:** Facturación + Pagos

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 💰 DASHBOARD CONTABILIDAD                                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ [📄 Facturas] [💳 Pagos Prestadores] [📊 Reportes] [📅 Calendario]     │
│                                                                         │
│ 📊 RESUMEN DEL MES                                                      │
│ ┌────────────┬────────────┬────────────┬────────────┬────────────┐     │
│ │ Facturado  │ Cobrado    │ Por cobrar │ Pagos prest│ Por pagar  │     │
│ │ $16,200    │ $12,500    │ $3,700     │ $8,500     │ $2,450     │     │
│ └────────────┴────────────┴────────────┴────────────┴────────────┘     │
│                                                                         │
│ ⏳ SOLICITUDES PENDIENTES                                               │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │ TIPO               │ CANTIDAD │ MONTO    │ ACCIÓN                   ││
│ │────────────────────┼──────────┼──────────┼──────────────────────────││
│ │ Facturas por emitir│ 3        │ $5,200   │ [Procesar]               ││
│ │ Pagos a prestadores│ 8        │ $2,450   │ [Procesar]               ││
│ │ Facturas vencidas  │ 2        │ $1,800   │ [Ver detalle]            ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ 🔔 ALERTAS                                                              │
│ • 🔴 FAC-089 vencida hace 15 días ($2,500)                             │
│ • 🟡 Pago nómina en 3 días ($4,500)                                    │
│ • 🟡 IVA a declarar antes del 28                                       │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

# PARTE 12: BASE DE DATOS

---

## 43. MODELO DE DATOS COMPLETO

### 43.1 Diagrama de Relaciones Principal

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    MODELO DE DATOS - GESTIONADMIN                       │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐               │
│  │DEPARTAMENTOS│────►│   PUESTOS   │────►│  USUARIOS   │               │
│  └─────────────┘     └─────────────┘     └──────┬──────┘               │
│                            │                    │                       │
│                            │                    │                       │
│                            ▼                    ▼                       │
│                      ┌───────────┐      ┌─────────────┐                │
│                      │ ESCALAS   │      │ CONTRATOS   │                │
│                      │ TARIFA    │      │  TRABAJO    │                │
│                      └───────────┘      └──────┬──────┘                │
│                                                │                       │
│  ┌─────────────┐     ┌─────────────┐          │                       │
│  │  CLIENTES   │────►│   CASOS     │◄─────────┤                       │
│  └─────────────┘     └──────┬──────┘          │                       │
│                             │                  │                       │
│                             ▼                  ▼                       │
│                      ┌─────────────┐    ┌─────────────┐                │
│                      │  PROYECTOS  │    │   TAREAS    │                │
│                      └──────┬──────┘    └──────┬──────┘                │
│                             │                  │                       │
│                             │                  ▼                       │
│                             │           ┌─────────────┐                │
│                             │           │  SUBTAREAS  │                │
│                             │           └──────┬──────┘                │
│                             │                  │                       │
│                             │                  ▼                       │
│                             │           ┌─────────────┐                │
│                             └──────────►│  REGISTRO   │                │
│                                         │   HORAS     │                │
│                                         └──────┬──────┘                │
│                                                │                       │
│                    ┌───────────────────────────┼────────────────┐      │
│                    │                           │                │      │
│                    ▼                           ▼                ▼      │
│             ┌─────────────┐           ┌─────────────┐   ┌───────────┐ │
│             │ SOLICITUDES │           │  ORDENES    │   │ COMISIONES│ │
│             │   COBRO     │           │   PAGO      │   │ GENERADAS │ │
│             └──────┬──────┘           └──────┬──────┘   └───────────┘ │
│                    │                         │                        │
│                    ▼                         ▼                        │
│             ┌─────────────┐           ┌─────────────┐                 │
│             │   PAGOS     │           │   PAGOS     │                 │
│             │ PRESTADORES │           │  RECIBIDOS  │                 │
│             └─────────────┘           └─────────────┘                 │
│                                                                        │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 44. DICCIONARIO DE TABLAS

### 44.1 Módulo: Estructura Organizacional

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 1 | wp_ga_departamentos | Departamentos de la empresa | 10 |
| 2 | wp_ga_puestos | Puestos de trabajo | 30 |
| 3 | wp_ga_puestos_escalas | Escalas de tarifa por antigüedad | 150 |
| 4 | wp_ga_usuarios | Extensión de wp_users | 100 |
| 5 | wp_ga_supervisiones | Relaciones de supervisión | 50 |

### 44.2 Módulo: Portal de Trabajo

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 6 | wp_ga_aplicantes | Personas/empresas que aplican | 500 |
| 7 | wp_ga_ordenes_trabajo | Ofertas de trabajo publicadas | 50 |
| 8 | wp_ga_aplicaciones | Aplicaciones a órdenes | 200 |
| 9 | wp_ga_contratos_trabajo | Contratos activos | 80 |

### 44.3 Módulo: Clientes y Casos

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 10 | wp_ga_clientes | Clientes de la empresa | 50 |
| 11 | wp_ga_casos | Casos/expedientes | 200 |
| 12 | wp_ga_proyectos | Proyectos por caso | 100 |

### 44.4 Módulo: Tareas y Timer

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 13 | wp_ga_catalogo_tareas | Catálogo de tipos de tarea | 100 |
| 14 | wp_ga_tareas | Tareas asignadas | 5,000 |
| 15 | wp_ga_subtareas | Pasos de cada tarea | 20,000 |
| 16 | wp_ga_registro_horas | Horas trabajadas | 50,000 |
| 17 | wp_ga_pausas_timer | Pausas del timer | 10,000 |
| 18 | wp_ga_plantillas_tareas | Plantillas de carga rápida | 50 |

### 44.5 Módulo: Facturación

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 19 | wp_ga_paises_config | Configuración por país | 10 |
| 20 | wp_ga_ordenes_pago | Facturas a clientes | 1,000 |
| 21 | wp_ga_ordenes_pago_items | Líneas de cada factura | 5,000 |
| 22 | wp_ga_pagos_recibidos | Pagos de clientes | 800 |

### 44.6 Módulo: Pagos a Prestadores

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 23 | wp_ga_solicitudes_cobro | Solicitudes de pago | 2,000 |
| 24 | wp_ga_solicitudes_cobro_detalle | Detalle de cada solicitud | 10,000 |
| 25 | wp_ga_pagos_prestadores | Pagos realizados | 2,000 |

### 44.7 Módulo: Compensación

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 26 | wp_ga_bonos | Catálogo de bonos | 10 |
| 27 | wp_ga_bonos_otorgados | Bonos dados | 500 |
| 28 | wp_ga_penalidades | Penalidades aplicadas | 100 |
| 29 | wp_ga_comisiones_config | Config de comisiones | 30 |
| 30 | wp_ga_comisiones_generadas | Comisiones calculadas | 5,000 |
| 31 | wp_ga_revisiones_tarifa | Revisiones de salario | 200 |

### 44.8 Módulo: Administración

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 32 | wp_ga_reglas_trabajo | Reglas y políticas | 30 |
| 33 | wp_ga_reglas_aceptadas | Aceptaciones | 1,000 |
| 34 | wp_ga_calendario_admin | Eventos administrativos | 200 |
| 35 | wp_ga_inversionistas | Socios/inversionistas | 5 |
| 36 | wp_ga_costos_fijos | Gastos fijos | 20 |

### 44.9 Módulo: Firma Digital

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 37 | wp_ga_firmas_documentos | Firmas realizadas | 500 |

### 44.10 Módulo: Comunicación

| # | Tabla | Descripción | Registros Est. |
|---|-------|-------------|----------------|
| 38 | wp_ga_solicitudes_cliente | Solicitudes de clientes | 500 |
| 39 | wp_ga_notificaciones | Notificaciones sistema | 10,000 |
| 40 | wp_ga_chat_ai | Historial AI chat | 5,000 |

---

### 44.11 Esquema SQL Completo - Tablas Principales

```sql
-- =====================================================
-- GESTIONADMIN - ESQUEMA DE BASE DE DATOS
-- =====================================================

-- -----------------------------------------------------
-- MÓDULO: ESTRUCTURA ORGANIZACIONAL
-- -----------------------------------------------------

CREATE TABLE wp_ga_departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo ENUM('OPERACION_FIJA', 'PROYECTOS', 'SOPORTE', 'COMERCIAL') DEFAULT 'PROYECTOS',
    jefe_id BIGINT UNSIGNED,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_puestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    nivel_jerarquico INT DEFAULT 4, -- 1=Socio, 2=Director, 3=Jefe, 4=Empleado
    reporta_a_puesto_id INT,
    capacidad_horas_semana INT DEFAULT 40,
    requiere_qa TINYINT(1) DEFAULT 0,
    flujo_revision_default ENUM('SOLO_JEFE', 'QA_JEFE', 'QA_JEFE_DIRECTOR') DEFAULT 'SOLO_JEFE',
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES wp_ga_departamentos(id),
    INDEX idx_departamento (departamento_id),
    INDEX idx_nivel (nivel_jerarquico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_puestos_escalas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    puesto_id INT NOT NULL,
    anio_antiguedad INT NOT NULL, -- 1, 2, 3, 4, 5+
    tarifa_hora DECIMAL(10,2) NOT NULL,
    incremento_porcentaje DECIMAL(5,2) DEFAULT 0,
    requiere_aprobacion_jefe TINYINT(1) DEFAULT 1,
    requiere_aprobacion_director TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (puesto_id) REFERENCES wp_ga_puestos(id),
    UNIQUE KEY uk_puesto_anio (puesto_id, anio_antiguedad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_wp_id BIGINT UNSIGNED NOT NULL UNIQUE,
    puesto_id INT,
    departamento_id INT,
    codigo_empleado VARCHAR(20) UNIQUE,
    fecha_ingreso DATE,
    nivel_jerarquico INT DEFAULT 4,
    es_jefe_de_jefes TINYINT(1) DEFAULT 0,
    puede_ver_departamentos JSON, -- Array de IDs
    -- Datos de pago
    metodo_pago_preferido ENUM('BINANCE', 'WISE', 'PAYPAL', 'PAYONEER', 'TRANSFERENCIA', 'EFECTIVO'),
    datos_pago_binance JSON,
    datos_pago_wise JSON,
    datos_pago_paypal JSON,
    datos_pago_banco JSON,
    -- Integraciones futuras
    timedoctor_user_id VARCHAR(50),
    stripe_customer_id VARCHAR(50),
    -- Control
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (puesto_id) REFERENCES wp_ga_puestos(id),
    FOREIGN KEY (departamento_id) REFERENCES wp_ga_departamentos(id),
    INDEX idx_usuario_wp (usuario_wp_id),
    INDEX idx_puesto (puesto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_supervisiones (
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
    INDEX idx_supervisor (supervisor_id),
    INDEX idx_supervisado (supervisado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: PORTAL DE TRABAJO (MARKETPLACE)
-- -----------------------------------------------------

CREATE TABLE wp_ga_aplicantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_wp_id BIGINT UNSIGNED UNIQUE, -- Para login
    tipo ENUM('PERSONA_NATURAL', 'EMPRESA') NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    -- Persona Natural
    nombre_completo VARCHAR(200),
    documento_tipo VARCHAR(20),
    documento_numero VARCHAR(50),
    telefono VARCHAR(50),
    ciudad VARCHAR(100),
    pais VARCHAR(100),
    linkedin VARCHAR(500),
    portafolio_url VARCHAR(500),
    cv_url VARCHAR(500),
    url_documento_frente VARCHAR(500),
    url_documento_reverso VARCHAR(500),
    -- Empresa
    razon_social VARCHAR(200),
    nombre_comercial VARCHAR(200),
    nit_rut VARCHAR(50),
    sitio_web VARCHAR(500),
    direccion TEXT,
    contacto_nombre VARCHAR(200),
    contacto_cargo VARCHAR(100),
    contacto_email VARCHAR(200),
    contacto_telefono VARCHAR(50),
    url_camara_comercio VARCHAR(500),
    url_rut VARCHAR(500),
    url_portafolio_servicios VARCHAR(500),
    -- Verificación
    documentos_verificados TINYINT(1) DEFAULT 0,
    verificado_por BIGINT UNSIGNED,
    fecha_verificacion DATETIME,
    -- Control
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_ordenes_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    requisitos TEXT,
    departamento_id INT,
    puesto_base_id INT,
    tipo_contratacion ENUM('MENSUAL', 'PROYECTO', 'CASO', 'POR_HORA') DEFAULT 'PROYECTO',
    modalidad ENUM('REMOTO', 'PRESENCIAL', 'HIBRIDO') DEFAULT 'REMOTO',
    tarifa_min DECIMAL(10,2),
    tarifa_max DECIMAL(10,2),
    moneda VARCHAR(3) DEFAULT 'USD',
    duracion_estimada VARCHAR(50),
    horas_semana INT,
    fecha_publicacion DATE,
    fecha_cierre DATE,
    estado ENUM('BORRADOR', 'PUBLICADA', 'CERRADA', 'CANCELADA') DEFAULT 'BORRADOR',
    max_aplicantes INT DEFAULT 50,
    caso_id INT,
    proyecto_id INT,
    responsable_id BIGINT UNSIGNED,
    activo TINYINT(1) DEFAULT 1,
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_fecha_pub (fecha_publicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_aplicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_trabajo_id INT NOT NULL,
    aplicante_id INT NOT NULL,
    fecha_aplicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    carta_presentacion TEXT,
    tarifa_solicitada DECIMAL(10,2),
    disponibilidad VARCHAR(200),
    horas_disponibles INT,
    estado ENUM('PENDIENTE', 'EN_REVISION', 'PRESELECCIONADO', 'ACEPTADO', 'RECHAZADO') DEFAULT 'PENDIENTE',
    notas_evaluador TEXT,
    evaluado_por BIGINT UNSIGNED,
    fecha_evaluacion DATETIME,
    contrato_generado_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orden_trabajo_id) REFERENCES wp_ga_ordenes_trabajo(id),
    FOREIGN KEY (aplicante_id) REFERENCES wp_ga_aplicantes(id),
    UNIQUE KEY uk_orden_aplicante (orden_trabajo_id, aplicante_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_contratos_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) NOT NULL UNIQUE,
    aplicante_id INT,
    usuario_id BIGINT UNSIGNED, -- Si es empleado interno
    orden_trabajo_id INT,
    puesto_id INT,
    proyecto_id INT,
    caso_id INT,
    tipo ENUM('MENSUAL', 'PROYECTO', 'CASO', 'POR_HORA') NOT NULL,
    tarifa_hora DECIMAL(10,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'USD',
    horas_semana_acordadas INT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    estado ENUM('ACTIVO', 'PAUSADO', 'TERMINADO', 'CANCELADO') DEFAULT 'ACTIVO',
    supervisor_id BIGINT UNSIGNED,
    -- Documentos
    url_contrato_pdf VARCHAR(500),
    url_nda_pdf VARCHAR(500),
    fecha_firma_contrato DATETIME,
    fecha_firma_nda DATETIME,
    -- Control
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_aplicante (aplicante_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: CLIENTES Y CASOS
-- -----------------------------------------------------

CREATE TABLE wp_ga_clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_wp_id BIGINT UNSIGNED UNIQUE, -- Para login portal
    codigo VARCHAR(20) NOT NULL UNIQUE,
    tipo ENUM('PERSONA_NATURAL', 'EMPRESA') DEFAULT 'EMPRESA',
    nombre_comercial VARCHAR(200) NOT NULL,
    razon_social VARCHAR(200),
    documento_tipo VARCHAR(20),
    documento_numero VARCHAR(50),
    email VARCHAR(200),
    telefono VARCHAR(50),
    pais VARCHAR(2),
    ciudad VARCHAR(100),
    direccion TEXT,
    regimen_fiscal VARCHAR(50),
    retencion_default DECIMAL(5,2) DEFAULT 0,
    -- Contacto principal
    contacto_nombre VARCHAR(200),
    contacto_cargo VARCHAR(100),
    contacto_email VARCHAR(200),
    contacto_telefono VARCHAR(50),
    -- Integraciones
    stripe_customer_id VARCHAR(50),
    paypal_email VARCHAR(200),
    metodo_pago_preferido ENUM('TRANSFERENCIA', 'STRIPE', 'PAYPAL', 'EFECTIVO'),
    -- Control
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_pais (pais)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_casos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) NOT NULL UNIQUE,
    cliente_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo ENUM('PROYECTO', 'LEGAL', 'SOPORTE', 'CONSULTORIA', 'OTRO') DEFAULT 'PROYECTO',
    estado ENUM('ABIERTO', 'EN_PROGRESO', 'EN_ESPERA', 'CERRADO', 'CANCELADO') DEFAULT 'ABIERTO',
    prioridad ENUM('BAJA', 'MEDIA', 'ALTA', 'URGENTE') DEFAULT 'MEDIA',
    fecha_apertura DATE NOT NULL,
    fecha_cierre_estimada DATE,
    fecha_cierre_real DATETIME,
    responsable_id BIGINT UNSIGNED,
    presupuesto_horas INT,
    presupuesto_dinero DECIMAL(12,2),
    horas_consumidas DECIMAL(10,2) DEFAULT 0,
    costo_interno DECIMAL(12,2) DEFAULT 0,
    porcentaje_avance INT DEFAULT 0,
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES wp_ga_clientes(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caso_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE,
    fecha_fin_estimada DATE,
    fecha_fin_real DATE,
    estado ENUM('PLANIFICACION', 'EN_PROGRESO', 'PAUSADO', 'COMPLETADO', 'CANCELADO') DEFAULT 'PLANIFICACION',
    responsable_id BIGINT UNSIGNED,
    presupuesto_horas INT,
    presupuesto_dinero DECIMAL(12,2),
    -- Integraciones
    timedoctor_project_id VARCHAR(50),
    -- Visibilidad
    mostrar_ranking TINYINT(1) DEFAULT 0,
    mostrar_tareas_equipo TINYINT(1) DEFAULT 1,
    mostrar_horas_equipo TINYINT(1) DEFAULT 0,
    -- Control
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caso_id) REFERENCES wp_ga_casos(id),
    INDEX idx_caso (caso_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: TAREAS Y TIMER
-- -----------------------------------------------------

CREATE TABLE wp_ga_catalogo_tareas (
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
    -- Flujo de revisión
    flujo_revision ENUM('DEFAULT_PUESTO', 'PERSONALIZADO') DEFAULT 'DEFAULT_PUESTO',
    revisor_tipo ENUM('NINGUNO', 'QA_DEPARTAMENTO', 'USUARIO_ESPECIFICO', 'PAR'),
    revisor_usuario_id BIGINT UNSIGNED,
    aprobador_tipo ENUM('JEFE_DIRECTO', 'JEFE_DEPARTAMENTO', 'USUARIO_ESPECIFICO', 'AUTO'),
    aprobador_usuario_id BIGINT UNSIGNED,
    requiere_segundo_aprobador TINYINT(1) DEFAULT 0,
    segundo_aprobador_nivel INT,
    -- Control
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_departamento (departamento_id),
    INDEX idx_puesto (puesto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_tareas (
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
    -- Integraciones
    timedoctor_task_id VARCHAR(50),
    -- Control
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_asignado (asignado_a),
    INDEX idx_proyecto (proyecto_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_limite (fecha_limite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_subtareas (
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
    FOREIGN KEY (tarea_id) REFERENCES wp_ga_tareas(id) ON DELETE CASCADE,
    INDEX idx_tarea (tarea_id),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_registro_horas (
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
    -- Revisión
    aprobado_qa_por BIGINT UNSIGNED,
    fecha_aprobacion_qa DATETIME,
    aprobado_por BIGINT UNSIGNED,
    fecha_aprobacion DATETIME,
    motivo_rechazo TEXT,
    -- Cálculo
    tarifa_hora DECIMAL(10,2),
    monto_calculado DECIMAL(12,2),
    -- Referencia a pago
    incluido_en_cobro_id INT,
    -- Control
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tarea_id) REFERENCES wp_ga_tareas(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tarea (tarea_id),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_pausas_timer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registro_hora_id INT NOT NULL,
    hora_pausa DATETIME NOT NULL,
    hora_reanudacion DATETIME,
    minutos INT DEFAULT 0,
    motivo ENUM('ALMUERZO', 'REUNION', 'EMERGENCIA', 'DESCANSO', 'OTRO') DEFAULT 'OTRO',
    nota VARCHAR(200),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registro_hora_id) REFERENCES wp_ga_registro_horas(id) ON DELETE CASCADE,
    INDEX idx_registro (registro_hora_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: FACTURACIÓN
-- -----------------------------------------------------

CREATE TABLE wp_ga_paises_config (
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
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_ordenes_pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_interno VARCHAR(20) NOT NULL UNIQUE,
    cliente_id INT NOT NULL,
    caso_id INT,
    proyecto_id INT,
    tipo ENUM('SOLICITUD', 'FACTURA', 'NOTA_CREDITO') DEFAULT 'SOLICITUD',
    estado ENUM('BORRADOR', 'SOLICITUD_FACTURA', 'EN_PROCESO_POS', 'FACTURADA', 'RECHAZADA', 'RECHAZADA_POS', 'PAGADA', 'VENCIDA', 'PARCIAL') DEFAULT 'BORRADOR',
    -- Facturación
    pais_facturacion VARCHAR(2),
    moneda VARCHAR(3) DEFAULT 'USD',
    tasa_cambio DECIMAL(10,4) DEFAULT 1,
    -- Datos POS
    numero_documento_pos VARCHAR(50),
    consecutivo_factura VARCHAR(100),
    cufe_uuid VARCHAR(200),
    url_pdf_factura VARCHAR(500),
    url_xml_factura VARCHAR(500),
    fecha_emision_pos DATE,
    -- Montos
    subtotal DECIMAL(12,2) DEFAULT 0,
    impuesto_nombre VARCHAR(20),
    impuesto_porcentaje DECIMAL(5,2) DEFAULT 0,
    impuesto_monto DECIMAL(12,2) DEFAULT 0,
    total_facturado DECIMAL(12,2) DEFAULT 0,
    retencion_porcentaje DECIMAL(5,2) DEFAULT 0,
    retencion_monto DECIMAL(12,2) DEFAULT 0,
    total_a_cobrar DECIMAL(12,2) DEFAULT 0,
    monto_cobrado DECIMAL(12,2) DEFAULT 0,
    -- Costos
    costo_interno DECIMAL(12,2) DEFAULT 0,
    comisiones_total DECIMAL(12,2) DEFAULT 0,
    utilidad_neta DECIMAL(12,2) DEFAULT 0,
    margen_porcentaje DECIMAL(5,2) DEFAULT 0,
    -- Responsables
    solicitado_por BIGINT UNSIGNED,
    fecha_solicitud DATETIME,
    procesado_por BIGINT UNSIGNED,
    fecha_procesado DATETIME,
    -- Notas
    notas_solicitante TEXT,
    notas_contabilidad TEXT,
    motivo_rechazo TEXT,
    fecha_vencimiento DATE,
    -- Control
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES wp_ga_clientes(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_pagos_recibidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_pago_id INT NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'USD',
    tasa_cambio DECIMAL(10,4) DEFAULT 1,
    monto_usd DECIMAL(12,2),
    metodo_pago ENUM('TRANSFERENCIA', 'PAYPAL', 'STRIPE', 'EFECTIVO', 'CHEQUE', 'OTRO'),
    referencia VARCHAR(200),
    banco VARCHAR(100),
    fecha_pago DATE NOT NULL,
    fecha_confirmacion DATETIME,
    comprobante_url VARCHAR(500),
    notas TEXT,
    registrado_por BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orden_pago_id) REFERENCES wp_ga_ordenes_pago(id),
    INDEX idx_orden (orden_pago_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: PAGOS A PRESTADORES
-- -----------------------------------------------------

CREATE TABLE wp_ga_solicitudes_cobro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL UNIQUE,
    prestador_id BIGINT UNSIGNED NOT NULL, -- usuario_id o aplicante convertido
    prestador_tipo ENUM('EMPLEADO', 'APLICANTE_NATURAL', 'APLICANTE_EMPRESA') NOT NULL,
    tipo_relacion ENUM('MENSUAL', 'PROYECTO', 'CASO_ESPECIFICO') NOT NULL,
    contrato_trabajo_id INT,
    orden_trabajo_id INT,
    -- Montos
    total_horas DECIMAL(10,2) NOT NULL,
    tarifa_hora DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    bonificaciones DECIMAL(12,2) DEFAULT 0,
    deducciones DECIMAL(12,2) DEFAULT 0,
    total_a_pagar DECIMAL(12,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'USD',
    -- Método de pago
    metodo_pago_solicitado ENUM('BINANCE', 'WISE', 'PAYPAL', 'PAYONEER', 'TRANSFERENCIA', 'EFECTIVO'),
    datos_pago JSON,
    -- Estado
    estado ENUM('PENDIENTE', 'EN_PROCESO', 'PAGADA', 'RECHAZADA') DEFAULT 'PENDIENTE',
    -- Fechas
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    periodo_desde DATE,
    periodo_hasta DATE,
    -- Control
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_prestador (prestador_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_solicitudes_cobro_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_cobro_id INT NOT NULL,
    registro_hora_id INT NOT NULL,
    tarea_id INT,
    proyecto_id INT,
    horas DECIMAL(10,2) NOT NULL,
    tarifa DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    aprobado_por BIGINT UNSIGNED,
    fecha_aprobacion DATETIME,
    FOREIGN KEY (solicitud_cobro_id) REFERENCES wp_ga_solicitudes_cobro(id) ON DELETE CASCADE,
    INDEX idx_solicitud (solicitud_cobro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_pagos_prestadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_cobro_id INT NOT NULL,
    monto_pagado DECIMAL(12,2) NOT NULL,
    moneda_pago VARCHAR(10) DEFAULT 'USD',
    tasa_cambio DECIMAL(10,4) DEFAULT 1,
    metodo_pago ENUM('BINANCE', 'WISE', 'PAYPAL', 'PAYONEER', 'TRANSFERENCIA', 'EFECTIVO') NOT NULL,
    referencia_transaccion VARCHAR(200),
    comprobante_url VARCHAR(500),
    fecha_pago DATETIME NOT NULL,
    notas TEXT,
    pagado_por BIGINT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitud_cobro_id) REFERENCES wp_ga_solicitudes_cobro(id),
    INDEX idx_solicitud (solicitud_cobro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: COMPENSACIÓN (BONOS, PENALIDADES, COMISIONES)
-- -----------------------------------------------------

CREATE TABLE wp_ga_bonos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('PRODUCTIVIDAD', 'PUNTUALIDAD', 'CALIDAD', 'ANTIGUEDAD', 'REFERIDO', 'OTRO') NOT NULL,
    valor_tipo ENUM('MONTO_FIJO', 'PORCENTAJE') DEFAULT 'MONTO_FIJO',
    valor DECIMAL(10,2) NOT NULL,
    condiciones TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_bonos_otorgados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bono_id INT NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    periodo VARCHAR(20), -- "2024-12"
    monto DECIMAL(12,2) NOT NULL,
    motivo TEXT,
    estado ENUM('PENDIENTE', 'APROBADO', 'PAGADO', 'CANCELADO') DEFAULT 'PENDIENTE',
    aprobado_por BIGINT UNSIGNED,
    fecha_aprobacion DATETIME,
    incluido_en_cobro_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bono_id) REFERENCES wp_ga_bonos(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_penalidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('TARDANZA', 'INCUMPLIMIENTO', 'CALIDAD', 'AUSENCIA', 'OTRO') NOT NULL,
    fecha DATE NOT NULL,
    descripcion TEXT,
    monto_deduccion DECIMAL(12,2) NOT NULL,
    estado ENUM('PENDIENTE', 'APLICADA', 'APELADA', 'CANCELADA') DEFAULT 'PENDIENTE',
    registrado_por BIGINT UNSIGNED,
    incluido_en_cobro_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_comisiones_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    nivel INT DEFAULT 1, -- 1=directo, 2=segundo nivel
    tipo ENUM('PORCENTAJE', 'MONTO_FIJO') DEFAULT 'PORCENTAJE',
    valor DECIMAL(10,2) NOT NULL,
    aplica_a ENUM('HORAS', 'FACTURADO', 'UTILIDAD') DEFAULT 'HORAS',
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_comisiones_generadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_id INT NOT NULL,
    beneficiario_id BIGINT UNSIGNED NOT NULL,
    origen_usuario_id BIGINT UNSIGNED NOT NULL,
    registro_hora_id INT,
    orden_pago_id INT,
    monto_base DECIMAL(12,2) NOT NULL,
    monto_comision DECIMAL(12,2) NOT NULL,
    periodo VARCHAR(20),
    estado ENUM('PENDIENTE', 'PAGADA') DEFAULT 'PENDIENTE',
    incluido_en_cobro_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (config_id) REFERENCES wp_ga_comisiones_config(id),
    INDEX idx_beneficiario (beneficiario_id),
    INDEX idx_periodo (periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_revisiones_tarifa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    contrato_trabajo_id INT,
    tarifa_anterior DECIMAL(10,2) NOT NULL,
    tarifa_nueva DECIMAL(10,2) NOT NULL,
    fecha_aplicacion DATE NOT NULL,
    motivo ENUM('ANTIGUEDAD', 'DESEMPEÑO', 'PROMOCION', 'AJUSTE_MERCADO') DEFAULT 'ANTIGUEDAD',
    estado ENUM('PENDIENTE', 'APROBADA_JEFE', 'APROBADA_DIRECTOR', 'RECHAZADA', 'APLICADA') DEFAULT 'PENDIENTE',
    aprobado_jefe_id BIGINT UNSIGNED,
    fecha_aprobacion_jefe DATETIME,
    aprobado_director_id BIGINT UNSIGNED,
    fecha_aprobacion_director DATETIME,
    notas TEXT,
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: ADMINISTRACIÓN
-- -----------------------------------------------------

CREATE TABLE wp_ga_reglas_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria ENUM('COMUNICACION', 'AUSENCIAS', 'PROCEDIMIENTOS', 'CONDUCTA', 'SEGURIDAD', 'CALIDAD') NOT NULL,
    contenido TEXT,
    url_documento VARCHAR(500),
    url_video VARCHAR(500),
    aplica_a ENUM('TODOS', 'DEPARTAMENTO', 'PUESTO', 'PROYECTO') DEFAULT 'TODOS',
    aplica_a_ids JSON,
    es_obligatorio TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_reglas_aceptadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    regla_id INT NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    fecha_lectura DATETIME,
    fecha_aceptacion DATETIME,
    ip_aceptacion VARCHAR(45),
    FOREIGN KEY (regla_id) REFERENCES wp_ga_reglas_trabajo(id),
    UNIQUE KEY uk_regla_usuario (regla_id, usuario_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_calendario_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    tipo ENUM('CONTRATO_VENCE', 'RENOVACION', 'PAGO_NOMINA', 'IMPUESTO', 'SEGURO', 'OTRO') NOT NULL,
    categoria ENUM('CONTRATOS', 'LEGAL', 'FINANCIERO', 'RRHH') NOT NULL,
    fecha_evento DATE NOT NULL,
    dias_anticipacion INT DEFAULT 7,
    es_recurrente TINYINT(1) DEFAULT 0,
    frecuencia ENUM('MENSUAL', 'TRIMESTRAL', 'ANUAL'),
    monto_estimado DECIMAL(12,2),
    responsable_id BIGINT UNSIGNED,
    estado ENUM('PENDIENTE', 'EN_GESTION', 'COMPLETADO') DEFAULT 'PENDIENTE',
    notas TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha_evento),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_inversionistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL UNIQUE,
    porcentaje_participacion DECIMAL(5,2) NOT NULL,
    inversion_inicial DECIMAL(12,2) NOT NULL,
    fecha_inversion DATE NOT NULL,
    moneda VARCHAR(3) DEFAULT 'USD',
    notas TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wp_ga_costos_fijos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concepto VARCHAR(200) NOT NULL,
    categoria ENUM('NOMINA', 'SERVICIOS', 'SOFTWARE', 'INFRAESTRUCTURA', 'LEGAL', 'OTRO') NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'USD',
    frecuencia ENUM('MENSUAL', 'TRIMESTRAL', 'ANUAL') DEFAULT 'MENSUAL',
    proveedor VARCHAR(200),
    fecha_inicio DATE,
    fecha_fin DATE,
    activo TINYINT(1) DEFAULT 1,
    created_by BIGINT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- MÓDULO: FIRMA DIGITAL
-- -----------------------------------------------------

CREATE TABLE wp_ga_firmas_documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    documento_tipo VARCHAR(50) NOT NULL, -- contrato, nda, orden_pago
    documento_id INT NOT NULL,
    url_documento_original VARCHAR(500),
    url_documento_firmado VARCHAR(500),
    firmante_tipo ENUM('APLICANTE', 'EMPLEADO', 'CLIENTE', 'EMPRESA') NOT NULL,
    firmante_id INT NOT NULL,
    firmante_nombre VARCHAR(200),
    firmante_email VARCHAR(200),
    firmante_documento VARCHAR(50),
    firma_imagen_url VARCHAR(500),
    posicion_firma ENUM('IZQUIERDA', 'CENTRO', 'DERECHA') DEFAULT 'CENTRO',
    ip_firma VARCHAR(45),
    user_agent VARCHAR(500),
    hash_documento VARCHAR(100),
    fecha_firma DATETIME NOT NULL,
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_documento (documento_tipo, documento_id),
    INDEX idx_firmante (firmante_tipo, firmante_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- DATOS INICIALES
-- -----------------------------------------------------

-- Países
INSERT INTO wp_ga_paises_config (codigo_iso, nombre, moneda_codigo, moneda_simbolo, impuesto_nombre, impuesto_porcentaje, retencion_default, requiere_electronica, proveedor_electronica) VALUES
('US', 'Estados Unidos', 'USD', '$', 'Sales Tax', 0.00, 0.00, 0, NULL),
('CO', 'Colombia', 'COP', '$', 'IVA', 19.00, 11.00, 1, 'DIAN'),
('MX', 'México', 'MXN', '$', 'IVA', 16.00, 10.00, 1, 'SAT'),
('CL', 'Chile', 'CLP', '$', 'IVA', 19.00, 0.00, 1, 'SII'),
('PE', 'Perú', 'PEN', 'S/', 'IGV', 18.00, 0.00, 1, 'SUNAT'),
('PA', 'Panamá', 'PAB', 'B/.', 'ITBMS', 7.00, 0.00, 1, 'DGI'),
('ES', 'España', 'EUR', '€', 'IVA', 21.00, 0.00, 1, 'AEAT');

-- Bonos predeterminados
INSERT INTO wp_ga_bonos (codigo, nombre, tipo, valor_tipo, valor, condiciones) VALUES
('BONO-PROD', 'Bono Productividad', 'PRODUCTIVIDAD', 'MONTO_FIJO', 50.00, '150+ horas QA aprobadas en el mes'),
('BONO-PUNT', 'Bono Puntualidad', 'PUNTUALIDAD', 'MONTO_FIJO', 25.00, 'Cero tardanzas en el mes'),
('BONO-CAL', 'Bono Calidad', 'CALIDAD', 'MONTO_FIJO', 30.00, 'Cero tareas rechazadas en el mes'),
('BONO-REF', 'Bono Referido', 'REFERIDO', 'MONTO_FIJO', 100.00, 'Referido cumple 3 meses en la empresa');
```

---

# PARTE 13: INTEGRACIONES

---

## 45. WOLK POS (Facturación Electrónica)

### 45.1 Países y Entidades Tributarias

| País | Entidad | Documentos Soportados |
|------|---------|----------------------|
| 🇨🇴 Colombia | DIAN | Factura electrónica, Nota crédito |
| 🇲🇽 México | SAT | CFDI 4.0, Complemento de pago |
| 🇨🇱 Chile | SII | DTE, Boleta electrónica |
| 🇵🇪 Perú | SUNAT | Factura electrónica, Guía remisión |
| 🇵🇦 Panamá | DGI | Factura electrónica |
| 🇺🇸 USA | IRS | Invoice (no electrónica requerida) |
| 🇪🇸 España | AEAT | TicketBAI, SII |

### 45.2 Flujo de Integración (Fase 2)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    INTEGRACIÓN WOLK POS - FASE 2                        │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  GESTIONADMIN                      WOLK POS                DIAN/SAT    │
│  ═══════════                       ════════                ════════    │
│                                                                         │
│  PM aprueba      API POST          Genera factura          Valida      │
│  facturar  ───► /invoices ───────► electrónica   ───────► firma OK    │
│                                         │                    │         │
│                                         │                    │         │
│                                         ▼                    │         │
│  Estado:               Webhook ◄─── PDF + XML  ◄────────────┘         │
│  FACTURADA   ◄────── actualiza      firmados                           │
│                       estado                                           │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 45.3 API Endpoints Wolk POS

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | /api/v1/invoices | Crear factura |
| GET | /api/v1/invoices/{id} | Consultar estado |
| POST | /api/v1/invoices/{id}/cancel | Anular factura |
| POST | /api/v1/credit-notes | Crear nota crédito |
| GET | /api/v1/invoices/{id}/pdf | Descargar PDF |
| GET | /api/v1/invoices/{id}/xml | Descargar XML |

### 45.4 Payload de Ejemplo

```json
{
  "country": "CO",
  "customer": {
    "document_type": "NIT",
    "document_number": "900123456",
    "name": "ABC Corporation S.A.S",
    "email": "facturacion@abccorp.com",
    "address": "Calle 123 #45-67",
    "city": "Bogotá"
  },
  "items": [
    {
      "description": "Desarrollo App Móvil - Sprint 3",
      "quantity": 80,
      "unit_price": 15.00,
      "tax_rate": 19.00
    }
  ],
  "payment_terms": 30,
  "currency": "USD",
  "notes": "Proyecto App Móvil Cliente ABC",
  "webhook_url": "https://gestionadmin.com/api/webhook/invoice"
}
```

---

## 46. TIME DOCTOR (Control de Tiempo)

### 46.1 Datos Obtenidos

| Dato | Descripción | Uso |
|------|-------------|-----|
| Horas verificadas | Tiempo con timer activo | Validar vs reportado |
| Screenshots | Capturas periódicas | Auditoría |
| Apps/sitios | Aplicaciones usadas | Productividad |
| Actividad | Keystrokes, mouse | Nivel de actividad |
| Webcam | Foto periódica (opcional) | Verificación identidad |

### 46.2 Flujo de Sincronización

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    INTEGRACIÓN TIME DOCTOR - FASE 2                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  TIME DOCTOR                                         GESTIONADMIN       │
│  ═══════════                                         ═══════════        │
│                                                                         │
│  Empleado trabaja                                                       │
│  con timer TD    ────────► Sync cada 15 min ────────► Actualiza        │
│                                                       horas parciales   │
│                                                                         │
│  Fin del día     ────────► API GET worklogs ────────► Compara con      │
│                                                       reportado         │
│                                                              │          │
│                                                              ▼          │
│                                                       ¿Diferencia      │
│                                                        > 30 min?        │
│                                                         /    \         │
│                                                       No      Sí       │
│                                                       │       │        │
│                                                       ▼       ▼        │
│                                                    OK ✅   Alerta ⚠️   │
│                                                            al jefe     │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 46.3 API Endpoints Time Doctor

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | /api/1.0/users | Listar usuarios |
| GET | /api/1.0/worklogs | Obtener registros de trabajo |
| GET | /api/1.0/screenshots | Obtener screenshots |
| GET | /api/1.0/activities | Actividad por aplicación |
| GET | /api/1.0/projects | Proyectos Time Doctor |

### 46.4 Mapeo de Campos

| Time Doctor | GestionAdmin |
|-------------|--------------|
| user_id | wp_ga_usuarios.timedoctor_user_id |
| project_id | wp_ga_proyectos.timedoctor_project_id |
| task_id | wp_ga_tareas.timedoctor_task_id |

---

## 47. PROCESADORES DE PAGO

### 47.1 Procesadores Soportados

| Procesador | Región | Tipo | Uso Principal |
|------------|--------|------|---------------|
| **Stripe** | Global | Tarjetas, ACH | USA/EUR, empresas grandes |
| **PayPal** | Global | Wallet, tarjetas | Freelancers, SMBs |
| **Payoneer** | Global | Transferencia | Pagos internacionales |
| **Wise** | Global | Transferencia | Bajo costo FX |
| **MercadoPago** | LATAM | Tarjetas, PSE | Colombia, México, Argentina |
| **PayU** | LATAM | Tarjetas, PSE | Colombia principalmente |
| **Wompi** | Colombia | Tarjetas, PSE, Nequi | Colombia |

### 47.2 Flujo de Payment Link

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE LINK DE PAGO                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  GESTIONADMIN           PROCESADOR            CLIENTE                   │
│  ═══════════            ══════════            ═══════                   │
│                                                                         │
│  Crear link    API POST   Genera               Recibe                   │
│  de pago  ───► checkout ─► checkout URL ──────► email                  │
│                              │                    │                     │
│                              │                    ▼                     │
│                              │               Paga en                    │
│                              │               checkout                   │
│                              │                    │                     │
│                              │                    ▼                     │
│  Estado:           Webhook    Confirma           Éxito ✅               │
│  PAGADA   ◄────── payment ◄── pago                                     │
│                   confirmed                                             │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 47.3 Vista de Factura con Opciones de Pago

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 📄 FACTURA FAC-2024-089 - ABC Corporation                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ Total a pagar: $1,771.20 USD                                           │
│ Vencimiento: 12 Enero 2025                                             │
│                                                                         │
│ 💳 OPCIONES DE PAGO                                                     │
│ ┌─────────────────────────────────────────────────────────────────────┐│
│ │                                                                     ││
│ │  [💳 Pagar con Stripe]     Tarjeta crédito/débito                   ││
│ │                                                                     ││
│ │  [🅿️ Pagar con PayPal]     Cuenta PayPal o tarjeta                  ││
│ │                                                                     ││
│ │  [🏦 Transferencia bancaria]                                        ││
│ │     Banco: Bancolombia                                              ││
│ │     Cuenta: 123-456789-00                                           ││
│ │     Titular: Empresa XYZ S.A.S                                      ││
│ │     Referencia: FAC-2024-089                                        ││
│ │                                                                     ││
│ └─────────────────────────────────────────────────────────────────────┘│
│                                                                         │
│ [📧 Enviar link de pago por email]                                     │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

# PARTE 14: PLAN DE TRABAJO

---

## 48. FASES DEL PROYECTO

### 48.1 Visión General

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        ROADMAP GESTIONADMIN                             │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  FASE 1: MVP                        FASE 2: INTEGRACIONES               │
│  ══════════                         ══════════════════════              │
│  ~660 horas                         ~250 horas                          │
│  15-16 semanas                      8-10 semanas                        │
│                                                                         │
│  ✓ Estructura organizacional        ✓ Wolk POS (factura elect.)        │
│  ✓ Tareas y timer                   ✓ Time Doctor                       │
│  ✓ 8 dashboards por rol             ✓ Procesadores de pago              │
│  ✓ Portal cliente                   ✓ Payment links                     │
│  ✓ Portal órdenes trabajo           ✓ Webhooks                          │
│  ✓ Facturación manual               ✓ Reportes avanzados                │
│  ✓ Pagos a prestadores                                                  │
│  ✓ Firma digital                                                        │
│  ✓ Reglas de trabajo                                                    │
│  ✓ Escalas de tarifa                                                    │
│                                                                         │
│  ───────────────────────────────────────────────────────────────────── │
│                                                                         │
│  FASE 3: INTELIGENCIA               FASE 4: MÓVIL                       │
│  ════════════════════               ══════════════                      │
│  ~150 horas                         ~200 horas                          │
│  6-8 semanas                        8-10 semanas                        │
│                                                                         │
│  ✓ AI estimación tiempos            ✓ App React Native                  │
│  ✓ Detección anomalías              ✓ Push notifications                │
│  ✓ Asignación inteligente           ✓ Timer móvil                       │
│  ✓ BI personalizable                ✓ Firma en móvil                    │
│  ✓ Proyecciones financieras         ✓ Offline sync                      │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 49. CRONOGRAMA FASE 1 (MVP)

### 49.1 Desglose por Módulo

| # | Módulo | Horas | Semanas | Prioridad |
|---|--------|-------|---------|-----------|
| 1 | Estructura base + DB | 40 | 1 | 🔴 CRÍTICO |
| 2 | Departamentos/Puestos/Usuarios | 50 | 1.5 | 🔴 CRÍTICO |
| 3 | Sistema de Tareas | 80 | 2 | 🔴 CRÍTICO |
| 4 | Timer y Registro Horas | 60 | 1.5 | 🔴 CRÍTICO |
| 5 | Flujos de Revisión | 40 | 1 | 🟡 ALTO |
| 6 | Clientes y Casos | 50 | 1 | 🟡 ALTO |
| 7 | Portal Cliente | 60 | 1.5 | 🟡 ALTO |
| 8 | Portal Órdenes Trabajo | 70 | 2 | 🟡 ALTO |
| 9 | Contratos y Firma Digital | 40 | 1 | 🟡 ALTO |
| 10 | Facturación (manual) | 50 | 1.5 | 🔴 CRÍTICO |
| 11 | Pagos a Prestadores | 50 | 1.5 | 🔴 CRÍTICO |
| 12 | Bonos/Penalidades/Comisiones | 40 | 1 | 🟢 MEDIO |
| 13 | Escalas y Revisiones Tarifa | 30 | 1 | 🟢 MEDIO |
| 14 | Reglas de Trabajo | 20 | 0.5 | 🟢 MEDIO |
| 15 | Calendario Administrativo | 15 | 0.5 | 🟢 MEDIO |
| 16 | Dashboard Inversionista | 25 | 0.5 | 🟢 MEDIO |
| 17 | 8 Dashboards por Rol | 80 | 2 | 🔴 CRÍTICO |
| 18 | Reportes Básicos | 40 | 1 | 🟡 ALTO |
| 19 | AI Chat (básico) | 20 | 0.5 | 🟢 MEDIO |
| 20 | Importación Excel | 20 | 0.5 | 🟢 MEDIO |
| **TOTAL** | | **~860** | **~22** | |

### 49.2 Orden de Desarrollo Sugerido

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    ORDEN DE DESARROLLO - FASE 1                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  SPRINT 1-2 (Semanas 1-4): FUNDAMENTOS                                  │
│  ═════════════════════════════════════                                  │
│  ☐ Base de datos completa                                               │
│  ☐ Departamentos, puestos, usuarios                                     │
│  ☐ Roles y permisos                                                     │
│  ☐ Sistema de supervisiones                                             │
│                                                                         │
│  SPRINT 3-4 (Semanas 5-8): CORE OPERATIVO                               │
│  ═════════════════════════════════════════                              │
│  ☐ Catálogo de tareas                                                   │
│  ☐ Tareas y subtareas                                                   │
│  ☐ Timer con pausas                                                     │
│  ☐ Registro de horas                                                    │
│  ☐ Flujo QA → Jefe → Aprobado                                          │
│                                                                         │
│  SPRINT 5-6 (Semanas 9-12): CLIENTES Y FACTURACIÓN                      │
│  ═══════════════════════════════════════════════                        │
│  ☐ Gestión de clientes                                                  │
│  ☐ Casos y proyectos                                                    │
│  ☐ Portal del cliente                                                   │
│  ☐ Solicitud de factura                                                 │
│  ☐ Procesamiento en contabilidad                                        │
│  ☐ Registro de pagos recibidos                                          │
│                                                                         │
│  SPRINT 7-8 (Semanas 13-16): MARKETPLACE Y PAGOS                        │
│  ═══════════════════════════════════════════════                        │
│  ☐ Portal órdenes de trabajo                                            │
│  ☐ Registro de aplicantes                                               │
│  ☐ Proceso de aplicación                                                │
│  ☐ Contratos de trabajo                                                 │
│  ☐ Firma digital                                                        │
│  ☐ Botón COBRAR                                                         │
│  ☐ Pago a prestadores                                                   │
│                                                                         │
│  SPRINT 9-10 (Semanas 17-20): COMPENSACIÓN Y ADMIN                      │
│  ═════════════════════════════════════════════════                      │
│  ☐ Escalas de tarifa                                                    │
│  ☐ Revisiones de tarifa                                                 │
│  ☐ Bonos y penalidades                                                  │
│  ☐ Comisiones multinivel                                                │
│  ☐ Reglas de trabajo                                                    │
│  ☐ Calendario administrativo                                            │
│                                                                         │
│  SPRINT 11 (Semanas 21-22): DASHBOARDS Y CIERRE                         │
│  ═════════════════════════════════════════════                          │
│  ☐ Dashboard Socio                                                      │
│  ☐ Dashboard Director                                                   │
│  ☐ Dashboard Jefe/PM                                                    │
│  ☐ Dashboard Empleado                                                   │
│  ☐ Dashboard Cliente                                                    │
│  ☐ Dashboard Contabilidad                                               │
│  ☐ Dashboard Inversionista                                              │
│  ☐ Reportes básicos                                                     │
│  ☐ AI Chat básico                                                       │
│  ☐ QA final y ajustes                                                   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 50. ESTIMACIÓN DE HORAS

### 50.1 Resumen por Fase

| Fase | Horas | Semanas | Costo Est. (@ $15/hr) |
|------|-------|---------|----------------------|
| **Fase 1: MVP** | 660-860 | 16-22 | $9,900 - $12,900 |
| **Fase 2: Integraciones** | 200-300 | 8-12 | $3,000 - $4,500 |
| **Fase 3: Inteligencia** | 150-200 | 6-8 | $2,250 - $3,000 |
| **Fase 4: Móvil** | 200-250 | 8-10 | $3,000 - $3,750 |
| **TOTAL** | **1,210-1,610** | **38-52** | **$18,150 - $24,150** |

### 50.2 Entregables por Fase

**FASE 1 - MVP:**
- ✅ Sistema funcional completo
- ✅ 8 dashboards operativos
- ✅ Portal cliente
- ✅ Portal marketplace
- ✅ Facturación manual
- ✅ Pagos a prestadores
- ✅ Firma digital
- ✅ Documentación básica

**FASE 2 - Integraciones:**
- ✅ Facturación electrónica automática
- ✅ Verificación Time Doctor
- ✅ Payment links
- ✅ Webhooks configurados
- ✅ Reportes financieros avanzados

**FASE 3 - Inteligencia:**
- ✅ Estimación de tiempos con IA
- ✅ Alertas de productividad
- ✅ Asignación inteligente
- ✅ Proyecciones financieras
- ✅ BI personalizable

**FASE 4 - Móvil:**
- ✅ App iOS/Android
- ✅ Timer móvil
- ✅ Notificaciones push
- ✅ Firma en móvil
- ✅ Modo offline

---

## ANEXO: CHECKLIST DE INICIO

### Antes de comenzar desarrollo:

```
☐ Confirmar alcance Fase 1
☐ Definir prioridades exactas
☐ Configurar ambiente de desarrollo
☐ Crear repositorio Git
☐ Instalar WordPress base
☐ Crear estructura del plugin
☐ Ejecutar scripts de base de datos
☐ Configurar usuarios de prueba
☐ Definir datos de prueba
☐ Establecer metodología de sprints
```

---

# FIN DEL DOCUMENTO

**Versión:** 1.0
**Fecha:** Diciembre 2024
**Páginas estimadas:** 120+
**Tablas de base de datos:** 40+
**Horas de desarrollo estimadas:** 660-860 (Fase 1)

---

*Este documento contiene la visión completa del proyecto GestionAdmin. Cualquier cambio o adición debe ser documentado en versiones posteriores.*
