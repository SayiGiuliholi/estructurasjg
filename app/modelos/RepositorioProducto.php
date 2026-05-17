<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/Producto.php';
require_once __DIR__ . '/ServicioMetricasProducto.php';

final class RepositorioProducto
{
    // Soporte y servicios compartidos
    private ?bool $columnaEstadoDisponible = null;
    private ServicioMetricasProducto $servicioMetricasProducto;

    public function __construct()
    {
        $this->servicioMetricasProducto = new ServicioMetricasProducto();
    }

    /**
     * Verifica si el codigo ya existe (con opcion de ignorar un id).
     */
    private function existeCodigo(string $codigo, ?int $ignorarIdProducto = null): bool
    {
        $conexion = obtenerConexion();
        $sql = 'SELECT 1 FROM productos WHERE codigo = :codigo';
        $parametros = ['codigo' => $codigo];

        if ($ignorarIdProducto !== null && $ignorarIdProducto > 0) {
            $sql .= ' AND id_producto <> :id_producto';
            $parametros['id_producto'] = $ignorarIdProducto;
        }

        $sql .= ' LIMIT 1';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($parametros);
        return (bool) $sentencia->fetchColumn();
    }

    /**
     * Obtiene todos los productos (sin paginacion efectiva).
     */
    public function obtenerTodos(): array
    {
        return $this->obtenerPaginado(1000000, 0);
    }

    /**
     * Agrega la columna de estado si no existe.
     */
    public function prepararSoporteEstado(): void
    {
        if ($this->tieneColumnaEstado()) {
            return;
        }
        throw new RuntimeException(
            'Falta la columna productos.estado. Ejecuta la migracion de seguridad antes de continuar.'
        );
    }

    /**
     * Obtiene catalogo paginado para listado de productos.
     */
    public function obtenerPaginado(int $limite = 20, int $offset = 0): array
    {
        $conexion = obtenerConexion();
        $tieneEstado = $this->tieneColumnaEstado();
        $campoEstado = $tieneEstado ? 'p.estado AS estado' : '1 AS estado';

        $sql = <<<SQL
            SELECT
                p.id_producto,
                COALESCE(
                    (
                        SELECT c2.codigo
                        FROM detalle_compras dc2
                        INNER JOIN compras c2 ON c2.id_compra = dc2.id_compra
                        WHERE dc2.id_producto = p.id_producto
                        ORDER BY c2.fecha DESC, c2.id_compra DESC
                        LIMIT 1
                    ),
                    'Sin factura'
                ) AS ultima_factura,
                p.codigo,
                p.descripcion,
                p.id_proveedor,
                p.stock,
                p.precio,
                p.fecha,
                {$campoEstado},
                pr.nombre AS proveedor_nombre,
                COALESCE(
                    GROUP_CONCAT(
                        CONCAT(b.codigo, ' (', sb.stock_actual, ')')
                        ORDER BY b.nombre SEPARATOR ' | '
                    ),
                    'Sin bodega'
                ) AS resumen_bodegas
            FROM productos p
            INNER JOIN proveedores pr ON pr.id_proveedor = p.id_proveedor
            LEFT JOIN stock_bodega sb ON sb.id_producto = p.id_producto
            LEFT JOIN bodegas b ON b.id_bodega = sb.id_bodega
            GROUP BY
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.id_proveedor,
                p.stock,
                p.precio,
                p.fecha,
                pr.nombre
            ORDER BY p.fecha DESC, p.id_producto DESC
            LIMIT :limite OFFSET :offset
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->bindValue('limite', $limite, PDO::PARAM_INT);
        $sentencia->bindValue('offset', $offset, PDO::PARAM_INT);
        $sentencia->execute();
        $filas = $sentencia->fetchAll();

        return array_map(
            static fn(array $fila): Producto => new Producto(
                (int) $fila['id_producto'],
                (string) ($fila['ultima_factura'] ?? 'Sin factura'),
                (string) $fila['codigo'],
                (string) $fila['descripcion'],
                (int) $fila['id_proveedor'],
                (string) $fila['proveedor_nombre'],
                (int) $fila['stock'],
                (float) $fila['precio'],
                isset($fila['fecha']) ? (string) $fila['fecha'] : null,
                (string) ($fila['resumen_bodegas'] ?? 'Sin bodega'),
                ((int) ($fila['estado'] ?? 1)) === 1,
            ),
            $filas
        );
    }

    /**
     * Busca un producto por id.
     */
    public function buscarPorId(int $idProducto): ?Producto
    {
        $conexion = obtenerConexion();
        $tieneEstado = $this->tieneColumnaEstado();
        $campoEstado = $tieneEstado ? 'p.estado AS estado' : '1 AS estado';

        $sql = <<<SQL
            SELECT
                p.id_producto,
                COALESCE(
                    (
                        SELECT c2.codigo
                        FROM detalle_compras dc2
                        INNER JOIN compras c2 ON c2.id_compra = dc2.id_compra
                        WHERE dc2.id_producto = p.id_producto
                        ORDER BY c2.fecha DESC, c2.id_compra DESC
                        LIMIT 1
                    ),
                    'Sin factura'
                ) AS ultima_factura,
                p.codigo,
                p.descripcion,
                p.id_proveedor,
                p.stock,
                p.precio,
                p.fecha,
                {$campoEstado},
                pr.nombre AS proveedor_nombre
            FROM productos p
            INNER JOIN proveedores pr ON pr.id_proveedor = p.id_proveedor
            WHERE p.id_producto = :id_producto
            LIMIT 1
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['id_producto' => $idProducto]);

        $fila = $sentencia->fetch();

        if (!$fila) {
            return null;
        }

        return new Producto(
            (int) $fila['id_producto'],
            (string) ($fila['ultima_factura'] ?? 'Sin factura'),
            (string) $fila['codigo'],
            (string) $fila['descripcion'],
            (int) $fila['id_proveedor'],
            (string) $fila['proveedor_nombre'],
                (int) $fila['stock'],
                (float) $fila['precio'],
                isset($fila['fecha']) ? (string) $fila['fecha'] : null,
                '',
                ((int) ($fila['estado'] ?? 1)) === 1,
            );
    }

    /**
     * Crea un nuevo producto.
     */
    public function crear(array $datos): int
    {
        if ($this->existeCodigo((string) ($datos['codigo'] ?? ''))) {
            throw new RuntimeException('El codigo del producto ya existe.');
        }

        $conexion = obtenerConexion();

        $sql = <<<SQL
            INSERT INTO productos (codigo, descripcion, id_proveedor, stock, precio)
            VALUES (:codigo, :descripcion, :id_proveedor, :stock, :precio)
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'codigo' => $datos['codigo'],
            'descripcion' => $datos['descripcion'],
            'id_proveedor' => $datos['id_proveedor'],
            'stock' => $datos['stock'],
            'precio' => $datos['precio'],
        ]);

        return (int) $conexion->lastInsertId();
    }

    /**
     * Actualiza un producto existente.
     */
    public function actualizar(int $idProducto, array $datos): void
    {
        if ($this->existeCodigo((string) ($datos['codigo'] ?? ''), $idProducto)) {
            throw new RuntimeException('El codigo del producto ya existe.');
        }

        $conexion = obtenerConexion();

        $sql = <<<SQL
            UPDATE productos
            SET
                codigo = :codigo,
                descripcion = :descripcion,
                id_proveedor = :id_proveedor,
                stock = :stock,
                precio = :precio
            WHERE id_producto = :id_producto
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_producto' => $idProducto,
            'codigo' => $datos['codigo'],
            'descripcion' => $datos['descripcion'],
            'id_proveedor' => $datos['id_proveedor'],
            'stock' => $datos['stock'],
            'precio' => $datos['precio'],
        ]);
    }

    /**
     * Elimina un producto por id.
     */
    public function eliminar(int $idProducto): void
    {
        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare('DELETE FROM productos WHERE id_producto = :id_producto');
        $sentencia->execute(['id_producto' => $idProducto]);
    }

    /**
     * Cambia estado activo/inactivo de un producto.
     */
    public function cambiarEstado(int $idProducto, bool $activo): void
    {
        if (!$this->tieneColumnaEstado()) {
            return;
        }

        $conexion = obtenerConexion();
        $sentencia = $conexion->prepare(
            'UPDATE productos SET estado = :estado WHERE id_producto = :id_producto'
        );
        $sentencia->execute([
            'id_producto' => $idProducto,
            'estado' => $activo ? 1 : 0,
        ]);
    }

    /**
     * Total de productos en catalogo.
     */
    public function contarTotal(): int
    {
        return $this->servicioMetricasProducto->contarTotal();
    }

    /**
     * Suma global de stock.
     */
    public function sumarStockTotal(): int
    {
        return $this->servicioMetricasProducto->sumarStockTotal();
    }

    /**
     * Cuenta productos con stock menor o igual al umbral.
     */
    public function contarStockBajo(int $umbral = 10): int
    {
        return $this->servicioMetricasProducto->contarStockBajo($umbral);
    }

    /**
     * Valor total estimado de inventario.
     */
    public function calcularValorEstimado(): float
    {
        return $this->servicioMetricasProducto->calcularValorEstimado();
    }

    /**
     * Detecta si existe columna estado en productos.
     */
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
}
