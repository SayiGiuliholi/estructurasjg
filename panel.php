<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/filtros/autenticado.php';
require_once __DIR__ . '/../app/ayudantes/sesion.php';

iniciarSesionSegura();

$autenticacion = $_SESSION['autenticacion'];
$permisos = $autenticacion['permisos'];

$modulo = $_GET['modulo'] ?? 'entradas';

$ruta = __DIR__ . '/../app/vistas/panel/';

switch ($modulo) {
    case 'configuracion':
        require_once $ruta . 'configuracion.php';
        break;

    case 'productos':
        require_once $ruta . 'productos.php';
        break;

    case 'proveedores':
        require_once $ruta . 'proveedores.php';
        break;

    case 'entradas':
        require_once $ruta . 'entradas.php';
        break;

    case 'salidas':
        require_once $ruta . 'salidas.php';
        break;

    default:
        require_once $ruta . 'entradas.php';
        break;
}
