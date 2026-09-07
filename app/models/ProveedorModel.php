<?php
/**
 * ProveedorModel — acceso a datos de proveedores via PDO.
 * Modelo compacto: nit, nombre_proveedor, tipo_contribuyente, nombre_banco, numero_cuenta, tipo_cuenta, estado.
 */
class ProveedorModel
{
    private \PDO $db;

    public function __construct(\PDO $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Lista proveedores con paginación y búsqueda opcional por NIT o Nombre.
     */
    public function listar(int $pagina = 1, int $porPagina = 10, string $busqueda = '', string $estado = 'activo'): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $params = [];
        $where  = [];

        if ($estado !== '') {
            $where[]  = 'estado = :estado';
            $params[':estado'] = $estado;
        }

        if ($busqueda !== '') {
            $where[] = '(nit LIKE :b1 OR nombre_proveedor LIKE :b2 OR nombre_banco LIKE :b3)';
            $params[':b1'] = '%' . $busqueda . '%';
            $params[':b2'] = '%' . $busqueda . '%';
            $params[':b3'] = '%' . $busqueda . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Total registros
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM proveedores {$whereClause}");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Registros paginados
        $stmt = $this->db->prepare(
            "SELECT * FROM proveedores {$whereClause}
             ORDER BY nombre_proveedor ASC
             LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,    \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'proveedores'  => $stmt->fetchAll(),
            'total'        => $total,
            'porPagina'    => $porPagina,
            'paginaActual' => $pagina,
            'totalPaginas' => max(1, (int)ceil($total / $porPagina)),
        ];
    }

    /**
     * Búsqueda en vivo (AJAX) para autocompletado en cotizaciones y órdenes.
     * Retorna lista de coincidencias activas por NIT o Nombre.
     */
    public function buscarLive(string $termino, int $limite = 10): array
    {
        $termino = trim($termino);
        if ($termino === '') {
            return [];
        }

        $termNitNorm = function_exists('normalizar_nit') ? normalizar_nit($termino) : str_replace(['.', ' '], '', $termino);

        $stmt = $this->db->prepare(
            "SELECT id, nit, nombre_proveedor, tipo_contribuyente, nombre_banco, numero_cuenta, tipo_cuenta
             FROM proveedores
             WHERE estado = 'activo'
               AND (nit LIKE :term1 
                    OR REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') LIKE :termNorm
                    OR nombre_proveedor LIKE :term2)
             ORDER BY (REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :termNormExact) DESC, nombre_proveedor ASC
             LIMIT :lim"
        );
        $stmt->bindValue(':term1', '%' . $termino . '%');
        $stmt->bindValue(':termNorm', '%' . $termNitNorm . '%');
        $stmt->bindValue(':term2', '%' . $termino . '%');
        $stmt->bindValue(':termNormExact', $termNitNorm);
        $stmt->bindValue(':lim',   $limite, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtiene un proveedor por su ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtiene un proveedor por su NIT (admite con o sin puntos).
     */
    public function buscarPorNit(string $nit): ?array
    {
        $nitNorm = function_exists('normalizar_nit') ? normalizar_nit($nit) : str_replace(['.', ' '], '', $nit);
        $stmt = $this->db->prepare(
            "SELECT * FROM proveedores 
             WHERE TRIM(nit) = :nit 
                OR REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :nitNorm 
             LIMIT 1"
        );
        $stmt->execute([':nit' => trim($nit), ':nitNorm' => $nitNorm]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Verifica si un NIT ya está registrado para otro ID (compara normalizado).
     */
    public function nitExiste(string $nit, int $excluirId = 0): bool
    {
        $nitNorm = function_exists('normalizar_nit') ? normalizar_nit($nit) : str_replace(['.', ' '], '', $nit);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM proveedores 
             WHERE (TRIM(nit) = :nit OR REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :nitNorm) 
               AND id != :id"
        );
        $stmt->execute([
            ':nit'     => trim($nit),
            ':nitNorm' => $nitNorm,
            ':id'      => $excluirId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Registra un nuevo proveedor.
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO proveedores 
             (nit, nombre_proveedor, tipo_contribuyente, nombre_banco, numero_cuenta, tipo_cuenta, estado)
             VALUES (:nit, :nom, :tipo, :banco, :cta, :tipo_cta, :est)"
        );
        $stmt->execute([
            ':nit'      => trim($datos['nit'] ?? ''),
            ':nom'      => trim($datos['nombre_proveedor'] ?? ''),
            ':tipo'     => trim($datos['tipo_contribuyente'] ?? 'PERSONA JURÍDICA'),
            ':banco'    => !empty($datos['nombre_banco']) ? trim($datos['nombre_banco']) : null,
            ':cta'      => !empty($datos['numero_cuenta']) ? trim($datos['numero_cuenta']) : null,
            ':tipo_cta' => !empty($datos['tipo_cuenta']) ? trim($datos['tipo_cuenta']) : null,
            ':est'      => in_array($datos['estado'] ?? '', ['activo', 'inactivo'], true) ? $datos['estado'] : 'activo',
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza los datos de un proveedor existente.
     */
    public function editar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE proveedores 
             SET nit                = :nit,
                 nombre_proveedor   = :nom,
                 tipo_contribuyente = :tipo,
                 nombre_banco       = :banco,
                 numero_cuenta      = :cta,
                 tipo_cuenta        = :tipo_cta,
                 estado             = :est
             WHERE id = :id"
        );
        return $stmt->execute([
            ':nit'      => trim($datos['nit'] ?? ''),
            ':nom'      => trim($datos['nombre_proveedor'] ?? ''),
            ':tipo'     => trim($datos['tipo_contribuyente'] ?? 'PERSONA JURÍDICA'),
            ':banco'    => !empty($datos['nombre_banco']) ? trim($datos['nombre_banco']) : null,
            ':cta'      => !empty($datos['numero_cuenta']) ? trim($datos['numero_cuenta']) : null,
            ':tipo_cta' => !empty($datos['tipo_cuenta']) ? trim($datos['tipo_cuenta']) : null,
            ':est'      => in_array($datos['estado'] ?? '', ['activo', 'inactivo'], true) ? $datos['estado'] : 'activo',
            ':id'       => $id,
        ]);
    }

    /**
     * Eliminación suave (Soft Delete) o física si no tiene historial.
     */
    public function eliminar(int $id): bool
    {
        // Cambiar a inactivo para proteger integridad referencial histórica
        $stmt = $this->db->prepare("UPDATE proveedores SET estado = 'inactivo' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
