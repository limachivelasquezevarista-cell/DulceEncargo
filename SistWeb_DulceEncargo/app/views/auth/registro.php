<?php require VIEW_PATH . 'layouts/head.php'; $d = isset($datos) ? $datos : []; $err = isset($errores) ? $errores : []; ?>
<body class="auth-fondo">
<main class="auth-layout">
    <?php require VIEW_PATH . 'layouts/auth_portada.php'; ?>
    <section class="auth-lado-form">
            <div class="card auth-card auth-card--ancha shadow-lg border-0">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-bold mb-1"><i class="bi bi-person-plus"></i> Crear cuenta</h1>
                    <p class="text-muted small mb-4">Regístrate para armar y seguir tus pedidos.</p>
                    <form method="post" action="<?= url('auth/registrar') ?>" class="needs-validation" novalidate>
                        <?= csrf() ?>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="nombres">Nombres</label>
                                <input class="form-control <?= isset($err['nombres']) ? 'is-invalid' : '' ?>" id="nombres" name="nombres" value="<?= e(isset($d['nombres']) ? $d['nombres'] : '') ?>" required>
                                <div class="invalid-feedback"><?= e(isset($err['nombres']) ? $err['nombres'] : 'Ingresa tus nombres.') ?></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="apellidos">Apellidos</label>
                                <input class="form-control <?= isset($err['apellidos']) ? 'is-invalid' : '' ?>" id="apellidos" name="apellidos" value="<?= e(isset($d['apellidos']) ? $d['apellidos'] : '') ?>" required>
                                <div class="invalid-feedback"><?= e(isset($err['apellidos']) ? $err['apellidos'] : 'Ingresa tus apellidos.') ?></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="correo">Correo</label>
                                <input type="email" class="form-control <?= isset($err['correo']) ? 'is-invalid' : '' ?>" id="correo" name="correo" value="<?= e(isset($d['correo']) ? $d['correo'] : '') ?>" required>
                                <div class="invalid-feedback"><?= e(isset($err['correo']) ? $err['correo'] : 'Ingresa un correo válido.') ?></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="telefono">Celular</label>
                                <input class="form-control <?= isset($err['telefono']) ? 'is-invalid' : '' ?>" id="telefono" name="telefono" value="<?= e(isset($d['telefono']) ? $d['telefono'] : '') ?>" pattern="[67][0-9]{7}" maxlength="8" required>
                                <div class="invalid-feedback"><?= e(isset($err['telefono']) ? $err['telefono'] : '8 dígitos, empieza con 6 o 7.') ?></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="contrasena">Contraseña</label>
                                <input type="password" class="form-control <?= isset($err['contrasena']) ? 'is-invalid' : '' ?>" id="contrasena" name="contrasena" minlength="8" required>
                                <div class="invalid-feedback"><?= e(isset($err['contrasena']) ? $err['contrasena'] : 'Mínimo 8 caracteres.') ?></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="confirmar">Repetir contraseña</label>
                                <input type="password" class="form-control" id="confirmar" data-igual-a="contrasena" required>
                                <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                            </div>
                        </div>
                        <button class="btn btn-dulce w-100 py-2 mt-4" type="submit">Crear cuenta</button>
                    </form>
                    <p class="text-center small mt-3 mb-0">¿Ya tienes cuenta? <a href="<?= url('auth/login') ?>">Inicia sesión</a></p>
                </div>
            </div>
    </section>
</main>
<script src="<?= url('js/validaciones.js') ?>"></script>
</body>
</html>
