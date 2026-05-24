(function () {
    var apiProducto = window.URL_API_PRODUCTO_SALIDA || '';
    var selectBodega = document.getElementById('salida-bodega');
    var selectBodegaDestino = document.getElementById('salida-bodega-destino');
    var grupoBodegaDestino = document.getElementById('grupo-salida-bodega-destino');
    var selectMotivo = document.getElementById('salida-motivo');
    var botonGuardar = document.getElementById('salida-boton-guardar');
    var cuerpoDetalles = document.getElementById('salida-detalles-body');
    var botonAgregar = document.getElementById('salida-agregar-linea');
    var totalFactura = document.getElementById('salida-total-factura');
    var mensajeValidacion = document.getElementById('salida-validacion');
    var formulario = document.getElementById('form-salidas');

    if (!selectBodega || !cuerpoDetalles || !botonAgregar || !totalFactura || !mensajeValidacion || !formulario) {
        return;
    }

    function esTrasladoActivo() {
        return selectMotivo && String(selectMotivo.value || '').trim() === 'traslado';
    }

    function actualizarModoTraslado() {
        var traslado = esTrasladoActivo();

        if (grupoBodegaDestino) {
            grupoBodegaDestino.hidden = !traslado;
            grupoBodegaDestino.style.display = traslado ? '' : 'none';
        }

        if (selectBodegaDestino) {
            if (traslado) {
                selectBodegaDestino.setAttribute('required', 'required');
            } else {
                selectBodegaDestino.removeAttribute('required');
            }
        }

        if (botonGuardar) {
            botonGuardar.textContent = traslado ? 'Trasladar productos' : 'Registrar salida';
        }

        if (traslado) {
            setMensaje('Modo traslado: se descuenta de origen y se suma en bodega destino.', 'ok');
        } else {
            setMensaje('Factura validada por stock en cada línea.', 'ok');
        }
    }

    function obtenerNumero(valor, fallback) {
        var texto = typeof valor === 'string' ? valor : String(valor);
        var limpio = texto.replace(/[^\d,.\-]/g, '').trim();

        if (limpio === '') {
            return fallback;
        }
        // En este modulo usamos valores enteros para moneda visual (ej: 300.000).
        // Se eliminan separadores para calcular correctamente.
        var normalizado = limpio.replace(/[.,]/g, '');

        var numero = Number(normalizado);
        return Number.isFinite(numero) ? numero : fallback;
    }

    function formatearMoneda(valor) {
        return '$' + Number(valor || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function formatearNumero(valor) {
        return '$' + Number(valor || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function setMensaje(texto, tipo) {
        mensajeValidacion.textContent = texto;
        mensajeValidacion.style.color = tipo === 'error' ? '#b42318' : '#667085';
    }

    function limpiarFila(fila) {
        var codigo = fila.querySelector('.js-salida-codigo');
        var descripcion = fila.querySelector('.js-salida-descripcion');
        var stock = fila.querySelector('.js-salida-stock');
        var cantidad = fila.querySelector('.js-salida-cantidad');
        var precio = fila.querySelector('.js-salida-precio');
        var totalLinea = fila.querySelector('.js-salida-total-linea');

        if (codigo) {
            codigo.value = '';
        }
        if (descripcion) {
            descripcion.value = '';
        }
        if (stock) {
            stock.value = '0';
        }
        if (cantidad) {
            cantidad.value = '1';
            cantidad.removeAttribute('max');
        }
        if (precio) {
            precio.value = '0';
        }
        if (totalLinea) {
            totalLinea.value = formatearMoneda(0);
        }
        if (fila) {
            fila.dataset.productoBloqueado = '0';
        }
    }

    function recalcularFila(fila) {
        var stock = fila.querySelector('.js-salida-stock');
        var cantidad = fila.querySelector('.js-salida-cantidad');
        var precio = fila.querySelector('.js-salida-precio');
        var totalLinea = fila.querySelector('.js-salida-total-linea');

        if (!stock || !cantidad || !precio || !totalLinea) {
            return { total: 0, valida: true };
        }

        var stockValor = Math.max(0, obtenerNumero(stock.value, 0));
        var cantidadValor = Math.max(0, obtenerNumero(cantidad.value, 0));
        var precioValor = Math.max(0, obtenerNumero(precio.value, 0));
        var total = cantidadValor * precioValor;
        var valida = cantidadValor <= stockValor;

        totalLinea.value = formatearMoneda(total);
        return { total: total, valida: valida };
    }

    function recalcularFactura() {
        var filas = cuerpoDetalles.querySelectorAll('tr.detalle-salida');
        var total = 0;
        var hayErrorStock = false;
        var hayProductoDesactivado = false;

        filas.forEach(function (fila) {
            var resultado = recalcularFila(fila);
            total += resultado.total;
            if (!resultado.valida) {
                hayErrorStock = true;
            }
            if (fila.dataset.productoBloqueado === '1') {
                hayProductoDesactivado = true;
            }
        });

        totalFactura.value = formatearMoneda(total);

        if (hayProductoDesactivado) {
            setMensaje('Hay productos desactivados en la salida. Corrige las lineas antes de guardar.', 'error');
            return false;
        }

        if (hayErrorStock) {
            setMensaje('La cantidad supera el stock disponible.', 'error');
            return false;
        }

        if (esTrasladoActivo()) {
            setMensaje('Modo traslado: se descuenta de origen y se suma en bodega destino.', 'ok');
        } else {
            setMensaje('Factura validada por stock en cada línea.', 'ok');
        }
        return true;
    }

    function autocompletarFila(fila) {
        var inputCodigo = fila.querySelector('.js-salida-codigo');
        var inputDescripcion = fila.querySelector('.js-salida-descripcion');
        var inputStock = fila.querySelector('.js-salida-stock');
        var inputCantidad = fila.querySelector('.js-salida-cantidad');
        var inputPrecio = fila.querySelector('.js-salida-precio');
        var idBodega = (selectBodega.value || '').trim();
        var codigo = inputCodigo ? (inputCodigo.value || '').trim() : '';

        if (!inputCodigo || !inputDescripcion || !inputStock || !inputCantidad || !inputPrecio) {
            return;
        }

        if (apiProducto === '' || idBodega === '' || codigo === '') {
            limpiarFila(fila);
            recalcularFactura();
            return;
        }

        var url = apiProducto + '?codigo=' + encodeURIComponent(codigo) + '&id_bodega=' + encodeURIComponent(idBodega);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (respuesta) {
                return respuesta.json().then(function (datos) {
                    if (!respuesta.ok || !datos.ok) {
                        var mensajeError = (datos && datos.mensaje)
                            ? datos.mensaje
                            : 'No se encontro el producto en la bodega seleccionada.';
                        throw new Error(mensajeError);
                    }
                    return datos;
                });
            })
            .then(function (datos) {
                if (!datos.producto) {
                    throw new Error('No se pudo cargar el producto.');
                }

                inputDescripcion.value = datos.producto.descripcion || '';
                inputStock.value = String(datos.producto.stock_bodega || 0);
                inputPrecio.value = formatearNumero(datos.producto.precio || 0);
                fila.dataset.productoBloqueado = '0';

                if (obtenerNumero(inputCantidad.value, 0) <= 0) {
                    inputCantidad.value = '1';
                }

                inputCantidad.max = String(datos.producto.stock_bodega || 0);
                recalcularFactura();
            })
            .catch(function (error) {
                limpiarFila(fila);
                if (String(error.message || '').toLowerCase().indexOf('desactivado') !== -1) {
                    fila.dataset.productoBloqueado = '1';
                }
                setMensaje(error.message || 'No se pudo autocompletar una línea.', 'error');
                recalcularFactura();
            });
    }

    function agregarLinea() {
        var primeraFila = cuerpoDetalles.querySelector('tr.detalle-salida');
        if (!primeraFila) {
            return;
        }

        var nuevaFila = primeraFila.cloneNode(true);
        limpiarFila(nuevaFila);
        cuerpoDetalles.appendChild(nuevaFila);
        recalcularFactura();
    }

    botonAgregar.addEventListener('click', agregarLinea);

    cuerpoDetalles.addEventListener('click', function (evento) {
        var botonQuitar = evento.target.closest('.js-salida-quitar-linea');
        if (!botonQuitar) {
            return;
        }

        var fila = botonQuitar.closest('tr.detalle-salida');
        if (!fila) {
            return;
        }

        var totalFilas = cuerpoDetalles.querySelectorAll('tr.detalle-salida').length;
        if (totalFilas <= 1) {
            limpiarFila(fila);
        } else {
            fila.remove();
        }

        recalcularFactura();
    });

    var temporizadorFila = null;

    cuerpoDetalles.addEventListener('input', function (evento) {
        var fila = evento.target.closest('tr.detalle-salida');
        if (!fila) {
            return;
        }

        if (evento.target.matches('.js-salida-cantidad')) {
            recalcularFactura();
            return;
        }

        if (evento.target.matches('.js-salida-codigo')) {
            if (temporizadorFila !== null) {
                clearTimeout(temporizadorFila);
            }

            temporizadorFila = setTimeout(function () {
                autocompletarFila(fila);
            }, 250);
        }
    });

    cuerpoDetalles.addEventListener('blur', function (evento) {
        if (!evento.target.matches('.js-salida-codigo')) {
            return;
        }

        var fila = evento.target.closest('tr.detalle-salida');
        if (fila) {
            autocompletarFila(fila);
        }
    }, true);

    selectBodega.addEventListener('change', function () {
        var filas = cuerpoDetalles.querySelectorAll('tr.detalle-salida');
        filas.forEach(function (fila) {
            var codigo = fila.querySelector('.js-salida-codigo');
            if (codigo && (codigo.value || '').trim() !== '') {
                autocompletarFila(fila);
            } else {
                limpiarFila(fila);
            }
        });

        recalcularFactura();
    });

    if (selectMotivo) {
        selectMotivo.addEventListener('change', function () {
            actualizarModoTraslado();
        });
    }

    formulario.addEventListener('submit', function (evento) {
        if (esTrasladoActivo()) {
            var origen = String(selectBodega.value || '').trim();
            var destino = selectBodegaDestino ? String(selectBodegaDestino.value || '').trim() : '';

            if (destino === '') {
                evento.preventDefault();
                setMensaje('Selecciona una bodega destino para el traslado.', 'error');
                return;
            }

            if (origen !== '' && origen === destino) {
                evento.preventDefault();
                setMensaje('La bodega destino debe ser distinta a la bodega origen.', 'error');
                return;
            }
        }

        if (!recalcularFactura()) {
            evento.preventDefault();
        }
    });

    actualizarModoTraslado();
    recalcularFactura();
})();

(function () {
    var tablaHistorial = document.getElementById('tabla-historial-salidas');
    var formularioFiltros = document.getElementById('salidas-filtros-form');
    var inputBuscar = document.getElementById('salidas-filtro-buscar');
    var selectBodega = document.getElementById('salidas-filtro-bodega');
    var inputFecha = document.getElementById('salidas-filtro-fecha');
    var filaVacia = document.getElementById('salidas-historial-vacio');

    if (!tablaHistorial || !formularioFiltros || !inputBuscar || !selectBodega || !inputFecha) {
        return;
    }

    var filasHistorial = Array.prototype.slice.call(
        tablaHistorial.querySelectorAll('tbody tr.js-historial-salida')
    );

    function normalizar(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function fechaInputARegistro(fechaInput) {
        if (!fechaInput || fechaInput.indexOf('-') === -1) {
            return '';
        }
        var partes = fechaInput.split('-');
        if (partes.length !== 3) {
            return '';
        }
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    function aplicarFiltros() {
        var termino = normalizar(inputBuscar.value);
        var esBusquedaCodigo = /^\d+$/.test(termino);
        var bodega = normalizar(selectBodega.value);
        var fechaSeleccionada = fechaInputARegistro(String(inputFecha.value || '').trim());
        var visibles = 0;

        filasHistorial.forEach(function (fila) {
            var textoFila = normalizar(fila.textContent || '');
            var codigoFila = normalizar(fila.getAttribute('data-codigo') || '');
            var bodegaFila = normalizar(fila.getAttribute('data-bodega') || '');
            var fechaFila = String(fila.getAttribute('data-fecha') || '').trim();

            var coincideTexto = termino === ''
                || (esBusquedaCodigo ? codigoFila === termino : textoFila.indexOf(termino) !== -1);
            var coincideBodega = bodega === '' || bodegaFila === bodega;
            var coincideFecha = fechaSeleccionada === '' || fechaFila === fechaSeleccionada;
            var mostrar = coincideTexto && coincideBodega && coincideFecha;

            fila.hidden = !mostrar;
            if (mostrar) {
                visibles++;
            }
        });

        if (filaVacia) {
            filaVacia.hidden = visibles > 0;
        }
    }

    inputBuscar.addEventListener('input', aplicarFiltros);
    selectBodega.addEventListener('change', aplicarFiltros);
    inputFecha.addEventListener('change', aplicarFiltros);
    formularioFiltros.addEventListener('submit', function (evento) {
        evento.preventDefault();
        aplicarFiltros();
    });

    [inputBuscar, selectBodega, inputFecha].forEach(function (control) {
        control.addEventListener('keydown', function (evento) {
            if (evento.key !== 'Enter') {
                return;
            }

            evento.preventDefault();
            aplicarFiltros();
        });
    });
})();
