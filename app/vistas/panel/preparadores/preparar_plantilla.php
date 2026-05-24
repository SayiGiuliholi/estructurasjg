<?php

declare(strict_types=1);
require_once __DIR__ . '/../../../ayudantes/sesion.php';

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
    $permisosActivos = static fn(string $clave): bool => ((int) ($permisos[$clave] ?? 0)) === 1;
    $esSuperadmin = esSuperadminSesion($autenticacion);
    $puedeVerConfiguracion = $esSuperadmin || $permisosActivos('gestionar_roles');

    $itemsMenuBase = [
        'entradas' => 'Entradas',
        'productos' => 'Productos',
        'proveedores' => 'Proveedores',
        'salidas' => 'Salidas',
    ];

    $puedeAcceder = static function (string $modulo) use ($permisosActivos, $esSuperadmin): bool {
        if ($esSuperadmin) {
            return true;
        }

        return match ($modulo) {
            'entradas', 'salidas' => $permisosActivos('registrar_movimientos') || $permisosActivos('consultar_movimientos'),
            'productos' => $permisosActivos('registrar_movimientos') || $permisosActivos('modificar_productos') || $permisosActivos('consultar_movimientos'),
            'proveedores' => $permisosActivos('registrar_movimientos') || $permisosActivos('modificar_productos') || $permisosActivos('consultar_movimientos') || $permisosActivos('gestionar_roles'),
            default => false,
        };
    };

    $itemsMenu = [];
    foreach ($itemsMenuBase as $clave => $etiqueta) {
        if ($puedeAcceder($clave)) {
            $itemsMenu[$clave] = $etiqueta;
        }
    }

    return [
        'tituloPagina' => $datosVista['tituloPagina'] ?? 'Panel',
        'tituloSeccion' => $datosVista['tituloSeccion'] ?? 'Panel del sistema',
        'descripcionSeccion' => $datosVista['descripcionSeccion'] ?? 'Gestiona la informacion del inventario desde una interfaz clara y ordenada.',
        'moduloActivo' => $datosVista['moduloActivo'] ?? '',
        'resaltarConfiguracion' => $datosVista['resaltarConfiguracion'] ?? false,
        'contenidoModulo' => $datosVista['contenidoModulo'] ?? '',
        'scriptsModulo' => $datosVista['scriptsModulo'] ?? '',
        'itemsMenu' => $itemsMenu,
        'puedeVerConfiguracion' => $puedeVerConfiguracion,
        'nombreUsuario' => $autenticacion['nombre'] ?? '',
        'usuarioAcceso' => $autenticacion['usuario'] ?? '',
        'nombreRol' => $autenticacion['rol'] ?? '',
        'esSuperadmin' => $esSuperadmin,
    ];
}
