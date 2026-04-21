<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelos/ModeloBocetoEntradas.php';

final class ControladorBocetoEntradas
{
    public function mostrar(): void
    {
        $modelo = new ModeloBocetoEntradas();
        $datos = $modelo->obtenerDatos();

        $titulo = 'Boceto Minimo - Entradas';
        $resumen = $datos['resumen'];
        $historial = $datos['historial'];

        require __DIR__ . '/../vistas/boceto/entradas.php';
    }
}
