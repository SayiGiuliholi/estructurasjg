<div class="resumen-kpis">
    <?php foreach ($resumenIndicadores as $indicador): ?>
        <article class="kpi">
            <span><?= htmlspecialchars($indicador['etiqueta'], ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= htmlspecialchars($indicador['valor'], ENT_QUOTES, 'UTF-8') ?></strong>
        </article>
    <?php endforeach; ?>
</div>

<div class="paneles">
    <article class="tarjeta bloque">
        <div class="cabecera-modulo">
            <div>
                <h3 class="subtitulo">Registro de venta</h3>
                <p>La cantidad solicitada se valida contra el stock disponible y el descuento se calcula automaticamente.</p>
            </div>
        </div>

        <form class="formulario-grid" id="form-salidas">
            <div class="campo">
                <label for="salida-codigo">Codigo</label>
                <input id="salida-codigo" type="text" value="<?= htmlspecialchars($formularioSalida['codigo'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo campo-amplio">
                <label for="salida-descripcion">Descripcion</label>
                <input id="salida-descripcion" type="text" value="<?= htmlspecialchars($formularioSalida['descripcion'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo">
                <label for="salida-stock">Stock disponible</label>
                <input id="salida-stock" type="number" value="<?= htmlspecialchars($formularioSalida['stock'], ENT_QUOTES, 'UTF-8') ?>" min="0">
            </div>
            <div class="campo">
                <label for="salida-cantidad">Cantidad a vender</label>
                <input id="salida-cantidad" type="number" value="<?= htmlspecialchars($formularioSalida['cantidad'], ENT_QUOTES, 'UTF-8') ?>" min="1">
            </div>
            <div class="campo">
                <label for="salida-precio">Precio unitario</label>
                <input id="salida-precio" type="number" value="<?= htmlspecialchars($formularioSalida['precio'], ENT_QUOTES, 'UTF-8') ?>" min="0" step="0.01">
            </div>
            <div class="campo">
                <label for="salida-descuento">Descuento %</label>
                <input id="salida-descuento" type="number" value="<?= htmlspecialchars($formularioSalida['descuento'], ENT_QUOTES, 'UTF-8') ?>" min="0" max="100" step="0.01">
            </div>
            <div class="campo">
                <label for="salida-subtotal">Subtotal</label>
                <input id="salida-subtotal" type="text" value="<?= htmlspecialchars($formularioSalida['subtotal'], ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
            <div class="campo">
                <label for="salida-total">Total final</label>
                <input id="salida-total" type="text" value="<?= htmlspecialchars($formularioSalida['total'], ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
            <div class="campo campo-amplio">
                <span class="ayuda-campo" id="salida-validacion"><?= htmlspecialchars($formularioSalida['mensajeValidacion'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="fila-acciones campo-amplio">
                <button type="button" class="boton-principal">Registrar salida</button>
                <button type="button" class="boton-fantasma">Cancelar</button>
            </div>
        </form>
    </article>

    <article class="tarjeta bloque">
        <h3 class="subtitulo">Estado de despacho</h3>
        <ul class="lista-simple">
            <?php foreach ($estadoDespacho as $itemEstado): ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars($itemEstado['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($itemEstado['detalle'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <span
                        <?= $itemEstado['id'] !== null ? 'id="' . htmlspecialchars($itemEstado['id'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                        class="estado <?= htmlspecialchars($itemEstado['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"
                    ><?= htmlspecialchars($itemEstado['estado'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>
</div>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 0;">
        <div>
            <h3 class="subtitulo">Historial de salidas</h3>
            <p>Registros recientes de ventas y despachos, con lectura rapida del stock comprometido.</p>
        </div>
    </div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Codigo</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Descuento</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historialSalidas as $salida): ?>
                    <tr>
                        <td><?= htmlspecialchars($salida['factura'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['descuento'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['total'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="estado <?= htmlspecialchars($salida['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($salida['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>Ver | Anular</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</article>
