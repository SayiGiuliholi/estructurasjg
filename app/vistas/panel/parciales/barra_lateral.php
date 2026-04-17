<aside class="barra-lateral">
    <div class="marca">
        <div class="marca-simbolo">JG</div>
        <h1>Estructuras JG</h1>
        <p>Sistema de inventario con navegacion simple, clara y sin modulos repetidos.</p>
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

    <div class="acciones">
        <a href="<?= htmlspecialchars($urlSalir, ENT_QUOTES, 'UTF-8') ?>" class="enlace-salir">Cerrar sesion</a>
    </div>
</aside>
