(function () {
    var modalEdicion = document.getElementById('productos-modal-edicion');
    var botonesCerrarModal = Array.prototype.slice.call(document.querySelectorAll('[data-cerrar-modal-edicion]'));
    var inputPrecioEdicion = document.getElementById('prod-precio-editar');
    var formularioFiltros = document.getElementById('productos-filtros-form');
    var inputBuscar = document.getElementById('productos-filtro-buscar');
    var selectProveedor = document.getElementById('productos-filtro-proveedor');
    var selectBodega = document.getElementById('productos-filtro-bodega');
    var tablas = [
        document.getElementById('tabla-historial-productos-principal'),
        document.getElementById('tabla-historial-productos-secundaria')
    ].filter(Boolean);
    var panelesBodega = Array.prototype.slice.call(document.querySelectorAll('.js-panel-bodega'));
    var filaVaciaPrincipal = document.getElementById('productos-historial-vacio-principal');
    var filaVaciaSecundaria = document.getElementById('productos-historial-vacio-secundaria');

    function cerrarModalEdicion() {
        if (!modalEdicion) {
            return;
        }

        modalEdicion.classList.remove('productos-modal-visible');
        modalEdicion.setAttribute('aria-hidden', 'true');
    }

    if (modalEdicion) {
        botonesCerrarModal.forEach(function (boton) {
            boton.addEventListener('click', cerrarModalEdicion);
        });

        modalEdicion.addEventListener('click', function (evento) {
            if (evento.target === modalEdicion) {
                cerrarModalEdicion();
            }
        });

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape' && modalEdicion.classList.contains('productos-modal-visible')) {
                cerrarModalEdicion();
            }
        });
    }

    function formatearMilesDesdeDigitos(digitos) {
        if (!digitos) {
            return '';
        }

        return String(digitos).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatearPrecioEdicion() {
        if (!inputPrecioEdicion) {
            return;
        }

        var digitos = String(inputPrecioEdicion.value || '').replace(/\D/g, '');
        inputPrecioEdicion.value = formatearMilesDesdeDigitos(digitos);
    }

    if (inputPrecioEdicion) {
        inputPrecioEdicion.addEventListener('input', formatearPrecioEdicion);
        formatearPrecioEdicion();
    }

    if (tablas.length === 0 || !formularioFiltros || !inputBuscar || !selectProveedor || !selectBodega) {
        return;
    }

    var filasHistorial = tablas.map(function (tabla) {
        return {
            tabla: tabla,
            filas: Array.prototype.slice.call(tabla.querySelectorAll('tbody tr.js-historial-producto'))
        };
    });

    function normalizar(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function aplicarFiltros() {
        var termino = normalizar(inputBuscar.value);
        var esBusquedaCodigo = /^\d+$/.test(termino);
        var proveedor = normalizar(selectProveedor.value);
        var bodegaSeleccionada = normalizar(selectBodega.value);

        filasHistorial.forEach(function (bloqueTabla) {
            var visiblesTabla = 0;
            bloqueTabla.filas.forEach(function (fila) {
                var textoFila = normalizar((fila.textContent || '') + ' ' + (fila.getAttribute('data-busqueda') || ''));
                var codigoFila = normalizar(fila.getAttribute('data-codigo') || '');
                var proveedorFila = normalizar(fila.getAttribute('data-proveedor') || '');
                var bodegaFila = normalizar(fila.getAttribute('data-bodega') || '');

                var coincideTexto = termino === ''
                    || (esBusquedaCodigo ? codigoFila === termino : textoFila.indexOf(termino) !== -1);
                var coincideProveedor = proveedor === '' || proveedorFila === proveedor;
                var coincideBodega = bodegaSeleccionada === '' || bodegaFila === bodegaSeleccionada;
                var mostrar = coincideTexto && coincideProveedor && coincideBodega;

                fila.hidden = !mostrar;
                if (mostrar) {
                    visiblesTabla++;
                }
            });

            if (bloqueTabla.tabla.id === 'tabla-historial-productos-principal' && filaVaciaPrincipal) {
                filaVaciaPrincipal.hidden = visiblesTabla > 0;
            }
            if (bloqueTabla.tabla.id === 'tabla-historial-productos-secundaria' && filaVaciaSecundaria) {
                filaVaciaSecundaria.hidden = visiblesTabla > 0;
            }
        });

        panelesBodega.forEach(function (panel) {
            var clave = normalizar(panel.getAttribute('data-bodega') || '');
            var mostrarPanel = bodegaSeleccionada === '' || clave === bodegaSeleccionada;
            panel.hidden = !mostrarPanel;
        });
    }

    inputBuscar.addEventListener('input', aplicarFiltros);
    selectProveedor.addEventListener('change', aplicarFiltros);
    selectBodega.addEventListener('change', aplicarFiltros);
    formularioFiltros.addEventListener('submit', function (evento) {
        evento.preventDefault();
        aplicarFiltros();
    });

    [inputBuscar, selectProveedor, selectBodega].forEach(function (control) {
        control.addEventListener('keydown', function (evento) {
            if (evento.key !== 'Enter') {
                return;
            }

            evento.preventDefault();
            aplicarFiltros();
        });
    });

    aplicarFiltros();
})();
