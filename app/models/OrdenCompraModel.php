<?php
/**
 * OrdenCompraModel — acceso a datos de órdenes de compra.
 *
 * Flujo:
 *   1. Se crea una orden ligada a una cotización + proveedor.
 *   2. Se insertan los ítems seleccionados (snapshot de cotizacion_items).
 *   3. El P.O. es un consecutivo global autoincremental.
 */
class OrdenCompraModel
{
    private \mysqli $db;

    public function __construct(\mysqli $conexion)
    {
        $this->db = $conexion;
    }

    // ── Consecutivo P.O. ──────────────────────────────────────────────────────

    private function siguientePO(): int
    {
        $result = mysqli_query($this->db,
            "SELECT COALESCE(MAX(numero_po), 0) + 1 AS siguiente FROM ordenes_compra");
        $row = mysqli_fetch_assoc($result);
        return (int)($row['siguiente'] ?? 1);
    }

    // ── CRUD Orden ────────────────────────────────────────────────────────────

    public function crearOrden(
        int    $cotizacionId,
        string $cotizacionNumero,
        int    $usuarioId,
        string $proveedor,
        string $proveedorNit,
        string $tipoContribuyente,
        string $condicionesPago,
        string $iva,
        string $departamentoCompras,
        string $nota,
        float  $retencion,
        string $fecha
    ): int {
        mysqli_begin_transaction($this->db);
        try {
            $po   = $this->siguientePO();
            $stmt = mysqli_prepare($this->db,
                "INSERT INTO ordenes_compra
                 (numero_po, cotizacion_id, cotizacion_numero, usuario_id,
                  proveedor, proveedor_nit, tipo_contribuyente,
                  condiciones_pago, iva, departamento_compras, nota, retencion, fecha)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            // i i s i s s s s s s s d s  (13 parámetros)
            mysqli_stmt_bind_param($stmt, 'iisisssssssds',
                $po, $cotizacionId, $cotizacionNumero, $usuarioId,
                $proveedor, $proveedorNit, $tipoContribuyente,
                $condicionesPago, $iva, $departamentoCompras,
                $nota, $retencion, $fecha);
            mysqli_stmt_execute($stmt);
            $id = (int)mysqli_stmt_insert_id($stmt);
            mysqli_stmt_close($stmt);
            mysqli_commit($this->db);
            return $id;
        } catch (\Exception $e) {
            mysqli_rollback($this->db);
            throw $e;
        }
    }

    public function insertarItem(
        int    $ordenId,
        int    $cotizacionItemId,
        string $codigoProveedor,
        string $titulo,
        string $descripcion,
        int    $cantidad,
        float  $precioUnit,
        string $iva,
        float  $porcentajeIva
    ): bool {
        $total = $precioUnit * $cantidad;
        $stmt  = mysqli_prepare($this->db,
            "INSERT INTO orden_compra_items
             (orden_id, cotizacion_item_id, codigo_proveedor, titulo, descripcion,
              cantidad, precio_unit, iva, porcentaje_iva, total)
             VALUES (?,?,?,?,?,?,?,?,?,?)");
        // i i s s s i d s d d  (10 parámetros)
        mysqli_stmt_bind_param($stmt, 'iisssidsdd',
            $ordenId, $cotizacionItemId, $codigoProveedor, $titulo, $descripcion,
            $cantidad, $precioUnit, $iva, $porcentajeIva, $total);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    // ── Consultas ─────────────────────────────────────────────────────────────

    public function buscarPorId(int $id): ?array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT o.*, u.nombre AS nombre_usuario
             FROM ordenes_compra o
             LEFT JOIN usuarios u ON o.usuario_id = u.id
             WHERE o.id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function buscarPorPO(int $po): ?array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT o.*, u.nombre AS nombre_usuario
             FROM ordenes_compra o
             LEFT JOIN usuarios u ON o.usuario_id = u.id
             WHERE o.numero_po = ?");
        mysqli_stmt_bind_param($stmt, 'i', $po);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function obtenerItems(int $ordenId): array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT * FROM orden_compra_items WHERE orden_id = ? ORDER BY id ASC");
        mysqli_stmt_bind_param($stmt, 'i', $ordenId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function listarConFiltros(array $filtros, int $offset, int $limite, int $usuarioId, string $rol): array
    {
        [$where, $params, $types] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql = "SELECT o.*, u.nombre AS nombre_usuario
                FROM ordenes_compra o
                LEFT JOIN usuarios u ON o.usuario_id = u.id"
             . ($where ? " WHERE $where" : '')
             . " ORDER BY o.numero_po DESC LIMIT ? OFFSET ?";
        $types   .= 'ii';
        $params[] = $limite;
        $params[] = $offset;

        $stmt = mysqli_prepare($this->db, $sql);
        if ($params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function contarConFiltros(array $filtros, int $usuarioId, string $rol): int
    {
        [$where, $params, $types] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql  = "SELECT COUNT(*) AS total FROM ordenes_compra o" . ($where ? " WHERE $where" : '');
        $stmt = mysqli_prepare($this->db, $sql);
        if ($params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return (int)($row['total'] ?? 0);
    }

    private function construirWhere(array $filtros, int $usuarioId, string $rol): array
    {
        $condiciones = [];
        $params      = [];
        $types       = '';

        if ($rol !== 'admin' && $usuarioId > 0) {
            $condiciones[] = 'o.usuario_id = ?';
            $params[]      = $usuarioId;
            $types        .= 'i';
        }
        if (!empty($filtros['proveedor'])) {
            $condiciones[] = 'o.proveedor LIKE ?';
            $params[]      = '%' . $filtros['proveedor'] . '%';
            $types        .= 's';
        }

        if (!empty($filtros['cotizacion_numero'])) {
            $condiciones[] = 'o.cotizacion_numero LIKE ?';
            $params[]      = '%' . $filtros['cotizacion_numero'] . '%';
            $types        .= 's';
        }
        if (!empty($filtros['fecha'])) {
            $condiciones[] = 'DATE(o.fecha) = ?';
            $params[]      = $filtros['fecha'];
            $types        .= 's';
        }

        return [
            $condiciones ? implode(' AND ', $condiciones) : '',
            $params,
            $types,
        ];
    }

    public function eliminar(int $id): bool
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM ordenes_compra WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}
