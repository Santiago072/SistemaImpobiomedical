<?php
/**
 * DeletableRepositoryInterface — Contrato segregado para operaciones de eliminación (ISP / SOLID).
 */
interface DeletableRepositoryInterface
{
    public function eliminar(int $id): bool;
}
