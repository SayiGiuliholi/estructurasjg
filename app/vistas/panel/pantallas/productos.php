<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_productos.php';
require_once __DIR__ . '/../../../configuracion/rutas.php';
require_once __DIR__ . '/../../../modelos/RepositorioProducto.php';

$repositorioProducto = new RepositorioProducto();

$opcionesPorPagina = [10, 20, 50];
$porPagina = (int) ($_GET['por_pagina'] ?? 20);
if (!in_array($porPagina, $opcionesPorPagina, true)) {
    $porPagina = 20;
}
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

$totalRegistrosCatalogo = $repositorioProducto->contarTotal();
$totalPaginas = max(1, (int) ceil($totalRegistrosCatalogo / $porPagina));
if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}
$offset = ($paginaActual - 1) * $porPagina;

$catalogoProductos = $repositorioProducto->obtenerPaginado($porPagina, $offset);

$datosModulo = prepararDatosModuloProductos([
    'catalogoProductos' => $catalogoProductos,
    'totalProductos' => $repositorioProducto->contarTotal(),
    'stockTotal' => $repositorioProducto->sumarStockTotal(),
    'stockBajo' => $repositorioProducto->contarStockBajo(),
    'valorEstimado' => $repositorioProducto->calcularValorEstimado(),
    'paginacion' => [
        'paginaActual' => $paginaActual,
        'totalPaginas' => $totalPaginas,
        'totalRegistros' => $totalRegistrosCatalogo,
        'porPagina' => $porPagina,
        'opcionesPorPagina' => $opcionesPorPagina,
    ],
]);

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$resumenIndicadores = $datosModulo['resumenIndicadores'];
$catalogoProductos = $datosModulo['catalogoProductos'];
$controlVisual = $datosModulo['controlVisual'];
$paginacion = $datosModulo['paginacion'];
$urlScriptProductos = construirUrlPublica('js/panel/productos.js');

ob_start();
require __DIR__ . '/../modulos/vista_productos.php';
$contenidoModulo = ob_get_clean();

ob_start();
?>
<script src="<?= htmlspecialchars($urlScriptProductos, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php
$scriptsModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
