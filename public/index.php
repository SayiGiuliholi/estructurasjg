<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/controladores/ControladorAutenticacion.php';
require_once __DIR__ . '/../app/ayudantes/sesion.php';
require_once __DIR__ . '/../app/ayudantes/csrf.php';
require_once __DIR__ . '/../app/ayudantes/seguridad_http.php';
require_once __DIR__ . '/../app/configuracion/rutas.php';

enviarEncabezadosSeguridad();
iniciarSesionSegura();

if (usuarioAutenticado()) {
    redirigirA('panel.php');
}

$mensajeError = null;
$ultimoUsuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfEsValidoEnPost($_POST)) {
        $mensajeError = 'La sesion del formulario expiro. Recarga la pagina e intenta de nuevo.';
        require_once __DIR__ . '/../app/vistas/autenticacion/pantallas/login.php';
        exit;
    }

    $ultimoUsuario = trim($_POST['usuario'] ?? '');

    try {
        $controlador = new ControladorAutenticacion();
        $resultado = $controlador->iniciarSesion($_POST);
    } catch (Throwable $error) {
        $resultado = [
            'exito' => false,
            'mensaje' => 'No hay conexion con la base de datos en este momento. Intenta nuevamente en unos minutos.',
        ];
    }

    if ($resultado['exito'] === true) {
        redirigirA('panel.php');
    }

    $mensajeError = $resultado['mensaje'];
}

require_once __DIR__ . '/../app/vistas/autenticacion/pantallas/login.php';
