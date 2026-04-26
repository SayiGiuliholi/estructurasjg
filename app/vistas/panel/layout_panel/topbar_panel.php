<div class="topbar">
    <div class="topbar-meta">
        <span>Sesion activa</span>
        <span><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($usuarioAcceso, ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="acciones-superiores">
        <span class="insignia-rol"><?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?></span>
        <a
            href="<?= htmlspecialchars($urlSalir, ENT_QUOTES, 'UTF-8') ?>"
            class="boton-salir"
            title="Cerrar sesion"
            aria-label="Cerrar sesion"
        >Salir</a>
        <?php if ($puedeVerConfiguracion): ?>
            <a
                href="<?= htmlspecialchars($urlConfiguracion, ENT_QUOTES, 'UTF-8') ?>"
                class="boton-config <?= $resaltarConfiguracion ? 'activo' : '' ?>"
                title="Configuracion"
                aria-label="Configuracion"
            >&#9881;</a>
        <?php endif; ?>
    </div>
</div>
