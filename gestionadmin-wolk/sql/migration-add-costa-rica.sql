-- ============================================================================
-- MIGRACIÓN: Agregar Costa Rica a wp_ga_paises_config
-- ============================================================================
-- Ejecutar este script si Costa Rica no aparece en la lista de países.
-- Solo inserta si el país no existe (evita duplicados).
--
-- Datos de Costa Rica:
-- - Código ISO: CR
-- - Moneda: Colón Costarricense (CRC) ₡
-- - Impuesto: IVA 13%
-- - Facturación Electrónica: Ministerio de Hacienda Costa Rica
-- ============================================================================

-- Paso 1: Verificar si Costa Rica ya existe
SELECT COUNT(*) AS existe FROM wp_ga_paises_config WHERE codigo_iso = 'CR';

-- Paso 2: Insertar Costa Rica (solo si no existe)
-- NOTA: Cambia 'wp_' por tu prefijo de tablas si es diferente
INSERT INTO wp_ga_paises_config (
    codigo_iso,
    nombre,
    moneda_codigo,
    moneda_simbolo,
    impuesto_nombre,
    impuesto_porcentaje,
    retencion_default,
    formato_factura,
    requiere_electronica,
    proveedor_electronica,
    activo,
    created_at
)
SELECT
    'CR',
    'Costa Rica',
    'CRC',
    '₡',
    'IVA',
    13.00,
    0.00,
    'FE-CR-{YYYY}-{NNNN}',
    1,
    'Ministerio de Hacienda Costa Rica',
    1,
    NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM wp_ga_paises_config WHERE codigo_iso = 'CR'
);

-- Paso 3: Verificar que se insertó correctamente
SELECT * FROM wp_ga_paises_config WHERE codigo_iso = 'CR';

-- ============================================================================
-- FIN DE MIGRACIÓN
-- ============================================================================
