<?php

declare(strict_types=1);

/**
 * Prepara los datos visibles del modulo de salidas con soporte de factura multiproducto.
 */
function prepararDatosModuloSalidas(array $contexto = []): array
{
    $formatearMoneda = static fn(float $valor): string => '$ ' . number_format($valor, 0, ',', '.');
    $parsearMoneda = static function (string $valor): float {
        $limpio = preg_replace('/[^\d\-]/', '', $valor) ?? '';
        if ($limpio === '' || $limpio === '-') {
            return 0.0;
        }
        return (float) $limpio;
    };
    $resumen = $contexto['resumen'] ?? [];
    $historial = $contexto['historialSalidas'] ?? [];
    $bodegas = $contexto['bodegas'] ?? [];

    $formulario = $contexto['formularioSalida'] ?? [
        'codigo_factura' => '',
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

    $mensajeExito = trim((string) ($contexto['mensajeExito'] ?? ''));
    $mensajeError = trim((string) ($contexto['mensajeError'] ?? ''));
    $paginacion = $contexto['paginacion'] ?? [
        'paginaActual' => 1,
        'totalPaginas' => 1,
        'totalRegistros' => count($historial),
        'porPagina' => 20,
        'opcionesPorPagina' => [10, 20, 50],
    ];

    $detallesFormulario = $formulario['detalles'] ?? [];
    if (count($detallesFormulario) === 0) {
        $detallesFormulario = [[
            'codigo' => '',
            'descripcion' => '',
            'stock' => '0',
            'cantidad' => '1',
            'precio' => '0',
        ]];
    }

    $detallesNormalizados = array_map(
        static fn(array $detalle): array => [
            'codigo' => (string) ($detalle['codigo'] ?? ''),
            'descripcion' => (string) ($detalle['descripcion'] ?? ''),
            'stock' => (string) max(0, (int) ($detalle['stock'] ?? 0)),
            'cantidad' => (string) max(0, (int) ($detalle['cantidad'] ?? 0)),
            'precio' => number_format(max(0, (float) ($detalle['precio'] ?? 0)), 0, ',', '.'),
        ],
        $detallesFormulario
    );

    $totalFactura = 0.0;
    foreach ($detallesNormalizados as $detalle) {
        $totalFactura += ((int) $detalle['cantidad'] * $parsearMoneda((string) $detalle['precio']));
    }

    $opcionesBodegas = array_map(
        static fn(array $bodega): array => [
            'id' => (int) ($bodega['id_bodega'] ?? 0),
            'codigo' => (string) ($bodega['codigo'] ?? ''),
            'nombre' => (string) ($bodega['nombre'] ?? ''),
        ],
        $bodegas
    );

    $resumenIndicadores = [
        ['etiqueta' => 'Ventas de hoy', 'valor' => (string) ((int) ($resumen['ventas_hoy'] ?? 0))],
        ['etiqueta' => 'Unidades despachadas', 'valor' => number_format((int) ($resumen['unidades_hoy'] ?? 0), 0, '.', ',')],
    ];

    $historialSalidas = array_map(
        static function (array $fila) use ($formatearMoneda): array {
            $marcaTiempo = strtotime((string) ($fila['fecha'] ?? ''));
            if ($marcaTiempo === false) {
                $horaRegistro = '--:--';
                $fechaRegistro = '--/--/----';
            } else {
                $horaRegistro = str_replace(
                    ['am', 'pm'],
                    ['a. m.', 'p. m.'],
                    strtolower(date('g:i a', $marcaTiempo))
                );
                $fechaRegistro = date('d/m/Y', $marcaTiempo);
            }

            return [
                'factura' => (string) ($fila['factura'] ?? ''),
                'hora_registro' => $horaRegistro,
                'fecha_registro' => $fechaRegistro,
                'codigo' => (string) ($fila['codigo_producto'] ?? ''),
                'producto' => (string) ($fila['producto'] ?? ''),
                'cantidad' => (string) ((int) ($fila['cantidad'] ?? 0)),
                'motivo_salida' => (string) ($fila['motivo_salida'] ?? 'normal'),
                'total' => $formatearMoneda((float) ($fila['total'] ?? 0)),
                'bodega' => (string) ($fila['bodega'] ?? ''),
            ];
        },
        $historial
    );

    return [
        'tituloPagina' => 'Salidas',
        'tituloSeccion' => 'Gestion de salidas',
        'descripcionSeccion' => 'Registra facturas de salida con multiples productos y descuenta stock por bodega automaticamente.',
        'moduloActivo' => 'salidas',
        'resaltarConfiguracion' => false,
        'resumenIndicadores' => $resumenIndicadores,
        'formularioSalida' => [
            'codigo_factura' => (string) ($formulario['codigo_factura'] ?? ''),
            'id_bodega' => (string) ($formulario['id_bodega'] ?? ''),
            'motivo_salida' => (string) ($formulario['motivo_salida'] ?? 'normal'),
            'detalles' => $detallesNormalizados,
            'total_factura' => $formatearMoneda($totalFactura),
            'bodegas' => $opcionesBodegas,
        ],
        'historialSalidas' => $historialSalidas,
        'mensajeExito' => $mensajeExito,
        'mensajeError' => $mensajeError,
        'paginacion' => $paginacion,
    ];
}
