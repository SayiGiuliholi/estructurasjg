<?php

declare(strict_types=1);

require_once __DIR__ . '/preparar_productos.php';

$datosModulo = prepararDatosModuloProductos();

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$resumenIndicadores = $datosModulo['resumenIndicadores'];
$catalogoProductos = $datosModulo['catalogoProductos'];
$formularioProducto = $datosModulo['formularioProducto'];
$controlVisual = $datosModulo['controlVisual'];

ob_start();
require __DIR__ . '/parciales/productos/contenido.php';
$contenidoModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
