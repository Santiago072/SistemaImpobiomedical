<?php
/**
 * UsuarioModel — acceso a datos de la tabla usuarios.
 *
 * - SRP: toda la lógica SQL de usuarios vive aquí.
 * - NOTA: No implementa RepositoryInterface debido a parámetros específicos del método crear()
 *
 * Campos clave: codigo (ej: EB) — usado para numerar cotizaciones.
 */
class UsuarioModel
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
                'SELECT * FROM usuarios WHERE nombre LIKE ? OR codigo LIKE ? ORDER BY nombre LIMIT ? OFFSET ?');
            mysqli_stmt_bind_param($stmt, 'ssii', $param, $param, $limite, $offset);
        } else {
            $stmt = mysqli_prepare($this->db,
                'SELECT * FROM usuarios ORDER BY nombre LIMIT ? OFFSET ?');
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
                'SELECT COUNT(*) AS total FROM usuarios WHERE nombre LIKE ? OR codigo LIKE ?');
            mysqli_stmt_bind_param($stmt, 'ss', $param, $param);
        } else {
            $stmt = mysqli_prepare($this->db, 'SELECT COUNT(*) AS total FROM usuarios');
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return (int)($row['total'] ?? 0);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = mysqli_prepare($this->db, 'SELECT * FROM usuarios WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function buscarPorDocumento(string $documento): ?array
    {
        $stmt = mysqli_prepare($this->db,
            "SELECT id, codigo, nombre, correo, documento, password, cargo, rol
             FROM usuarios WHERE documento = ? AND estado = 'activo'");
        mysqli_stmt_bind_param($stmt, 's', $documento);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }

    public function existeCodigoOCorreo(string $codigo, string $correo, int $excluirId = 0): bool
    {
        $stmt = mysqli_prepare($this->db,
            'SELECT id FROM usuarios WHERE (codigo = ? OR correo = ?) AND id != ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'ssi', $codigo, $correo, $excluirId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existe = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    public function existeDocumentoOCorreo(string $documento, string $correo, int $excluirId = 0): bool
    {
        $stmt = mysqli_prepare($this->db,
            'SELECT id FROM usuarios WHERE (documento = ? OR correo = ?) AND id != ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'ssi', $documento, $correo, $excluirId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existe = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    /**
     * @param bool $requierePassword Si false, se permite NULL (el admin lo asigna después).
     */
    public function crear(string $codigo, string $documento, string $nombre, string $correo,
                          ?string $passwordHash, string $telefono, string $cargo, string $rol): bool
    {
        $stmt = mysqli_prepare($this->db,
            'INSERT INTO usuarios (codigo, documento, nombre, correo, password, telefono, cargo, rol)
             VALUES (?,?,?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'ssssssss',
            $codigo, $documento, $nombre, $correo, $passwordHash, $telefono, $cargo, $rol);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function actualizar(int $id, string $codigo, string $documento, string $nombre,
                               string $correo, string $telefono, string $cargo,
                               string $rol, string $estado, ?string $passwordHash = null): bool
    {
        if ($passwordHash !== null) {
            $stmt = mysqli_prepare($this->db,
                'UPDATE usuarios SET codigo=?,documento=?,nombre=?,correo=?,password=?,telefono=?,cargo=?,rol=?,estado=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'sssssssssi',
                $codigo, $documento, $nombre, $correo, $passwordHash, $telefono, $cargo, $rol, $estado, $id);
        } else {
            $stmt = mysqli_prepare($this->db,
                'UPDATE usuarios SET codigo=?,documento=?,nombre=?,correo=?,telefono=?,cargo=?,rol=?,estado=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'ssssssssi',
                $codigo, $documento, $nombre, $correo, $telefono, $cargo, $rol, $estado, $id);
        }
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function resetPassword(int $id, string $passwordHash): bool
    {
        $stmt = mysqli_prepare($this->db, 'UPDATE usuarios SET password=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'si', $passwordHash, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function eliminar(int $id): bool
    {
        $stmt = mysqli_prepare($this->db, 'DELETE FROM usuarios WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function contarAdmins(): int
    {
        $result = mysqli_query($this->db,
            "SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'admin'");
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    public function listarActivos(): array
    {
        $result = mysqli_query($this->db,
            "SELECT id, codigo, nombre FROM usuarios WHERE estado = 'activo' ORDER BY nombre ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}
