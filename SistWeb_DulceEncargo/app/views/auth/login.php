<?php require VIEW_PATH . 'layouts/head.php'; ?>
<body class="auth-fondo">
<main class="auth-layout">
    <?php require VIEW_PATH . 'layouts/auth_portada.php'; ?>

    <section class="auth-lado-form">
        <div class="card auth-card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="<?= url('img/logo.jpg') ?>" alt="Logo DulceEncargo" class="auth-logo-img">
                    <h1 class="marca-texto marca-texto--login mt-2 mb-1"><span class="marca-dulce">Dulce</span><span class="marca-encargo">Encargo</span></h1>
                    <div class="marca-lema">Tortas hechas con amor · Sacaba</div>
                    <p class="text-muted small mb-0">Ingresa con tu correo y contraseña</p>
                </div>

                <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                    <div class="alert alert-<?= e($f['tipo']) ?> py-2 small"><?= e($f['texto']) ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= url('auth/autenticar') ?>" class="needs-validation" novalidate>
                    <?= csrf() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="correo">Correo</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="correo" name="correo" placeholder="tucorreo@ejemplo.com"
                                   value="<?= e(isset($correo) ? $correo : '') ?>" autocomplete="username" required>
                            <div class="invalid-feedback">Ingresa un correo válido.</div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold" for="contrasena">Contraseña</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="••••••••"
                                   autocomplete="current-password" required>
                            <button class="btn btn-light border" type="button" id="ver-contrasena" title="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                            <div class="invalid-feedback">Ingresa tu contraseña.</div>
                        </div>
                    </div>
                    <button class="btn btn-dulce w-100 py-2" type="submit"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
                </form>

                <?php if (MOSTRAR_USUARIOS_DEMO): ?>
                    <div class="auth-demo mt-4">
                        <div class="fw-bold mb-1">👉 Usuarios de ejemplo</div>
                        <div><strong>Administrador:</strong> admin@dulceencargo.bo · Admin123</div>
                        <div><strong>Cliente:</strong> maria@correo.com · Cliente123</div>
                    </div>
                <?php endif; ?>

                <p class="text-center small mt-4 mb-0">¿No tienes cuenta? <a href="<?= url('auth/registro') ?>" class="fw-semibold">Crear cuenta</a></p>
            </div>
        </div>
    </section>
</main>
<script src="<?= url('js/validaciones.js') ?>"></script>
<script>
// Mostrar / ocultar la contraseña
document.getElementById('ver-contrasena').addEventListener('click', function () {
    var campo = document.getElementById('contrasena');
    var visible = campo.type === 'text';
    campo.type = visible ? 'password' : 'text';
    this.innerHTML = visible ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
});
</script>
</body>
</html>
