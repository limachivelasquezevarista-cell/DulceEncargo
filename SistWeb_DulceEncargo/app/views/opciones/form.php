<?php $o = isset($opcion) ? $opcion : []; $editar = !empty($o); ?>
<h1 class="h3 fw-bold mb-4"><?= $editar ? 'Editar opción' : 'Nueva opción' ?></h1>
<div class="row"><div class="col-lg-6">
<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <form method="post" class="needs-validation" novalidate action="<?= url($editar ? 'opciones/actualizar/' . (int) $o['id_opcion'] : 'opciones/guardar') ?>">
        <?= csrf() ?>
        <div class="mb-3">
            <label class="form-label" for="id_tipo">Tipo</label>
            <select class="form-select" id="id_tipo" name="id_tipo" required>
                <option value="">Elegir...</option>
                <?php foreach ((isset($tipos) ? $tipos : array()) as $t): ?>
                    <option value="<?= (int) $t['id_tipo'] ?>" <?= (isset($o['id_tipo']) ? $o['id_tipo'] : '') == $t['id_tipo'] ? 'selected' : '' ?>><?= e($t['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Elige el tipo de opción.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="nombre">Nombre</label>
            <input class="form-control" id="nombre" name="nombre" maxlength="60" value="<?= e(isset($o['nombre']) ? $o['nombre'] : '') ?>" required>
            <div class="invalid-feedback">El nombre es obligatorio.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="recargo">Recargo (Bs)</label>
            <input type="number" step="0.50" min="0" class="form-control" id="recargo" name="recargo" value="<?= e(isset($o['recargo']) ? $o['recargo'] : '0') ?>" required>
            <div class="invalid-feedback">El recargo debe ser 0 o mayor.</div>
        </div>
        <button class="btn btn-dulce" type="submit"><i class="bi bi-check2"></i> Guardar</button>
        <a href="<?= url('opciones') ?>" class="btn btn-light">Cancelar</a>
    </form>
</div></div>
</div></div>
