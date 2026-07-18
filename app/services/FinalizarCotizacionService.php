<?php

/**
 * FinalizarCotizacionService — Maneja la lógica de negocio para finalizar una cotización.
 * Principio SRP: Se encarga exclusivamente de procesar los datos del cliente (crear/actualizar)
 * y finalizar el documento de la cotización.
 */
class FinalizarCotizacionService
{
    private CotizacionModel $cotizacionModel;
    private ClienteModel $clienteModel;

    public function __construct(CotizacionModel $cotizacionModel, ClienteModel $clienteModel)
    {
        $this->cotizacionModel = $cotizacionModel;
        $this->clienteModel = $clienteModel;
    }

    /**
     * Procesa los datos finales, actualiza el cliente si es necesario, y finaliza la cotización.
     *
     * @param int $cotizacion_id ID de la cotización en borrador.
     * @param array $postData Datos enviados en el formulario.
     * @param array $sessionData Datos de la sesión actual (usuario, rol, etc).
     * @return string El número de cotización generado.
     */
    public function procesarFinalizacion(int $cotizacion_id, array $postData, array $sessionData): string
    {
        $fechaCreacion    = mb_substr(sanitizar_entrada($postData['fecha_creacion'] ?? date('Y-m-d')), 0, 10);
        $diasValidez      = max(1, (int)($postData['dias_validez'] ?? 30));
        $condicionesPago  = mb_substr(sanitizar_entrada($postData['condiciones_pago'] ?? 'CONTADO'), 0, 100);
        $observaciones    = mb_substr(sanitizar_entrada($postData['observaciones'] ?? ''), 0, 1000);
        
        $clienteId        = validar_numero($postData['cliente_id'] ?? '') ? (int)$postData['cliente_id'] : null;
        $clienteNombre    = mb_substr(sanitizar_entrada($postData['cliente_nombre'] ?? ''), 0, 200);
        $clienteNit       = mb_substr(sanitizar_entrada($postData['cliente_nit'] ?? ''), 0, 30);
        $clienteDireccion = mb_substr(sanitizar_entrada($postData['cliente_direccion'] ?? ''), 0, 200);
        $clienteTelefono  = mb_substr(sanitizar_entrada($postData['cliente_telefono'] ?? ''), 0, 30);
        $clienteCorreo    = mb_substr(sanitizar_entrada($postData['cliente_correo'] ?? ''), 0, 100);
        $clienteContacto  = mb_substr(sanitizar_entrada($postData['cliente_contacto'] ?? ''), 0, 100);
        $clienteCiudad    = mb_substr(sanitizar_entrada($postData['cliente_ciudad'] ?? ''), 0, 100);

        if ($clienteId === null && !empty($clienteNit)) {
            $clienteExistente = $this->clienteModel->buscarPorNit($clienteNit);
            if ($clienteExistente) {
                $clienteId = (int)$clienteExistente['id'];
                if (!empty($clienteNombre) || !empty($clienteCiudad) || !empty($clienteDireccion) || !empty($clienteContacto) || !empty($clienteTelefono) || !empty($clienteCorreo)) {
                    $this->clienteModel->actualizar(
                        $clienteId,
                        !empty($clienteNombre) ? $clienteNombre : $clienteExistente['nombre'],
                        $clienteNit,
                        $clienteExistente['departamento'],
                        !empty($clienteCiudad) ? $clienteCiudad : $clienteExistente['municipio'],
                        !empty($clienteDireccion) ? $clienteDireccion : $clienteExistente['direccion'],
                        !empty($clienteContacto) ? $clienteContacto : $clienteExistente['nombre_contacto'],
                        !empty($clienteTelefono) ? $clienteTelefono : $clienteExistente['telefono'],
                        !empty($clienteCorreo) ? $clienteCorreo : $clienteExistente['correo']
                    );
                }
            } else {
                $clienteId = $this->clienteModel->crear(
                    $clienteNombre, $clienteNit, '', $clienteCiudad, 
                    $clienteDireccion, $clienteContacto, $clienteTelefono, $clienteCorreo
                );
            }
        }

        $numeroCotizacion = $this->cotizacionModel->finalizarCotizacion(
            $cotizacion_id, $fechaCreacion, $diasValidez, $condicionesPago, $observaciones,
            $clienteNombre, $clienteNit, $clienteDireccion, $clienteTelefono,
            $clienteCorreo, $clienteContacto, $clienteCiudad, $clienteId,
            $sessionData['usuario_nombre'] ?? '',
            $sessionData['usuario_cargo'] ?? '',
            $sessionData['usuario_codigo'] ?? ''
        );

        return $numeroCotizacion;
    }
}
