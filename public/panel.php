<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/filtros/autenticado.php';
require_once __DIR__ . '/../app/ayudantes/sesion.php';
require_once __DIR__ . '/../app/controladores/ControladorPanel.php';

iniciarSesionSegura();

$autenticacion = $_SESSION['autenticacion'];
$permisos = $autenticacion['permisos'];

$controladorPanel = new ControladorPanel();
$rutaVista = $controladorPanel->resolverVistaModulo($_GET, $autenticacion);

require_once $rutaVista;
