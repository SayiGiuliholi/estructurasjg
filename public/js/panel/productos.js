(function () {
    var tablaHistorial = document.getElementById('tabla-historial-productos');
    var formularioFiltros = document.getElementById('productos-filtros-form');
    var inputBuscar = document.getElementById('productos-filtro-buscar');
    var selectProveedor = document.getElementById('productos-filtro-proveedor');
    var inputFecha = document.getElementById('productos-filtro-fecha');
    var filaVacia = document.getElementById('productos-historial-vacio');

    if (!tablaHistorial || !formularioFiltros || !inputBuscar || !selectProveedor || !inputFecha) {
        return;
    }

    var filasHistorial = Array.prototype.slice.call(
        tablaHistorial.querySelectorAll('tbody tr.js-historial-producto')
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
        var proveedor = normalizar(selectProveedor.value);
        var fechaSeleccionada = fechaInputARegistro(String(inputFecha.value || '').trim());
        var visibles = 0;

        filasHistorial.forEach(function (fila) {
            var textoFila = normalizar((fila.textContent || '') + ' ' + (fila.getAttribute('data-busqueda') || ''));
            var proveedorFila = normalizar(fila.getAttribute('data-proveedor') || '');
            var fechaFila = String(fila.getAttribute('data-fecha') || '').trim();

            var coincideTexto = termino === '' || textoFila.indexOf(termino) !== -1;
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
