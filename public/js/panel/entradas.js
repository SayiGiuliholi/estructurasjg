(function () {
    var cuerpoDetalles = document.getElementById('entrada-detalles-body');
    var botonAgregar = document.getElementById('entrada-agregar-linea');
    var totalFactura = document.getElementById('entrada-total-factura');

    if (!cuerpoDetalles || !botonAgregar || !totalFactura) {
        return;
    }

    function obtenerValorNumero(input, fallback) {
        var valorTexto = input && typeof input.value === 'string' ? input.value : String(fallback);
        var limpio = valorTexto.replace(/[^\d,.\-]/g, '').trim();

        if (limpio === '') {
            return fallback;
        }

        var ultimoPunto = limpio.lastIndexOf('.');
        var ultimaComa = limpio.lastIndexOf(',');
        var normalizado = limpio;

        if (ultimoPunto !== -1 && ultimaComa !== -1) {
            if (ultimaComa > ultimoPunto) {
                normalizado = limpio.replace(/\./g, '').replace(',', '.');
            } else {
                normalizado = limpio.replace(/,/g, '');
            }
        } else if (ultimaComa !== -1) {
            normalizado = limpio.replace(',', '.');
        }

        var valor = Number(normalizado);
        return Number.isFinite(valor) ? valor : fallback;
    }

    function formatearMoneda(valor) {
        return '$ ' + Number(valor || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
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
        var codigo = fila.querySelector('input[name="codigo_producto[]"]');
        var descripcion = fila.querySelector('input[name="descripcion_producto[]"]');
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
            precio.value = '0.00';
        }
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
        if (evento.target.matches('.js-cantidad, .js-precio')) {
            recalcularFactura();
        }
    });

    recalcularFactura();
})();
