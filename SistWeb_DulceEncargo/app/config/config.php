<?php
/**
 * Configuración general de DulceEncargo.
 * Cambiar estos valores según el servidor (XAMPP local o hosting).
 */

// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'dulceencargo');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Aplicación
define('APP_NAME', 'DulceEncargo');
define('APP_TIMEZONE', 'America/La_Paz');

// Muestra las cuentas de prueba en el login (poner en false al publicar el sistema)
define('MOSTRAR_USUARIOS_DEMO', true);

// URL base calculada automáticamente (funciona en XAMPP y en hosting)
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$carpeta   = rtrim(str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/')), '/');
define('BASE_URL', $protocolo . '://' . $host . $carpeta . '/');

// Rutas internas
define('APP_PATH', dirname(__DIR__));                 // carpeta app/
define('VIEW_PATH', APP_PATH . '/views/');
define('UPLOAD_PATH', dirname(APP_PATH) . '/public/img/productos/');

// Controlador y método por defecto
define('DEFAULT_CONTROLLER', 'Auth');
define('DEFAULT_METHOD', 'login');

date_default_timezone_set(APP_TIMEZONE);
