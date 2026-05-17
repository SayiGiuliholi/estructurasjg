<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class ServicioRolesPermisos
{
    public function obtenerRolesConPermisos(): array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                id_rol,
                nombre,
                p_registrar_productos,
                p_modificar_productos,
                p_registrar_movimientos,
                p_consultar_movimientos,
                p_gestionar_roles,
                p_configuracion
            FROM roles
            ORDER BY id_rol ASC
        SQL;

        return $conexion->query($sql)->fetchAll();
    }

    public function actualizarPermisosRol(int $idRol, array $permisos): void
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            UPDATE roles
            SET
                p_registrar_productos = :p_registrar_productos,
                p_modificar_productos = :p_modificar_productos,
                p_registrar_movimientos = :p_registrar_movimientos,
                p_consultar_movimientos = :p_consultar_movimientos,
                p_gestionar_roles = :p_gestionar_roles,
                p_configuracion = :p_configuracion
            WHERE id_rol = :id_rol
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_rol' => $idRol,
            'p_registrar_productos' => (int) ($permisos['registrar_productos'] ?? 0),
            'p_modificar_productos' => (int) ($permisos['modificar_productos'] ?? 0),
            'p_registrar_movimientos' => (int) ($permisos['registrar_movimientos'] ?? 0),
            'p_consultar_movimientos' => (int) ($permisos['consultar_movimientos'] ?? 0),
            'p_gestionar_roles' => (int) ($permisos['gestionar_roles'] ?? 0),
            'p_configuracion' => (int) ($permisos['configuracion'] ?? 0),
        ]);
    }
}

