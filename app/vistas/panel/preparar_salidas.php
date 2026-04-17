<?php

declare(strict_types=1);

/**
 * Prepara los datos visibles del modulo de salidas para mantener la vista
 * enfocada en estructura y presentacion.
 */
function prepararDatosModuloSalidas(): array
{
    return [
        'tituloPagina' => 'Salidas',
        'tituloSeccion' => 'Gestion de salidas',
        'descripcionSeccion' => 'Centraliza el registro de ventas y despachos en un solo modulo con validacion visual de stock y descuento automatico en pantalla.',
        'moduloActivo' => 'salidas',
        'resaltarConfiguracion' => false,
        'resumenIndicadores' => [
            ['etiqueta' => 'Ventas de hoy', 'valor' => '11'],
            ['etiqueta' => 'Unidades despachadas', 'valor' => '73'],
            ['etiqueta' => 'Ingresos', 'valor' => '$ 4,925.20'],
            ['etiqueta' => 'Alertas de stock', 'valor' => '3'],
        ],
        'formularioSalida' => [
            'codigo' => 'PRD-00125',
            'descripcion' => 'Perfil estructural galvanizado',
            'stock' => '95',
            'cantidad' => '10',
            'precio' => '42.00',
            'descuento' => '5',
            'subtotal' => '$ 420.00',
            'total' => '$ 399.00',
            'mensajeValidacion' => 'Stock suficiente para completar la venta.',
            'estadoStock' => 'Correcto',
            'tipoEstado' => 'ok',
        ],
        'estadoDespacho' => [
            [
                'titulo' => 'Validacion de stock',
                'detalle' => 'La vista te indica si la cantidad excede lo disponible.',
                'estado' => 'Correcto',
                'tipoEstado' => 'ok',
                'id' => 'estado-stock',
            ],
            [
                'titulo' => 'Descuento automatico',
                'detalle' => 'Se aplica sobre el subtotal en tiempo real.',
                'estado' => 'Activo',
                'tipoEstado' => 'ok',
                'id' => null,
            ],
            [
                'titulo' => 'Control operativo',
                'detalle' => 'Las ventas estan concentradas solo en este modulo.',
                'estado' => 'Unificado',
                'tipoEstado' => 'alerta',
                'id' => null,
            ],
        ],
        'historialSalidas' => [
            [
                'factura' => 'FV-000781',
                'codigo' => 'PRD-00125',
                'producto' => 'Perfil estructural galvanizado',
                'cantidad' => '10',
                'descuento' => '5%',
                'total' => '$ 399.00',
                'estado' => 'Despachado',
                'tipoEstado' => 'ok',
            ],
            [
                'factura' => 'FV-000780',
                'codigo' => 'PRD-00102',
                'producto' => 'Tubo rectangular 2x1',
                'cantidad' => '6',
                'descuento' => '0%',
                'total' => '$ 113.40',
                'estado' => 'Procesando',
                'tipoEstado' => 'alerta',
            ],
            [
                'factura' => 'FV-000779',
                'codigo' => 'PRD-00061',
                'producto' => 'Angulo estructural 3/16',
                'cantidad' => '4',
                'descuento' => '8%',
                'total' => '$ 150.88',
                'estado' => 'Stock critico',
                'tipoEstado' => 'critico',
            ],
        ],
    ];
}
