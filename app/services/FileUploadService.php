<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * FileUploadService — Responsabilidad Única: manejar la subida y reemplazo de archivos.
 *
 * Principios aplicados:
 *   - SRP: toda la lógica de archivos vive aquí.
 *   - OCP: para cambiar la lógica de subida solo se modifica esta clase.
 */
class FileUploadService
{
    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
    }

    /**
     * Sube un nuevo archivo. Devuelve su nombre en disco o el nombre actual si falla.
     */
    public function subir(array $fileInput, string $nombreActual = ''): string
    {
        if (!isset($fileInput['error']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
            return basename($nombreActual);
        }

        $validacion = validar_imagen($fileInput);
        if (!$validacion['valido']) {
            return basename($nombreActual);
        }

        $ext    = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
        $nombre = generar_nombre_archivo($ext);

        $this->asegurarDirectorio();

        if (move_uploaded_file($fileInput['tmp_name'], $this->uploadDir . $nombre)) {
            return $nombre;
        }

        return basename($nombreActual);
    }

    /**
     * Reemplaza un archivo existente con uno nuevo.
     * Si la subida es exitosa, elimina el archivo anterior.
     */
    public function reemplazar(array $fileInput, string $nombreActual = ''): string
    {
        if (!isset($fileInput['error']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
            return basename($nombreActual);
        }

        $validacion = validar_imagen($fileInput);
        if (!$validacion['valido']) {
            return basename($nombreActual);
        }

        $ext    = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
        $nombre = generar_nombre_archivo($ext);

        $this->asegurarDirectorio();

        if (move_uploaded_file($fileInput['tmp_name'], $this->uploadDir . $nombre)) {
            $this->eliminarSiExiste($nombreActual);
            return $nombre;
        }

        return basename($nombreActual);
    }

    public function eliminarSiExiste(string $nombre): void
    {
        if (empty($nombre)) return;
        $ruta = $this->uploadDir . basename($nombre);
        if (file_exists($ruta)) {
            @unlink($ruta);
        }
    }

    private function asegurarDirectorio(): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
}
