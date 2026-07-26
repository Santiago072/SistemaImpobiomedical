<?php
/**
 * Vista: Exportar Productos a Excel
 * Variables: $productos
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #000000; padding: 5px; text-align: left; vertical-align: top; }
        th { background-color: #1f3864; color: #ffffff; font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Código del Producto</th>
                <th>Categoría</th>
                <th>Nombre del Producto</th>
                <th>Descripción</th>
                <th>¿Aplica IVA?</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $row): ?>
            <tr>
                <td style="mso-number-format:'\@';"><?= htmlspecialchars($row['codigo_producto'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['titulo'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($row['descripcion'] ?? '')) ?></td>
                <td><?= (strtolower($row['iva'] ?? '') === 'si') ? 'Sí' : 'No' ?></td>
                <td><?= (strtolower($row['estado'] ?? '') === 'activo') ? 'Activo' : 'Inactivo' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
