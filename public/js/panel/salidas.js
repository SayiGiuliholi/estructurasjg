(function () {
    var stock = document.getElementById('salida-stock');
    var cantidad = document.getElementById('salida-cantidad');
    var precio = document.getElementById('salida-precio');
    var descuento = document.getElementById('salida-descuento');
    var subtotal = document.getElementById('salida-subtotal');
    var total = document.getElementById('salida-total');
    var mensaje = document.getElementById('salida-validacion');
    var estado = document.getElementById('estado-stock');

    if (!stock || !cantidad || !precio || !descuento || !subtotal || !total || !mensaje || !estado) {
        return;
    }

    function recalcularSalida() {
        var stockValor = Number(stock.value || 0);
        var cantidadValor = Number(cantidad.value || 0);
        var precioValor = Number(precio.value || 0);
        var descuentoValor = Number(descuento.value || 0);
        var subtotalValor = cantidadValor * precioValor;
        var totalValor = subtotalValor - (subtotalValor * (descuentoValor / 100));

        subtotal.value = '$ ' + subtotalValor.toFixed(2);
        total.value = '$ ' + totalValor.toFixed(2);

        if (cantidadValor > stockValor) {
            mensaje.textContent = 'La cantidad supera el stock disponible. Ajusta el pedido antes de registrar la salida.';
            mensaje.style.color = '#b42318';
            estado.textContent = 'Sin stock';
            estado.className = 'estado critico';
            return;
        }

        mensaje.textContent = 'Stock suficiente para completar la venta.';
        mensaje.style.color = '#667085';
        estado.textContent = 'Correcto';
        estado.className = 'estado ok';
    }

    [stock, cantidad, precio, descuento].forEach(function (campo) {
        campo.addEventListener('input', recalcularSalida);
    });

    recalcularSalida();
})();
