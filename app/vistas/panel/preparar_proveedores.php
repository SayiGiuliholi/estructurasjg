<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelos/Proveedor.php';

/**
 * Prepara datos del modulo de proveedores con informacion real.
 */
function prepararDatosModuloProveedores(array $contexto = []): array
{
    /** @var Proveedor[] $proveedores */
    $proveedores = $contexto['proveedores'] ?? [];
    $totalConProductos = (int) ($contexto['totalConProductos'] ?? 0);

    $fichaProveedor = $contexto['fichaProveedor'] ?? [
        'id_proveedor' => '',
        'ruc' => '',
        'nombre' => '',
        'telefono' => '',
        'direccion' => '',
    ];

    $idProveedorEdicion = $contexto['idProveedorEdicion'] ?? null;
    $mensajeExito = trim((string) ($contexto['mensajeExito'] ?? ''));
    $mensajeError = trim((string) ($contexto['mensajeError'] ?? ''));

    $directorioProveedores = array_map(
        static function (Proveedor $proveedor): array {
            $activo = $proveedor->totalProductos > 0;

            return [
                'id_proveedor' => $proveedor->idProveedor,
                'ruc' => $proveedor->ruc,
                'nombre' => $proveedor->nombre,
                'telefono' => $proveedor->telefono,
                'direccion' => $proveedor->direccion,
                'estado' => $activo ? 'Activo' : 'Sin productos',
                'tipoEstado' => $activo ? 'ok' : 'alerta',
            ];
        },
        $proveedores
    );

    $totalProveedores = count($proveedores);
    $totalContactables = 0;

    foreach ($proveedores as $proveedor) {
        if (trim($proveedor->telefono) !== '' && trim($proveedor->direccion) !== '') {
            $totalContactables++;
        }
    }

    return [
        'tituloPagina' => 'Proveedores',
        'tituloSeccion' => 'Gestion de proveedores',
        'descripcionSeccion' => 'Administra proveedores desde una vista exclusiva con formulario, tabla de consulta y acciones listas para un flujo CRUD.',
        'moduloActivo' => 'proveedores',
        'resaltarConfiguracion' => false,
        'fichaProveedor' => $fichaProveedor,
        'idProveedorEdicion' => $idProveedorEdicion,
        'mensajeExito' => $mensajeExito,
        'mensajeError' => $mensajeError,
        'indicadores' => [
            [
                'titulo' => 'Proveedores registrados',
                'detalle' => 'Base de abastecimiento activa del sistema.',
                'valor' => (string) $totalProveedores,
                'tipo' => 'valor',
            ],
            [
                'titulo' => 'Contactables',
                'detalle' => 'Con telefono y direccion completos.',
                'valor' => (string) $totalContactables,
                'tipo' => 'estado-ok',
            ],
            [
                'titulo' => 'Con productos asociados',
                'detalle' => 'Proveedores que ya tienen productos registrados.',
                'valor' => (string) $totalConProductos,
                'tipo' => 'estado-alerta',
            ],
        ],
        'directorioProveedores' => $directorioProveedores,
    ];
}
