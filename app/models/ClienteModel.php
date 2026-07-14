<?php
/**
 * ClienteModel — acceso a datos de la tabla clientes.
 *
 * - SRP: toda la lógica SQL de clientes vive aquí.
 * - ISP: implementa RepositoryInterface (contrato estricto).
 * - Campos: nombre, nit, departamento, municipio, direccion, nombre_contacto, telefono, correo
 */
class ClienteModel implements RepositoryInterface
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
                "SELECT * FROM clientes WHERE (nombre LIKE ? OR nit LIKE ? OR municipio LIKE ?)
                 ORDER BY nombre LIMIT ? OFFSET ?");
            mysqli_stmt_bind_param($stmt, 'sssii', $param, $param, $param, $limite, $offset);
        } else {
            $stmt = mysqli_prepare($this->db,
                "SELECT * FROM clientes ORDER BY nombre LIMIT ? OFFSET ?");
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
                "SELECT COUNT(*) AS total FROM clientes WHERE 
                 (nombre LIKE ? OR nit LIKE ? OR municipio LIKE ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $param, $param, $param);
        } else {
            $stmt = mysqli_prepare($this->db,
                "SELECT COUNT(*) AS total FROM clientes");
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return (int)($row['total'] ?? 0);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = mysqli_prepare($this->db, 'SELECT * FROM clientes WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    /** Búsqueda rápida para autocompletado en cotizaciones */
    public function buscarParaSelect(string $busqueda, int $limite = 10): array
    {
        $param = "%$busqueda%";
        $stmt  = mysqli_prepare($this->db,
            "SELECT id, nombre, nit, municipio, departamento, direccion, telefono, correo, nombre_contacto
             FROM clientes WHERE estado='activo' AND (nombre LIKE ? OR nit LIKE ?)
             ORDER BY nombre LIMIT ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $param, $param, $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function existeNit(string $nit, int $excluirId = 0): bool
    {
        $stmt = mysqli_prepare($this->db,
            'SELECT id FROM clientes WHERE nit = ? AND id != ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'si', $nit, $excluirId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existe = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    public function crear(string $nombre, string $nit, string $departamento, string $municipio,
                          string $direccion, string $nombre_contacto, string $telefono,
                          ?string $correo): bool
    {
        $stmt = mysqli_prepare($this->db,
            'INSERT INTO clientes (nombre, nit, departamento, municipio, direccion, nombre_contacto, telefono, correo)
             VALUES (?,?,?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'ssssssss',
            $nombre, $nit, $departamento, $municipio, $direccion, $nombre_contacto, $telefono, $correo);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function actualizar(int $id, string $nombre, string $nit, string $departamento,
                               string $municipio, string $direccion, string $nombre_contacto,
                               string $telefono, ?string $correo, string $estado): bool
    {
        $stmt = mysqli_prepare($this->db,
            'UPDATE clientes SET nombre=?,nit=?,departamento=?,municipio=?,direccion=?,
             nombre_contacto=?,telefono=?,correo=?,estado=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssssssssi',
            $nombre, $nit, $departamento, $municipio, $direccion,
            $nombre_contacto, $telefono, $correo, $estado, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function eliminar(int $id): bool
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM clientes WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}
