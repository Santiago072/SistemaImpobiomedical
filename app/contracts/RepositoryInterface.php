<?php
/**
 * RepositoryInterface — Contrato compuesto para repositorios completos (ISP y LSP / SOLID).
 * Extiende interfaces segregadas para mantener alta cohesión y bajo acoplamiento.
 */
interface RepositoryInterface extends ReadableRepositoryInterface, DeletableRepositoryInterface
{
}
