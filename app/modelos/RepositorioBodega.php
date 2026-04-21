<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class RepositorioBodega
{
    public function obtenerActivas(): array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT id_bodega, codigo, nombre
            FROM bodegas
            WHERE estado = 1
            ORDER BY nombre ASC
        SQL;

        return $conexion->query($sql)->fetchAll();
    }
}
