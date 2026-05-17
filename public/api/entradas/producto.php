<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../app/filtros/autenticado.php';
require_once __DIR__ . '/../../../app/ayudantes/sesion.php';
require_once __DIR__ . '/../../../app/ayudantes/seguridad_http.php';
require_once __DIR__ . '/../../../app/modelos/RepositorioEntrada.php';

enviarEncabezadosSeguridad();
header('Content-Type: application/json; charset=utf-8');

if (!tienePermisoSesion('registrar_movimientos') && !tienePermisoSesion('consultar_movimientos')) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No tienes permisos para consultar productos de entradas.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$codigo = trim((string) ($_GET['codigo'] ?? ''));

if ($codigo === '') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Debes enviar un codigo valido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $repositorioEntrada = new RepositorioEntrada();
    $producto = $repositorioEntrada->obtenerProductoParaFormulario($codigo);

    if ($producto === null) {
        if ($repositorioEntrada->esProductoDesactivadoPorCodigo($codigo)) {
            http_response_code(409);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'El producto esta desactivado y no se puede usar en entradas.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Producto no encontrado.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'producto' => [
            'codigo' => (string) ($producto['codigo'] ?? ''),
            'descripcion' => (string) ($producto['descripcion'] ?? ''),
            'precio' => (float) ($producto['precio'] ?? 0),
            'id_proveedor' => (int) ($producto['id_proveedor'] ?? 0),
            'proveedor' => (string) ($producto['proveedor'] ?? ''),
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error interno al consultar producto.',
    ], JSON_UNESCAPED_UNICODE);
}
