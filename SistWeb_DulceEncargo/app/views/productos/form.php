<?php $p = isset($producto) ? $producto : []; $editar = !empty($p); ?>
<h1 class="h3 fw-bold mb-4"><?= $editar ? 'Editar producto' : 'Nuevo producto' ?></h1>
<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate
          action="<?= url($editar ? 'productos/actualizar/' . (int) $p['id_producto'] : 'productos/guardar') ?>">
        <?= csrf() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="nombre">Nombre</label>
                <input class="form-control" id="nombre" name="nombre" maxlength="80" value="<?= e(isset($p['nombre']) ? $p['nombre'] : '') ?>" required>
                <div class="invalid-feedback">El nombre es obligatorio.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="id_categoria">Categoría</label>
                <select class="form-select" id="id_categoria" name="id_categoria" required>
                    <option value="">Elegir...</option>
                    <?php foreach ((isset($categorias) ? $categorias : array()) as $c): ?>
                        <option value="<?= (int) $c['id_categoria'] ?>" <?= (isset($p['id_categoria']) ? $p['id_categoria'] : '') == $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Elige una categoría.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="precio_base">Precio base (Bs)</label>
                <input type="number" step="0.50" min="1" class="form-control" id="precio_base" name="precio_base" value="<?= e(isset($p['precio_base']) ? $p['precio_base'] : '') ?>" required>
                <div class="invalid-feedback">Ingresa un precio mayor a cero.</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="255"><?= e(isset($p['descripcion']) ? $p['descripcion'] : '') ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="foto">Foto (JPG o PNG, máx. 2 MB)</label>
                <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png" data-max-mb="2">
                <div class="invalid-feedback">La imagen debe ser JPG o PNG de hasta 2 MB.</div>
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-dulce" type="submit"><i class="bi bi-check2"></i> Guardar</button>
            <a href="<?= url('productos') ?>" class="btn btn-light">Cancelar</a>
        </div>
    </form>
</div></div>
