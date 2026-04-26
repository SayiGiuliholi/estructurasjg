<section class="panel-formulario">
    <div class="tarjeta-formulario">
        <div class="encabezado-formulario">
            <span class="etiqueta-formulario">Acceso seguro</span>
            <h2>Bienvenido de nuevo</h2>
            <p>Ingresa y gestiona tu inventario de manera rápida, segura y eficiente.</p>
        </div>

        <?php if ($mensajeError !== null): ?>
            <div class="caja-error">
                <?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($accionFormulario, ENT_QUOTES, 'UTF-8') ?>" method="POST" autocomplete="off">
            <div class="campo">
                <label for="usuario">Usuario</label>
                <span class="ayuda-etiqueta">Nombre de acceso registrado</span>
                <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($ultimoUsuario, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <span class="ayuda-etiqueta">Protegida con validacion segura</span>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>

            <button type="submit" class="boton-login">Acceder ahora</button>
        </form>

        <div class="pie-formulario">
            <p class="texto-ayuda">Ingresa con tu cuenta y empieza a controlar tu inventario en segundos.</p>
        </div>
    </div>
</section>
