<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/controladores/ControladorAutenticacion.php';
require_once __DIR__ . '/../app/ayudantes/sesion.php';
require_once __DIR__ . '/../app/configuracion/rutas.php';

iniciarSesionSegura();

if (usuarioAutenticado()) {
    redirigirA('panel.php');
}

$mensajeError = null;
$ultimoUsuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ultimoUsuario = trim($_POST['usuario'] ?? '');

    $controlador = new ControladorAutenticacion();
    $resultado = $controlador->iniciarSesion($_POST);

    if ($resultado['exito'] === true) {
        redirigirA('panel.php');
    }

    $mensajeError = $resultado['mensaje'];
}

require_once __DIR__ . '/../app/vistas/autenticacion/login.php';
