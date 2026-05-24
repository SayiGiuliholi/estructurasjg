<?php
$mensajeError = $mensajeError ?? null;
$accionFormulario = $accionFormulario ?? '';
$ultimoUsuario = $ultimoUsuario ?? '';
$urlLogoMarca = $urlLogoMarca ?? '';
?>
<section class="panel-formulario">
    <div class="tarjeta-formulario card-login">
        <div class="encabezado-formulario">
            <div class="logo-login" aria-hidden="true">
                <img
                    src="<?= htmlspecialchars($urlLogoMarca, ENT_QUOTES, 'UTF-8') ?>"
                    alt=""
                    class="logo-login-imagen"
                >
            </div>
            <h1>Iniciar sesión</h1>
            <p>Accede a tu sistema de inventario</p>
        </div>

        <?php if ($mensajeError !== null): ?>
            <div class="caja-error">
                <?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($accionFormulario, ENT_QUOTES, 'UTF-8') ?>" method="POST" autocomplete="off">
            <?= csrfCampoOculto() ?>
            <div class="campo">
                <label for="usuario">Usuario</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    value="<?= htmlspecialchars($ultimoUsuario, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <div class="campo-contrasena">
                    <input type="password" id="contrasena" name="contrasena" required>
                    <button
                        type="button"
                        class="boton-toggle-contrasena"
                        id="toggle-contrasena"
                        aria-controls="contrasena"
                        aria-pressed="false"
                    >Mostrar</button>
                </div>
            </div>

            <button type="submit" class="boton-login">Iniciar sesión</button>
        </form>
    </div>
</section>
