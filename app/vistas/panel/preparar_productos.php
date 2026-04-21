<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelos/Producto.php';

/**
 * Prepara datos del modulo de productos en modo consulta.
 */
function prepararDatosModuloProductos(array $contexto = []): array
{
    $formatearMoneda = static fn(float $valor): string => '$ ' . number_format($valor, 0, ',', '.');
    /** @var Producto[] $productos */
    $productos = $contexto['catalogoProductos'] ?? [];
    $paginacion = $contexto['paginacion'] ?? [
        'paginaActual' => 1,
        'totalPaginas' => 1,
        'totalRegistros' => count($productos),
        'porPagina' => 20,
        'opcionesPorPagina' => [10, 20, 50],
    ];

    $catalogoProductos = array_map(
        static function (Producto $producto) use ($formatearMoneda): array {
            return [
                'id_producto' => $producto->idProducto,
                'factura' => trim($producto->ultimaFactura) !== '' ? $producto->ultimaFactura : 'Sin factura',
                'codigo' => $producto->codigo,
                'descripcion' => $producto->descripcion,
                'proveedor' => $producto->nombreProveedor,
                'bodegas' => trim($producto->resumenBodegas) !== ''
                    ? (preg_replace('/\s*\(\d+\)/', '', $producto->resumenBodegas) ?: 'Sin bodega')
                    : 'Sin bodega',
                'stock' => (string) $producto->stock,
                'precio' => $formatearMoneda($producto->precio),
            ];
        },
        $productos
    );

    $resumenIndicadores = [
        [
            'etiqueta' => 'Productos registrados',
            'valor' => (string) ($contexto['totalProductos'] ?? count($productos)),
        ],
        [
            'etiqueta' => 'Stock total',
            'valor' => number_format((float) ($contexto['stockTotal'] ?? 0), 0, '.', ','),
        ],
        [
            'etiqueta' => 'Stock bajo',
            'valor' => (string) ($contexto['stockBajo'] ?? 0),
        ],
        [
            'etiqueta' => 'Valor estimado',
            'valor' => $formatearMoneda((float) ($contexto['valorEstimado'] ?? 0)),
        ],
    ];

    return [
        'tituloPagina' => 'Productos',
        'tituloSeccion' => 'Consulta de productos',
        'descripcionSeccion' => 'Este modulo es solo de consulta. El stock se actualiza por movimientos operativos del inventario.',
        'moduloActivo' => 'productos',
        'resaltarConfiguracion' => false,
        'resumenIndicadores' => $resumenIndicadores,
        'catalogoProductos' => $catalogoProductos,
        'paginacion' => $paginacion,
        'controlVisual' => [
            [
                'titulo' => 'Origen del catalogo',
                'detalle' => 'Cada fila proviene directamente de la tabla productos.',
                'estado' => 'BD real',
                'tipoEstado' => 'ok',
            ],
            [
                'titulo' => 'Actualizacion de stock',
                'detalle' => 'Entradas y salidas actualizan stock automaticamente desde sus modulos.',
                'estado' => 'Sincronizado',
                'tipoEstado' => 'ok',
            ],
            [
                'titulo' => 'Politica de modulo',
                'detalle' => 'No se crean productos aqui para evitar duplicidad de registro.',
                'estado' => 'Solo lectura',
                'tipoEstado' => 'alerta',
            ],
        ],
    ];
}
