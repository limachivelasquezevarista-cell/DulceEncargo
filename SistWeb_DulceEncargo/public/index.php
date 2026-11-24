<?php
/**
 * Punto de entrada único (Front Controller).
 * Todas las peticiones pasan por aquí y el Router decide qué controlador usar.
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/helpers.php';

// Carga automática de clases del núcleo y de los modelos
spl_autoload_register(function ($clase) {
    foreach (['core', 'models', 'controllers'] as $carpeta) {
        $archivo = APP_PATH . "/{$carpeta}/{$clase}.php";
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

Auth::iniciarSesion();

$router = new Router();
$router->despachar();
