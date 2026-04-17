<?php

declare(strict_types=1);

require_once __DIR__ . '/Rol.php';

final class Usuario
{
    public function __construct(
        public int $idUsuario,
        public string $nombre,
        public string $usuario,
        public string $contrasena,
        public int $estado,
        public Rol $rol,
    ) {
    }
}