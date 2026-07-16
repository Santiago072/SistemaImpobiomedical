-- ============================================================
-- Migraciones pendientes — Sistema Impobiomedical
-- Ejecutar una sola vez en producción (IF NOT EXISTS / seguro repetir)
-- ============================================================

USE sistema_impobiomedical;

-- ── 1. Columnas faltantes en tabla productos ────────────────
ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS categoria        VARCHAR(100) DEFAULT NULL AFTER porcentaje_iva,
    ADD COLUMN IF NOT EXISTS codigo_producto  VARCHAR(60)  DEFAULT NULL AFTER categoria,
    ADD COLUMN IF NOT EXISTS codigo_proveedor VARCHAR(60)  DEFAULT NULL AFTER codigo_producto;

-- ── 2. Columnas faltantes en tabla cotizacion_items ─────────
ALTER TABLE cotizacion_items
    ADD COLUMN IF NOT EXISTS categoria           VARCHAR(100)  DEFAULT NULL  AFTER tiempo_entrega,
    ADD COLUMN IF NOT EXISTS codigo_producto     VARCHAR(60)   DEFAULT NULL  AFTER categoria,
    ADD COLUMN IF NOT EXISTS precio_proveedor    DECIMAL(20,2) DEFAULT 0.00  AFTER codigo_producto,
    ADD COLUMN IF NOT EXISTS porcentaje_utilidad DECIMAL(8,2)  DEFAULT 0.00  AFTER precio_proveedor,
    ADD COLUMN IF NOT EXISTS flete               DECIMAL(20,2) DEFAULT 0.00  AFTER porcentaje_utilidad,
    ADD COLUMN IF NOT EXISTS calibracion         DECIMAL(20,2) DEFAULT 0.00  AFTER flete,
    ADD COLUMN IF NOT EXISTS estampillas         DECIMAL(20,2) DEFAULT 0.00  AFTER calibracion,
    ADD COLUMN IF NOT EXISTS proveedor           VARCHAR(100)  DEFAULT NULL  AFTER estampillas,
    ADD COLUMN IF NOT EXISTS codigo_proveedor    VARCHAR(60)   DEFAULT NULL  AFTER proveedor;

-- ── 3. Columna codigo_proveedor en orden_compra_items ───────
--    (por si la tabla existía antes de agregar esta columna)
ALTER TABLE orden_compra_items
    ADD COLUMN IF NOT EXISTS codigo_proveedor VARCHAR(60) DEFAULT NULL AFTER cotizacion_item_id;

-- ── 4. Limpiar valores '0' que quedaron de registros anteriores ─────────────
UPDATE orden_compra_items SET codigo_proveedor = NULL WHERE codigo_proveedor = '0';
UPDATE cotizacion_items    SET codigo_proveedor = NULL WHERE codigo_proveedor = '0';
