<?php

declare(strict_types=1);

final class Proveedor
{
    public function __construct(
        public int $idProveedor,
        public string $ruc,
        public string $nombre,
        public string $telefono,
        public string $direccion,
        public int $totalProductos = 0,
    ) {
    }
}
