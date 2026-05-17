<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class ServicioConsultaSalidas
{
    public function obtenerResumenIndicadores(): array
    {
        $conexion = obtenerConexion();

        $resumen = [
            'ventas_hoy' => 0,
            'unidades_hoy' => 0,
            'ingresos_hoy' => 0.0,
            'alertas_stock' => 0,
        ];

        $sqlVentasHoy = <<<SQL
            SELECT
                COUNT(DISTINCT v.id_venta) AS ventas_hoy,
                COALESCE(SUM(dv.cantidad), 0) AS unidades_hoy,
                COALESCE(SUM(dv.cantidad * dv.precio_unitario), 0) AS ingresos_hoy
            FROM ventas v
            INNER JOIN detalle_ventas dv ON dv.id_venta = v.id_venta
            WHERE DATE(v.fecha) = CURRENT_DATE()
        SQL;

        $filaVentas = $conexion->query($sqlVentasHoy)->fetch();
        if ($filaVentas) {
            $resumen['ventas_hoy'] = (int) $filaVentas['ventas_hoy'];
            $resumen['unidades_hoy'] = (int) $filaVentas['unidades_hoy'];
            $resumen['ingresos_hoy'] = (float) $filaVentas['ingresos_hoy'];
        }

        $sqlAlertas = 'SELECT COUNT(*) AS total FROM productos WHERE stock <= 10';
        $filaAlertas = $conexion->query($sqlAlertas)->fetch();
        if ($filaAlertas) {
            $resumen['alertas_stock'] = (int) $filaAlertas['total'];
        }

        return $resumen;
    }

    public function obtenerHistorial(int $limite = 20, int $offset = 0): array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                v.id_venta,
                v.codigo AS factura,
                v.motivo_salida,
                v.descripcion AS descripcion_movimiento,
                v.fecha,
                dv.cantidad,
                p.codigo AS codigo_producto,
                p.descripcion AS producto,
                p.stock AS stock_actual,
                dv.precio_unitario,
                (dv.cantidad * dv.precio_unitario) AS total,
                b.nombre AS bodega
            FROM ventas v
            INNER JOIN detalle_ventas dv ON dv.id_venta = v.id_venta
            INNER JOIN productos p ON p.id_producto = dv.id_producto
            INNER JOIN bodegas b ON b.id_bodega = v.id_bodega
            ORDER BY v.id_venta DESC
            LIMIT :limite OFFSET :offset
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->bindValue('limite', $limite, PDO::PARAM_INT);
        $sentencia->bindValue('offset', $offset, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    public function contarHistorial(): int
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT COUNT(*) AS total
            FROM ventas v
            INNER JOIN detalle_ventas dv ON dv.id_venta = v.id_venta
        SQL;

        $fila = $conexion->query($sql)->fetch();
        return (int) ($fila['total'] ?? 0);
    }
}

