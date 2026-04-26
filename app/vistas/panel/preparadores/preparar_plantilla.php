<?php

declare(strict_types=1);

/**
 * Construye los datos base que necesita la plantilla principal del panel.
 *
 * Aqui dejamos la preparacion de variables fuera del HTML para que la vista
 * quede mas limpia y centrada en la estructura.
 */
function prepararDatosPlantillaPanel(
    array $autenticacion,
    array $permisos,
    array $datosVista = []
): array {
    $itemsMenu = [
        'entradas' => 'Entradas',
        'productos' => 'Productos',
        'proveedores' => 'Proveedores',
        'salidas' => 'Salidas',
    ];

    return [
        'tituloPagina' => $datosVista['tituloPagina'] ?? 'Panel',
        'tituloSeccion' => $datosVista['tituloSeccion'] ?? 'Panel del sistema',
        'descripcionSeccion' => $datosVista['descripcionSeccion'] ?? 'Gestiona la informacion del inventario desde una interfaz clara y ordenada.',
        'moduloActivo' => $datosVista['moduloActivo'] ?? '',
        'resaltarConfiguracion' => $datosVista['resaltarConfiguracion'] ?? false,
        'contenidoModulo' => $datosVista['contenidoModulo'] ?? '',
        'scriptsModulo' => $datosVista['scriptsModulo'] ?? '',
        'itemsMenu' => $itemsMenu,
        'puedeVerConfiguracion' => (($permisos['configuracion'] ?? 0) === 1) || (($permisos['gestionar_roles'] ?? 0) === 1),
        'nombreUsuario' => $autenticacion['nombre'] ?? '',
        'usuarioAcceso' => $autenticacion['usuario'] ?? '',
        'nombreRol' => $autenticacion['rol'] ?? '',
    ];
}
