<?php

declare(strict_types=1);

require_once __DIR__ . '/preparar_login.php';
require_once __DIR__ . '/../../configuracion/rutas.php';

$datosVista = prepararDatosVistaLogin(
    [
        'mensajeError' => $mensajeError ?? null,
        'ultimoUsuario' => $ultimoUsuario ?? '',
        'tituloPagina' => 'Login | Estructuras JG',
        'accionFormulario' => construirUrlPublica('index.php'),
    ]
);

$mensajeError = $datosVista['mensajeError'];
$ultimoUsuario = $datosVista['ultimoUsuario'];
$tituloPagina = $datosVista['tituloPagina'];
$accionFormulario = $datosVista['accionFormulario'];
$urlLogoMarca = construirUrlPublica('imagenes/marca/logo-login-principal.png');
$rutaArchivoCssLogin = __DIR__ . '/../../../public/css/autenticacion/login.css';
$versionCssLogin = is_file($rutaArchivoCssLogin) ? (string) filemtime($rutaArchivoCssLogin) : '1';
$urlCssLogin = construirUrlPublica('css/autenticacion/login.css') . '?v=' . rawurlencode($versionCssLogin);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlCssLogin, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <main class="contenedor-login">
        <?php require __DIR__ . '/parciales/panel_marca.php'; ?>
        <?php require __DIR__ . '/parciales/formulario_login.php'; ?>
    </main>
</body>
</html>
