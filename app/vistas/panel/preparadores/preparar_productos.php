<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../modelos/Producto.php';

/**
 * Prepara datos del modulo de productos en modo consulta.
 */
function prepararDatosModuloProductos(array $contexto = []): array
{
    $formatearMoneda = static fn(float $valor): string => '$' . number_format($valor, 0, ',', '.');
    $extraerStockPorBodega = static function (string $resumenBodegas): array {
        $resultado = [];
        if ($resumenBodegas === '') {
            return $resultado;
        }

        if (preg_match_all('/(BOD-\d+)\s*\((\d+)\)/i', $resumenBodegas, $coincidencias, PREG_SET_ORDER) !== false) {
            foreach ($coincidencias as $coincidencia) {
                $codigoBodega = strtoupper((string) ($coincidencia[1] ?? ''));
                $stockBodega = (int) ($coincidencia[2] ?? 0);
                if ($codigoBodega !== '') {
                    $resultado[$codigoBodega] = $stockBodega;
                }
            }
        }

        return $resultado;
    };
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
            $marcaTiempo = $producto->fecha !== null ? strtotime($producto->fecha) : false;
            $fechaRegistro = $marcaTiempo !== false ? date('d/m/Y', $marcaTiempo) : '';

            return [
                'id_producto' => $producto->idProducto,
                'factura' => trim($producto->ultimaFactura) !== '' ? $producto->ultimaFactura : 'Sin factura',
                'codigo' => $producto->codigo,
                'descripcion' => $producto->descripcion,
                'proveedor' => $producto->nombreProveedor,
                'fecha_registro' => $fechaRegistro,
                'activo' => $producto->activo,
                'estado' => $producto->activo ? 'Activo' : 'Inactivo',
                'tipoEstado' => $producto->activo ? 'ok' : 'alerta',
                'resumen_bodegas_raw' => (string) $producto->resumenBodegas,
                'bodegas' => trim($producto->resumenBodegas) !== ''
                    ? (preg_replace('/\s*\(\d+\)/', '', $producto->resumenBodegas) ?: 'Sin bodega')
                    : 'Sin bodega',
                'stock' => (string) $producto->stock,
                'precio' => $formatearMoneda($producto->precio),
            ];
        },
        $productos
    );

    $catalogoPrincipal = [];
    $catalogoSecundaria = [];
    foreach ($catalogoProductos as $item) {
        $bodegas = strtolower((string) ($item['bodegas'] ?? ''));
        $stocksPorBodega = $extraerStockPorBodega((string) ($item['resumen_bodegas_raw'] ?? ''));
        $enPrincipal = str_contains($bodegas, 'bod-01') || str_contains($bodegas, 'principal');
        $enSecundaria = str_contains($bodegas, 'bod-02') || str_contains($bodegas, 'secundaria');

        if ($enPrincipal) {
            $itemPrincipal = $item;
            if (array_key_exists('BOD-01', $stocksPorBodega)) {
                $itemPrincipal['stock'] = (string) $stocksPorBodega['BOD-01'];
            }
            $catalogoPrincipal[] = $itemPrincipal;
        }
        if ($enSecundaria) {
            $itemSecundaria = $item;
            if (array_key_exists('BOD-02', $stocksPorBodega)) {
                $itemSecundaria['stock'] = (string) $stocksPorBodega['BOD-02'];
            }
            $catalogoSecundaria[] = $itemSecundaria;
        }
    }

    $resumenIndicadores = [
        [
            'etiqueta' => 'Unidades en inventario',
            'valor' => number_format((float) ($contexto['stockTotal'] ?? 0), 0, '.', ','),
        ],
        [
            'etiqueta' => 'Productos con bajo stock',
            'valor' => (string) ($contexto['stockBajo'] ?? 0),
        ],
        [
            'etiqueta' => 'Valor total del inventario',
            'valor' => $formatearMoneda((float) ($contexto['valorEstimado'] ?? 0)),
        ],
    ];

    return [
        'tituloPagina' => 'Productos',
        'tituloSeccion' => 'Inventario de productos',
        'descripcionSeccion' => 'Visualiza el stock actual de tus productos en tiempo real.',
        'moduloActivo' => 'productos',
        'resaltarConfiguracion' => false,
        'resumenIndicadores' => $resumenIndicadores,
        'catalogoProductos' => $catalogoProductos,
        'catalogoPrincipal' => $catalogoPrincipal,
        'catalogoSecundaria' => $catalogoSecundaria,
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
