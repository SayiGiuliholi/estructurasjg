<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/Usuario.php';

final class RepositorioUsuario
{
    private function construirUsuarioDesdeFila(array $fila): Usuario
    {
        $rol = new Rol(
            (int) $fila['id_rol'],
            (string) $fila['rol_nombre'],
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
            (string) $fila['nombre'],
            (string) $fila['usuario'],
            (string) $fila['contrasena'],
            (int) $fila['estado'],
            $rol,
        );
    }

    private function sqlBaseUsuarioConRol(): string
    {
        return <<<SQL
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
        SQL;
    }

    public function buscarPorUsuario(string $usuario): ?Usuario
    {
        $conexion = obtenerConexion();

        $sql = $this->sqlBaseUsuarioConRol() . ' WHERE u.usuario = :usuario LIMIT 1';

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['usuario' => $usuario]);

        $fila = $sentencia->fetch();

        if (!$fila) {
            return null;
        }

        return $this->construirUsuarioDesdeFila($fila);
    }

    public function buscarPorIdUsuario(int $idUsuario): ?Usuario
    {
        $conexion = obtenerConexion();

        $sql = $this->sqlBaseUsuarioConRol() . ' WHERE u.id_usuario = :id_usuario LIMIT 1';

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['id_usuario' => $idUsuario]);

        $fila = $sentencia->fetch();

        if (!$fila) {
            return null;
        }

        return $this->construirUsuarioDesdeFila($fila);
    }

    public function actualizarHashContrasena(int $idUsuario, string $hashContrasena): void
    {
        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare(
            'UPDATE usuarios SET contrasena = :contrasena WHERE id_usuario = :id_usuario'
        );
        $sentencia->execute([
            'id_usuario' => $idUsuario,
            'contrasena' => $hashContrasena,
        ]);
    }

    public function actualizarUltimoAcceso(int $idUsuario): void
    {
        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare(
            'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id_usuario'
        );
        $sentencia->execute(['id_usuario' => $idUsuario]);
    }

    public function obtenerUsuariosParaGestion(): array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                u.id_usuario,
                u.nombre,
                u.usuario,
                u.id_rol,
                u.estado,
                u.ultimo_acceso,
                r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id_rol = u.id_rol
            ORDER BY u.id_usuario DESC
        SQL;

        return $conexion->query($sql)->fetchAll();
    }

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

    public function obtenerUsuarioPorId(int $idUsuario): ?array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                id_usuario,
                nombre,
                usuario,
                id_rol,
                estado
            FROM usuarios
            WHERE id_usuario = :id_usuario
            LIMIT 1
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['id_usuario' => $idUsuario]);
        $fila = $sentencia->fetch();

        return $fila ?: null;
    }

    public function crearUsuarioGestion(
        string $nombre,
        string $usuario,
        string $contrasenaPlano,
        int $idRol,
        int $estado
    ): void {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            INSERT INTO usuarios (nombre, usuario, contrasena, id_rol, estado)
            VALUES (:nombre, :usuario, :contrasena, :id_rol, :estado)
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'nombre' => $nombre,
            'usuario' => $usuario,
            'contrasena' => password_hash($contrasenaPlano, PASSWORD_DEFAULT),
            'id_rol' => $idRol,
            'estado' => $estado,
        ]);
    }

    public function actualizarUsuarioGestion(
        int $idUsuario,
        string $nombre,
        string $usuario,
        int $idRol,
        int $estado,
        ?string $contrasenaPlano = null
    ): void {
        $conexion = obtenerConexion();

        if ($contrasenaPlano !== null && trim($contrasenaPlano) !== '') {
            $sql = <<<SQL
                UPDATE usuarios
                SET
                    nombre = :nombre,
                    usuario = :usuario,
                    contrasena = :contrasena,
                    id_rol = :id_rol,
                    estado = :estado
                WHERE id_usuario = :id_usuario
            SQL;

            $sentencia = $conexion->prepare($sql);
            $sentencia->execute([
                'id_usuario' => $idUsuario,
                'nombre' => $nombre,
                'usuario' => $usuario,
                'contrasena' => password_hash($contrasenaPlano, PASSWORD_DEFAULT),
                'id_rol' => $idRol,
                'estado' => $estado,
            ]);
            return;
        }

        $sql = <<<SQL
            UPDATE usuarios
            SET
                nombre = :nombre,
                usuario = :usuario,
                id_rol = :id_rol,
                estado = :estado
            WHERE id_usuario = :id_usuario
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_usuario' => $idUsuario,
            'nombre' => $nombre,
            'usuario' => $usuario,
            'id_rol' => $idRol,
            'estado' => $estado,
        ]);
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
