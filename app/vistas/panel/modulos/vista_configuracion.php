<?php

declare(strict_types=1);
?>
<div class="contenido-personalizado">
    <?php if ($mensajeExito !== ''): ?>
        <p class="nota-exito"><?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($mensajeError !== ''): ?>
        <p class="nota-error"><?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <article class="tarjeta bloque">
        <div class="cabecera-modulo">
            <div>
                <h3 class="subtitulo">Crear y modificar usuarios</h3>
                <p>Desde aqui administras cuentas de empleados, rol asignado y estado del usuario.</p>
            </div>
        </div>

        <form method="post" class="formulario-grid">
            <input type="hidden" name="accion" value="guardar_usuario">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string) $formularioUsuario['id_usuario'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="campo">
                <label for="cfg-nombre">Nombre completo</label>
                <input
                    id="cfg-nombre"
                    name="nombre"
                    type="text"
                    required
                    value="<?= htmlspecialchars((string) $formularioUsuario['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="campo">
                <label for="cfg-usuario">Usuario</label>
                <input
                    id="cfg-usuario"
                    name="usuario"
                    type="text"
                    required
                    value="<?= htmlspecialchars((string) $formularioUsuario['usuario'], ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="campo">
                <label for="cfg-contrasena">
                    <?= (int) $formularioUsuario['id_usuario'] > 0 ? 'Contrasena (opcional para cambiar)' : 'Contrasena' ?>
                </label>
                <input id="cfg-contrasena" name="contrasena" type="password" <?= (int) $formularioUsuario['id_usuario'] > 0 ? '' : 'required' ?>>
            </div>

            <div class="campo">
                <label for="cfg-rol">Rol</label>
                <select id="cfg-rol" name="id_rol" required>
                    <option value="">Selecciona un rol</option>
                    <?php foreach ($rolesOpciones as $rol): ?>
                        <option
                            value="<?= htmlspecialchars((string) $rol['id'], ENT_QUOTES, 'UTF-8') ?>"
                            <?= (string) $formularioUsuario['id_rol'] === (string) $rol['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars((string) $rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="control-linea campo-amplio">
                <div>
                    <strong>Usuario activo</strong>
                    <span>Si lo desactivas, no podra iniciar sesion.</span>
                </div>
                <label class="switch">
                    <input type="checkbox" name="estado" <?= (int) $formularioUsuario['estado'] === 1 ? 'checked' : '' ?>>
                    <span class="switch-slider"></span>
                </label>
            </div>

            <div class="fila-acciones campo-amplio">
                <button type="submit" class="boton-principal">
                    <?= (int) $formularioUsuario['id_usuario'] > 0 ? 'Actualizar usuario' : 'Crear usuario' ?>
                </button>
                <a href="?modulo=configuracion" class="boton-fantasma">Limpiar</a>
            </div>
        </form>
    </article>

    <article class="tarjeta tarjeta-tabla">
        <div class="cabecera-modulo" style="padding: 22px 22px 0;">
            <div>
                <h3 class="subtitulo">Usuarios del sistema</h3>
                <p>Listado de cuentas registradas para operar el sistema.</p>
            </div>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Ultimo acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) === 0): ?>
                        <tr>
                            <td colspan="7">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $usuario['id_usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['rol'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['ultimo_acceso'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a
                                    class="boton-fantasma"
                                    href="?<?= htmlspecialchars(http_build_query(['modulo' => 'configuracion', 'editar_usuario' => (int) $usuario['id_usuario']]), ENT_QUOTES, 'UTF-8') ?>"
                                >Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="tarjeta bloque">
        <div class="cabecera-modulo">
            <div>
                <h3 class="subtitulo">Permisos por rol</h3>
                <p>Activa o desactiva permisos para empleados segun el rol.</p>
            </div>
        </div>

        <?php foreach ($rolesConPermisos as $rol): ?>
            <form method="post" class="tarjeta" style="border-radius: 12px; box-shadow: none;">
                <input type="hidden" name="accion" value="guardar_permisos_rol">
                <input type="hidden" name="id_rol" value="<?= htmlspecialchars((string) $rol['id_rol'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="cabecera-modulo">
                    <div>
                        <h3 class="subtitulo" style="margin-bottom:6px;"><?= htmlspecialchars((string) $rol['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Define que puede hacer este rol en el sistema.</p>
                    </div>
                    <button type="submit" class="boton-principal">Guardar permisos</button>
                </div>

                <div class="formulario-grid" style="margin-top: 12px;">
                    <label class="control-linea">
                        <span>Registrar productos</span>
                        <input type="checkbox" name="registrar_productos" <?= $rol['permisos']['registrar_productos'] ? 'checked' : '' ?>>
                    </label>
                    <label class="control-linea">
                        <span>Modificar productos</span>
                        <input type="checkbox" name="modificar_productos" <?= $rol['permisos']['modificar_productos'] ? 'checked' : '' ?>>
                    </label>
                    <label class="control-linea">
                        <span>Registrar movimientos</span>
                        <input type="checkbox" name="registrar_movimientos" <?= $rol['permisos']['registrar_movimientos'] ? 'checked' : '' ?>>
                    </label>
                    <label class="control-linea">
                        <span>Consultar movimientos</span>
                        <input type="checkbox" name="consultar_movimientos" <?= $rol['permisos']['consultar_movimientos'] ? 'checked' : '' ?>>
                    </label>
                    <label class="control-linea">
                        <span>Gestionar roles</span>
                        <input type="checkbox" name="gestionar_roles" <?= $rol['permisos']['gestionar_roles'] ? 'checked' : '' ?>>
                    </label>
                    <label class="control-linea">
                        <span>Configuracion</span>
                        <input type="checkbox" name="configuracion" <?= $rol['permisos']['configuracion'] ? 'checked' : '' ?>>
                    </label>
                </div>
            </form>
        <?php endforeach; ?>
    </article>
</div>
