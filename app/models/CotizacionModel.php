<?php
/**
 * CotizacionModel — acceso a datos de cotizaciones y sus ítems.
 *
 * Principios aplicados:
 *   - SRP: toda la lógica de acceso a datos vive aquí.
 *   - Número de cotización: codigo_usuario + consecutivo mensual (Ej: EB01, EB02).
 *     Cada mes el consecutivo se reinicia. Se usa transacción + SELECT FOR UPDATE
 *     para evitar colisiones concurrentes.
 */
class CotizacionModel
{
    private \mysqli $db;

    public function __construct(\mysqli $conexion)
    {
        $this->db = $conexion;
    }

    // ── Contadores para Dashboard ─────────────────────────────────────────────

    public function contarDelUsuario(int $usuarioId): int
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT COUNT(*) AS total FROM cotizaciones
             WHERE usuario_id = ? AND estado = 'finalizada'");
        mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return (int)($row['total'] ?? 0);
    }

    public function contarTotal(): int
    {
        $result = mysqli_query($this->db,
            "SELECT COUNT(*) AS total FROM cotizaciones WHERE estado = 'finalizada'");
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    public function contarDelMes(): int
    {
        $result = mysqli_query($this->db,
            "SELECT COUNT(*) AS total FROM cotizaciones
             WHERE estado='finalizada' AND MONTH(fecha_creacion)=MONTH(CURDATE())
             AND YEAR(fecha_creacion)=YEAR(CURDATE())");
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    // ── Cabecera ──────────────────────────────────────────────────────────────

    public function buscarPorId(int $id): ?array
    {
        $stmt = mysqli_prepare($this->db, 'SELECT * FROM cotizaciones WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function buscarPorNumero(string $numero): ?array
    {
        $stmt = mysqli_prepare($this->db,
            'SELECT * FROM cotizaciones WHERE numero_cotizacion = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $numero);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    /** Borrador con ítems, sin número, del usuario */
    public function buscarBorradorConItems(int $usuarioId): ?int
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT c.id FROM cotizaciones c
             INNER JOIN cotizacion_items i ON c.id = i.cotizacion_id
             WHERE c.usuario_id = ?
               AND (c.numero_cotizacion IS NULL OR c.numero_cotizacion = '')
               AND c.estado = 'borrador'
             ORDER BY c.id DESC LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? (int)$row['id'] : null;
    }

    public function buscarCabeceraVacia(int $usuarioId): ?int
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT id FROM cotizaciones
             WHERE usuario_id = ?
               AND (numero_cotizacion IS NULL OR numero_cotizacion = '')
               AND estado = 'borrador'
             ORDER BY id DESC LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? (int)$row['id'] : null;
    }

    public function crearCabecera(int $usuarioId, string $usuarioCodigo, string $asesorNombre, string $asesorCargo): int
    {
        $stmt = mysqli_prepare($this->db,
            'INSERT INTO cotizaciones (usuario_id, usuario_codigo, asesor_nombre, asesor_cargo) VALUES (?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'isss', $usuarioId, $usuarioCodigo, $asesorNombre, $asesorCargo);
        mysqli_stmt_execute($stmt);
        $id = (int)mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);
        return $id;
    }

    /**
     * Genera el número de cotización: CODIGO_USUARIO + consecutivo mensual de 2 dígitos.
     * Ejemplo: EB01, EB02, ..., EB99
     * El consecutivo se reinicia cada mes.
     * Usa transacción + SELECT FOR UPDATE para evitar colisiones concurrentes.
     */
    public function finalizarCotizacion(
        int    $id,
        string $fechaCreacion,
        int    $diasValidez,
        string $condicionesPago,
        string $observaciones,
        string $clienteNombre,
        string $clienteNit,
        string $clienteDireccion,
        string $clienteTelefono,
        string $clienteCorreo,
        string $clienteContacto,
        string $clienteCiudad,
        ?int   $clienteId = null,
        string $asesorNombre = '',
        string $asesorCargo = '',
        string $usuarioCodigo = ''
    ): string {
        mysqli_begin_transaction($this->db);
        try {
            // Utilizar el código del usuario actual si se proporcionó, si no, buscar en la bd
            if (!empty($usuarioCodigo)) {
                $codigo = $usuarioCodigo;
            } else {
                $stmtCodigo = mysqli_prepare($this->db,
                    'SELECT usuario_codigo FROM cotizaciones WHERE id = ? FOR UPDATE');
                mysqli_stmt_bind_param($stmtCodigo, 'i', $id);
                mysqli_stmt_execute($stmtCodigo);
                $resCodigo   = mysqli_stmt_get_result($stmtCodigo);
                $rowCodigo   = mysqli_fetch_assoc($resCodigo);
                $codigo      = $rowCodigo['usuario_codigo'] ?? 'COT';
                mysqli_stmt_close($stmtCodigo);
            }

            // Contar cotizaciones finalizadas de este usuario en el mes actual
            $mes  = date('Y-m');
            $like = $codigo . '%';
            $stmtCnt = mysqli_prepare($this->db,
                "SELECT COUNT(*) AS total FROM cotizaciones
                 WHERE usuario_codigo = ?
                   AND estado = 'finalizada'
                   AND DATE_FORMAT(fecha_creacion, '%Y-%m') = ?
                 FOR UPDATE");
            mysqli_stmt_bind_param($stmtCnt, 'ss', $codigo, $mes);
            mysqli_stmt_execute($stmtCnt);
            $resCnt = mysqli_stmt_get_result($stmtCnt);
            $cnt    = (int)mysqli_fetch_assoc($resCnt)['total'];
            mysqli_stmt_close($stmtCnt);

            $numeroCotizacion = trim($codigo) . ' ' . str_pad($cnt + 1, 2, '0', STR_PAD_LEFT);

            // Calcular fecha de validez
            $fechaValidez = date('Y-m-d', strtotime($fechaCreacion . " + $diasValidez days"));

            $stmtUpd = mysqli_prepare($this->db,
                "UPDATE cotizaciones
                 SET numero_cotizacion=?, estado='finalizada',
                     fecha_creacion=?, dias_validez=?, fecha_validez=?,
                     condiciones_pago=?, observaciones=?,
                     cliente_nombre=?, cliente_nit=?, cliente_direccion=?,
                     cliente_telefono=?, cliente_correo=?, cliente_contacto=?,
                     cliente_ciudad=?, cliente_id=?,
                     asesor_nombre=?, asesor_cargo=?, usuario_codigo=?
                 WHERE id=?");
            mysqli_stmt_bind_param($stmtUpd, 'ssissssssssssisssi',
                $numeroCotizacion, $fechaCreacion, $diasValidez, $fechaValidez,
                $condicionesPago, $observaciones,
                $clienteNombre, $clienteNit, $clienteDireccion,
                $clienteTelefono, $clienteCorreo, $clienteContacto,
                $clienteCiudad, $clienteId,
                $asesorNombre, $asesorCargo, $codigo,
                $id);
            mysqli_stmt_execute($stmtUpd);
            mysqli_stmt_close($stmtUpd);

            mysqli_commit($this->db);
            return $numeroCotizacion;
        } catch (\Exception $e) {
            mysqli_rollback($this->db);
            throw $e;
        }
    }

    // ── Búsqueda con filtros ──────────────────────────────────────────────────

    public function buscarConFiltros(array $filtros, int $offset, int $limite, int $usuarioId = 0, string $rol = 'usuario'): array
    {
        [$where, $params, $types] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql    = 'SELECT c.*, u.nombre AS nombre_usuario FROM cotizaciones c
                   LEFT JOIN usuarios u ON c.usuario_id = u.id'
                . ($where ? " WHERE $where" : '')
                . ' ORDER BY c.id DESC LIMIT ? OFFSET ?';
        $types .= 'ii';
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

    public function contarConFiltros(array $filtros, int $usuarioId = 0, string $rol = 'usuario'): int
    {
        [$where, $params, $types] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql  = 'SELECT COUNT(*) AS total FROM cotizaciones c' . ($where ? " WHERE $where" : '');
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

    private function construirWhere(array $filtros, int $usuarioId = 0, string $rol = 'usuario'): array
    {
        $condiciones = ["c.estado = 'finalizada'"];
        $params      = [];
        $types       = '';

        // Los usuarios solo ven sus propias cotizaciones
        if ($rol !== 'admin' && $usuarioId > 0) {
            $condiciones[] = 'c.usuario_id = ?';
            $params[]      = $usuarioId;
            $types        .= 'i';
        }

        if (!empty($filtros['fecha'])) {
            $condiciones[] = 'DATE(c.fecha_creacion) = ?';
            $params[]      = $filtros['fecha'];
            $types        .= 's';
        }
        if (!empty($filtros['nombre_cliente'])) {
            $condiciones[] = 'c.cliente_nombre LIKE ?';
            $params[]      = '%' . $filtros['nombre_cliente'] . '%';
            $types        .= 's';
        }
        if (!empty($filtros['numero_cotizacion'])) {
            $condiciones[] = 'c.numero_cotizacion LIKE ?';
            $params[]      = '%' . $filtros['numero_cotizacion'] . '%';
            $types        .= 's';
        }

        return [
            implode(' AND ', $condiciones),
            $params,
            $types,
        ];
    }

    // ── Ítems ─────────────────────────────────────────────────────────────────

    public function obtenerItems(int $cotizacionId): array
    {
        $stmt = mysqli_prepare($this->db,
            'SELECT * FROM cotizacion_items WHERE cotizacion_id = ? ORDER BY id ASC');
        mysqli_stmt_bind_param($stmt, 'i', $cotizacionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function buscarItemPorId(int $itemId, int $cotizacionId): ?array
    {
        $stmt = mysqli_prepare($this->db,
            'SELECT * FROM cotizacion_items WHERE id = ? AND cotizacion_id = ?');
        mysqli_stmt_bind_param($stmt, 'ii', $itemId, $cotizacionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function insertarItem(int $cotizacionId, ?int $productoId, string $titulo, string $foto,
                                 string $descripcion, int $cantidad, float $precio,
                                 string $iva, float $porcentajeIva, string $tiempoEntrega,
                                 string $categoria = '', string $codigoProducto = '',
                                 float $precioProveedor = 0, float $porcentajeUtilidad = 0,
                                 float $flete = 0, float $calibracion = 0,
                                 float $estampillas = 0, string $proveedor = '',
                                 string $codigoProveedor = ''): bool
    {
        $stmt = mysqli_prepare($this->db,
            'INSERT INTO cotizacion_items
             (cotizacion_id, producto_id, titulo, foto, descripcion, cantidad, precio, iva, porcentaje_iva, tiempo_entrega,
              categoria, codigo_producto, precio_proveedor, porcentaje_utilidad, flete, calibracion, estampillas, proveedor, codigo_proveedor)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'iisssidsdsssddddss',
            $cotizacionId, $productoId, $titulo, $foto, $descripcion,
            $cantidad, $precio, $iva, $porcentajeIva, $tiempoEntrega,
            $categoria, $codigoProducto, $precioProveedor, $porcentajeUtilidad,
            $flete, $calibracion, $estampillas, $proveedor, $codigoProveedor);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function actualizarItem(int $itemId, int $cotizacionId, string $titulo, string $foto,
                                   string $descripcion, int $cantidad, float $precio,
                                   string $iva, float $porcentajeIva, string $tiempoEntrega,
                                   string $categoria = '', string $codigoProducto = '',
                                   float $precioProveedor = 0, float $porcentajeUtilidad = 0,
                                   float $flete = 0, float $calibracion = 0,
                                   float $estampillas = 0, string $proveedor = '',
                                   string $codigoProveedor = ''): bool
    {
        $stmt = mysqli_prepare($this->db,
            'UPDATE cotizacion_items
             SET titulo=?,foto=?,descripcion=?,cantidad=?,precio=?,iva=?,porcentaje_iva=?,tiempo_entrega=?,
                 categoria=?,codigo_producto=?,precio_proveedor=?,porcentaje_utilidad=?,flete=?,calibracion=?,estampillas=?,proveedor=?,codigo_proveedor=?
             WHERE id=? AND cotizacion_id=?');
        mysqli_stmt_bind_param($stmt, 'sssidsdsssddddssii',
            $titulo, $foto, $descripcion, $cantidad, $precio, $iva, $porcentajeIva, $tiempoEntrega,
            $categoria, $codigoProducto, $precioProveedor, $porcentajeUtilidad,
            $flete, $calibracion, $estampillas, $proveedor, $codigoProveedor,
            $itemId, $cotizacionId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function eliminarItem(int $itemId): bool
    {
        $stmt = mysqli_prepare($this->db, 'DELETE FROM cotizacion_items WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $itemId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}
