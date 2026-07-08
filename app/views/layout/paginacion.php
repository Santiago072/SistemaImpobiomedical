<?php
/**
 * Componente reutilizable de paginación con controles "<" y ">"
 * y limitación de números para no extender la interfaz.
 * 
 * Variables requeridas en la vista que lo incluye:
 * - $paginaActual (int)
 * - $totalPaginas (int)
 * - $pagBaseUrl (string) - URL base a la que se le añadirá "&pagina=X"
 */
if (!isset($paginaActual) || !isset($totalPaginas) || !isset($pagBaseUrl)) {
    return;
}

if ($totalPaginas <= 1) {
    return;
}

$rango = 2; // Cuántas páginas a la izquierda y derecha de la actual mostrar
$inicio = max(1, $paginaActual - $rango);
$fin = min($totalPaginas, $paginaActual + $rango);
?>
<div class="mod-pag">
    <!-- Botón Anterior -->
    <?php if ($paginaActual > 1): ?>
        <a href="<?= $pagBaseUrl ?>&pagina=<?= $paginaActual - 1 ?>" class="mod-pag-link" title="Anterior">&laquo;</a>
    <?php endif; ?>

    <!-- Primera página y puntos suspensivos -->
    <?php if ($inicio > 1): ?>
        <a href="<?= $pagBaseUrl ?>&pagina=1" class="mod-pag-link">1</a>
        <?php if ($inicio > 2): ?>
            <span class="mod-pag-dots" style="padding: 0 8px; color: #555; align-self: center;">...</span>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Rango de páginas cercanas -->
    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
        <a href="<?= $pagBaseUrl ?>&pagina=<?= $i ?>" class="mod-pag-link <?= $i === $paginaActual ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <!-- Última página y puntos suspensivos -->
    <?php if ($fin < $totalPaginas): ?>
        <?php if ($fin < $totalPaginas - 1): ?>
            <span class="mod-pag-dots" style="padding: 0 8px; color: #555; align-self: center;">...</span>
        <?php endif; ?>
        <a href="<?= $pagBaseUrl ?>&pagina=<?= $totalPaginas ?>" class="mod-pag-link"><?= $totalPaginas ?></a>
    <?php endif; ?>

    <!-- Botón Siguiente -->
    <?php if ($paginaActual < $totalPaginas): ?>
        <a href="<?= $pagBaseUrl ?>&pagina=<?= $paginaActual + 1 ?>" class="mod-pag-link" title="Siguiente">&raquo;</a>
    <?php endif; ?>
</div>
