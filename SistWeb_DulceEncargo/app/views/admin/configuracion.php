<h1 class="h3 fw-bold mb-4"><i class="bi bi-gear"></i> Configuración</h1>
<div class="row"><div class="col-md-6">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form method="post" action="<?= url('configuracion/guardar') ?>" class="needs-validation" novalidate>
            <?= csrf() ?>
            <label class="form-label fw-semibold" for="limite">Límite de pedidos por día</label>
            <input type="number" class="form-control" id="limite" name="limite" min="1" max="50" value="<?= (int) (isset($limite) ? $limite : 5) ?>" required>
            <div class="form-text">Cuando una fecha llega a este número de pedidos, el cliente ya no podrá elegirla.</div>
            <div class="invalid-feedback">Ingresa un número entre 1 y 50.</div>

            <label class="form-label fw-semibold mt-3" for="telefono">WhatsApp del negocio</label>
            <input class="form-control" id="telefono" name="telefono" value="<?= e(isset($telefono) ? $telefono : '') ?>" pattern="[67][0-9]{7}" maxlength="8" placeholder="Ej: 7XXXXXXX">
            <div class="form-text">Celular de 8 dígitos. El cliente lo usa para agendar y coordinar su pedido por WhatsApp. Déjalo vacío para ocultar el botón.</div>
            <div class="invalid-feedback">8 dígitos, empieza con 6 o 7.</div>
            <button class="btn btn-dulce mt-3" type="submit"><i class="bi bi-check2"></i> Guardar</button>
        </form>
    </div></div>
</div></div>
