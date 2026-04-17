<?php

declare(strict_types=1);

final class Rol
{
    public function __construct(
        public int $idRol,
        public string $nombre,
        public array $permisos,
    ) {
    }
}