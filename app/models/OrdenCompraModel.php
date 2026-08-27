<?php
/**
 * OrdenCompraModel — acceso a datos de órdenes de compra (migrado a PDO).
 *
 * Flujo:
 *   1. Se crea una orden ligada a una cotización + proveedor.
 *   2. Se insertan los ítems seleccionados (snapshot de cotizacion_items).
 *   3. El P.O. es un consecutivo global autoincremental.
 */
class OrdenCompraModel
{
    private \PDO $db;

    public function __construct(\PDO $conexion)
    {
        $this->db = $conexion;
    }

    // ── Consecutivo P.O. ──────────────────────────────────────────────────────

    private function siguientePO(): int
    {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(numero_po), 0) + 1 AS siguiente FROM ordenes_compra");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
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
        string $fecha,
        string $bancoNombre = '',
        string $bancoCuenta = '',
        string $bancoTipoCuenta = '',
        string $estadoProveedor = 'nuevo',
        float  $flete = 0.00,
        string $tipoDescuento = 'monto',
        float  $descuentoValor = 0.00,
        float  $descuento = 0.00
    ): int {
        $this->db->beginTransaction();
        try {
            $po   = $this->siguientePO();
            $stmt = $this->db->prepare(
                "INSERT INTO ordenes_compra
                 (numero_po, cotizacion_id, cotizacion_numero, usuario_id,
                  proveedor, proveedor_nit, estado_proveedor, tipo_contribuyente,
                  condiciones_pago, iva, departamento_compras, nota, retencion,
                  flete, tipo_descuento, descuento_valor, descuento, fecha,
                  banco_nombre, banco_cuenta, banco_tipo_cuenta)
                 VALUES (:po, :cid, :cnum, :uid, :prov, :pnit, :eprov, :tcont,
                         :condpago, :iva, :depto, :nota, :ret,
                         :flete, :tdesc, :dval, :desc, :fecha,
                         :bnom, :bcuenta, :btipo)"
            );
            $stmt->execute([
                ':po'      => $po,
                ':cid'     => $cotizacionId,
                ':cnum'    => $cotizacionNumero,
                ':uid'     => $usuarioId,
                ':prov'    => $proveedor,
                ':pnit'    => $proveedorNit,
                ':eprov'   => $estadoProveedor,
                ':tcont'   => $tipoContribuyente,
                ':condpago'=> $condicionesPago,
                ':iva'     => $iva,
                ':depto'   => $departamentoCompras,
                ':nota'    => $nota,
                ':ret'     => $retencion,
                ':flete'   => $flete,
                ':tdesc'   => $tipoDescuento,
                ':dval'    => $descuentoValor,
                ':desc'    => $descuento,
                ':fecha'   => $fecha,
                ':bnom'    => $bancoNombre,
                ':bcuenta' => $bancoCuenta,
                ':btipo'   => $bancoTipoCuenta,
            ]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Consulta el historial de un proveedor exclusivamente en base a órdenes de compra emitidas.
     * Retorna registrado = true únicamente si ya tiene al menos una orden previa (>= 1).
     */
    public function buscarHistorialProveedor(string $termino): array
    {
        $termino = trim($termino);
        if (empty($termino)) {
            return ['registrado' => false, 'ordenes' => 0, 'datos' => null];
        }

        // 1. Contar total de órdenes previas de este proveedor (coincidencia exacta o parcial sin espacios extra)
        $stmtCount = $this->db->prepare(
            "SELECT COUNT(*) AS total
             FROM ordenes_compra
             WHERE LOWER(REPLACE(TRIM(proveedor), ' ', '')) = LOWER(REPLACE(:term, ' ', ''))
                OR (proveedor_nit != '' AND TRIM(proveedor_nit) = :term)"
        );
        $stmtCount->execute([':term' => $termino]);
        $totalOrdenes = (int)$stmtCount->fetchColumn();

        // 2. Si tiene al menos 1 orden previa, es un proveedor registrado
        if ($totalOrdenes > 0) {
            $stmt = $this->db->prepare(
                "SELECT proveedor, proveedor_nit, tipo_contribuyente, condiciones_pago,
                        banco_nombre, banco_cuenta, banco_tipo_cuenta
                 FROM ordenes_compra
                 WHERE LOWER(REPLACE(TRIM(proveedor), ' ', '')) = LOWER(REPLACE(:term, ' ', ''))
                    OR (proveedor_nit != '' AND TRIM(proveedor_nit) = :term)
                 ORDER BY id DESC
                 LIMIT 1"
            );
            $stmt->execute([':term' => $termino]);
            $row = $stmt->fetch();

            return [
                'registrado' => true,
                'ordenes'    => $totalOrdenes,
                'datos'      => [
                    'proveedor'          => $row['proveedor'] ?? $termino,
                    'proveedor_nit'      => $row['proveedor_nit'] ?? '',
                    'tipo_contribuyente' => $row['tipo_contribuyente'] ?? '',
                    'condiciones_pago'   => $row['condiciones_pago'] ?? '',
                    'banco_nombre'       => $row['banco_nombre'] ?? '',
                    'banco_cuenta'       => $row['banco_cuenta'] ?? '',
                    'banco_tipo_cuenta'  => $row['banco_tipo_cuenta'] ?? '',
                ]
            ];
        }

        // Si no tiene órdenes previas emitidas en el sistema, es proveedor nuevo
        return ['registrado' => false, 'ordenes' => 0, 'datos' => null];
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
        $stmt  = $this->db->prepare(
            "INSERT INTO orden_compra_items
             (orden_id, cotizacion_item_id, codigo_proveedor, titulo, descripcion,
              cantidad, precio_unit, iva, porcentaje_iva, total)
             VALUES (:oid, :ciid, :cprov, :tit, :desc, :cant, :prec, :iva, :porciva, :total)"
        );
        return $stmt->execute([
            ':oid'    => $ordenId,
            ':ciid'   => $cotizacionItemId,
            ':cprov'  => $codigoProveedor,
            ':tit'    => $titulo,
            ':desc'   => $descripcion,
            ':cant'   => $cantidad,
            ':prec'   => $precioUnit,
            ':iva'    => $iva,
            ':porciva'=> $porcentajeIva,
            ':total'  => $total,
        ]);
    }

    // ── Consultas ─────────────────────────────────────────────────────────────

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.nombre AS nombre_usuario
             FROM ordenes_compra o
             LEFT JOIN usuarios u ON o.usuario_id = u.id
             WHERE o.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorPO(int $po): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.nombre AS nombre_usuario
             FROM ordenes_compra o
             LEFT JOIN usuarios u ON o.usuario_id = u.id
             WHERE o.numero_po = :po"
        );
        $stmt->execute([':po' => $po]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function obtenerItems(int $ordenId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM orden_compra_items WHERE orden_id = :oid ORDER BY id ASC"
        );
        $stmt->execute([':oid' => $ordenId]);
        return $stmt->fetchAll();
    }

    public function obtenerItemsPorOrdenIds(array $ordenIds): array
    {
        if (empty($ordenIds)) {
            return [];
        }
        $cleanIds = array_map('intval', array_filter($ordenIds, 'is_numeric'));
        if (empty($cleanIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT * FROM orden_compra_items WHERE orden_id IN ($placeholders) ORDER BY orden_id ASC, id ASC"
        );
        $stmt->execute($cleanIds);
        
        $agrupados = [];
        foreach ($stmt->fetchAll() as $item) {
            $agrupados[(int)$item['orden_id']][] = $item;
        }
        return $agrupados;
    }

    public function listarConFiltros(array $filtros, int $offset, int $limite, int $usuarioId, string $rol): array
    {
        [$where, $params] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql = "SELECT o.*, u.nombre AS nombre_usuario, c.cliente_nombre
                FROM ordenes_compra o
                LEFT JOIN usuarios u ON o.usuario_id = u.id
                LEFT JOIN cotizaciones c ON o.cotizacion_id = c.id"
             . ($where ? " WHERE $where" : '')
             . " ORDER BY o.numero_po DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listarParaExcel(array $filtros, int $usuarioId, string $rol): array
    {
        [$where, $params] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql = "SELECT o.*, u.nombre AS nombre_usuario, c.cliente_nombre
                FROM ordenes_compra o
                LEFT JOIN usuarios u ON o.usuario_id = u.id
                LEFT JOIN cotizaciones c ON o.cotizacion_id = c.id"
             . ($where ? " WHERE $where" : '')
             . " ORDER BY o.numero_po DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contarConFiltros(array $filtros, int $usuarioId, string $rol): int
    {
        [$where, $params] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql  = "SELECT COUNT(*) AS total FROM ordenes_compra o" . ($where ? " WHERE $where" : '');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function listarPorIds(array $ids, int $usuarioId, string $rol): array
    {
        if (empty($ids)) {
            return [];
        }

        // Sanitizar array de IDs a enteros
        $cleanIds = array_map('intval', array_filter($ids, 'is_numeric'));
        if (empty($cleanIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $sql = "SELECT o.*, u.nombre AS nombre_usuario, c.cliente_nombre
                FROM ordenes_compra o
                LEFT JOIN usuarios u ON o.usuario_id = u.id
                LEFT JOIN cotizaciones c ON o.cotizacion_id = c.id
                WHERE o.id IN ($placeholders)";

        $params = $cleanIds;
        if (!in_array($rol, ['admin', 'compras'], true) && $usuarioId > 0) {
            $sql .= " AND o.usuario_id = ?";
            $params[] = $usuarioId;
        }

        $sql .= " ORDER BY o.numero_po DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function actualizarEstado(int $id, string $nuevoEstado): bool
    {
        $estadosValidos = ['pendiente', 'completada'];
        if (!in_array($nuevoEstado, $estadosValidos, true)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE ordenes_compra SET estado = :est WHERE id = :id");
        return $stmt->execute([
            ':est' => $nuevoEstado,
            ':id'  => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM ordenes_compra WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function construirWhere(array $filtros, int $usuarioId, string $rol): array
    {
        $condiciones = [];
        $params      = [];

        if (!in_array($rol, ['admin', 'compras'], true) && $usuarioId > 0) {
            $condiciones[]   = 'o.usuario_id = :uid';
            $params[':uid']  = $usuarioId;
        }
        if (!empty($filtros['estado'])) {
            $condiciones[]   = 'o.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['proveedor'])) {
            $condiciones[]       = 'o.proveedor LIKE :prov';
            $params[':prov']     = '%' . $filtros['proveedor'] . '%';
        }
        if (!empty($filtros['cotizacion_numero'])) {
            $condiciones[]       = 'o.cotizacion_numero LIKE :cnum';
            $params[':cnum']     = '%' . $filtros['cotizacion_numero'] . '%';
        }
        if (!empty($filtros['fecha_inicio'])) {
            $condiciones[]       = 'DATE(o.fecha) >= :fi';
            $params[':fi']       = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $condiciones[]       = 'DATE(o.fecha) <= :ff';
            $params[':ff']       = $filtros['fecha_fin'];
        }

        return [
            $condiciones ? implode(' AND ', $condiciones) : '',
            $params,
        ];
    }
}
