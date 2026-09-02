<?php
/**
 * CotizacionModel — acceso a datos de cotizaciones y sus ítems (migrado a PDO).
 *
 * Principios aplicados:
 *   - SRP: toda la lógica de acceso a datos vive aquí.
 *   - Número de cotización: codigo_usuario + consecutivo mensual (Ej: EB01, EB02).
 *     Cada mes el consecutivo se reinicia. Se usa transacción para evitar colisiones concurrentes.
 */
class CotizacionModel
{
    private \PDO $db;

    public function __construct(\PDO $conexion)
    {
        $this->db = $conexion;
    }

    // ── Contadores para Dashboard ─────────────────────────────────────────────

    public function contarDelUsuario(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM cotizaciones WHERE usuario_id = :uid AND estado = 'finalizada'"
        );
        $stmt->execute([':uid' => $usuarioId]);
        return (int)$stmt->fetchColumn();
    }

    public function contarMesDelUsuario(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM cotizaciones
             WHERE usuario_id = :uid AND estado = 'finalizada'
             AND MONTH(fecha_creacion)=MONTH(CURDATE())
             AND YEAR(fecha_creacion)=YEAR(CURDATE())"
        );
        $stmt->execute([':uid' => $usuarioId]);
        return (int)$stmt->fetchColumn();
    }

    public function getMetricasDashboard(int $usuarioId, string $rol): array
    {
        // Cotizaciones creadas en los últimos 6 meses (agrupadas por mes)
        // Nota: Filtramos por usuario_id como parámetro (seguro), el rol decide el filtro
        $query = "SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') AS mes, COUNT(*) AS total
                  FROM cotizaciones
                  WHERE estado = 'finalizada'
                  AND fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)"
               . ($rol !== 'admin' ? " AND usuario_id = :uid" : "")
               . " GROUP BY mes ORDER BY mes ASC";

        $stmt = $this->db->prepare($query);
        if ($rol !== 'admin') {
            $stmt->bindValue(':uid', $usuarioId, \PDO::PARAM_INT);
        }
        $stmt->execute();

        $meses = [];
        $totales = [];
        foreach ($stmt->fetchAll() as $row) {
            $meses[]   = $row['mes'];
            $totales[] = (int)$row['total'];
        }

        return ['meses' => $meses, 'totales' => $totales];
    }

    public function contarTotal(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM cotizaciones WHERE estado = 'finalizada'");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function contarDelMes(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM cotizaciones
             WHERE estado='finalizada' AND MONTH(fecha_creacion)=MONTH(CURDATE())
             AND YEAR(fecha_creacion)=YEAR(CURDATE())"
        );
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function contarTotalClientes(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM clientes WHERE estado='activo'");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function contarTotalProductos(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM productos WHERE estado='activo'");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // ── Cabecera ──────────────────────────────────────────────────────────────

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cotizaciones WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorNumero(string $numero): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cotizaciones WHERE numero_cotizacion = :num ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([':num' => $numero]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Borrador con ítems, sin número, del usuario (excluye clones de modificación) */
    public function buscarBorradorConItems(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT c.id FROM cotizaciones c
             INNER JOIN cotizacion_items i ON c.id = i.cotizacion_id
             WHERE c.usuario_id = :uid
               AND (c.numero_cotizacion IS NULL OR c.numero_cotizacion = '')
               AND c.estado = 'borrador'
               AND c.es_revision = 0
             GROUP BY c.id
             ORDER BY MAX(i.id) DESC, c.id DESC
             LIMIT 1"
        );
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    /** Marca un borrador como clon temporal de modificación (es_revision = 1) */
    public function marcarComoRevision(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE cotizaciones SET es_revision = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function buscarCabeceraVacia(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM cotizaciones
             WHERE usuario_id = :uid
               AND (numero_cotizacion IS NULL OR numero_cotizacion = '')
               AND estado = 'borrador'
               AND es_revision = 0
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    public function crearCabecera(int $usuarioId, string $usuarioCodigo, string $asesorNombre, string $asesorCargo): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cotizaciones (usuario_id, usuario_codigo, asesor_nombre, asesor_cargo) VALUES (:uid, :cod, :nom, :cargo)'
        );
        $stmt->execute([
            ':uid'   => $usuarioId,
            ':cod'   => $usuarioCodigo,
            ':nom'   => $asesorNombre,
            ':cargo' => $asesorCargo,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function clonarDatosCabecera(int $oldId, int $newId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE cotizaciones c_new
             JOIN cotizaciones c_old ON c_old.id = :old_id
             SET c_new.cliente_id       = c_old.cliente_id,
                 c_new.cliente_nombre   = c_old.cliente_nombre,
                 c_new.cliente_nit      = c_old.cliente_nit,
                 c_new.cliente_direccion= c_old.cliente_direccion,
                 c_new.cliente_telefono = c_old.cliente_telefono,
                 c_new.cliente_correo   = c_old.cliente_correo,
                 c_new.cliente_contacto = c_old.cliente_contacto,
                 c_new.cliente_ciudad   = c_old.cliente_ciudad,
                 c_new.dias_validez     = c_old.dias_validez,
                 c_new.condiciones_pago = c_old.condiciones_pago,
                 c_new.observaciones    = c_old.observaciones
             WHERE c_new.id = :new_id"
        );
        if (!$stmt->execute([':old_id' => $oldId, ':new_id' => $newId])) {
            error_log('Error execute clonarDatosCabecera');
        }
    }

    public function clonarItems(int $oldId, int $newId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cotizacion_items
             (cotizacion_id, producto_id, titulo, foto, descripcion, cantidad, precio,
              iva, porcentaje_iva, tiempo_entrega, categoria, codigo_producto,
              precio_proveedor, porcentaje_utilidad, flete, calibracion, estampillas,
              proveedor, codigo_proveedor, calc_ops)
             SELECT :new_id, producto_id, titulo, foto, descripcion, cantidad, precio,
                    iva, porcentaje_iva, tiempo_entrega, categoria, codigo_producto,
                    precio_proveedor, porcentaje_utilidad, flete, calibracion, estampillas,
                    proveedor, codigo_proveedor, calc_ops
             FROM cotizacion_items WHERE cotizacion_id = :old_id"
        );
        if (!$stmt->execute([':new_id' => $newId, ':old_id' => $oldId])) {
            error_log('Error execute clonarItems');
        }
    }

    /**
     * Genera el número de cotización: CODIGO_USUARIO + consecutivo mensual de 2 dígitos.
     * Ejemplo: EB01, EB02, ..., EB99
     * Usa transacción PDO para evitar colisiones concurrentes.
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
        string $usuarioCodigo = '',
        ?string $revisionDe = null
    ): string {
        $this->db->beginTransaction();
        try {
            // Utilizar el código del usuario actual si se proporcionó, si no, buscar en la bd
            if (!empty($usuarioCodigo)) {
                $codigo = $usuarioCodigo;
            } else {
                $stmtCodigo = $this->db->prepare(
                    'SELECT usuario_codigo FROM cotizaciones WHERE id = :id FOR UPDATE'
                );
                $stmtCodigo->execute([':id' => $id]);
                $rowCodigo = $stmtCodigo->fetch();
                $codigo    = $rowCodigo['usuario_codigo'] ?? 'COT';
            }

            if (!empty($revisionDe)) {
                $likeBase = $revisionDe . '_%';
                $stmtRev  = $this->db->prepare(
                    "SELECT numero_cotizacion FROM cotizaciones
                     WHERE numero_cotizacion LIKE :base
                     ORDER BY CHAR_LENGTH(numero_cotizacion) DESC, numero_cotizacion DESC
                     LIMIT 1 FOR UPDATE"
                );
                $stmtRev->execute([':base' => $likeBase]);
                $rowRev = $stmtRev->fetch();

                if ($rowRev && preg_match('/\_(\d+)$/', $rowRev['numero_cotizacion'], $matches)) {
                    $nextSuffix = (int)$matches[1] + 1;
                } else {
                    $nextSuffix = 1;
                }
                $numeroCotizacion = $revisionDe . '_' . str_pad($nextSuffix, 2, '0', STR_PAD_LEFT);
            } else {
                // Determinar el mes objetivo basado en la fecha de creación de la cotización
                $mesObj = !empty($fechaCreacion) ? date('Y-m', strtotime($fechaCreacion)) : date('Y-m');
                $prefix = trim($codigo) . ' ';

                // Buscar todos los números finalizados del usuario en este mes para extraer el máximo secuencial
                $stmtMax = $this->db->prepare(
                    "SELECT numero_cotizacion FROM cotizaciones
                     WHERE usuario_codigo = :cod
                       AND estado = 'finalizada'
                       AND DATE_FORMAT(fecha_creacion, '%Y-%m') = :mes
                       AND numero_cotizacion NOT LIKE '%\_%'
                     FOR UPDATE"
                );
                $stmtMax->execute([':cod' => $codigo, ':mes' => $mesObj]);
                $rows = $stmtMax->fetchAll();

                $maxNum = 0;
                foreach ($rows as $r) {
                    $numStr = trim($r['numero_cotizacion'] ?? '');
                    // Extraer los dígitos finales después del prefijo (ej: "EB-HM 01" -> 1, "EB-HM 02" -> 2)
                    if (preg_match('/(?:^|\s)(\d+)$/', $numStr, $m)) {
                        $val = (int)$m[1];
                        if ($val > $maxNum) {
                            $maxNum = $val;
                        }
                    }
                }

                $nextNum = $maxNum + 1;
                $numeroCotizacion = trim($codigo) . ' ' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
            }

            // Calcular fecha de validez
            $fechaValidez = date('Y-m-d', strtotime($fechaCreacion . " + $diasValidez days"));

            $stmtUpd = $this->db->prepare(
                "UPDATE cotizaciones
                 SET numero_cotizacion=:num, estado='finalizada',
                     fecha_creacion=:fech, dias_validez=:dval, fecha_validez=:fval,
                     condiciones_pago=:condpago, observaciones=:obs,
                     cliente_nombre=:cnombre, cliente_nit=:cnit, cliente_direccion=:cdir,
                     cliente_telefono=:ctel, cliente_correo=:ccorreo, cliente_contacto=:ccont,
                     cliente_ciudad=:ccity, cliente_id=:cid,
                     asesor_nombre=:anom, asesor_cargo=:acargo, usuario_codigo=:ucod
                 WHERE id=:id"
            );
            $stmtUpd->execute([
                ':num'     => $numeroCotizacion,
                ':fech'    => $fechaCreacion,
                ':dval'    => $diasValidez,
                ':fval'    => $fechaValidez,
                ':condpago'=> $condicionesPago,
                ':obs'     => $observaciones,
                ':cnombre' => $clienteNombre,
                ':cnit'    => $clienteNit,
                ':cdir'    => $clienteDireccion,
                ':ctel'    => $clienteTelefono,
                ':ccorreo' => $clienteCorreo,
                ':ccont'   => $clienteContacto,
                ':ccity'   => $clienteCiudad,
                ':cid'     => $clienteId,
                ':anom'    => $asesorNombre,
                ':acargo'  => $asesorCargo,
                ':ucod'    => $codigo,
                ':id'      => $id,
            ]);

            $this->db->commit();
            return $numeroCotizacion;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Búsqueda con filtros ──────────────────────────────────────────────────

    public function buscarConFiltros(array $filtros, int $offset, int $limite, int $usuarioId = 0, string $rol = 'usuario'): array
    {
        [$where, $params] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql = 'SELECT c.*, u.nombre AS nombre_usuario FROM cotizaciones c
                LEFT JOIN usuarios u ON c.usuario_id = u.id'
             . ($where ? " WHERE $where" : '')
             . ' ORDER BY c.id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarConFiltros(array $filtros, int $usuarioId = 0, string $rol = 'usuario'): int
    {
        [$where, $params] = $this->construirWhere($filtros, $usuarioId, $rol);
        $sql  = 'SELECT COUNT(*) AS total FROM cotizaciones c' . ($where ? " WHERE $where" : '');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function construirWhere(array $filtros, int $usuarioId = 0, string $rol = 'usuario'): array
    {
        $condiciones = ["c.estado = 'finalizada'"];
        $params      = [];

        if ($rol !== 'admin' && $usuarioId > 0) {
            $condiciones[] = 'c.usuario_id = :uid';
            $params[':uid'] = $usuarioId;
        }
        // Rango de fechas
        if (!empty($filtros['fecha_desde'])) {
            $condiciones[] = 'DATE(c.fecha_creacion) >= :fecha_desde';
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = 'DATE(c.fecha_creacion) <= :fecha_hasta';
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        // Compatibilidad con filtro de fecha única
        if (empty($filtros['fecha_desde']) && empty($filtros['fecha_hasta']) && !empty($filtros['fecha'])) {
            $condiciones[] = 'DATE(c.fecha_creacion) = :fecha';
            $params[':fecha'] = $filtros['fecha'];
        }
        if (!empty($filtros['nombre_cliente'])) {
            $condiciones[] = 'c.cliente_nombre LIKE :nombre';
            $params[':nombre'] = '%' . $filtros['nombre_cliente'] . '%';
        }
        if (!empty($filtros['numero_cotizacion'])) {
            $condiciones[] = 'c.numero_cotizacion LIKE :ncot';
            $params[':ncot'] = '%' . $filtros['numero_cotizacion'] . '%';
        }
        if (!empty($filtros['estado_comercial'])) {
            $condiciones[] = 'c.estado_comercial = :est_com';
            $params[':est_com'] = $filtros['estado_comercial'];
        }

        return [implode(' AND ', $condiciones), $params];
    }

    /**
     * Actualiza el estado comercial de una cotización (pendiente, concluida, descartada).
     */
    public function actualizarEstadoComercial(int $id, string $nuevoEstado): bool
    {
        $estadosPermitidos = ['pendiente', 'concluida', 'descartada'];
        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE cotizaciones SET estado_comercial = :estado WHERE id = :id AND estado = 'finalizada'"
        );
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id'     => $id
        ]);
    }

    // ── Ítems ─────────────────────────────────────────────────────────────────

    public function obtenerItems(int $cotizacionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cotizacion_items WHERE cotizacion_id = :cid ORDER BY id ASC'
        );
        $stmt->execute([':cid' => $cotizacionId]);
        return $stmt->fetchAll();
    }

    public function buscarItemPorId(int $itemId, int $cotizacionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cotizacion_items WHERE id = :iid AND cotizacion_id = :cid'
        );
        $stmt->execute([':iid' => $itemId, ':cid' => $cotizacionId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insertarItem(int $cotizacionId, ?int $productoId, string $titulo, string $foto,
                                 string $descripcion, int $cantidad, float $precio,
                                 string $iva, float $porcentajeIva, string $tiempoEntrega,
                                 string $categoria = '', string $codigoProducto = '',
                                 float $precioProveedor = 0, float $porcentajeUtilidad = 0,
                                 float $flete = 0, float $calibracion = 0,
                                 float $estampillas = 0, string $proveedor = '',
                                 string $codigoProveedor = '', string $calcOps = '{}'): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cotizacion_items
             (cotizacion_id, producto_id, titulo, foto, descripcion, cantidad, precio, iva, porcentaje_iva, tiempo_entrega,
              categoria, codigo_producto, precio_proveedor, porcentaje_utilidad, flete, calibracion, estampillas, proveedor, codigo_proveedor, calc_ops)
             VALUES (:cid, :pid, :tit, :foto, :desc, :cant, :prec, :iva, :porciva, :tient,
                     :cat, :codprod, :precprov, :porcutil, :flet, :calib, :estamp, :prov, :codprov, :calc)'
        );
        $ok = $stmt->execute([
            ':cid'      => $cotizacionId,
            ':pid'      => $productoId,
            ':tit'      => $titulo,
            ':foto'     => $foto,
            ':desc'     => $descripcion,
            ':cant'     => $cantidad,
            ':prec'     => $precio,
            ':iva'      => $iva,
            ':porciva'  => $porcentajeIva,
            ':tient'    => $tiempoEntrega,
            ':cat'      => $categoria,
            ':codprod'  => $codigoProducto,
            ':precprov' => $precioProveedor,
            ':porcutil' => $porcentajeUtilidad,
            ':flet'     => $flete,
            ':calib'    => $calibracion,
            ':estamp'   => $estampillas,
            ':prov'     => $proveedor,
            ':codprov'  => $codigoProveedor,
            ':calc'     => $calcOps,
        ]);
        if (!$ok) {
            error_log('insertarItem PDO error: ' . implode(' | ', $stmt->errorInfo()));
        }
        return $ok;
    }

    public function actualizarItem(int $itemId, int $cotizacionId, string $titulo, string $foto,
                                   string $descripcion, int $cantidad, float $precio,
                                   string $iva, float $porcentajeIva, string $tiempoEntrega,
                                   string $categoria = '', string $codigoProducto = '',
                                   float $precioProveedor = 0, float $porcentajeUtilidad = 0,
                                   float $flete = 0, float $calibracion = 0,
                                   float $estampillas = 0, string $proveedor = '',
                                   string $codigoProveedor = '', string $calcOps = '{}'): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE cotizacion_items
             SET titulo=:tit, foto=:foto, descripcion=:desc, cantidad=:cant, precio=:prec,
                 iva=:iva, porcentaje_iva=:porciva, tiempo_entrega=:tient,
                 categoria=:cat, codigo_producto=:codprod,
                 precio_proveedor=:precprov, porcentaje_utilidad=:porcutil,
                 flete=:flet, calibracion=:calib, estampillas=:estamp,
                 proveedor=:prov, codigo_proveedor=:codprov, calc_ops=:calc
             WHERE id=:iid AND cotizacion_id=:cid'
        );
        return $stmt->execute([
            ':tit'      => $titulo,
            ':foto'     => $foto,
            ':desc'     => $descripcion,
            ':cant'     => $cantidad,
            ':prec'     => $precio,
            ':iva'      => $iva,
            ':porciva'  => $porcentajeIva,
            ':tient'    => $tiempoEntrega,
            ':cat'      => $categoria,
            ':codprod'  => $codigoProducto,
            ':precprov' => $precioProveedor,
            ':porcutil' => $porcentajeUtilidad,
            ':flet'     => $flete,
            ':calib'    => $calibracion,
            ':estamp'   => $estampillas,
            ':prov'     => $proveedor,
            ':codprov'  => $codigoProveedor,
            ':calc'     => $calcOps,
            ':iid'      => $itemId,
            ':cid'      => $cotizacionId,
        ]);
    }

    public function eliminarItem(int $itemId, int $cotizacionId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cotizacion_items WHERE id = :id AND cotizacion_id = :cid');
        $stmt->execute([':id' => $itemId, ':cid' => $cotizacionId]);
        return $stmt->rowCount() > 0;
    }

    // ── ELIMINAR ───────────────────────────────────────────────────────────────

    public function eliminar(int $id): bool
    {
        $stmtItems = $this->db->prepare('DELETE FROM cotizacion_items WHERE cotizacion_id = :cid');
        $stmtItems->execute([':cid' => $id]);

        $stmt = $this->db->prepare('DELETE FROM cotizaciones WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Elimina todos los borradores (y sus ítems) que pertenezcan al usuario especificado.
     */
    public function eliminarTodosBorradoresDelUsuario(int $usuarioId): bool
    {
        // 1. Eliminar ítems de todos los borradores del usuario
        $stmtItems = $this->db->prepare(
            "DELETE i FROM cotizacion_items i
             INNER JOIN cotizaciones c ON i.cotizacion_id = c.id
             WHERE c.usuario_id = :uid AND c.estado = 'borrador'"
        );
        $stmtItems->execute([':uid' => $usuarioId]);

        // 2. Eliminar las cabeceras en borrador del usuario
        $stmtCot = $this->db->prepare(
            "DELETE FROM cotizaciones WHERE usuario_id = :uid AND estado = 'borrador'"
        );
        return $stmtCot->execute([':uid' => $usuarioId]);
    }
}
