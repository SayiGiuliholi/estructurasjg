<?php
$resumenIndicadores = $resumenIndicadores ?? [];
$mensajeExito = $mensajeExito ?? '';
$mensajeError = $mensajeError ?? '';
$formularioSalida = $formularioSalida ?? [
    'codigo_factura' => '',
    'id_bodega' => '',
    'id_bodega_destino' => '',
    'motivo_salida' => 'normal',
    'total_factura' => '$0',
    'detalles' => [],
    'bodegas' => [],
];
$historialSalidas = $historialSalidas ?? [];
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
            <h3 class="subtitulo">Informacion de la salida</h3>
            
        </div>
    </div>

    <?php if ($mensajeExito !== ''): ?>
        <p class="nota-exito"><?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($mensajeError !== ''): ?>
        <p class="nota-error"><?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (isset($puedeRegistrarMovimientos) && !$puedeRegistrarMovimientos): ?>
        <p class="nota-modulo">Modo solo lectura: puedes consultar historial, pero no registrar salidas.</p>
    <?php endif; ?>

    <form class="flujo-formulario" id="form-salidas" method="post">
        <?= csrfCampoOculto() ?>
        <fieldset <?= (isset($puedeRegistrarMovimientos) && !$puedeRegistrarMovimientos) ? 'disabled' : '' ?> style="border:0;padding:0;margin:0;display:grid;gap:14px;">
        <input type="hidden" name="accion" value="registrar">

        <section class="tarjeta flujo-bloque">
            <div class="cabecera-modulo cabecera-bloque">
                <div>
                    <h3 class="subtitulo">Datos de la salida</h3>
                    <p>Ingresa los datos de la salida.</p>
                </div>
            </div>

            <div class="formulario-grid formulario-grid-datos">
                <div class="campo">
                    <label for="salida-codigo-factura">Número de factura</label>
                    <input
                        id="salida-codigo-factura"
                        name="codigo_factura"
                        type="text"
                        value="<?= htmlspecialchars($formularioSalida['codigo_factura'], ENT_QUOTES, 'UTF-8') ?>"
                        readonly
                    >
                </div>

                <div class="campo">
                    <label for="salida-fecha-registro">Fecha</label>
                    <input id="salida-fecha-registro" type="date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" readonly>
                </div>

                <div class="campo">
                    <label for="salida-bodega">Bodega</label>
                    <select id="salida-bodega" name="id_bodega" required>
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
                    <label for="salida-motivo">Tipo de salida</label>
                    <select id="salida-motivo" name="motivo_salida" required>
                        <option value="normal" <?= $formularioSalida['motivo_salida'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="devolucion" <?= $formularioSalida['motivo_salida'] === 'devolucion' ? 'selected' : '' ?>>Devolucion</option>
                        <option value="fallo" <?= $formularioSalida['motivo_salida'] === 'fallo' ? 'selected' : '' ?>>Fallo</option>
                        <option value="traslado" <?= $formularioSalida['motivo_salida'] === 'traslado' ? 'selected' : '' ?>>Traslado</option>
                    </select>
                </div>

                <div class="campo" id="grupo-salida-bodega-destino" <?= $formularioSalida['motivo_salida'] === 'traslado' ? '' : 'hidden' ?>>
                    <label for="salida-bodega-destino">Bodega destino</label>
                    <select id="salida-bodega-destino" name="id_bodega_destino" <?= $formularioSalida['motivo_salida'] === 'traslado' ? 'required' : '' ?>>
                        <?php foreach ($formularioSalida['bodegas'] as $bodega): ?>
                            <option
                                value="<?= htmlspecialchars((string) $bodega['id'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($formularioSalida['id_bodega_destino'] ?? '') === (string) $bodega['id'] ? 'selected' : '' ?>
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
                    <h3 class="subtitulo">Productos</h3>
                </div>
                <button type="button" class="boton-secundario" id="salida-agregar-linea">Añadir producto</button>
            </div>

            <div class="tabla-contenedor">
                <table class="tabla" id="tabla-detalles-salida">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Stock bodega</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                            <th>Acción</th>
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
                    <p>Resumen de la salida.</p>
                </div>
            </div>

            <div class="campo campo-total-factura">
                <label for="salida-total-factura">Total de la salida</label>
                <input id="salida-total-factura" type="text" value="<?= htmlspecialchars($formularioSalida['total_factura'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                <span class="ayuda-campo" id="salida-validacion">La factura se valida por stock en cada linea.</span>
            </div>

            <div class="fila-acciones">
                <?php if (!isset($puedeRegistrarMovimientos) || $puedeRegistrarMovimientos): ?>
                    <button type="submit" form="form-salidas" class="boton-principal" id="salida-boton-guardar">
                        <?= $formularioSalida['motivo_salida'] === 'traslado' ? 'Trasladar productos' : 'Registrar salida' ?>
                    </button>
                <?php endif; ?>
            </div>
        </section>
        </fieldset>
    </form>
</article>

<article class="tarjeta tarjeta-tabla">
    <div class="cabecera-modulo" style="padding: 22px 22px 14px;">
        <div>
            <h3 class="subtitulo">Historial de salidas</h3>
            <p>Consulta las salidas registradas en el sistema.</p>
        </div>
    </div>

    <?php
    $bodegasHistorial = [];
    foreach (($formularioSalida['bodegas'] ?? []) as $bodegaFormulario) {
        $nombreBodega = trim((string) ($bodegaFormulario['nombre'] ?? ''));
        if ($nombreBodega !== '') {
            $bodegasHistorial[$nombreBodega] = $nombreBodega;
        }
    }
    foreach ($historialSalidas as $salidaHistorial) {
        $nombreBodega = trim((string) ($salidaHistorial['bodega'] ?? ''));
        if ($nombreBodega !== '') {
            $bodegasHistorial[$nombreBodega] = $nombreBodega;
        }
    }
    ksort($bodegasHistorial);
    ?>

    <form class="historial-filtros historial-filtros-salidas" id="salidas-filtros-form" onsubmit="return false;">
        <div class="campo campo-busqueda">
            <label for="salidas-filtro-buscar">Buscar salida</label>
            <input id="salidas-filtro-buscar" type="text" placeholder="Buscar factura, codigo o producto...">
        </div>

        <div class="campo">
            <label for="salidas-filtro-bodega">Bodega</label>
            <select id="salidas-filtro-bodega">
                <option value="">Todas</option>
                <?php foreach ($bodegasHistorial as $bodegaHistorial): ?>
                    <option value="<?= htmlspecialchars($bodegaHistorial, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($bodegaHistorial, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="salidas-filtro-fecha">Fecha</label>
            <input id="salidas-filtro-fecha" type="date">
        </div>

        <div class="historial-filtros-accion">
            <button type="submit" class="boton-secundario">Buscar</button>
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla tabla-historial-salidas" id="tabla-historial-salidas">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Tipo de salida</th>
                    <th>Bodega</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($historialSalidas) === 0): ?>
                    <tr>
                        <td colspan="8">Aun no hay salidas registradas.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($historialSalidas as $salida): ?>
                    <tr
                        class="js-historial-salida"
                        data-codigo="<?= htmlspecialchars((string) $salida['codigo'], ENT_QUOTES, 'UTF-8') ?>"
                        data-bodega="<?= htmlspecialchars(strtolower((string) $salida['bodega']), ENT_QUOTES, 'UTF-8') ?>"
                        data-fecha="<?= htmlspecialchars((string) ($salida['fecha_registro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    >
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
                <tr id="salidas-historial-vacio" hidden>
                    <td colspan="8">No hay resultados con los filtros aplicados.</td>
                </tr>
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
