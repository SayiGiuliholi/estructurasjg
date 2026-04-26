<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_configuracion.php';
require_once __DIR__ . '/../../../modelos/RepositorioUsuario.php';

$repositorioUsuario = new RepositorioUsuario();

/**
 * Mantiene la sesion alineada con la base de datos despues de cambios en usuarios/roles.
 */
function refrescarSesionAutenticacionActual(RepositorioUsuario $repositorioUsuario): void
{
    if (!isset($_SESSION['autenticacion']['id_usuario'])) {
        return;
    }

    $idUsuarioActual = (int) $_SESSION['autenticacion']['id_usuario'];
    $usuarioActualizado = $repositorioUsuario->buscarPorIdUsuario($idUsuarioActual);

    if ($usuarioActualizado === null) {
        return;
    }

    $_SESSION['autenticacion'] = [
        'id_usuario' => $usuarioActualizado->idUsuario,
        'nombre' => $usuarioActualizado->nombre,
        'usuario' => $usuarioActualizado->usuario,
        'id_rol' => $usuarioActualizado->rol->idRol,
        'rol' => $usuarioActualizado->rol->nombre,
        'permisos' => $usuarioActualizado->rol->permisos,
    ];
}

$mensajeExito = '';
$mensajeError = '';

$formularioUsuario = [
    'id_usuario' => 0,
    'nombre' => '',
    'usuario' => '',
    'id_rol' => '',
    'estado' => 1,
];

if (isset($_GET['editar_usuario'])) {
    $idUsuarioEditar = (int) $_GET['editar_usuario'];
    if ($idUsuarioEditar > 0) {
        $usuarioEditar = $repositorioUsuario->obtenerUsuarioPorId($idUsuarioEditar);
        if ($usuarioEditar !== null) {
            $formularioUsuario = [
                'id_usuario' => (int) ($usuarioEditar['id_usuario'] ?? 0),
                'nombre' => (string) ($usuarioEditar['nombre'] ?? ''),
                'usuario' => (string) ($usuarioEditar['usuario'] ?? ''),
                'id_rol' => (string) ($usuarioEditar['id_rol'] ?? ''),
                'estado' => (int) ($usuarioEditar['estado'] ?? 0),
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim((string) ($_POST['accion'] ?? ''));

    if ($accion === 'guardar_usuario') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        $idRol = (int) ($_POST['id_rol'] ?? 0);
        $estado = isset($_POST['estado']) ? 1 : 0;

        $formularioUsuario = [
            'id_usuario' => $idUsuario,
            'nombre' => $nombre,
            'usuario' => $usuario,
            'id_rol' => (string) $idRol,
            'estado' => $estado,
        ];

        if ($nombre === '' || $usuario === '' || $idRol <= 0) {
            $mensajeError = 'Completa nombre, usuario y rol para guardar el usuario.';
        } else {
            try {
                if ($idUsuario > 0) {
                    $repositorioUsuario->actualizarUsuarioGestion(
                        $idUsuario,
                        $nombre,
                        $usuario,
                        $idRol,
                        $estado,
                        trim($contrasena) !== '' ? $contrasena : null
                    );
                    refrescarSesionAutenticacionActual($repositorioUsuario);
                    $mensajeExito = 'Usuario actualizado correctamente.';
                } else {
                    if (trim($contrasena) === '') {
                        $mensajeError = 'La contrasena es obligatoria para crear un usuario.';
                    } else {
                        $repositorioUsuario->crearUsuarioGestion($nombre, $usuario, $contrasena, $idRol, $estado);
                        $mensajeExito = 'Usuario creado correctamente.';
                        $formularioUsuario = [
                            'id_usuario' => 0,
                            'nombre' => '',
                            'usuario' => '',
                            'id_rol' => '',
                            'estado' => 1,
                        ];
                    }
                }
            } catch (Throwable $error) {
                $mensajeError = 'No se pudo guardar el usuario. Verifica si el usuario ya existe.';
            }
        }
    }

    if ($accion === 'guardar_permisos_rol') {
        $idRolPermiso = (int) ($_POST['id_rol'] ?? 0);
        if ($idRolPermiso <= 0) {
            $mensajeError = 'Rol invalido para actualizar permisos.';
        } else {
            try {
                $repositorioUsuario->actualizarPermisosRol($idRolPermiso, [
                    'registrar_productos' => isset($_POST['registrar_productos']) ? 1 : 0,
                    'modificar_productos' => isset($_POST['modificar_productos']) ? 1 : 0,
                    'registrar_movimientos' => isset($_POST['registrar_movimientos']) ? 1 : 0,
                    'consultar_movimientos' => isset($_POST['consultar_movimientos']) ? 1 : 0,
                    'gestionar_roles' => isset($_POST['gestionar_roles']) ? 1 : 0,
                    'configuracion' => isset($_POST['configuracion']) ? 1 : 0,
                ]);
                refrescarSesionAutenticacionActual($repositorioUsuario);
                $mensajeExito = 'Permisos del rol actualizados correctamente.';
            } catch (Throwable $error) {
                $mensajeError = 'No fue posible actualizar permisos del rol.';
            }
        }
    }
}

$datosModulo = prepararDatosModuloConfiguracion(
    $formularioUsuario,
    $repositorioUsuario->obtenerUsuariosParaGestion(),
    $repositorioUsuario->obtenerRolesConPermisos(),
    $mensajeExito,
    $mensajeError
);

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$formularioUsuario = $datosModulo['formularioUsuario'];
$rolesOpciones = $datosModulo['rolesOpciones'];
$usuarios = $datosModulo['usuarios'];
$rolesConPermisos = $datosModulo['rolesConPermisos'];
$mensajeExito = $datosModulo['mensajeExito'];
$mensajeError = $datosModulo['mensajeError'];

ob_start();
require __DIR__ . '/../modulos/vista_configuracion.php';
$contenidoModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
