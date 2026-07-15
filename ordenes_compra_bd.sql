-- ============================================================
-- Módulo: Órdenes de Compra — Sistema Impobiomedical
-- Ejecutar en: sistema_impobiomedical
-- ============================================================

USE sistema_impobiomedical;

-- ── Cabecera de la orden ────────────────────────────────────
CREATE TABLE IF NOT EXISTS ordenes_compra (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    numero_po            INT NOT NULL,                          -- P.O. consecutivo global
    cotizacion_id        INT NOT NULL,
    cotizacion_numero    VARCHAR(30) NOT NULL,
    usuario_id           INT NOT NULL,
    -- Datos del proveedor
    proveedor            VARCHAR(200) NOT NULL,
    proveedor_nit        VARCHAR(30) DEFAULT NULL,
    tipo_contribuyente   VARCHAR(100) DEFAULT NULL,
    -- Datos de la orden
    condiciones_pago     VARCHAR(100) DEFAULT 'Según acuerdo',
    iva                  VARCHAR(20) DEFAULT '19%',
    departamento_compras VARCHAR(100) DEFAULT NULL,
    nota                 TEXT DEFAULT NULL,
    retencion            DECIMAL(5,2) DEFAULT 2.50,             -- Porcentaje de retención
    fecha                DATE NOT NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_numero_po (numero_po),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id)    REFERENCES usuarios(id),
    INDEX idx_po_numero   (numero_po),
    INDEX idx_po_proveedor (proveedor),
    INDEX idx_po_fecha     (fecha),
    INDEX idx_po_usuario   (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Ítems de la orden ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS orden_compra_items (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    orden_id            INT NOT NULL,
    cotizacion_item_id  INT NOT NULL,                           -- Referencia al ítem de la cotización
    codigo_proveedor    VARCHAR(60) DEFAULT NULL,
    titulo              VARCHAR(255) NOT NULL,
    descripcion         TEXT DEFAULT NULL,
    cantidad            INT NOT NULL DEFAULT 1,
    precio_unit         DECIMAL(20,2) NOT NULL DEFAULT 0.00,
    iva                 ENUM('si','no') NOT NULL DEFAULT 'si',
    porcentaje_iva      DECIMAL(5,2) NOT NULL DEFAULT 19.00,
    total               DECIMAL(20,2) NOT NULL DEFAULT 0.00,    -- precio_unit * cantidad (sin IVA)
    FOREIGN KEY (orden_id) REFERENCES ordenes_compra(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
