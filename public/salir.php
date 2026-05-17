<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/controladores/ControladorAutenticacion.php';
require_once __DIR__ . '/../app/ayudantes/seguridad_http.php';
require_once __DIR__ . '/../app/configuracion/rutas.php';

enviarEncabezadosSeguridad();
$controlador = new ControladorAutenticacion();
$controlador->cerrarSesion();

redirigirA('index.php');
