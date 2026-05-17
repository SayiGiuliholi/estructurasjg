<?php

declare(strict_types=1);

function obtenerConexion(): PDO
{
    static $conexion = null;

    if ($conexion instanceof PDO) {
        return $conexion;
    }

    $servidor = (string) (getenv('DB_HOST') ?: '127.0.0.1');
    $baseDeDatos = (string) (getenv('DB_NAME') ?: 'estructurasjg');
    $usuario = (string) (getenv('DB_USER') ?: 'root');
    $contrasena = (string) (getenv('DB_PASS') ?: '');

    $dsn = "mysql:host={$servidor};dbname={$baseDeDatos};charset=utf8mb4";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];

    $conexion = new PDO($dsn, $usuario, $contrasena, $opciones);

    return $conexion;
}
