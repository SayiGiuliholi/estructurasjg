<?php

declare(strict_types=1);

final class Producto
{
    public function __construct(
        public int $idProducto,
        public string $ultimaFactura,
        public string $codigo,
        public string $descripcion,
        public int $idProveedor,
        public string $nombreProveedor,
        public int $stock,
        public float $precio,
        public ?string $fecha = null,
        public string $resumenBodegas = '',
    ) {
    }
}
