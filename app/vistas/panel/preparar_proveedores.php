<?php

declare(strict_types=1);

/**
 * Prepara los datos visibles del modulo de proveedores para mantener
 * la vista enfocada en la estructura HTML.
 */
function prepararDatosModuloProveedores(): array
{
    return [
        'tituloPagina' => 'Proveedores',
        'tituloSeccion' => 'Gestion de proveedores',
        'descripcionSeccion' => 'Administra proveedores desde una vista exclusiva con formulario, tabla de consulta y acciones listas para un flujo CRUD.',
        'moduloActivo' => 'proveedores',
        'resaltarConfiguracion' => false,
        'fichaProveedor' => [
            'ruc' => '900123456-7',
            'nombre' => 'Acero Nacional SAS',
            'telefono' => '+57 300 555 1122',
            'direccion' => 'Zona industrial, calle 18 # 24-35',
        ],
        'indicadores' => [
            [
                'titulo' => 'Proveedores registrados',
                'detalle' => 'Base de abastecimiento activa del sistema.',
                'valor' => '24',
                'tipo' => 'valor',
            ],
            [
                'titulo' => 'Contactables',
                'detalle' => 'Con telefono y direccion completos.',
                'valor' => '20',
                'tipo' => 'estado-ok',
            ],
            [
                'titulo' => 'Con movimientos recientes',
                'detalle' => 'Usados en compras durante la ultima semana.',
                'valor' => '7',
                'tipo' => 'estado-alerta',
            ],
        ],
        'directorioProveedores' => [
            [
                'ruc' => '900123456-7',
                'nombre' => 'Acero Nacional SAS',
                'telefono' => '+57 300 555 1122',
                'direccion' => 'Zona industrial, calle 18 # 24-35',
                'estado' => 'Activo',
                'tipoEstado' => 'ok',
            ],
            [
                'ruc' => '901778320-1',
                'nombre' => 'Materiales JG',
                'telefono' => '+57 301 444 8831',
                'direccion' => 'Avenida central # 45-90',
                'estado' => 'Activo',
                'tipoEstado' => 'ok',
            ],
            [
                'ruc' => '890771235-9',
                'nombre' => 'Ferreteria Industrial Norte',
                'telefono' => '+57 304 778 0011',
                'direccion' => 'Carrera 12 # 9-88',
                'estado' => 'Pendiente',
                'tipoEstado' => 'alerta',
            ],
        ],
    ];
}
