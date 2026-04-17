<?php

declare(strict_types=1);

function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function usuarioAutenticado(): bool
{
    iniciarSesionSegura();

    return isset($_SESSION['autenticacion']['id_usuario']);
}