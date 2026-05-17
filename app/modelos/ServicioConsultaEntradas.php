<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class ServicioConsultaEntradas
{
    public function obtenerResumenIndicadores(bool $filtrarProductosActivos): array
    {
        $conexion = obtenerConexion();

        $resumen = [
            'entradas_hoy' => 0,
            'unidades_hoy' => 0,
            'valor_hoy' => 0.0,
            'proveedores_activos' => 0,
        ];

        $sqlMovimientosHoy = <<<SQL
            SELECT
                COUNT(*) AS entradas_hoy,
                COALESCE(SUM(cantidad), 0) AS unidades_hoy,
                COALESCE(SUM(total), 0) AS valor_hoy
            FROM compras
            WHERE DATE(fecha) = CURRENT_DATE()
        SQL;

        $filaMovimientos = $conexion->query($sqlMovimientosHoy)->fetch();
        if ($filaMovimientos) {
            $resumen['entradas_hoy'] = (int) $filaMovimientos['entradas_hoy'];
            $resumen['unidades_hoy'] = (int) $filaMovimientos['unidades_hoy'];
            $resumen['valor_hoy'] = (float) $filaMovimientos['valor_hoy'];
        }

        $filtroEstadoProductos = $filtrarProductosActivos ? ' AND p.estado = 1' : '';
        $sqlProveedores = <<<SQL
            SELECT COUNT(DISTINCT pr.id_proveedor) AS total
            FROM proveedores pr
            INNER JOIN productos p ON p.id_proveedor = pr.id_proveedor
            WHERE 1 = 1
            {$filtroEstadoProductos}
        SQL;
        $filaProveedores = $conexion->query($sqlProveedores)->fetch();
        if ($filaProveedores) {
            $resumen['proveedores_activos'] = (int) $filaProveedores['total'];
        }

        return $resumen;
    }

    public function obtenerUltimoMovimiento(): ?array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                c.codigo AS codigo_compra,
                dc.cantidad,
                dc.costo_unitario AS precio,
                (dc.cantidad * dc.costo_unitario) AS total_linea,
                c.fecha,
                p.codigo AS codigo_producto,
                p.descripcion,
                p.stock,
                COALESCE(sb.stock_actual, p.stock) AS stock_bodega,
                pr.nombre AS proveedor,
                b.nombre AS bodega
            FROM compras c
            INNER JOIN detalle_compras dc ON dc.id_compra = c.id_compra
            INNER JOIN productos p ON p.id_producto = dc.id_producto
            INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
            INNER JOIN bodegas b ON b.id_bodega = c.id_bodega
            LEFT JOIN stock_bodega sb ON sb.id_producto = p.id_producto AND sb.id_bodega = c.id_bodega
            ORDER BY c.id_compra DESC
            LIMIT 1
        SQL;

        $fila = $conexion->query($sql)->fetch();
        return $fila ?: null;
    }

    public function obtenerHistorial(int $limite = 20, int $offset = 0): array
    {
        $conexion = obtenerConexion();

        $sql = <<<SQL
            SELECT
                c.id_compra,
                c.codigo AS codigo_compra,
                c.fecha,
                dc.cantidad,
                dc.costo_unitario AS precio,
                (dc.cantidad * dc.costo_unitario) AS total_linea,
                p.codigo AS codigo_producto,
                p.descripcion,
                p.stock,
                COALESCE(sb.stock_actual, p.stock) AS stock_bodega,
                pr.nombre AS proveedor,
                b.nombre AS bodega
            FROM compras c
            INNER JOIN detalle_compras dc ON dc.id_compra = c.id_compra
            INNER JOIN productos p ON p.id_producto = dc.id_producto
            INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
            INNER JOIN bodegas b ON b.id_bodega = c.id_bodega
            LEFT JOIN stock_bodega sb ON sb.id_producto = p.id_producto AND sb.id_bodega = c.id_bodega
            ORDER BY c.id_compra DESC
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
            FROM compras c
            INNER JOIN detalle_compras dc ON dc.id_compra = c.id_compra
        SQL;

        $fila = $conexion->query($sql)->fetch();
        return (int) ($fila['total'] ?? 0);
    }
}

