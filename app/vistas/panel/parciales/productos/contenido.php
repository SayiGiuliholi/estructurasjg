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
            <p>Cada producto vive unicamente en este modulo para evitar duplicidad de vistas.</p>
        </div>
        <div class="botones-acciones">
            <button type="button" class="boton-principal">Nuevo producto</button>
            <button type="button" class="boton-secundario">Exportar</button>
        </div>
    </div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Descripcion</th>
                    <th>Proveedor</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($catalogoProductos as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['stock'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($producto['precio'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="estado <?= htmlspecialchars($producto['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($producto['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>Editar | Eliminar</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</article>

<div class="paneles">
    <article class="tarjeta bloque">
        <h3 class="subtitulo">Formulario rapido</h3>
        <form class="formulario-grid">
            <div class="campo">
                <label for="producto-codigo">Codigo</label>
                <input id="producto-codigo" type="text" value="<?= htmlspecialchars($formularioProducto['codigo'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo campo-amplio">
                <label for="producto-descripcion">Descripcion</label>
                <input id="producto-descripcion" type="text" value="<?= htmlspecialchars($formularioProducto['descripcion'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo">
                <label for="producto-proveedor">Proveedor</label>
                <select id="producto-proveedor">
                    <?php foreach ($formularioProducto['proveedores'] as $proveedor): ?>
                        <option><?= htmlspecialchars($proveedor, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="producto-stock">Stock</label>
                <input id="producto-stock" type="number" value="<?= htmlspecialchars($formularioProducto['stock'], ENT_QUOTES, 'UTF-8') ?>" min="0">
            </div>
            <div class="campo">
                <label for="producto-precio">Precio</label>
                <input id="producto-precio" type="number" value="<?= htmlspecialchars($formularioProducto['precio'], ENT_QUOTES, 'UTF-8') ?>" min="0" step="0.01">
            </div>
            <div class="fila-acciones campo-amplio">
                <button type="button" class="boton-principal">Guardar</button>
                <button type="button" class="boton-fantasma">Cancelar</button>
            </div>
        </form>
    </article>

    <article class="tarjeta bloque">
        <h3 class="subtitulo">Control visual</h3>
        <ul class="lista-simple">
            <?php foreach ($controlVisual as $itemControl): ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars($itemControl['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($itemControl['detalle'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <span class="estado <?= htmlspecialchars($itemControl['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($itemControl['estado'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>
</div>
