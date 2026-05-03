<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_salidas.php';
require_once __DIR__ . '/../../../configuracion/rutas.php';
require_once __DIR__ . '/../../../modelos/RepositorioSalida.php';
require_once __DIR__ . '/../../../modelos/RepositorioBodega.php';

$repositorioSalida = new RepositorioSalida();
$repositorioBodega = new RepositorioBodega();
$puedeRegistrarMovimientos = ((int) ($permisos['registrar_movimientos'] ?? 0)) === 1;

$opcionesPorPagina = [10, 20, 50];
$porPagina = (int) ($_GET['por_pagina'] ?? 20);
if (!in_array($porPagina, $opcionesPorPagina, true)) {
    $porPagina = 20;
}
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

$mensajeExito = '';
$mensajeError = '';

$normalizarMoneda = static function ($valor): float {
    $texto = trim((string) $valor);
    if ($texto === '') {
        return 0.0;
    }

    $texto = preg_replace('/[^\d\-]/', '', $texto) ?? '';
    if ($texto === '' || $texto === '-') {
        return 0.0;
    }

    return (float) $texto;
};

$formularioSalida = [
    'codigo_factura' => $repositorioSalida->obtenerSiguienteCodigoFactura(),
    'id_bodega' => '',
    'motivo_salida' => 'normal',
    'detalles' => [[
        'codigo' => '',
        'descripcion' => '',
        'stock' => '0',
        'cantidad' => '1',
        'precio' => '0',
    ]],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$puedeRegistrarMovimientos) {
        $mensajeError = 'Tu rol solo permite consultar movimientos. No puedes registrar salidas.';
    } else {
    $accion = trim((string) ($_POST['accion'] ?? ''));

    if ($accion === 'registrar') {
        $codigoFactura = trim((string) ($_POST['codigo_factura'] ?? ''));
        $idBodega = (int) ($_POST['id_bodega'] ?? 0);
        $motivoSalida = trim((string) ($_POST['motivo_salida'] ?? 'normal'));
        $idUsuario = (int) ($autenticacion['id_usuario'] ?? 0);

        $codigos = is_array($_POST['codigo_producto'] ?? null) ? $_POST['codigo_producto'] : [];
        $descripciones = is_array($_POST['descripcion_producto'] ?? null) ? $_POST['descripcion_producto'] : [];
        $stocks = is_array($_POST['stock_producto'] ?? null) ? $_POST['stock_producto'] : [];
        $cantidades = is_array($_POST['cantidad_producto'] ?? null) ? $_POST['cantidad_producto'] : [];
        $precios = is_array($_POST['precio_producto'] ?? null) ? $_POST['precio_producto'] : [];

        $cantidadLineas = max(count($codigos), count($descripciones), count($stocks), count($cantidades), count($precios));
        $detallesFactura = [];

        for ($indice = 0; $indice < $cantidadLineas; $indice++) {
            $codigo = trim((string) ($codigos[$indice] ?? ''));
            $descripcion = trim((string) ($descripciones[$indice] ?? ''));
            $stock = max(0, (int) ($stocks[$indice] ?? 0));
            $cantidad = max(0, (int) ($cantidades[$indice] ?? 0));
            $precio = max(0, $normalizarMoneda($precios[$indice] ?? 0));

            if ($codigo === '' && $descripcion === '' && $stock === 0 && $cantidad === 0 && $precio === 0.0) {
                continue;
            }

            $detallesFactura[] = [
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'stock' => $stock,
                'cantidad' => $cantidad,
                'precio' => $precio,
            ];
        }

        if (count($detallesFactura) === 0) {
            $detallesFactura[] = [
                'codigo' => '',
                'descripcion' => '',
                'stock' => '0',
                'cantidad' => '1',
                'precio' => '0',
            ];
        }

        $formularioSalida = [
            'codigo_factura' => $codigoFactura,
            'id_bodega' => (string) $idBodega,
            'motivo_salida' => in_array($motivoSalida, ['normal', 'devolucion', 'fallo'], true) ? $motivoSalida : 'normal',
            'detalles' => array_map(
                static fn(array $detalle): array => [
                    'codigo' => (string) ($detalle['codigo'] ?? ''),
                    'descripcion' => (string) ($detalle['descripcion'] ?? ''),
                    'stock' => (string) max(0, (int) ($detalle['stock'] ?? 0)),
                    'cantidad' => (string) max(0, (int) ($detalle['cantidad'] ?? 0)),
                    'precio' => number_format(max(0, (float) ($detalle['precio'] ?? 0)), 0, ',', '.'),
                ],
                $detallesFactura
            ),
        ];

        if (
            $idBodega <= 0
            || $idUsuario <= 0
            || !in_array($motivoSalida, ['normal', 'devolucion', 'fallo'], true)
        ) {
            $mensajeError = 'Completa correctamente bodega y motivo de salida.';
        } else {
            $lineasIncompletas = array_filter(
                $detallesFactura,
                static fn(array $detalle): bool =>
                    (
                        trim((string) ($detalle['codigo'] ?? '')) !== ''
                        || trim((string) ($detalle['descripcion'] ?? '')) !== ''
                        || (int) ($detalle['cantidad'] ?? 0) > 0
                    )
                    && (
                        trim((string) ($detalle['codigo'] ?? '')) === ''
                        || (int) ($detalle['cantidad'] ?? 0) <= 0
                    )
            );

            if (count($lineasIncompletas) > 0) {
                $mensajeError = 'Hay lineas incompletas. Cada linea debe tener codigo y cantidad valida.';
            } else {
                $lineasValidas = array_filter(
                    $detallesFactura,
                    static fn(array $detalle): bool =>
                        trim((string) ($detalle['codigo'] ?? '')) !== ''
                        && (int) ($detalle['cantidad'] ?? 0) > 0
                );

                if (count($lineasValidas) === 0) {
                    $mensajeError = 'Agrega al menos una linea valida en la factura de salida.';
                } else {
                    try {
                        $repositorioSalida->registrarSalidaFactura([
                            'codigo_factura' => $codigoFactura,
                            'id_bodega' => $idBodega,
                            'id_usuario' => $idUsuario,
                            'motivo_salida' => $motivoSalida,
                        ], array_values($lineasValidas));

                        $mensajeExito = 'Factura de salida registrada correctamente con ' . count($lineasValidas) . ' producto(s).';
                            $formularioSalida = [
                                'codigo_factura' => $repositorioSalida->obtenerSiguienteCodigoFactura(),
                                'id_bodega' => '',
                                'motivo_salida' => 'normal',
                            'detalles' => [[
                                'codigo' => '',
                                'descripcion' => '',
                                'stock' => '0',
                                'cantidad' => '1',
                                'precio' => '0',
                            ]],
                        ];
                    } catch (Throwable $error) {
                        $mensajeError = $error->getMessage();
                    }
                }
            }
        }
    }
    }
}

if (($formularioSalida['codigo_factura'] ?? '') === '') {
    $formularioSalida['codigo_factura'] = $repositorioSalida->obtenerSiguienteCodigoFactura();
}

$totalRegistrosHistorial = $repositorioSalida->contarHistorial();
$totalPaginas = max(1, (int) ceil($totalRegistrosHistorial / $porPagina));
if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}
$offset = ($paginaActual - 1) * $porPagina;
$historialSalidas = $repositorioSalida->obtenerHistorial($porPagina, $offset);

$datosModulo = prepararDatosModuloSalidas([
    'resumen' => $repositorioSalida->obtenerResumenIndicadores(),
    'historialSalidas' => $historialSalidas,
    'bodegas' => $repositorioBodega->obtenerActivas(),
    'formularioSalida' => $formularioSalida,
    'mensajeExito' => $mensajeExito,
    'mensajeError' => $mensajeError,
    'paginacion' => [
        'paginaActual' => $paginaActual,
        'totalPaginas' => $totalPaginas ?? 1,
        'totalRegistros' => $totalRegistrosHistorial ?? 0,
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
$formularioSalida = $datosModulo['formularioSalida'];
$historialSalidas = $datosModulo['historialSalidas'];
$mensajeExito = $datosModulo['mensajeExito'];
$mensajeError = $datosModulo['mensajeError'];
$paginacion = $datosModulo['paginacion'];
$urlScriptSalidas = construirUrlPublica('js/panel/salidas.js');
$urlApiProductoSalida = construirUrlPublica('api/salidas/producto.php');

ob_start();
require __DIR__ . '/../modulos/vista_salidas.php';
$contenidoModulo = ob_get_clean();

ob_start();
?>
<script>
window.URL_API_PRODUCTO_SALIDA = <?= json_encode($urlApiProductoSalida, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= htmlspecialchars($urlScriptSalidas, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php
$scriptsModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
