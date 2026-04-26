<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../modelos/Proveedor.php';

/**
 * Prepara los datos del modulo de entradas con informacion real de base de datos.
 */
function prepararDatosModuloEntradas(array $contexto = []): array
{
    $formatearMoneda = static fn(float $valor): string => '$ ' . number_format($valor, 0, ',', '.');
    $resumen = $contexto['resumen'] ?? [];
    $ultimoMovimiento = $contexto['ultimoMovimiento'] ?? null;
    $historial = $contexto['historialEntradas'] ?? [];
    $bodegas = $contexto['bodegas'] ?? [];

    /** @var Proveedor[] $proveedores */
    $proveedores = $contexto['proveedores'] ?? [];

    $formulario = $contexto['formularioEntrada'] ?? [
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

    $mensajeExito = trim((string) ($contexto['mensajeExito'] ?? ''));
    $mensajeError = trim((string) ($contexto['mensajeError'] ?? ''));
    $paginacion = $contexto['paginacion'] ?? [
        'paginaActual' => 1,
        'totalPaginas' => 1,
        'totalRegistros' => count($historial),
        'porPagina' => 20,
        'opcionesPorPagina' => [10, 20, 50],
    ];

    $resumenIndicadores = [
        [
            'etiqueta' => 'Entradas de hoy',
            'valor' => (string) ((int) ($resumen['entradas_hoy'] ?? 0)),
        ],
        [
            'etiqueta' => 'Unidades ingresadas',
            'valor' => number_format((int) ($resumen['unidades_hoy'] ?? 0), 0, '.', ','),
        ],
        [
            'etiqueta' => 'Valor acumulado',
            'valor' => $formatearMoneda((float) ($resumen['valor_hoy'] ?? 0)),
        ],
        [
            'etiqueta' => 'Proveedores activos',
            'valor' => (string) ((int) ($resumen['proveedores_activos'] ?? 0)),
        ],
    ];

    $opcionesProveedores = array_map(
        static fn(Proveedor $proveedor): array => [
            'id' => $proveedor->idProveedor,
            'nombre' => $proveedor->nombre,
        ],
        $proveedores
    );

    $opcionesBodegas = array_map(
        static fn(array $bodega): array => [
            'id' => (int) ($bodega['id_bodega'] ?? 0),
            'nombre' => (string) ($bodega['nombre'] ?? ''),
            'codigo' => (string) ($bodega['codigo'] ?? ''),
        ],
        $bodegas
    );

    $resumenOperativo = [
        [
            'titulo' => 'Ultimo movimiento',
            'detalle' => $ultimoMovimiento !== null
                ? ((string) ($ultimoMovimiento['descripcion'] ?? 'Sin descripcion')
                    . ' | '
                    . (string) ($ultimoMovimiento['proveedor'] ?? 'Proveedor no definido'))
                : 'Aun no hay entradas registradas.',
            'valor' => $formatearMoneda((float) ($ultimoMovimiento['total'] ?? 0)),
            'tipo' => 'valor',
        ],
        [
            'titulo' => 'Actualizacion de stock',
            'detalle' => $ultimoMovimiento !== null
                ? 'Stock actual de ' . (string) ($ultimoMovimiento['codigo_producto'] ?? '')
                    . ': ' . (string) ($ultimoMovimiento['stock'] ?? 0) . ' unidades'
                : 'Se actualizara automaticamente al registrar una entrada.',
            'valor' => 'Automatico',
            'tipo' => 'estado-ok',
        ],
        [
            'titulo' => 'Fuente de registro',
            'detalle' => 'Este modulo guarda compras, detalle y stock por producto/bodega.',
            'valor' => 'BD real',
            'tipo' => 'estado-alerta',
        ],
    ];

    $historialEntradas = array_map(
        static function (array $fila) use ($formatearMoneda): array {
            $stock = (int) ($fila['stock'] ?? 0);
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
                'codigo_compra' => (string) ($fila['codigo_compra'] ?? ''),
                'codigo_producto' => (string) ($fila['codigo_producto'] ?? ''),
                'descripcion' => (string) ($fila['descripcion'] ?? ''),
                'cantidad' => (string) ((int) ($fila['cantidad'] ?? 0)),
                'precio' => $formatearMoneda((float) ($fila['precio'] ?? 0)),
                'proveedor' => (string) ($fila['proveedor'] ?? ''),
                'bodega' => (string) ($fila['bodega'] ?? 'Sin bodega'),
                'total' => $formatearMoneda((float) ($fila['total'] ?? 0)),
                'stock' => (string) $stock . ' und',
                'estado' => $stock <= 10 ? 'alerta' : 'ok',
                'hora_registro' => $horaRegistro,
                'fecha_registro' => $fechaRegistro,
            ];
        },
        $historial
    );

    $detallesFormulario = $formulario['detalles'] ?? [];
    if (count($detallesFormulario) === 0) {
        $detallesFormulario = [[
            'codigo' => '',
            'descripcion' => '',
            'cantidad' => '1',
            'precio' => '0',
        ]];
    }

    $detallesNormalizados = array_map(
        static fn(array $detalle): array => [
            'codigo' => (string) ($detalle['codigo'] ?? ''),
            'descripcion' => (string) ($detalle['descripcion'] ?? ''),
            'cantidad' => (string) max(0, (int) ($detalle['cantidad'] ?? 0)),
            'precio' => number_format(max(0, (float) ($detalle['precio'] ?? 0)), 2, '.', ''),
        ],
        $detallesFormulario
    );

    $totalFactura = 0.0;
    foreach ($detallesNormalizados as $detalle) {
        $totalFactura += ((int) $detalle['cantidad'] * (float) $detalle['precio']);
    }

    return [
        'tituloPagina' => 'Entradas',
        'tituloSeccion' => 'Gestion de entradas',
        'descripcionSeccion' => 'Registra los productos que ingresan a tu inventario.',
        'moduloActivo' => 'entradas',
        'resaltarConfiguracion' => false,
        'resumenIndicadores' => $resumenIndicadores,
        'formularioEntrada' => [
            'codigo_factura' => (string) ($formulario['codigo_factura'] ?? ''),
            'id_proveedor' => (string) ($formulario['id_proveedor'] ?? ''),
            'id_bodega' => (string) ($formulario['id_bodega'] ?? ''),
            'total_factura' => $formatearMoneda($totalFactura),
            'detalles' => $detallesNormalizados,
            'proveedores' => $opcionesProveedores,
            'bodegas' => $opcionesBodegas,
        ],
        'resumenOperativo' => $resumenOperativo,
        'notaModulo' => 'Cada registro actualiza compras, detalle_compras, productos.stock y stock_bodega.',
        'historialEntradas' => $historialEntradas,
        'mensajeExito' => $mensajeExito,
        'mensajeError' => $mensajeError,
        'paginacion' => $paginacion,
    ];
}
