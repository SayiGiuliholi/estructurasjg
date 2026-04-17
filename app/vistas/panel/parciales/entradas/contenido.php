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
                <h3 class="subtitulo">Nueva entrada</h3>
                <p>Completa el formulario para registrar el ingreso de mercaderia.</p>
            </div>
            <div class="botones-acciones">
                <button type="button" class="boton-principal">Guardar</button>
                <button type="button" class="boton-fantasma">Limpiar</button>
            </div>
        </div>

        <form class="formulario-grid" id="form-entradas">
            <div class="campo">
                <label for="entrada-codigo">Codigo</label>
                <input id="entrada-codigo" name="codigo" type="text" value="<?= htmlspecialchars($formularioEntrada['codigo'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="campo campo-amplio">
                <label for="entrada-descripcion">Descripcion</label>
                <input id="entrada-descripcion" name="descripcion" type="text" value="<?= htmlspecialchars($formularioEntrada['descripcion'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="campo">
                <label for="entrada-cantidad">Cantidad</label>
                <input id="entrada-cantidad" name="cantidad" type="number" min="1" value="<?= htmlspecialchars($formularioEntrada['cantidad'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="campo">
                <label for="entrada-precio">Precio unitario</label>
                <input id="entrada-precio" name="precio" type="number" min="0" step="0.01" value="<?= htmlspecialchars($formularioEntrada['precio'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="campo">
                <label for="entrada-proveedor">Proveedor</label>
                <select id="entrada-proveedor" name="proveedor">
                    <?php foreach ($formularioEntrada['proveedores'] as $proveedor): ?>
                        <option><?= htmlspecialchars($proveedor, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="entrada-total">Total</label>
                <input id="entrada-total" name="total" type="text" value="<?= htmlspecialchars($formularioEntrada['total'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                <span class="ayuda-campo">El total se calcula automaticamente con cantidad por precio.</span>
            </div>
        </form>
    </article>

    <article class="tarjeta bloque">
        <h3 class="subtitulo">Resumen operativo</h3>
        <ul class="lista-simple">
            <?php foreach ($resumenOperativo as $itemResumen): ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars($itemResumen['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($itemResumen['detalle'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <?php if ($itemResumen['tipo'] === 'valor'): ?>
                        <span class="valor-destacado"><?= htmlspecialchars($itemResumen['valor'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php elseif ($itemResumen['tipo'] === 'estado-ok'): ?>
                        <span class="estado ok"><?= htmlspecialchars($itemResumen['valor'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                        <span class="estado alerta"><?= htmlspecialchars($itemResumen['valor'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="nota-modulo"><?= htmlspecialchars($notaModulo, ENT_QUOTES, 'UTF-8') ?></div>
    </article>
</div>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 0;">
        <div>
            <h3 class="subtitulo">Historial de entradas</h3>
            <p>Vista unica para consultar, editar o eliminar movimientos de ingreso.</p>
        </div>
        <div class="botones-acciones">
            <button type="button" class="boton-secundario">Editar</button>
            <button type="button" class="boton-peligro">Eliminar</button>
        </div>
    </div>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Descripcion</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historialEntradas as $entrada): ?>
                    <tr>
                        <td><?= htmlspecialchars($entrada['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['precio'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['total'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="estado <?= htmlspecialchars($entrada['estado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($entrada['stock'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>Editar | Eliminar</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</article>
