<?php

declare(strict_types=1);

require_once __DIR__ . '/../ayudantes/sesion.php';
require_once __DIR__ . '/../configuracion/rutas.php';

if (!usuarioAutenticado()) {
    redirigirA('index.php');
}
