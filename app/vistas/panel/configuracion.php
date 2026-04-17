<?php

declare(strict_types=1);

require_once __DIR__ . '/preparar_configuracion.php';

$datosModulo = prepararDatosModuloConfiguracion();

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$configuracionUsuarios = $datosModulo['configuracionUsuarios'];
$roles = $datosModulo['roles'];
$nivelesControl = $datosModulo['nivelesControl'];
$temasVisuales = $datosModulo['temasVisuales'];
$tamanosLetra = $datosModulo['tamanosLetra'];
$ajustesInterfaz = $datosModulo['ajustesInterfaz'];
$ayudaContextual = $datosModulo['ayudaContextual'];
$notaConfiguracion = $datosModulo['notaConfiguracion'];

ob_start();
require __DIR__ . '/parciales/configuracion/contenido.php';
$contenidoModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
