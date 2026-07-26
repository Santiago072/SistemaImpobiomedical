<?php
/**
 * Vista: Exportar a Excel
 * Variables: $datosExcel
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Órdenes de Compra</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 15px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #10757e; color: #ffffff; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        td { font-size: 12px; }
        .filter-table { border: none; margin-bottom: 20px; width: 50%; }
        .filter-table th { background-color: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db; }
        .filter-table td { border: 1px solid #d1d5db; }
        .money { font-weight: bold; color: #059669; }
        .title-row { font-size: 18px; font-weight: bold; color: #10757e; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="8" class="title-row" style="border: none; padding-bottom: 15px;">Reporte de Órdenes de Compra (Generado el: <?= date('d/m/Y') ?>)</td>
        </tr>
    </table>

    <?php if (!empty($filtros)): ?>
    <table class="filter-table">
        <thead>
            <tr>
                <th colspan="2">Filtros Aplicados</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($filtros['proveedor'])): ?>
            <tr><td><strong>Proveedor:</strong></td><td><?= htmlspecialchars($filtros['proveedor']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($filtros['cotizacion_numero'])): ?>
            <tr><td><strong>Nº Cotización:</strong></td><td><?= htmlspecialchars($filtros['cotizacion_numero']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin'])): ?>
            <tr><td><strong>Rango de Fechas:</strong></td><td>
                <?php 
                    if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
                        echo htmlspecialchars($filtros['fecha_inicio']) . ' a ' . htmlspecialchars($filtros['fecha_fin']);
                    } elseif (!empty($filtros['fecha_inicio'])) {
                        echo 'Desde ' . htmlspecialchars($filtros['fecha_inicio']);
                    } elseif (!empty($filtros['fecha_fin'])) {
                        echo 'Hasta ' . htmlspecialchars($filtros['fecha_fin']);
                    }
                ?>
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Nombre del Proveedor</th>
                <th>Número de Orden</th>
                <th>Nombre de Banco</th>
                <th>Número de Cuenta</th>
                <th>Tipo de Cuenta</th>
                <th>NIT / Identificación</th>
                <th>Valor a Pagar ($)</th>
                <th>Cliente a Entregar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datosExcel as $row): ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['proveedor'] ?? '') ?></strong></td>
                <td><?= (int)$row['numero_po'] ?></td>
                <td><?= htmlspecialchars($row['banco_nombre'] ?? '') ?></td>
                <!-- Usar formato de texto para evitar que excel lo convierta a notación científica -->
                <td style="mso-number-format:'\@';"><?= htmlspecialchars($row['banco_cuenta'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['banco_tipo_cuenta'] ?? '') ?></td>
                <td style="mso-number-format:'\@';"><?= htmlspecialchars($row['nit'] ?? '') ?></td>
                <td class="money"><?= number_format((float)$row['valor_pagar'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['cliente'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
