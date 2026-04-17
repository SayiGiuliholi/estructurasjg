<?php

declare(strict_types=1);

const RUTA_BASE_PROYECTO = '/Estructurasjg';
const RUTA_BASE_PUBLICA = RUTA_BASE_PROYECTO . '/public';

/**
 * Construye una URL publica interna del proyecto.
 */
function construirUrlPublica(string $ruta = ''): string
{
    $rutaNormalizada = trim($ruta);

    if ($rutaNormalizada === '') {
        return RUTA_BASE_PUBLICA;
    }

    return RUTA_BASE_PUBLICA . '/' . ltrim($rutaNormalizada, '/');
}

/**
 * Redirige a una ruta publica interna del proyecto.
 */
function redirigirA(string $ruta = ''): void
{
    header('Location: ' . construirUrlPublica($ruta));
    exit;
}
