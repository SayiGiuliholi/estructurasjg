<?php

declare(strict_types=1);

/**
 * Prepara la informacion visible del modulo de productos para mantener
 * la vista enfocada en estructura y presentacion.
 */
function prepararDatosModuloProductos(): array
{
    return [
        'tituloPagina' => 'Productos',
        'tituloSeccion' => 'Gestion de productos',
        'descripcionSeccion' => 'Consolida el catalogo del inventario en una sola vista: tabla central, datos clave del stock y acciones visuales de mantenimiento.',
        'moduloActivo' => 'productos',
        'resaltarConfiguracion' => false,
        'resumenIndicadores' => [
            ['etiqueta' => 'Productos registrados', 'valor' => '142'],
            ['etiqueta' => 'Stock total', 'valor' => '3,428'],
            ['etiqueta' => 'Stock bajo', 'valor' => '11'],
            ['etiqueta' => 'Valor estimado', 'valor' => '$ 84,250.00'],
        ],
        'catalogoProductos' => [
            [
                'codigo' => 'PRD-00125',
                'descripcion' => 'Perfil estructural galvanizado',
                'proveedor' => 'Acero Nacional SAS',
                'stock' => '95',
                'precio' => '$ 35.50',
                'estado' => 'Disponible',
                'tipoEstado' => 'ok',
            ],
            [
                'codigo' => 'PRD-00102',
                'descripcion' => 'Tubo rectangular 2x1',
                'proveedor' => 'Ferreteria Industrial Norte',
                'stock' => '32',
                'precio' => '$ 18.90',
                'estado' => 'Stock medio',
                'tipoEstado' => 'alerta',
            ],
            [
                'codigo' => 'PRD-00061',
                'descripcion' => 'Angulo estructural 3/16',
                'proveedor' => 'Materiales JG',
                'stock' => '5',
                'precio' => '$ 41.00',
                'estado' => 'Stock bajo',
                'tipoEstado' => 'critico',
            ],
        ],
        'formularioProducto' => [
            'codigo' => 'PRD-00143',
            'descripcion' => 'Viga laminada estructural',
            'proveedores' => [
                'Acero Nacional SAS',
                'Materiales JG',
            ],
            'stock' => '24',
            'precio' => '57.80',
        ],
        'controlVisual' => [
            [
                'titulo' => 'CRUD disponible',
                'detalle' => 'La interfaz contempla crear, editar y eliminar productos.',
                'estado' => 'Activo',
                'tipoEstado' => 'ok',
            ],
            [
                'titulo' => 'Proveedor asociado',
                'detalle' => 'Cada producto mantiene vinculo visual con su proveedor.',
                'estado' => 'Trazable',
                'tipoEstado' => 'ok',
            ],
            [
                'titulo' => 'Inventario sin duplicacion',
                'detalle' => 'Este modulo concentra el catalogo completo sin repetir tarjetas en otras pantallas.',
                'estado' => 'Unificado',
                'tipoEstado' => 'alerta',
            ],
        ],
    ];
}
