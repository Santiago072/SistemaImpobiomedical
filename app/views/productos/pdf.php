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
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2dbecb; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e293b; font-size: 20px; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #0f172a; font-weight: bold; border-bottom: 2px solid #cbd5e1; padding: 8px 6px; text-align: left; }
        td { border-bottom: 1px solid #e2e8f0; padding: 8px 6px; vertical-align: top; }
        
        .col-codigo { width: 15%; font-weight: bold; color: #0284c7; }
        .col-categoria { width: 15%; color: #64748b; font-size: 10px; }
        .col-nombre { width: 20%; font-weight: bold; }
        .col-desc { width: 35%; font-size: 10px; color: #475569; }
        .col-iva { width: 7%; text-align: center; }
        .col-estado { width: 8%; text-align: center; }
        
        .tag-activo { color: #166534; font-weight: bold; }
        .tag-inactivo { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Catálogo de Productos</h1>
        <p>Generado el: <?= date('d/m/Y H:i') ?></p>
        <p>Total de registros: <?= count($productos) ?></p>
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
