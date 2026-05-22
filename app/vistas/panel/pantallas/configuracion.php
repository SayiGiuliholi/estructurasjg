<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_configuracion.php';
require_once __DIR__ . '/../../../modelos/RepositorioUsuario.php';
require_once __DIR__ . '/../../../modelos/RepositorioAuditoria.php';
require_once __DIR__ . '/../../../ayudantes/sesion.php';
require_once __DIR__ . '/../../../ayudantes/csrf.php';

$repositorioUsuario = new RepositorioUsuario();
$repositorioAuditoria = new RepositorioAuditoria();

$esSuperadminSesion = esSuperadminSesion($_SESSION['autenticacion'] ?? []);
$esAdministradorSesion = $esSuperadminSesion
    || strtolower(trim((string) ($_SESSION['autenticacion']['rol'] ?? ''))) === 'administrador';

function esRolPrivilegiadoPorNombre(string $nombreRol): bool
{
    $rol = strtolower(trim($nombreRol));
    return $rol === 'administrador' || $rol === 'superadmin';
}

function esRolSuperadmin(RepositorioUsuario $repositorioUsuario, int $idRol): bool
{
    if ($idRol <= 0) {
        return false;
    }

    $roles = $repositorioUsuario->obtenerRolesConPermisos();
    foreach ($roles as $rol) {
        if ((int) ($rol['id_rol'] ?? 0) !== $idRol) {
            continue;
        }
        return strtolower(trim((string) ($rol['nombre'] ?? ''))) === 'superadmin';
    }

    return false;
}

/**
 * Determina si el rol indicado corresponde a un rol privilegiado.
 */
function esRolAdministrador(RepositorioUsuario $repositorioUsuario, int $idRol): bool
{
    if ($idRol <= 0) {
        return false;
    }

    $roles = $repositorioUsuario->obtenerRolesConPermisos();
    foreach ($roles as $rol) {
        $idRolActual = (int) ($rol['id_rol'] ?? 0);
        if ($idRolActual !== $idRol) {
            continue;
        }
        return esRolPrivilegiadoPorNombre((string) ($rol['nombre'] ?? ''));
    }

    return false;
}

/**
 * Determina si el rol indicado corresponde al rol Empleado.
 */
function esRolEmpleado(RepositorioUsuario $repositorioUsuario, int $idRol): bool
{
    if ($idRol <= 0) {
        return false;
    }

    $roles = $repositorioUsuario->obtenerRolesConPermisos();
    foreach ($roles as $rol) {
        $idRolActual = (int) ($rol['id_rol'] ?? 0);
        if ($idRolActual !== $idRol) {
            continue;
        }
        return strtolower((string) ($rol['nombre'] ?? '')) === 'empleado';
    }

    return false;
}

/**
 * Determina si el usuario indicado pertenece a un rol privilegiado.
 */
function esUsuarioAdministrador(RepositorioUsuario $repositorioUsuario, int $idUsuario): bool
{
    if ($idUsuario <= 0) {
        return false;
    }

    $usuario = $repositorioUsuario->obtenerUsuarioPorId($idUsuario);
    if ($usuario === null) {
        return false;
    }

    return esRolAdministrador($repositorioUsuario, (int) ($usuario['id_rol'] ?? 0));
}

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
        'es_superadmin' => calcularSuperadminPorRol($usuarioActualizado->rol->nombre) ? 1 : 0,
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
        if ($usuarioEditar !== null && esRolSuperadmin($repositorioUsuario, (int) ($usuarioEditar['id_rol'] ?? 0))) {
            $mensajeError = 'El usuario Superadmin es protegido y no se puede editar desde el sistema.';
        } elseif (!$esSuperadminSesion && esUsuarioAdministrador($repositorioUsuario, $idUsuarioEditar)) {
            $mensajeError = 'No tienes permiso para editar usuarios administradores.';
        } else {
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
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfEsValidoEnPost($_POST)) {
        $mensajeError = 'Token de seguridad invalido. Recarga la pagina e intenta nuevamente.';
    } else {
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
        } elseif (esRolSuperadmin($repositorioUsuario, $idRol)) {
            $mensajeError = 'No se permite asignar el rol Superadmin desde el sistema.';
        } elseif (
            !$esSuperadminSesion
            && (
                esRolAdministrador($repositorioUsuario, $idRol)
                || ($idUsuario > 0 && esUsuarioAdministrador($repositorioUsuario, $idUsuario))
            )
        ) {
            $mensajeError = 'No tienes permiso para crear ni editar usuarios administradores.';
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
                    $repositorioAuditoria->registrarEvento([
                        'id_usuario' => (int) ($_SESSION['autenticacion']['id_usuario'] ?? 0),
                        'usuario' => (string) ($_SESSION['autenticacion']['usuario'] ?? ''),
                        'modulo' => 'configuracion',
                        'accion' => 'actualizar_usuario',
                        'entidad' => 'usuario',
                        'id_entidad' => $idUsuario,
                        'detalle' => [
                            'nombre' => $nombre,
                            'usuario' => $usuario,
                            'id_rol' => $idRol,
                            'estado' => $estado,
                        ],
                    ]);
                } else {
                    if (trim($contrasena) === '') {
                        $mensajeError = 'La contrasena es obligatoria para crear un usuario.';
                    } else {
                        $repositorioUsuario->crearUsuarioGestion($nombre, $usuario, $contrasena, $idRol, $estado);
                        $mensajeExito = 'Usuario creado correctamente.';
                        $repositorioAuditoria->registrarEvento([
                            'id_usuario' => (int) ($_SESSION['autenticacion']['id_usuario'] ?? 0),
                            'usuario' => (string) ($_SESSION['autenticacion']['usuario'] ?? ''),
                            'modulo' => 'configuracion',
                            'accion' => 'crear_usuario',
                            'entidad' => 'usuario',
                            'id_entidad' => null,
                            'detalle' => [
                                'nombre' => $nombre,
                                'usuario' => $usuario,
                                'id_rol' => $idRol,
                                'estado' => $estado,
                            ],
                        ]);
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
        $esRolSuperadminObjetivo = esRolSuperadmin($repositorioUsuario, $idRolPermiso);
        $esRolEmpleadoObjetivo = esRolEmpleado($repositorioUsuario, $idRolPermiso);

        if (!$esAdministradorSesion) {
            $mensajeError = 'Solo los administradores pueden modificar permisos de rol.';
        } elseif ($idRolPermiso <= 0) {
            $mensajeError = 'Rol invalido para actualizar permisos.';
        } elseif ($esRolSuperadminObjetivo) {
            $mensajeError = 'El rol Superadmin es protegido y no se edita desde esta seccion.';
        } elseif (!$esSuperadminSesion && !$esRolEmpleadoObjetivo) {
            $mensajeError = 'Como Administrador solo puedes modificar permisos del rol Empleado.';
        } else {
            try {
                $permisoRegistrarMovimientos = isset($_POST['registrar_movimientos']) ? 1 : 0;
                $repositorioUsuario->actualizarPermisosRol($idRolPermiso, [
                    'registrar_productos' => $permisoRegistrarMovimientos,
                    'modificar_productos' => isset($_POST['modificar_productos']) ? 1 : 0,
                    'registrar_movimientos' => $permisoRegistrarMovimientos,
                    'consultar_movimientos' => isset($_POST['consultar_movimientos']) ? 1 : 0,
                    'gestionar_roles' => $esRolEmpleadoObjetivo ? 0 : (isset($_POST['gestionar_roles']) ? 1 : 0),
                    'configuracion' => $esRolEmpleadoObjetivo ? 0 : (isset($_POST['configuracion']) ? 1 : 0),
                ]);

                refrescarSesionAutenticacionActual($repositorioUsuario);
                $mensajeExito = 'Permisos del rol actualizados correctamente.';
                $repositorioAuditoria->registrarEvento([
                    'id_usuario' => (int) ($_SESSION['autenticacion']['id_usuario'] ?? 0),
                    'usuario' => (string) ($_SESSION['autenticacion']['usuario'] ?? ''),
                    'modulo' => 'configuracion',
                    'accion' => 'actualizar_permisos_rol',
                    'entidad' => 'rol',
                    'id_entidad' => $idRolPermiso,
                    'detalle' => [
                        'registrar_productos' => $permisoRegistrarMovimientos,
                        'modificar_productos' => isset($_POST['modificar_productos']) ? 1 : 0,
                        'registrar_movimientos' => $permisoRegistrarMovimientos,
                        'consultar_movimientos' => isset($_POST['consultar_movimientos']) ? 1 : 0,
                        'gestionar_roles' => isset($_POST['gestionar_roles']) ? 1 : 0,
                        'configuracion' => isset($_POST['configuracion']) ? 1 : 0,
                    ],
                ]);
            } catch (Throwable $error) {
                $mensajeError = 'No fue posible actualizar permisos del rol.';
            }
        }
    }
    }
}

$auditoriaEventos = [];
if ($esSuperadminSesion) {
    $auditoriaEventos = $repositorioAuditoria->obtenerEventos(250);
}

$datosModulo = prepararDatosModuloConfiguracion(
    $formularioUsuario,
    $repositorioUsuario->obtenerUsuariosParaGestion(),
    $repositorioUsuario->obtenerRolesConPermisos(),
    $auditoriaEventos,
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
$auditoriaEventos = $datosModulo['auditoriaEventos'];
$mensajeExito = $datosModulo['mensajeExito'];
$mensajeError = $datosModulo['mensajeError'];

ob_start();
require __DIR__ . '/../modulos/vista_configuracion.php';
$contenidoModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
