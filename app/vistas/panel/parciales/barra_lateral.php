<aside class="barra-lateral">
    <div class="marca">

    <div class="marca-simbolo">
        <div class="marca-logo-contenedor">
            <img src="/Estructurasjg/public/imagenes/marca/logo-login-principal.png" alt="Logo de Estructuras JG" class="imagen-marca-simbolo">
        </div>
    </div>


        <h1>Estructuras JG</h1>
        <p>Manten tu inventario actualizado con mas agilidad.</p>
    </div>

    <nav class="menu" aria-label="Navegacion principal">
        <span class="menu-etiqueta">Modulos</span>
        <?php foreach ($itemsMenu as $clave => $etiqueta): ?>
            <a
                href="<?= htmlspecialchars($urlPanel . '?modulo=' . urlencode($clave), ENT_QUOTES, 'UTF-8') ?>"
                class="enlace-menu <?= $moduloActivo === $clave ? 'activo' : '' ?>"
            >
                <span class="icono-item"><?= strtoupper(substr($etiqueta, 0, 1)) ?></span>
                <span><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
