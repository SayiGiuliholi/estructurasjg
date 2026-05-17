<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/filtros/autenticado.php';
require_once __DIR__ . '/../app/ayudantes/sesion.php';
require_once __DIR__ . '/../app/ayudantes/seguridad_http.php';
require_once __DIR__ . '/../app/controladores/ControladorPanel.php';
require_once __DIR__ . '/../app/modelos/RepositorioUsuario.php';

enviarEncabezadosSeguridad();
iniciarSesionSegura();

$autenticacion = $_SESSION['autenticacion'] ?? [];
$repositorioUsuario = new RepositorioUsuario();
$idUsuarioSesion = (int) ($autenticacion['id_usuario'] ?? 0);
$usuarioActualizado = $idUsuarioSesion > 0 ? $repositorioUsuario->buscarPorIdUsuario($idUsuarioSesion) : null;

if ($usuarioActualizado !== null) {
    $autenticacion = [
        'id_usuario' => $usuarioActualizado->idUsuario,
        'nombre' => $usuarioActualizado->nombre,
        'usuario' => $usuarioActualizado->usuario,
        'id_rol' => $usuarioActualizado->rol->idRol,
        'rol' => $usuarioActualizado->rol->nombre,
        'permisos' => $usuarioActualizado->rol->permisos,
        'es_superadmin' => calcularSuperadminPorRol($usuarioActualizado->rol->nombre) ? 1 : 0,
    ];
    $_SESSION['autenticacion'] = $autenticacion;
}

$permisos = $autenticacion['permisos'] ?? [];

$controladorPanel = new ControladorPanel();
$rutaVista = $controladorPanel->resolverVistaModulo($_GET, $autenticacion);

require_once $rutaVista;
