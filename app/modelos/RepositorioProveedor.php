<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/Proveedor.php';

final class RepositorioProveedor
{
    private ?bool $columnaEstadoDisponible = null;

    public function obtenerTodos(): array
    {
        $conexion = obtenerConexion();
        $campoEstado = $this->tieneColumnaEstado() ? 'p.estado AS estado' : '1 AS estado';

        $sql = <<<SQL
            SELECT
                p.id_proveedor,
                p.ruc,
                p.nombre,
                p.telefono,
                p.direccion,
                {$campoEstado},
                COUNT(pr.id_producto) AS total_productos
            FROM proveedores p
            LEFT JOIN productos pr ON pr.id_proveedor = p.id_proveedor
            GROUP BY p.id_proveedor, p.ruc, p.nombre, p.telefono, p.direccion, estado
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
                ((int) ($fila['estado'] ?? 1)) === 1,
            ),
            $filas
        );
    }

    public function obtenerActivos(): array
    {
        if (!$this->tieneColumnaEstado()) {
            return $this->obtenerTodos();
        }

        return array_values(
            array_filter(
                $this->obtenerTodos(),
                static fn(Proveedor $proveedor): bool => $proveedor->activo
            )
        );
    }

    public function buscarPorId(int $idProveedor): ?Proveedor
    {
        $conexion = obtenerConexion();
        $campoEstado = $this->tieneColumnaEstado() ? 'p.estado AS estado' : '1 AS estado';

        $sql = <<<SQL
            SELECT
                p.id_proveedor,
                p.ruc,
                p.nombre,
                p.telefono,
                p.direccion,
                {$campoEstado},
                COUNT(pr.id_producto) AS total_productos
            FROM proveedores p
            LEFT JOIN productos pr ON pr.id_proveedor = p.id_proveedor
            WHERE p.id_proveedor = :id_proveedor
            GROUP BY p.id_proveedor, p.ruc, p.nombre, p.telefono, p.direccion, estado
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
            ((int) ($fila['estado'] ?? 1)) === 1,
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

    public function prepararSoporteEstado(): void
    {
        if ($this->tieneColumnaEstado()) {
            return;
        }

        $conexion = obtenerConexion();
        $conexion->exec('ALTER TABLE proveedores ADD COLUMN estado TINYINT(1) NOT NULL DEFAULT 1');
        $this->columnaEstadoDisponible = true;
    }

    public function cambiarEstado(int $idProveedor, bool $activo): void
    {
        $this->prepararSoporteEstado();

        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare(
            'UPDATE proveedores SET estado = :estado WHERE id_proveedor = :id_proveedor'
        );
        $sentencia->execute([
            'estado' => $activo ? 1 : 0,
            'id_proveedor' => $idProveedor,
        ]);
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

    private function tieneColumnaEstado(): bool
    {
        if ($this->columnaEstadoDisponible !== null) {
            return $this->columnaEstadoDisponible;
        }

        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare(
            'SELECT COUNT(*) AS total
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :tabla
               AND COLUMN_NAME = :columna'
        );
        $sentencia->execute([
            'tabla' => 'proveedores',
            'columna' => 'estado',
        ]);

        $fila = $sentencia->fetch();
        $this->columnaEstadoDisponible = ((int) ($fila['total'] ?? 0)) > 0;

        return $this->columnaEstadoDisponible;
    }
}
