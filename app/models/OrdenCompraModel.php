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
        string $bancoTipoCuenta = ''
    ): int {
        $this->db->beginTransaction();
        try {
            $po   = $this->siguientePO();
            $stmt = $this->db->prepare(
                "INSERT INTO ordenes_compra
                 (numero_po, cotizacion_id, cotizacion_numero, usuario_id,
                  proveedor, proveedor_nit, tipo_contribuyente,
                  condiciones_pago, iva, departamento_compras, nota, retencion, fecha,
                  banco_nombre, banco_cuenta, banco_tipo_cuenta)
                 VALUES (:po, :cid, :cnum, :uid, :prov, :pnit, :tcont,
                         :condpago, :iva, :depto, :nota, :ret, :fecha,
                         :bnom, :bcuenta, :btipo)"
            );
            $stmt->execute([
                ':po'      => $po,
                ':cid'     => $cotizacionId,
                ':cnum'    => $cotizacionNumero,
                ':uid'     => $usuarioId,
                ':prov'    => $proveedor,
                ':pnit'    => $proveedorNit,
                ':tcont'   => $tipoContribuyente,
                ':condpago'=> $condicionesPago,
                ':iva'     => $iva,
                ':depto'   => $departamentoCompras,
                ':nota'    => $nota,
                ':ret'     => $retencion,
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

    private function construirWhere(array $filtros, int $usuarioId, string $rol): array
    {
        $condiciones = [];
        $params      = [];

        if ($rol !== 'admin' && $usuarioId > 0) {
            $condiciones[]   = 'o.usuario_id = :uid';
            $params[':uid']  = $usuarioId;
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

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM ordenes_compra WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
