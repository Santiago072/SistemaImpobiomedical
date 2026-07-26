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
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #000000; padding: 5px; text-align: left; }
        th { background-color: #1f3864; color: #ffffff; font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Nombre del Proveedor</th>
                <th>Numero de Orden</th>
                <th>Nombre de Banco</th>
                <th>Numero de Cuenta</th>
                <th>Tipo de Cuenta</th>
                <th>Nit</th>
                <th>Valor a Pagar</th>
                <th>Cliente a Entregar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datosExcel as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['proveedor'] ?? '') ?></td>
                <td><?= (int)$row['numero_po'] ?></td>
                <td><?= htmlspecialchars($row['banco_nombre'] ?? '') ?></td>
                <!-- Usar formato de texto para evitar que excel lo convierta a notación científica -->
                <td style="mso-number-format:'\@';"><?= htmlspecialchars($row['banco_cuenta'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['banco_tipo_cuenta'] ?? '') ?></td>
                <td style="mso-number-format:'\@';"><?= htmlspecialchars($row['nit'] ?? '') ?></td>
                <td><?= number_format((float)$row['valor_pagar'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['cliente'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
