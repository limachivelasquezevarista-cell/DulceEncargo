<h1 class="h3 fw-bold mb-4"><i class="bi bi-bag-heart"></i> Mis pedidos</h1>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Pedido</th><th>Fecha de entrega</th><th>Estado</th><th class="text-end">Total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ((isset($pedidos) ? $pedidos : array()) as $p): ?>
                <tr>
                    <td><strong>#<?= (int) $p['id_pedido'] ?></strong><div class="small text-muted"><?= e(isset($p['producto']) ? $p['producto'] : '') ?></div></td>
                    <td><?= fecha($p['fecha_entrega']) ?></td>
                    <td><?= badgeEstado($p['estado']) ?></td>
                    <td class="text-end"><?= bs($p['total']) ?></td>
                    <td class="text-end"><a href="<?= url('pedidos/ver/' . (int) $p['id_pedido']) ?>" class="btn btn-sm btn-outline-dulce">Ver</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($pedidos)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aún no tienes pedidos.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
