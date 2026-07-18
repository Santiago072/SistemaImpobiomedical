<?php
/**
 * EstadisticaModel — maneja todas las consultas complejas de análisis de datos.
 * Exclusivo para administradores.
 */
class EstadisticaModel
{
    private \mysqli $db;

    public function __construct(\mysqli $conexion)
    {
        $this->db = $conexion;
    }

    // ── 1. KPIs Generales ───────────────────────────────────────────────────
    public function getKpisGenerales(?string $fecha_inicio = null, ?string $fecha_fin = null): array
    {
        $kpis = [
            'total_cotizaciones' => 0,
            'total_ordenes' => 0,
            'total_clientes' => 0,
            'total_productos' => 0,
            'monto_cotizado_mes' => 0
        ];

        $whereFechas = "";
        $whereFechasCot = "";
        $whereFechasOrd = "";
        if ($fecha_inicio && $fecha_fin) {
            $whereFechas = " AND fecha_creacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            $whereFechasCot = " AND c.fecha_creacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            $whereFechasOrd = " AND fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }

        // Cotizaciones (finalizadas)
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM cotizaciones WHERE estado = 'finalizada' $whereFechas");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_cotizaciones'] = (int)$row['total'];
        }

        // Órdenes
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM ordenes_compra WHERE 1=1 $whereFechasOrd");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_ordenes'] = (int)$row['total'];
        }

        // Clientes
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_clientes'] = (int)$row['total'];
        }

        // Productos
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM productos WHERE estado = 'activo'");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_productos'] = (int)$row['total'];
        }

        // Monto cotizado este periodo
        $queryMonto = "
            SELECT SUM(i.cantidad * i.precio) as total_monto
            FROM cotizacion_items i
            JOIN cotizaciones c ON i.cotizacion_id = c.id
            WHERE c.estado = 'finalizada' 
        ";
        if ($fecha_inicio && $fecha_fin) {
            $queryMonto .= " AND c.fecha_creacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }
        
        $res = mysqli_query($this->db, $queryMonto);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['monto_cotizado_mes'] = (float)($row['total_monto'] ?? 0);
        }

        return $kpis;
    }

    // ── 2. Top Clientes (Bar Chart Horizontal) ──────────────────────────────
    public function getTopClientes(int $limite = 5, ?string $fecha_inicio = null, ?string $fecha_fin = null): array
    {
        $query = "
            SELECT cliente_nombre, COUNT(*) as cantidad
            FROM cotizaciones
            WHERE estado = 'finalizada' AND cliente_nombre != ''
        ";
        if ($fecha_inicio && $fecha_fin) {
            $query .= " AND fecha_creacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }
        $query .= " GROUP BY cliente_nombre ORDER BY cantidad DESC LIMIT ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $datos = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result)) {
            $datos['labels'][] = mb_substr($row['cliente_nombre'], 0, 45); 
            $datos['data'][] = (int)$row['cantidad'];
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    // ── 3. Top Productos Cotizados (Doughnut Chart) ─────────────────────────
    public function getTopProductos(int $limite = 5, ?string $fecha_inicio = null, ?string $fecha_fin = null): array
    {
        $query = "
            SELECT p.titulo, COUNT(i.id) as cantidad
            FROM cotizacion_items i
            JOIN productos p ON i.producto_id = p.id
            JOIN cotizaciones c ON i.cotizacion_id = c.id
            WHERE c.estado = 'finalizada'
        ";
        if ($fecha_inicio && $fecha_fin) {
            $query .= " AND c.fecha_creacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }
        $query .= " GROUP BY p.id ORDER BY cantidad DESC LIMIT ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $datos = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result)) {
            $datos['labels'][] = mb_substr($row['titulo'], 0, 45);
            $datos['data'][] = (int)$row['cantidad'];
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    // ── 4. Top Vendedores (Bar Chart Horizontal) ────────────────────────────
    public function getTopVendedores(int $limite = 5, ?string $fecha_inicio = null, ?string $fecha_fin = null): array
    {
        $query = "
            SELECT u.nombre, COUNT(o.id) as cantidad
            FROM ordenes_compra o
            JOIN cotizaciones c ON o.cotizacion_id = c.id
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE 1=1
        ";
        if ($fecha_inicio && $fecha_fin) {
            $query .= " AND o.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }
        $query .= " GROUP BY u.id ORDER BY cantidad DESC LIMIT ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $datos = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result)) {
            $datos['labels'][] = mb_substr($row['nombre'], 0, 45);
            $datos['data'][] = (int)$row['cantidad'];
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    // ── 5. Cotizaciones y Órdenes por Mes (Line Chart / Bar Chart) ──────────
    public function getMetricasEvolucion(): array
    {
        // Traer últimos 6 meses
        $query = "
            SELECT 
                DATE_FORMAT(c.fecha_creacion, '%Y-%m') as mes,
                COUNT(c.id) as cotizaciones,
                (SELECT COUNT(o.id) FROM ordenes_compra o JOIN cotizaciones c2 ON o.cotizacion_id = c2.id WHERE DATE_FORMAT(c2.fecha_creacion, '%Y-%m') = DATE_FORMAT(c.fecha_creacion, '%Y-%m')) as ordenes
            FROM cotizaciones c
            WHERE c.estado = 'finalizada' 
              AND c.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ";
        $res = mysqli_query($this->db, $query);
        
        $datos = ['meses' => [], 'cotizaciones' => [], 'ordenes' => []];
        while ($row = mysqli_fetch_assoc($res)) {
            $datos['meses'][] = $row['mes'];
            $datos['cotizaciones'][] = (int)$row['cotizaciones'];
            $datos['ordenes'][] = (int)$row['ordenes'];
        }
        return $datos;
    }
}
