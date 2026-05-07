<?php

declare(strict_types=1);

require_once __DIR__ . '/../ayudantes/sesion.php';
require_once __DIR__ . '/../modelos/RepositorioUsuario.php';

final class ControladorAutenticacion
{
    private function esSuperadmin(Usuario $usuario): bool
    {
        return esSuperadminSesion([
            'id_usuario' => $usuario->idUsuario,
            'usuario' => $usuario->usuario,
        ]);
    }

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

        if ($usuario === null) {
            return [
                'exito' => false,
                'mensaje' => 'Las credenciales ingresadas no son validas.',
            ];
        }

        $contrasenaValida = password_verify($contrasenaIngresada, $usuario->contrasena);

        // Compatibilidad para cuentas antiguas guardadas sin hash.
        if (!$contrasenaValida && hash_equals($usuario->contrasena, $contrasenaIngresada)) {
            $nuevoHash = password_hash($contrasenaIngresada, PASSWORD_DEFAULT);
            $repositorioUsuario->actualizarHashContrasena($usuario->idUsuario, $nuevoHash);
            $usuario->contrasena = $nuevoHash;
            $contrasenaValida = true;
        }

        if (!$contrasenaValida) {
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

        $tieneAlgunPermiso = false;
        foreach ($usuario->rol->permisos as $permiso) {
            if ((int) $permiso === 1) {
                $tieneAlgunPermiso = true;
                break;
            }
        }

        $esSuperadmin = $this->esSuperadmin($usuario);

        if (!$tieneAlgunPermiso && !$esSuperadmin) {
            return [
                'exito' => false,
                'mensaje' => 'Tu rol no tiene permisos activos. Solicita revision al administrador.',
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
            'es_superadmin' => $esSuperadmin ? 1 : 0,
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
