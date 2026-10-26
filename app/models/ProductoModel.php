<?php
/**
 * ProductoModel — acceso a datos del catálogo de productos (migrado a PDO).
 *
 * - SRP: toda la lógica SQL de productos vive aquí.
 * - ISP: implementa RepositoryInterface (contrato estricto).
 * - Campos: titulo, foto, descripcion, precio, iva, porcentaje_iva, estado
 */
class ProductoModel implements RepositoryInterface
{
    private \PDO $db;

    public function __construct(\PDO $conexion)
    {
        $this->db = $conexion;
    }

    public function listar(int $offset, int $limite, string $busqueda = '', string $categoria = ''): array
    {
        $where  = [];
        $params = [];

        if ($busqueda !== '') {
            $where[] = "(titulo LIKE :busq_tit OR codigo_producto LIKE :busq_cod OR marca LIKE :busq_mar OR modelo LIKE :busq_mod OR categoria LIKE :busq_cat OR descripcion LIKE :busq_desc)";
            $params[':busq_tit']  = "%$busqueda%";
            $params[':busq_cod']  = "%$busqueda%";
            $params[':busq_mar']  = "%$busqueda%";
            $params[':busq_mod']  = "%$busqueda%";
            $params[':busq_cat']  = "%$busqueda%";
            $params[':busq_desc'] = "%$busqueda%";
        }
        if ($categoria !== '') {
            $where[]         = "categoria = :categoria";
            $params[':categoria'] = $categoria;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT productos.*, (SELECT COUNT(*) FROM cotizacion_items WHERE cotizacion_items.producto_id = productos.id) AS total_cotizaciones FROM productos $whereClause ORDER BY productos.titulo LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listarParaExportar(string $busqueda = '', string $categoria = '', array $ids = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($ids)) {
            $placeholders = [];
            foreach ($ids as $idx => $idVal) {
                $pName = ':id_' . $idx;
                $placeholders[] = $pName;
                $params[$pName] = (int)$idVal;
            }
            $where[] = "id IN (" . implode(',', $placeholders) . ")";
        } else {
            if ($busqueda !== '') {
                $where[] = "(titulo LIKE :busq_tit OR codigo_producto LIKE :busq_cod OR marca LIKE :busq_mar OR modelo LIKE :busq_mod OR categoria LIKE :busq_cat OR descripcion LIKE :busq_desc)";
                $params[':busq_tit']  = "%$busqueda%";
                $params[':busq_cod']  = "%$busqueda%";
                $params[':busq_mar']  = "%$busqueda%";
                $params[':busq_mod']  = "%$busqueda%";
                $params[':busq_cat']  = "%$busqueda%";
                $params[':busq_desc'] = "%$busqueda%";
            }
            if ($categoria !== '') {
                $where[]              = "categoria = :categoria";
                $params[':categoria'] = $categoria;
            }
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT * FROM productos $whereClause ORDER BY categoria ASC, titulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contar(string $busqueda = '', string $categoria = ''): int
    {
        $where  = [];
        $params = [];

        if ($busqueda !== '') {
            $where[] = "(titulo LIKE :busq_tit OR codigo_producto LIKE :busq_cod OR marca LIKE :busq_mar OR modelo LIKE :busq_mod OR categoria LIKE :busq_cat OR descripcion LIKE :busq_desc)";
            $params[':busq_tit']  = "%$busqueda%";
            $params[':busq_cod']  = "%$busqueda%";
            $params[':busq_mar']  = "%$busqueda%";
            $params[':busq_mod']  = "%$busqueda%";
            $params[':busq_cat']  = "%$busqueda%";
            $params[':busq_desc'] = "%$busqueda%";
        }
        if ($categoria !== '') {
            $where[]              = "categoria = :categoria";
            $params[':categoria'] = $categoria;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) AS total FROM productos $whereClause";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function obtenerConteosPorCategoria(): array
    {
        $stmt = $this->db->prepare(
            "SELECT categoria, COUNT(*) as cantidad FROM productos
             WHERE categoria IS NOT NULL AND categoria != ''
             GROUP BY categoria ORDER BY cantidad DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listarTodos(string $busqueda = ''): array
    {
        if ($busqueda !== '') {
            $stmt = $this->db->prepare(
                "SELECT id, titulo, foto, descripcion, iva, porcentaje_iva, categoria, codigo_producto, marca, modelo, codigo_proveedor
                 FROM productos WHERE estado='activo' AND titulo LIKE :busqueda
                 ORDER BY titulo LIMIT 50"
            );
            $stmt->execute([':busqueda' => "%$busqueda%"]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT id, titulo, foto, descripcion, iva, porcentaje_iva, categoria, codigo_producto, marca, modelo, codigo_proveedor
                 FROM productos WHERE estado='activo' ORDER BY titulo LIMIT 50"
            );
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function existePorTitulo(string $titulo): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM productos WHERE titulo = :titulo AND estado='activo' LIMIT 1"
        );
        $stmt->execute([':titulo' => $titulo]);
        return (bool)$stmt->fetch();
    }

    /** Buscar producto por título (sin restricción de estado, para poder actualizarlo) */
    public function buscarPorTitulo(string $titulo): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE titulo = :titulo LIMIT 1");
        $stmt->execute([':titulo' => $titulo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crear(string $titulo, string $foto, string $descripcion,
                          string $iva, float $porcentaje_iva,
                          ?string $categoria = null, ?string $codigo_producto = null,
                          ?string $marca = null, ?string $modelo = null): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO productos (titulo, foto, descripcion, iva, porcentaje_iva, categoria, codigo_producto, marca, modelo)
             VALUES (:titulo, :foto, :desc, :iva, :porciva, :cat, :codprod, :marca, :modelo)'
        );
        return $stmt->execute([
            ':titulo'   => $titulo,
            ':foto'     => $foto,
            ':desc'     => $descripcion,
            ':iva'      => $iva,
            ':porciva'  => $porcentaje_iva,
            ':cat'      => $categoria,
            ':codprod'  => $codigo_producto,
            ':marca'    => $marca,
            ':modelo'   => $modelo,
        ]);
    }

    public function actualizar(int $id, string $titulo, string $foto, string $descripcion,
                               string $iva, float $porcentaje_iva,
                               string $estado, ?string $categoria = null, ?string $codigo_producto = null,
                               ?string $marca = null, ?string $modelo = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE productos SET titulo=:titulo, foto=:foto, descripcion=:desc, iva=:iva,
             porcentaje_iva=:porciva, estado=:estado, categoria=:cat, codigo_producto=:codprod,
             marca=:marca, modelo=:modelo
             WHERE id=:id'
        );
        return $stmt->execute([
            ':titulo'   => $titulo,
            ':foto'     => $foto,
            ':desc'     => $descripcion,
            ':iva'      => $iva,
            ':porciva'  => $porcentaje_iva,
            ':estado'   => $estado,
            ':cat'      => $categoria,
            ':codprod'  => $codigo_producto,
            ':marca'    => $marca,
            ':modelo'   => $modelo,
            ':id'       => $id,
        ]);
    }

    public function contarCotizacionesAsociadas(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM cotizacion_items WHERE producto_id = :id');
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $estadoValido = in_array($estado, ['activo', 'inactivo'], true) ? $estado : 'activo';
        $stmt = $this->db->prepare('UPDATE productos SET estado = :estado WHERE id = :id');
        return $stmt->execute([':estado' => $estadoValido, ':id' => $id]);
    }

    public function eliminarFisico(int $id): bool
    {
        $prod = $this->buscarPorId($id);
        if ($prod && !empty($prod['foto'])) {
            $stmtImg = $this->db->prepare('SELECT COUNT(*) FROM productos WHERE foto = :foto AND id != :id');
            $stmtImg->execute([':foto' => $prod['foto'], ':id' => $id]);
            if ((int)$stmtImg->fetchColumn() === 0) {
                $path = dirname(__DIR__, 2) . '/uploads/' . $prod['foto'];
                if (file_exists($path) && is_file($path)) {
                    @unlink($path);
                }
            }
        }
        $stmt = $this->db->prepare('DELETE FROM productos WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        // Por compatibilidad de interfaz, elimina cambiando estado a inactivo
        return $this->cambiarEstado($id, 'inactivo');
    }
}
