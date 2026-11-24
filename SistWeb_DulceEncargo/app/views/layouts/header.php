<?php require VIEW_PATH . 'layouts/head.php'; $u = Auth::usuario(); ?>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-dulce sticky-top">
    <div class="container">
<a class="navbar-brand marca" href="<?= url('panel') ?>"><img src="<?= url('img/logo.jpg') ?>" alt="" class="marca-logo"><span class="marca-texto"><span class="marca-dulce">Dulce</span><span class="marca-encargo">Encargo</span></span></a>        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <?php if (Auth::esAdmin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('panel') ?>"><i class="bi bi-speedometer2"></i> Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('pedidos') ?>"><i class="bi bi-receipt"></i> Pedidos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('productos') ?>"><i class="bi bi-box-seam"></i> Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('opciones') ?>"><i class="bi bi-sliders"></i> Opciones</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('reportes') ?>"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('configuracion') ?>"><i class="bi bi-gear"></i> Configuración</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('panel') ?>"><i class="bi bi-house"></i> Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('catalogo') ?>"><i class="bi bi-shop"></i> Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('pedidos') ?>"><i class="bi bi-bag-heart"></i> Mis pedidos</a></li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text me-3 small"><i class="bi bi-person-circle"></i> <?= e(isset($u['nombres']) ? $u['nombres'] : '') ?>
                <span class="badge rounded-pill bg-light text-dark ms-1"><?= e(isset($u['rol']) ? $u['rol'] : '') ?></span></span>
            <a class="btn btn-sm btn-outline-light" href="<?= url('auth/logout') ?>"><i class="bi bi-box-arrow-right"></i> Salir</a>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert alert-<?= e($f['tipo']) ?> alert-dismissible fade show"><?= e($f['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
