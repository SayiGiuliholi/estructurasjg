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
            <h3 class="subtitulo">Datos de la salida</h3>
            <p>Registra facturas de salida y descuenta stock por bodega.</p>
        </div>
        <button type="submit" form="form-salidas" class="boton-principal">Guardar salida</button>
    </div>

    <?php if ($mensajeExito !== ''): ?>
        <p class="nota-exito"><?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($mensajeError !== ''): ?>
        <p class="nota-error"><?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="flujo-formulario" id="form-salidas" method="post">
        <input type="hidden" name="accion" value="registrar">

        <section class="tarjeta flujo-bloque">
            <div class="cabecera-modulo cabecera-bloque">
                <div>
                    <h3 class="subtitulo">Datos de la salida</h3>
                    <p>Completa la cabecera de la factura antes de cargar productos.</p>
                </div>
            </div>

            <div class="formulario-grid formulario-grid-datos">
                <div class="campo">
                    <label for="salida-codigo-factura">Factura</label>
                    <input
                        id="salida-codigo-factura"
                        name="codigo_factura"
                        type="text"
                        placeholder="Ej: VTA-2026-001"
                        value="<?= htmlspecialchars($formularioSalida['codigo_factura'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="campo">
                    <label for="salida-fecha-registro">Fecha</label>
                    <input id="salida-fecha-registro" type="date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" readonly>
                </div>

                <div class="campo">
                    <label for="salida-bodega">Bodega</label>
                    <select id="salida-bodega" name="id_bodega" required>
                        <option value="">Selecciona una bodega</option>
                        <?php foreach ($formularioSalida['bodegas'] as $bodega): ?>
                            <option
                                value="<?= htmlspecialchars((string) $bodega['id'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= $formularioSalida['id_bodega'] === (string) $bodega['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($bodega['codigo'] . ' - ' . $bodega['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="salida-motivo">Motivo de salida</label>
                    <select id="salida-motivo" name="motivo_salida" required>
                        <option value="normal" <?= $formularioSalida['motivo_salida'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="devolucion" <?= $formularioSalida['motivo_salida'] === 'devolucion' ? 'selected' : '' ?>>Devolucion</option>
                        <option value="fallo" <?= $formularioSalida['motivo_salida'] === 'fallo' ? 'selected' : '' ?>>Fallo</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="tarjeta flujo-bloque">
            <div class="cabecera-modulo cabecera-bloque">
                <div>
                    <h3 class="subtitulo">Productos de la factura</h3>
                    <p>Consulta stock por bodega y valida cantidades por linea.</p>
                </div>
                <button type="button" class="boton-secundario" id="salida-agregar-linea">Agregar producto</button>
            </div>

            <div class="tabla-contenedor">
                <table class="tabla" id="tabla-detalles-salida">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Stock bodega</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total linea</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="salida-detalles-body">
                        <?php foreach ($formularioSalida['detalles'] as $detalle): ?>
                            <tr class="detalle-salida">
                                <td>
                                    <input type="text" name="codigo_producto[]" class="js-salida-codigo" autocomplete="off" value="<?= htmlspecialchars($detalle['codigo'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="descripcion_producto[]" class="js-salida-descripcion" value="<?= htmlspecialchars($detalle['descripcion'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                                </td>
                                <td>
                                    <input type="number" name="stock_producto[]" class="js-salida-stock" value="<?= htmlspecialchars($detalle['stock'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                                </td>
                                <td>
                                    <input type="number" min="1" name="cantidad_producto[]" class="js-salida-cantidad" value="<?= htmlspecialchars($detalle['cantidad'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="precio_producto[]" class="js-salida-precio" value="<?= htmlspecialchars($detalle['precio'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                                </td>
                                <td>
                                    <input type="text" class="js-salida-total-linea" readonly>
                                </td>
                                <td>
                                    <button type="button" class="boton-peligro js-salida-quitar-linea">Quitar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="tarjeta flujo-bloque">
            <div class="cabecera-modulo cabecera-bloque">
                <div>
                    <h3 class="subtitulo">Resumen</h3>
                    <p>Confirma el total de la factura antes de registrar la salida.</p>
                </div>
            </div>

            <div class="campo campo-total-factura">
                <label for="salida-total-factura">Total factura</label>
                <input id="salida-total-factura" type="text" value="<?= htmlspecialchars($formularioSalida['total_factura'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                <span class="ayuda-campo" id="salida-validacion">La factura se valida por stock en cada linea.</span>
            </div>

            <div class="fila-acciones">
                <a href="?modulo=salidas" class="boton-fantasma">Limpiar formulario</a>
            </div>
        </section>
    </form>
</article>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 0;">
        <div>
            <h3 class="subtitulo">Historial de salidas</h3>
            <p>Registros recientes de ventas y despachos, con lectura rapida del stock comprometido.</p>
        </div>
    </div>
    <div class="tabla-contenedor">
        <table class="tabla tabla-historial-salidas">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Codigo</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Motivo</th>
                    <th>Bodega</th>
                    <th>Total</th>
                    <th>Fecha y hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($historialSalidas) === 0): ?>
                    <tr>
                        <td colspan="8">Aun no hay salidas registradas.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($historialSalidas as $salida): ?>
                    <tr>
                        <td><?= htmlspecialchars($salida['factura'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['motivo_salida'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['bodega'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($salida['total'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="fecha-hora-registro">
                                <strong><?= htmlspecialchars((string) ($salida['hora_registro'] ?? '--:--'), ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars((string) ($salida['fecha_registro'] ?? '--/--/----'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginacion-contenedor">
        <form method="get" class="paginacion-form">
            <input type="hidden" name="modulo" value="salidas">
            <input type="hidden" name="pagina" value="1">
            <label for="salidas-por-pagina">Registros por pagina</label>
            <select id="salidas-por-pagina" name="por_pagina" onchange="this.form.submit()">
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
                <a class="boton-fantasma <?= $paginaActual <= 1 ? 'deshabilitado' : '' ?>" href="?<?= htmlspecialchars(http_build_query(['modulo' => 'salidas', 'pagina' => max(1, $paginaActual - 1), 'por_pagina' => $porPaginaActual]), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
                <span class="paginacion-texto">Pagina <?= htmlspecialchars((string) $paginaActual, ENT_QUOTES, 'UTF-8') ?> de <?= htmlspecialchars((string) $totalPaginas, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="boton-fantasma <?= $paginaActual >= $totalPaginas ? 'deshabilitado' : '' ?>" href="?<?= htmlspecialchars(http_build_query(['modulo' => 'salidas', 'pagina' => min($totalPaginas, $paginaActual + 1), 'por_pagina' => $porPaginaActual]), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
            </div>
        <?php endif; ?>
    </div>
</article>
