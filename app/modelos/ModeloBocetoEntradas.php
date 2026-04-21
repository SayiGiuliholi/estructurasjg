<?php

declare(strict_types=1);

final class ModeloBocetoEntradas
{
    public function obtenerDatos(): array
    {
        return [
            'resumen' => [
                ['etiqueta' => 'Entradas hoy', 'valor' => '4'],
                ['etiqueta' => 'Unidades', 'valor' => '120'],
                ['etiqueta' => 'Valor total', 'valor' => '$ 1.250.000'],
            ],
            'historial' => [
                [
                    'codigo' => 'FAC-001',
                    'producto' => 'Tubo estructural 2x2',
                    'cantidad' => 20,
                    'proveedor' => 'Aceros del Norte',
                    'fecha' => '2026-04-20',
                ],
                [
                    'codigo' => 'FAC-002',
                    'producto' => 'Lamina galvanizada',
                    'cantidad' => 35,
                    'proveedor' => 'Metal Plus',
                    'fecha' => '2026-04-20',
                ],
            ],
        ];
    }
}
