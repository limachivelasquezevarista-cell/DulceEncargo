<div class="hero-cliente rounded-4 p-4 p-md-5 mb-4">
    <h1 class="fw-bold">¡Hola, <?= e(Auth::nombre()) ?>!</h1>
    <p class="lead mb-4">Arma tu torta a tu gusto, elige la fecha y nosotros la preparamos.</p>
    <a href="<?= url('catalogo') ?>" class="btn btn-light btn-lg fw-semibold"><i class="bi bi-shop"></i> Ver catálogo</a>
</div>

<h2 class="h5 fw-bold mb-3">Mis últimos pedidos</h2>
<div class="row g-3">
    <?php foreach (array_slice((isset($pedidos) ? $pedidos : array()), 0, 3) as $p): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex justify-content-between"><strong>Pedido #<?= (int) $p['id_pedido'] ?></strong><?= badgeEstado($p['estado']) ?></div>
                <div class="text-muted small mt-1">Entrega: <?= fecha($p['fecha_entrega']) ?></div>
                <div class="mt-2">Total: <strong><?= bs($p['total']) ?></strong></div>
                <a href="<?= url('pedidos/ver/' . (int) $p['id_pedido']) ?>" class="stretched-link"></a>
            </div></div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($pedidos)): ?>
        <div class="col-12"><div class="alert alert-light border">Todavía no tienes pedidos. ¡Empieza desde el catálogo!</div></div>
    <?php endif; ?>
</div>
