<aside class="barra-lateral">
    <div class="marca">
        <div class="marca-simbolo">
            <div class="marca-logo-contenedor">
                <img src="/Estructurasjg/public/imagenes/marca/logo-login-principal.png" alt="Logo de Estructuras JG" class="imagen-marca-simbolo">
            </div>
        </div>

        <h1>Estructuras JG</h1>
        <p>Sistema de inventario</p>
    </div>

    <nav class="menu" aria-label="Navegacion principal">
        <?php
        $iconosMenu = [
            'entradas' => 'inbox',
            'productos' => 'box',
            'proveedores' => 'truck',
            'salidas' => 'arrow-up-right',
        ];
        ?>
        <?php foreach ($itemsMenu as $clave => $etiqueta): ?>
            <?php if ($moduloActivo === $clave): ?>
                <span class="enlace-menu activo enlace-menu-actual" aria-current="page">
                    <span class="icono-item" aria-hidden="true">
                        <i data-lucide="<?= htmlspecialchars($iconosMenu[$clave] ?? 'dot', ENT_QUOTES, 'UTF-8') ?>"></i>
                    </span>
                    <span><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            <?php else: ?>
                <button
                    type="button"
                    class="enlace-menu enlace-menu-boton js-nav-sidebar"
                    data-url="<?= htmlspecialchars($urlPanel . '?modulo=' . urlencode($clave), ENT_QUOTES, 'UTF-8') ?>"
                >
                    <span class="icono-item" aria-hidden="true">
                        <i data-lucide="<?= htmlspecialchars($iconosMenu[$clave] ?? 'dot', ENT_QUOTES, 'UTF-8') ?>"></i>
                    </span>
                    <span><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></span>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="menu-inferior" aria-label="Acciones de sesion">
        <?php if ($puedeVerConfiguracion): ?>
            <button
                type="button"
                class="enlace-menu enlace-menu-boton enlace-menu-inferior <?= $resaltarConfiguracion ? 'activo' : '' ?> js-nav-sidebar"
                data-url="<?= htmlspecialchars($urlConfiguracion, ENT_QUOTES, 'UTF-8') ?>"
            >
                <span class="icono-item" aria-hidden="true"><i data-lucide="settings"></i></span>
                <span>Configuracion</span>
            </button>
        <?php endif; ?>

        <button
            type="button"
            class="enlace-menu enlace-menu-boton enlace-menu-inferior enlace-menu-salir js-nav-sidebar"
            data-url="<?= htmlspecialchars($urlSalir, ENT_QUOTES, 'UTF-8') ?>"
        >
            <span class="icono-item" aria-hidden="true"><i data-lucide="log-out"></i></span>
            <span>Cerrar sesion</span>
        </button>
    </div>

    <script>
        document.querySelectorAll('.js-nav-sidebar').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var url = boton.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            });
        });
    </script>
</aside>
