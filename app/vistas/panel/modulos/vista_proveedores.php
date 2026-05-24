<?php
declare(strict_types=1);

$puedeGestionProveedores = $puedeGestionProveedores ?? false;
$mensajeExito = $mensajeExito ?? '';
$mensajeError = $mensajeError ?? '';
$idProveedorEdicion = $idProveedorEdicion ?? null;
$fichaProveedor = $fichaProveedor ?? [
    'id_proveedor' => null,
    'ruc' => '',
    'nombre' => '',
    'telefono' => '',
    'direccion' => '',
];
$directorioProveedores = $directorioProveedores ?? [];
?>
<?php if (!isset($puedeGestionProveedores) || $puedeGestionProveedores): ?>
<article class="tarjeta bloque">
    <div class="cabecera-modulo">
        <div>
            <h3 class="subtitulo">Nuevo proveedor</h3>
        </div>
    </div>

    <?php if ($mensajeExito !== ''): ?>
        <p class="nota-exito"><?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($mensajeError !== ''): ?>
        <p class="nota-error"><?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="formulario-grid" method="post">
        <?= csrfCampoOculto() ?>
        <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars((string) ($fichaProveedor['id_proveedor'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <div class="campo">
            <label for="prov-ruc">NIT</label>
            <input id="prov-ruc" name="ruc" type="text" inputmode="numeric" pattern="[0-9]+" required value="<?= htmlspecialchars($fichaProveedor['ruc'], ENT_QUOTES, 'UTF-8') ?>" oninput="this.value=this.value.replace(/\D/g,'')">
        </div>
        <div class="campo">
            <label for="prov-nombre">Nombre</label>
            <input id="prov-nombre" name="nombre" type="text" required value="<?= htmlspecialchars($fichaProveedor['nombre'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="campo">
            <label for="prov-telefono">Telefono</label>
            <input id="prov-telefono" name="telefono" type="text" inputmode="numeric" pattern="[0-9]+" required value="<?= htmlspecialchars($fichaProveedor['telefono'], ENT_QUOTES, 'UTF-8') ?>" oninput="this.value=this.value.replace(/\D/g,'')">
        </div>
        <div class="campo campo-amplio">
            <label for="prov-direccion">Direccion</label>
            <textarea id="prov-direccion" name="direccion" required><?= htmlspecialchars($fichaProveedor['direccion'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="fila-acciones campo-amplio">
            <?php if ($idProveedorEdicion === null): ?>
                <button type="submit" name="accion" value="guardar" class="boton-principal">Registrar proveedor</button>
            <?php else: ?>
                <button type="submit" name="accion" value="actualizar" class="boton-principal">Actualizar proveedor</button>
                <button type="submit" name="accion" value="guardar" class="boton-secundario">Guardar como nuevo</button>
            <?php endif; ?>
        </div>
    </form>
</article>
<?php else: ?>
<article class="tarjeta bloque">
    <p class="nota-modulo">Modo solo lectura: puedes consultar proveedores, pero no crearlos ni editarlos.</p>
</article>
<?php endif; ?>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 14px;">
        <div>
            <h3 class="subtitulo">Listado de proveedores</h3>
        </div>
    </div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>NIT</th>
                    <th>Proveedor</th>
                    <th>Telefono</th>
                    <th>Direccion</th>
                    <th>Estado</th>
                    <?php if (!isset($puedeGestionProveedores) || $puedeGestionProveedores): ?>
                        <th>Accion</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($directorioProveedores) === 0): ?>
                    <tr>
                        <td colspan="<?= (!isset($puedeGestionProveedores) || $puedeGestionProveedores) ? '6' : '5' ?>">Aún no hay proveedores registrados.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($directorioProveedores as $proveedor): ?>
                    <tr>
                        <td><?= htmlspecialchars($proveedor['ruc'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($proveedor['direccion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="estado <?= htmlspecialchars($proveedor['tipoEstado'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($proveedor['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <?php if (!isset($puedeGestionProveedores) || $puedeGestionProveedores): ?>
                            <td>
                                <div class="acciones-tabla">
                                    <form method="post" class="form-accion-tabla">
                                        <?= csrfCampoOculto() ?>
                                        <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars((string) $proveedor['id_proveedor'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" name="accion" value="cargar" class="boton-fantasma">Editar</button>
                                    </form>
                                    <form method="post" class="form-accion-tabla">
                                        <?= csrfCampoOculto() ?>
                                        <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars((string) $proveedor['id_proveedor'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" name="accion" value="<?= ($proveedor['activo'] ?? true) ? 'desactivar' : 'activar' ?>" class="<?= ($proveedor['activo'] ?? true) ? 'boton-peligro' : 'boton-principal' ?>">
                                            <?= ($proveedor['activo'] ?? true) ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</article>
