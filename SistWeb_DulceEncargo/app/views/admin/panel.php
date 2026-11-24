<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Hola, <?= e(Auth::nombre()) ?></h1>
        <p class="text-muted mb-0">Resumen de tu emprendimiento al <?= date('d/m/Y') ?></p>
    </div>
    <a href="<?= url('pedidos') ?>" class="btn btn-dulce mt-2 mt-md-0"><i class="bi bi-receipt"></i> Ver todos los pedidos</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card tarjeta-resumen border-0 shadow-sm">
            <div class="card-body"><i class="bi bi-truck icono bg-rosa"></i>
                <div><div class="numero"><?= (int) (isset($resumen['hoy']) ? $resumen['hoy'] : 0) ?></div><div class="text-muted small">Entregas de hoy</div></div></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card tarjeta-resumen border-0 shadow-sm">
            <div class="card-body"><i class="bi bi-hourglass-split icono bg-crema"></i>
                <div><div class="numero"><?= (int) (isset($resumen['pendientes']) ? $resumen['pendientes'] : 0) ?></div><div class="text-muted small">Pedidos por confirmar</div></div></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card tarjeta-resumen border-0 shadow-sm">
            <div class="card-body"><i class="bi bi-basket icono bg-chocolate"></i>
                <div><div class="numero"><?= (int) (isset($resumen['en_preparacion']) ? $resumen['en_preparacion'] : 0) ?></div><div class="text-muted small">Pedidos en preparación</div></div></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Próximas entregas</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Cliente</th><th>Producto</th><th>Entrega</th><th>Estado</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            <?php foreach ((isset($proximos) ? $proximos : array()) as $p): ?>
                <tr>
                    <td><a href="<?= url('pedidos/ver/' . (int) $p['id_pedido']) ?>">#<?= (int) $p['id_pedido'] ?></a></td>
                    <td><?= e($p['cliente']) ?></td>
                    <td><?= e($p['producto']) ?></td>
                    <td><?= fecha($p['fecha_entrega']) ?></td>
                    <td><?= badgeEstado($p['estado']) ?></td>
                    <td class="text-end"><?= bs($p['total']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($proximos)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No hay entregas programadas.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
