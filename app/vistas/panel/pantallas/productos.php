<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_productos.php';
require_once __DIR__ . '/../../../configuracion/rutas.php';
require_once __DIR__ . '/../../../modelos/RepositorioProducto.php';
require_once __DIR__ . '/../../../modelos/RepositorioProveedor.php';

$repositorioProducto = new RepositorioProducto();
$repositorioProveedor = new RepositorioProveedor();
$puedeGestionProductos = ((int) ($permisos['registrar_productos'] ?? 0) === 1)
    || ((int) ($permisos['modificar_productos'] ?? 0) === 1);

$mensajeExito = '';
$mensajeError = '';
$idProductoEdicion = null;
$fichaProducto = [
    'id_producto' => null,
    'codigo' => '',
    'descripcion' => '',
    'id_proveedor' => '',
    'stock' => '',
    'precio' => '',
];

try {
    $repositorioProducto->prepararSoporteEstado();
} catch (Throwable $error) {
    $mensajeError = 'No se pudo preparar el estado de productos. Revisa permisos de base de datos.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!$puedeGestionProductos) {
        $mensajeError = 'Tu rol solo permite consulta. No puedes modificar productos.';
    } else {
    $accion = (string) ($_POST['accion'] ?? '');
    $idProducto = (int) ($_POST['id_producto'] ?? 0);

    if ($accion === 'cargar' && $idProducto > 0) {
        $producto = $repositorioProducto->buscarPorId($idProducto);
        if ($producto !== null) {
            $idProductoEdicion = $producto->idProducto;
            $fichaProducto = [
                'id_producto' => $producto->idProducto,
                'codigo' => $producto->codigo,
                'descripcion' => $producto->descripcion,
                'id_proveedor' => (string) $producto->idProveedor,
                'stock' => (string) $producto->stock,
                'precio' => number_format($producto->precio, 0, ',', '.'),
            ];
        } else {
            $mensajeError = 'No se encontro el producto para editar.';
        }
    }

    if ($accion === 'actualizar' && $idProducto > 0) {
        $codigo = trim((string) ($_POST['codigo'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $idProveedor = (int) ($_POST['id_proveedor'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $precioLimpio = preg_replace('/[^\d]/', '', (string) ($_POST['precio'] ?? ''));
        $precio = (float) $precioLimpio;

        if ($codigo === '' || $descripcion === '' || $idProveedor <= 0 || $stock < 0 || $precio < 0) {
            $mensajeError = 'Completa todos los campos de edicion correctamente.';
            $idProductoEdicion = $idProducto;
            $fichaProducto = [
                'id_producto' => $idProducto,
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'id_proveedor' => (string) $idProveedor,
                'stock' => (string) max(0, $stock),
                'precio' => (string) ($_POST['precio'] ?? ''),
            ];
        } else {
            try {
                $repositorioProducto->actualizar($idProducto, [
                    'codigo' => $codigo,
                    'descripcion' => $descripcion,
                    'id_proveedor' => $idProveedor,
                    'stock' => $stock,
                    'precio' => $precio,
                ]);
                $mensajeExito = 'Producto actualizado correctamente.';
            } catch (Throwable $error) {
                $mensajeError = 'No se pudo actualizar el producto. Verifica que el codigo no este repetido.';
                $idProductoEdicion = $idProducto;
            }
        }
    }

    if ($accion === 'desactivar' && $idProducto > 0) {
        try {
            $repositorioProducto->cambiarEstado($idProducto, false);
            $mensajeExito = 'Producto desactivado correctamente.';
        } catch (Throwable $error) {
            $mensajeError = 'No se pudo desactivar el producto.';
        }
    }

    if ($accion === 'activar' && $idProducto > 0) {
        try {
            $repositorioProducto->cambiarEstado($idProducto, true);
            $mensajeExito = 'Producto activado correctamente.';
        } catch (Throwable $error) {
            $mensajeError = 'No se pudo activar el producto.';
        }
    }
    }
}

$opcionesPorPagina = [10, 20, 50];
$porPagina = (int) ($_GET['por_pagina'] ?? 20);
if (!in_array($porPagina, $opcionesPorPagina, true)) {
    $porPagina = 20;
}
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

$totalRegistrosCatalogo = $repositorioProducto->contarTotal();
$totalPaginas = max(1, (int) ceil($totalRegistrosCatalogo / $porPagina));
if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}
$offset = ($paginaActual - 1) * $porPagina;

$catalogoProductos = $repositorioProducto->obtenerPaginado($porPagina, $offset);

$datosModulo = prepararDatosModuloProductos([
    'catalogoProductos' => $catalogoProductos,
    'totalProductos' => $repositorioProducto->contarTotal(),
    'stockTotal' => $repositorioProducto->sumarStockTotal(),
    'stockBajo' => $repositorioProducto->contarStockBajo(),
    'valorEstimado' => $repositorioProducto->calcularValorEstimado(),
    'paginacion' => [
        'paginaActual' => $paginaActual,
        'totalPaginas' => $totalPaginas,
        'totalRegistros' => $totalRegistrosCatalogo,
        'porPagina' => $porPagina,
        'opcionesPorPagina' => $opcionesPorPagina,
    ],
]);

$tituloPagina = $datosModulo['tituloPagina'];
$tituloSeccion = $datosModulo['tituloSeccion'];
$descripcionSeccion = $datosModulo['descripcionSeccion'];
$moduloActivo = $datosModulo['moduloActivo'];
$resaltarConfiguracion = $datosModulo['resaltarConfiguracion'];

$resumenIndicadores = $datosModulo['resumenIndicadores'];
$catalogoProductos = $datosModulo['catalogoProductos'];
$catalogoPrincipal = $datosModulo['catalogoPrincipal'];
$catalogoSecundaria = $datosModulo['catalogoSecundaria'];
$controlVisual = $datosModulo['controlVisual'];
$paginacion = $datosModulo['paginacion'];
$rutaArchivoScriptProductos = __DIR__ . '/../../../../public/js/panel/productos.js';
$versionScriptProductos = is_file($rutaArchivoScriptProductos) ? (string) filemtime($rutaArchivoScriptProductos) : '1';
$urlScriptProductos = construirUrlPublica('js/panel/productos.js') . '?v=' . rawurlencode($versionScriptProductos);
$directorioProveedores = $repositorioProveedor->obtenerTodos();

ob_start();
require __DIR__ . '/../modulos/vista_productos.php';
$contenidoModulo = ob_get_clean();

ob_start();
?>
<script src="<?= htmlspecialchars($urlScriptProductos, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php
$scriptsModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
