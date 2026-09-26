<?php
/**
 * Partial reutilizable: alerta_error.php
 * Uso: $alertaErrorMsg = $mensajeError; include dirname(__DIR__)."/layout/alerta_error.php";
 */
if (empty($alertaErrorMsg)) return;

$_mapErrores = [
    'csrf'                                          => ['icono'=>'bi-shield-lock-fill',          'titulo'=>'Sesión de seguridad expirada',             'desc'=>'Tu token de seguridad venció por inactividad.',                                      'sug'=>'Recarga la página con <strong>F5</strong> y vuelve a intentarlo.'],
    'Token de seguridad'                            => ['icono'=>'bi-shield-lock-fill',          'titulo'=>'Sesión de seguridad expirada',             'desc'=>'El token de seguridad no es válido o ya expiró.',                                    'sug'=>'Recarga la página con <strong>F5</strong> y vuelve a intentarlo.'],
    'proveedor es obligatorio'                      => ['icono'=>'bi-building-exclamation',      'titulo'=>'Falta el proveedor',                       'desc'=>'No se indicó el nombre del proveedor antes de guardar.',                             'sug'=>'Escribe el nombre o NIT del proveedor en el campo <strong>Proveedor</strong> e intenta de nuevo.'],
    'Debe indicar el nombre del proveedor'          => ['icono'=>'bi-building-exclamation',      'titulo'=>'Falta el nombre del proveedor',            'desc'=>'El campo proveedor no puede quedar vacío al generar una orden.',                    'sug'=>'Escribe el nombre del proveedor o búscalo con el buscador predictivo.'],
    'Debe agregar al menos un producto'             => ['icono'=>'bi-cart-x-fill',               'titulo'=>'Sin productos en la orden',                'desc'=>'No se agregó ningún producto a la orden de compra.',                                'sug'=>'Usa el buscador de productos para agregar al menos un ítem antes de guardar.'],
    'Debe seleccionar al menos un ítem'             => ['icono'=>'bi-ui-checks',                 'titulo'=>'Ningún ítem seleccionado',                 'desc'=>'Debes marcar al menos un producto de la cotización para generar la orden.',        'sug'=>'Activa la casilla ☑ de los ítems que deseas incluir en la orden de compra.'],
    'Debe seleccionar al menos una orden'           => ['icono'=>'bi-check2-square',             'titulo'=>'Ninguna orden seleccionada',               'desc'=>'Debes marcar al menos una orden de compra para realizar esta acción.',            'sug'=>'Activa la casilla junto a la(s) orden(es) que deseas exportar o generar en PDF.'],
    'distintos proveedores'                         => ['icono'=>'bi-people-fill',               'titulo'=>'Proveedores mixtos en la selección',       'desc'=>'Seleccionaste ítems de diferentes proveedores. Una orden solo puede tener uno.', 'sug'=>'Filtra los ítems por proveedor y genera una orden separada para cada uno.'],
    'nombre del cliente es obligatorio'             => ['icono'=>'bi-person-exclamation',        'titulo'=>'Falta el nombre del cliente',              'desc'=>'El campo nombre del cliente no puede estar vacío al finalizar la cotización.',   'sug'=>'Escribe el nombre del cliente o búscalo en el catálogo del formulario.'],
    'NIT/CC ya está registrado'                     => ['icono'=>'bi-fingerprint',               'titulo'=>'NIT / CC duplicado',                       'desc'=>'Ya existe un cliente registrado con ese número de identificación.',               'sug'=>'Busca el cliente existente en el listado o usa un número diferente.'],
    'NIT ya se encuentra registrado'                => ['icono'=>'bi-fingerprint',               'titulo'=>'NIT de proveedor duplicado',               'desc'=>'Ya existe un proveedor con ese NIT en el sistema.',                              'sug'=>'Busca el proveedor en el listado o edítalo si necesitas actualizar su información.'],
    'NIT ya pertenece a otro proveedor'             => ['icono'=>'bi-fingerprint',               'titulo'=>'NIT asignado a otro proveedor',            'desc'=>'El NIT que ingresaste ya está asociado a un proveedor diferente.',              'sug'=>'Revisa el listado de proveedores o usa un NIT diferente.'],
    'NIT y el Nombre del Proveedor son obligatorios'=> ['icono'=>'bi-building-exclamation',      'titulo'=>'Campos obligatorios incompletos',          'desc'=>'El NIT y el nombre del proveedor son requeridos.',                               'sug'=>'Completa los campos <strong>NIT</strong> y <strong>Nombre</strong> antes de guardar.'],
    'correo electrónico no es válido'               => ['icono'=>'bi-envelope-exclamation-fill', 'titulo'=>'Correo electrónico inválido',              'desc'=>'El formato del correo ingresado no es correcto.',                                'sug'=>'Verifica que el correo tenga el formato <em>nombre@dominio.com</em> e intenta de nuevo.'],
    'Datos insuficientes'                           => ['icono'=>'bi-clipboard-x-fill',          'titulo'=>'Datos incompletos',                        'desc'=>'No se enviaron todos los datos requeridos.',                                     'sug'=>'Completa todos los campos marcados con <strong>*</strong> antes de guardar.'],
    'No tienes permisos'                            => ['icono'=>'bi-lock-fill',                 'titulo'=>'Acción no permitida',                      'desc'=>'Tu usuario no tiene los permisos necesarios para esta operación.',               'sug'=>'Contacta al administrador si crees que deberías tener acceso.'],
    'cotización que está'                           => ['icono'=>'bi-file-earmark-lock2-fill',   'titulo'=>'Estado de cotización no compatible',       'desc'=>'',                                                                               'sug'=>'Solo puedes generar órdenes para cotizaciones en estado <strong>Pendiente</strong>.'],
    'demasiado grande'                              => ['icono'=>'bi-file-earmark-x-fill',       'titulo'=>'Imagen demasiado pesada',                  'desc'=>'El archivo de imagen supera el límite de <strong>8 MB</strong>.',               'sug'=>'Comprime la imagen en <a href="https://squoosh.app" target="_blank" rel="noopener">squoosh.app</a> antes de subirla.'],
    'Error al guardar'                              => ['icono'=>'bi-wifi-off',                  'titulo'=>'Error al guardar',                         'desc'=>'Ocurrió un problema al intentar registrar el dato.',                             'sug'=>'Verifica tu conexión a internet e intenta nuevamente.'],
    'Error al crear'                                => ['icono'=>'bi-wifi-off',                  'titulo'=>'Error al registrar',                       'desc'=>'No fue posible guardar el registro en este momento.',                            'sug'=>'Verifica tu conexión e intenta nuevamente.'],
    'Error al actualizar'                           => ['icono'=>'bi-arrow-clockwise',            'titulo'=>'Error al actualizar',                      'desc'=>'No fue posible aplicar los cambios.',                                            'sug'=>'Verifica los campos e intenta de nuevo. Si persiste, contacta al administrador.'],
    'Error inesperado'                              => ['icono'=>'bi-exclamation-octagon-fill',   'titulo'=>'Error inesperado',                         'desc'=>'Ocurrió un error no previsto.',                                                  'sug'=>'Toma una captura de pantalla y compártela con el administrador.'],
];

$_errInfo = null;
foreach ($_mapErrores as $_pat => $_info) {
    if (stripos($alertaErrorMsg, $_pat) !== false) {
        $_errInfo = $_info;
        if (empty($_errInfo['desc'])) $_errInfo['desc'] = htmlspecialchars($alertaErrorMsg);
        break;
    }
}
if (!$_errInfo) {
    $_errInfo = ['icono'=>'bi-exclamation-triangle-fill','titulo'=>'No fue posible completar la operación','desc'=>htmlspecialchars($alertaErrorMsg),'sug'=>'Toma una captura y compártela con el administrador si el problema persiste.'];
}
?>
<div class="mod-alert mod-alert-err" style="display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap;margin-bottom:16px;">
    <i class="bi <?= $_errInfo['icono'] ?>" style="font-size:1.6rem;flex-shrink:0;margin-top:2px;"></i>
    <div style="flex:1;min-width:0;">
        <strong style="display:block;font-size:1rem;margin-bottom:4px;"><?= $_errInfo['titulo'] ?></strong>
        <span style="display:block;color:#7f1d1d;margin-bottom:8px;line-height:1.5;"><?= $_errInfo['desc'] ?></span>
        <span style="display:flex;gap:6px;align-items:flex-start;font-size:0.85rem;color:#92400e;background:#fef3c7;border-radius:6px;padding:6px 10px;line-height:1.5;">
            <i class="bi bi-lightbulb-fill" style="color:#d97706;flex-shrink:0;margin-top:2px;"></i>
            <span><strong>Sugerencia:</strong> <?= $_errInfo['sug'] ?></span>
        </span>
    </div>
</div>
<?php unset($_mapErrores, $_errInfo, $_pat, $_info, $alertaErrorMsg);
