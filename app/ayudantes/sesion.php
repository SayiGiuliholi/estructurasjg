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

/**
 * Determina si la sesion actual corresponde al superadmin protegido.
 * Se conserva compatibilidad con sesiones antiguas y nuevos inicios.
 */
function esSuperadminSesion(?array $autenticacion = null): bool
{
    iniciarSesionSegura();
    $datos = $autenticacion ?? ($_SESSION['autenticacion'] ?? []);

    if ((int) ($datos['es_superadmin'] ?? 0) === 1) {
        return true;
    }

    return (int) ($datos['id_usuario'] ?? 0) === 1
        || strtolower((string) ($datos['usuario'] ?? '')) === 'admin';
}
