<?php
/**
 * Vista: Exportación a Excel de Cotización (Sin Imágenes) — Sistema Impobiomedical
 * Genera un archivo .xls ligero y rápido, compatible con Microsoft Excel, LibreOffice y Google Sheets.
 */

try {
    $data = $ctrl->generarPdf();
} catch (Exception $e) {
    http_response_code(500);
    die('Error al exportar cotización: ' . htmlspecialchars($e->getMessage()));
}

$cotizacion = $data['cotizacion'];
$items      = $data['items'];

if (empty($items)) {
    die('La cotización no tiene ítems.');
}

$numero           = $cotizacion['numero_cotizacion'] ?? 'SIN_NUMERO';
$fecha            = $cotizacion['fecha_creacion'] ?? date('Y-m-d');
$diasValidez      = (int)($cotizacion['dias_validez'] ?? 30);
$fechaValidez     = $cotizacion['fecha_validez'] ?? date('Y-m-d', strtotime("$fecha + $diasValidez days"));
$condicionesPago  = $cotizacion['condiciones_pago'] ?? 'CONTADO';
$observaciones    = $cotizacion['observaciones'] ?? '';

$clienteNombre    = $cotizacion['cliente_nombre'] ?? '';
$clienteNit       = $cotizacion['cliente_nit'] ?? '';
$clienteTel       = $cotizacion['cliente_telefono'] ?? '';
$clienteEmail     = $cotizacion['cliente_correo'] ?? '';
$clienteContacto  = $cotizacion['cliente_contacto'] ?? '';
$clienteCiudad    = $cotizacion['cliente_ciudad'] ?? '';
$clienteDir       = $cotizacion['cliente_direccion'] ?? '';

$asesorNombre     = $cotizacion['asesor_nombre'] ?? '';
$asesorCargo      = $cotizacion['asesor_cargo'] ?? '';

date_default_timezone_set('America/Bogota');
$fechaFmt = date('d/m/Y', strtotime($fecha));
$validezFmt = date('d/m/Y', strtotime($fechaValidez));

// Cálculos
$valorBase = 0;
$valorIva  = 0;
foreach ($items as $it) {
    $pu       = (float)$it['precio'];
    $qty      = (int)$it['cantidad'];
    $pct      = (float)($it['porcentaje_iva'] ?? 19);
    $aplica   = strtolower($it['iva'] ?? 'si') === 'si';
    $subtotal = $pu * $qty;
    $ivaFila  = $aplica ? $subtotal * ($pct / 100) : 0;
    $valorBase += $subtotal;
    $valorIva  += $ivaFila;
}
$totalFinal = $valorBase + $valorIva;

// Limpiar buffers para descarga limpia
while (ob_get_level() > 0) {
    ob_end_clean();
}

$safeNumero = preg_replace('/[^A-Za-z0-9_\-]/', '_', $numero);
$nombreArchivo = 'Cotizacion_' . $safeNumero . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Calibri, Arial, sans-serif; font-size: 10pt; color: #1f2937; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .hdr-title { font-size: 15pt; font-weight: bold; color: #10757e; text-align: left; }
    .hdr-sub { font-size: 9pt; color: #475569; }
    .hdr-num { font-size: 14pt; font-weight: bold; color: #1f3864; text-align: center; }
    .sec-header { background-color: #1a8a8a; color: #ffffff; font-weight: bold; padding: 6px 8px; font-size: 10pt; }
    .sec-sub-header { background-color: #e8f4f4; color: #1a3a5c; font-weight: bold; }
    .lbl { font-weight: bold; color: #1a3a5c; background-color: #f1f5f9; }
    th { background-color: #1a8a8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #0d5c63; padding: 7px 5px; font-size: 9.5pt; }
    td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: middle; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .money { text-align: right; font-weight: 600; }
    .row-even { background-color: #f8fafc; }
    .row-odd { background-color: #ffffff; }
    .total-lbl { background-color: #e8f4f4; font-weight: bold; text-align: right; }
    .total-val { background-color: #ffffff; font-weight: bold; text-align: right; }
    .grand-lbl { background-color: #1a8a8a; color: #ffffff; font-weight: bold; text-align: right; font-size: 11pt; }
    .grand-val { background-color: #e0f2fe; color: #0369a1; font-weight: bold; text-align: right; font-size: 12pt; }
    .note-box { background-color: #fefce8; border: 1px solid #fef08a; padding: 8px; font-size: 8.5pt; color: #713f12; }
    .foot-box { background-color: #e8f4f4; border: 1px solid #99f6e4; font-weight: bold; text-align: center; font-size: 9pt; color: #115e59; padding: 6px; }
</style>
</head>
<body>

<!-- ENCABEZADO EMPRESA Y COTIZACIÓN -->
<table>
    <tr>
        <td colspan="5" style="border:none;">
            <div class="hdr-title">IMPOMIN S.A.S — IMPOBIOMEDICAL</div>
            <div class="hdr-sub">NIT: 900.535.843-3</div>
            <div class="hdr-sub">Cra. 10 #9-80 Barrio Cooperativa Florencia-Caquetá</div>
            <div class="hdr-sub">Cl. 61 Sur #43a85 - Sabaneta - Antioquia - Colombia</div>
            <div class="hdr-sub">Telefax: (4) 322 27 79 | Cels: 317 345 3644 / 310 269 0595</div>
            <div class="hdr-sub">Email: impobiomedical@impomin.com | Web: https://impobiomedical.impomin.com/</div>
        </td>
        <td colspan="4" style="border: 2px solid #1a8a8a; text-align: center; vertical-align: middle; background-color: #f0fdfa;">
            <div style="font-size: 9.5pt; font-weight: bold; color: #0f766e;">COTIZACIÓN N°</div>
            <div class="hdr-num"><?= htmlspecialchars($numero) ?></div>
            <div style="font-size: 8.5pt; color: #64748b; margin-top: 4px;">Fecha: <?= $fechaFmt ?></div>
        </td>
    </tr>
</table>

<!-- DATOS DEL CLIENTE Y ASESOR -->
<table>
    <tr>
        <th colspan="9" class="sec-header text-left">INFORMACIÓN DEL CLIENTE Y ASESOR</th>
    </tr>
    <tr>
        <td class="lbl" style="width: 12%;">CLIENTE:</td>
        <td colspan="4" style="font-weight: bold;"><?= mb_strtoupper(htmlspecialchars($clienteNombre)) ?></td>
        <td class="lbl" style="width: 12%;">FECHA:</td>
        <td colspan="3"><?= $fechaFmt ?></td>
    </tr>
    <tr>
        <td class="lbl">NIT / C.C.:</td>
        <td colspan="4"><?= htmlspecialchars($clienteNit ?: 'No especificado') ?></td>
        <td class="lbl">VALIDEZ:</td>
        <td colspan="3"><?= $diasValidez ?> DÍAS (Hasta <?= $validezFmt ?>)</td>
    </tr>
    <tr>
        <td class="lbl">CONTACTO:</td>
        <td colspan="4"><?= mb_strtoupper(htmlspecialchars($clienteContacto ?: '-')) ?></td>
        <td class="lbl">FORMA DE PAGO:</td>
        <td colspan="3"><?= mb_strtoupper(htmlspecialchars($condicionesPago)) ?></td>
    </tr>
    <tr>
        <td class="lbl">TELÉFONO / CEL:</td>
        <td colspan="4"><?= htmlspecialchars($clienteTel ?: '-') ?></td>
        <td class="lbl">ASESOR:</td>
        <td colspan="3"><?= mb_strtoupper(htmlspecialchars($asesorNombre)) ?></td>
    </tr>
    <tr>
        <td class="lbl">CORREO:</td>
        <td colspan="4"><?= htmlspecialchars($clienteEmail ?: '-') ?></td>
        <td class="lbl">CARGO:</td>
        <td colspan="3"><?= mb_strtoupper(htmlspecialchars($asesorCargo)) ?></td>
    </tr>
    <?php if (!empty($clienteDir) || !empty($clienteCiudad)): ?>
    <tr>
        <td class="lbl">DIRECCIÓN / CIUDAD:</td>
        <td colspan="8"><?= htmlspecialchars(trim($clienteDir . ' ' . $clienteCiudad)) ?></td>
    </tr>
    <?php endif; ?>
</table>

<!-- TABLA DE PRODUCTOS (SIN IMÁGENES PARA MÁXIMA RAPIDEZ) -->
<table>
    <thead>
        <tr>
            <th style="width: 5%;">ITEM</th>
            <th style="width: 6%;">CANT</th>
            <th style="width: 10%;">CÓDIGO</th>
            <th style="width: 35%;">DESCRIPCIÓN DEL PRODUCTO / SERVICIO</th>
            <th style="width: 7%;">% IVA</th>
            <th style="width: 11%;">VALOR UNITARIO</th>
            <th style="width: 11%;">IVA FILA</th>
            <th style="width: 12%;">TOTAL CON IVA</th>
            <th style="width: 10%;">ENTREGA</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $i = 0;
        foreach ($items as $it): 
            $i++;
            $pu       = (float)$it['precio'];
            $qty      = (int)$it['cantidad'];
            $pct      = (float)($it['porcentaje_iva'] ?? 19);
            $aplica   = strtolower($it['iva'] ?? 'si') === 'si';
            $subtotal = $pu * $qty;
            $ivaFila  = $aplica ? $subtotal * ($pct / 100) : 0;
            $totalFila= $subtotal + $ivaFila;
            $rowCls   = ($i % 2 === 0) ? 'row-even' : 'row-odd';

            $descCompleta = trim($it['titulo']);
            if (!empty($it['descripcion'])) {
                $descCompleta .= "\n" . trim($it['descripcion']);
            }
        ?>
        <tr class="<?= $rowCls ?>">
            <td class="text-center"><?= $i ?></td>
            <td class="text-center" style="font-weight: bold;"><?= $qty ?></td>
            <td class="text-center"><?= htmlspecialchars($it['codigo_producto'] ?? $it['codigo_proveedor'] ?? '-') ?></td>
            <td>
                <strong><?= htmlspecialchars(mb_strtoupper($it['titulo'])) ?></strong>
                <?php if (!empty($it['descripcion'])): ?>
                <br><span style="font-size: 8.5pt; color: #4b5563;"><?= nl2br(htmlspecialchars($it['descripcion'])) ?></span>
                <?php endif; ?>
            </td>
            <td class="text-center"><?= $aplica ? number_format($pct, 0) . '%' : '0%' ?></td>
            <td class="money">$ <?= number_format($pu, 0, ',', '.') ?></td>
            <td class="money">$ <?= number_format($ivaFila, 0, ',', '.') ?></td>
            <td class="money" style="font-weight: bold; color: #047857;">$ <?= number_format($totalFila, 0, ',', '.') ?></td>
            <td class="text-center"><?= htmlspecialchars($it['tiempo_entrega'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>

        <!-- TOTALES -->
        <tr>
            <td colspan="5" rowspan="3" class="note-box" style="vertical-align: top;">
                <strong>CONDICIONES DE ENTREGA:</strong><br>
                * El tiempo de entrega cuenta a partir del recibo de la orden de compra. Sujeto a verificación de disponibilidad de existencia en el momento de confirmación de envío.<br>
                <?php if (!empty($observaciones)): ?>
                <br><strong>OBSERVACIONES:</strong><br>
                <?= nl2br(htmlspecialchars($observaciones)) ?>
                <?php endif; ?>
            </td>
            <td colspan="2" class="total-lbl">SUBTOTAL BASE:</td>
            <td colspan="2" class="total-val">$ <?= number_format($valorBase, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td colspan="2" class="total-lbl">VALOR IVA:</td>
            <td colspan="2" class="total-val">$ <?= number_format($valorIva, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td colspan="2" class="grand-lbl">TOTAL A PAGAR:</td>
            <td colspan="2" class="grand-val">$ <?= number_format($totalFinal, 0, ',', '.') ?></td>
        </tr>
    </tbody>
</table>

<!-- PIE DE PÁGINA COMERCIAL Y BANCARIO -->
<table>
    <tr>
        <td class="foot-box">TRANSPORTE CONTRAENTREGA A TODO EL PAÍS</td>
    </tr>
    <tr>
        <td class="foot-box" style="background-color: #f0fdfa; color: #0f766e;">
            FAVOR CONSIGNAR A NOMBRE DE IMPOMIN S.A.S A LA CUENTA DE AHORROS BANCOLOMBIA # 34 413745006
        </td>
    </tr>
</table>

</body>
</html>
