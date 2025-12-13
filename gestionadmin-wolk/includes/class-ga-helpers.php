<?php
/**
 * Funciones Helper - GestionAdmin
 *
 * Funciones utilitarias compartidas en todo el plugin.
 * Incluye conversión de tiempos, formateo, etc.
 *
 * @package    GestionAdmin_Wolk
 * @subpackage Includes
 * @since      1.5.0
 * @author     Wolksoftcr.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// =========================================================================
// FUNCIONES DE TIEMPO
// =========================================================================
// ESTÁNDAR: Todos los tiempos se almacenan en MINUTOS en la BD.
// Al mostrar al usuario: mostrar minutos Y horas calculadas.
// =========================================================================

/**
 * Convertir minutos a horas decimales
 *
 * @param int|float $minutos Cantidad de minutos
 * @return float Horas decimales (ej: 90 min = 1.50 hrs)
 */
function ga_minutos_a_horas($minutos) {
    return round(floatval($minutos) / 60, 2);
}

/**
 * Convertir horas decimales a minutos
 *
 * @param float $horas Cantidad de horas
 * @return int Minutos enteros
 */
function ga_horas_a_minutos($horas) {
    return intval(round(floatval($horas) * 60));
}

/**
 * Formatear tiempo para mostrar al usuario
 *
 * Muestra minutos con su equivalente en horas.
 * Ejemplo: "120 min (2.00 hrs)"
 *
 * @param int|float $minutos Cantidad de minutos
 * @param bool      $compact Si es true, usa formato corto
 * @return string Tiempo formateado
 */
function ga_formatear_tiempo($minutos, $compact = false) {
    $minutos = intval($minutos);
    $horas = ga_minutos_a_horas($minutos);

    if ($compact) {
        return sprintf('%dm (%.1fh)', $minutos, $horas);
    }

    return sprintf('%d min (%.2f hrs)', $minutos, $horas);
}

/**
 * Formatear tiempo solo en horas con minutos
 *
 * Ejemplo: "2h 30m" o "45m"
 *
 * @param int $minutos Cantidad de minutos
 * @return string Tiempo formateado
 */
function ga_formatear_tiempo_hm($minutos) {
    $minutos = intval($minutos);

    if ($minutos < 60) {
        return sprintf('%dm', $minutos);
    }

    $horas = floor($minutos / 60);
    $mins = $minutos % 60;

    if ($mins === 0) {
        return sprintf('%dh', $horas);
    }

    return sprintf('%dh %dm', $horas, $mins);
}

/**
 * Parsear entrada de tiempo del usuario
 *
 * Acepta formatos: "120" (minutos), "2h", "2h30m", "2:30"
 *
 * @param string $input Entrada del usuario
 * @return int Minutos
 */
function ga_parsear_tiempo($input) {
    $input = trim(strtolower($input));

    // Solo número = minutos
    if (is_numeric($input)) {
        return intval($input);
    }

    // Formato "2h" o "2h30m"
    if (preg_match('/^(\d+)h(?:\s*(\d+)m?)?$/', $input, $matches)) {
        $horas = intval($matches[1]);
        $minutos = isset($matches[2]) ? intval($matches[2]) : 0;
        return ($horas * 60) + $minutos;
    }

    // Formato "30m"
    if (preg_match('/^(\d+)m$/', $input, $matches)) {
        return intval($matches[1]);
    }

    // Formato "2:30" (horas:minutos)
    if (preg_match('/^(\d+):(\d+)$/', $input, $matches)) {
        $horas = intval($matches[1]);
        $minutos = intval($matches[2]);
        return ($horas * 60) + $minutos;
    }

    // Si no se puede parsear, retornar 0
    return 0;
}

/**
 * Validar que el tiempo esté en un rango razonable
 *
 * @param int $minutos  Minutos a validar
 * @param int $min      Mínimo permitido (default 1)
 * @param int $max      Máximo permitido (default 40 horas = 2400 min)
 * @return bool True si es válido
 */
function ga_validar_tiempo($minutos, $min = 1, $max = 2400) {
    $minutos = intval($minutos);
    return $minutos >= $min && $minutos <= $max;
}

// =========================================================================
// FUNCIONES DE FORMATO MONETARIO
// =========================================================================

/**
 * Formatear monto como moneda
 *
 * @param float  $monto    Monto a formatear
 * @param string $moneda   Código de moneda (default USD)
 * @param bool   $simbolo  Incluir símbolo
 * @return string Monto formateado
 */
function ga_formatear_moneda($monto, $moneda = 'USD', $simbolo = true) {
    $monto = floatval($monto);

    $simbolos = array(
        'USD' => '$',
        'COP' => '$',
        'MXN' => '$',
        'EUR' => '€',
        'CRC' => '₡',
    );

    $decimales = ($moneda === 'COP') ? 0 : 2;
    $formatted = number_format($monto, $decimales, '.', ',');

    if ($simbolo && isset($simbolos[$moneda])) {
        return $simbolos[$moneda] . $formatted;
    }

    return $formatted . ' ' . $moneda;
}

// =========================================================================
// FUNCIONES DE FECHA
// =========================================================================

/**
 * Formatear fecha para mostrar
 *
 * @param string $fecha  Fecha en formato MySQL
 * @param string $formato Formato de salida (default d/m/Y)
 * @return string Fecha formateada
 */
function ga_formatear_fecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha) || $fecha === '0000-00-00') {
        return '-';
    }
    return date_i18n($formato, strtotime($fecha));
}

/**
 * Tiempo transcurrido en formato humano
 *
 * @param string $fecha Fecha en formato MySQL
 * @return string Tiempo transcurrido (ej: "hace 2 días")
 */
function ga_tiempo_transcurrido($fecha) {
    if (empty($fecha)) {
        return '-';
    }
    return human_time_diff(strtotime($fecha), current_time('timestamp'));
}

// =========================================================================
// FUNCIONES DE TEXTO
// =========================================================================

/**
 * Truncar texto con ellipsis
 *
 * @param string $texto   Texto a truncar
 * @param int    $longitud Longitud máxima
 * @param string $sufijo  Sufijo a agregar (default ...)
 * @return string Texto truncado
 */
function ga_truncar_texto($texto, $longitud = 50, $sufijo = '...') {
    $texto = wp_strip_all_tags($texto);
    if (strlen($texto) <= $longitud) {
        return $texto;
    }
    return substr($texto, 0, $longitud) . $sufijo;
}

// =========================================================================
// FUNCIONES DE ESTADO
// =========================================================================

/**
 * Obtener clase CSS para badge de estado genérico
 *
 * @param string $estado Estado
 * @param string $tipo   Tipo de entidad (tarea, orden, factura, etc.)
 * @return string Clase CSS
 */
function ga_estado_badge_class($estado, $tipo = 'default') {
    $mapeo = array(
        // Estados positivos
        'COMPLETADA'  => 'ga-badge-success',
        'PAGADA'      => 'ga-badge-success',
        'APROBADA'    => 'ga-badge-success',
        'VERIFICADO'  => 'ga-badge-success',
        'ACTIVO'      => 'ga-badge-success',
        'PUBLICADA'   => 'ga-badge-success',

        // Estados de advertencia
        'PENDIENTE'   => 'ga-badge-warning',
        'EN_REVISION' => 'ga-badge-warning',
        'EN_PROGRESO' => 'ga-badge-info',
        'PARCIAL'     => 'ga-badge-warning',
        'PAUSADA'     => 'ga-badge-warning',

        // Estados negativos
        'RECHAZADA'   => 'ga-badge-danger',
        'CANCELADA'   => 'ga-badge-danger',
        'ANULADA'     => 'ga-badge-danger',
        'VENCIDA'     => 'ga-badge-danger',

        // Estados neutros
        'BORRADOR'    => 'ga-badge-secondary',
        'CERRADA'     => 'ga-badge-secondary',
        'ARCHIVADA'   => 'ga-badge-secondary',

        // Estados informativos
        'ENVIADA'     => 'ga-badge-info',
        'ASIGNADA'    => 'ga-badge-info',
        'DISPONIBLE'  => 'ga-badge-primary',
        'SOLICITADA'  => 'ga-badge-info',
    );

    return $mapeo[$estado] ?? 'ga-badge-secondary';
}
