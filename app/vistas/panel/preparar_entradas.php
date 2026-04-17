<?php

declare(strict_types=1);

/**
 * Prepara los datos base del modulo de entradas para mantener la vista
 * enfocada en la estructura HTML.
 */
function prepararDatosModuloEntradas(): array
{
    return [
        'tituloPagina' => 'Entradas',
        'tituloSeccion' => 'Gestion de entradas',
        'descripcionSeccion' => 'Registra ingresos al inventario desde una sola seccion: formulario operativo, calculo automatico del total y una tabla preparada para operaciones CRUD.',
        'moduloActivo' => 'entradas',
        'resaltarConfiguracion' => false,
        'resumenIndicadores' => [
            ['etiqueta' => 'Entradas de hoy', 'valor' => '18'],
            ['etiqueta' => 'Unidades ingresadas', 'valor' => '254'],
            ['etiqueta' => 'Valor acumulado', 'valor' => '$ 12,840.00'],
            ['etiqueta' => 'Proveedores activos', 'valor' => '9'],
        ],
        'formularioEntrada' => [
            'codigo' => 'PRD-00125',
            'descripcion' => 'Perfil estructural galvanizado',
            'cantidad' => '15',
            'precio' => '35.50',
            'total' => '$ 532.50',
            'proveedores' => [
                'Acero Nacional SAS',
                'Materiales JG',
                'Ferreteria Industrial Norte',
            ],
        ],
        'resumenOperativo' => [
            [
                'titulo' => 'Ultimo movimiento',
                'detalle' => 'Ingreso de perfiles estructurales',
                'valor' => '$ 532.50',
                'tipo' => 'valor',
            ],
            [
                'titulo' => 'Actualizacion de stock',
                'detalle' => 'Disponible despues del registro: 95 unidades',
                'valor' => 'Automatico',
                'tipo' => 'estado-ok',
            ],
            [
                'titulo' => 'CRUD del modulo',
                'detalle' => 'La interfaz ya contempla crear, editar y eliminar registros.',
                'valor' => 'UI lista',
                'tipo' => 'estado-alerta',
            ],
        ],
        'notaModulo' => 'La actualizacion de stock se representa visualmente aqui; no se altero la logica del backend.',
        'historialEntradas' => [
            [
                'codigo' => 'PRD-00125',
                'descripcion' => 'Perfil estructural galvanizado',
                'cantidad' => '15',
                'precio' => '$ 35.50',
                'proveedor' => 'Acero Nacional SAS',
                'total' => '$ 532.50',
                'stock' => '95 und',
                'estado' => 'ok',
            ],
            [
                'codigo' => 'PRD-00098',
                'descripcion' => 'Platina laminada',
                'cantidad' => '8',
                'precio' => '$ 21.80',
                'proveedor' => 'Materiales JG',
                'total' => '$ 174.40',
                'stock' => '48 und',
                'estado' => 'ok',
            ],
            [
                'codigo' => 'PRD-00102',
                'descripcion' => 'Tubo rectangular 2x1',
                'cantidad' => '22',
                'precio' => '$ 18.90',
                'proveedor' => 'Ferreteria Industrial Norte',
                'total' => '$ 415.80',
                'stock' => '32 und',
                'estado' => 'alerta',
            ],
        ],
    ];
}
