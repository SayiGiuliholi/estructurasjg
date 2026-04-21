<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/Estructurasjg/public/css/panel/boceto-entradas.css">
</head>
<body>
    <main class="contenedor">
        <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="sub">Vista simple para mostrar avance del modulo de entradas.</p>

        <section class="kpis">
            <?php foreach ($resumen as $item): ?>
                <article class="kpi">
                    <span><?= htmlspecialchars($item['etiqueta'], ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= htmlspecialchars($item['valor'], ENT_QUOTES, 'UTF-8') ?></strong>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="tarjeta">
            <h2>Registrar entrada (demo)</h2>
            <form class="formulario" action="#" method="post">
                <input type="text" placeholder="Codigo factura">
                <input type="text" placeholder="Proveedor">
                <input type="text" placeholder="Producto">
                <input type="number" placeholder="Cantidad">
                <button type="button">Guardar (demo)</button>
            </form>
        </section>

        <section class="tarjeta">
            <h2>Historial</h2>
            <table>
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($fila['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $fila['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($fila['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($fila['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
