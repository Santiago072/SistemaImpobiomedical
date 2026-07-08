<?php
/**
 * ProductoModel — acceso a datos del catálogo de productos.
 *
 * - SRP: toda la lógica SQL de productos vive aquí.
 * - Campos: titulo, foto, descripcion, precio, iva, porcentaje_iva, estado
 */
class ProductoModel
{
    private \mysqli $db;

    public function __construct(\mysqli $conexion)
    {
        $this->db = $conexion;
    }

    public function listar(int $offset, int $limite, string $busqueda = ''): array
    {
        if ($busqueda !== '') {
            $param = "%$busqueda%";
            $stmt  = mysqli_prepare($this->db,
                "SELECT * FROM productos WHERE titulo LIKE ?
                 ORDER BY titulo LIMIT ? OFFSET ?");
            mysqli_stmt_bind_param($stmt, 'sii', $param, $limite, $offset);
        } else {
            $stmt = mysqli_prepare($this->db,
                "SELECT * FROM productos ORDER BY titulo LIMIT ? OFFSET ?");
            mysqli_stmt_bind_param($stmt, 'ii', $limite, $offset);
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

    public function contar(string $busqueda = ''): int
    {
        if ($busqueda !== '') {
            $param = "%$busqueda%";
            $stmt  = mysqli_prepare($this->db,
                "SELECT COUNT(*) AS total FROM productos WHERE titulo LIKE ?");
            mysqli_stmt_bind_param($stmt, 's', $param);
        } else {
            $stmt = mysqli_prepare($this->db,
                "SELECT COUNT(*) AS total FROM productos");
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return (int)($row['total'] ?? 0);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT * FROM productos WHERE id = ? AND estado='activo'");
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
                "SELECT id, titulo, foto, descripcion, precio, iva, porcentaje_iva
                 FROM productos WHERE estado='activo' AND titulo LIKE ?
                 ORDER BY titulo LIMIT 50");
            mysqli_stmt_bind_param($stmt, 's', $param);
        } else {
            $stmt = mysqli_prepare($this->db,
                "SELECT id, titulo, foto, descripcion, precio, iva, porcentaje_iva
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

    public function crear(string $titulo, string $foto, string $descripcion,
                          float $precio, string $iva, float $porcentaje_iva): bool
    {
        $stmt = mysqli_prepare($this->db,
            'INSERT INTO productos (titulo, foto, descripcion, precio, iva, porcentaje_iva)
             VALUES (?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'sssdsd',
            $titulo, $foto, $descripcion, $precio, $iva, $porcentaje_iva);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function actualizar(int $id, string $titulo, string $foto, string $descripcion,
                               float $precio, string $iva, float $porcentaje_iva,
                               string $estado): bool
    {
        $stmt = mysqli_prepare($this->db,
            'UPDATE productos SET titulo=?,foto=?,descripcion=?,precio=?,iva=?,porcentaje_iva=?,estado=?
             WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssdsdsi',
            $titulo, $foto, $descripcion, $precio, $iva, $porcentaje_iva, $estado, $id);
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
