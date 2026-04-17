<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/Usuario.php';

final class RepositorioUsuario
{
    public function buscarPorUsuario(string $usuario): ?Usuario
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                u.id_usuario,
                u.nombre,
                u.usuario,
                u.contrasena,
                u.estado,
                r.id_rol,
                r.nombre AS rol_nombre,
                r.p_registrar_productos,
                r.p_modificar_productos,
                r.p_registrar_movimientos,
                r.p_consultar_movimientos,
                r.p_gestionar_roles,
                r.p_configuracion
            FROM usuarios u
            INNER JOIN roles r ON r.id_rol = u.id_rol
            WHERE u.usuario = :usuario
            LIMIT 1
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['usuario' => $usuario]);

        $fila = $sentencia->fetch();

        if (!$fila) {
            return null;
        }

        $rol = new Rol(
            (int) $fila['id_rol'],
            $fila['rol_nombre'],
            [
                'registrar_productos' => (int) $fila['p_registrar_productos'],
                'modificar_productos' => (int) $fila['p_modificar_productos'],
                'registrar_movimientos' => (int) $fila['p_registrar_movimientos'],
                'consultar_movimientos' => (int) $fila['p_consultar_movimientos'],
                'gestionar_roles' => (int) $fila['p_gestionar_roles'],
                'configuracion' => (int) $fila['p_configuracion'],
            ]
        );

        return new Usuario(
            (int) $fila['id_usuario'],
            $fila['nombre'],
            $fila['usuario'],
            $fila['contrasena'],
            (int) $fila['estado'],
            $rol,
        );
    }

    public function actualizarUltimoAcceso(int $idUsuario): void
    {
        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare(
            'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id_usuario'
        );
        $sentencia->execute(['id_usuario' => $idUsuario]);
    }
}