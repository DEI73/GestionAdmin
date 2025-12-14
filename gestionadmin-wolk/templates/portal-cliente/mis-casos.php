<?php
/**
 * Template: Portal Cliente - Mis Casos
 *
 * Lista de casos del cliente con:
 * - Filtros por estado (ABIERTO, EN_PROGRESO, EN_ESPERA, CERRADO)
 * - Busqueda por codigo o nombre
 * - Detalle expandible con proyectos y avance
 * - Estados con colores
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Templates/PortalCliente
 * @since      1.3.0
 * @updated    1.11.0 - Mis Casos funcional completo (Sprint C2)
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar autenticacion
if (!is_user_logged_in()) {
    wp_redirect(home_url('/acceso/'));
    exit;
}

// Obtener usuario actual
$wp_user_id = get_current_user_id();
$wp_user = wp_get_current_user();

// Cargar modulo de clientes
require_once GA_PLUGIN_DIR . 'includes/modules/class-ga-clientes.php';

// Verificar que el usuario es un cliente registrado
$cliente = GA_Clientes::get_by_wp_id($wp_user_id);

// Si no es cliente, mostrar mensaje de acceso denegado
if (!$cliente) {
    get_header();
    GA_Theme_Integration::print_portal_styles();
    ?>
    <div class="ga-public-container ga-portal-cliente">
        <div class="ga-container">
            <div class="ga-access-denied">
                <div class="ga-access-denied-icon">
                    <span class="dashicons dashicons-lock"></span>
                </div>
                <h2><?php esc_html_e('Acceso Restringido', 'gestionadmin-wolk'); ?></h2>
                <p><?php esc_html_e('No tienes acceso al portal de clientes.', 'gestionadmin-wolk'); ?></p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="ga-btn ga-btn-primary">
                    <?php esc_html_e('Volver al Inicio', 'gestionadmin-wolk'); ?>
                </a>
            </div>
        </div>
    </div>
    <style>
    .ga-portal-cliente {
        min-height: 80vh;
        padding: 40px 20px;
        background: #f5f7fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ga-access-denied {
        background: #fff;
        border-radius: 12px;
        padding: 60px 40px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        max-width: 500px;
    }
    .ga-access-denied-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
    }
    .ga-access-denied-icon .dashicons {
        font-size: 40px;
        width: 40px;
        height: 40px;
        color: #fff;
    }
    .ga-access-denied h2 {
        font-size: 24px;
        margin: 0 0 15px 0;
        color: #1a1a2e;
    }
    .ga-access-denied p {
        color: #666;
        margin-bottom: 25px;
    }
    .ga-btn {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
    }
    .ga-btn-primary {
        background: #28a745;
        color: #fff;
    }
    .ga-btn-primary:hover {
        background: #218838;
        color: #fff;
    }
    </style>
    <?php
    get_footer();
    exit;
}

global $wpdb;

// =========================================================================
// PROCESAR FILTROS Y BUSQUEDA
// =========================================================================

$filtro_estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
$busqueda = isset($_GET['buscar']) ? sanitize_text_field($_GET['buscar']) : '';

// Estados validos
$estados_validos = array('ABIERTO', 'EN_PROGRESO', 'EN_ESPERA', 'CERRADO', 'CANCELADO');

// Validar estado
if (!empty($filtro_estado) && !in_array($filtro_estado, $estados_validos, true)) {
    $filtro_estado = '';
}

// =========================================================================
// OBTENER CASOS DEL CLIENTE
// =========================================================================
$table_casos = $wpdb->prefix . 'ga_casos';
$table_proyectos = $wpdb->prefix . 'ga_proyectos';

// Construir query base
$sql = "SELECT c.*,
               (SELECT COUNT(*) FROM {$table_proyectos} p WHERE p.caso_id = c.id) as total_proyectos,
               (SELECT COUNT(*) FROM {$table_proyectos} p WHERE p.caso_id = c.id AND p.estado = 'EN_PROGRESO') as proyectos_activos
        FROM {$table_casos} c
        WHERE c.cliente_id = %d";

$params = array($cliente->id);

// Aplicar filtro de estado
if (!empty($filtro_estado)) {
    $sql .= " AND c.estado = %s";
    $params[] = $filtro_estado;
}

// Aplicar busqueda
if (!empty($busqueda)) {
    $sql .= " AND (c.numero LIKE %s OR c.titulo LIKE %s)";
    $like = '%' . $wpdb->esc_like($busqueda) . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY c.fecha_apertura DESC";

$casos = $wpdb->get_results($wpdb->prepare($sql, $params));

// =========================================================================
// OBTENER PROYECTOS PARA CADA CASO
// =========================================================================
$proyectos_por_caso = array();

if (!empty($casos)) {
    $caso_ids = array_map(function($c) { return $c->id; }, $casos);
    $caso_ids_str = implode(',', array_map('intval', $caso_ids));

    $proyectos = $wpdb->get_results(
        "SELECT id, caso_id, codigo, nombre, estado, porcentaje_avance, fecha_fin_estimada
         FROM {$table_proyectos}
         WHERE caso_id IN ({$caso_ids_str})
         ORDER BY estado ASC, nombre ASC"
    );

    foreach ($proyectos as $proyecto) {
        if (!isset($proyectos_por_caso[$proyecto->caso_id])) {
            $proyectos_por_caso[$proyecto->caso_id] = array();
        }
        $proyectos_por_caso[$proyecto->caso_id][] = $proyecto;
    }
}

// =========================================================================
// CONTAR CASOS POR ESTADO (para badges en filtros)
// =========================================================================
$conteo_estados = $wpdb->get_results($wpdb->prepare(
    "SELECT estado, COUNT(*) as total
     FROM {$table_casos}
     WHERE cliente_id = %d
     GROUP BY estado",
    $cliente->id
), OBJECT_K);

$total_casos = 0;
foreach ($conteo_estados as $e) {
    $total_casos += (int) $e->total;
}

// URLs de navegacion
$url_dashboard = home_url('/cliente/');
$url_casos = home_url('/cliente/mis-casos/');
$url_facturas = home_url('/cliente/mis-facturas/');
$url_perfil = home_url('/cliente/mi-perfil/');

// Usar header del tema
get_header();
GA_Theme_Integration::print_portal_styles();
?>

<div class="ga-public-container ga-portal-cliente">
    <div class="ga-container">

        <!-- Header con navegacion -->
        <div class="ga-portal-nav">
            <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-dashboard"></span>
                <?php esc_html_e('Dashboard', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($url_casos); ?>" class="ga-nav-item active">
                <span class="dashicons dashicons-portfolio"></span>
                <?php esc_html_e('Mis Casos', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($url_facturas); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-media-text"></span>
                <?php esc_html_e('Mis Facturas', 'gestionadmin-wolk'); ?>
            </a>
            <a href="<?php echo esc_url($url_perfil); ?>" class="ga-nav-item">
                <span class="dashicons dashicons-id"></span>
                <?php esc_html_e('Mi Perfil', 'gestionadmin-wolk'); ?>
            </a>
        </div>

        <!-- Titulo de pagina -->
        <div class="ga-page-header">
            <div class="ga-page-title">
                <h1>
                    <span class="dashicons dashicons-portfolio"></span>
                    <?php esc_html_e('Mis Casos', 'gestionadmin-wolk'); ?>
                </h1>
                <p><?php esc_html_e('Seguimiento de tus casos y proyectos', 'gestionadmin-wolk'); ?></p>
            </div>
            <div class="ga-page-stats">
                <span class="ga-total-badge">
                    <?php echo esc_html($total_casos); ?>
                    <?php echo esc_html(_n('caso', 'casos', $total_casos, 'gestionadmin-wolk')); ?>
                </span>
            </div>
        </div>

        <!-- Filtros y busqueda -->
        <div class="ga-filters-bar">
            <form method="get" action="<?php echo esc_url($url_casos); ?>" class="ga-filters-form">
                <!-- Filtro por estado -->
                <div class="ga-filter-tabs">
                    <a href="<?php echo esc_url($url_casos); ?>"
                       class="ga-filter-tab <?php echo empty($filtro_estado) ? 'active' : ''; ?>">
                        <?php esc_html_e('Todos', 'gestionadmin-wolk'); ?>
                        <span class="ga-tab-count"><?php echo esc_html($total_casos); ?></span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('estado', 'ABIERTO', $url_casos)); ?>"
                       class="ga-filter-tab <?php echo $filtro_estado === 'ABIERTO' ? 'active' : ''; ?>">
                        <?php esc_html_e('Abiertos', 'gestionadmin-wolk'); ?>
                        <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['ABIERTO']) ? $conteo_estados['ABIERTO']->total : 0); ?></span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('estado', 'EN_PROGRESO', $url_casos)); ?>"
                       class="ga-filter-tab <?php echo $filtro_estado === 'EN_PROGRESO' ? 'active' : ''; ?>">
                        <?php esc_html_e('En Progreso', 'gestionadmin-wolk'); ?>
                        <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['EN_PROGRESO']) ? $conteo_estados['EN_PROGRESO']->total : 0); ?></span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('estado', 'EN_ESPERA', $url_casos)); ?>"
                       class="ga-filter-tab <?php echo $filtro_estado === 'EN_ESPERA' ? 'active' : ''; ?>">
                        <?php esc_html_e('En Espera', 'gestionadmin-wolk'); ?>
                        <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['EN_ESPERA']) ? $conteo_estados['EN_ESPERA']->total : 0); ?></span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('estado', 'CERRADO', $url_casos)); ?>"
                       class="ga-filter-tab <?php echo $filtro_estado === 'CERRADO' ? 'active' : ''; ?>">
                        <?php esc_html_e('Cerrados', 'gestionadmin-wolk'); ?>
                        <span class="ga-tab-count"><?php echo esc_html(isset($conteo_estados['CERRADO']) ? $conteo_estados['CERRADO']->total : 0); ?></span>
                    </a>
                </div>

                <!-- Busqueda -->
                <div class="ga-search-box">
                    <span class="dashicons dashicons-search"></span>
                    <input type="text"
                           name="buscar"
                           placeholder="<?php esc_attr_e('Buscar por codigo o nombre...', 'gestionadmin-wolk'); ?>"
                           value="<?php echo esc_attr($busqueda); ?>">
                    <?php if (!empty($filtro_estado)): ?>
                        <input type="hidden" name="estado" value="<?php echo esc_attr($filtro_estado); ?>">
                    <?php endif; ?>
                    <button type="submit" class="ga-search-btn">
                        <?php esc_html_e('Buscar', 'gestionadmin-wolk'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Indicador de filtro activo -->
        <?php if (!empty($busqueda) || !empty($filtro_estado)): ?>
        <div class="ga-active-filters">
            <span class="ga-filter-label"><?php esc_html_e('Filtros activos:', 'gestionadmin-wolk'); ?></span>
            <?php if (!empty($filtro_estado)): ?>
                <span class="ga-filter-tag">
                    <?php echo esc_html($filtro_estado); ?>
                    <a href="<?php echo esc_url(remove_query_arg('estado')); ?>" class="ga-remove-filter">&times;</a>
                </span>
            <?php endif; ?>
            <?php if (!empty($busqueda)): ?>
                <span class="ga-filter-tag">
                    "<?php echo esc_html($busqueda); ?>"
                    <a href="<?php echo esc_url(remove_query_arg('buscar')); ?>" class="ga-remove-filter">&times;</a>
                </span>
            <?php endif; ?>
            <a href="<?php echo esc_url($url_casos); ?>" class="ga-clear-all">
                <?php esc_html_e('Limpiar filtros', 'gestionadmin-wolk'); ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- Lista de casos -->
        <div class="ga-casos-list">
            <?php if (empty($casos)): ?>
                <div class="ga-empty-state">
                    <div class="ga-empty-icon">
                        <span class="dashicons dashicons-portfolio"></span>
                    </div>
                    <?php if (!empty($busqueda) || !empty($filtro_estado)): ?>
                        <h3><?php esc_html_e('No se encontraron casos', 'gestionadmin-wolk'); ?></h3>
                        <p><?php esc_html_e('No hay casos que coincidan con tu busqueda o filtro.', 'gestionadmin-wolk'); ?></p>
                        <a href="<?php echo esc_url($url_casos); ?>" class="ga-btn ga-btn-secondary">
                            <?php esc_html_e('Ver todos los casos', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php else: ?>
                        <h3><?php esc_html_e('No tienes casos registrados', 'gestionadmin-wolk'); ?></h3>
                        <p><?php esc_html_e('Cuando tengas casos asignados, apareceran aqui con su informacion y proyectos.', 'gestionadmin-wolk'); ?></p>
                        <a href="<?php echo esc_url($url_dashboard); ?>" class="ga-btn ga-btn-secondary">
                            <?php esc_html_e('Volver al Dashboard', 'gestionadmin-wolk'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($casos as $caso):
                    // Determinar clase y etiqueta del estado
                    $estado_class = '';
                    $estado_label = '';
                    switch ($caso->estado) {
                        case 'ABIERTO':
                            $estado_class = 'ga-estado-abierto';
                            $estado_label = __('Abierto', 'gestionadmin-wolk');
                            break;
                        case 'EN_PROGRESO':
                            $estado_class = 'ga-estado-progreso';
                            $estado_label = __('En Progreso', 'gestionadmin-wolk');
                            break;
                        case 'EN_ESPERA':
                            $estado_class = 'ga-estado-espera';
                            $estado_label = __('En Espera', 'gestionadmin-wolk');
                            break;
                        case 'CERRADO':
                            $estado_class = 'ga-estado-cerrado';
                            $estado_label = __('Cerrado', 'gestionadmin-wolk');
                            break;
                        case 'CANCELADO':
                            $estado_class = 'ga-estado-cancelado';
                            $estado_label = __('Cancelado', 'gestionadmin-wolk');
                            break;
                        default:
                            $estado_class = 'ga-estado-default';
                            $estado_label = $caso->estado;
                    }

                    // Obtener proyectos del caso
                    $proyectos_caso = isset($proyectos_por_caso[$caso->id]) ? $proyectos_por_caso[$caso->id] : array();
                    $tiene_proyectos = !empty($proyectos_caso);
                ?>
                <details class="ga-caso-card">
                    <summary class="ga-caso-header">
                        <div class="ga-caso-main">
                            <div class="ga-caso-estado-indicator <?php echo esc_attr($estado_class); ?>"></div>
                            <div class="ga-caso-info">
                                <div class="ga-caso-titulo-row">
                                    <span class="ga-caso-numero"><?php echo esc_html($caso->numero); ?></span>
                                    <span class="ga-caso-badge <?php echo esc_attr($estado_class); ?>">
                                        <?php echo esc_html($estado_label); ?>
                                    </span>
                                </div>
                                <h3 class="ga-caso-titulo"><?php echo esc_html($caso->titulo); ?></h3>
                                <div class="ga-caso-meta">
                                    <span class="ga-meta-item">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <?php
                                        printf(
                                            esc_html__('Abierto: %s', 'gestionadmin-wolk'),
                                            date_i18n('d M Y', strtotime($caso->fecha_apertura))
                                        );
                                        ?>
                                    </span>
                                    <span class="ga-meta-item">
                                        <span class="dashicons dashicons-chart-line"></span>
                                        <?php
                                        printf(
                                            esc_html(_n('%d proyecto', '%d proyectos', $caso->total_proyectos, 'gestionadmin-wolk')),
                                            $caso->total_proyectos
                                        );
                                        ?>
                                    </span>
                                    <?php if ($caso->proyectos_activos > 0): ?>
                                    <span class="ga-meta-item ga-meta-active">
                                        <span class="dashicons dashicons-controls-play"></span>
                                        <?php
                                        printf(
                                            esc_html(_n('%d activo', '%d activos', $caso->proyectos_activos, 'gestionadmin-wolk')),
                                            $caso->proyectos_activos
                                        );
                                        ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="ga-caso-toggle">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                    </summary>

                    <div class="ga-caso-content">
                        <?php if (!empty($caso->descripcion)): ?>
                        <div class="ga-caso-descripcion">
                            <h4><?php esc_html_e('Descripcion', 'gestionadmin-wolk'); ?></h4>
                            <p><?php echo esc_html($caso->descripcion); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($caso->tipo)): ?>
                        <div class="ga-caso-tipo">
                            <span class="ga-tipo-label"><?php esc_html_e('Tipo:', 'gestionadmin-wolk'); ?></span>
                            <span class="ga-tipo-value"><?php echo esc_html($caso->tipo); ?></span>
                        </div>
                        <?php endif; ?>

                        <!-- Lista de proyectos -->
                        <div class="ga-proyectos-section">
                            <h4>
                                <span class="dashicons dashicons-chart-line"></span>
                                <?php esc_html_e('Proyectos del Caso', 'gestionadmin-wolk'); ?>
                            </h4>

                            <?php if (!$tiene_proyectos): ?>
                                <div class="ga-no-proyectos">
                                    <span class="dashicons dashicons-info-outline"></span>
                                    <?php esc_html_e('Este caso aun no tiene proyectos asignados.', 'gestionadmin-wolk'); ?>
                                </div>
                            <?php else: ?>
                                <div class="ga-proyectos-grid">
                                    <?php foreach ($proyectos_caso as $proyecto):
                                        $avance = (int) $proyecto->porcentaje_avance;

                                        // Estado del proyecto
                                        $proy_estado_class = '';
                                        $proy_estado_label = '';
                                        switch ($proyecto->estado) {
                                            case 'PLANIFICACION':
                                                $proy_estado_class = 'ga-proy-planificacion';
                                                $proy_estado_label = __('Planificacion', 'gestionadmin-wolk');
                                                break;
                                            case 'EN_PROGRESO':
                                                $proy_estado_class = 'ga-proy-progreso';
                                                $proy_estado_label = __('En Progreso', 'gestionadmin-wolk');
                                                break;
                                            case 'PAUSADO':
                                                $proy_estado_class = 'ga-proy-pausado';
                                                $proy_estado_label = __('Pausado', 'gestionadmin-wolk');
                                                break;
                                            case 'COMPLETADO':
                                                $proy_estado_class = 'ga-proy-completado';
                                                $proy_estado_label = __('Completado', 'gestionadmin-wolk');
                                                break;
                                            case 'CANCELADO':
                                                $proy_estado_class = 'ga-proy-cancelado';
                                                $proy_estado_label = __('Cancelado', 'gestionadmin-wolk');
                                                break;
                                            default:
                                                $proy_estado_class = 'ga-proy-default';
                                                $proy_estado_label = $proyecto->estado;
                                        }
                                    ?>
                                    <div class="ga-proyecto-card <?php echo esc_attr($proy_estado_class); ?>">
                                        <div class="ga-proyecto-header">
                                            <span class="ga-proyecto-codigo"><?php echo esc_html($proyecto->codigo); ?></span>
                                            <span class="ga-proyecto-estado <?php echo esc_attr($proy_estado_class); ?>">
                                                <?php echo esc_html($proy_estado_label); ?>
                                            </span>
                                        </div>
                                        <div class="ga-proyecto-nombre">
                                            <?php echo esc_html($proyecto->nombre); ?>
                                        </div>
                                        <?php if (!empty($proyecto->fecha_fin_estimada)): ?>
                                        <div class="ga-proyecto-fecha">
                                            <span class="dashicons dashicons-calendar"></span>
                                            <?php
                                            printf(
                                                esc_html__('Fin estimado: %s', 'gestionadmin-wolk'),
                                                date_i18n('d M Y', strtotime($proyecto->fecha_fin_estimada))
                                            );
                                            ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="ga-proyecto-progress">
                                            <div class="ga-progress-bar">
                                                <div class="ga-progress-fill <?php echo $avance >= 100 ? 'ga-progress-complete' : ''; ?>"
                                                     style="width: <?php echo esc_attr(min($avance, 100)); ?>%;">
                                                </div>
                                            </div>
                                            <span class="ga-progress-text"><?php echo esc_html($avance); ?>%</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($caso->fecha_cierre_real): ?>
                        <div class="ga-caso-cierre">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php
                            printf(
                                esc_html__('Caso cerrado el %s', 'gestionadmin-wolk'),
                                date_i18n('d M Y', strtotime($caso->fecha_cierre_real))
                            );
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </details>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="ga-portal-footer">
            <p>
                <?php esc_html_e('Desarrollado por', 'gestionadmin-wolk'); ?>
                <a href="https://wolksoftcr.com" target="_blank">Wolksoftcr.com</a>
            </p>
        </div>

    </div>
</div>

<style>
/* =========================================================================
   PORTAL CLIENTE - MIS CASOS STYLES
   ========================================================================= */

.ga-portal-cliente {
    min-height: 100vh;
    padding: 30px 20px;
    background: #f5f7fa;
}

.ga-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* =========================================================================
   NAVEGACION (heredado de dashboard)
   ========================================================================= */

.ga-portal-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 25px;
    background: #fff;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    flex-wrap: wrap;
}

.ga-nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
}

.ga-nav-item:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.ga-nav-item.active {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
}

.ga-nav-item .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* =========================================================================
   HEADER DE PAGINA
   ========================================================================= */

.ga-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 24px 28px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ga-page-title h1 {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 6px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
}

.ga-page-title h1 .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    color: #28a745;
}

.ga-page-title p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.ga-total-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

/* =========================================================================
   FILTROS Y BUSQUEDA
   ========================================================================= */

.ga-filters-bar {
    background: #fff;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ga-filters-form {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.ga-filter-tabs {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.ga-filter-tab {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    color: #64748b;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
    background: #f8fafc;
}

.ga-filter-tab:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.ga-filter-tab.active {
    background: #28a745;
    color: #fff;
}

.ga-tab-count {
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.ga-filter-tab.active .ga-tab-count {
    background: rgba(255,255,255,0.2);
}

.ga-search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border-radius: 8px;
    padding: 4px 4px 4px 14px;
    border: 1px solid #e2e8f0;
    min-width: 280px;
}

.ga-search-box .dashicons {
    color: #94a3b8;
}

.ga-search-box input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 0;
    font-size: 14px;
    outline: none;
}

.ga-search-btn {
    padding: 8px 16px;
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.ga-search-btn:hover {
    background: #218838;
}

/* =========================================================================
   FILTROS ACTIVOS
   ========================================================================= */

.ga-active-filters {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 12px 16px;
    background: #fef3c7;
    border-radius: 8px;
    font-size: 13px;
}

.ga-filter-label {
    color: #92400e;
    font-weight: 500;
}

.ga-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    padding: 4px 10px;
    border-radius: 4px;
    color: #1e293b;
}

.ga-remove-filter {
    color: #dc2626;
    text-decoration: none;
    font-weight: bold;
    font-size: 16px;
    line-height: 1;
}

.ga-clear-all {
    margin-left: auto;
    color: #92400e;
    text-decoration: none;
    font-weight: 500;
}

.ga-clear-all:hover {
    text-decoration: underline;
}

/* =========================================================================
   LISTA DE CASOS
   ========================================================================= */

.ga-casos-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* =========================================================================
   TARJETA DE CASO (details/summary)
   ========================================================================= */

.ga-caso-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
    transition: box-shadow 0.2s;
}

.ga-caso-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.ga-caso-card[open] {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.ga-caso-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    cursor: pointer;
    list-style: none;
}

.ga-caso-header::-webkit-details-marker {
    display: none;
}

.ga-caso-main {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    flex: 1;
}

.ga-caso-estado-indicator {
    width: 4px;
    height: 60px;
    border-radius: 2px;
    flex-shrink: 0;
}

.ga-caso-info {
    flex: 1;
}

.ga-caso-titulo-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
}

.ga-caso-numero {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    font-family: monospace;
}

.ga-caso-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.ga-caso-titulo {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}

.ga-caso-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.ga-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #64748b;
}

.ga-meta-item .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.ga-meta-active {
    color: #28a745;
    font-weight: 500;
}

.ga-caso-toggle {
    color: #94a3b8;
    transition: transform 0.2s;
}

.ga-caso-card[open] .ga-caso-toggle {
    transform: rotate(180deg);
}

/* =========================================================================
   ESTADOS DE CASO (colores)
   ========================================================================= */

.ga-estado-abierto {
    background: #dbeafe;
    color: #1d4ed8;
}

.ga-estado-abierto.ga-caso-estado-indicator,
.ga-estado-abierto.ga-caso-badge {
    background: #3b82f6;
}

.ga-estado-abierto.ga-caso-badge {
    color: #fff;
}

.ga-estado-progreso {
    background: #dcfce7;
    color: #15803d;
}

.ga-estado-progreso.ga-caso-estado-indicator,
.ga-estado-progreso.ga-caso-badge {
    background: #22c55e;
}

.ga-estado-progreso.ga-caso-badge {
    color: #fff;
}

.ga-estado-espera {
    background: #fef3c7;
    color: #b45309;
}

.ga-estado-espera.ga-caso-estado-indicator,
.ga-estado-espera.ga-caso-badge {
    background: #f59e0b;
}

.ga-estado-espera.ga-caso-badge {
    color: #fff;
}

.ga-estado-cerrado {
    background: #f1f5f9;
    color: #475569;
}

.ga-estado-cerrado.ga-caso-estado-indicator,
.ga-estado-cerrado.ga-caso-badge {
    background: #64748b;
}

.ga-estado-cerrado.ga-caso-badge {
    color: #fff;
}

.ga-estado-cancelado {
    background: #fef2f2;
    color: #dc2626;
}

.ga-estado-cancelado.ga-caso-estado-indicator,
.ga-estado-cancelado.ga-caso-badge {
    background: #dc2626;
}

.ga-estado-cancelado.ga-caso-badge {
    color: #fff;
}

/* =========================================================================
   CONTENIDO EXPANDIDO DEL CASO
   ========================================================================= */

.ga-caso-content {
    padding: 0 24px 24px 24px;
    border-top: 1px solid #e5e7eb;
    margin-top: 0;
    padding-top: 20px;
}

.ga-caso-descripcion {
    margin-bottom: 20px;
}

.ga-caso-descripcion h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.ga-caso-descripcion p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

.ga-caso-tipo {
    display: inline-flex;
    gap: 8px;
    padding: 8px 14px;
    background: #f8fafc;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 20px;
}

.ga-tipo-label {
    color: #64748b;
}

.ga-tipo-value {
    color: #1e293b;
    font-weight: 500;
}

/* =========================================================================
   SECCION DE PROYECTOS
   ========================================================================= */

.ga-proyectos-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px dashed #e5e7eb;
}

.ga-proyectos-section h4 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 16px 0;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.ga-proyectos-section h4 .dashicons {
    color: #28a745;
}

.ga-no-proyectos {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
    color: #64748b;
    font-size: 14px;
}

.ga-proyectos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.ga-proyecto-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 16px;
    border-left: 3px solid #e2e8f0;
}

.ga-proyecto-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.ga-proyecto-codigo {
    font-size: 12px;
    color: #64748b;
    font-family: monospace;
    font-weight: 500;
}

.ga-proyecto-estado {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.ga-proyecto-nombre {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
    margin-bottom: 8px;
}

.ga-proyecto-fecha {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 12px;
}

.ga-proyecto-fecha .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.ga-proyecto-progress {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ga-progress-bar {
    flex: 1;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.ga-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.ga-progress-fill.ga-progress-complete {
    background: linear-gradient(90deg, #22c55e 0%, #15803d 100%);
}

.ga-progress-text {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    min-width: 40px;
    text-align: right;
}

/* Estados de proyecto */
.ga-proy-planificacion {
    border-left-color: #94a3b8;
}
.ga-proy-planificacion.ga-proyecto-estado {
    background: #f1f5f9;
    color: #475569;
}

.ga-proy-progreso {
    border-left-color: #22c55e;
}
.ga-proy-progreso.ga-proyecto-estado {
    background: #dcfce7;
    color: #15803d;
}

.ga-proy-pausado {
    border-left-color: #f59e0b;
}
.ga-proy-pausado.ga-proyecto-estado {
    background: #fef3c7;
    color: #b45309;
}

.ga-proy-completado {
    border-left-color: #3b82f6;
}
.ga-proy-completado.ga-proyecto-estado {
    background: #dbeafe;
    color: #1d4ed8;
}

.ga-proy-cancelado {
    border-left-color: #dc2626;
}
.ga-proy-cancelado.ga-proyecto-estado {
    background: #fef2f2;
    color: #dc2626;
}

/* =========================================================================
   CASO CERRADO
   ========================================================================= */

.ga-caso-cierre {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    padding: 12px 16px;
    background: #f1f5f9;
    border-radius: 8px;
    color: #475569;
    font-size: 13px;
}

.ga-caso-cierre .dashicons {
    color: #22c55e;
}

/* =========================================================================
   ESTADO VACIO
   ========================================================================= */

.ga-empty-state {
    text-align: center;
    padding: 60px 40px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ga-empty-icon {
    width: 80px;
    height: 80px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.ga-empty-icon .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    color: #94a3b8;
}

.ga-empty-state h3 {
    margin: 0 0 10px 0;
    font-size: 20px;
    color: #1e293b;
}

.ga-empty-state p {
    margin: 0 0 25px 0;
    color: #64748b;
    font-size: 15px;
}

.ga-btn {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s;
}

.ga-btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.ga-btn-secondary:hover {
    background: #e2e8f0;
    color: #1e293b;
}

/* =========================================================================
   FOOTER
   ========================================================================= */

.ga-portal-footer {
    text-align: center;
    padding: 30px 20px;
    color: #94a3b8;
    font-size: 13px;
}

.ga-portal-footer a {
    color: #28a745;
    text-decoration: none;
}

.ga-portal-footer a:hover {
    text-decoration: underline;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */

@media (max-width: 768px) {
    .ga-portal-cliente {
        padding: 20px 15px;
    }

    .ga-portal-nav {
        gap: 6px;
        padding: 10px;
    }

    .ga-nav-item {
        padding: 8px 12px;
        font-size: 13px;
    }

    .ga-page-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
        padding: 20px;
    }

    .ga-page-title h1 {
        justify-content: center;
        font-size: 20px;
    }

    .ga-filters-form {
        flex-direction: column;
        align-items: stretch;
    }

    .ga-filter-tabs {
        justify-content: center;
    }

    .ga-filter-tab {
        padding: 6px 10px;
        font-size: 12px;
    }

    .ga-search-box {
        min-width: auto;
        width: 100%;
    }

    .ga-active-filters {
        flex-wrap: wrap;
    }

    .ga-clear-all {
        margin-left: 0;
        width: 100%;
        text-align: center;
        margin-top: 8px;
    }

    .ga-caso-header {
        padding: 16px;
    }

    .ga-caso-main {
        flex-direction: column;
        gap: 12px;
    }

    .ga-caso-estado-indicator {
        width: 100%;
        height: 4px;
    }

    .ga-caso-titulo {
        font-size: 16px;
    }

    .ga-caso-meta {
        flex-direction: column;
        gap: 8px;
    }

    .ga-caso-content {
        padding: 16px;
    }

    .ga-proyectos-grid {
        grid-template-columns: 1fr;
    }

    .ga-empty-state {
        padding: 40px 20px;
    }
}

@media (max-width: 480px) {
    .ga-filter-tabs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }

    .ga-filter-tab {
        justify-content: center;
    }
}
</style>

<?php get_footer(); ?>
