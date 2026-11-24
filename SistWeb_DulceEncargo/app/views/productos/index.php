<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h1 class="h3 fw-bold mb-2 mb-md-0"><i class="bi bi-box-seam"></i> Productos</h1>
    <a href="<?= url('productos/crear') ?>" class="btn btn-dulce"><i class="bi bi-plus-lg"></i> Nuevo producto</a>
</div>

<form class="row g-2 mb-3" method="get" action="<?= url('productos') ?>">
    <div class="col-md-6"><input class="form-control" name="q" placeholder="Buscar por nombre" value="<?= e(isset($filtros['texto']) ? $filtros['texto'] : '') ?>"></div>
    <div class="col-md-4">
        <select class="form-select" name="categoria">
            <option value="">Todas las categorías</option>
            <?php foreach ((isset($categorias) ? $categorias : array()) as $c): ?>
                <option value="<?= (int) $c['id_categoria'] ?>" <?= (isset($filtros['id_categoria']) ? $filtros['id_categoria'] : '') == $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-grid"><button class="btn btn-outline-dulce"><i class="bi bi-search"></i> Buscar</button></div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Producto</th><th>Categoría</th><th class="text-end">Precio base</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ((isset($productos) ? $productos : array()) as $p): ?>
                <tr>
                    <td><strong><?= e($p['nombre']) ?></strong><div class="small text-muted"><?= e($p['descripcion']) ?></div></td>
                    <td><?= e($p['categoria']) ?></td>
                    <td class="text-end"><?= bs($p['precio_base']) ?></td>
                    <td><?= $p['estado'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('productos/editar/' . (int) $p['id_producto']) ?>" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="<?= url(($p['estado'] ? 'productos/desactivar/' : 'productos/activar/') . (int) $p['id_producto']) ?>" class="d-inline" data-confirmar="¿Seguro que deseas cambiar el estado de este producto?">
                            <?= csrf() ?>
                            <button class="btn btn-sm <?= $p['estado'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $p['estado'] ? 'Desactivar' : 'Activar' ?>"><i class="bi <?= $p['estado'] ? 'bi-slash-circle' : 'bi-check-circle' ?>"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($productos)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron productos.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
