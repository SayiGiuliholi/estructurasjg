<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/filtros/autenticado.php';
require_once __DIR__ . '/../../../app/ayudantes/sesion.php';
require_once __DIR__ . '/../../../app/ayudantes/seguridad_http.php';
require_once __DIR__ . '/../../../app/modelos/RepositorioSalida.php';

enviarEncabezadosSeguridad();
header('Content-Type: application/json; charset=utf-8');

if (!tienePermisoSesion('registrar_movimientos') && !tienePermisoSesion('consultar_movimientos')) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No tienes permisos para consultar productos de salidas.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$codigo = trim((string) ($_GET['codigo'] ?? ''));
$idBodega = (int) ($_GET['id_bodega'] ?? 0);

if ($codigo === '' || $idBodega <= 0) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Debes enviar codigo e id_bodega validos.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $repositorioSalida = new RepositorioSalida();
    $producto = $repositorioSalida->obtenerProductoParaFormulario($codigo, $idBodega);

    if ($producto === null) {
        if ($repositorioSalida->esProductoDesactivadoPorCodigo($codigo)) {
            http_response_code(409);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'El producto esta desactivado y no se puede usar en salidas.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Producto no encontrado para esa bodega.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'producto' => [
            'codigo' => (string) ($producto['codigo'] ?? ''),
            'descripcion' => (string) ($producto['descripcion'] ?? ''),
            'precio' => (float) ($producto['precio'] ?? 0),
            'stock_bodega' => (int) ($producto['stock_bodega'] ?? 0),
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error interno al consultar producto.',
    ], JSON_UNESCAPED_UNICODE);
}
