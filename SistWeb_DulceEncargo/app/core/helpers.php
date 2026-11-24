<?php
/**
 * Funciones de ayuda para las vistas.
 */

/** Escapa texto para mostrarlo en HTML (evita ataques XSS). */
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Formatea un monto en bolivianos. */
function bs($monto)
{
    return 'Bs ' . number_format((float) $monto, 2, ',', '.');
}

/** Formatea una fecha YYYY-MM-DD como DD/MM/YYYY. */
function fecha($fecha)
{
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '';
}

/** Nombre legible de cada estado del pedido. */
function nombreEstado($estado)
{
    $nombres = [
        'pendiente'      => 'Pendiente',
        'confirmado'     => 'Confirmado',
        'en_preparacion' => 'En preparación',
        'entregado'      => 'Entregado',
        'cancelado'      => 'Cancelado',
    ];
    return isset($nombres[$estado]) ? $nombres[$estado] : $estado;
}

/** Etiqueta de color para cada estado del pedido. */
function badgeEstado($estado)
{
    $colores = [
        'pendiente'      => 'warning text-dark',
        'confirmado'     => 'info text-dark',
        'en_preparacion' => 'primary',
        'entregado'      => 'success',
        'cancelado'      => 'secondary',
    ];
    return '<span class="badge bg-' . (isset($colores[$estado]) ? $colores[$estado] : 'light') . '">' . e(nombreEstado($estado)) . '</span>';
}

/** Campo oculto con el token CSRF para todos los formularios POST. */
function csrf()
{
    return '<input type="hidden" name="csrf" value="' . e(Auth::tokenCsrf()) . '">';
}

/** URL completa dentro del sistema. */
function url($ruta = '')
{
    return BASE_URL . ltrim($ruta, '/');
}

/** Imagen del producto o marcador si todavía no tiene foto. */
function fotoProducto($foto)
{
    if ($foto && file_exists(UPLOAD_PATH . $foto)) {
        return '<img src="' . e(url('img/productos/' . $foto)) . '" class="card-img-top producto-foto" alt="">';
    }
    return '<div class="producto-foto producto-foto--vacia"><i class="bi bi-cake2"></i></div>';
}
