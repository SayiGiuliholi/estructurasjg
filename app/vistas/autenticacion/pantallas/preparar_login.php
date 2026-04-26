<?php

declare(strict_types=1);

/**
 * Organiza los datos base de la vista de login para mantener la plantilla
 * enfocada en renderizar y no en preparar valores.
 */
function prepararDatosVistaLogin(array $datosVista = []): array
{
    return [
        'mensajeError' => $datosVista['mensajeError'] ?? null,
        'ultimoUsuario' => $datosVista['ultimoUsuario'] ?? '',
        'tituloPagina' => $datosVista['tituloPagina'] ?? 'Login | Estructuras JG',
        'accionFormulario' => $datosVista['accionFormulario'] ?? '/Estructurasjg/public/index.php',
    ];
}
