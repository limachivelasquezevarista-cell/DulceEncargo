<?php
$p = isset($producto) ? $producto : [];
$scripts = [
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    url('js/mapa.js'),
    url('js/pedido.js'),
];
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<nav aria-label="breadcrumb"><ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= url('catalogo') ?>">Catálogo</a></li>
    <li class="breadcrumb-item active"><?= e(isset($p['nombre']) ? $p['nombre'] : '') ?></li>
</ol></nav>

<form method="post" action="<?= url('pedidos/guardar') ?>" id="form-pedido" class="needs-validation" novalidate
      data-precio-base="<?= e(isset($p['precio_base']) ? $p['precio_base'] : 0) ?>"
      data-fechas-llenas='<?= e(json_encode(isset($fechasLlenas) ? $fechasLlenas : [])) ?>'>
    <?= csrf() ?>
    <input type="hidden" name="id_producto" value="<?= (int) (isset($p['id_producto']) ? $p['id_producto'] : 0) ?>">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
                <h1 class="h4 fw-bold mb-1">Personaliza tu <?= e(mb_strtolower(isset($p['nombre']) ? $p['nombre'] : 'pedido')) ?></h1>
                <p class="text-muted small">Precio base: <?= bs(isset($p['precio_base']) ? $p['precio_base'] : 0) ?>. Cada opción suma su recargo.</p>
                <?php foreach ((isset($opciones) ? $opciones : array()) as $tipo => $lista): ?>
                    <div class="mb-3">
                        <div class="fw-semibold mb-2"><?= e($tipo) ?></div>
                        <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($lista as $i => $o): ?>
                            <input type="radio" class="btn-check opcion" name="opciones[<?= e($tipo) ?>]" id="op<?= (int) $o['id_opcion'] ?>"
                                   value="<?= (int) $o['id_opcion'] ?>" data-recargo="<?= e($o['recargo']) ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                            <label class="btn btn-outline-dulce btn-opcion" for="op<?= (int) $o['id_opcion'] ?>">
                                <?= e($o['nombre']) ?><?php if ($o['recargo'] > 0): ?><small class="d-block">+ <?= bs($o['recargo']) ?></small><?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label" for="cantidad">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" max="10" value="1" required>
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label" for="dedicatoria">Dedicatoria (opcional)</label>
                        <input class="form-control" id="dedicatoria" name="dedicatoria" maxlength="120" placeholder="Ej: Feliz cumpleaños, Ana">
                    </div>
                </div>
            </div></div>

            <div class="card border-0 shadow-sm"><div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3"><i class="bi bi-geo-alt"></i> Entrega</h2>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="fecha_entrega">Fecha de entrega</label>
                        <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        <div class="invalid-feedback" id="msg-fecha">Elige una fecha a partir de mañana.</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="hora_entrega">Hora aproximada</label>
                        <input type="time" class="form-control" id="hora_entrega" name="hora_entrega" min="08:00" max="20:00">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="direccion">Dirección</label>
                        <input class="form-control" id="direccion" name="direccion" maxlength="200" placeholder="Calle, número y zona de Sacaba" required>
                        <div class="invalid-feedback">La dirección es obligatoria.</div>
                    </div>
                    <div class="col-12">
                        <div id="mapa" class="mapa rounded-3"></div>
                        <div class="form-text">Toca el mapa para marcar el lugar exacto de entrega.</div>
                        <input type="hidden" id="latitud" name="latitud"><input type="hidden" id="longitud" name="longitud">
                    </div>
                </div>
            </div></div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm resumen-pedido sticky-lg-top"><div class="card-body p-4">
                <?= fotoProducto(isset($p['foto']) ? $p['foto'] : null) ?>
                <h2 class="h5 fw-bold mt-3"><?= e(isset($p['nombre']) ? $p['nombre'] : '') ?></h2>
                <ul class="list-unstyled small text-muted" id="resumen-opciones"></ul>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total</span><span class="total" id="total"><?= bs(isset($p['precio_base']) ? $p['precio_base'] : 0) ?></span>
                </div>
                <p class="small text-muted mt-2 mb-3">Después de registrar tu pedido podrás coordinar la entrega por WhatsApp.</p>
                <button class="btn btn-dulce w-100 py-2" type="submit"><i class="bi bi-bag-check"></i> Confirmar pedido</button>
            </div></div>
        </div>
    </div>
</form>
