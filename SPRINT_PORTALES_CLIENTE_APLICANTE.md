# SPRINT: Portal Cliente + Portal Aplicante

> **Proyecto:** GestionAdmin Wolk
> **Fecha:** Diciembre 2024
> **Prerequisito:** Portal Empleado ✅ COMPLETADO

---

## 📊 Resumen de Portales

| Portal | URL | Usuario | Propósito |
|--------|-----|---------|-----------|
| **Cliente** | `/cliente/` | Registro en `wp_ga_clientes` | Ver proyectos, facturas, progreso |
| **Aplicante** | `/mi-cuenta/` | Registro en `wp_ga_aplicantes` | Aplicar a órdenes de trabajo |

---

# 🔵 PORTAL CLIENTE

## Contexto

El cliente es una persona o empresa que contrata servicios. Tiene:
- Registro en `wp_ga_clientes` con `usuario_wp_id`
- Casos/expedientes en `wp_ga_casos`
- Proyectos en `wp_ga_proyectos`
- Facturas en `wp_ga_facturas`

## Tablas Relevantes

```
wp_ga_clientes
├── id, usuario_wp_id (FK wp_users)
├── codigo, tipo (PERSONA_NATURAL, EMPRESA)
├── nombre, email, telefono
├── direccion, pais_codigo
└── activo

wp_ga_casos
├── id, cliente_id (FK)
├── codigo, nombre, descripcion
├── estado (ACTIVO, EN_PAUSA, CERRADO, ARCHIVADO)
└── fecha_apertura, fecha_cierre

wp_ga_proyectos
├── id, caso_id (FK), cliente_id (FK)
├── codigo, nombre, descripcion
├── estado, porcentaje_avance
├── fecha_inicio, fecha_fin_estimada
└── presupuesto_aprobado

wp_ga_facturas
├── id, cliente_id (FK), proyecto_id (FK)
├── numero, fecha_emision, fecha_vencimiento
├── subtotal, impuestos, total
├── estado (BORRADOR, ENVIADA, PAGADA, VENCIDA, ANULADA)
└── moneda, notas
```

---

## 🎯 SPRINT C1: Dashboard Cliente
**Archivo:** `templates/portal-cliente/dashboard.php`
**Duración:** 2-3 horas

### Tareas:

#### C1.1 Verificar cliente
```php
// Obtener cliente por usuario WP
$cliente = GA_Clientes::get_by_wp_id($wp_user_id);
if (!$cliente) {
    // Mostrar mensaje "No eres cliente registrado"
}
```

#### C1.2 Resumen de casos
```php
// Contar casos por estado
// - Casos activos
// - Casos en pausa
// - Casos cerrados
```

#### C1.3 Resumen de proyectos
```php
// Proyectos del cliente con estado y avance
// - En progreso
// - Completados
// - Porcentaje promedio de avance
```

#### C1.4 Resumen de facturas
```php
// Facturas pendientes de pago
// - Total pendiente
// - Próxima a vencer
// - Facturas vencidas (alerta)
```

#### C1.5 Navegación del portal
```php
// Links a:
// - Dashboard
// - Mis Casos
// - Mis Facturas
// - Mi Perfil
```

### Wireframe Dashboard Cliente:
```
┌─────────────────────────────────────────────────────────────────┐
│ 👤 Bienvenido, [Nombre Cliente/Empresa]                         │
│ Código: CLI-001 | Tipo: Empresa                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 📊 RESUMEN                                                      │
│ ┌────────────┬────────────┬────────────┬────────────┐          │
│ │ Casos      │ Proyectos  │ Facturas   │ Por Pagar  │          │
│ │ Activos    │ En Curso   │ Pendientes │            │          │
│ │     3      │     5      │     2      │  $1,250    │          │
│ └────────────┴────────────┴────────────┴────────────┘          │
│                                                                 │
│ ⚠️ FACTURAS PRÓXIMAS A VENCER                                   │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ FAC-2024-0045  |  $500.00  |  Vence: 18 Dic  | [Ver →]      │ │
│ │ FAC-2024-0048  |  $750.00  |  Vence: 22 Dic  | [Ver →]      │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 📁 PROYECTOS RECIENTES                                          │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ PROY-001 Rediseño Web        ████████░░ 80%  EN_PROGRESO   │ │
│ │ PROY-002 App Móvil           ███░░░░░░░ 30%  EN_PROGRESO   │ │
│ │ PROY-003 Integración API     ██████████ 100% COMPLETADO    │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🔗 [📁 Mis Casos] [📄 Mis Facturas] [👤 Mi Perfil]              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 SPRINT C2: Mis Casos
**Archivo:** `templates/portal-cliente/mis-casos.php`
**Duración:** 2-3 horas

### Tareas:

#### C2.1 Listar casos del cliente
```php
// Query a wp_ga_casos WHERE cliente_id = $cliente->id
// Ordenar por fecha_apertura DESC
```

#### C2.2 Filtros
- Por estado: ACTIVO, EN_PAUSA, CERRADO
- Búsqueda por nombre/código

#### C2.3 Vista de cada caso
- Código y nombre
- Estado con color
- Fecha apertura
- Cantidad de proyectos asociados
- Link a ver proyectos del caso

#### C2.4 Detalle expandible
- Descripción del caso
- Lista de proyectos con estado y avance

### Wireframe Mis Casos:
```
┌─────────────────────────────────────────────────────────────────┐
│ 📁 Mis Casos                                     [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│ Filtros: [Todos ▼]                              🔍 Buscar...   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 🟢 ACTIVO | CASO-2024-001                                       │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Desarrollo Plataforma E-commerce                            │ │
│ │ Abierto: 15 Oct 2024 | Proyectos: 3                        │ │
│ │ [▼ Ver proyectos]                                          │ │
│ │                                                             │ │
│ │ └─ PROY-001 Backend API      ████████░░ 80%                │ │
│ │ └─ PROY-002 Frontend React   ███████░░░ 70%                │ │
│ │ └─ PROY-003 App Móvil        ███░░░░░░░ 30%                │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🟡 EN_PAUSA | CASO-2024-002                                     │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Consultoría Legal                                           │ │
│ │ Abierto: 01 Sep 2024 | Proyectos: 1                        │ │
│ │ [▼ Ver proyectos]                                          │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 SPRINT C3: Mis Facturas
**Archivo:** `templates/portal-cliente/mis-facturas.php`
**Duración:** 3-4 horas

### Tareas:

#### C3.1 Listar facturas del cliente
```php
// Query a wp_ga_facturas WHERE cliente_id = $cliente->id
// Incluir detalle de wp_ga_facturas_detalle
```

#### C3.2 Filtros
- Por estado: ENVIADA, PAGADA, VENCIDA
- Por período: Este mes, últimos 3 meses, año

#### C3.3 Vista de cada factura
- Número y fecha
- Estado con color (alerta si vencida)
- Total
- Botón "Ver PDF" o "Descargar"

#### C3.4 Resumen superior
- Total pendiente
- Total pagado (período)
- Facturas vencidas (cantidad y monto)

### Wireframe Mis Facturas:
```
┌─────────────────────────────────────────────────────────────────┐
│ 📄 Mis Facturas                                  [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 💰 RESUMEN                                                      │
│ ┌────────────┬────────────┬────────────┐                       │
│ │ Pendiente  │ Pagado     │ Vencido    │                       │
│ │ $1,250.00  │ $3,500.00  │ $0.00      │                       │
│ └────────────┴────────────┴────────────┘                       │
│                                                                 │
│ Filtros: [Todas ▼] [Este año ▼]                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 🟡 PENDIENTE | FAC-2024-0045                                    │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Fecha: 01 Dic 2024 | Vence: 18 Dic 2024                    │ │
│ │ Proyecto: PROY-001 Rediseño Web                            │ │
│ │ Total: $500.00 USD                                         │ │
│ │                              [📄 Ver PDF] [💳 Pagar]        │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🟢 PAGADA | FAC-2024-0042                                       │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Fecha: 15 Nov 2024 | Pagada: 20 Nov 2024                   │ │
│ │ Proyecto: PROY-003 Integración API                         │ │
│ │ Total: $1,200.00 USD                                       │ │
│ │                              [📄 Ver PDF]                   │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 SPRINT C4: Mi Perfil (Cliente)
**Archivo:** `templates/portal-cliente/mi-perfil.php`
**Duración:** 1-2 horas

### Tareas:
- Mostrar datos del cliente (nombre, tipo, código)
- Datos de contacto
- Información fiscal (si aplica)
- Enlace cambiar contraseña

---

# 🟣 PORTAL APLICANTE

## Contexto

El aplicante es un freelancer o empresa que aplica a órdenes de trabajo del marketplace. Tiene:
- Registro en `wp_ga_aplicantes` con `usuario_wp_id`
- Aplicaciones en `wp_ga_aplicaciones_orden`
- Cuando es aceptado, puede tener órdenes asignadas

## Tablas Relevantes

```
wp_ga_aplicantes
├── id, usuario_wp_id (FK wp_users)
├── codigo, tipo (PERSONA_NATURAL, EMPRESA)
├── nombre, email, telefono
├── pais_codigo, identificacion_fiscal
├── cv_url, portafolio_url
├── habilidades (JSON)
├── estado_verificacion (PENDIENTE, VERIFICADO, RECHAZADO)
└── activo

wp_ga_ordenes_trabajo
├── id, codigo, titulo, descripcion
├── tipo_trabajo, modalidad
├── habilidades_requeridas (JSON)
├── presupuesto_min, presupuesto_max
├── estado (BORRADOR, PUBLICADA, EN_PROCESO, COMPLETADA, CANCELADA)
└── fecha_publicacion, fecha_limite

wp_ga_aplicaciones_orden
├── id, orden_id (FK), aplicante_id (FK)
├── propuesta (TEXT)
├── tarifa_propuesta, tiempo_estimado
├── estado (ENVIADA, EN_REVISION, ACEPTADA, RECHAZADA)
└── fecha_aplicacion
```

---

## 🎯 SPRINT A1: Dashboard Aplicante
**Archivo:** `templates/portal-aplicante/dashboard.php`
**Duración:** 2-3 horas

### Tareas:

#### A1.1 Verificar aplicante
```php
// Obtener aplicante por usuario WP
$aplicante = GA_Aplicantes::get_by_wp_id($wp_user_id);
if (!$aplicante) {
    // Redirigir a registro
}
```

#### A1.2 Estado de verificación
- Mostrar si está PENDIENTE, VERIFICADO, RECHAZADO
- Si pendiente, mostrar qué documentos faltan

#### A1.3 Resumen de aplicaciones
- Aplicaciones enviadas
- En revisión
- Aceptadas
- Rechazadas

#### A1.4 Órdenes recomendadas
- Mostrar órdenes que coincidan con sus habilidades
- Link al marketplace

#### A1.5 Navegación
```php
// Links a:
// - Dashboard
// - Mis Aplicaciones
// - Marketplace (ver órdenes)
// - Mis Pagos (si tiene órdenes completadas)
// - Mi Perfil
```

### Wireframe Dashboard Aplicante:
```
┌─────────────────────────────────────────────────────────────────┐
│ 👤 Bienvenido, [Nombre Aplicante]                               │
│ Código: APL-001 | Estado: ✅ VERIFICADO                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 📊 MIS APLICACIONES                                             │
│ ┌────────────┬────────────┬────────────┬────────────┐          │
│ │ Enviadas   │ En Revisión│ Aceptadas  │ Rechazadas │          │
│ │     8      │     2      │     3      │     3      │          │
│ └────────────┴────────────┴────────────┴────────────┘          │
│                                                                 │
│ 🔔 APLICACIONES EN REVISIÓN                                     │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ OT-2024-015 Desarrollo WordPress                           │ │
│ │ Aplicaste: 10 Dic | Estado: EN_REVISION                    │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 💼 ÓRDENES RECOMENDADAS PARA TI                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ OT-2024-018 Desarrollo API REST                            │ │
│ │ Presupuesto: $500-$800 | Fecha límite: 20 Dic              │ │
│ │ Tags: PHP, WordPress, API          [Ver detalles →]        │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🔗 [📋 Mis Aplicaciones] [🛒 Ver Marketplace] [👤 Mi Perfil]    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 SPRINT A2: Mis Aplicaciones
**Archivo:** `templates/portal-aplicante/mis-aplicaciones.php`
**Duración:** 2-3 horas

### Tareas:

#### A2.1 Listar aplicaciones
```php
// Query wp_ga_aplicaciones_orden WHERE aplicante_id = $aplicante->id
// JOIN con wp_ga_ordenes_trabajo para datos de la orden
```

#### A2.2 Filtros
- Por estado: ENVIADA, EN_REVISION, ACEPTADA, RECHAZADA
- Por fecha

#### A2.3 Vista de cada aplicación
- Orden (código y título)
- Fecha de aplicación
- Propuesta enviada
- Tarifa propuesta
- Estado con color

#### A2.4 Acciones
- Si ACEPTADA: ver detalles del acuerdo
- Si RECHAZADA: ver motivo (si lo hay)

### Wireframe Mis Aplicaciones:
```
┌─────────────────────────────────────────────────────────────────┐
│ 📋 Mis Aplicaciones                              [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│ Filtros: [Todas ▼]                              🔍 Buscar...   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 🟢 ACEPTADA | OT-2024-012                                       │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Desarrollo Plugin WordPress                                 │ │
│ │ Aplicaste: 01 Dic | Tu propuesta: $600                     │ │
│ │ Estado: ACEPTADA ✓                                         │ │
│ │                              [Ver Acuerdo →]               │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🟡 EN_REVISION | OT-2024-015                                    │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Desarrollo WordPress Avanzado                               │ │
│ │ Aplicaste: 10 Dic | Tu propuesta: $450                     │ │
│ │ Estado: EN_REVISION (esperando respuesta)                  │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🔴 RECHAZADA | OT-2024-010                                      │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Diseño UI/UX                                                │ │
│ │ Aplicaste: 25 Nov | Tu propuesta: $300                     │ │
│ │ Estado: RECHAZADA                                          │ │
│ │ Motivo: "Buscamos perfil con más experiencia en Figma"     │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 SPRINT A3: Mi Perfil Aplicante
**Archivo:** `templates/portal-aplicante/mi-perfil.php`
**Duración:** 2-3 horas

### Tareas:

#### A3.1 Datos personales
- Nombre, email, teléfono
- País, identificación fiscal

#### A3.2 Documentos
- CV (subir/actualizar)
- Portafolio URL
- Documentos de identidad

#### A3.3 Habilidades
- Lista de habilidades/tags
- Poder agregar/quitar

#### A3.4 Estado de verificación
- Ver estado actual
- Ver documentos faltantes

### Wireframe Mi Perfil Aplicante:
```
┌─────────────────────────────────────────────────────────────────┐
│ 👤 Mi Perfil                                     [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ [Avatar]  Juan Developer                                    │ │
│ │           juan@email.com | +506 8888-8888                   │ │
│ │           APL-001 | VERIFICADO ✅                           │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 📄 DOCUMENTOS                                                   │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ CV: cv_juan_developer.pdf        [📄 Ver] [↑ Actualizar]   │ │
│ │ Portafolio: github.com/juandev   [✏️ Editar]               │ │
│ │ Documento ID: ✅ Verificado                                 │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🏷️ MIS HABILIDADES                                              │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ [PHP] [WordPress] [JavaScript] [React] [MySQL] [+Agregar]  │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 💰 MÉTODO DE PAGO                                               │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Método preferido: Binance Pay                               │ │
│ │ ID: ****4521                                                │ │
│ │ [Contactar para cambios]                                    │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 SPRINT A4: Mis Pagos (Aplicante)
**Archivo:** `templates/portal-aplicante/mis-pagos.php`
**Duración:** 2 horas

### Tareas:
- Historial de pagos recibidos
- Estado de cada pago
- Total ganado por período

---

## 📁 Archivos a Modificar

### Portal Cliente:
```
templates/portal-cliente/
├── dashboard.php      ← SPRINT C1
├── mis-casos.php      ← SPRINT C2
├── mis-facturas.php   ← SPRINT C3
└── mi-perfil.php      ← SPRINT C4
```

### Portal Aplicante:
```
templates/portal-aplicante/
├── dashboard.php          ← SPRINT A1
├── mis-aplicaciones.php   ← SPRINT A2
├── mi-perfil.php          ← SPRINT A3
├── mis-pagos.php          ← SPRINT A4
├── registro.php           ← Ya existe (verificar)
└── login-required.php     ← Ya existe (verificar)
```

---

## 🚀 Orden de Ejecución Recomendado

### Fase 1: Portal Cliente (Sprints C1-C4)
1. **C1** - Dashboard Cliente
2. **C2** - Mis Casos
3. **C3** - Mis Facturas
4. **C4** - Mi Perfil Cliente

### Fase 2: Portal Aplicante (Sprints A1-A4)
1. **A1** - Dashboard Aplicante
2. **A2** - Mis Aplicaciones
3. **A3** - Mi Perfil Aplicante
4. **A4** - Mis Pagos

---

## 🔒 Consideraciones de Seguridad

Cada template debe verificar:

```php
<?php
// SEGURIDAD: Verificar acceso directo
if (!defined('ABSPATH')) exit;

// SEGURIDAD: Usuario logueado
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// SEGURIDAD: Verificar que es CLIENTE
$cliente = GA_Clientes::get_by_wp_id(get_current_user_id());
if (!$cliente) {
    wp_redirect(home_url('/'));
    exit;
}

// SEGURIDAD: Solo ver sus propios datos
// WHERE cliente_id = $cliente->id
```

---

## ✅ Checklist Final

### Portal Cliente:
- [ ] C1: Dashboard funcional
- [ ] C2: Lista de casos con proyectos
- [ ] C3: Facturas con estados y PDF
- [ ] C4: Perfil cliente

### Portal Aplicante:
- [ ] A1: Dashboard con resumen
- [ ] A2: Mis aplicaciones con estados
- [ ] A3: Perfil con documentos y habilidades
- [ ] A4: Historial de pagos

---

*Documento generado para GestionAdmin Wolk - Diciembre 2024*
