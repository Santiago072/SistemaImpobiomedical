<?php
/**
 * ClienteModel — acceso a datos de la tabla clientes (migrado a PDO).
 *
 * - SRP: toda la lógica SQL de clientes vive aquí.
 * - ISP: implementa RepositoryInterface (contrato estricto).
 * - Campos: nombre, nit, departamento, municipio, direccion, nombre_contacto, telefono, correo
 */
class ClienteModel implements RepositoryInterface
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
                "SELECT * FROM clientes WHERE (nombre LIKE :b1 OR nit LIKE :b2 OR municipio LIKE :b3)
                 ORDER BY nombre LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':b1', $param);
            $stmt->bindValue(':b2', $param);
            $stmt->bindValue(':b3', $param);
        } else {
            $stmt = $this->db->prepare(
                "SELECT * FROM clientes ORDER BY nombre LIMIT :limit OFFSET :offset"
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
                "SELECT COUNT(*) AS total FROM clientes WHERE
                 (nombre LIKE :b1 OR nit LIKE :b2 OR municipio LIKE :b3)"
            );
            $stmt->execute([':b1' => $param, ':b2' => $param, ':b3' => $param]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM clientes");
            $stmt->execute();
        }
        return (int)$stmt->fetchColumn();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Buscar cliente por NIT */
    public function buscarPorNit(string $nit): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE nit = :nit LIMIT 1');
        $stmt->execute([':nit' => $nit]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Búsqueda rápida para autocompletado en cotizaciones */
    public function buscarParaSelect(string $busqueda, int $limite = 10): array
    {
        $param = "%$busqueda%";
        $stmt  = $this->db->prepare(
            "SELECT id, nombre, nit, municipio, departamento, direccion, telefono, correo, nombre_contacto
             FROM clientes WHERE estado='activo' AND (nombre LIKE :b1 OR nit LIKE :b2)
             ORDER BY nombre LIMIT :limit"
        );
        $stmt->bindValue(':b1', $param);
        $stmt->bindValue(':b2', $param);
        $stmt->bindValue(':limit', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function existeNit(string $nit, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM clientes WHERE nit = :nit AND id != :excluir LIMIT 1'
        );
        $stmt->execute([':nit' => $nit, ':excluir' => $excluirId]);
        return (bool)$stmt->fetch();
    }

    public function crear(string $nombre, string $nit, string $departamento, string $municipio,
                          string $direccion, string $nombre_contacto, string $telefono,
                          ?string $correo): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO clientes (nombre, nit, departamento, municipio, direccion, nombre_contacto, telefono, correo)
             VALUES (:nom, :nit, :dep, :mun, :dir, :contacto, :tel, :correo)'
        );
        $stmt->execute([
            ':nom'      => $nombre,
            ':nit'      => $nit,
            ':dep'      => $departamento,
            ':mun'      => $municipio,
            ':dir'      => $direccion,
            ':contacto' => $nombre_contacto,
            ':tel'      => $telefono,
            ':correo'   => $correo,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $nit, string $departamento,
                               string $municipio, string $direccion, string $nombre_contacto,
                               string $telefono, ?string $correo, string $estado = 'activo'): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE clientes SET nombre=:nom, nit=:nit, departamento=:dep, municipio=:mun,
             direccion=:dir, nombre_contacto=:contacto, telefono=:tel, correo=:correo,
             estado=:estado WHERE id=:id'
        );
        return $stmt->execute([
            ':nom'      => $nombre,
            ':nit'      => $nit,
            ':dep'      => $departamento,
            ':mun'      => $municipio,
            ':dir'      => $direccion,
            ':contacto' => $nombre_contacto,
            ':tel'      => $telefono,
            ':correo'   => $correo,
            ':estado'   => $estado,
            ':id'       => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id=:id");
        return $stmt->execute([':id' => $id]);
    }
}
