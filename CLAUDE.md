# CLAUDE.md - Instrucciones para Claude Code

## Proyecto: GestionAdmin by Wolk

### ¿Qué es este proyecto?
Sistema integral de gestión empresarial estilo "Uber del trabajo profesional". Plugin WordPress para gestionar:
- Empleados, freelancers y empresas externas
- Tareas con timer y flujos de aprobación
- Facturación multi-país (Colombia, México, USA, etc.)
- Pagos a prestadores (Binance, Wise, PayPal)
- Portal de clientes
- Marketplace de órdenes de trabajo

### Documentación Principal
- `docs/GestionAdmin_Vision_Completa.md` - Visión completa del proyecto (4000+ líneas)

---

## Estándares de Código WordPress

### Seguridad OBLIGATORIA
Todo código DEBE seguir estas reglas:

```php
// 1. ENTRADA: Sanitizar SIEMPRE
$nombre = sanitize_text_field($_POST['nombre']);
$email = sanitize_email($_POST['email']);
$id = absint($_GET['id']);

// 2. SALIDA: Escapar SIEMPRE
echo esc_html($variable);
echo esc_attr($atributo);
echo esc_url($url);

// 3. SQL: SIEMPRE $wpdb->prepare()
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}ga_usuarios WHERE id = %d", $id);
// NUNCA: "SELECT * FROM tabla WHERE id = $id"

// 4. FORMS: Nonces + Permisos
wp_nonce_field('ga_action', 'ga_nonce');
if (!wp_verify_nonce($_POST['ga_nonce'], 'ga_action')) die('Security check failed');
if (!current_user_can('manage_options')) die('Unauthorized');

// 5. AJAX: check_ajax_referer
check_ajax_referer('ga_ajax_nonce', 'nonce');

// 6. Prefijo: ga_ para todo
function ga_mi_funcion() {}
class GA_Mi_Clase {}

// 7. ABSPATH al inicio de cada archivo PHP
if (!defined('ABSPATH')) exit;
```

### Estructura de Archivos
```
gestionadmin-wolk/
├── gestionadmin-wolk.php          # Archivo principal
├── includes/
│   ├── class-ga-loader.php
│   ├── class-ga-activator.php
│   ├── class-ga-deactivator.php
│   └── modules/
│       ├── class-ga-departamentos.php
│       ├── class-ga-usuarios.php
│       ├── class-ga-tareas.php
│       └── ...
├── admin/
│   ├── class-ga-admin.php
│   └── views/
├── public/
│   ├── class-ga-public.php
│   └── views/
├── api/
│   └── class-ga-rest-api.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── templates/
```

### Base de Datos
- Prefijo de tablas: `wp_ga_`
- 40+ tablas definidas en `docs/GestionAdmin_Vision_Completa.md`
- Usar dbDelta() para crear/actualizar tablas

---

## Comandos Útiles

```bash
# Ver estructura actual
tree -L 2

# Buscar en archivos
grep -r "texto" --include="*.php"

# Crear archivo con seguridad
# Siempre incluir: if (!defined('ABSPATH')) exit;
```

---

## Módulos por Prioridad

### Sprint 1-2: Fundamentos
1. ☐ Estructura base del plugin
2. ☐ Activación/desactivación con tablas
3. ☐ wp_ga_departamentos
4. ☐ wp_ga_puestos
5. ☐ wp_ga_puestos_escalas
6. ☐ wp_ga_usuarios
7. ☐ wp_ga_supervisiones

### Sprint 3-4: Core Operativo
1. ☐ wp_ga_catalogo_tareas
2. ☐ wp_ga_tareas
3. ☐ wp_ga_subtareas
4. ☐ wp_ga_registro_horas
5. ☐ wp_ga_pausas_timer
6. ☐ Timer JavaScript

### Sprint 5-6: Clientes
1. ☐ wp_ga_clientes
2. ☐ wp_ga_casos
3. ☐ wp_ga_proyectos
4. ☐ Portal cliente (frontend)

### Sprint 7+: Ver documento completo

---

## Notas Importantes

- **Moneda principal:** USD
- **Países iniciales:** CO, US, MX
- **Roles WordPress:** Usar roles personalizados (ga_socio, ga_director, ga_jefe, ga_empleado, ga_cliente, ga_aplicante)
- **API REST:** Prefijo `/wp-json/gestionadmin/v1/`
- **Integraciones futuras:** Wolk POS, Time Doctor, Stripe, PayPal
