<?php

declare(strict_types=1);

function configurarRutaSesiones(): void
{
    $rutaSesiones = __DIR__ . '/../../storage/sesiones';

    if (!is_dir($rutaSesiones)) {
        mkdir($rutaSesiones, 0775, true);
    }

    if (is_dir($rutaSesiones) && is_writable($rutaSesiones)) {
        session_save_path($rutaSesiones);
    }
}

function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        configurarRutaSesiones();
        session_start();
    }
}

function usuarioAutenticado(): bool
{
    iniciarSesionSegura();

    return isset($_SESSION['autenticacion']['id_usuario']);
}
