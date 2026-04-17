<?php

declare(strict_types=1);

require_once __DIR__ . '/preparar_salidas.php';
require_once __DIR__ . '/../../configuracion/rutas.php';

$datosModulo = prepararDatosModuloSalidas();

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$resumenIndicadores = $datosModulo['resumenIndicadores'];
$formularioSalida = $datosModulo['formularioSalida'];
$estadoDespacho = $datosModulo['estadoDespacho'];
$historialSalidas = $datosModulo['historialSalidas'];
$urlScriptSalidas = construirUrlPublica('js/panel/salidas.js');

ob_start();
require __DIR__ . '/parciales/salidas/contenido.php';
$contenidoModulo = ob_get_clean();

ob_start();
?>
<script src="<?= htmlspecialchars($urlScriptSalidas, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php
$scriptsModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
