<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/ServicioRolesPermisos.php';

final class RepositorioUsuario
{
    // Servicio dedicado para roles/permisos
    private ServicioRolesPermisos $servicioRolesPermisos;

    public function __construct()
    {
        $this->servicioRolesPermisos = new ServicioRolesPermisos();
    }

    /**
     * Verifica si el nombre de usuario ya existe.
     */
    private function existeNombreUsuario(string $usuario, ?int $ignorarIdUsuario = null): bool
    {
        $conexion = obtenerConexion();
        $sql = 'SELECT 1 FROM usuarios WHERE usuario = :usuario';
        $parametros = ['usuario' => $usuario];

        if ($ignorarIdUsuario !== null && $ignorarIdUsuario > 0) {
            $sql .= ' AND id_usuario <> :id_usuario';
            $parametros['id_usuario'] = $ignorarIdUsuario;
        }

        $sql .= ' LIMIT 1';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($parametros);
        return (bool) $sentencia->fetchColumn();
    }

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

    /**
     * Busca un usuario con su rol/permisos por nombre de usuario.
     */
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

    /**
     * Busca un usuario con su rol/permisos por id de usuario.
     */
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

    /**
     * Actualiza solo el hash de contrasena.
     */
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

    /**
     * Registra el ultimo acceso del usuario.
     */
    public function actualizarUltimoAcceso(int $idUsuario): void
    {
        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare(
            'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id_usuario'
        );
        $sentencia->execute(['id_usuario' => $idUsuario]);
    }

    /**
     * Lista usuarios para la pantalla de gestion.
     */
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

    /**
     * Lista los roles con su matriz de permisos.
     */
    public function obtenerRolesConPermisos(): array
    {
        return $this->servicioRolesPermisos->obtenerRolesConPermisos();
    }

    /**
     * Obtiene datos simples de usuario por id para edicion.
     */
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

    /**
     * Crea un usuario desde Configuracion.
     */
    public function crearUsuarioGestion(
        string $nombre,
        string $usuario,
        string $contrasenaPlano,
        int $idRol,
        int $estado
    ): void {
        if ($this->existeNombreUsuario($usuario)) {
            throw new RuntimeException('El usuario ya existe.');
        }

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

    /**
     * Actualiza usuario desde Configuracion.
     */
    public function actualizarUsuarioGestion(
        int $idUsuario,
        string $nombre,
        string $usuario,
        int $idRol,
        int $estado,
        ?string $contrasenaPlano = null
    ): void {
        if ($this->existeNombreUsuario($usuario, $idUsuario)) {
            throw new RuntimeException('El usuario ya existe.');
        }

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

    /**
     * Actualiza permisos de un rol.
     */
    public function actualizarPermisosRol(int $idRol, array $permisos): void
    {
        $this->servicioRolesPermisos->actualizarPermisosRol($idRol, $permisos);
    }
}
