<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_entradas.php';
require_once __DIR__ . '/../../../configuracion/rutas.php';
require_once __DIR__ . '/../../../modelos/RepositorioEntrada.php';
require_once __DIR__ . '/../../../modelos/RepositorioProveedor.php';
require_once __DIR__ . '/../../../modelos/RepositorioBodega.php';

$repositorioEntrada = new RepositorioEntrada();
$repositorioProveedor = new RepositorioProveedor();
$repositorioBodega = new RepositorioBodega();

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

$formularioEntrada = [
    'codigo_factura' => '',
    'id_proveedor' => '',
    'id_bodega' => '',
    'detalles' => [[
        'codigo' => '',
        'descripcion' => '',
        'cantidad' => '1',
        'precio' => '0',
    ]],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim((string) ($_POST['accion'] ?? ''));

    if ($accion === 'guardar') {
        $codigoFactura = trim((string) ($_POST['codigo_factura'] ?? ''));
        $idProveedor = (int) ($_POST['id_proveedor'] ?? 0);
        $idBodega = (int) ($_POST['id_bodega'] ?? 0);
        $idUsuario = (int) ($autenticacion['id_usuario'] ?? 0);

        $codigos = is_array($_POST['codigo_producto'] ?? null) ? $_POST['codigo_producto'] : [];
        $descripciones = is_array($_POST['descripcion_producto'] ?? null) ? $_POST['descripcion_producto'] : [];
        $cantidades = is_array($_POST['cantidad_producto'] ?? null) ? $_POST['cantidad_producto'] : [];
        $precios = is_array($_POST['precio_producto'] ?? null) ? $_POST['precio_producto'] : [];

        $detallesFactura = [];
        $cantidadLineas = max(count($codigos), count($descripciones), count($cantidades), count($precios));

        for ($indice = 0; $indice < $cantidadLineas; $indice++) {
            $codigo = trim((string) ($codigos[$indice] ?? ''));
            $descripcion = trim((string) ($descripciones[$indice] ?? ''));
            $cantidad = (int) ($cantidades[$indice] ?? 0);
            $precio = $normalizarMoneda($precios[$indice] ?? 0);

            if ($codigo === '' && $descripcion === '' && $cantidad === 0 && $precio === 0.0) {
                continue;
            }

            $detallesFactura[] = [
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'cantidad' => max(0, $cantidad),
                'precio' => max(0, $precio),
            ];
        }

        if (count($detallesFactura) === 0) {
            $detallesFactura[] = [
                'codigo' => '',
                'descripcion' => '',
                'cantidad' => '1',
                'precio' => '0',
            ];
        }

        $formularioEntrada = [
            'codigo_factura' => $codigoFactura,
            'id_proveedor' => (string) $idProveedor,
            'id_bodega' => (string) $idBodega,
            'detalles' => array_map(
                static fn(array $detalle): array => [
                    'codigo' => (string) ($detalle['codigo'] ?? ''),
                    'descripcion' => (string) ($detalle['descripcion'] ?? ''),
                    'cantidad' => (string) max(0, (int) ($detalle['cantidad'] ?? 0)),
                    'precio' => number_format(max(0, (float) ($detalle['precio'] ?? 0)), 0, '.', ''),
                ],
                $detallesFactura
            ),
        ];

        if (
            $idProveedor <= 0
            || $idBodega <= 0
            || $idUsuario <= 0
        ) {
            $mensajeError = 'Completa proveedor y bodega para registrar la factura.';
        } else {
            $lineasIncompletas = array_filter(
                $detallesFactura,
                static fn(array $detalle): bool =>
                    (
                        trim((string) ($detalle['codigo'] ?? '')) !== ''
                        || trim((string) ($detalle['descripcion'] ?? '')) !== ''
                        || (int) ($detalle['cantidad'] ?? 0) > 0
                        || (float) ($detalle['precio'] ?? 0) > 0
                    )
                    && (
                        trim((string) ($detalle['codigo'] ?? '')) === ''
                        || trim((string) ($detalle['descripcion'] ?? '')) === ''
                        || (int) ($detalle['cantidad'] ?? 0) <= 0
                    )
            );

            if (count($lineasIncompletas) > 0) {
                $mensajeError = 'Hay lineas incompletas. Cada producto debe tener codigo, descripcion, cantidad y precio.';
                $lineasValidas = [];
            } else {
            $lineasValidas = array_filter(
                $detallesFactura,
                static fn(array $detalle): bool =>
                    trim((string) ($detalle['codigo'] ?? '')) !== ''
                    && trim((string) ($detalle['descripcion'] ?? '')) !== ''
                    && (int) ($detalle['cantidad'] ?? 0) > 0
            );
            }

            if (count($lineasValidas) === 0) {
                $mensajeError = 'Agrega al menos una linea de producto valida en la factura.';
            } else {
                try {
                    $repositorioEntrada->registrarEntradaFactura([
                        'codigo_factura' => $codigoFactura,
                        'id_proveedor' => $idProveedor,
                        'id_bodega' => $idBodega,
                        'id_usuario' => $idUsuario,
                    ], array_values($lineasValidas));

                    $mensajeExito = 'Factura de entrada registrada correctamente con ' . count($lineasValidas) . ' producto(s).';
                    $formularioEntrada = [
                        'codigo_factura' => '',
                        'id_proveedor' => '',
                        'id_bodega' => '',
                        'detalles' => [[
                            'codigo' => '',
                            'descripcion' => '',
                            'cantidad' => '1',
                            'precio' => '0',
                        ]],
                    ];
                } catch (Throwable $error) {
                    $mensajeError = 'No fue posible registrar la factura. Revisa datos duplicados o relaciones de la base de datos.';
                }
            }
        }
    }
}

$resumen = $repositorioEntrada->obtenerResumenIndicadores();
$ultimoMovimiento = $repositorioEntrada->obtenerUltimoMovimiento();
$totalRegistrosHistorial = $repositorioEntrada->contarHistorial();
$totalPaginas = max(1, (int) ceil($totalRegistrosHistorial / $porPagina));
if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}
$offset = ($paginaActual - 1) * $porPagina;
$historialEntradas = $repositorioEntrada->obtenerHistorial($porPagina, $offset);
$proveedores = $repositorioProveedor->obtenerTodos();
$bodegas = $repositorioBodega->obtenerActivas();

$datosModulo = prepararDatosModuloEntradas([
    'resumen' => $resumen,
    'ultimoMovimiento' => $ultimoMovimiento,
    'historialEntradas' => $historialEntradas,
    'proveedores' => $proveedores,
    'bodegas' => $bodegas,
    'formularioEntrada' => $formularioEntrada,
    'mensajeExito' => $mensajeExito,
    'mensajeError' => $mensajeError,
    'paginacion' => [
        'paginaActual' => $paginaActual,
        'totalPaginas' => $totalPaginas,
        'totalRegistros' => $totalRegistrosHistorial,
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
$formularioEntrada = $datosModulo['formularioEntrada'];
$historialEntradas = $datosModulo['historialEntradas'];
$mensajeExito = $datosModulo['mensajeExito'];
$mensajeError = $datosModulo['mensajeError'];
$paginacion = $datosModulo['paginacion'];
$urlScriptEntradas = construirUrlPublica('js/panel/entradas.js');

ob_start();
require __DIR__ . '/../modulos/vista_entradas.php';
$contenidoModulo = ob_get_clean();

ob_start();
?>
<script src="<?= htmlspecialchars($urlScriptEntradas, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php
$scriptsModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
