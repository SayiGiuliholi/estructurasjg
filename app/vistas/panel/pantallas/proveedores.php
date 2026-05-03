<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_proveedores.php';
require_once __DIR__ . '/../../../modelos/RepositorioProveedor.php';

$repositorioProveedor = new RepositorioProveedor();
$puedeGestionProveedores = ((int) ($permisos['registrar_productos'] ?? 0) === 1)
    || ((int) ($permisos['modificar_productos'] ?? 0) === 1)
    || ((int) ($permisos['configuracion'] ?? 0) === 1);

$mensajeExito = '';
$mensajeError = '';
$idProveedorEdicion = null;

$fichaProveedor = [
    'id_proveedor' => '',
    'ruc' => '',
    'nombre' => '',
    'telefono' => '',
    'direccion' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$puedeGestionProveedores) {
        $mensajeError = 'Tu rol solo permite consulta. No puedes gestionar proveedores.';
    } else {
    $accion = trim((string) ($_POST['accion'] ?? ''));
    $idProveedorPost = (int) ($_POST['id_proveedor'] ?? 0);

    $datosFormulario = [
        'ruc' => trim((string) ($_POST['ruc'] ?? '')),
        'nombre' => trim((string) ($_POST['nombre'] ?? '')),
        'telefono' => trim((string) ($_POST['telefono'] ?? '')),
        'direccion' => trim((string) ($_POST['direccion'] ?? '')),
    ];

    $rucValido = $datosFormulario['ruc'] !== '' && ctype_digit($datosFormulario['ruc']);
    $telefonoValido = $datosFormulario['telefono'] !== '' && ctype_digit($datosFormulario['telefono']);

    try {
        if ($accion === 'guardar') {
            if (
                $datosFormulario['ruc'] === ''
                || $datosFormulario['nombre'] === ''
                || $datosFormulario['telefono'] === ''
                || $datosFormulario['direccion'] === ''
            ) {
                $mensajeError = 'Debes completar todos los campos para guardar el proveedor.';
                $fichaProveedor = array_merge($fichaProveedor, $datosFormulario);
            } elseif (!$rucValido || !$telefonoValido) {
                $mensajeError = 'NIT y Telefono deben contener solo numeros enteros.';
                $fichaProveedor = array_merge($fichaProveedor, $datosFormulario);
            } else {
                $repositorioProveedor->crear($datosFormulario);
                $mensajeExito = 'Proveedor registrado correctamente.';
            }
        } elseif ($accion === 'actualizar') {
            if ($idProveedorPost <= 0) {
                $mensajeError = 'No se encontro el proveedor a actualizar.';
            } elseif (
                $datosFormulario['ruc'] === ''
                || $datosFormulario['nombre'] === ''
                || $datosFormulario['telefono'] === ''
                || $datosFormulario['direccion'] === ''
            ) {
                $mensajeError = 'Debes completar todos los campos para actualizar.';
                $idProveedorEdicion = $idProveedorPost;
                $fichaProveedor = array_merge($fichaProveedor, $datosFormulario, [
                    'id_proveedor' => (string) $idProveedorPost,
                ]);
            } elseif (!$rucValido || !$telefonoValido) {
                $mensajeError = 'NIT y Telefono deben contener solo numeros enteros.';
                $idProveedorEdicion = $idProveedorPost;
                $fichaProveedor = array_merge($fichaProveedor, $datosFormulario, [
                    'id_proveedor' => (string) $idProveedorPost,
                ]);
            } else {
                $repositorioProveedor->actualizar($idProveedorPost, $datosFormulario);
                $mensajeExito = 'Proveedor actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            if ($idProveedorPost <= 0) {
                $mensajeError = 'No se encontro el proveedor a eliminar.';
            } else {
                $repositorioProveedor->eliminar($idProveedorPost);
                $mensajeExito = 'Proveedor eliminado correctamente.';
            }
        } elseif ($accion === 'cargar') {
            if ($idProveedorPost > 0) {
                $proveedorSeleccionado = $repositorioProveedor->buscarPorId($idProveedorPost);

                if ($proveedorSeleccionado !== null) {
                    $idProveedorEdicion = $proveedorSeleccionado->idProveedor;
                    $fichaProveedor = [
                        'id_proveedor' => (string) $proveedorSeleccionado->idProveedor,
                        'ruc' => $proveedorSeleccionado->ruc,
                        'nombre' => $proveedorSeleccionado->nombre,
                        'telefono' => $proveedorSeleccionado->telefono,
                        'direccion' => $proveedorSeleccionado->direccion,
                    ];
                } else {
                    $mensajeError = 'No se encontro el proveedor seleccionado.';
                }
            } else {
                $mensajeError = 'Proveedor invalido para editar.';
            }
        }
    } catch (Throwable $error) {
        if ($accion === 'eliminar') {
            $mensajeError = 'No fue posible eliminar el proveedor. Verifica si tiene productos relacionados.';
        } else {
            $mensajeError = 'No fue posible completar la operacion. Revisa datos duplicados (RUC) e intenta de nuevo.';
        }
    }
    }
}

$proveedores = $repositorioProveedor->obtenerTodos();

$datosModulo = prepararDatosModuloProveedores([
    'fichaProveedor' => $fichaProveedor,
    'idProveedorEdicion' => $idProveedorEdicion,
    'mensajeExito' => $mensajeExito,
    'mensajeError' => $mensajeError,
    'proveedores' => $proveedores,
    'totalConProductos' => $repositorioProveedor->contarConProductos(),
]);

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$fichaProveedor = $datosModulo['fichaProveedor'];
$idProveedorEdicion = $datosModulo['idProveedorEdicion'];
$mensajeExito = $datosModulo['mensajeExito'];
$mensajeError = $datosModulo['mensajeError'];
$indicadores = $datosModulo['indicadores'];
$directorioProveedores = $datosModulo['directorioProveedores'];

ob_start();
require __DIR__ . '/../modulos/vista_proveedores.php';
$contenidoModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
