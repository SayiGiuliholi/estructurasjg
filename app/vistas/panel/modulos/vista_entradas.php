<?php
$resumenIndicadores = $resumenIndicadores ?? [];
$mensajeExito = $mensajeExito ?? '';
$mensajeError = $mensajeError ?? '';
$formularioEntrada = $formularioEntrada ?? [
    'codigo_factura' => '',
    'id_proveedor' => '',
    'id_bodega' => '',
    'total_factura' => '$0',
    'detalles' => [],
    'proveedores' => [],
    'bodegas' => [],
];
$historialEntradas = $historialEntradas ?? [];
$paginacion = $paginacion ?? [
    'paginaActual' => 1,
    'totalPaginas' => 1,
    'totalRegistros' => 0,
    'porPagina' => 20,
    'opcionesPorPagina' => [10, 20, 50],
];
?>
<div class="resumen-kpis">
    <?php foreach ($resumenIndicadores as $indicador): ?>
        <article class="kpi">
            <span><?= htmlspecialchars($indicador['etiqueta'], ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= htmlspecialchars($indicador['valor'], ENT_QUOTES, 'UTF-8') ?></strong>
        </article>
    <?php endforeach; ?>
</div>

<article class="tarjeta bloque formulario-flujo">
    <div class="cabecera-modulo">
        <div>
            <h3 class="subtitulo">Nueva entrada</h3>
        </div>
    </div>

    <?php if ($mensajeExito !== ''): ?>
        <p class="nota-exito"><?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($mensajeError !== ''): ?>
        <p class="nota-error"><?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="flujo-formulario" id="form-entradas" method="post">
        <input type="hidden" name="accion" value="guardar">

        <section class="tarjeta flujo-bloque">
            <div class="cabecera-modulo cabecera-bloque">
                <div>
                    <h3 class="subtitulo">Información de la compra</h3>
                    <p>Ingresa los datos generales de la factura.</p>
                </div>
            </div>

            <div class="formulario-grid formulario-grid-datos">
                <div class="campo">
                    <label for="entrada-codigo-factura">Factura</label>
                    <input
                        id="entrada-codigo-factura"
                        name="codigo_factura"
                        type="text"
                        placeholder="Ej: FAC-2026-001"
                        value="<?= htmlspecialchars($formularioEntrada['codigo_factura'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="campo">
                    <label for="entrada-fecha-registro">Fecha</label>
                    <input id="entrada-fecha-registro" type="date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" readonly>
                </div>

                <div class="campo">
                    <label for="entrada-proveedor">Proveedor</label>
                    <select id="entrada-proveedor" name="id_proveedor" required>
                        <option value="">Selecciona un proveedor</option>
                        <?php foreach ($formularioEntrada['proveedores'] as $proveedor): ?>
                            <option
                                value="<?= htmlspecialchars((string) $proveedor['id'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= $formularioEntrada['id_proveedor'] === (string) $proveedor['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($proveedor['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="entrada-bodega">Bodega</label>
                    <select id="entrada-bodega" name="id_bodega" required>
                        <option value="">Selecciona una bodega</option>
                        <?php foreach ($formularioEntrada['bodegas'] as $bodega): ?>
                            <option
                                value="<?= htmlspecialchars((string) $bodega['id'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= $formularioEntrada['id_bodega'] === (string) $bodega['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($bodega['codigo'] . ' - ' . $bodega['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section class="tarjeta flujo-bloque">
            <div class="cabecera-modulo cabecera-bloque">
                <div>
                    <h3 class="subtitulo">Registra la información del producto</h3>
                    <p>Agrega los productos incluidos en la compra.</p>
                </div>
            </div>

            <div class="tabla-contenedor">
                <table class="tabla" id="tabla-detalles-entrada">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripcion</th>
                            <th>Cantidad</th>
                            <th>Costo unitario</th>
                            <th>Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="entrada-detalles-body">
                        <?php foreach ($formularioEntrada['detalles'] as $detalle): ?>
                            <tr class="detalle-entrada">
                                <td>
                                    <input type="text" name="codigo_producto[]" value="<?= htmlspecialchars($detalle['codigo'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="descripcion_producto[]" value="<?= htmlspecialchars($detalle['descripcion'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </td>
                                <td>
                                    <input type="number" min="1" name="cantidad_producto[]" class="js-cantidad" value="<?= htmlspecialchars($detalle['cantidad'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </td>
                                <td>
                                    <input type="text" inputmode="numeric" name="precio_producto[]" class="js-precio" value="<?= htmlspecialchars($detalle['precio'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </td>
                                <td>
                                    <input type="text" class="js-total-linea" readonly>
                                </td>
                                <td>
                                    <button type="button" class="boton-peligro js-quitar-linea">Quitar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="fila-acciones">
                <button type="button" class="boton-secundario" id="entrada-agregar-linea">Añadir producto</button>
            </div>
        </section>

        <section class="tarjeta flujo-bloque">
            <div class="cabecera-modulo cabecera-bloque">
                <div>
                    <h3 class="subtitulo">Resumen de la compra</h3>
                    <p>Revisa la información antes de guardar.</p>
                </div>
            </div>

            <div class="campo campo-total-factura campo-total-factura-compacta">
                <label for="entrada-total-factura">Total de la compra</label>
                <input id="entrada-total-factura" type="text" value="<?= htmlspecialchars($formularioEntrada['total_factura'], ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>

            <div class="fila-acciones">
                <button type="submit" class="boton-principal">Registrar entrada</button>
                <button type="reset" class="boton-fantasma">Cancelar</button>
            </div>
        </section>
    </form>
</article>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 0;">
        <div>
            <h3 class="subtitulo">Historial de compras</h3>
          
        </div>
    </div>

    <?php
    $proveedoresHistorial = [];
    foreach ($historialEntradas as $entradaHistorial) {
        $nombreProveedor = trim((string) ($entradaHistorial['proveedor'] ?? ''));
        if ($nombreProveedor !== '') {
            $proveedoresHistorial[$nombreProveedor] = $nombreProveedor;
        }
    }
    ksort($proveedoresHistorial);
    ?>

    <form class="historial-filtros" id="entrada-filtros-form" onsubmit="return false;">
        <div class="campo campo-busqueda">
            <label for="entrada-filtro-buscar">Buscar compra</label>
            <input id="entrada-filtro-buscar" type="text" placeholder="Buscar factura, codigo o producto...">
        </div>

        <div class="campo">
            <label for="entrada-filtro-proveedor">Proveedor</label>
            <select id="entrada-filtro-proveedor">
                <option value="">Todos</option>
                <?php foreach ($proveedoresHistorial as $proveedorHistorial): ?>
                    <option value="<?= htmlspecialchars($proveedorHistorial, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($proveedorHistorial, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="entrada-filtro-fecha">Fecha</label>
            <input id="entrada-filtro-fecha" type="date">
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla tabla-historial-entradas" id="tabla-historial-entradas">
            <thead>
                <tr>
                    <th>Compra</th>
                    <th>Codigo</th>
                    <th>Descripcion</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Proveedor</th>
                    <th>Bodega</th>
                    <th>Total</th>
                    <th>Fecha y hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($historialEntradas) === 0): ?>
                    <tr>
                        <td colspan="9">Aun no hay entradas registradas.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($historialEntradas as $entrada): ?>
                    <tr
                        class="js-historial-entrada"
                        data-proveedor="<?= htmlspecialchars(strtolower((string) $entrada['proveedor']), ENT_QUOTES, 'UTF-8') ?>"
                        data-fecha="<?= htmlspecialchars((string) ($entrada['fecha_registro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td><?= htmlspecialchars($entrada['codigo_compra'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['codigo_producto'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['precio'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['bodega'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($entrada['total'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="fecha-hora-registro">
                                <strong><?= htmlspecialchars((string) ($entrada['hora_registro'] ?? '--:--'), ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars((string) ($entrada['fecha_registro'] ?? '--/--/----'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr id="entrada-historial-vacio" hidden>
                    <td colspan="9">No hay resultados con los filtros aplicados.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="paginacion-contenedor">
        <form method="get" class="paginacion-form">
            <input type="hidden" name="modulo" value="entradas">
            <input type="hidden" name="pagina" value="1">
            <label for="entradas-por-pagina">Registros por pagina</label>
            <select id="entradas-por-pagina" name="por_pagina" onchange="this.form.submit()">
                <?php foreach ($paginacion['opcionesPorPagina'] as $opcion): ?>
                    <option value="<?= htmlspecialchars((string) $opcion, ENT_QUOTES, 'UTF-8') ?>" <?= (int) $paginacion['porPagina'] === (int) $opcion ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $opcion, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="paginacion-resumen">
                <?= htmlspecialchars((string) $paginacion['totalRegistros'], ENT_QUOTES, 'UTF-8') ?> registros
            </span>
        </form>

        <?php if ((int) $paginacion['totalPaginas'] > 1): ?>
            <div class="paginacion-botones">
                <?php
                $paginaActual = (int) $paginacion['paginaActual'];
                $totalPaginas = (int) $paginacion['totalPaginas'];
                $porPaginaActual = (int) $paginacion['porPagina'];
                ?>
                <a class="boton-fantasma <?= $paginaActual <= 1 ? 'deshabilitado' : '' ?>" href="?<?= htmlspecialchars(http_build_query(['modulo' => 'entradas', 'pagina' => max(1, $paginaActual - 1), 'por_pagina' => $porPaginaActual]), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
                <span class="paginacion-texto">Pagina <?= htmlspecialchars((string) $paginaActual, ENT_QUOTES, 'UTF-8') ?> de <?= htmlspecialchars((string) $totalPaginas, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="boton-fantasma <?= $paginaActual >= $totalPaginas ? 'deshabilitado' : '' ?>" href="?<?= htmlspecialchars(http_build_query(['modulo' => 'entradas', 'pagina' => min($totalPaginas, $paginaActual + 1), 'por_pagina' => $porPaginaActual]), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
            </div>
        <?php endif; ?>
    </div>
</article>
