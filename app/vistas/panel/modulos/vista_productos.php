<div class="resumen-kpis">
    <?php foreach ($resumenIndicadores as $indicador): ?>
        <article class="kpi">
            <span><?= htmlspecialchars($indicador['etiqueta'], ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= htmlspecialchars($indicador['valor'], ENT_QUOTES, 'UTF-8') ?></strong>
        </article>
    <?php endforeach; ?>
</div>

<?php if (!isset($puedeGestionProductos) || $puedeGestionProductos): ?>
<article class="tarjeta bloque">
    <div class="cabecera-modulo">
        <div>
            <h3 class="subtitulo">Edicion de producto</h3>
            <p>Actualiza datos o cambia el estado del producto seleccionado.</p>
        </div>
    </div>

    <?php if (($mensajeExito ?? '') !== ''): ?>
        <p class="nota-exito"><?= htmlspecialchars((string) $mensajeExito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (($mensajeError ?? '') !== ''): ?>
        <p class="nota-error"><?= htmlspecialchars((string) $mensajeError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (($idProductoEdicion ?? null) !== null): ?>
        <form class="formulario-grid" method="post">
            <input type="hidden" name="id_producto" value="<?= htmlspecialchars((string) ($fichaProducto['id_producto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="campo">
                <label for="prod-codigo-editar">Codigo</label>
                <input id="prod-codigo-editar" name="codigo" type="text" required value="<?= htmlspecialchars((string) ($fichaProducto['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo">
                <label for="prod-descripcion-editar">Producto</label>
                <input id="prod-descripcion-editar" name="descripcion" type="text" required value="<?= htmlspecialchars((string) ($fichaProducto['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo">
                <label for="prod-proveedor-editar">Proveedor</label>
                <select id="prod-proveedor-editar" name="id_proveedor" required>
                    <option value="">Selecciona proveedor</option>
                    <?php foreach (($directorioProveedores ?? []) as $proveedor): ?>
                        <option
                            value="<?= htmlspecialchars((string) $proveedor->idProveedor, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ((string) $proveedor->idProveedor === (string) ($fichaProducto['id_proveedor'] ?? '')) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($proveedor->nombre, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="prod-stock-editar">Stock</label>
                <input id="prod-stock-editar" name="stock" type="number" min="0" required value="<?= htmlspecialchars((string) ($fichaProducto['stock'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo">
                <label for="prod-precio-editar">Precio</label>
                <input id="prod-precio-editar" name="precio" type="text" inputmode="numeric" required value="<?= htmlspecialchars((string) ($fichaProducto['precio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            </div>
            <div class="fila-acciones campo-amplio">
                <button type="submit" name="accion" value="actualizar" class="boton-principal">Guardar cambios</button>
            </div>
        </form>
    <?php else: ?>
        <p class="nota-ayuda">Selecciona "Editar" en la tabla para cargar un producto.</p>
    <?php endif; ?>
</article>
<?php else: ?>
<article class="tarjeta bloque">
    <p class="nota-modulo">Modo solo lectura: puedes consultar productos, pero no editarlos ni cambiar su estado.</p>
</article>
<?php endif; ?>

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
            <input id="productos-filtro-buscar" type="text" placeholder="Buscar codigo o producto...">
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

        <div class="campo">
            <label for="productos-filtro-bodega">Bodega</label>
            <select id="productos-filtro-bodega">
                <option value="">Todas</option>
                <option value="principal">Principal</option>
                <option value="secundaria">Secundaria</option>
            </select>
        </div>
    </form>

    <?php
    $catalogoPrincipal = $catalogoPrincipal ?? [];
    $catalogoSecundaria = $catalogoSecundaria ?? [];
    $columnasTabla = (!isset($puedeGestionProductos) || $puedeGestionProductos) ? 8 : 7;
    $renderTablaBodega = static function (array $productosTabla, string $tituloBodega, string $idTabla, string $idVacio, string $claveBodega) use ($columnasTabla, $puedeGestionProductos): void {
        ?>
        <article class="tarjeta bloque bloque-bodega-productos js-panel-bodega" data-bodega="<?= htmlspecialchars($claveBodega, ENT_QUOTES, 'UTF-8') ?>">
            <div class="cabecera-modulo">
                <div>
                    <h3 class="subtitulo"><?= htmlspecialchars($tituloBodega, ENT_QUOTES, 'UTF-8') ?></h3>
                </div>
            </div>
            <div class="tabla-contenedor">
                <table class="tabla tabla-historial-productos" id="<?= htmlspecialchars($idTabla, ENT_QUOTES, 'UTF-8') ?>">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th>Bodega(s)</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <?php if (!isset($puedeGestionProductos) || $puedeGestionProductos): ?>
                                <th>Accion</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($productosTabla) === 0): ?>
                            <tr>
                                <td colspan="<?= htmlspecialchars((string) $columnasTabla, ENT_QUOTES, 'UTF-8') ?>">No hay productos en esta bodega.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($productosTabla as $producto): ?>
                            <tr
                                class="js-historial-producto"
                                data-codigo="<?= htmlspecialchars((string) $producto['codigo'], ENT_QUOTES, 'UTF-8') ?>"
                                data-busqueda="<?= htmlspecialchars((string) ($producto['factura'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-proveedor="<?= htmlspecialchars(strtolower((string) $producto['proveedor']), ENT_QUOTES, 'UTF-8') ?>"
                                data-fecha="<?= htmlspecialchars((string) ($producto['fecha_registro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-bodega="<?= htmlspecialchars($claveBodega, ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <td><?= htmlspecialchars($producto['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($producto['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($producto['bodegas'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($producto['stock'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($producto['precio'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="estado <?= htmlspecialchars((string) $producto['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $producto['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <?php if (!isset($puedeGestionProductos) || $puedeGestionProductos): ?>
                                    <td>
                                        <div class="acciones-tabla">
                                            <form method="post" class="form-accion-tabla">
                                                <input type="hidden" name="id_producto" value="<?= htmlspecialchars((string) $producto['id_producto'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" name="accion" value="cargar" class="boton-fantasma">Editar</button>
                                            </form>
                                            <form method="post" class="form-accion-tabla">
                                                <input type="hidden" name="id_producto" value="<?= htmlspecialchars((string) $producto['id_producto'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" name="accion" value="<?= ($producto['activo'] ?? true) ? 'desactivar' : 'activar' ?>" class="<?= ($producto['activo'] ?? true) ? 'boton-peligro' : 'boton-principal' ?>">
                                                    <?= ($producto['activo'] ?? true) ? 'Desactivar' : 'Activar' ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="<?= htmlspecialchars($idVacio, ENT_QUOTES, 'UTF-8') ?>" hidden>
                            <td colspan="<?= htmlspecialchars((string) $columnasTabla, ENT_QUOTES, 'UTF-8') ?>">No hay resultados con los filtros aplicados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>
        <?php
    };
    ?>

    <div class="productos-por-bodega">
        <?php $renderTablaBodega($catalogoPrincipal, 'Bodega Principal', 'tabla-historial-productos-principal', 'productos-historial-vacio-principal', 'principal'); ?>
        <?php $renderTablaBodega($catalogoSecundaria, 'Bodega Secundaria', 'tabla-historial-productos-secundaria', 'productos-historial-vacio-secundaria', 'secundaria'); ?>
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
