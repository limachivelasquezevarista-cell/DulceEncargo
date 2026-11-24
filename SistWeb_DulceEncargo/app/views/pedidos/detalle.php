<?php $p = isset($pedido) ? $pedido : []; $admin = Auth::esAdmin(); ?>
<?php if (!$p): ?>
    <div class="alert alert-warning">El pedido no existe.</div>
<?php else: ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h1 class="h3 fw-bold mb-0">Pedido #<?= (int) $p['id_pedido'] ?> <?= badgeEstado($p['estado']) ?></h1>
    <a href="<?= url('pedidos') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
            <h2 class="h5 fw-bold">Detalle</h2>
            <?php foreach ((isset($p['productos']) ? $p['productos'] : array()) as $d): ?>
                <div class="border rounded-3 p-3 mb-2">
                    <div class="d-flex justify-content-between"><strong><?= (int) $d['cantidad'] ?> × <?= e($d['nombre']) ?></strong><span><?= bs($d['precio_unitario'] * $d['cantidad']) ?></span></div>
                    <div class="small text-muted"><?= e(implode(' · ', isset($d['opciones']) ? $d['opciones'] : [])) ?></div>
                    <?php if (!empty($d['dedicatoria'])): ?><div class="small mt-1"><i class="bi bi-chat-quote"></i> "<?= e($d['dedicatoria']) ?>"</div><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <dl class="row small mt-3 mb-0">
                <?php if ($admin): ?><dt class="col-sm-4">Cliente</dt><dd class="col-sm-8"><?= e(isset($p['cliente']) ? $p['cliente'] : '') ?> · <?= e(isset($p['telefono']) ? $p['telefono'] : '') ?></dd><?php endif; ?>
                <dt class="col-sm-4">Entrega</dt><dd class="col-sm-8"><?= fecha($p['fecha_entrega']) ?> <?= e(substr(isset($p['hora_entrega']) ? $p['hora_entrega'] : '', 0, 5)) ?></dd>
                <dt class="col-sm-4">Dirección</dt><dd class="col-sm-8"><?= e($p['direccion']) ?></dd>
            </dl>
        </div></div>
        <?php if ($admin): ?>
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h2 class="h5 fw-bold">Cambiar estado</h2>
            <form method="post" action="<?= url('pedidos/estado/' . (int) $p['id_pedido']) ?>" class="d-flex gap-2">
                <?= csrf() ?>
                <select class="form-select" name="estado">
                    <?php foreach (Pedido::ESTADOS as $es): ?>
                        <option value="<?= e($es) ?>" <?= $p['estado'] === $es ? 'selected' : '' ?>><?= e(nombreEstado($es)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-dulce text-nowrap">Actualizar</button>
            </form>
        </div></div>
        <?php endif; ?>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h2 class="h5 fw-bold">Resumen y agenda</h2>
            <p>Total del encargo: <strong><?= bs($p['total']) ?></strong></p>
            <p class="small text-muted">La fecha, hora y detalles de la entrega se coordinan por WhatsApp. DulceEncargo no procesa pagos.</p>
            <?php
              $telefonoWhatsApp = preg_replace('/[^0-9]/', '', (string) (new Configuracion())->obtener('telefono_negocio', ''));
              if (strlen($telefonoWhatsApp) === 8) { $telefonoWhatsApp = '591' . $telefonoWhatsApp; }
              $textoWhatsApp = 'Hola, deseo coordinar mi encargo DulceEncargo #' . (int) $p['id_pedido'] . ' para el ' . fecha($p['fecha_entrega']) . ', por un total de ' . bs($p['total']) . '. Quisiera confirmar la entrega.';
            ?>
            <?php if (!$admin && preg_match('/^[0-9]{10,15}$/', $telefonoWhatsApp)): ?>
                <a class="btn btn-success w-100" target="_blank" rel="noopener noreferrer" href="https://wa.me/<?= e($telefonoWhatsApp) ?>?text=<?= rawurlencode($textoWhatsApp) ?>"><i class="bi bi-whatsapp"></i> Agendar por WhatsApp</a>
            <?php elseif (!$admin): ?>
                <div class="alert alert-warning small">El administrador debe configurar el teléfono real del negocio para habilitar WhatsApp.</div>
            <?php endif; ?>
            <?php if ($admin): ?><p class="small">Para coordinar la entrega, contacte al cliente al teléfono registrado: <strong><?= e($p['telefono']) ?></strong>.</p><?php endif; ?>
        </div></div>
    </div>
</div>
<?php endif; ?>
