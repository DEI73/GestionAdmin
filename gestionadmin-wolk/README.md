# GestionAdmin by Wolksoftcr

Sistema integral de gestión empresarial estilo "Uber del trabajo profesional" para WordPress.

## Descripción

GestionAdmin es un plugin completo de WordPress que permite gestionar:

- **Recursos Humanos**: Empleados, freelancers y empresas externas
- **Tareas y Proyectos**: Sistema de tareas con timer integrado y flujos de aprobación
- **Facturación Multi-país**: Soporte para Colombia, México, USA y más
- **Pagos a Prestadores**: Binance, Wise, PayPal, Payoneer
- **Portal de Clientes**: Acceso para que clientes consulten sus proyectos
- **Marketplace**: Órdenes de trabajo para freelancers

## Características Principales

### Sprint 1-2: Fundamentos ✅
- ✅ Estructura base del plugin
- ✅ Sistema de activación/desactivación
- ✅ 6 tablas iniciales:
  - `wp_ga_departamentos`
  - `wp_ga_puestos`
  - `wp_ga_puestos_escalas`
  - `wp_ga_usuarios`
  - `wp_ga_supervisiones`
  - `wp_ga_paises_config`

### Próximos Sprints
- Sprint 3-4: Core Operativo (Tareas, Timer, Registro de horas)
- Sprint 5-6: Clientes (Portal, Casos, Proyectos)
- Sprint 7+: Ver [documentación completa](../GestionAdmin_Vision_Completa.md)

## Requisitos

- WordPress 5.8 o superior
- PHP 7.4 o superior
- MySQL 5.7 o superior

## Instalación

1. Subir la carpeta `gestionadmin-wolk` al directorio `/wp-content/plugins/`
2. Activar el plugin a través del menú 'Plugins' en WordPress
3. Las tablas de base de datos se crearán automáticamente
4. Acceder al menú "GestionAdmin" en el panel de WordPress

## Estructura del Proyecto

```
gestionadmin-wolk/
├── gestionadmin-wolk.php          # Archivo principal
├── includes/
│   ├── class-ga-loader.php        # Cargador principal
│   ├── class-ga-activator.php     # Activación del plugin
│   ├── class-ga-deactivator.php   # Desactivación del plugin
│   └── modules/                   # Módulos funcionales
├── admin/
│   ├── class-ga-admin.php         # Administración
│   └── views/                     # Vistas del admin
├── public/
│   ├── class-ga-public.php        # Área pública
│   └── views/                     # Vistas públicas
├── api/
│   └── class-ga-rest-api.php      # REST API
├── assets/
│   ├── css/                       # Estilos
│   ├── js/                        # JavaScript
│   └── images/                    # Imágenes
└── templates/                     # Plantillas
```

## Roles y Capacidades

El plugin crea 6 roles personalizados:

1. **Socio** (`ga_socio`) - Acceso total al sistema
2. **Director** (`ga_director`) - Gestión de departamentos y proyectos
3. **Jefe** (`ga_jefe`) - Gestión de equipo y aprobación de tareas
4. **Empleado** (`ga_empleado`) - Registro de horas y tareas
5. **Cliente** (`ga_cliente`) - Acceso al portal de clientes
6. **Aplicante** (`ga_aplicante`) - Acceso al marketplace

## Seguridad

El plugin sigue los estándares de seguridad de WordPress:

- ✅ Sanitización de todas las entradas (`sanitize_text_field`, `sanitize_email`, `absint`)
- ✅ Escapado de todas las salidas (`esc_html`, `esc_attr`, `esc_url`)
- ✅ Uso de `$wpdb->prepare()` para todas las consultas SQL
- ✅ Verificación de nonces en formularios y AJAX
- ✅ Verificación de permisos (`current_user_can`)
- ✅ Prefijo `ga_` en todas las funciones, clases y tablas

## Configuración Inicial

### Países Pre-configurados

El plugin incluye configuración para 3 países:

| País | Código | Moneda | Impuesto | Retención |
|------|--------|--------|----------|-----------|
| Estados Unidos | US | USD | 0% | 0% |
| Colombia | CO | COP | IVA 19% | 11% |
| México | MX | MXN | IVA 16% | 10% |

## API REST

Endpoint base: `/wp-json/gestionadmin/v1/`

Endpoints disponibles (se agregarán en sprints futuros):
- `/usuarios`
- `/departamentos`
- `/tareas`
- `/proyectos`
- `/clientes`

## Desarrollo

### Agregar Nuevos Módulos

1. Crear clase en `includes/modules/class-ga-[modulo].php`
2. Seguir el patrón de nomenclatura `GA_[Nombre]`
3. Incluir seguridad (`if (!defined('ABSPATH')) exit;`)
4. Cargar en `class-ga-loader.php`

### Hooks Disponibles

```php
// Acciones
do_action('ga_after_activation');
do_action('ga_before_deactivation');

// Filtros
apply_filters('ga_departamentos_query', $query);
apply_filters('ga_usuario_data', $data);
```

## Changelog

### Versión 1.0.0 (2024-12-12)
- Estructura base del plugin
- Creación de 6 tablas iniciales
- Sistema de roles y capacidades
- Configuración multi-país
- Panel de administración base

## Soporte

Para soporte y documentación completa, consultar:
- [Documentación Completa](../GestionAdmin_Vision_Completa.md)
- [Instrucciones para Claude](../CLAUDE.md)

## Licencia

GPL-2.0+

## Autor

**Wolksoftcr**
https://wolksoftcr.com

Diseñado y desarrollado por Wolksoftcr.com

---

**Versión actual:** 1.0.0
**Última actualización:** Diciembre 2024
