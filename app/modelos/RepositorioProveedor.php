<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/Proveedor.php';

final class RepositorioProveedor
{
    public function obtenerTodos(): array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                p.id_proveedor,
                p.ruc,
                p.nombre,
                p.telefono,
                p.direccion,
                COUNT(pr.id_producto) AS total_productos
            FROM proveedores p
            LEFT JOIN productos pr ON pr.id_proveedor = p.id_proveedor
            GROUP BY p.id_proveedor, p.ruc, p.nombre, p.telefono, p.direccion
            ORDER BY p.nombre ASC
        SQL;

        $filas = $conexion->query($sql)->fetchAll();

        return array_map(
            static fn(array $fila): Proveedor => new Proveedor(
                (int) $fila['id_proveedor'],
                (string) $fila['ruc'],
                (string) $fila['nombre'],
                (string) $fila['telefono'],
                (string) $fila['direccion'],
                (int) $fila['total_productos'],
            ),
            $filas
        );
    }

    public function buscarPorId(int $idProveedor): ?Proveedor
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                p.id_proveedor,
                p.ruc,
                p.nombre,
                p.telefono,
                p.direccion,
                COUNT(pr.id_producto) AS total_productos
            FROM proveedores p
            LEFT JOIN productos pr ON pr.id_proveedor = p.id_proveedor
            WHERE p.id_proveedor = :id_proveedor
            GROUP BY p.id_proveedor, p.ruc, p.nombre, p.telefono, p.direccion
            LIMIT 1
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['id_proveedor' => $idProveedor]);

        $fila = $sentencia->fetch();

        if (!$fila) {
            return null;
        }

        return new Proveedor(
            (int) $fila['id_proveedor'],
            (string) $fila['ruc'],
            (string) $fila['nombre'],
            (string) $fila['telefono'],
            (string) $fila['direccion'],
            (int) $fila['total_productos'],
        );
    }

    public function crear(array $datos): int
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            INSERT INTO proveedores (ruc, nombre, telefono, direccion)
            VALUES (:ruc, :nombre, :telefono, :direccion)
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'ruc' => $datos['ruc'],
            'nombre' => $datos['nombre'],
            'telefono' => $datos['telefono'],
            'direccion' => $datos['direccion'],
        ]);

        return (int) $conexion->lastInsertId();
    }

    public function actualizar(int $idProveedor, array $datos): void
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            UPDATE proveedores
            SET
                ruc = :ruc,
                nombre = :nombre,
                telefono = :telefono,
                direccion = :direccion
            WHERE id_proveedor = :id_proveedor
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_proveedor' => $idProveedor,
            'ruc' => $datos['ruc'],
            'nombre' => $datos['nombre'],
            'telefono' => $datos['telefono'],
            'direccion' => $datos['direccion'],
        ]);
    }

    public function eliminar(int $idProveedor): void
    {
        $conexion = obtenerConexion();

        $sentencia = $conexion->prepare(
            'DELETE FROM proveedores WHERE id_proveedor = :id_proveedor'
        );

        $sentencia->execute(['id_proveedor' => $idProveedor]);
    }

    public function contarConProductos(): int
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT COUNT(DISTINCT p.id_proveedor) AS total
            FROM proveedores p
            INNER JOIN productos pr ON pr.id_proveedor = p.id_proveedor
        SQL;

        $fila = $conexion->query($sql)->fetch();

        return (int) ($fila['total'] ?? 0);
    }
}
