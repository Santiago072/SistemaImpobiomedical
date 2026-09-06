<?php
/**
 * ReadableRepositoryInterface — Contrato segregado para operaciones de lectura/consulta (ISP / SOLID).
 */
interface ReadableRepositoryInterface
{
    public function listar(int $offset, int $limite, string $busqueda = ''): array;
    public function contar(string $busqueda = ''): int;
    public function buscarPorId(int $id): ?array;
}
