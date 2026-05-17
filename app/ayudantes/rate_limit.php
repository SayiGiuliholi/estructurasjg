<?php

declare(strict_types=1);

function rutaRateLimitLogin(): string
{
    return __DIR__ . '/../../storage/login_attempts.json';
}

function cargarRateLimitLogin(): array
{
    $ruta = rutaRateLimitLogin();
    if (!is_file($ruta)) {
        return [];
    }

    $json = (string) file_get_contents($ruta);
    $datos = json_decode($json, true);
    return is_array($datos) ? $datos : [];
}

function guardarRateLimitLogin(array $datos): void
{
    $ruta = rutaRateLimitLogin();
    @file_put_contents($ruta, json_encode($datos, JSON_UNESCAPED_UNICODE));
}

function llaveRateLimit(string $usuario): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'ip-desconocida');
    return strtolower(trim($usuario)) . '|' . $ip;
}

function loginEstaBloqueado(string $usuario): bool
{
    $llave = llaveRateLimit($usuario);
    $datos = cargarRateLimitLogin();
    $ahora = time();
    $registro = $datos[$llave] ?? null;
    if (!is_array($registro)) {
        return false;
    }

    $hasta = (int) ($registro['bloqueado_hasta'] ?? 0);
    return $hasta > $ahora;
}

function registrarFalloLogin(string $usuario): void
{
    $llave = llaveRateLimit($usuario);
    $datos = cargarRateLimitLogin();
    $ahora = time();
    $registro = $datos[$llave] ?? ['intentos' => 0, 'bloqueado_hasta' => 0];
    $intentos = (int) ($registro['intentos'] ?? 0) + 1;

    $bloqueadoHasta = 0;
    if ($intentos >= 5) {
        $bloqueadoHasta = $ahora + 900;
        $intentos = 0;
    }

    $datos[$llave] = [
        'intentos' => $intentos,
        'bloqueado_hasta' => $bloqueadoHasta,
    ];
    guardarRateLimitLogin($datos);
}

function limpiarBloqueoLogin(string $usuario): void
{
    $llave = llaveRateLimit($usuario);
    $datos = cargarRateLimitLogin();
    if (isset($datos[$llave])) {
        unset($datos[$llave]);
        guardarRateLimitLogin($datos);
    }
}

