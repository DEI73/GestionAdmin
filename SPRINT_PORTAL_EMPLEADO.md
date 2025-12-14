# SPRINT: Portal del Empleado Funcional

> **Proyecto:** GestionAdmin Wolk
> **Fecha:** Diciembre 2024
> **Objetivo:** Implementar el portal del empleado con dashboard, tareas, timer y horas funcionales

---

## 📊 Estado Actual

### ✅ Lo que YA existe y funciona:
- Roles WP: `ga_socio`, `ga_director`, `ga_jefe`, `ga_empleado`, `ga_cliente`, `ga_aplicante`
- Bloqueo de wp-admin para roles invitados
- Redirección post-login por rol
- Módulo `GA_Tareas` con timer completo (start/stop/pause/resume)
- Módulo `GA_Usuarios` con `get_by_wp_id()`
- Tablas: `wp_ga_usuarios`, `wp_ga_tareas`, `wp_ga_subtareas`, `wp_ga_registro_horas`, `wp_ga_pausas_timer`
- Templates placeholder en `/templates/portal-empleado/`

### ❌ Lo que FALTA (solo placeholders "En Desarrollo"):
- `dashboard.php` - No muestra datos reales
- `mis-tareas.php` - No muestra datos reales
- `mi-timer.php` - No muestra datos reales
- `mis-horas.php` - No muestra datos reales
- `mi-perfil.php` - No muestra datos reales
- Endpoints AJAX para el timer desde frontend

---

## 🎯 SPRINT 1: Dashboard del Empleado
**Duración estimada:** 2-3 horas
**Archivo:** `templates/portal-empleado/dashboard.php`

### Tareas:

#### 1.1 Obtener datos del empleado
```php
// Usar GA_Usuarios::get_by_wp_id() para obtener:
// - Nombre, puesto, departamento
// - Fecha de ingreso
// - Nivel jerárquico
```

#### 1.2 Mostrar resumen de tareas
```php
// Usar GA_Tareas::get_all(['asignado_a' => $wp_user_id]) para contar:
// - Tareas PENDIENTE
// - Tareas EN_PROGRESO  
// - Tareas COMPLETADA (del mes actual)
// - Tareas EN_REVISION
```

#### 1.3 Mostrar timer activo (si existe)
```php
// Usar GA_Tareas::get_active_timer($wp_user_id) para mostrar:
// - Tarea actual con timer
// - Tiempo transcurrido
// - Estado (activo/pausado)
// - Botón para ir a Mi Timer
```

#### 1.4 Mostrar métricas del mes
```sql
-- Query para horas del mes actual
SELECT 
    SUM(minutos_efectivos) as minutos_mes,
    COUNT(*) as registros_mes
FROM wp_ga_registro_horas 
WHERE usuario_id = %d 
AND MONTH(fecha) = MONTH(CURRENT_DATE)
AND YEAR(fecha) = YEAR(CURRENT_DATE)
```

#### 1.5 Navegación del portal
```php
// Menú lateral o cards con links a:
// - Mis Tareas (/empleado/mis-tareas/)
// - Mi Timer (/empleado/mi-timer/)
// - Mis Horas (/empleado/mis-horas/)
// - Mi Perfil (/empleado/mi-perfil/)
```

### Wireframe Dashboard:
```
┌─────────────────────────────────────────────────────────────────┐
│ 👤 Bienvenido, [Nombre]                                         │
│ Puesto: [Developer Backend] | Depto: [Desarrollo]               │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ ⏱️ TIMER ACTIVO (si existe)                                     │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Tarea: TASK-2024-0015 - Desarrollo API                      │ │
│ │ Tiempo: 02:15:30  [Estado: Activo]                          │ │
│ │                        [Ir a Mi Timer →]                    │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 📊 RESUMEN DE TAREAS                                            │
│ ┌────────────┬────────────┬────────────┬────────────┐          │
│ │ Pendientes │ En Progreso│ En Revisión│ Completadas│          │
│ │     5      │     2      │     1      │    12      │          │
│ └────────────┴────────────┴────────────┴────────────┘          │
│                                                                 │
│ 📈 ESTE MES                                                     │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Horas trabajadas: 78.5 hrs                                  │ │
│ │ Horas aprobadas:  65.0 hrs                                  │ │
│ │ Listo para cobrar: $195.00 USD                              │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🔗 ACCESOS RÁPIDOS                                              │
│ [📋 Mis Tareas] [⏱️ Mi Timer] [🕐 Mis Horas] [👤 Mi Perfil]     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Entregables Sprint 1:
- [ ] Dashboard con datos reales del usuario
- [ ] Contadores de tareas funcionando
- [ ] Timer activo visible si existe
- [ ] Métricas del mes
- [ ] Navegación funcional

---

## 🎯 SPRINT 2: Mis Tareas
**Duración estimada:** 3-4 horas
**Archivo:** `templates/portal-empleado/mis-tareas.php`

### Tareas:

#### 2.1 Listar tareas del usuario
```php
// GA_Tareas::get_all([
//     'asignado_a' => $wp_user_id,
//     'limit' => 50
// ])
```

#### 2.2 Filtros
- Por estado: PENDIENTE, EN_PROGRESO, COMPLETADA, EN_REVISION
- Por prioridad: URGENTE, ALTA, MEDIA, BAJA
- Por proyecto (si aplica)

#### 2.3 Vista de cada tarea
- Número y nombre
- Estado con color
- Prioridad con ícono
- Fecha límite
- Horas estimadas vs reales
- Subtareas (expandible)
- Botón "Iniciar Timer"

#### 2.4 Detalle expandible de tarea
- Lista de subtareas con estado
- Instrucciones/descripción
- Historial de tiempo registrado

### Wireframe Mis Tareas:
```
┌─────────────────────────────────────────────────────────────────┐
│ 📋 Mis Tareas                                    [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│ Filtros: [Todos ▼] [Todas ▼] [Todos ▼]          🔍 Buscar...   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 🔴 URGENTE | TASK-2024-0018                                     │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Corrección bug login                                        │ │
│ │ Estado: EN_PROGRESO | Vence: Hoy | Est: 2h | Real: 1.5h    │ │
│ │ Subtareas: 2/4 completadas                                  │ │
│ │                                    [▶ Iniciar Timer]        │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🟡 ALTA | TASK-2024-0015                                        │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Desarrollo API pagos                                        │ │
│ │ Estado: PENDIENTE | Vence: 16 Dic | Est: 8h | Real: 0h     │ │
│ │ Subtareas: 0/6 completadas                                  │ │
│ │                                    [▶ Iniciar Timer]        │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🟢 MEDIA | TASK-2024-0012                                       │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Documentación endpoints                                     │ │
│ │ Estado: EN_REVISION | Vence: 20 Dic | Est: 4h | Real: 3.5h │ │
│ │ Subtareas: 4/4 completadas ✓                                │ │
│ │                                    [Ver detalle]            │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Entregables Sprint 2:
- [ ] Lista de tareas con datos reales
- [ ] Filtros funcionando
- [ ] Botón iniciar timer (llama AJAX)
- [ ] Vista de subtareas
- [ ] Paginación si hay muchas tareas

---

## 🎯 SPRINT 3: Mi Timer
**Duración estimada:** 4-5 horas
**Archivos:** 
- `templates/portal-empleado/mi-timer.php`
- `api/timer-endpoints.php` (NUEVO)
- `assets/js/timer.js` (NUEVO)

### Tareas:

#### 3.1 Vista del timer activo
```php
// GA_Tareas::get_active_timer($wp_user_id)
// Mostrar:
// - Tarea y subtarea actual
// - Contador de tiempo en vivo (JavaScript)
// - Estado: Activo / Pausado
// - Tiempo en pausas
```

#### 3.2 Controles del timer
- Botón PAUSAR (con selector de motivo)
- Botón REANUDAR
- Botón DETENER (con campo de descripción)

#### 3.3 Endpoints AJAX
```php
// Crear en api/timer-endpoints.php:
// POST /wp-json/gestionadmin/v1/timer/start
// POST /wp-json/gestionadmin/v1/timer/pause
// POST /wp-json/gestionadmin/v1/timer/resume
// POST /wp-json/gestionadmin/v1/timer/stop
```

#### 3.4 JavaScript del timer
```javascript
// Timer en tiempo real
// Actualización cada segundo
// Manejo de estados
// Llamadas AJAX a endpoints
```

#### 3.5 Historial del día
- Registros de horas del día actual
- Tiempo total trabajado hoy

### Wireframe Mi Timer:
```
┌─────────────────────────────────────────────────────────────────┐
│ ⏱️ Mi Timer                                      [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │                                                             │ │
│ │              ⏱️  02 : 45 : 32                               │ │
│ │                                                             │ │
│ │  Tarea: TASK-2024-0018 - Corrección bug login               │ │
│ │  Subtarea: 1-2 Validar credenciales                         │ │
│ │                                                             │ │
│ │  Estado: 🟢 ACTIVO                                          │ │
│ │  Pausas hoy: 2 (35 min total)                               │ │
│ │                                                             │ │
│ │  ┌──────────────┐  ┌──────────────┐                         │ │
│ │  │  ⏸️ PAUSAR   │  │  ⏹️ DETENER  │                         │ │
│ │  └──────────────┘  └──────────────┘                         │ │
│ │                                                             │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 📊 HOY HAS TRABAJADO                                            │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Total: 5.5 horas                                            │ │
│ │                                                             │ │
│ │ • TASK-0015 API pagos      2:15:00  BORRADOR               │ │
│ │ • TASK-0018 Bug login      2:45:32  ACTIVO ⏱️               │ │
│ │ • TASK-0012 Documentación  0:30:00  ENVIADO                │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ ❌ SIN TIMER ACTIVO (si no hay timer)                          │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ No tienes un timer activo.                                  │ │
│ │ Ve a Mis Tareas para iniciar uno.                          │ │
│ │                        [Ir a Mis Tareas →]                  │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Modal de Pausa:
```
┌─────────────────────────────────────────┐
│ ⏸️ Pausar Timer                    [X] │
├─────────────────────────────────────────┤
│                                         │
│ Motivo de la pausa:                     │
│ ○ Almuerzo                              │
│ ○ Reunión                               │
│ ○ Descanso                              │
│ ○ Emergencia                            │
│ ○ Otro                                  │
│                                         │
│ Nota (opcional):                        │
│ ┌─────────────────────────────────────┐ │
│ │                                     │ │
│ └─────────────────────────────────────┘ │
│                                         │
│        [Cancelar]  [Pausar Timer]       │
│                                         │
└─────────────────────────────────────────┘
```

### Entregables Sprint 3:
- [ ] Timer visual con contador en tiempo real
- [ ] Botones pausar/reanudar/detener funcionales
- [ ] Endpoints REST API para timer
- [ ] JavaScript para actualizaciones en vivo
- [ ] Modal de pausa con motivos
- [ ] Historial del día

---

## 🎯 SPRINT 4: Mis Horas
**Duración estimada:** 2-3 horas
**Archivo:** `templates/portal-empleado/mis-horas.php`

### Tareas:

#### 4.1 Historial de registros
```sql
SELECT rh.*, t.nombre as tarea_nombre, t.numero as tarea_numero
FROM wp_ga_registro_horas rh
LEFT JOIN wp_ga_tareas t ON rh.tarea_id = t.id
WHERE rh.usuario_id = %d
ORDER BY rh.fecha DESC, rh.hora_inicio DESC
```

#### 4.2 Filtros de fecha
- Esta semana
- Este mes
- Mes anterior
- Rango personalizado

#### 4.3 Agrupación
- Por día
- Por semana
- Por proyecto

#### 4.4 Estados de registros
- ACTIVO (timer corriendo)
- BORRADOR (pendiente de enviar)
- ENVIADO (en revisión)
- APROBADO (listo para cobrar)
- RECHAZADO (requiere corrección)
- PAGADO

#### 4.5 Totales y métricas
- Horas totales del período
- Horas por estado
- Monto estimado (horas × tarifa)

### Wireframe Mis Horas:
```
┌─────────────────────────────────────────────────────────────────┐
│ 🕐 Mis Horas                                     [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│ Período: [Este mes ▼]        Ver por: [Día ▼]                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 📊 RESUMEN DEL PERÍODO                                          │
│ ┌────────────┬────────────┬────────────┬────────────┐          │
│ │ Total      │ Aprobadas  │ Pendientes │ Valor Est. │          │
│ │ 78.5 hrs   │ 65.0 hrs   │ 13.5 hrs   │ $195.00    │          │
│ └────────────┴────────────┴────────────┴────────────┘          │
│                                                                 │
│ 📅 SÁBADO 14 DICIEMBRE                           Total: 6.5h   │
│ ├─ TASK-0018 Bug login           2:45  ACTIVO ⏱️               │
│ ├─ TASK-0015 API pagos           2:15  BORRADOR                │
│ └─ TASK-0012 Documentación       1:30  APROBADO ✓              │
│                                                                 │
│ 📅 VIERNES 13 DICIEMBRE                          Total: 8.0h   │
│ ├─ TASK-0015 API pagos           4:00  APROBADO ✓              │
│ ├─ TASK-0010 Tests unitarios     2:30  APROBADO ✓              │
│ └─ TASK-0008 Code review         1:30  APROBADO ✓              │
│                                                                 │
│ 📅 JUEVES 12 DICIEMBRE                           Total: 7.5h   │
│ ├─ TASK-0015 API pagos           5:00  ENVIADO                 │
│ └─ TASK-0010 Tests unitarios     2:30  RECHAZADO ⚠️            │
│                                                                 │
│                         [Ver más...]                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Entregables Sprint 4:
- [ ] Lista de registros de horas
- [ ] Filtros por período
- [ ] Agrupación por día
- [ ] Indicadores de estado con colores
- [ ] Totales del período
- [ ] Cálculo de monto estimado

---

## 🎯 SPRINT 5: Mi Perfil
**Duración estimada:** 2 horas
**Archivo:** `templates/portal-empleado/mi-perfil.php`

### Tareas:

#### 5.1 Mostrar datos del empleado
- Datos de WordPress (nombre, email)
- Datos de wp_ga_usuarios (puesto, departamento, fecha ingreso)
- Tarifa actual (de wp_ga_puestos_escalas)

#### 5.2 Métodos de pago
- Mostrar método preferido
- Mostrar datos configurados (enmascarados)

#### 5.3 Edición básica
- Cambiar contraseña
- Actualizar email (si permitido)

### Wireframe Mi Perfil:
```
┌─────────────────────────────────────────────────────────────────┐
│ 👤 Mi Perfil                                     [← Dashboard]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │  [Avatar]    Juan Pérez García                              │ │
│ │              juan.perez@empresa.com                         │ │
│ │              Miembro desde: Enero 2024                      │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 💼 INFORMACIÓN LABORAL                                          │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Código empleado:  EMP-042                                   │ │
│ │ Departamento:     Desarrollo                                │ │
│ │ Puesto:           Developer Backend                         │ │
│ │ Nivel:            Empleado (4)                              │ │
│ │ Fecha ingreso:    15 de Enero, 2024                         │ │
│ │ Antigüedad:       11 meses                                  │ │
│ │ Tarifa actual:    $5.00 USD/hora                            │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 💳 MÉTODO DE PAGO PREFERIDO                                     │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Método: Binance Pay                                         │ │
│ │ ID: ****4521                                                │ │
│ │ [Contactar RRHH para cambios]                               │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🔐 SEGURIDAD                                                    │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ [Cambiar contraseña]                                        │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Entregables Sprint 5:
- [ ] Mostrar datos del perfil
- [ ] Información laboral completa
- [ ] Método de pago (solo lectura)
- [ ] Opción cambiar contraseña

---

## 📁 Archivos a Crear/Modificar

### Modificar:
```
templates/portal-empleado/
├── dashboard.php      ← SPRINT 1
├── mis-tareas.php     ← SPRINT 2
├── mi-timer.php       ← SPRINT 3
├── mis-horas.php      ← SPRINT 4
└── mi-perfil.php      ← SPRINT 5
```

### Crear:
```
api/
└── class-ga-timer-api.php    ← SPRINT 3 (endpoints REST)

assets/js/
└── portal-timer.js           ← SPRINT 3 (JavaScript timer)

assets/css/
└── portal-empleado.css       ← Estilos consolidados (opcional)

includes/
└── class-ga-empleado-helpers.php  ← Funciones helper (opcional)
```

---

## 🔒 Consideraciones de Seguridad

Cada template DEBE incluir:

```php
<?php
// SEGURIDAD: Verificar que no se accede directamente
if (!defined('ABSPATH')) {
    exit;
}

// SEGURIDAD: Verificar autenticación
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// SEGURIDAD: Verificar que es empleado
$usuario_ga = GA_Usuarios::get_by_wp_id(get_current_user_id());
if (!$usuario_ga) {
    wp_redirect(home_url('/'));
    exit;
}
```

Para AJAX/API:
```php
// SEGURIDAD: Verificar nonce
check_ajax_referer('ga_timer_nonce', 'nonce');

// SEGURIDAD: Verificar capacidad
if (!current_user_can('ga_track_time')) {
    wp_send_json_error('Sin permisos');
}

// SEGURIDAD: Verificar que el registro pertenece al usuario
$registro = GA_Tareas::get_registro($registro_id);
if ($registro->usuario_id !== get_current_user_id()) {
    wp_send_json_error('No autorizado');
}
```

---

## 🚀 Orden de Ejecución

1. **SPRINT 1** → Dashboard (base para todo)
2. **SPRINT 2** → Mis Tareas (necesario para iniciar timer)
3. **SPRINT 3** → Mi Timer (core del sistema)
4. **SPRINT 4** → Mis Horas (historial)
5. **SPRINT 5** → Mi Perfil (complementario)

---

## ✅ Checklist Final

- [ ] Sprint 1: Dashboard funcional
- [ ] Sprint 2: Lista de tareas funcional
- [ ] Sprint 3: Timer completo con AJAX
- [ ] Sprint 4: Historial de horas
- [ ] Sprint 5: Perfil del empleado
- [ ] Pruebas con usuario real
- [ ] Revisión de seguridad
- [ ] Commit y push

---

*Documento generado para GestionAdmin Wolk - Diciembre 2024*
