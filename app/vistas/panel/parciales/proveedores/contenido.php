<article class="tarjeta bloque">
    <div class="cabecera-modulo">
        <div>
            <h3 class="subtitulo">Ficha del proveedor</h3>
            <p>Registra y actualiza los datos principales de cada proveedor.</p>
        </div>
    </div>

    <?php if ($mensajeExito !== ''): ?>
        <p class="nota-exito"><?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($mensajeError !== ''): ?>
        <p class="nota-error"><?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="formulario-grid" method="post">
        <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars((string) ($fichaProveedor['id_proveedor'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <div class="campo">
            <label for="prov-ruc">RUC</label>
            <input id="prov-ruc" name="ruc" type="text" required value="<?= htmlspecialchars($fichaProveedor['ruc'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="campo">
            <label for="prov-nombre">Nombre</label>
            <input id="prov-nombre" name="nombre" type="text" required value="<?= htmlspecialchars($fichaProveedor['nombre'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="campo">
            <label for="prov-telefono">Telefono</label>
            <input id="prov-telefono" name="telefono" type="text" required value="<?= htmlspecialchars($fichaProveedor['telefono'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="campo campo-amplio">
            <label for="prov-direccion">Direccion</label>
            <textarea id="prov-direccion" name="direccion" required><?= htmlspecialchars($fichaProveedor['direccion'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="fila-acciones campo-amplio">
            <?php if ($idProveedorEdicion === null): ?>
                <button type="submit" name="accion" value="guardar" class="boton-principal">Guardar proveedor</button>
            <?php else: ?>
                <button type="submit" name="accion" value="actualizar" class="boton-principal">Actualizar proveedor</button>
                <button type="submit" name="accion" value="guardar" class="boton-secundario">Guardar como nuevo</button>
            <?php endif; ?>
        </div>
    </form>
</article>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 0;">
        <div>
            <h3 class="subtitulo">Directorio de proveedores</h3>
            <p>Vista unica del modulo, sin repetir informacion en Inicio ni en otros apartados.</p>
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
                <?php if (count($directorioProveedores) === 0): ?>
                    <tr>
                        <td colspan="6">Aun no hay proveedores registrados.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($directorioProveedores as $proveedor): ?>
                    <tr>
                        <td><?= htmlspecialchars($proveedor['ruc'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['direccion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="estado <?= htmlspecialchars($proveedor['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($proveedor['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <div class="acciones-tabla">
                                <form method="post" class="form-accion-tabla">
                                    <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars((string) $proveedor['id_proveedor'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" name="accion" value="cargar" class="boton-fantasma">Editar</button>
                                </form>
                                <form method="post" class="form-accion-tabla">
                                    <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars((string) $proveedor['id_proveedor'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" name="accion" value="eliminar" class="boton-peligro">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</article>
