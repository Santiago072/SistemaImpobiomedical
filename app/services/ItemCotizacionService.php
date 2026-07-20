<?php

/**
 * ItemCotizacionService — Maneja la lógica de negocio para los ítems de las cotizaciones.
 * Principio SRP: Se encarga exclusivamente de validar, procesar imágenes e insertar/actualizar ítems.
 */
class ItemCotizacionService
{
    private CotizacionModel $cotizacionModel;
    private ProductoModel $productoModel;
    private FileUploadService $uploader;

    public function __construct(
        CotizacionModel $cotizacionModel,
        ProductoModel $productoModel,
        FileUploadService $uploader
    ) {
        $this->cotizacionModel = $cotizacionModel;
        $this->productoModel = $productoModel;
        $this->uploader = $uploader;
    }

    /**
     * Procesa y guarda un ítem (nuevo o existente) en una cotización.
     * También actualiza/crea el producto en el catálogo si es necesario.
     *
     * @param int $cotizacion_id ID de la cotización actual.
     * @param array $postData Datos enviados por POST.
     * @param array $fileData Datos enviados por FILES.
     * @throws \RuntimeException Si falla la inserción en BD.
     */
    public function guardarItem(int $cotizacion_id, array $postData, array $fileData): void
    {
        $producto_id         = validar_numero($postData['producto_id'] ?? '') ? (int)$postData['producto_id'] : null;
        $titulo              = mb_substr(sanitizar_entrada($postData['titulo'] ?? ''), 0, 255);
        $descripcion         = mb_substr(sanitizar_entrada($postData['descripcion'] ?? ''), 0, 5000);
        $cantidad            = max(1, (int)($postData['cantidad'] ?? 1));
        $precio              = (float)($postData['precio'] ?? 0);
        $iva                 = mb_substr(sanitizar_entrada($postData['iva'] ?? 'si'), 0, 5);
        $porcentaje_iva      = (float)($postData['porcentaje_iva'] ?? 19);
        $tiempo_entrega      = mb_substr(sanitizar_entrada($postData['tiempo_entrega'] ?? ''), 0, 120);
        $categoria           = mb_substr(sanitizar_entrada($postData['categoria'] ?? ''), 0, 100);
        $codigo_producto     = mb_substr(sanitizar_entrada($postData['codigo_producto'] ?? ''), 0, 60);
        $precio_proveedor    = (float)($postData['precio_proveedor'] ?? 0);
        $porcentaje_utilidad = (float)($postData['porcentaje_utilidad'] ?? 0);
        $flete               = (float)($postData['flete'] ?? 0);
        $calibracion         = (float)($postData['calibracion'] ?? 0);
        $estampillas         = (float)($postData['estampillas'] ?? 0);
        $proveedor           = mb_substr(sanitizar_entrada($postData['proveedor'] ?? ''), 0, 100);
        $codigo_proveedor    = mb_substr(sanitizar_entrada($postData['codigo_proveedor'] ?? ''), 0, 60);
        
        $calc_ops_raw = $postData['calc_ops'] ?? '{}';
        $calc_ops_decoded = json_decode($calc_ops_raw, true);
        $calc_ops = ($calc_ops_decoded === null) ? '{}' : json_encode($calc_ops_decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!in_array($iva, ['si', 'no'], true)) {
            $iva = 'si';
        }

        $producto = null;
        if ($producto_id !== null) {
            $producto = $this->productoModel->buscarPorId($producto_id);
        }
        
        if ($producto !== null && !empty($producto['foto'])) {
            $foto = $producto['foto'];
        } else {
            $fileInput = $fileData['foto'] ?? [];
            $fotoActual = $postData['foto_actual'] ?? '';
            
            if (!isset($fileInput['error']) || $fileInput['error'] === UPLOAD_ERR_NO_FILE) {
                $foto = basename($fotoActual);
            } else {
                $foto = $this->uploader->subir($fileInput, $fotoActual);
            }
        }

        $inserted = $this->cotizacionModel->insertarItem(
            $cotizacion_id, $producto_id, $titulo, $foto,
            $descripcion, $cantidad, $precio, $iva, $porcentaje_iva, $tiempo_entrega,
            $categoria, $codigo_producto, $precio_proveedor, $porcentaje_utilidad,
            $flete, $calibracion, $estampillas, $proveedor, $codigo_proveedor, $calc_ops
        );

        if (!$inserted) {
            throw new \RuntimeException('No se pudo guardar el ítem en la base de datos.');
        }

        if ($producto_id === null) {
            $productoExistente = $this->productoModel->buscarPorTitulo($titulo);
            if (!$productoExistente) {
                // El precio unitario NO se guarda en el catálogo (varía por cotización).
                $this->productoModel->crear($titulo, $foto, $descripcion, 0, $iva, $porcentaje_iva, $categoria, $codigo_producto);
            } else {
                if (empty($foto) && !empty($productoExistente['foto'])) {
                    $foto = $productoExistente['foto'];
                }
                // Actualizar info del catálogo SIN sobreescribir precio (no se almacena)
                if (!empty($foto) || !empty($descripcion) || !empty($codigo_producto)) {
                    $this->productoModel->actualizar(
                        (int)$productoExistente['id'],
                        $titulo,
                        !empty($foto) ? $foto : $productoExistente['foto'],
                        !empty($descripcion) ? $descripcion : $productoExistente['descripcion'],
                        0,                    // precio no se persiste en el catálogo
                        $iva,
                        $porcentaje_iva,
                        'activo',
                        !empty($categoria) ? $categoria : $productoExistente['categoria'],
                        !empty($codigo_producto) ? $codigo_producto : $productoExistente['codigo_producto']
                    );
                }
            }
        }
    }

    /**
     * Procesa y actualiza un ítem existente en una cotización.
     *
     * @param int $itemId ID del ítem de la cotización.
     * @param int $cotizacion_id ID de la cotización actual.
     * @param array $postData Datos enviados por POST.
     * @param array $fileData Datos enviados por FILES.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function actualizarItem(int $itemId, int $cotizacion_id, array $postData, array $fileData): bool
    {
        $titulo              = mb_substr(sanitizar_entrada($postData['titulo'] ?? ''), 0, 255);
        $descripcion         = mb_substr(sanitizar_entrada($postData['descripcion'] ?? ''), 0, 5000);
        $cantidad            = max(1, (int)($postData['cantidad'] ?? 1));
        $precio              = (float)($postData['precio'] ?? 0);
        $iva                 = mb_substr(sanitizar_entrada($postData['iva'] ?? 'si'), 0, 5);
        $porcentaje_iva      = (float)($postData['porcentaje_iva'] ?? 19);
        $tiempo_entrega      = mb_substr(sanitizar_entrada($postData['tiempo_entrega'] ?? ''), 0, 120);
        $categoria           = mb_substr(sanitizar_entrada($postData['categoria'] ?? ''), 0, 100);
        $codigo_producto     = mb_substr(sanitizar_entrada($postData['codigo_producto'] ?? ''), 0, 60);
        $precio_proveedor    = (float)($postData['precio_proveedor'] ?? 0);
        $porcentaje_utilidad = (float)($postData['porcentaje_utilidad'] ?? 0);
        $flete               = (float)($postData['flete'] ?? 0);
        $calibracion         = (float)($postData['calibracion'] ?? 0);
        $estampillas         = (float)($postData['estampillas'] ?? 0);
        $proveedor           = mb_substr(sanitizar_entrada($postData['proveedor'] ?? ''), 0, 100);
        $codigo_proveedor    = mb_substr(sanitizar_entrada($postData['codigo_proveedor'] ?? ''), 0, 60);
        
        $calc_ops_raw = $postData['calc_ops'] ?? '{}';
        $calc_ops_decoded = json_decode($calc_ops_raw, true);
        $calc_ops = ($calc_ops_decoded === null) ? '{}' : json_encode($calc_ops_decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!in_array($iva, ['si', 'no'], true)) {
            throw new \InvalidArgumentException('IVA no válido');
        } elseif ($cantidad <= 0 || $precio < 0) {
            throw new \InvalidArgumentException('Cantidad y precio deben ser valores válidos');
        }

        $foto = $this->uploader->reemplazar($fileData['foto'] ?? [], $postData['foto_actual'] ?? '');

        return $this->cotizacionModel->actualizarItem(
            $itemId, $cotizacion_id, $titulo, $foto,
            $descripcion, $cantidad, $precio, $iva, $porcentaje_iva, $tiempo_entrega,
            $categoria, $codigo_producto, $precio_proveedor, $porcentaje_utilidad,
            $flete, $calibracion, $estampillas, $proveedor, $codigo_proveedor, $calc_ops
        );
    }
}
