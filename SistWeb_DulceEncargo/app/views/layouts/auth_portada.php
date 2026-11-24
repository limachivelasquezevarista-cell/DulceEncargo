<?php
/**
 * Portada de la izquierda en login y registro (temática de repostería).
 * - public/img/login-fondo.jpg : foto de fondo (opcional)
 * - public/img/login-torta.jpg : foto redonda al centro (opcional)
 * Si no hay fotos, se muestra la ilustración de la torta.
 */
$carpetaImg = dirname(APP_PATH) . '/public/img/';
$hayFoto    = file_exists($carpetaImg . 'login-fondo.jpg');
$hayTorta   = file_exists($carpetaImg . 'login-torta.jpg');
$hayDibujo  = file_exists($carpetaImg . 'torta-ilustracion.svg');
?>
<section class="auth-portada <?= $hayFoto ? 'auth-portada--foto' : '' ?>"
         <?php if ($hayFoto): ?>style="background-image: url('<?= e(url('img/login-fondo.jpg')) ?>')"<?php endif; ?>>
    <?php if ($hayTorta): ?>
        <img class="auth-foto-centro" src="<?= e(url('img/login-torta.jpg')) ?>" alt="">
    <?php elseif (!$hayFoto && $hayDibujo): ?>
        <img class="auth-ilustracion" src="<?= e(url('img/torta-ilustracion.svg')) ?>" alt="">
    <?php endif; ?>
    <div class="auth-portada__texto">
        <h2 class="auth-portada__titulo">Tus tortas, a tu gusto 🎂</h2>
        <p class="auth-portada__sub">Encarga tortas y postres personalizados en Sacaba, desde tu celular.</p>
        <div class="auth-chips">
            <span class="auth-chip">🍰 Catálogo</span>
            <span class="auth-chip">🎨 Personaliza</span>
            <span class="auth-chip">📍 Mapa de entrega</span>
            <span class="auth-chip">💬 WhatsApp</span>
            <span class="auth-chip">📄 Reportes</span>
        </div>
    </div>
</section>