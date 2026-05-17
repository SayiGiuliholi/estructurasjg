<?php

declare(strict_types=1);

function configurarRutaSesiones(): void
{
    $rutaSesiones = __DIR__ . '/../../storage/sesiones';

    if (!is_dir($rutaSesiones)) {
        mkdir($rutaSesiones, 0700, true);
    }

    if (is_dir($rutaSesiones) && is_writable($rutaSesiones)) {
        session_save_path($rutaSesiones);
    }
}

function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        configurarRutaSesiones();
        $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $esHttps,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
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
    return (int) ($datos['es_superadmin'] ?? 0) === 1;
}

function calcularSuperadminPorRol(?string $nombreRol): bool
{
    $rolSuperadmin = (string) (getenv('SUPERADMIN_ROLE_NAME') ?: 'superadmin');
    return strtolower(trim((string) $nombreRol)) === strtolower(trim($rolSuperadmin));
}

function permisosSesionActual(): array
{
    iniciarSesionSegura();
    $permisos = $_SESSION['autenticacion']['permisos'] ?? [];
    return is_array($permisos) ? $permisos : [];
}

function tienePermisoSesion(string $clave): bool
{
    $permisos = permisosSesionActual();
    return ((int) ($permisos[$clave] ?? 0)) === 1;
}
