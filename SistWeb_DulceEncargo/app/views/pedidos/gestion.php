<h1 class="h3 fw-bold mb-3"><i class="bi bi-receipt"></i> Gestión de pedidos</h1>
<form class="card border-0 shadow-sm mb-3" method="get" action="<?= url('pedidos') ?>">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small" for="fecha">Fecha de entrega</label>
            <input type="date" class="form-control" id="fecha" name="fecha" value="<?= e(isset($filtros['fecha']) ? $filtros['fecha'] : '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small" for="estado">Estado</label>
            <select class="form-select" id="estado" name="estado">
                <option value="">Todos</option>
                <?php foreach ((isset($estados) ? $estados : array()) as $es): ?>
                    <option value="<?= e($es) ?>" <?= (isset($filtros['estado']) ? $filtros['estado'] : '') === $es ? 'selected' : '' ?>><?= e(nombreEstado($es)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small" for="cliente">Cliente</label>
            <input class="form-control" id="cliente" name="cliente" placeholder="Nombre del cliente" value="<?= e(isset($filtros['cliente']) ? $filtros['cliente'] : '') ?>">
        </div>
        <div class="col-md-2 d-grid"><button class="btn btn-dulce"><i class="bi bi-funnel"></i> Filtrar</button></div>
    </div>
</form>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Cliente</th><th>Producto</th><th>Entrega</th><th>Estado</th><th class="text-end">Total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ((isset($pedidos) ? $pedidos : array()) as $p): ?>
                <tr>
                    <td>#<?= (int) $p['id_pedido'] ?></td>
                    <td><?= e($p['cliente']) ?><div class="small text-muted"><?= e($p['telefono']) ?></div></td>
                    <td><?= e($p['producto']) ?></td>
                    <td><?= fecha($p['fecha_entrega']) ?></td>
                    <td><?= badgeEstado($p['estado']) ?></td>
                    <td class="text-end"><?= bs($p['total']) ?></td>
                    <td class="text-end"><a href="<?= url('pedidos/ver/' . (int) $p['id_pedido']) ?>" class="btn btn-sm btn-outline-dulce">Gestionar</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($pedidos)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No hay pedidos con esos filtros.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
