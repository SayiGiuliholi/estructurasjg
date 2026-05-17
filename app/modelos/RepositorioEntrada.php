<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/ServicioConsecutivosFactura.php';
require_once __DIR__ . '/ServicioStockBodega.php';
require_once __DIR__ . '/ServicioConsultaEntradas.php';

final class RepositorioEntrada
{
    // Soporte y servicios compartidos
    private ?bool $columnaEstadoDisponible = null;
    private ServicioConsecutivosFactura $servicioConsecutivos;
    private ServicioStockBodega $servicioStockBodega;
    private ServicioConsultaEntradas $servicioConsultaEntradas;
    private const PREFIJO_FACTURA = 'FAC-2026-';
    private const RANGO_INICIO = 1001;
    private const RANGO_FIN = 4999;

    public function __construct()
    {
        $this->servicioConsecutivos = new ServicioConsecutivosFactura();
        $this->servicioStockBodega = new ServicioStockBodega();
        $this->servicioConsultaEntradas = new ServicioConsultaEntradas();
    }

    public function existeProductoPorCodigo(string $codigoProducto): bool
    {
        $codigo = trim($codigoProducto);
        if ($codigo === '') {
            return false;
        }

        $conexion = obtenerConexion();
        $sql = 'SELECT 1 FROM productos WHERE codigo = :codigo LIMIT 1';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['codigo' => $codigo]);

        return (bool) $sentencia->fetchColumn();
    }

    public function existeCodigoCompra(string $codigoCompra): bool
    {
        $codigo = trim($codigoCompra);
        if ($codigo === '') {
            return false;
        }

        $conexion = obtenerConexion();
        $sql = 'SELECT 1 FROM compras WHERE codigo = :codigo LIMIT 1';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['codigo' => $codigo]);

        return (bool) $sentencia->fetchColumn();
    }

    public function obtenerProductoParaFormulario(string $codigoProducto): ?array
    {
        $codigo = trim($codigoProducto);
        if ($codigo === '') {
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
                p.id_proveedor,
                pr.nombre AS proveedor
            FROM productos p
            INNER JOIN proveedores pr ON pr.id_proveedor = p.id_proveedor
            WHERE p.codigo = :codigo
            {$filtroEstado}
            LIMIT 1
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['codigo' => $codigo]);
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
        $sql = 'SELECT 1 FROM productos WHERE codigo = :codigo AND estado = 0 LIMIT 1';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['codigo' => $codigo]);

        return (bool) $sentencia->fetchColumn();
    }

    public function obtenerSiguienteCodigoFactura(): string
    {
        $conexion = obtenerConexion();
        $numero = $this->servicioConsecutivos->obtenerSiguienteNumeroFactura(
            $conexion,
            self::PREFIJO_FACTURA,
            'compras',
            self::RANGO_INICIO,
            self::RANGO_FIN
        );
        return self::PREFIJO_FACTURA . $numero;
    }

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
        return $this->servicioConsultaEntradas->obtenerResumenIndicadores($this->tieneColumnaEstado());
    }

    public function obtenerUltimoMovimiento(): ?array
    {
        return $this->servicioConsultaEntradas->obtenerUltimoMovimiento();
    }

    public function obtenerHistorial(int $limite = 20, int $offset = 0): array
    {
        return $this->servicioConsultaEntradas->obtenerHistorial($limite, $offset);
    }

    public function contarHistorial(): int
    {
        return $this->servicioConsultaEntradas->contarHistorial();
    }

    private function obtenerOCrearProducto(PDO $conexion, array $datos): int
    {
        $campoEstado = $this->tieneColumnaEstado() ? ', estado' : '';
        $sqlBuscar = 'SELECT id_producto' . $campoEstado . ' FROM productos WHERE codigo = :codigo LIMIT 1';
        $sentenciaBuscar = $conexion->prepare($sqlBuscar);
        $sentenciaBuscar->execute(['codigo' => $datos['codigo']]);
        $fila = $sentenciaBuscar->fetch();

        if ($fila) {
            $idProducto = (int) $fila['id_producto'];
            if ($this->tieneColumnaEstado() && ((int) ($fila['estado'] ?? 1)) === 0) {
                throw new RuntimeException(
                    'El codigo "' . ((string) ($datos['codigo'] ?? '')) . '" esta desactivado y no puede registrarse.'
                );
            }

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

    private function crearCompra(PDO $conexion, array $datos): int
    {
        $lock = 'factura_entradas_2026';
        if (!$this->servicioConsecutivos->adquirirBloqueoFactura($conexion, $lock)) {
            throw new RuntimeException('No se pudo bloquear la numeracion de facturas de entradas.');
        }

        try {
            $numero = $this->servicioConsecutivos->obtenerSiguienteNumeroFactura(
                $conexion,
                self::PREFIJO_FACTURA,
                'compras',
                self::RANGO_INICIO,
                self::RANGO_FIN,
                true
            );
            $codigoCompra = self::PREFIJO_FACTURA . $numero;
        } finally {
            $this->servicioConsecutivos->liberarBloqueoFactura($conexion, $lock);
        }

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
        $this->servicioStockBodega->incrementarStock($conexion, $idBodega, $idProducto, $cantidad);
    }
}
