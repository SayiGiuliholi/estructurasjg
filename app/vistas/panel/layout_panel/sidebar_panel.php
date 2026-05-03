<?php
$itemsMenu = $itemsMenu ?? [];
$moduloActivo = $moduloActivo ?? '';
$urlPanel = $urlPanel ?? '';
$puedeVerConfiguracion = $puedeVerConfiguracion ?? false;
$resaltarConfiguracion = $resaltarConfiguracion ?? false;
$urlConfiguracion = $urlConfiguracion ?? '';
$urlSalir = $urlSalir ?? '';
?>
<aside class="barra-lateral sidebar-minimal">
    <div class="sidebar-superior">
        <div class="sidebar-header">
            <div class="logo-minimal" aria-hidden="true">
                <img
                    src="/Estructurasjg/public/imagenes/marca/logo-login-principal.png"
                    alt="Logo de Estructuras JG"
                    class="logo-minimal-imagen"
                >
            </div>
            <h3 class="sidebar-brand-title">Estructuras JG</h3>
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
                    <span class="menu-item menu-item-activo" aria-current="page">
                        <span class="menu-icono" aria-hidden="true"><i data-lucide="<?= htmlspecialchars($iconosMenu[$clave] ?? 'dot', ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        <span><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                <?php else: ?>
                    <button
                        type="button"
                        class="menu-item js-nav-sidebar"
                        data-url="<?= htmlspecialchars($urlPanel . '?modulo=' . urlencode($clave), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <span class="menu-icono" aria-hidden="true"><i data-lucide="<?= htmlspecialchars($iconosMenu[$clave] ?? 'dot', ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        <span><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="sidebar-footer">
        <?php if ($puedeVerConfiguracion): ?>
            <?php if ($resaltarConfiguracion): ?>
                <span class="menu-item menu-item-activo" aria-current="page">
                    <span class="menu-icono" aria-hidden="true"><i data-lucide="settings"></i></span>
                    <span>Configuración</span>
                </span>
            <?php else: ?>
                <button
                    type="button"
                    class="menu-item js-nav-sidebar"
                    data-url="<?= htmlspecialchars($urlConfiguracion, ENT_QUOTES, 'UTF-8') ?>"
                >
                    <span class="menu-icono" aria-hidden="true"><i data-lucide="settings"></i></span>
                    <span>Configuración</span>
                </button>
            <?php endif; ?>
        <?php endif; ?>

        <button
            type="button"
            class="menu-item menu-item-logout js-nav-sidebar"
            data-url="<?= htmlspecialchars($urlSalir, ENT_QUOTES, 'UTF-8') ?>"
        >
            <span class="menu-icono" aria-hidden="true"><i data-lucide="log-out"></i></span>
            <span>Salir</span>
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
