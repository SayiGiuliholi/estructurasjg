<?php

declare(strict_types=1);

final class ServicioStockBodega
{
    /**
     * Obtiene (con lock) el registro de stock de una bodega para un producto.
     */
    public function obtenerParaActualizar(PDO $conexion, int $idBodega, int $idProducto): ?array
    {
        $sql = <<<SQL
            SELECT id_stock_bodega, stock_actual
            FROM stock_bodega
            WHERE id_bodega = :id_bodega AND id_producto = :id_producto
            LIMIT 1
            FOR UPDATE
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_bodega' => $idBodega,
            'id_producto' => $idProducto,
        ]);

        $fila = $sentencia->fetch();
        return $fila ?: null;
    }

    /**
     * Suma stock en bodega; si no existe registro lo crea.
     */
    public function incrementarStock(PDO $conexion, int $idBodega, int $idProducto, int $cantidad): void
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

    /**
     * Descuenta stock por id de fila en stock_bodega.
     */
    public function descontarPorIdStock(PDO $conexion, int $idStockBodega, int $cantidad): void
    {
        $sql = <<<SQL
            UPDATE stock_bodega
            SET stock_actual = stock_actual - :cantidad
            WHERE id_stock_bodega = :id_stock_bodega
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'cantidad' => $cantidad,
            'id_stock_bodega' => $idStockBodega,
        ]);
    }

    /**
     * Incrementa stock en bodega destino usando id de producto.
     */
    public function incrementarPorProducto(PDO $conexion, int $idBodega, int $idProducto, int $cantidad): void
    {
        $stockDestino = $this->obtenerParaActualizar($conexion, $idBodega, $idProducto);
        if ($stockDestino === null) {
            $sqlInsert = <<<SQL
                INSERT INTO stock_bodega (id_bodega, id_producto, stock_actual)
                VALUES (:id_bodega, :id_producto, :stock_actual)
            SQL;
            $sentenciaInsert = $conexion->prepare($sqlInsert);
            $sentenciaInsert->execute([
                'id_bodega' => $idBodega,
                'id_producto' => $idProducto,
                'stock_actual' => $cantidad,
            ]);
            return;
        }

        $sqlUpdate = <<<SQL
            UPDATE stock_bodega
            SET stock_actual = stock_actual + :cantidad
            WHERE id_stock_bodega = :id_stock_bodega
        SQL;
        $sentenciaUpdate = $conexion->prepare($sqlUpdate);
        $sentenciaUpdate->execute([
            'cantidad' => $cantidad,
            'id_stock_bodega' => (int) $stockDestino['id_stock_bodega'],
        ]);
    }
}
