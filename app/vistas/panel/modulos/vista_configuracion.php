<?php

declare(strict_types=1);

$mensajeExito = $mensajeExito ?? '';
$mensajeError = $mensajeError ?? '';
$formularioUsuario = $formularioUsuario ?? ['id_usuario'=>0,'nombre'=>'','usuario'=>'','id_rol'=>'','estado'=>1];
$rolesOpciones = $rolesOpciones ?? [];
$usuarios = $usuarios ?? [];
$rolesConPermisos = $rolesConPermisos ?? [];
$esSuperadminVista = esSuperadminSesion($_SESSION['autenticacion'] ?? []);
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
                    <?= (int) $formularioUsuario['id_usuario'] > 0 ? 'Contraseña (opcional para cambiar)' : 'Contraseña' ?>
                </label>
                <input id="cfg-contrasena" name="contrasena" type="password" <?= (int) $formularioUsuario['id_usuario'] > 0 ? '' : 'required' ?>>
            </div>

            <div class="campo">
                <label for="cfg-rol">Rol</label>
                <select id="cfg-rol" name="id_rol" required>
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

            <div class="control-linea campo-amplio control-linea-estado">
                <div>
                    <strong>Usuario activo</strong>
                    <span>ON: puede iniciar sesion. OFF: usuario bloqueado.</span>
                    <div class="control-estado-usuario">
                        <span class="etiqueta-estado-usuario <?= (int) $formularioUsuario['estado'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $formularioUsuario['estado'] === 1 ? 'Activo' : 'Deshabilitado' ?>
                        </span>
                        <label class="switch switch-destacado" title="Cambiar estado de usuario">
                            <input type="checkbox" name="estado" <?= (int) $formularioUsuario['estado'] === 1 ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="fila-acciones campo-amplio">
                <button type="submit" class="boton-principal">
                    <?= (int) $formularioUsuario['id_usuario'] > 0 ? 'Actualizar usuario' : 'Crear usuario' ?>
                </button>
                <button type="reset" class="boton-fantasma">Limpiar</button>
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
                        <?php
                        $esUsuarioAdministrador = strtolower((string) ($usuario['rol'] ?? '')) === 'administrador';
                        $puedeEditarUsuario = $esSuperadminVista || !$esUsuarioAdministrador;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $usuario['id_usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['rol'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $usuario['ultimo_acceso'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($puedeEditarUsuario): ?>
                                    <a
                                        class="boton-fantasma"
                                        href="?<?= htmlspecialchars(http_build_query(['modulo' => 'configuracion', 'editar_usuario' => (int) $usuario['id_usuario']]), ENT_QUOTES, 'UTF-8') ?>"
                                    >Editar</a>
                                <?php else: ?>
                                    <span class="boton-fantasma" style="opacity:.6; cursor:not-allowed;">Protegido</span>
                                <?php endif; ?>
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

        <?php
        $ayudaPermisos = [
            'registrar_productos' => 'Crear productos nuevos y cargarlos al inventario.',
            'modificar_productos' => 'Editar datos, precio o estado de productos.',
            'registrar_movimientos' => 'Registrar entradas y salidas de inventario.',
            'consultar_movimientos' => 'Ver historial de entradas, salidas y productos.',
            'gestionar_roles' => 'Cambiar permisos por rol.',
            'configuracion' => 'Entrar al modulo de configuracion.',
        ];
        ?>
        <?php foreach ($rolesConPermisos as $rol): ?>
            <?php
            $nombreRolNormalizado = strtolower((string) ($rol['nombre'] ?? ''));
            $esRolAdministrador = $nombreRolNormalizado === 'administrador';
            $esRolEmpleado = $nombreRolNormalizado === 'empleado';
            $puedeEditarPermisosRol = $esSuperadminVista || !$esRolAdministrador;
            ?>
            <form method="post" class="tarjeta" style="border-radius: 12px; box-shadow: none;">
                <input type="hidden" name="accion" value="guardar_permisos_rol">
                <input type="hidden" name="id_rol" value="<?= htmlspecialchars((string) $rol['id_rol'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="cabecera-modulo">
                    <div>
                        <h3 class="subtitulo" style="margin-bottom:6px;"><?= htmlspecialchars((string) $rol['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Define que puede hacer este rol en el sistema.</p>
                    </div>
                    <?php if ($puedeEditarPermisosRol): ?>
                        <button type="submit" class="boton-principal">Guardar permisos</button>
                    <?php else: ?>
                        <span class="boton-fantasma" style="opacity:.7; cursor:not-allowed;">Protegido</span>
                    <?php endif; ?>
                </div>

                <p class="nota-ayuda-permisos">Marca solo lo que este rol puede hacer.</p>
                <div class="permisos-grid" style="margin-top: 12px;">
                    <label class="permiso-item">
                        <input type="checkbox" name="registrar_productos" <?= $rol['permisos']['registrar_productos'] ? 'checked' : '' ?> <?= $puedeEditarPermisosRol ? '' : 'disabled' ?>>
                        <span class="permiso-texto">
                            <strong>Registrar productos</strong>
                            <small><?= htmlspecialchars($ayudaPermisos['registrar_productos'], ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                    </label>
                    <label class="permiso-item">
                        <input type="checkbox" name="modificar_productos" <?= $rol['permisos']['modificar_productos'] ? 'checked' : '' ?> <?= $puedeEditarPermisosRol ? '' : 'disabled' ?>>
                        <span class="permiso-texto">
                            <strong>Modificar productos</strong>
                            <small><?= htmlspecialchars($ayudaPermisos['modificar_productos'], ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                    </label>
                    <label class="permiso-item">
                        <input type="checkbox" name="registrar_movimientos" <?= $rol['permisos']['registrar_movimientos'] ? 'checked' : '' ?> <?= $puedeEditarPermisosRol ? '' : 'disabled' ?>>
                        <span class="permiso-texto">
                            <strong>Registrar movimientos</strong>
                            <small><?= htmlspecialchars($ayudaPermisos['registrar_movimientos'], ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                    </label>
                    <label class="permiso-item">
                        <input type="checkbox" name="consultar_movimientos" <?= $rol['permisos']['consultar_movimientos'] ? 'checked' : '' ?> <?= $puedeEditarPermisosRol ? '' : 'disabled' ?>>
                        <span class="permiso-texto">
                            <strong>Consultar movimientos</strong>
                            <small><?= htmlspecialchars($ayudaPermisos['consultar_movimientos'], ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                    </label>
                    <?php if (!$esRolEmpleado): ?>
                        <label class="permiso-item">
                            <input type="checkbox" name="gestionar_roles" <?= $rol['permisos']['gestionar_roles'] ? 'checked' : '' ?> <?= $puedeEditarPermisosRol ? '' : 'disabled' ?>>
                            <span class="permiso-texto">
                                <strong>Gestionar roles</strong>
                                <small><?= htmlspecialchars($ayudaPermisos['gestionar_roles'], ENT_QUOTES, 'UTF-8') ?></small>
                            </span>
                        </label>
                        <label class="permiso-item">
                            <input type="checkbox" name="configuracion" <?= $rol['permisos']['configuracion'] ? 'checked' : '' ?> <?= $puedeEditarPermisosRol ? '' : 'disabled' ?>>
                            <span class="permiso-texto">
                                <strong>Configuracion</strong>
                                <small><?= htmlspecialchars($ayudaPermisos['configuracion'], ENT_QUOTES, 'UTF-8') ?></small>
                            </span>
                        </label>
                    <?php endif; ?>
                </div>
            </form>
        <?php endforeach; ?>
    </article>
</div>

