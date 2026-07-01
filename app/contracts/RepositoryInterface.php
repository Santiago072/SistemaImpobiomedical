<?php
/**
 * RepositoryInterface — Contrato base para los modelos (Principio ISP/SOLID).
 * Los modelos que representen entidades simples implementan esta interfaz.
 */
interface RepositoryInterface
{
    public function listar(int $offset, int $limite, string $busqueda = ''): array;
    public function contar(string $busqueda = ''): int;
    public function buscarPorId(int $id): ?array;
    public function eliminar(int $id): bool;
}
