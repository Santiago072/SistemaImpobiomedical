<?php
/**
 * Vista: Exportar Productos a PDF
 * Variables: $productos
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 10px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 3px solid #10757e; padding-bottom: 15px; }
        .header h1 { margin: 0; color: #10757e; font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; color: #d97706; font-size: 12px; font-weight: bold; }
        .header .subtext { color: #64748b; font-weight: normal; margin-top: 2px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #e2e8f0; }
        th { background-color: #10757e; color: #ffffff; font-weight: bold; padding: 10px 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; border-right: 1px solid #f1f5f9; padding: 10px 8px; vertical-align: top; }
        
        /* Zebra striping for better readability */
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        td.col-codigo { width: 12%; font-weight: bold; color: #0f766e; }
        td.col-categoria { width: 13%; color: #64748b; font-size: 10px; font-style: italic; }
        td.col-nombre { width: 22%; font-weight: bold; color: #1e293b; }
        td.col-desc { width: 35%; font-size: 10px; color: #475569; line-height: 1.3; }
        td.col-iva { width: 8%; text-align: center; }
        td.col-estado { width: 10%; text-align: center; }
        
        .tag-activo { color: #166534; font-weight: bold; background-color: #dcfce7; padding: 3px 6px; border-radius: 4px; display: inline-block; font-size: 10px; }
        .tag-inactivo { color: #991b1b; font-weight: bold; background-color: #fee2e2; padding: 3px 6px; border-radius: 4px; display: inline-block; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Catálogo de Productos</h1>
        <p>Generado el: <?= date('d/m/Y') ?></p>
        <p class="subtext">Total de registros: <?= count($productos) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-codigo">Código</th>
                <th class="col-categoria">Categoría</th>
                <th class="col-nombre">Nombre</th>
                <th class="col-desc">Descripción</th>
                <th class="col-iva">IVA</th>
                <th class="col-estado">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td class="col-codigo"><?= htmlspecialchars($p['codigo_producto'] ?? '') ?></td>
                <td class="col-categoria"><?= htmlspecialchars($p['categoria'] ?? '') ?></td>
                <td class="col-nombre"><?= htmlspecialchars($p['titulo'] ?? '') ?></td>
                <td class="col-desc"><?= nl2br(htmlspecialchars($p['descripcion'] ?? '')) ?></td>
                <td class="col-iva"><?= (strtolower($p['iva'] ?? '') === 'si') ? 'Sí' : 'No' ?></td>
                <td class="col-estado">
                    <?php if (strtolower($p['estado'] ?? '') === 'activo'): ?>
                        <span class="tag-activo">Activo</span>
                    <?php else: ?>
                        <span class="tag-inactivo">Inactivo</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
