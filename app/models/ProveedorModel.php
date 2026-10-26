<?php
/**
 * ProveedorModel — acceso a datos de proveedores via PDO.
 * Campos: nit, nombre_proveedor, tipo_contribuyente, nombre_banco, numero_cuenta, tipo_cuenta, celular, correo, estado.
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
            "SELECT proveedores.*, 
                ((SELECT COUNT(*) FROM cotizacion_items WHERE cotizacion_items.proveedor_id = proveedores.id) +
                 (SELECT COUNT(*) FROM ordenes_compra WHERE ordenes_compra.proveedor_nit = proveedores.nit)) AS total_asociadas
             FROM proveedores {$whereClause}
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
        $termSoloDigitos = preg_replace('/\D/', '', $termino);
        $tieneDigitosSuficientes = strlen($termSoloDigitos) >= 3;

        $sql = "SELECT id, nit, nombre_proveedor, tipo_contribuyente, nombre_banco, numero_cuenta, tipo_cuenta, celular, correo
                FROM proveedores
                WHERE estado = 'activo'
                  AND (
                      LOWER(nombre_proveedor) LIKE LOWER(:termNom)
                      OR nit LIKE :termNitRaw
                      OR REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') LIKE :termNorm";

        if ($tieneDigitosSuficientes) {
            $sql .= " OR REPLACE(REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', ''), '-', '') LIKE :termDigitos";
        }

        $sql .= ")
                ORDER BY 
                    (LOWER(TRIM(nombre_proveedor)) = LOWER(:termExactNom)) DESC,
                    (REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :termNormExact) DESC,
                    (LOWER(nombre_proveedor) LIKE LOWER(:termNomPrefix)) DESC,
                    nombre_proveedor ASC
                LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':termNom', '%' . $termino . '%');
        $stmt->bindValue(':termNitRaw', '%' . $termino . '%');
        $stmt->bindValue(':termNorm', '%' . $termNitNorm . '%');
        if ($tieneDigitosSuficientes) {
            $stmt->bindValue(':termDigitos', '%' . $termSoloDigitos . '%');
        }
        $stmt->bindValue(':termExactNom', $termino);
        $stmt->bindValue(':termNormExact', $termNitNorm);
        $stmt->bindValue(':termNomPrefix', $termino . '%');
        $stmt->bindValue(':lim', $limite, \PDO::PARAM_INT);
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
     * Obtiene un proveedor por su NIT (admite con/sin puntos, espacios o guion).
     */
    public function buscarPorNit(string $nit): ?array
    {
        $nitLimpio = trim($nit);
        if ($nitLimpio === '') return null;

        $nitNorm    = function_exists('normalizar_nit') ? normalizar_nit($nitLimpio) : str_replace(['.', ' '], '', $nitLimpio);
        $nitDigitos = preg_replace('/\D/', '', $nitLimpio);

        $sql = "SELECT * FROM proveedores 
                WHERE TRIM(nit) = :nit 
                   OR REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :nitNorm";
        $params = [
            ':nit'     => $nitLimpio,
            ':nitNorm' => $nitNorm,
        ];

        if (strlen($nitDigitos) >= 6) {
            $sql .= " OR REPLACE(REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', ''), '-', '') = :nitDigitos";
            $params[':nitDigitos'] = $nitDigitos;
        }

        $sql .= " ORDER BY (TRIM(nit) = :nitExacto) DESC, id DESC LIMIT 1";
        $params[':nitExacto'] = $nitLimpio;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Verifica si un NIT ya está registrado para otro ID (compara normalizado).
     */
    public function nitExiste(string $nit, int $excluirId = 0): bool
    {
        $nitLimpio = trim($nit);
        if ($nitLimpio === '') return false;

        $nitNorm    = function_exists('normalizar_nit') ? normalizar_nit($nitLimpio) : str_replace(['.', ' '], '', $nitLimpio);
        $nitDigitos = preg_replace('/\D/', '', $nitLimpio);

        $sql = "SELECT COUNT(*) FROM proveedores 
                WHERE (
                    TRIM(nit) = :nit 
                    OR REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', '') = :nitNorm";
        $params = [
            ':nit'     => $nitLimpio,
            ':nitNorm' => $nitNorm,
            ':id'      => $excluirId,
        ];

        if (strlen($nitDigitos) >= 6) {
            $sql .= " OR REPLACE(REPLACE(REPLACE(TRIM(nit), '.', ''), ' ', ''), '-', '') = :nitDigitos";
            $params[':nitDigitos'] = $nitDigitos;
        }

        $sql .= ") AND id != :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Registra un nuevo proveedor.
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO proveedores 
             (nit, nombre_proveedor, tipo_contribuyente, nombre_banco, numero_cuenta, tipo_cuenta, celular, correo, estado)
             VALUES (:nit, :nom, :tipo, :banco, :cta, :tipo_cta, :celular, :correo, :est)"
        );
        $stmt->execute([
            ':nit'      => trim($datos['nit'] ?? ''),
            ':nom'      => trim($datos['nombre_proveedor'] ?? ''),
            ':tipo'     => trim($datos['tipo_contribuyente'] ?? 'PERSONA JURÍDICA'),
            ':banco'    => !empty($datos['nombre_banco']) ? trim($datos['nombre_banco']) : null,
            ':cta'      => !empty($datos['numero_cuenta']) ? trim($datos['numero_cuenta']) : null,
            ':tipo_cta' => !empty($datos['tipo_cuenta']) ? trim($datos['tipo_cuenta']) : null,
            ':celular'  => !empty($datos['celular']) ? trim($datos['celular']) : null,
            ':correo'   => !empty($datos['correo']) ? trim($datos['correo']) : null,
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
                 celular            = :celular,
                 correo             = :correo,
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
            ':celular'  => !empty($datos['celular']) ? trim($datos['celular']) : null,
            ':correo'   => !empty($datos['correo']) ? trim($datos['correo']) : null,
            ':est'      => in_array($datos['estado'] ?? '', ['activo', 'inactivo'], true) ? $datos['estado'] : 'activo',
            ':id'       => $id,
        ]);
    }

    /**
     * Registra un proveedor si no existe por NIT, o actualiza datos bancarios/nombre
     * si ya existe pero tiene esos campos vacíos. Diseñado para el auto-registro
     * desde órdenes de compra sin generar Duplicate Entry.
     *
     * @return int ID del proveedor (nuevo o existente)
     */
    public function registrarOActualizar(array $datos): int
    {
        $nit  = trim($datos['nit'] ?? '');
        $nom  = trim($datos['nombre_proveedor'] ?? '');
        $tipo = trim($datos['tipo_contribuyente'] ?? 'PERSONA JURÍDICA');
        $banco   = !empty($datos['nombre_banco'])   ? trim($datos['nombre_banco'])   : null;
        $cuenta  = !empty($datos['numero_cuenta'])  ? trim($datos['numero_cuenta'])  : null;
        $tipoCta = !empty($datos['tipo_cuenta'])    ? trim($datos['tipo_cuenta'])    : null;

        if ($nit === '') {
            return 0;
        }

        // Verificar si ya existe por NIT (normalizado)
        $existente = $this->buscarPorNit($nit);

        if ($existente) {
            // Actualizar datos del proveedor (bancarios, contacto y datos fiscales) si cambiaron o fueron modificados en la orden
            $actualizar = [];
            if (!empty($nom)     && $nom !== ($existente['nombre_proveedor'] ?? ''))     $actualizar['nombre_proveedor']   = $nom;
            if (!empty($tipo)    && $tipo !== ($existente['tipo_contribuyente'] ?? ''))  $actualizar['tipo_contribuyente'] = $tipo;
            if (!empty($banco)   && $banco !== ($existente['nombre_banco'] ?? ''))       $actualizar['nombre_banco']       = $banco;
            if (!empty($cuenta)  && $cuenta !== ($existente['numero_cuenta'] ?? ''))     $actualizar['numero_cuenta']      = $cuenta;
            if (!empty($tipoCta) && $tipoCta !== ($existente['tipo_cuenta'] ?? ''))     $actualizar['tipo_cuenta']        = $tipoCta;
            
            $celular = !empty($datos['celular']) ? trim($datos['celular']) : null;
            $correo  = !empty($datos['correo'])  ? trim($datos['correo'])  : null;
            if (!empty($celular) && $celular !== ($existente['celular'] ?? ''))          $actualizar['celular']            = $celular;
            if (!empty($correo)  && $correo !== ($existente['correo'] ?? ''))            $actualizar['correo']             = $correo;

            if (!empty($actualizar)) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($actualizar)));
                $params = array_combine(
                    array_map(fn($k) => ":$k", array_keys($actualizar)),
                    array_values($actualizar)
                );
                $params[':id'] = (int)$existente['id'];
                $stmt = $this->db->prepare("UPDATE proveedores SET $sets WHERE id = :id");
                $stmt->execute($params);
            }
            return (int)$existente['id'];
        }

        // No existe → INSERT
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO proveedores
                 (nit, nombre_proveedor, tipo_contribuyente, nombre_banco, numero_cuenta, tipo_cuenta, celular, correo, estado)
                 VALUES (:nit, :nom, :tipo, :banco, :cta, :tipo_cta, :celular, :correo, 'activo')"
            );
            $stmt->execute([
                ':nit'      => mb_substr($nit, 0, 30),
                ':nom'      => mb_substr($nom, 0, 200),
                ':tipo'     => mb_substr($tipo, 0, 100),
                ':banco'    => !empty($banco) ? mb_substr($banco, 0, 100) : null,
                ':cta'      => !empty($cuenta) ? mb_substr($cuenta, 0, 100) : null,
                ':tipo_cta' => !empty($tipoCta) ? mb_substr($tipoCta, 0, 50) : null,
                ':celular'  => !empty($datos['celular']) ? mb_substr(trim($datos['celular']), 0, 30) : null,
                ':correo'   => !empty($datos['correo']) ? mb_substr(trim($datos['correo']), 0, 100) : null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            // Si hubo colisión por concurrencia o variación de NIT, recuperar el proveedor existente
            $recuperado = $this->buscarPorNit($nit);
            if ($recuperado) {
                return (int)$recuperado['id'];
            }
            error_log("[ProveedorModel::registrarOActualizar] Error en INSERT: " . $e->getMessage());
            return 0;
        }
    }

    public function contarAsociadas(int $id): int
    {
        $stmt = $this->db->prepare(
            "SELECT 
                (SELECT COUNT(*) FROM cotizacion_items WHERE proveedor_id = :id1) +
                (SELECT COUNT(*) FROM ordenes_compra WHERE proveedor_nit = (SELECT nit FROM proveedores WHERE id = :id2 LIMIT 1))"
        );
        $stmt->execute([':id1' => $id, ':id2' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $estadoValido = in_array($estado, ['activo', 'inactivo'], true) ? $estado : 'activo';
        $stmt = $this->db->prepare("UPDATE proveedores SET estado = :estado WHERE id = :id");
        return $stmt->execute([':estado' => $estadoValido, ':id' => $id]);
    }

    public function eliminarFisico(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        return $this->cambiarEstado($id, 'inactivo');
    }
}
