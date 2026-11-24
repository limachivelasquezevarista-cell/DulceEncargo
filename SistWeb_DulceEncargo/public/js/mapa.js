/**
 * Mapa de ubicación de entrega (RF07) con la API de Leaflet + OpenStreetMap.
 * Al tocar el mapa se guarda la latitud y longitud en campos ocultos.
 */
(function () {
    'use strict';
    var contenedor = document.getElementById('mapa');
    if (!contenedor || typeof L === 'undefined') return;

    var plazaSacaba = [-17.4045, -66.0387]; // plaza principal de Sacaba
    var mapa = L.map('mapa').setView(plazaSacaba, 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);

    var marcador = null;
    mapa.on('click', function (e) {
        if (marcador) {
            marcador.setLatLng(e.latlng);
        } else {
            marcador = L.marker(e.latlng).addTo(mapa);
        }
        document.getElementById('latitud').value  = e.latlng.lat.toFixed(7);
        document.getElementById('longitud').value = e.latlng.lng.toFixed(7);
    });
})();
