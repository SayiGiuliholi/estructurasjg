<?php

declare(strict_types=1);

/**
 * Prepara los datos visibles del modulo de configuracion para dejar
 * la vista principal enfocada en la estructura.
 */
function prepararDatosModuloConfiguracion(): array
{
    return [
        'tituloPagina' => 'Configuracion',
        'tituloSeccion' => 'Configuracion del sistema',
        'descripcionSeccion' => 'Pantalla de ajustes del sistema con controles visibles para administracion, permisos y apariencia, separada de los modulos operativos.',
        'moduloActivo' => '',
        'resaltarConfiguracion' => true,
        'configuracionUsuarios' => [
            'permitirNuevosUsuarios' => true,
            'sesionUnica' => false,
            'rolPorDefecto' => ['Operador', 'Consulta', 'Administrador'],
        ],
        'roles' => [
            [
                'titulo' => 'Rol administrador',
                'detalle' => 'Acceso completo a configuracion y operaciones del sistema.',
                'etiqueta' => 'Editar',
                'activo' => true,
            ],
            [
                'titulo' => 'Rol operador',
                'detalle' => 'Movimientos de inventario, consulta y ejecucion diaria.',
                'etiqueta' => 'Editar',
                'activo' => false,
            ],
            [
                'titulo' => 'Rol consulta',
                'detalle' => 'Lectura general sin permisos de modificacion.',
                'etiqueta' => 'Editar',
                'activo' => false,
            ],
        ],
        'nivelesControl' => ['Estricto', 'Intermedio', 'Flexible'],
        'temasVisuales' => [
            ['titulo' => 'Claro', 'detalle' => 'Equilibrado y profesional', 'activo' => true],
            ['titulo' => 'Suave', 'detalle' => 'Menos contraste visual', 'activo' => false],
            ['titulo' => 'Industrial', 'detalle' => 'Tonos mas sobrios', 'activo' => false],
        ],
        'tamanosLetra' => [
            ['titulo' => 'Compacto', 'activo' => false],
            ['titulo' => 'Normal', 'activo' => true],
            ['titulo' => 'Grande', 'activo' => false],
        ],
        'ajustesInterfaz' => [
            [
                'titulo' => 'Animaciones suaves',
                'detalle' => 'Mejora la transicion visual del panel.',
                'activo' => true,
            ],
            [
                'titulo' => 'Tarjetas compactas',
                'detalle' => 'Reduce espacios para mostrar mas informacion.',
                'activo' => false,
            ],
        ],
        'ayudaContextual' => true,
        'notaConfiguracion' => 'La navegacion principal sigue reservada exclusivamente para Entradas, Productos, Proveedores y Salidas.',
    ];
}
