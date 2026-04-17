<div class="paneles">
    <article class="tarjeta bloque">
        <div class="cabecera-modulo">
            <div>
                <h3 class="subtitulo">Ficha del proveedor</h3>
                <p>Registra y actualiza los datos principales de cada proveedor.</p>
            </div>
        </div>
        <form class="formulario-grid">
            <div class="campo">
                <label for="prov-ruc">RUC</label>
                <input id="prov-ruc" type="text" value="<?= htmlspecialchars($fichaProveedor['ruc'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo">
                <label for="prov-nombre">Nombre</label>
                <input id="prov-nombre" type="text" value="<?= htmlspecialchars($fichaProveedor['nombre'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo">
                <label for="prov-telefono">Telefono</label>
                <input id="prov-telefono" type="text" value="<?= htmlspecialchars($fichaProveedor['telefono'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="campo campo-amplio">
                <label for="prov-direccion">Direccion</label>
                <textarea id="prov-direccion"><?= htmlspecialchars($fichaProveedor['direccion'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="fila-acciones campo-amplio">
                <button type="button" class="boton-principal">Guardar proveedor</button>
                <button type="button" class="boton-secundario">Editar</button>
                <button type="button" class="boton-peligro">Eliminar</button>
            </div>
        </form>
    </article>

    <article class="tarjeta bloque">
        <h3 class="subtitulo">Indicadores</h3>
        <ul class="lista-simple">
            <?php foreach ($indicadores as $indicador): ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars($indicador['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($indicador['detalle'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <?php if ($indicador['tipo'] === 'valor'): ?>
                        <span class="valor-destacado"><?= htmlspecialchars($indicador['valor'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php elseif ($indicador['tipo'] === 'estado-ok'): ?>
                        <span class="estado ok"><?= htmlspecialchars($indicador['valor'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                        <span class="estado alerta"><?= htmlspecialchars($indicador['valor'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>
</div>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 0;">
        <div>
            <h3 class="subtitulo">Directorio de proveedores</h3>
            <p>Vista unica del modulo, sin repetir informacion en Inicio ni en otros apartados.</p>
        </div>
        <div class="botones-acciones">
            <button type="button" class="boton-fantasma">Filtrar</button>
        </div>
    </div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>RUC</th>
                    <th>Nombre</th>
                    <th>Telefono</th>
                    <th>Direccion</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($directorioProveedores as $proveedor): ?>
                    <tr>
                        <td><?= htmlspecialchars($proveedor['ruc'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['direccion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="estado <?= htmlspecialchars($proveedor['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($proveedor['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>Editar | Eliminar</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</article>
