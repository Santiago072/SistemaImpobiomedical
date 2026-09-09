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
        ?int   $cotizacionId,
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
        float  $descuento = 0.00,
        string $fleteIva = 'no',
        float  $fletePorcentajeIva = 19.00
    ): int {
        $this->db->beginTransaction();
        try {
            $po   = $this->siguientePO();
            $stmt = $this->db->prepare(
                "INSERT INTO ordenes_compra
                 (numero_po, cotizacion_id, cotizacion_numero, usuario_id,
                  proveedor, proveedor_nit, estado_proveedor, tipo_contribuyente,
                  condiciones_pago, iva, departamento_compras,
                  nota, retencion, flete, flete_iva, flete_porcentaje_iva,
                  tipo_descuento, descuento_valor, descuento, fecha,
                  banco_nombre, banco_cuenta, banco_tipo_cuenta)
                 VALUES (:po, :cid, :cnum, :uid, :prov, :pnit, :eprov, :tcont,
                         :condpago, :iva, :depto, :nota, :ret,
                         :flete, :fiva, :fpctiva,
                         :tdesc, :dval, :desc, :fecha,
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
                ':fiva'    => $fleteIva,
                ':fpctiva' => $fletePorcentajeIva,
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

    public function actualizarOrden(
        int    $ordenId,
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
        float  $descuento = 0.00,
        string $fleteIva = 'no',
        float  $fletePorcentajeIva = 19.00
    ): bool {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE ordenes_compra SET
                    proveedor = :prov,
                    proveedor_nit = :pnit,
                    estado_proveedor = :eprov,
                    tipo_contribuyente = :tcont,
                    condiciones_pago = :condpago,
                    iva = :iva,
                    departamento_compras = :depto,
                    nota = :nota,
                    retencion = :ret,
                    flete = :flete,
                    flete_iva = :fiva,
                    flete_porcentaje_iva = :fpctiva,
                    tipo_descuento = :tdesc,
                    descuento_valor = :dval,
                    descuento = :desc,
                    fecha = :fecha,
                    banco_nombre = :bnom,
                    banco_cuenta = :bcuenta,
                    banco_tipo_cuenta = :btipo
                 WHERE id = :oid"
            );
            $stmt->execute([
                ':oid'     => $ordenId,
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
                ':fiva'    => $fleteIva,
                ':fpctiva' => $fletePorcentajeIva,
                ':tdesc'   => $tipoDescuento,
                ':dval'    => $descuentoValor,
                ':desc'    => $descuento,
                ':fecha'   => $fecha,
                ':bnom'    => $bancoNombre,
                ':bcuenta' => $bancoCuenta,
                ':btipo'   => $bancoTipoCuenta,
            ]);

            // Eliminar items existentes para reemplazarlos con los actualizados
            $stmtDel = $this->db->prepare("DELETE FROM orden_compra_items WHERE orden_id = :oid");
            $stmtDel->execute([':oid' => $ordenId]);

            $this->db->commit();
            return true;
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

        try {
            $termRaw = $termino;
            $termNitNorm = function_exists('normalizar_nit') ? normalizar_nit($termino) : str_replace(['.', ' '], '', $termino);

            // 1. Primero buscar en la tabla oficial de proveedores (por NIT normalizado, NIT crudo o nombre)
            $stmtProv = $this->db->prepare(
                "SELECT id, nit, nombre_proveedor, tipo_contribuyente,
                        nombre_banco, numero_cuenta, tipo_cuenta, estado
                 FROM proveedores
                 WHERE REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :p_nitnorm
                    OR TRIM(nit) = :p_nit
                    OR LOWER(TRIM(nombre_proveedor)) = LOWER(:p_exact)
                    OR LOWER(TRIM(nombre_proveedor)) LIKE LOWER(:p_like)
                 ORDER BY (REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :p_nitnorm_exact) DESC, id DESC
                 LIMIT 1"
            );
            $stmtProv->execute([
                ':p_nitnorm'       => $termNitNorm,
                ':p_nit'           => $termRaw,
                ':p_exact'         => $termRaw,
                ':p_like'          => '%' . $termRaw . '%',
                ':p_nitnorm_exact' => $termNitNorm
            ]);
            $provOficial = $stmtProv->fetch();

            // Contar total de órdenes previas de este proveedor (por NIT o nombre)
            $nitOficialNorm = ($provOficial && !empty($provOficial['nit'])) 
                ? (function_exists('normalizar_nit') ? normalizar_nit($provOficial['nit']) : str_replace(['.', ' '], '', $provOficial['nit'])) 
                : '';
            $nombreOficial = $provOficial ? trim($provOficial['nombre_proveedor']) : '';

            $stmtCount = $this->db->prepare(
                "SELECT COUNT(*) AS total
                 FROM ordenes_compra
                 WHERE LOWER(TRIM(proveedor)) = LOWER(:c_exact)
                    OR LOWER(TRIM(proveedor)) LIKE LOWER(:c_like)
                    OR (:c_nom_oficial != '' AND LOWER(TRIM(proveedor)) = LOWER(:c_nom_oficial2))
                    OR (proveedor_nit != '' AND (
                        REPLACE(REPLACE(TRIM(proveedor_nit), '.', ''), ' ', '') = :c_nitnorm
                        OR (:c_nit_oficial != '' AND REPLACE(REPLACE(TRIM(proveedor_nit), '.', ''), ' ', '') = :c_nit_oficial2)
                    ))"
            );
            $stmtCount->execute([
                ':c_exact'        => $termRaw,
                ':c_like'         => '%' . $termRaw . '%',
                ':c_nom_oficial'  => $nombreOficial,
                ':c_nom_oficial2' => $nombreOficial,
                ':c_nitnorm'      => $termNitNorm,
                ':c_nit_oficial'  => $nitOficialNorm,
                ':c_nit_oficial2' => $nitOficialNorm
            ]);
            $totalOrdenes = (int)$stmtCount->fetchColumn();

            $esRegistrado = ($totalOrdenes >= 1);

            // Si se encuentra en proveedores oficial:
            if ($provOficial) {
                return [
                    'registrado' => $esRegistrado,
                    'ordenes'    => $totalOrdenes,
                    'datos'      => [
                        'proveedor'          => $provOficial['nombre_proveedor'],
                        'proveedor_nit'      => $provOficial['nit'],
                        'tipo_contribuyente' => $provOficial['tipo_contribuyente'] ?: 'PERSONA JURÍDICA',
                        'condiciones_pago'   => 'Según acuerdo',
                        'banco_nombre'       => $provOficial['nombre_banco'] ?: '',
                        'banco_cuenta'       => $provOficial['numero_cuenta'] ?: '',
                        'banco_tipo_cuenta'  => $provOficial['tipo_cuenta'] ?: '',
                    ]
                ];
            }

            // 2. Fallback: Si no está en proveedores pero tiene órdenes previas
            if ($totalOrdenes > 0) {
                $stmt = $this->db->prepare(
                    "SELECT proveedor, proveedor_nit, tipo_contribuyente, condiciones_pago,
                            banco_nombre, banco_cuenta, banco_tipo_cuenta
                     FROM ordenes_compra
                     WHERE LOWER(TRIM(proveedor)) = LOWER(:termExact)
                        OR LOWER(TRIM(proveedor)) LIKE LOWER(:termLike)
                        OR (proveedor_nit != '' AND TRIM(proveedor_nit) = :termNit)
                     ORDER BY id DESC
                     LIMIT 1"
                );
                $stmt->execute([
                    ':termExact' => $termino,
                    ':termLike'  => '%' . $termino . '%',
                    ':termNit'   => $termino
                ]);
                $row = $stmt->fetch();

                return [
                    'registrado' => $esRegistrado,
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
        } catch (\Throwable $e) {
            error_log('Error en buscarHistorialProveedor: ' . $e->getMessage());
        }

        return ['registrado' => false, 'ordenes' => 0, 'datos' => null];
    }

    public function insertarItem(
        int    $ordenId,
        ?int   $cotizacionItemId,
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
        $sql = "SELECT o.*, 
                       CASE 
                           WHEN (
                               SELECT COUNT(*) 
                               FROM ordenes_compra o2 
                               WHERE o2.id < o.id 
                                 AND (
                                     (o.proveedor != '' AND LOWER(TRIM(o2.proveedor)) = LOWER(TRIM(o.proveedor)))
                                     OR 
                                     (o.proveedor_nit != '' AND REPLACE(REPLACE(TRIM(o2.proveedor_nit), '.', ''), ' ', '') = REPLACE(REPLACE(TRIM(o.proveedor_nit), '.', ''), ' ', ''))
                                 )
                           ) >= 1 THEN 'registrado'
                           ELSE 'nuevo'
                       END AS estado_proveedor,
                       u.nombre AS nombre_usuario, c.cliente_nombre
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
        $sql = "SELECT o.*, 
                       CASE 
                           WHEN (
                               SELECT COUNT(*) 
                               FROM ordenes_compra o2 
                               WHERE o2.id < o.id 
                                 AND (
                                     (o.proveedor != '' AND LOWER(TRIM(o2.proveedor)) = LOWER(TRIM(o.proveedor)))
                                     OR 
                                     (o.proveedor_nit != '' AND REPLACE(REPLACE(TRIM(o2.proveedor_nit), '.', ''), ' ', '') = REPLACE(REPLACE(TRIM(o.proveedor_nit), '.', ''), ' ', ''))
                                 )
                           ) >= 1 THEN 'registrado'
                           ELSE 'nuevo'
                       END AS estado_proveedor,
                       u.nombre AS nombre_usuario, c.cliente_nombre
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
        $sql = "SELECT o.*, 
                       CASE 
                           WHEN (
                               SELECT COUNT(*) 
                               FROM ordenes_compra o2 
                               WHERE o2.id < o.id 
                                 AND (
                                     (o.proveedor != '' AND LOWER(TRIM(o2.proveedor)) = LOWER(TRIM(o.proveedor)))
                                     OR 
                                     (o.proveedor_nit != '' AND REPLACE(REPLACE(TRIM(o2.proveedor_nit), '.', ''), ' ', '') = REPLACE(REPLACE(TRIM(o.proveedor_nit), '.', ''), ' ', ''))
                                 )
                           ) >= 1 THEN 'registrado'
                           ELSE 'nuevo'
                       END AS estado_proveedor,
                       u.nombre AS nombre_usuario, c.cliente_nombre
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
