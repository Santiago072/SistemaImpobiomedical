<?php
/**
 * UsuarioModel — acceso a datos de la tabla usuarios (migrado a PDO).
 *
 * - SRP: toda la lógica SQL de usuarios vive aquí.
 * - ISP: implementa RepositoryInterface (contrato estricto).
 *
 * Campos clave: codigo (ej: EB) — usado para numerar cotizaciones.
 */
class UsuarioModel implements RepositoryInterface
{
    private \PDO $db;

    public function __construct(\PDO $conexion)
    {
        $this->db = $conexion;
    }

    public function listar(int $offset, int $limite, string $busqueda = ''): array
    {
        if ($busqueda !== '') {
            $param = "%$busqueda%";
            $stmt  = $this->db->prepare(
                'SELECT * FROM usuarios WHERE nombre LIKE :b1 OR codigo LIKE :b2 ORDER BY nombre LIMIT :limit OFFSET :offset'
            );
            $stmt->bindValue(':b1', $param);
            $stmt->bindValue(':b2', $param);
        } else {
            $stmt = $this->db->prepare(
                'SELECT * FROM usuarios ORDER BY nombre LIMIT :limit OFFSET :offset'
            );
        }
        $stmt->bindValue(':limit',  $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contar(string $busqueda = ''): int
    {
        if ($busqueda !== '') {
            $param = "%$busqueda%";
            $stmt  = $this->db->prepare(
                'SELECT COUNT(*) AS total FROM usuarios WHERE nombre LIKE :b1 OR codigo LIKE :b2'
            );
            $stmt->bindValue(':b1', $param);
            $stmt->bindValue(':b2', $param);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM usuarios');
        }
        $stmt->execute();
        return (int)($stmt->fetchColumn());
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorDocumento(string $documento): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, codigo, nombre, correo, documento, password, cargo, rol
             FROM usuarios WHERE documento = :doc AND estado = 'activo'"
        );
        $stmt->execute([':doc' => $documento]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existeCodigo(string $codigo, int $excluirId = 0): bool
    {
        if (trim($codigo) === '') {
            return false;
        }
        $stmt = $this->db->prepare(
            'SELECT id FROM usuarios WHERE codigo = :cod AND id != :excluir LIMIT 1'
        );
        $stmt->execute([':cod' => $codigo, ':excluir' => $excluirId]);
        return (bool)$stmt->fetch();
    }

    public function existeDocumento(string $documento, int $excluirId = 0): bool
    {
        if (trim($documento) === '') {
            return false;
        }
        $stmt = $this->db->prepare(
            'SELECT id FROM usuarios WHERE documento = :doc AND id != :excluir LIMIT 1'
        );
        $stmt->execute([':doc' => $documento, ':excluir' => $excluirId]);
        return (bool)$stmt->fetch();
    }

    public function existeCorreo(string $correo, int $excluirId = 0): bool
    {
        $correo = trim($correo);
        if ($correo === '') {
            return false; // Correo opcional no puede colisionar
        }
        $stmt = $this->db->prepare(
            'SELECT id FROM usuarios WHERE correo = :correo AND id != :excluir LIMIT 1'
        );
        $stmt->execute([':correo' => $correo, ':excluir' => $excluirId]);
        return (bool)$stmt->fetch();
    }

    public function existeCodigoOCorreo(string $codigo, string $correo, int $excluirId = 0): bool
    {
        return $this->existeCodigo($codigo, $excluirId) || $this->existeCorreo($correo, $excluirId);
    }

    public function existeDocumentoOCorreo(string $documento, string $correo, int $excluirId = 0): bool
    {
        return $this->existeDocumento($documento, $excluirId) || $this->existeCorreo($correo, $excluirId);
    }

    /**
     * @param bool $requierePassword Si false, se permite NULL (el admin lo asigna después).
     */
    public function crear(string $codigo, string $documento, string $nombre, string $correo,
                          ?string $passwordHash, string $telefono, string $cargo, string $rol): bool
    {
        $valCorreo = !empty(trim($correo)) ? trim($correo) : null;
        $valTel    = !empty(trim($telefono)) ? trim($telefono) : null;
        $valCargo  = !empty(trim($cargo)) ? trim($cargo) : null;

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO usuarios (codigo, documento, nombre, correo, password, telefono, cargo, rol)
                 VALUES (:cod, :doc, :nom, :correo, :pass, :tel, :cargo, :rol)'
            );
            return $stmt->execute([
                ':cod'    => $codigo,
                ':doc'    => $documento,
                ':nom'    => $nombre,
                ':correo' => $valCorreo,
                ':pass'   => $passwordHash,
                ':tel'    => $valTel,
                ':cargo'  => $valCargo,
                ':rol'    => $rol,
            ]);
        } catch (\PDOException $e) {
            // Auto-migración en caso de que la tabla en MySQL tenga la columna correo o telefono como NOT NULL
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'cannot be null')) {
                try {
                    $this->db->exec("ALTER TABLE usuarios MODIFY COLUMN correo VARCHAR(100) NULL DEFAULT NULL, MODIFY COLUMN telefono VARCHAR(20) NULL DEFAULT NULL");
                    $stmt = $this->db->prepare(
                        'INSERT INTO usuarios (codigo, documento, nombre, correo, password, telefono, cargo, rol)
                         VALUES (:cod, :doc, :nom, :correo, :pass, :tel, :cargo, :rol)'
                    );
                    return $stmt->execute([
                        ':cod'    => $codigo,
                        ':doc'    => $documento,
                        ':nom'    => $nombre,
                        ':correo' => $valCorreo,
                        ':pass'   => $passwordHash,
                        ':tel'    => $valTel,
                        ':cargo'  => $valCargo,
                        ':rol'    => $rol,
                    ]);
                } catch (\Throwable $e2) {
                    error_log('Error tras auto-migración de usuarios: ' . $e2->getMessage());
                }
            }
            error_log('Error en UsuarioModel::crear: ' . $e->getMessage());
            $_SESSION['db_error'] = $e->getMessage();
            return false;
        }
    }

    public function actualizar(int $id, string $codigo, string $documento, string $nombre,
                               string $correo, string $telefono, string $cargo,
                               string $rol, string $estado, ?string $passwordHash = null): bool
    {
        $valCorreo = !empty(trim($correo)) ? trim($correo) : null;
        $valTel    = !empty(trim($telefono)) ? trim($telefono) : null;
        $valCargo  = !empty(trim($cargo)) ? trim($cargo) : null;

        try {
            if ($passwordHash !== null) {
                $stmt = $this->db->prepare(
                    'UPDATE usuarios SET codigo=:cod, documento=:doc, nombre=:nom, correo=:correo,
                     password=:pass, telefono=:tel, cargo=:cargo, rol=:rol, estado=:estado
                     WHERE id=:id'
                );
                $params = [
                    ':cod'    => $codigo,
                    ':doc'    => $documento,
                    ':nom'    => $nombre,
                    ':correo' => $valCorreo,
                    ':pass'   => $passwordHash,
                    ':tel'    => $valTel,
                    ':cargo'  => $valCargo,
                    ':rol'    => $rol,
                    ':estado' => $estado,
                    ':id'     => $id,
                ];
            } else {
                $stmt = $this->db->prepare(
                    'UPDATE usuarios SET codigo=:cod, documento=:doc, nombre=:nom, correo=:correo,
                     telefono=:tel, cargo=:cargo, rol=:rol, estado=:estado
                     WHERE id=:id'
                );
                $params = [
                    ':cod'    => $codigo,
                    ':doc'    => $documento,
                    ':nom'    => $nombre,
                    ':correo' => $valCorreo,
                    ':tel'    => $valTel,
                    ':cargo'  => $valCargo,
                    ':rol'    => $rol,
                    ':estado' => $estado,
                    ':id'     => $id,
                ];
            }
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'cannot be null')) {
                try {
                    $this->db->exec("ALTER TABLE usuarios MODIFY COLUMN correo VARCHAR(100) NULL DEFAULT NULL, MODIFY COLUMN telefono VARCHAR(20) NULL DEFAULT NULL");
                    return $this->actualizar($id, $codigo, $doc, $nombre, $correo, $telefono, $cargo, $rol, $estado, $passwordHash);
                } catch (\Throwable $e2) {
                    error_log('Error tras auto-migración de usuarios: ' . $e2->getMessage());
                }
            }
            error_log('Error en UsuarioModel::actualizar: ' . $e->getMessage());
            $_SESSION['db_error'] = $e->getMessage();
            return false;
        }
    }

    public function resetPassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET password=:pass WHERE id=:id');
        return $stmt->execute([':pass' => $passwordHash, ':id' => $id]);
    }

    public function cambiarPassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET password=:pass WHERE id=:id');
        return $stmt->execute([':pass' => $passwordHash, ':id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        try {
            // Limpiar cotizaciones en estado borrador (basura) para evitar bloqueo de FK
            $stmtClean = $this->db->prepare(
                "DELETE FROM cotizaciones WHERE usuario_id = :id AND estado = 'borrador'"
            );
            $stmtClean->execute([':id' => $id]);

            $stmt = $this->db->prepare('DELETE FROM usuarios WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // SQLSTATE: Integrity constraint violation
                $_SESSION['db_error'] = 'El usuario tiene registros asociados (ej. Cotizaciones) y no puede ser eliminado. Recomendación: Cambie su estado a Inactivo.';
            } else {
                $_SESSION['db_error'] = 'Error interno: ' . $e->getMessage();
            }
            return false;
        }
    }

    public function contarAdmins(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'admin'");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function listarActivos(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, codigo, nombre FROM usuarios WHERE estado = 'activo' ORDER BY nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
