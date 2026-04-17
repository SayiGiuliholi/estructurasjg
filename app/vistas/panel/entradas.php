<?php

declare(strict_types=1);

require_once __DIR__ . '/preparar_entradas.php';
require_once __DIR__ . '/../../configuracion/rutas.php';

$datosModulo = prepararDatosModuloEntradas();

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$resumenIndicadores = $datosModulo['resumenIndicadores'];
$formularioEntrada = $datosModulo['formularioEntrada'];
$resumenOperativo = $datosModulo['resumenOperativo'];
$notaModulo = $datosModulo['notaModulo'];
$historialEntradas = $datosModulo['historialEntradas'];
$urlScriptEntradas = construirUrlPublica('js/panel/entradas.js');

ob_start();
require __DIR__ . '/parciales/entradas/contenido.php';
$contenidoModulo = ob_get_clean();

ob_start();
?>
<script src="<?= htmlspecialchars($urlScriptEntradas, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php
$scriptsModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
