<?php

declare(strict_types=1);

require_once __DIR__ . '/preparar_proveedores.php';

$datosModulo = prepararDatosModuloProveedores();

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$fichaProveedor = $datosModulo['fichaProveedor'];
$indicadores = $datosModulo['indicadores'];
$directorioProveedores = $datosModulo['directorioProveedores'];

ob_start();
require __DIR__ . '/parciales/proveedores/contenido.php';
$contenidoModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
