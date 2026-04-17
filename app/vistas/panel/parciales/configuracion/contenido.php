<div class="ajustes-grid">
    <section class="ajuste-tarjeta">
        <div class="ajuste-cabecera">
            <h3>Gestion de usuarios</h3>
            <p>Administra accesos y comportamiento de las cuentas desde controles visibles.</p>
        </div>

        <div class="control-linea">
            <div>
                <strong>Permitir nuevos usuarios</strong>
                <span>Habilita el registro interno de cuentas desde administracion.</span>
            </div>
            <label class="switch">
                <input type="checkbox" <?= $configuracionUsuarios['permitirNuevosUsuarios'] ? 'checked' : '' ?>>
                <span class="switch-slider"></span>
            </label>
        </div>

        <div class="control-linea">
            <div>
                <strong>Sesion unica por usuario</strong>
                <span>Evita multiples accesos simultaneos con la misma cuenta.</span>
            </div>
            <label class="switch">
                <input type="checkbox" <?= $configuracionUsuarios['sesionUnica'] ? 'checked' : '' ?>>
                <span class="switch-slider"></span>
            </label>
        </div>

        <div class="campo">
            <label for="config-usuario-defecto">Rol por defecto</label>
            <select id="config-usuario-defecto">
                <?php foreach ($configuracionUsuarios['rolPorDefecto'] as $rol): ?>
                    <option><?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="fila-acciones">
            <button type="button" class="boton-principal">Guardar usuarios</button>
            <button type="button" class="boton-fantasma">Crear usuario</button>
        </div>
    </section>

    <section class="ajuste-tarjeta">
        <div class="ajuste-cabecera">
            <h3>Roles y permisos</h3>
            <p>Los roles permanecen unicamente aqui, como parte del area de configuracion.</p>
        </div>

        <?php foreach ($roles as $rol): ?>
            <div class="control-linea">
                <div>
                    <strong><?= htmlspecialchars($rol['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars($rol['detalle'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <button type="button" class="chip-opcion<?= $rol['activo'] ? ' activo' : '' ?>">
                    <?= htmlspecialchars($rol['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
                </button>
            </div>
        <?php endforeach; ?>

        <div class="campo">
            <label for="config-nivel-seguridad">Nivel de control</label>
            <select id="config-nivel-seguridad">
                <?php foreach ($nivelesControl as $nivel): ?>
                    <option><?= htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </section>

    <section class="ajuste-tarjeta">
        <div class="ajuste-cabecera">
            <h3>Apariencia</h3>
            <p>Ajustes visuales del panel para tema, tamano y detalles de interfaz.</p>
        </div>

        <div>
            <strong style="display:block; margin-bottom:10px;">Tema visual</strong>
            <div class="selector-tema">
                <?php foreach ($temasVisuales as $tema): ?>
                    <button type="button" class="tema-opcion<?= $tema['activo'] ? ' activo' : '' ?>">
                        <strong><?= htmlspecialchars($tema['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($tema['detalle'], ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <strong style="display:block; margin-bottom:10px;">Tamano de letra</strong>
            <div class="tamano-opciones">
                <?php foreach ($tamanosLetra as $tamano): ?>
                    <button type="button" class="chip-opcion<?= $tamano['activo'] ? ' activo' : '' ?>">
                        <?= htmlspecialchars($tamano['titulo'], ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php foreach ($ajustesInterfaz as $ajuste): ?>
            <div class="control-linea">
                <div>
                    <strong><?= htmlspecialchars($ajuste['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars($ajuste['detalle'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <label class="switch">
                    <input type="checkbox" <?= $ajuste['activo'] ? 'checked' : '' ?>>
                    <span class="switch-slider"></span>
                </label>
            </div>
        <?php endforeach; ?>

        <div class="fila-acciones">
            <button type="button" class="boton-principal">Aplicar apariencia</button>
            <button type="button" class="boton-secundario">Restablecer</button>
        </div>
    </section>

    <section class="ajuste-tarjeta">
        <div class="ajuste-cabecera">
            <h3>Acceso a configuracion</h3>
            <p>Este espacio reemplaza el enlace lateral y se consulta solo desde el engranaje superior.</p>
        </div>
        <div class="nota-modulo"><?= htmlspecialchars($notaConfiguracion, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="control-linea">
            <div>
                <strong>Mostrar ayuda contextual</strong>
                <span>Presenta descripciones breves en pantallas administrativas.</span>
            </div>
            <label class="switch">
                <input type="checkbox" <?= $ayudaContextual ? 'checked' : '' ?>>
                <span class="switch-slider"></span>
            </label>
        </div>
    </section>
</div>
