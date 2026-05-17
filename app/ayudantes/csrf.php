<?php

declare(strict_types=1);

require_once __DIR__ . '/sesion.php';

const CSRF_TOKEN_KEY = '_csrf_token';

function csrfToken(): string
{
    iniciarSesionSegura();

    $token = (string) ($_SESSION[CSRF_TOKEN_KEY] ?? '');
    if ($token === '') {
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_KEY] = $token;
    }

    return $token;
}

function csrfCampoOculto(): string
{
    $token = htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf" value="' . $token . '">';
}

function csrfEsValidoEnPost(array $datos): bool
{
    iniciarSesionSegura();
    $tokenSesion = (string) ($_SESSION[CSRF_TOKEN_KEY] ?? '');
    $tokenPost = (string) ($datos['_csrf'] ?? '');

    if ($tokenSesion === '' || $tokenPost === '') {
        return false;
    }

    return hash_equals($tokenSesion, $tokenPost);
}

