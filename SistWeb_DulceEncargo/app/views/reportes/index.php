<h1 class="h3 fw-bold mb-4"><i class="bi bi-file-earmark-pdf"></i> Reportes</h1>
<div class="row"><div class="col-lg-6">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h2 class="h5 fw-bold">Pedidos por fecha</h2>
        <p class="text-muted small">Genera un PDF con los pedidos (sin cancelados) por fecha de entrega, el valor de los encargos y los productos más pedidos.</p>
        <form method="get" action="<?= url('reportes/ventas') ?>" class="needs-validation" novalidate target="_blank">
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label" for="desde">Desde</label>
                    <input type="date" class="form-control" id="desde" name="desde" value="<?= date('Y-m-01') ?>" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="hasta">Hasta</label>
                    <input type="date" class="form-control" id="hasta" name="hasta" value="<?= date('Y-m-d') ?>" data-mayor-igual="desde" required>
                    <div class="invalid-feedback">Debe ser igual o posterior a la fecha inicial.</div>
                </div>
            </div>
            <button class="btn btn-dulce mt-3"><i class="bi bi-download"></i> Generar PDF</button>
        </form>
    </div></div>
</div></div>
