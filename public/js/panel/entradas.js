(function () {
    var cantidad = document.getElementById('entrada-cantidad');
    var precio = document.getElementById('entrada-precio');
    var total = document.getElementById('entrada-total');

    if (!cantidad || !precio || !total) {
        return;
    }

    function actualizarTotal() {
        var cantidadValor = Number(cantidad.value || 0);
        var precioValor = Number(precio.value || 0);

        total.value = '$ ' + (cantidadValor * precioValor).toFixed(2);
    }

    cantidad.addEventListener('input', actualizarTotal);
    precio.addEventListener('input', actualizarTotal);

    actualizarTotal();
})();
