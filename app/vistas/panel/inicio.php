<?php

declare(strict_types=1);

$tituloPagina = 'Panel general';
$tituloSeccion = 'Vista general del sistema';
$descripcionSeccion = 'Esta pantalla funciona como acceso inicial del panel, sin duplicar modulos ni repetir funcionalidades del menu lateral.';
$moduloActivo = '';
$resaltarConfiguracion = false;

ob_start();
?>
<div class="rejilla">
    <article class="tarjeta">
        <h3>Datos de sesion</h3>
        <p>Usuario: <?= htmlspecialchars($autenticacion['usuario'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Nombre: <?= htmlspecialchars($autenticacion['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
        <span class="etiqueta">Rol: <?= htmlspecialchars($autenticacion['rol'], ENT_QUOTES, 'UTF-8') ?></span>
    </article>

    <article class="tarjeta">
        <h3>Permisos activos</h3>
        <ul class="lista">
            <li>Registrar productos: <?= !empty($permisos['registrar_productos']) ? 'Si' : 'No' ?></li>
            <li>Modificar productos: <?= !empty($permisos['modificar_productos']) ? 'Si' : 'No' ?></li>
            <li>Registrar movimientos: <?= !empty($permisos['registrar_movimientos']) ? 'Si' : 'No' ?></li>
        </ul>
    </article>

    <article class="tarjeta">
        <h3>Navegacion organizada</h3>
        <p>El menu lateral ahora contiene solo Entradas, Productos, Proveedores y Salidas.</p>
        <p>Configuracion permanece fuera del menu y se consulta desde el engranaje superior.</p>
    </article>

    <article class="tarjeta">
        <h3>Estado visual</h3>
        <p>Se unifico la interfaz del panel para que cada vista mantenga el mismo estilo profesional, limpio y moderno.</p>
    </article>
</div>
<?php
$contenidoModulo = ob_get_clean();

require __DIR__ . '/plantilla.php';
