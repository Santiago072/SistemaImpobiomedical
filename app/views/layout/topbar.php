<?php
/**
 * Topbar reutilizable — botón de menú + mensaje de bienvenida + etiqueta de rol + botón modo.
 * Variables esperadas:
 *   $pageHeading  string  — título h1 de la sección (opcional)
 *   $usuario      array   — datos del usuario (nombre, rol)
 *   $esDashboard  bool    — indica si es el dashboard principal
 */
$pageHeading = $pageHeading ?? '';
$usuario = $usuario ?? null;
$esDashboard = $esDashboard ?? false;
?>
<div class="cabecera-superior">
    <button class="boton-menu-ocultar" id="btnMenu">
        <i class="fas fa-bars"></i> Ocultar Menú
    </button>
    <div class="cabecera-bienvenida flex-1 pl-16">
        <?php if ($esDashboard && $usuario): 
            $codigoMostrar = !empty($usuario['codigo']) ? $usuario['codigo'] : ($_SESSION['usuario_codigo'] ?? '');
        ?>
        <h3 class="topbar-welcome-title">
            <span>¡Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>!</span>
            <?php if (!empty($codigoMostrar)): ?>
            <span class="badge-codigo">
                <?= htmlspecialchars($codigoMostrar) ?>
            </span>
            <?php endif; ?>
        </h3>
        <?php endif; ?>
        <?php if ($pageHeading): ?>
        <span class="page-heading">
            <?= htmlspecialchars($pageHeading) ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="header-actions-wrap align-center gap-16">
        <?php if ($esDashboard && $usuario): ?>
            <?php if ($usuario['rol'] === 'admin'): ?>
            <span class="rol-admin">
                <i class="bi bi-shield-check"></i> Administrador
            </span>
            <?php else: ?>
            <span class="rol-usuario">
                <i class="bi bi-person"></i> Usuario
            </span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
