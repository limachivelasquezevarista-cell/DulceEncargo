<?php
/**
 * Conexión única a MySQL con PDO (patrón Singleton).
 * Todas las consultas del sistema usan esta conexión.
 */
class Database
{
    private static $instancia = null;

    private function __construct() {}

    public static function conectar()
    {
        if (self::$instancia === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            try {
                self::$instancia = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // consultas preparadas reales (contra inyección SQL)
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Error de conexión con la base de datos.');
            }
        }
        return self::$instancia;
    }
}
