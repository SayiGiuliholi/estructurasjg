<?php

declare(strict_types=1);

final class ServicioConsecutivosFactura
{
    /**
     * Busca el siguiente consecutivo disponible para una serie de facturas.
     */
    public function obtenerSiguienteNumeroFactura(
        PDO $conexion,
        string $prefijo,
        string $tabla,
        int $inicio,
        int $fin,
        bool $forUpdate = false
    ): int {
        $forUpdateSql = $forUpdate ? ' FOR UPDATE' : '';
        $sql = "SELECT codigo FROM {$tabla} WHERE codigo LIKE :prefijo{$forUpdateSql}";
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute(['prefijo' => $prefijo . '%']);
        $filas = $sentencia->fetchAll();

        $usados = [];
        $patron = '/^' . preg_quote($prefijo, '/') . '(\d{4})$/';
        foreach ($filas as $fila) {
            $codigo = (string) ($fila['codigo'] ?? '');
            if (preg_match($patron, $codigo, $coincidencia) === 1) {
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

        throw new RuntimeException('Se agotaron los consecutivos de facturas para el rango solicitado.');
    }

    /**
     * Adquiere un lock de MySQL para proteger la asignacion de consecutivos.
     */
    public function adquirirBloqueoFactura(PDO $conexion, string $nombre, int $timeout = 5): bool
    {
        $sentencia = $conexion->prepare('SELECT GET_LOCK(:nombre, :timeout) AS bloqueado');
        $sentencia->execute([
            'nombre' => $nombre,
            'timeout' => $timeout,
        ]);
        $fila = $sentencia->fetch();
        return ((int) ($fila['bloqueado'] ?? 0)) === 1;
    }

    /**
     * Libera el lock de MySQL asociado a la serie de facturas.
     */
    public function liberarBloqueoFactura(PDO $conexion, string $nombre): void
    {
        $sentencia = $conexion->prepare('SELECT RELEASE_LOCK(:nombre) AS liberado');
        $sentencia->execute(['nombre' => $nombre]);
    }
}
