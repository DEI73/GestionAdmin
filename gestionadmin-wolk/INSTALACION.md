# Guía de Instalación - GestionAdmin by Wolk

## Instalación en WordPress

### Opción 1: Instalación Manual

1. **Copiar el plugin a WordPress:**
   ```bash
   cp -r gestionadmin-wolk /ruta/a/wordpress/wp-content/plugins/
   ```

2. **Acceder al panel de WordPress:**
   - Ir a `Plugins > Plugins instalados`
   - Buscar "GestionAdmin by Wolk"
   - Hacer clic en "Activar"

3. **Verificar instalación:**
   - Se creará un nuevo menú "GestionAdmin" en el panel lateral
   - Se habrán creado 6 tablas en la base de datos
   - Se habrán creado 6 roles personalizados

### Opción 2: Instalación vía ZIP

1. **Comprimir el plugin:**
   ```bash
   zip -r gestionadmin-wolk.zip gestionadmin-wolk/
   ```

2. **Subir a WordPress:**
   - Ir a `Plugins > Añadir nuevo > Subir plugin`
   - Seleccionar el archivo ZIP
   - Hacer clic en "Instalar ahora"
   - Activar el plugin

## Verificación Post-Instalación

### 1. Verificar Tablas de Base de Datos

Ejecutar en phpMyAdmin o línea de comandos MySQL:

```sql
SHOW TABLES LIKE 'wp_ga_%';
```

Deberías ver 6 tablas:
- `wp_ga_departamentos`
- `wp_ga_puestos`
- `wp_ga_puestos_escalas`
- `wp_ga_usuarios`
- `wp_ga_supervisiones`
- `wp_ga_paises_config`

### 2. Verificar Países Configurados

```sql
SELECT * FROM wp_ga_paises_config;
```

Deberías ver 3 registros:
- Estados Unidos (US)
- Colombia (CO)
- México (MX)

### 3. Verificar Roles Creados

En WordPress: `Usuarios > Todos los usuarios > Añadir nuevo`

En el dropdown de "Rol" deberías ver:
- Socio
- Director
- Jefe de Equipo
- Empleado
- Cliente
- Aplicante

### 4. Acceder al Panel de GestionAdmin

1. En el menú lateral de WordPress, hacer clic en "GestionAdmin"
2. Deberías ver la página principal con información del plugin

## Estructura de Tablas Creadas

### wp_ga_departamentos
Almacena los departamentos de la empresa (Desarrollo, Administración, Soporte, etc.)

**Campos principales:**
- `codigo`: DEV, ADMIN, SOPORTE
- `nombre`: Nombre completo del departamento
- `tipo`: OPERACION_FIJA, PROYECTOS, SOPORTE, COMERCIAL
- `jefe_id`: FK al usuario jefe del departamento

### wp_ga_puestos
Define los puestos de trabajo dentro de cada departamento

**Campos principales:**
- `departamento_id`: FK a departamentos
- `codigo`: DEV-BACK, QA-SR, etc.
- `nivel_jerarquico`: 1=Socio, 2=Director, 3=Jefe, 4=Empleado
- `capacidad_horas_semana`: Horas esperadas por semana
- `requiere_qa`: Si las tareas requieren revisión de QA

### wp_ga_puestos_escalas
Escalas salariales por antigüedad para cada puesto

**Campos principales:**
- `puesto_id`: FK a puestos
- `anio_antiguedad`: 1, 2, 3, 4, 5+
- `tarifa_hora`: Tarifa en USD por hora
- `incremento_porcentaje`: % de incremento vs año anterior
- `requiere_aprobacion_jefe/director`: Aprobaciones necesarias

### wp_ga_usuarios
Extensión de wp_users con datos específicos del sistema

**Campos principales:**
- `usuario_wp_id`: FK a wp_users (UNIQUE)
- `puesto_id`: Puesto asignado
- `departamento_id`: Departamento
- `codigo_empleado`: Código interno único
- `metodo_pago_preferido`: BINANCE, WISE, PAYPAL, etc.
- `datos_pago_*`: JSON con información de pago
- `pais_residencia`: Código ISO del país

### wp_ga_supervisiones
Relaciones de supervisión entre usuarios

**Campos principales:**
- `supervisor_id`: Usuario que supervisa
- `supervisado_id`: Usuario supervisado
- `tipo_supervision`: DIRECTA, PROYECTO, DEPARTAMENTO
- `proyecto_id/departamento_id`: Contexto de la supervisión
- `fecha_inicio/fecha_fin`: Vigencia

### wp_ga_paises_config
Configuración por país (impuestos, moneda, facturación)

**Campos principales:**
- `codigo_iso`: US, CO, MX
- `moneda_codigo`: USD, COP, MXN
- `impuesto_nombre/porcentaje`: IVA, Sales Tax, etc.
- `retencion_default`: % de retención
- `requiere_electronica`: Si requiere factura electrónica
- `proveedor_electronica`: DIAN, SAT, etc.

## Datos Iniciales Insertados

### Países Pre-configurados

| Código | País | Moneda | Impuesto | Retención | Factura Electrónica |
|--------|------|--------|----------|-----------|---------------------|
| US | Estados Unidos | USD | 0% | 0% | No |
| CO | Colombia | COP | IVA 19% | 11% | Sí (DIAN) |
| MX | México | MXN | IVA 16% | 10% | Sí (SAT) |

## Próximos Pasos

1. **Crear Departamentos:**
   - Ir a GestionAdmin > Departamentos (próximo sprint)
   - Agregar: Desarrollo, Administración, Soporte, etc.

2. **Crear Puestos:**
   - Definir puestos para cada departamento
   - Configurar escalas salariales

3. **Importar/Crear Usuarios:**
   - Asignar roles a usuarios existentes de WordPress
   - Crear nuevos usuarios con rol de GestionAdmin

4. **Configurar Supervisiones:**
   - Definir quién supervisa a quién
   - Configurar supervisiones por proyecto/departamento

## Troubleshooting

### Error: "Las tablas no se crearon"

1. Verificar permisos de MySQL del usuario
2. Desactivar y volver a activar el plugin
3. Verificar logs de error de WordPress en `wp-content/debug.log`

### Error: "No aparece el menú GestionAdmin"

1. Verificar que el usuario tiene rol de "Administrator"
2. Limpiar caché del navegador
3. Verificar que el plugin está activado

### Error: "Los roles no se crearon"

1. Ir a `Usuarios > Capacidades` (si tienes algún plugin de roles)
2. Desactivar y reactivar el plugin
3. Los roles deberían aparecer

## Soporte Técnico

Para más información, consultar:
- [README.md](README.md)
- [Documentación Completa](../GestionAdmin_Vision_Completa.md)
- [Instrucciones para Claude](../CLAUDE.md)

---

**Versión:** 1.0.0
**Fecha:** Diciembre 2024
