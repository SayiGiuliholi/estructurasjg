<?php

declare(strict_types=1);

require_once __DIR__ . '/../ayudantes/sesion.php';
require_once __DIR__ . '/../modelos/RepositorioUsuario.php';

final class ControladorAutenticacion
{
    public function iniciarSesion(array $datos): array
    {
        iniciarSesionSegura();

        $usuarioIngresado = trim($datos['usuario'] ?? '');
        $contrasenaIngresada = trim($datos['contrasena'] ?? '');

        if ($usuarioIngresado === '' || $contrasenaIngresada === '') {
            return [
                'exito' => false,
                'mensaje' => 'Debes completar usuario y contrasena.',
            ];
        }

        $repositorioUsuario = new RepositorioUsuario();
        $usuario = $repositorioUsuario->buscarPorUsuario($usuarioIngresado);

        if ($usuario === null || !password_verify($contrasenaIngresada, $usuario->contrasena)) {
            return [
                'exito' => false,
                'mensaje' => 'Las credenciales ingresadas no son validas.',
            ];
        }

        if ($usuario->estado !== 1) {
            return [
                'exito' => false,
                'mensaje' => 'Tu usuario esta inactivo. Contacta al administrador.',
            ];
        }

        session_regenerate_id(true);

        $_SESSION['autenticacion'] = [
            'id_usuario' => $usuario->idUsuario,
            'nombre' => $usuario->nombre,
            'usuario' => $usuario->usuario,
            'id_rol' => $usuario->rol->idRol,
            'rol' => $usuario->rol->nombre,
            'permisos' => $usuario->rol->permisos,
        ];

        $repositorioUsuario->actualizarUltimoAcceso($usuario->idUsuario);

        return ['exito' => true];
    }

    public function cerrarSesion(): void
    {
        iniciarSesionSegura();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametros['path'],
                $parametros['domain'],
                (bool) $parametros['secure'],
                (bool) $parametros['httponly']
            );
        }

        session_destroy();
    }
}