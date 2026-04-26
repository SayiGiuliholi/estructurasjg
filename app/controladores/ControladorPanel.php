<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/rutas.php';

final class ControladorPanel
{
    /**
     * Determina si el usuario autenticado puede acceder al modulo solicitado.
     */
    public function puedeAccederAlModulo(string $modulo, array $permisos): bool
    {
        return match ($modulo) {
            'entradas', 'salidas' => (($permisos['registrar_movimientos'] ?? 0) === 1)
                || (($permisos['consultar_movimientos'] ?? 0) === 1),

            'productos' => (($permisos['registrar_productos'] ?? 0) === 1)
                || (($permisos['modificar_productos'] ?? 0) === 1)
                || (($permisos['consultar_movimientos'] ?? 0) === 1),

            'proveedores' => (($permisos['registrar_productos'] ?? 0) === 1)
                || (($permisos['modificar_productos'] ?? 0) === 1)
                || (($permisos['configuracion'] ?? 0) === 1),

            'configuracion' => (($permisos['configuracion'] ?? 0) === 1)
                || (($permisos['gestionar_roles'] ?? 0) === 1),

            default => false,
        };
    }

    /**
     * Devuelve el primer modulo permitido para el usuario actual.
     */
    public function obtenerPrimerModuloPermitido(array $permisos): string
    {
        $modulosDisponibles = ['entradas', 'productos', 'proveedores', 'salidas', 'configuracion'];

        foreach ($modulosDisponibles as $moduloDisponible) {
            if ($this->puedeAccederAlModulo($moduloDisponible, $permisos)) {
                return $moduloDisponible;
            }
        }

        return 'entradas';
    }

    /**
     * Resuelve la vista del modulo solicitado y redirige si el acceso no es valido.
     */
    public function resolverVistaModulo(array $consulta, array $autenticacion): string
    {
        $permisos = $autenticacion['permisos'] ?? [];

        $modulos = [
            'entradas' => 'entradas.php',
            'productos' => 'productos.php',
            'proveedores' => 'proveedores.php',
            'salidas' => 'salidas.php',
            'configuracion' => 'configuracion.php',
        ];

        $moduloSolicitado = $consulta['modulo'] ?? 'entradas';

        if (!array_key_exists($moduloSolicitado, $modulos) || !$this->puedeAccederAlModulo($moduloSolicitado, $permisos)) {
            $moduloSeguro = $this->obtenerPrimerModuloPermitido($permisos);
            redirigirA('panel.php?modulo=' . urlencode($moduloSeguro));
        }

        return __DIR__ . '/../vistas/panel/pantallas/' . $modulos[$moduloSolicitado];
    }
}
