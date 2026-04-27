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
            <h3 class="subtitulo">Listado de productos</h3>
            <p>Consulta y gestiona el inventario disponible.</p>
        </div>
    </div>

    <?php
    $proveedoresCatalogo = [];
    foreach ($catalogoProductos as $productoCatalogo) {
        $nombreProveedor = trim((string) ($productoCatalogo['proveedor'] ?? ''));
        if ($nombreProveedor !== '') {
            $proveedoresCatalogo[$nombreProveedor] = $nombreProveedor;
        }
    }
    ksort($proveedoresCatalogo);
    ?>

    <form class="historial-filtros" id="productos-filtros-form" onsubmit="return false;">
        <div class="campo campo-busqueda">
            <label for="productos-filtro-buscar">Buscar producto</label>
            <input id="productos-filtro-buscar" type="text" placeholder="Buscar codigo, producto o factura...">
        </div>

        <div class="campo">
            <label for="productos-filtro-proveedor">Proveedor</label>
            <select id="productos-filtro-proveedor">
                <option value="">Todos</option>
                <?php foreach ($proveedoresCatalogo as $proveedorCatalogo): ?>
                    <option value="<?= htmlspecialchars($proveedorCatalogo, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($proveedorCatalogo, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="productos-filtro-fecha">Fecha</label>
            <input id="productos-filtro-fecha" type="date">
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla tabla-historial-productos" id="tabla-historial-productos">
            <thead>
                <tr>
                 
                    <th>Código</th>
                    <th>Producto</th>
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
                    <tr
                        class="js-historial-producto"
                        data-busqueda="<?= htmlspecialchars((string) ($producto['factura'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        data-proveedor="<?= htmlspecialchars(strtolower((string) $producto['proveedor']), ENT_QUOTES, 'UTF-8') ?>"
                        data-fecha="<?= htmlspecialchars((string) ($producto['fecha_registro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        
                        <td><?= htmlspecialchars($producto['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['bodegas'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['stock'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['precio'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr id="productos-historial-vacio" hidden>
                    <td colspan="6">No hay resultados con los filtros aplicados.</td>
                </tr>
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
