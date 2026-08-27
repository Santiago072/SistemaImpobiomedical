<?php
/**
 * EstadisticaModel — consultas complejas de análisis de datos (migrado a PDO).
 *
 * SEGURIDAD: Se eliminó la concatenación directa de fechas en SQL (SQLi).
 * Ahora todas las fechas se pasan como parámetros enlazados via PDO.
 * Exclusivo para administradores.
 */
class EstadisticaModel
{
    private \PDO $db;

    public function __construct(\PDO $conexion)
    {
        $this->db = $conexion;
    }

    // ── 1. KPIs Generales ───────────────────────────────────────────────────
    public function getKpisGenerales(?string $fecha_inicio = null, ?string $fecha_fin = null): array
    {
        $kpis = [
            'total_cotizaciones' => 0,
            'total_ordenes'      => 0,
            'total_clientes'     => 0,
            'total_productos'    => 0,
            'monto_cotizado_mes' => 0,
            'monto_vendido'      => 0,
        ];

        $tieneFechas = ($fecha_inicio && $fecha_fin);

        // Cotizaciones finalizadas
        if ($tieneFechas) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) as total FROM cotizaciones
                 WHERE estado = 'finalizada'
                 AND fecha_creacion BETWEEN :fi AND :ff"
            );
            $stmt->execute([':fi' => "$fecha_inicio 00:00:00", ':ff' => "$fecha_fin 23:59:59"]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM cotizaciones WHERE estado = 'finalizada'");
            $stmt->execute();
        }
        $kpis['total_cotizaciones'] = (int)$stmt->fetchColumn();

        // Órdenes de compra
        if ($tieneFechas) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) as total FROM ordenes_compra WHERE fecha BETWEEN :fi AND :ff"
            );
            $stmt->execute([':fi' => "$fecha_inicio 00:00:00", ':ff' => "$fecha_fin 23:59:59"]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM ordenes_compra");
            $stmt->execute();
        }
        $kpis['total_ordenes'] = (int)$stmt->fetchColumn();

        // Clientes activos
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'");
        $stmt->execute();
        $kpis['total_clientes'] = (int)$stmt->fetchColumn();

        // Productos activos
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM productos WHERE estado = 'activo'");
        $stmt->execute();
        $kpis['total_productos'] = (int)$stmt->fetchColumn();

        // Monto cotizado (ítems de cotizaciones finalizadas * precio cliente)
        if ($tieneFechas) {
            $stmt = $this->db->prepare(
                "SELECT SUM(i.cantidad * i.precio) as total_monto
                 FROM cotizacion_items i
                 JOIN cotizaciones c ON i.cotizacion_id = c.id
                 WHERE c.estado = 'finalizada'
                 AND c.fecha_creacion BETWEEN :fi AND :ff"
            );
            $stmt->execute([':fi' => "$fecha_inicio 00:00:00", ':ff' => "$fecha_fin 23:59:59"]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT SUM(i.cantidad * i.precio) as total_monto
                 FROM cotizacion_items i
                 JOIN cotizaciones c ON i.cotizacion_id = c.id
                 WHERE c.estado = 'finalizada'"
            );
            $stmt->execute();
        }
        $kpis['monto_cotizado_mes'] = (float)($stmt->fetchColumn() ?? 0);

        // Monto Vendido
        if ($tieneFechas) {
            $stmt = $this->db->prepare(
                "SELECT SUM(oi.cantidad * ci.precio) as total_vendido
                 FROM orden_compra_items oi
                 JOIN ordenes_compra o ON oi.orden_id = o.id
                 JOIN cotizacion_items ci ON oi.cotizacion_item_id = ci.id
                 JOIN cotizaciones c ON o.cotizacion_id = c.id
                 WHERE c.estado = 'finalizada'
                 AND o.fecha BETWEEN :fi AND :ff"
            );
            $stmt->execute([':fi' => "$fecha_inicio 00:00:00", ':ff' => "$fecha_fin 23:59:59"]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT SUM(oi.cantidad * ci.precio) as total_vendido
                 FROM orden_compra_items oi
                 JOIN ordenes_compra o ON oi.orden_id = o.id
                 JOIN cotizacion_items ci ON oi.cotizacion_item_id = ci.id
                 JOIN cotizaciones c ON o.cotizacion_id = c.id
                 WHERE c.estado = 'finalizada'"
            );
            $stmt->execute();
        }
        $kpis['monto_vendido'] = (float)($stmt->fetchColumn() ?? 0);

        return $kpis;
    }

    // ── 2. Top Clientes ──────────────────────────────────────────────────────
    public function getTopClientes(int $limite = 5, ?string $fi = null, ?string $ff = null): array
    {
        $params = [];
        $q = "SELECT cliente_nombre, COUNT(*) as cantidad
              FROM cotizaciones
              WHERE estado = 'finalizada' AND cliente_nombre != ''";

        if ($fi && $ff) {
            $q .= " AND fecha_creacion BETWEEN :fi AND :ff";
            $params = [':fi' => "$fi 00:00:00", ':ff' => "$ff 23:59:59"];
        }

        $q .= " GROUP BY cliente_nombre ORDER BY cantidad DESC LIMIT :limite";
        $stmt = $this->db->prepare($q);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        $datos = ['labels' => [], 'data' => []];
        foreach ($stmt->fetchAll() as $row) {
            $datos['labels'][] = mb_substr($row['cliente_nombre'], 0, 45);
            $datos['data'][]   = (int)$row['cantidad'];
        }
        return $datos;
    }

    // ── 3. Top Productos Cotizados ───────────────────────────────────────────
    public function getTopProductos(int $limite = 5, ?string $fi = null, ?string $ff = null): array
    {
        $params = [];
        $q = "SELECT COALESCE(p.titulo, i.titulo) AS titulo_prod, SUM(i.cantidad) as cantidad
              FROM cotizacion_items i
              LEFT JOIN productos p ON i.producto_id = p.id
              JOIN cotizaciones c ON i.cotizacion_id = c.id
              WHERE c.estado = 'finalizada' AND i.titulo IS NOT NULL AND i.titulo != ''";

        if ($fi && $ff) {
            $q .= " AND c.fecha_creacion BETWEEN :fi AND :ff";
            $params = [':fi' => "$fi 00:00:00", ':ff' => "$ff 23:59:59"];
        }

        $q .= " GROUP BY titulo_prod ORDER BY cantidad DESC LIMIT :limite";
        $stmt = $this->db->prepare($q);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        $datos = ['labels' => [], 'data' => []];
        foreach ($stmt->fetchAll() as $row) {
            $datos['labels'][] = mb_substr($row['titulo_prod'], 0, 45);
            $datos['data'][]   = (int)$row['cantidad'];
        }
        return $datos;
    }

    // ── 4. Top Vendedores (por Monto Vendido) ───────────────────────────
    public function getTopVendedores(int $limite = 5, ?string $fi = null, ?string $ff = null): array
    {
        $params = [];
        $q = "SELECT u.nombre, SUM(oi.cantidad * ci.precio) as cantidad
              FROM orden_compra_items oi
              JOIN ordenes_compra o ON oi.orden_id = o.id
              JOIN cotizacion_items ci ON oi.cotizacion_item_id = ci.id
              JOIN cotizaciones c ON o.cotizacion_id = c.id
              JOIN usuarios u ON c.usuario_id = u.id
              WHERE c.estado = 'finalizada'";

        if ($fi && $ff) {
            $q .= " AND o.fecha BETWEEN :fi AND :ff";
            $params = [':fi' => "$fi 00:00:00", ':ff' => "$ff 23:59:59"];
        }

        $q .= " GROUP BY u.id ORDER BY cantidad DESC LIMIT :limite";
        $stmt = $this->db->prepare($q);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        $datos = ['labels' => [], 'data' => []];
        foreach ($stmt->fetchAll() as $row) {
            $datos['labels'][] = mb_substr($row['nombre'], 0, 45);
            $datos['data'][]   = (float)($row['cantidad'] ?? 0);
        }
        return $datos;
    }

    // ── 5. Evolución mensual: Cotizaciones Totales vs Concluidas ─────────────
    public function getMetricasEvolucion(?string $fi = null, ?string $ff = null): array
    {
        $params = [];
        if ($fi && $ff) {
            $whereEvo  = " AND c.fecha_creacion BETWEEN :fi AND :ff";
            $params = [':fi' => "$fi 00:00:00", ':ff' => "$ff 23:59:59"];
        } else {
            $whereEvo = " AND c.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        }

        $q = "SELECT
                DATE_FORMAT(c.fecha_creacion, '%Y-%m') as mes,
                COUNT(c.id) as cotizaciones,
                SUM(CASE WHEN c.estado_comercial = 'concluida' THEN 1 ELSE 0 END) as concluidas
              FROM cotizaciones c
              WHERE c.estado = 'finalizada' $whereEvo
              GROUP BY mes ORDER BY mes ASC";

        $stmt = $this->db->prepare($q);
        $stmt->execute($params);

        $datos = ['meses' => [], 'cotizaciones' => [], 'concluidas' => []];
        foreach ($stmt->fetchAll() as $row) {
            $datos['meses'][]        = $row['mes'];
            $datos['cotizaciones'][] = (int)$row['cotizaciones'];
            $datos['concluidas'][]   = (int)$row['concluidas'];
        }
        return $datos;
    }

    // ── 6. Datos completos para exportar PDF de Reporte ─────────────────────
    public function getDatosReporte(?string $fi = null, ?string $ff = null): array
    {
        return [
            'kpis'          => $this->getKpisGenerales($fi, $ff),
            'topClientes'   => $this->getTopClientes(10, $fi, $ff),
            'topProductos'  => $this->getTopProductos(10, $fi, $ff),
            'topVendedores' => $this->getTopVendedores(10, $fi, $ff),
            'evolucion'     => $this->getMetricasEvolucion($fi, $ff),
        ];
    }
}
