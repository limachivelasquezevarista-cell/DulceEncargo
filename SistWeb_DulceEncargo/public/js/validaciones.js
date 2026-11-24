/**
 * Validaciones del lado del navegador (JavaScript).
 * Se aplican a todos los formularios con la clase .needs-validation.
 * El servidor (PHP) vuelve a validar todo, por seguridad.
 */
(function () {
    'use strict';

    function validarExtras(form) {
        // Campos que deben ser iguales (ej: repetir contraseña)
        form.querySelectorAll('[data-igual-a]').forEach(function (campo) {
            var otro = form.querySelector('#' + campo.dataset.igualA);
            campo.setCustomValidity(otro && campo.value !== otro.value ? 'No coincide' : '');
        });

        // Fecha que debe ser mayor o igual a otra (ej: rango de reportes)
        form.querySelectorAll('[data-mayor-igual]').forEach(function (campo) {
            var otro = form.querySelector('#' + campo.dataset.mayorIgual);
            campo.setCustomValidity(otro && campo.value && otro.value && campo.value < otro.value ? 'Fecha inválida' : '');
        });

        // Tamaño máximo de archivos (ej: foto del producto)
        form.querySelectorAll('input[type=file][data-max-mb]').forEach(function (campo) {
            var archivo = campo.files[0];
            var max = parseFloat(campo.dataset.maxMb) * 1024 * 1024;
            var tipos = ['image/jpeg', 'image/png'];
            var malo = archivo && (archivo.size > max || tipos.indexOf(archivo.type) === -1);
            campo.setCustomValidity(malo ? 'Archivo no válido' : '');
        });
    }

    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            validarExtras(form);
            if (!form.checkValidity()) {
                evento.preventDefault();
                evento.stopPropagation();
            }
            form.classList.add('was-validated');
        });
        form.addEventListener('input', function () {
            if (form.classList.contains('was-validated')) validarExtras(form);
        });
    });

    // Confirmación antes de acciones como desactivar
    document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            if (!confirm(form.dataset.confirmar)) evento.preventDefault();
        });
    });
})();
