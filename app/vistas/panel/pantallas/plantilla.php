<?php

declare(strict_types=1);

require_once __DIR__ . '/../preparadores/preparar_plantilla.php';
require_once __DIR__ . '/../../../configuracion/rutas.php';

$datosPlantilla = prepararDatosPlantillaPanel(
    $autenticacion ?? [],
    $permisos ?? [],
    [
        'tituloPagina' => $tituloPagina ?? null,
        'tituloSeccion' => $tituloSeccion ?? null,
        'descripcionSeccion' => $descripcionSeccion ?? null,
        'moduloActivo' => $moduloActivo ?? null,
        'resaltarConfiguracion' => $resaltarConfiguracion ?? null,
        'contenidoModulo' => $contenidoModulo ?? null,
        'scriptsModulo' => $scriptsModulo ?? null,
    ]
);

$tituloPagina = $datosPlantilla['tituloPagina'];
$tituloSeccion = $datosPlantilla['tituloSeccion'];
$descripcionSeccion = $datosPlantilla['descripcionSeccion'];
$moduloActivo = $datosPlantilla['moduloActivo'];
$resaltarConfiguracion = $datosPlantilla['resaltarConfiguracion'];
$contenidoModulo = $datosPlantilla['contenidoModulo'];
$scriptsModulo = $datosPlantilla['scriptsModulo'];
$itemsMenu = $datosPlantilla['itemsMenu'];
$puedeVerConfiguracion = $datosPlantilla['puedeVerConfiguracion'];
$nombreUsuario = $datosPlantilla['nombreUsuario'];
$usuarioAcceso = $datosPlantilla['usuarioAcceso'];
$nombreRol = $datosPlantilla['nombreRol'];
$urlHojaEstilosPanel = construirUrlPublica('css/panel/plantilla.css');
$rutaFisicaHojaEstilosPanel = __DIR__ . '/../../../../public/css/panel/plantilla.css';
$versionHojaEstilosPanel = is_file($rutaFisicaHojaEstilosPanel)
    ? (string) filemtime($rutaFisicaHojaEstilosPanel)
    : (string) time();
$urlPanel = construirUrlPublica('panel.php');
$urlConfiguracion = construirUrlPublica('panel.php?modulo=configuracion');
$urlSalir = construirUrlPublica('salir.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?> | Estructuras JG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($urlHojaEstilosPanel . '?v=' . $versionHojaEstilosPanel, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <div class="distribucion">
        <?php require __DIR__ . '/../layout_panel/sidebar_panel.php'; ?>

        <main class="contenido">
            <?php require __DIR__ . '/../layout_panel/topbar_panel.php'; ?>

            <section class="hero">
                <h2><?= htmlspecialchars($tituloSeccion, ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($descripcionSeccion, ENT_QUOTES, 'UTF-8') ?></p>
            </section>

            <section class="contenido-personalizado">
                <?= $contenidoModulo ?>
            </section>
        </main>
    </div>
</body>
<?= $scriptsModulo ?>
</html>
