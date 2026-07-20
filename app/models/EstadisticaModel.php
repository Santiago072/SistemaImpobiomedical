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
            'total_ordenes'      => 0,
            'total_clientes'     => 0,
            'total_productos'    => 0,
            'monto_cotizado_mes' => 0,
            'monto_pedido'       => 0,
        ];

        $whereFechas    = '';
        $whereFechasOrd = '';
        if ($fecha_inicio && $fecha_fin) {
            $whereFechas    = " AND fecha_creacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            $whereFechasOrd = " AND o.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }

        // Cotizaciones finalizadas
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM cotizaciones WHERE estado = 'finalizada' $whereFechas");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_cotizaciones'] = (int)$row['total'];
        }

        // Órdenes de compra
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM ordenes_compra o WHERE 1=1 $whereFechasOrd");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_ordenes'] = (int)$row['total'];
        }

        // Clientes activos
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_clientes'] = (int)$row['total'];
        }

        // Productos activos
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM productos WHERE estado = 'activo'");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['total_productos'] = (int)$row['total'];
        }

        // Monto cotizado (ítems de cotizaciones finalizadas * precio cliente)
        $q = "SELECT SUM(i.cantidad * i.precio) as total_monto
              FROM cotizacion_items i
              JOIN cotizaciones c ON i.cotizacion_id = c.id
              WHERE c.estado = 'finalizada'";
        if ($fecha_inicio && $fecha_fin) {
            $q .= " AND c.fecha_creacion BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }
        $res = mysqli_query($this->db, $q);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['monto_cotizado_mes'] = (float)($row['total_monto'] ?? 0);
        }

        // Monto pedido (suma real de ítems en órdenes de compra: precio_proveedor * cantidad + iva)
        $q2 = "SELECT SUM(oi.total) as total_pedido
               FROM orden_compra_items oi
               JOIN ordenes_compra o ON oi.orden_id = o.id
               WHERE 1=1";
        if ($fecha_inicio && $fecha_fin) {
            $q2 .= " AND o.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        }
        $res = mysqli_query($this->db, $q2);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $kpis['monto_pedido'] = (float)($row['total_pedido'] ?? 0);
        }

        return $kpis;
    }

    // ── 2. Top Clientes ──────────────────────────────────────────────────────
    public function getTopClientes(int $limite = 5, ?string $fi = null, ?string $ff = null): array
    {
        $q = "SELECT cliente_nombre, COUNT(*) as cantidad
              FROM cotizaciones
              WHERE estado = 'finalizada' AND cliente_nombre != ''";
        if ($fi && $ff) {
            $q .= " AND fecha_creacion BETWEEN '$fi 00:00:00' AND '$ff 23:59:59'";
        }
        $q .= " GROUP BY cliente_nombre ORDER BY cantidad DESC LIMIT ?";
        $stmt = mysqli_prepare($this->db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $datos  = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result)) {
            $datos['labels'][] = mb_substr($row['cliente_nombre'], 0, 45);
            $datos['data'][]   = (int)$row['cantidad'];
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    // ── 3. Top Productos Cotizados ───────────────────────────────────────────
    public function getTopProductos(int $limite = 5, ?string $fi = null, ?string $ff = null): array
    {
        $q = "SELECT p.titulo, COUNT(i.id) as cantidad
              FROM cotizacion_items i
              JOIN productos p ON i.producto_id = p.id
              JOIN cotizaciones c ON i.cotizacion_id = c.id
              WHERE c.estado = 'finalizada'";
        if ($fi && $ff) {
            $q .= " AND c.fecha_creacion BETWEEN '$fi 00:00:00' AND '$ff 23:59:59'";
        }
        $q .= " GROUP BY p.id ORDER BY cantidad DESC LIMIT ?";
        $stmt = mysqli_prepare($this->db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $datos  = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result)) {
            $datos['labels'][] = mb_substr($row['titulo'], 0, 45);
            $datos['data'][]   = (int)$row['cantidad'];
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    // ── 4. Top Vendedores (por Órdenes generadas) ───────────────────────────
    public function getTopVendedores(int $limite = 5, ?string $fi = null, ?string $ff = null): array
    {
        $q = "SELECT u.nombre, COUNT(o.id) as cantidad
              FROM ordenes_compra o
              JOIN cotizaciones c ON o.cotizacion_id = c.id
              JOIN usuarios u ON c.usuario_id = u.id
              WHERE 1=1";
        if ($fi && $ff) {
            $q .= " AND o.fecha BETWEEN '$fi 00:00:00' AND '$ff 23:59:59'";
        }
        $q .= " GROUP BY u.id ORDER BY cantidad DESC LIMIT ?";
        $stmt = mysqli_prepare($this->db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $datos  = ['labels' => [], 'data' => []];
        while ($row = mysqli_fetch_assoc($result)) {
            $datos['labels'][] = mb_substr($row['nombre'], 0, 45);
            $datos['data'][]   = (int)$row['cantidad'];
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    // ── 5. Evolución mensual Cotizaciones vs Órdenes ─────────────────────────
    public function getMetricasEvolucion(?string $fi = null, ?string $ff = null): array
    {
        if ($fi && $ff) {
            $whereEvo = " AND c.fecha_creacion BETWEEN '$fi 00:00:00' AND '$ff 23:59:59'";
        } else {
            $whereEvo = " AND c.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        }

        $q = "SELECT
                DATE_FORMAT(c.fecha_creacion, '%Y-%m') as mes,
                COUNT(c.id) as cotizaciones,
                (SELECT COUNT(o.id) FROM ordenes_compra o
                 JOIN cotizaciones c2 ON o.cotizacion_id = c2.id
                 WHERE DATE_FORMAT(c2.fecha_creacion, '%Y-%m') = DATE_FORMAT(c.fecha_creacion, '%Y-%m')) as ordenes
              FROM cotizaciones c
              WHERE c.estado = 'finalizada' $whereEvo
              GROUP BY mes ORDER BY mes ASC";
        $res  = mysqli_query($this->db, $q);
        $datos = ['meses' => [], 'cotizaciones' => [], 'ordenes' => []];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $datos['meses'][]        = $row['mes'];
                $datos['cotizaciones'][] = (int)$row['cotizaciones'];
                $datos['ordenes'][]      = (int)$row['ordenes'];
            }
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
