/**
 * Configurador del pedido (RF04):
 * - calcula el precio en pantalla al elegir opciones
 * - bloquea las fechas que ya llegaron al límite de pedidos por día
 * El total definitivo siempre se recalcula en el servidor.
 */
(function () {
    'use strict';
    var form = document.getElementById('form-pedido');
    if (!form) return;

    var precioBase   = parseFloat(form.dataset.precioBase) || 0;
    var fechasLlenas = JSON.parse(form.dataset.fechasLlenas || '[]');
    var cantidad     = document.getElementById('cantidad');
    var total        = document.getElementById('total');
    var resumen      = document.getElementById('resumen-opciones');
    var fecha        = document.getElementById('fecha_entrega');
    var msgFecha     = document.getElementById('msg-fecha');

    function formatoBs(monto) {
        return 'Bs ' + monto.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcular() {
        var unitario = precioBase;
        resumen.innerHTML = '';
        form.querySelectorAll('.opcion:checked').forEach(function (op) {
            unitario += parseFloat(op.dataset.recargo) || 0;
            var li = document.createElement('li');
            li.textContent = form.querySelector('label[for="' + op.id + '"]').childNodes[0].textContent.trim();
            resumen.appendChild(li);
        });
        var cant = Math.max(1, parseInt(cantidad.value, 10) || 1);
        total.textContent = formatoBs(unitario * cant);
    }

    function revisarFecha() {
        if (fechasLlenas.indexOf(fecha.value) !== -1) {
            fecha.setCustomValidity('Fecha sin cupo');
            msgFecha.textContent = 'Esa fecha ya no tiene cupo. Elige otra, por favor.';
        } else {
            fecha.setCustomValidity('');
            msgFecha.textContent = 'Elige una fecha a partir de mañana.';
        }
    }

    form.addEventListener('change', recalcular);
    cantidad.addEventListener('input', recalcular);
    fecha.addEventListener('change', revisarFecha);
    recalcular();
})();
