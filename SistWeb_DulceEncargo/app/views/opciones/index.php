<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h1 class="h3 fw-bold mb-2 mb-md-0"><i class="bi bi-sliders"></i> Opciones de personalización</h1>
    <a href="<?= url('opciones/crear') ?>" class="btn btn-dulce"><i class="bi bi-plus-lg"></i> Nueva opción</a>
</div>
<div class="row g-3">
<?php foreach ((isset($tipos) ? $tipos : array()) as $t): ?>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold"><?= e($t['nombre']) ?></div>
            <ul class="list-group list-group-flush">
            <?php foreach ((isset($opciones) ? $opciones : array()) as $o): if ((int) $o['id_tipo'] !== (int) $t['id_tipo']) continue; ?>
                <li class="list-group-item d-flex justify-content-between align-items-center <?= $o['estado'] ? '' : 'text-muted' ?>">
                    <span><?= e($o['nombre']) ?><?= $o['estado'] ? '' : ' <span class="badge bg-secondary">Inactiva</span>' ?><br><small class="text-muted">+ <?= bs($o['recargo']) ?></small></span>
                    <span class="text-nowrap">
                        <a href="<?= url('opciones/editar/' . (int) $o['id_opcion']) ?>" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="<?= url(($o['estado'] ? 'opciones/desactivar/' : 'opciones/activar/') . (int) $o['id_opcion']) ?>" class="d-inline" data-confirmar="¿Seguro que deseas cambiar el estado de esta opción?">
                            <?= csrf() ?>
                            <button class="btn btn-sm <?= $o['estado'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $o['estado'] ? 'Desactivar' : 'Activar' ?>"><i class="bi <?= $o['estado'] ? 'bi-slash-circle' : 'bi-check-circle' ?>"></i></button>
                        </form>
                    </span>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endforeach; ?>
</div>
