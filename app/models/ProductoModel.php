<?php
/**
 * ProductoModel — acceso a datos del catálogo de productos.
 *
 * - SRP: toda la lógica SQL de productos vive aquí.
 * - ISP: implementa RepositoryInterface (contrato estricto).
 * - Campos: titulo, foto, descripcion, precio, iva, porcentaje_iva, estado
 */
class ProductoModel implements RepositoryInterface
{
    private \mysqli $db;

    public function __construct(\mysqli $conexion)
    {
        $this->db = $conexion;
    }

    public function listar(int $offset, int $limite, string $busqueda = '', string $categoria = ''): array
    {
        $where = [];
        $params = [];
        $types = '';

        if ($busqueda !== '') {
            $where[] = "titulo LIKE ?";
            $params[] = "%$busqueda%";
            $types .= 's';
        }

        if ($categoria !== '') {
            $where[] = "categoria = ?";
            $params[] = $categoria;
            $types .= 's';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT * FROM productos $whereClause ORDER BY titulo LIMIT ? OFFSET ?";
        
        $params[] = $limite;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = mysqli_prepare($this->db, $sql);
        if (!empty($params)) {
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

    public function contar(string $busqueda = '', string $categoria = ''): int
    {
        $where = [];
        $params = [];
        $types = '';

        if ($busqueda !== '') {
            $where[] = "titulo LIKE ?";
            $params[] = "%$busqueda%";
            $types .= 's';
        }

        if ($categoria !== '') {
            $where[] = "categoria = ?";
            $params[] = $categoria;
            $types .= 's';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) AS total FROM productos $whereClause";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return (int)($row['total'] ?? 0);
    }

    public function obtenerConteosPorCategoria(): array
    {
        $stmt = mysqli_prepare($this->db, "SELECT categoria, COUNT(*) as cantidad FROM productos WHERE categoria IS NOT NULL AND categoria != '' GROUP BY categoria ORDER BY cantidad DESC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT * FROM productos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function listarTodos(string $busqueda = ''): array
    {
        if ($busqueda !== '') {
            $param = "%$busqueda%";
            $stmt  = mysqli_prepare($this->db,
                "SELECT id, titulo, foto, descripcion, iva, porcentaje_iva, categoria, codigo_producto, codigo_proveedor
                 FROM productos WHERE estado='activo' AND titulo LIKE ?
                 ORDER BY titulo LIMIT 50");
            mysqli_stmt_bind_param($stmt, 's', $param);
        } else {
            $stmt = mysqli_prepare($this->db,
                "SELECT id, titulo, foto, descripcion, iva, porcentaje_iva, categoria, codigo_producto, codigo_proveedor
                 FROM productos WHERE estado='activo' ORDER BY titulo LIMIT 50");
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

    public function existePorTitulo(string $titulo): bool
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT id FROM productos WHERE titulo = ? AND estado='activo' LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $titulo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existe = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    /** Buscar producto por título (sin restricción de estado, para poder actualizarlo) */
    public function buscarPorTitulo(string $titulo): ?array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT * FROM productos WHERE titulo = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $titulo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function crear(string $titulo, string $foto, string $descripcion,
                          string $iva, float $porcentaje_iva,
                          string $categoria = null, string $codigo_producto = null): bool
    {
        $stmt = mysqli_prepare($this->db,
            'INSERT INTO productos (titulo, foto, descripcion, iva, porcentaje_iva, categoria, codigo_producto)
             VALUES (?,?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'ssssdss',
            $titulo, $foto, $descripcion, $iva, $porcentaje_iva, $categoria, $codigo_producto);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function actualizar(int $id, string $titulo, string $foto, string $descripcion,
                               string $iva, float $porcentaje_iva,
                               string $estado, string $categoria = null, string $codigo_producto = null): bool
    {
        $stmt = mysqli_prepare($this->db,
            'UPDATE productos SET titulo=?,foto=?,descripcion=?,iva=?,porcentaje_iva=?,estado=?,categoria=?,codigo_producto=?
             WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssssdsssi',
            $titulo, $foto, $descripcion, $iva, $porcentaje_iva, $estado, $categoria, $codigo_producto, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function eliminar(int $id): bool
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM productos WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}
