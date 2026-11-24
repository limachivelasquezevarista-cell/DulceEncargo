<div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
    <div><h1 class="h3 fw-bold mb-0">Catálogo</h1><p class="text-muted mb-0">Elige un producto y personalízalo.</p></div>
</div>
<form class="row g-2 mb-4" method="get" action="<?= url('catalogo') ?>">
    <div class="col-md-7"><input class="form-control" name="q" placeholder="¿Qué se te antoja? Ej: chocolate" value="<?= e(isset($filtros['texto']) ? $filtros['texto'] : '') ?>"></div>
    <div class="col-md-3">
        <select class="form-select" name="categoria">
            <option value="">Todas las categorías</option>
            <?php foreach ((isset($categorias) ? $categorias : array()) as $c): ?>
                <option value="<?= (int) $c['id_categoria'] ?>" <?= (isset($filtros['id_categoria']) ? $filtros['id_categoria'] : '') == $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-grid"><button class="btn btn-dulce"><i class="bi bi-search"></i> Buscar</button></div>
</form>
<div class="row g-4">
    <?php foreach ((isset($productos) ? $productos : array()) as $p): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card producto-card border-0 shadow-sm h-100">
                <?= fotoProducto(isset($p['foto']) ? $p['foto'] : null) ?>
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-crema text-dark align-self-start mb-2"><?= e($p['categoria']) ?></span>
                    <h2 class="h5 fw-bold"><?= e($p['nombre']) ?></h2>
                    <p class="text-muted small flex-grow-1"><?= e($p['descripcion']) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="precio">Desde <?= bs($p['precio_base']) ?></span>
                        <a href="<?= url('pedidos/nuevo/' . (int) $p['id_producto']) ?>" class="btn btn-dulce btn-sm">Personalizar</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($productos)): ?>
        <div class="col-12"><div class="alert alert-light border">No encontramos productos con esa búsqueda.</div></div>
    <?php endif; ?>
</div>
