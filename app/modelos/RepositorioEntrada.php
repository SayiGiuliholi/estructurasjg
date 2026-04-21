<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class RepositorioEntrada
{
    public function registrarEntrada(array $datos): void
    {
        $this->registrarEntradaFactura(
            [
                'codigo_factura' => '',
                'id_proveedor' => (int) ($datos['id_proveedor'] ?? 0),
                'id_bodega' => (int) ($datos['id_bodega'] ?? 0),
                'id_usuario' => (int) ($datos['id_usuario'] ?? 0),
            ],
            [[
                'codigo' => (string) ($datos['codigo'] ?? ''),
                'descripcion' => (string) ($datos['descripcion'] ?? ''),
                'cantidad' => (int) ($datos['cantidad'] ?? 0),
                'precio' => (float) ($datos['precio'] ?? 0),
            ]]
        );
    }

    public function registrarEntradaFactura(array $cabecera, array $detalles): void
    {
        $conexion = obtenerConexion();
        $conexion->beginTransaction();

        try {
            if (count($detalles) === 0) {
                throw new RuntimeException('La factura debe contener al menos un producto.');
            }

            $cantidadTotal = 0;
            $valorTotal = 0.0;

            foreach ($detalles as $detalle) {
                $cantidadLinea = (int) ($detalle['cantidad'] ?? 0);
                $precioLinea = (float) ($detalle['precio'] ?? 0);
                $cantidadTotal += $cantidadLinea;
                $valorTotal += ($cantidadLinea * $precioLinea);
            }

            if ($cantidadTotal <= 0) {
                throw new RuntimeException('La cantidad total de la factura debe ser mayor que cero.');
            }

            $precioPromedio = $valorTotal / $cantidadTotal;

            $idCompra = $this->crearCompra($conexion, [
                'codigo_factura' => (string) ($cabecera['codigo_factura'] ?? ''),
                'descripcion' => 'Factura de entrada con ' . count($detalles) . ' producto(s)',
                'id_proveedor' => (int) ($cabecera['id_proveedor'] ?? 0),
                'id_bodega' => (int) ($cabecera['id_bodega'] ?? 0),
                'id_usuario' => (int) ($cabecera['id_usuario'] ?? 0),
                'cantidad' => $cantidadTotal,
                'precio' => $precioPromedio,
                'total' => $valorTotal,
            ]);

            foreach ($detalles as $detalle) {
                $idProducto = $this->obtenerOCrearProducto($conexion, [
                    'codigo' => (string) ($detalle['codigo'] ?? ''),
                    'descripcion' => (string) ($detalle['descripcion'] ?? ''),
                    'id_proveedor' => (int) ($cabecera['id_proveedor'] ?? 0),
                    'precio' => (float) ($detalle['precio'] ?? 0),
                ]);

                $cantidad = (int) ($detalle['cantidad'] ?? 0);
                $precio = (float) ($detalle['precio'] ?? 0);

                $this->crearDetalleCompra($conexion, $idCompra, $idProducto, $cantidad, $precio);
                $this->incrementarStockProducto($conexion, $idProducto, $cantidad);
                $this->incrementarStockBodega(
                    $conexion,
                    (int) ($cabecera['id_bodega'] ?? 0),
                    $idProducto,
                    $cantidad
                );
            }

            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollBack();
            throw $error;
        }
    }

    public function obtenerResumenIndicadores(): array
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

        $sqlProveedores = 'SELECT COUNT(*) AS total FROM proveedores';
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
                c.cantidad,
                c.precio,
                c.total,
                c.fecha,
                p.codigo AS codigo_producto,
                p.descripcion,
                p.stock,
                pr.nombre AS proveedor,
                b.nombre AS bodega
            FROM compras c
            INNER JOIN detalle_compras dc ON dc.id_compra = c.id_compra
            INNER JOIN productos p ON p.id_producto = dc.id_producto
            INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
            INNER JOIN bodegas b ON b.id_bodega = c.id_bodega
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
                c.cantidad,
                c.precio,
                c.total,
                p.codigo AS codigo_producto,
                p.descripcion,
                p.stock,
                pr.nombre AS proveedor,
                b.nombre AS bodega
            FROM compras c
            INNER JOIN detalle_compras dc ON dc.id_compra = c.id_compra
            INNER JOIN productos p ON p.id_producto = dc.id_producto
            INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
            INNER JOIN bodegas b ON b.id_bodega = c.id_bodega
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

    private function obtenerOCrearProducto(PDO $conexion, array $datos): int
    {
        $sqlBuscar = 'SELECT id_producto FROM productos WHERE codigo = :codigo LIMIT 1';
        $sentenciaBuscar = $conexion->prepare($sqlBuscar);
        $sentenciaBuscar->execute(['codigo' => $datos['codigo']]);
        $fila = $sentenciaBuscar->fetch();

        if ($fila) {
            $idProducto = (int) $fila['id_producto'];

            $sqlActualizar = <<<SQL
                UPDATE productos
                SET
                    descripcion = :descripcion,
                    id_proveedor = :id_proveedor,
                    precio = :precio
                WHERE id_producto = :id_producto
            SQL;

            $sentenciaActualizar = $conexion->prepare($sqlActualizar);
            $sentenciaActualizar->execute([
                'id_producto' => $idProducto,
                'descripcion' => $datos['descripcion'],
                'id_proveedor' => $datos['id_proveedor'],
                'precio' => $datos['precio'],
            ]);

            return $idProducto;
        }

        $sqlCrear = <<<SQL
            INSERT INTO productos (codigo, descripcion, id_proveedor, stock, precio)
            VALUES (:codigo, :descripcion, :id_proveedor, 0, :precio)
        SQL;

        $sentenciaCrear = $conexion->prepare($sqlCrear);
        $sentenciaCrear->execute([
            'codigo' => $datos['codigo'],
            'descripcion' => $datos['descripcion'],
            'id_proveedor' => $datos['id_proveedor'],
            'precio' => $datos['precio'],
        ]);

        return (int) $conexion->lastInsertId();
    }

    private function crearCompra(PDO $conexion, array $datos): int
    {
        $codigoFactura = trim((string) ($datos['codigo_factura'] ?? ''));
        $codigoCompra = $codigoFactura !== '' ? $codigoFactura : 'CMP-' . date('YmdHis');
        $total = isset($datos['total'])
            ? (float) $datos['total']
            : ((float) $datos['cantidad'] * (float) $datos['precio']);

        $sql = <<<SQL
            INSERT INTO compras (
                codigo,
                descripcion,
                id_proveedor,
                id_bodega,
                id_usuario,
                cantidad,
                precio,
                total
            ) VALUES (
                :codigo,
                :descripcion,
                :id_proveedor,
                :id_bodega,
                :id_usuario,
                :cantidad,
                :precio,
                :total
            )
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'codigo' => $codigoCompra,
            'descripcion' => $datos['descripcion'],
            'id_proveedor' => $datos['id_proveedor'],
            'id_bodega' => $datos['id_bodega'],
            'id_usuario' => $datos['id_usuario'],
            'cantidad' => $datos['cantidad'],
            'precio' => $datos['precio'],
            'total' => $total,
        ]);

        return (int) $conexion->lastInsertId();
    }

    private function crearDetalleCompra(
        PDO $conexion,
        int $idCompra,
        int $idProducto,
        int $cantidad,
        float $costoUnitario
    ): void {
        $sql = <<<SQL
            INSERT INTO detalle_compras (id_compra, id_producto, cantidad, costo_unitario)
            VALUES (:id_compra, :id_producto, :cantidad, :costo_unitario)
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_compra' => $idCompra,
            'id_producto' => $idProducto,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
        ]);
    }

    private function incrementarStockProducto(PDO $conexion, int $idProducto, int $cantidad): void
    {
        $sql = 'UPDATE productos SET stock = stock + :cantidad WHERE id_producto = :id_producto';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'cantidad' => $cantidad,
            'id_producto' => $idProducto,
        ]);
    }

    private function incrementarStockBodega(PDO $conexion, int $idBodega, int $idProducto, int $cantidad): void
    {
        $sqlBuscar = <<<SQL
            SELECT id_stock_bodega
            FROM stock_bodega
            WHERE id_bodega = :id_bodega AND id_producto = :id_producto
            LIMIT 1
        SQL;

        $sentenciaBuscar = $conexion->prepare($sqlBuscar);
        $sentenciaBuscar->execute([
            'id_bodega' => $idBodega,
            'id_producto' => $idProducto,
        ]);
        $fila = $sentenciaBuscar->fetch();

        if ($fila) {
            $sqlActualizar = <<<SQL
                UPDATE stock_bodega
                SET stock_actual = stock_actual + :cantidad
                WHERE id_stock_bodega = :id_stock_bodega
            SQL;

            $sentenciaActualizar = $conexion->prepare($sqlActualizar);
            $sentenciaActualizar->execute([
                'cantidad' => $cantidad,
                'id_stock_bodega' => (int) $fila['id_stock_bodega'],
            ]);
            return;
        }

        $sqlCrear = <<<SQL
            INSERT INTO stock_bodega (id_bodega, id_producto, stock_actual, stock_minimo)
            VALUES (:id_bodega, :id_producto, :stock_actual, 0)
        SQL;

        $sentenciaCrear = $conexion->prepare($sqlCrear);
        $sentenciaCrear->execute([
            'id_bodega' => $idBodega,
            'id_producto' => $idProducto,
            'stock_actual' => $cantidad,
        ]);
    }
}
