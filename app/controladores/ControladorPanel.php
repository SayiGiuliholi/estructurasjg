<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/rutas.php';
require_once __DIR__ . '/../ayudantes/sesion.php';

final class ControladorPanel
{
    private function esSuperadmin(array $autenticacion): bool
    {
        return esSuperadminSesion($autenticacion);
    }

    private function permisoActivo(array $permisos, string $clave): bool
    {
        return ((int) ($permisos[$clave] ?? 0)) === 1;
    }

    /**
     * Determina si el usuario autenticado puede acceder al modulo solicitado.
     */
    public function puedeAccederAlModulo(string $modulo, array $permisos, array $autenticacion = []): bool
    {
        if ($this->esSuperadmin($autenticacion)) {
            return true;
        }

        $esAdministrador = strtolower((string) ($autenticacion['rol'] ?? '')) === 'administrador'
            || (int) ($autenticacion['id_rol'] ?? 0) === 1;

        return match ($modulo) {
            'entradas', 'salidas' => $this->permisoActivo($permisos, 'registrar_movimientos')
                || $this->permisoActivo($permisos, 'consultar_movimientos'),

            'productos' => $this->permisoActivo($permisos, 'registrar_productos')
                || $this->permisoActivo($permisos, 'modificar_productos')
                || $this->permisoActivo($permisos, 'consultar_movimientos'),

            'proveedores' => $this->permisoActivo($permisos, 'registrar_productos')
                || $this->permisoActivo($permisos, 'modificar_productos')
                || $this->permisoActivo($permisos, 'consultar_movimientos')
                || $this->permisoActivo($permisos, 'configuracion'),

            // Configuracion queda reservada para administradores.
            'configuracion' => $esAdministrador,

            default => false,
        };
    }

    /**
     * Devuelve el primer modulo permitido para el usuario actual.
     */
    public function obtenerPrimerModuloPermitido(array $permisos, array $autenticacion = []): string
    {
        if ($this->esSuperadmin($autenticacion)) {
            return 'entradas';
        }

        $modulosDisponibles = ['entradas', 'productos', 'proveedores', 'salidas', 'configuracion'];

        foreach ($modulosDisponibles as $moduloDisponible) {
            if ($this->puedeAccederAlModulo($moduloDisponible, $permisos, $autenticacion)) {
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

        if (!array_key_exists($moduloSolicitado, $modulos) || !$this->puedeAccederAlModulo($moduloSolicitado, $permisos, $autenticacion)) {
            $moduloSeguro = $this->obtenerPrimerModuloPermitido($permisos, $autenticacion);
            redirigirA('panel.php?modulo=' . urlencode($moduloSeguro));
        }

        return __DIR__ . '/../vistas/panel/pantallas/' . $modulos[$moduloSolicitado];
    }
}
