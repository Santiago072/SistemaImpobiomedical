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
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #10757e; color: #ffffff; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        td { font-size: 12px; }
        .codigo { font-weight: bold; color: #0f766e; }
        .tag-activo { color: #166534; font-weight: bold; }
        .tag-inactivo { color: #991b1b; font-weight: bold; }
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
                <td class="codigo" style="mso-number-format:'\@';"><?= htmlspecialchars($row['codigo_producto'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                <td><strong><?= htmlspecialchars($row['titulo'] ?? '') ?></strong></td>
                <td><?= nl2br(htmlspecialchars($row['descripcion'] ?? '')) ?></td>
                <td><?= (strtolower($row['iva'] ?? '') === 'si') ? 'Sí' : 'No' ?></td>
                <td class="<?= (strtolower($row['estado'] ?? '') === 'activo') ? 'tag-activo' : 'tag-inactivo' ?>">
                    <?= (strtolower($row['estado'] ?? '') === 'activo') ? 'Activo' : 'Inactivo' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
