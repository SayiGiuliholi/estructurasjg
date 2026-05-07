(function () {
    var apiProducto = window.URL_API_PRODUCTO_ENTRADA || '';
    var cuerpoDetalles = document.getElementById('entrada-detalles-body');
    var botonAgregar = document.getElementById('entrada-agregar-linea');
    var totalFactura = document.getElementById('entrada-total-factura');
    var selectProveedor = document.getElementById('entrada-proveedor');

    if (!cuerpoDetalles || !botonAgregar || !totalFactura) {
        return;
    }

    function obtenerValorNumero(input, fallback) {
        var valorTexto = input && typeof input.value === 'string' ? input.value : String(fallback);
        var limpio = valorTexto.replace(/[^\d,.\-]/g, '').trim();

        if (limpio === '') {
            return fallback;
        }

        var normalizado = limpio.replace(/[.,]/g, '');

        var valor = Number(normalizado);
        return Number.isFinite(valor) ? valor : fallback;
    }

    function formatearMoneda(valor) {
        return '$' + Number(valor || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function formatearMonedaDesdeDigitos(digitos) {
        if (!digitos) {
            return '';
        }

        return '$' + Number(digitos).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function formatearPrecioInput(inputPrecio) {
        if (!inputPrecio) {
            return;
        }

        var digitos = String(inputPrecio.value || '').replace(/\D/g, '');
        if (digitos === '') {
            inputPrecio.value = '';
            return;
        }

        var precio = Math.max(0, obtenerValorNumero(inputPrecio, 0));
        inputPrecio.value = formatearMoneda(precio);
    }

    function recalcularLinea(fila) {
        var cantidadInput = fila.querySelector('.js-cantidad');
        var precioInput = fila.querySelector('.js-precio');
        var totalInput = fila.querySelector('.js-total-linea');

        if (!cantidadInput || !precioInput || !totalInput) {
            return 0;
        }

        var cantidad = Math.max(0, obtenerValorNumero(cantidadInput, 0));
        var precio = Math.max(0, obtenerValorNumero(precioInput, 0));
        var total = cantidad * precio;

        totalInput.value = formatearMoneda(total);
        return total;
    }

    function recalcularFactura() {
        var filas = cuerpoDetalles.querySelectorAll('tr.detalle-entrada');
        var total = 0;

        filas.forEach(function (fila) {
            total += recalcularLinea(fila);
        });

        totalFactura.value = formatearMoneda(total);
    }

    function limpiarFila(fila) {
        var codigo = fila.querySelector('.js-entrada-codigo');
        var descripcion = fila.querySelector('.js-entrada-descripcion');
        var cantidad = fila.querySelector('.js-cantidad');
        var precio = fila.querySelector('.js-precio');

        if (codigo) {
            codigo.value = '';
        }
        if (descripcion) {
            descripcion.value = '';
        }
        if (cantidad) {
            cantidad.value = '1';
        }
        if (precio) {
            precio.value = '';
        }
        if (descripcion) {
            descripcion.readOnly = false;
        }
        if (precio) {
            precio.readOnly = false;
        }
    }

    function autocompletarFila(fila) {
        if (!fila || apiProducto === '') {
            return;
        }

        var inputCodigo = fila.querySelector('.js-entrada-codigo');
        var inputDescripcion = fila.querySelector('.js-entrada-descripcion');
        var inputPrecio = fila.querySelector('.js-precio');
        var codigo = inputCodigo ? String(inputCodigo.value || '').trim() : '';

        if (!inputCodigo || !inputDescripcion || !inputPrecio || codigo === '') {
            if (inputDescripcion) {
                inputDescripcion.readOnly = false;
            }
            if (inputPrecio) {
                inputPrecio.readOnly = false;
            }
            return;
        }

        var url = apiProducto + '?codigo=' + encodeURIComponent(codigo);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (respuesta) {
                return respuesta.json().then(function (datos) {
                    if (!respuesta.ok || !datos.ok) {
                        throw new Error((datos && datos.mensaje) ? datos.mensaje : 'No se pudo cargar el producto.');
                    }
                    return datos;
                });
            })
            .then(function (datos) {
                if (!datos.producto) {
                    return;
                }

                var descripcion = String(datos.producto.descripcion || '').trim();
                var precio = Number(datos.producto.precio || 0);
                var idProveedorProducto = String(datos.producto.id_proveedor || '').trim();
                var descripcionActual = String(inputDescripcion.value || '').trim();
                var precioActual = Math.max(0, obtenerValorNumero(inputPrecio, 0));

                // Solo autocompleta descripcion si el usuario no ha escrito una.
                if (descripcion !== '' && descripcionActual === '') {
                    inputDescripcion.value = descripcion;
                }

                // Solo autocompleta precio si el usuario no ha digitado uno.
                if (precio > 0 && precioActual <= 0) {
                    inputPrecio.value = formatearMoneda(precio);
                }

                // Para productos existentes solo se permite cambiar cantidad.
                inputDescripcion.readOnly = true;
                inputPrecio.readOnly = true;

                if (selectProveedor && idProveedorProducto !== '' && String(selectProveedor.value || '').trim() === '') {
                    selectProveedor.value = idProveedorProducto;
                }

                recalcularFactura();
            })
            .catch(function () {
                // Si no existe el codigo, se permite captura manual de descripcion y precio.
                inputDescripcion.readOnly = false;
                inputPrecio.readOnly = false;
            });
    }

    function agregarLinea() {
        var primeraFila = cuerpoDetalles.querySelector('tr.detalle-entrada');
        if (!primeraFila) {
            return;
        }

        var nuevaFila = primeraFila.cloneNode(true);
        limpiarFila(nuevaFila);
        cuerpoDetalles.appendChild(nuevaFila);
        recalcularFactura();
    }

    botonAgregar.addEventListener('click', agregarLinea);
    var temporizadorCodigo = null;

    cuerpoDetalles.addEventListener('click', function (evento) {
        var botonQuitar = evento.target.closest('.js-quitar-linea');
        if (!botonQuitar) {
            return;
        }

        var fila = botonQuitar.closest('tr.detalle-entrada');
        if (!fila) {
            return;
        }

        var totalFilas = cuerpoDetalles.querySelectorAll('tr.detalle-entrada').length;
        if (totalFilas <= 1) {
            limpiarFila(fila);
        } else {
            fila.remove();
        }

        recalcularFactura();
    });

    cuerpoDetalles.addEventListener('input', function (evento) {
        if (evento.target.matches('.js-precio')) {
            var digitosPrecio = String(evento.target.value || '').replace(/\D/g, '');
            evento.target.value = formatearMonedaDesdeDigitos(digitosPrecio);
            recalcularFactura();
            return;
        }

        if (evento.target.matches('.js-cantidad')) {
            recalcularFactura();
            return;
        }

        if (evento.target.matches('.js-entrada-codigo')) {
            var filaCodigo = evento.target.closest('tr.detalle-entrada');
            if (!filaCodigo) {
                return;
            }

            if (temporizadorCodigo) {
                clearTimeout(temporizadorCodigo);
            }

            temporizadorCodigo = setTimeout(function () {
                autocompletarFila(filaCodigo);
            }, 250);
        }
    });

    cuerpoDetalles.addEventListener('blur', function (evento) {
        if (evento.target.matches('.js-entrada-codigo')) {
            var filaCodigo = evento.target.closest('tr.detalle-entrada');
            if (filaCodigo) {
                autocompletarFila(filaCodigo);
            }
            return;
        }

        if (!evento.target.matches('.js-precio')) {
            return;
        }

        formatearPrecioInput(evento.target);
        recalcularFactura();
    }, true);

    cuerpoDetalles.querySelectorAll('.js-precio').forEach(function (inputPrecio) {
        formatearPrecioInput(inputPrecio);
    });

    var formularioEntradas = document.getElementById('form-entradas');
    if (formularioEntradas) {
        formularioEntradas.addEventListener('submit', function () {
            var botonesSubmit = formularioEntradas.querySelectorAll('button[type="submit"]');
            botonesSubmit.forEach(function (boton) {
                boton.disabled = true;
            });
        });
    }

    recalcularFactura();
})();

(function () {
    var tablaHistorial = document.getElementById('tabla-historial-entradas');
    var formularioFiltros = document.getElementById('entrada-filtros-form');
    var inputBuscar = document.getElementById('entrada-filtro-buscar');
    var selectProveedor = document.getElementById('entrada-filtro-proveedor');
    var inputFecha = document.getElementById('entrada-filtro-fecha');
    var filaVacia = document.getElementById('entrada-historial-vacio');

    if (!tablaHistorial || !formularioFiltros || !inputBuscar || !selectProveedor || !inputFecha) {
        return;
    }

    var filasHistorial = Array.prototype.slice.call(
        tablaHistorial.querySelectorAll('tbody tr.js-historial-entrada')
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
        var proveedor = normalizar(selectProveedor.value);
        var fechaSeleccionada = fechaInputARegistro(String(inputFecha.value || '').trim());
        var visibles = 0;

        filasHistorial.forEach(function (fila) {
            var textoFila = normalizar(fila.textContent || '');
            var codigoFila = normalizar(fila.getAttribute('data-codigo') || '');
            var proveedorFila = normalizar(fila.getAttribute('data-proveedor') || '');
            var fechaFila = String(fila.getAttribute('data-fecha') || '').trim();

            var coincideTexto = termino === ''
                || (esBusquedaCodigo ? codigoFila === termino : textoFila.indexOf(termino) !== -1);
            var coincideProveedor = proveedor === '' || proveedorFila === proveedor;
            var coincideFecha = fechaSeleccionada === '' || fechaFila === fechaSeleccionada;
            var mostrar = coincideTexto && coincideProveedor && coincideFecha;

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
    selectProveedor.addEventListener('change', aplicarFiltros);
    inputFecha.addEventListener('change', aplicarFiltros);
    formularioFiltros.addEventListener('submit', function (evento) {
        evento.preventDefault();
        aplicarFiltros();
    });

    [inputBuscar, selectProveedor, inputFecha].forEach(function (control) {
        control.addEventListener('keydown', function (evento) {
            if (evento.key !== 'Enter') {
                return;
            }

            evento.preventDefault();
            aplicarFiltros();
        });
    });
})();
