<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class RepositorioSalida
{
    private ?bool $columnaEstadoDisponible = null;
    private ?bool $soporteTrasladoDisponible = null;
    private const PREFIJO_FACTURA = 'FAC-2026-';
    private const RANGO_INICIO = 5000;
    private const RANGO_FIN = 9999;

    public function registrarSalida(array $datos): void
    {
        $this->registrarSalidaFactura(
            [
                'codigo_factura' => '',
                'id_bodega' => (int) ($datos['id_bodega'] ?? 0),
                'id_bodega_destino' => (int) ($datos['id_bodega_destino'] ?? 0),
                'id_usuario' => (int) ($datos['id_usuario'] ?? 0),
                'motivo_salida' => (string) ($datos['motivo_salida'] ?? 'normal'),
            ],
            [[
                'codigo' => (string) ($datos['codigo'] ?? ''),
                'cantidad' => (int) ($datos['cantidad'] ?? 0),
                'precio_unitario' => (float) ($datos['precio_unitario'] ?? 0),
            ]]
        );
    }

    public function obtenerSiguienteCodigoFactura(): string
    {
        $conexion = obtenerConexion();
        $numero = $this->obtenerSiguienteNumeroFactura($conexion, 'ventas', self::RANGO_INICIO, self::RANGO_FIN);
        return self::PREFIJO_FACTURA . $numero;
    }

    public function registrarSalidaFactura(array $cabecera, array $detalles): void
    {
        $this->prepararSoporteTraslado();
        $conexion = obtenerConexion();
        $conexion->beginTransaction();

        try {
            if (count($detalles) === 0) {
                throw new RuntimeException('La factura debe contener al menos un producto.');
            }

            $idBodega = (int) ($cabecera['id_bodega'] ?? 0);
            $idBodegaDestino = (int) ($cabecera['id_bodega_destino'] ?? 0);
            $idUsuario = (int) ($cabecera['id_usuario'] ?? 0);
            $motivoSalida = (string) ($cabecera['motivo_salida'] ?? 'normal');

            if ($idBodega <= 0 || $idUsuario <= 0) {
                throw new RuntimeException('Cabecera invalida para registrar la salida.');
            }

            if (!in_array($motivoSalida, ['normal', 'devolucion', 'fallo', 'traslado'], true)) {
                throw new RuntimeException('Motivo de salida invalido.');
            }
            if ($motivoSalida === 'traslado') {
                if ($idBodegaDestino <= 0) {
                    throw new RuntimeException('Debes seleccionar bodega destino para el traslado.');
                }
                if ($idBodegaDestino === $idBodega) {
                    throw new RuntimeException('La bodega destino debe ser distinta a la bodega origen.');
                }
            }

            $cantidadTotal = 0;
            $detallesProcesados = [];

            foreach ($detalles as $detalle) {
                $codigoProducto = trim((string) ($detalle['codigo'] ?? ''));
                $cantidad = (int) ($detalle['cantidad'] ?? 0);

                if ($codigoProducto === '' || $cantidad <= 0) {
                    throw new RuntimeException('Cada linea debe tener codigo y cantidad valida.');
                }

                $producto = $this->buscarProductoPorCodigoParaActualizar($conexion, $codigoProducto);
                if ($producto === null) {
                    if ($this->esProductoDesactivadoPorCodigoInterno($conexion, $codigoProducto)) {
                        throw new RuntimeException('El codigo "' . $codigoProducto . '" esta desactivado.');
                    }
                    throw new RuntimeException('El codigo "' . $codigoProducto . '" no existe.');
                }

                $idProducto = (int) $producto['id_producto'];
                $stockGeneral = (int) $producto['stock'];
                $precioUnitario = (float) $producto['precio'];

                if ($stockGeneral < $cantidad) {
                    throw new RuntimeException('Stock general insuficiente para el codigo "' . $codigoProducto . '".');
                }

                $stockBodega = $this->obtenerStockBodegaParaActualizar($conexion, $idBodega, $idProducto);
                if ($stockBodega === null || (int) $stockBodega['stock_actual'] < $cantidad) {
                    throw new RuntimeException('Stock insuficiente en bodega para el codigo "' . $codigoProducto . '".');
                }

                $cantidadTotal += $cantidad;
                $detallesProcesados[] = [
                    'id_producto' => $idProducto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'id_stock_bodega' => (int) $stockBodega['id_stock_bodega'],
                ];
            }

            $idVenta = $this->crearVenta($conexion, [
                'codigo_factura' => (string) ($cabecera['codigo_factura'] ?? ''),
                'id_bodega' => $idBodega,
                'id_usuario' => $idUsuario,
                'descripcion' => $motivoSalida === 'traslado'
                    ? ('Traslado de bodega ' . $idBodega . ' a bodega ' . $idBodegaDestino)
                    : ('Factura con ' . count($detallesProcesados) . ' prod'),
                'motivo_salida' => $motivoSalida,
                'cantidad' => $cantidadTotal,
            ]);

            foreach ($detallesProcesados as $detalleProcesado) {
                $this->crearDetalleVenta(
                    $conexion,
                    $idVenta,
                    (int) $detalleProcesado['id_producto'],
                    (int) $detalleProcesado['cantidad'],
                    (float) $detalleProcesado['precio_unitario']
                );

                if ($motivoSalida === 'traslado') {
                    $this->descontarStockBodega(
                        $conexion,
                        (int) $detalleProcesado['id_stock_bodega'],
                        (int) $detalleProcesado['cantidad']
                    );
                    $this->incrementarStockBodegaPorProducto(
                        $conexion,
                        $idBodegaDestino,
                        (int) $detalleProcesado['id_producto'],
                        (int) $detalleProcesado['cantidad']
                    );
                } else {
                    $this->descontarStockProducto(
                        $conexion,
                        (int) $detalleProcesado['id_producto'],
                        (int) $detalleProcesado['cantidad']
                    );

                    $this->descontarStockBodega(
                        $conexion,
                        (int) $detalleProcesado['id_stock_bodega'],
                        (int) $detalleProcesado['cantidad']
                    );
                }
            }

            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollBack();
            throw $error;
        }
    }

    public function obtenerProductoParaFormulario(string $codigoProducto, int $idBodega): ?array
    {
        $codigo = trim($codigoProducto);
        if ($codigo === '' || $idBodega <= 0) {
            return null;
        }

        $conexion = obtenerConexion();
        $filtroEstado = $this->tieneColumnaEstado() ? ' AND p.estado = 1 ' : '';

        $sql = <<<SQL
            SELECT
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.precio,
                p.stock AS stock_general,
                COALESCE(sb.stock_actual, 0) AS stock_bodega
            FROM productos p
            LEFT JOIN stock_bodega sb
                ON sb.id_producto = p.id_producto
                AND sb.id_bodega = :id_bodega
            WHERE p.codigo = :codigo
            {$filtroEstado}
            LIMIT 1
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'codigo' => $codigo,
            'id_bodega' => $idBodega,
        ]);

        $fila = $sentencia->fetch();

        return $fila ?: null;
    }

    public function esProductoDesactivadoPorCodigo(string $codigoProducto): bool
    {
        $codigo = trim($codigoProducto);
        if ($codigo === '' || !$this->tieneColumnaEstado()) {
            return false;
        }

        $conexion = obtenerConexion();
        return $this->esProductoDesactivadoPorCodigoInterno($conexion, $codigo);
    }

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

    private function buscarProductoPorCodigoParaActualizar(PDO $conexion, string $codigo): ?array
    {
        $filtroEstado = $this->tieneColumnaEstado() ? ' AND estado = 1 ' : '';
        $sql = <<<SQL
            SELECT id_producto, descripcion, stock, precio
            FROM productos
            WHERE codigo = :codigo
            {$filtroEstado}
            LIMIT 1
            FOR UPDATE
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['codigo' => $codigo]);

        $fila = $sentencia->fetch();

        return $fila ?: null;
    }

    private function esProductoDesactivadoPorCodigoInterno(PDO $conexion, string $codigo): bool
    {
        if (!$this->tieneColumnaEstado()) {
            return false;
        }

        $sql = <<<SQL
            SELECT 1
            FROM productos
            WHERE codigo = :codigo
              AND estado = 0
            LIMIT 1
        SQL;
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['codigo' => $codigo]);

        return (bool) $sentencia->fetchColumn();
    }

    private function tieneColumnaEstado(): bool
    {
        if ($this->columnaEstadoDisponible !== null) {
            return $this->columnaEstadoDisponible;
        }

        $conexion = obtenerConexion();
        $sql = <<<SQL
            SELECT COUNT(*) AS total
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'productos'
              AND COLUMN_NAME = 'estado'
        SQL;
        $fila = $conexion->query($sql)->fetch();
        $this->columnaEstadoDisponible = ((int) ($fila['total'] ?? 0)) > 0;

        return $this->columnaEstadoDisponible;
    }

    private function obtenerStockBodegaParaActualizar(PDO $conexion, int $idBodega, int $idProducto): ?array
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

    private function crearVenta(PDO $conexion, array $datos): int
    {
        $lock = 'factura_salidas_2026';
        if (!$this->adquirirBloqueoFactura($conexion, $lock)) {
            throw new RuntimeException('No se pudo bloquear la numeracion de facturas de salidas.');
        }

        try {
            $numero = $this->obtenerSiguienteNumeroFactura($conexion, 'ventas', self::RANGO_INICIO, self::RANGO_FIN, true);
            $codigoVenta = self::PREFIJO_FACTURA . $numero;
        } finally {
            $this->liberarBloqueoFactura($conexion, $lock);
        }

        $sql = <<<SQL
            INSERT INTO ventas (
                codigo,
                id_bodega,
                id_usuario,
                descripcion,
                motivo_salida,
                cantidad
            ) VALUES (
                :codigo,
                :id_bodega,
                :id_usuario,
                :descripcion,
                :motivo_salida,
                :cantidad
            )
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'codigo' => $codigoVenta,
            'id_bodega' => $datos['id_bodega'],
            'id_usuario' => $datos['id_usuario'],
            'descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : 'Salida de inventario',
            'motivo_salida' => $datos['motivo_salida'],
            'cantidad' => $datos['cantidad'],
        ]);

        return (int) $conexion->lastInsertId();
    }

    private function obtenerSiguienteNumeroFactura(
        PDO $conexion,
        string $tabla,
        int $inicio,
        int $fin,
        bool $forUpdate = false
    ): int {
        $forUpdateSql = $forUpdate ? ' FOR UPDATE' : '';
        $sql = "SELECT codigo FROM {$tabla} WHERE codigo LIKE :prefijo{$forUpdateSql}";
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['prefijo' => self::PREFIJO_FACTURA . '%']);
        $filas = $sentencia->fetchAll();

        $usados = [];
        foreach ($filas as $fila) {
            $codigo = (string) ($fila['codigo'] ?? '');
            if (preg_match('/^FAC-2026-(\d{4})$/', $codigo, $coincidencia) === 1) {
                $numero = (int) $coincidencia[1];
                if ($numero >= $inicio && $numero <= $fin) {
                    $usados[$numero] = true;
                }
            }
        }

        for ($n = $inicio; $n <= $fin; $n++) {
            if (!isset($usados[$n])) {
                return $n;
            }
        }

        throw new RuntimeException('Se agotaron los consecutivos de facturas para salidas (5000-9999).');
    }

    private function adquirirBloqueoFactura(PDO $conexion, string $nombre): bool
    {
        $sentencia = $conexion->prepare('SELECT GET_LOCK(:nombre, 5) AS bloqueado');
        $sentencia->execute(['nombre' => $nombre]);
        $fila = $sentencia->fetch();
        return ((int) ($fila['bloqueado'] ?? 0)) === 1;
    }

    private function liberarBloqueoFactura(PDO $conexion, string $nombre): void
    {
        $sentencia = $conexion->prepare('SELECT RELEASE_LOCK(:nombre) AS liberado');
        $sentencia->execute(['nombre' => $nombre]);
    }

    private function crearDetalleVenta(
        PDO $conexion,
        int $idVenta,
        int $idProducto,
        int $cantidad,
        float $precioUnitario
    ): void {
        $sql = <<<SQL
            INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario)
            VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario)
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_venta' => $idVenta,
            'id_producto' => $idProducto,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
        ]);
    }

    private function descontarStockProducto(PDO $conexion, int $idProducto, int $cantidad): void
    {
        $sql = 'UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'cantidad' => $cantidad,
            'id_producto' => $idProducto,
        ]);
    }

    private function descontarStockBodega(PDO $conexion, int $idStockBodega, int $cantidad): void
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

    private function incrementarStockBodegaPorProducto(PDO $conexion, int $idBodega, int $idProducto, int $cantidad): void
    {
        $stockDestino = $this->obtenerStockBodegaParaActualizar($conexion, $idBodega, $idProducto);
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

    private function prepararSoporteTraslado(): void
    {
        if ($this->soporteTrasladoDisponible !== null) {
            return;
        }

        $conexion = obtenerConexion();
        $sql = <<<SQL
            SELECT COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'ventas'
              AND COLUMN_NAME = 'motivo_salida'
            LIMIT 1
        SQL;
        $fila = $conexion->query($sql)->fetch();
        $tipoColumna = strtolower((string) ($fila['COLUMN_TYPE'] ?? ''));

        if (str_contains($tipoColumna, "'traslado'")) {
            $this->soporteTrasladoDisponible = true;
            return;
        }

        $conexion->exec("ALTER TABLE ventas MODIFY COLUMN motivo_salida ENUM('normal','devolucion','fallo','traslado') NOT NULL DEFAULT 'normal'");
        $this->soporteTrasladoDisponible = true;
    }
}
