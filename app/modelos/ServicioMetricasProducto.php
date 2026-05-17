<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class ServicioMetricasProducto
{
    public function contarTotal(): int
    {
        $conexion = obtenerConexion();
        $fila = $conexion->query('SELECT COUNT(*) AS total FROM productos')->fetch();
        return (int) ($fila['total'] ?? 0);
    }

    public function sumarStockTotal(): int
    {
        $conexion = obtenerConexion();
        $fila = $conexion->query('SELECT COALESCE(SUM(stock), 0) AS total_stock FROM productos')->fetch();
        return (int) ($fila['total_stock'] ?? 0);
    }

    public function contarStockBajo(int $umbral = 10): int
    {
        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare('SELECT COUNT(*) AS total FROM productos WHERE stock <= :umbral');
        $sentencia->execute(['umbral' => $umbral]);
        $fila = $sentencia->fetch();
        return (int) ($fila['total'] ?? 0);
    }

    public function calcularValorEstimado(): float
    {
        $conexion = obtenerConexion();
        $fila = $conexion->query('SELECT COALESCE(SUM(stock * precio), 0) AS valor FROM productos')->fetch();
        return (float) ($fila['valor'] ?? 0);
    }
}

