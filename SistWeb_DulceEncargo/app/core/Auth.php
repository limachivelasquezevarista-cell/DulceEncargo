<?php
/**
 * Manejo de sesión, roles y token CSRF.
 * Roles del sistema: 'administrador' y 'cliente'.
 */
class Auth
{
    const ADMIN   = 'administrador';
    const CLIENTE = 'cliente';

    public static function iniciarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);   // JavaScript no puede leer la cookie de sesión
            ini_set('session.use_only_cookies', 1);
            session_start();
        }
    }

    /** Nombre del usuario que inició sesión (para saludos en las vistas). */
    public static function nombre()
    {
        return isset($_SESSION['usuario']['nombres']) ? $_SESSION['usuario']['nombres'] : '';
    }

    /** Guarda los datos del usuario en sesión después de un login correcto. */
    public static function login(array $usuario)
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'      => (int) $usuario['id_usuario'],
            'nombres' => $usuario['nombres'],
            'rol'     => $usuario['rol'],
        ];
    }

    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function usuario()
    {
        return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
    }

    public static function id()
    {
        return isset($_SESSION['usuario']['id']) ? $_SESSION['usuario']['id'] : null;
    }

    public static function rol()
    {
        return isset($_SESSION['usuario']['rol']) ? $_SESSION['usuario']['rol'] : null;
    }

    public static function esAdmin()
    {
        return self::rol() === self::ADMIN;
    }

    // ---------- Protección CSRF para formularios ----------

    public static function tokenCsrf()
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function verificarCsrf($token)
    {
        return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
    }
}
