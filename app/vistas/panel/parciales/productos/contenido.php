<div class="resumen-kpis">
    <?php foreach ($resumenIndicadores as $indicador): ?>
        <article class="kpi">
            <span><?= htmlspecialchars($indicador['etiqueta'], ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= htmlspecialchars($indicador['valor'], ENT_QUOTES, 'UTF-8') ?></strong>
        </article>
    <?php endforeach; ?>
</div>

<article class="tarjeta bloque">
    <div class="cabecera-modulo">
        <div>
            <h3 class="subtitulo">Catalogo de productos</h3>
            <p>Vista de consulta en tiempo real del inventario registrado por movimientos.</p>
        </div>
    </div>

    <div class="tabla-contenedor">
        <table class="tabla tabla-historial-productos">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Proveedor</th>
                    <th>Bodega(s)</th>
                    <th>Stock</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($catalogoProductos) === 0): ?>
                    <tr>
                        <td colspan="7">Aun no hay productos registrados.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($catalogoProductos as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['factura'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['bodegas'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['stock'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['precio'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginacion-contenedor">
        <form method="get" class="paginacion-form">
            <input type="hidden" name="modulo" value="productos">
            <input type="hidden" name="pagina" value="1">
            <label for="productos-por-pagina">Registros por pagina</label>
            <select id="productos-por-pagina" name="por_pagina" onchange="this.form.submit()">
                <?php foreach ($paginacion['opcionesPorPagina'] as $opcion): ?>
                    <option value="<?= htmlspecialchars((string) $opcion, ENT_QUOTES, 'UTF-8') ?>" <?= (int) $paginacion['porPagina'] === (int) $opcion ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $opcion, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="paginacion-resumen">
                <?= htmlspecialchars((string) $paginacion['totalRegistros'], ENT_QUOTES, 'UTF-8') ?> registros
            </span>
        </form>

        <?php if ((int) $paginacion['totalPaginas'] > 1): ?>
            <div class="paginacion-botones">
                <?php
                $paginaActual = (int) $paginacion['paginaActual'];
                $totalPaginas = (int) $paginacion['totalPaginas'];
                $porPaginaActual = (int) $paginacion['porPagina'];
                ?>
                <a class="boton-fantasma <?= $paginaActual <= 1 ? 'deshabilitado' : '' ?>" href="?<?= htmlspecialchars(http_build_query(['modulo' => 'productos', 'pagina' => max(1, $paginaActual - 1), 'por_pagina' => $porPaginaActual]), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
                <span class="paginacion-texto">Pagina <?= htmlspecialchars((string) $paginaActual, ENT_QUOTES, 'UTF-8') ?> de <?= htmlspecialchars((string) $totalPaginas, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="boton-fantasma <?= $paginaActual >= $totalPaginas ? 'deshabilitado' : '' ?>" href="?<?= htmlspecialchars(http_build_query(['modulo' => 'productos', 'pagina' => min($totalPaginas, $paginaActual + 1), 'por_pagina' => $porPaginaActual]), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
            </div>
        <?php endif; ?>
    </div>
</article>
