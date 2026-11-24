<?php
/**
 * Clase base de todos los controladores.
 * Carga vistas, redirige y controla el acceso por rol.
 */
abstract class Controller
{
    /**
     * Muestra una vista dentro del layout principal.
     * @param string $vista  ruta dentro de app/views sin .php (ej: 'productos/index')
     * @param array  $datos  variables que recibe la vista
     */
    protected function vista($vista, array $datos = [], $conLayout = true)
    {
        extract($datos);
        $archivo = VIEW_PATH . $vista . '.php';
        if (!file_exists($archivo)) {
            http_response_code(404);
            $archivo = VIEW_PATH . 'errores/404.php';
        }
        if ($conLayout) {
            require VIEW_PATH . 'layouts/header.php';
            require $archivo;
            require VIEW_PATH . 'layouts/footer.php';
        } else {
            require $archivo;
        }
    }

    protected function redirigir($ruta)
    {
        header('Location: ' . BASE_URL . ltrim($ruta, '/'));
        exit;
    }

    /** Devuelve datos en JSON (para peticiones con fetch desde JavaScript). */
    protected function json(array $datos, $codigo = 200)
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Exige sesión iniciada. */
    protected function requiereSesion()
    {
        if (!Auth::usuario()) {
            $this->redirigir('auth/login');
        }
    }

    /** Exige un rol concreto; si no lo tiene, muestra error 403. */
    protected function requiereRol($rol)
    {
        $this->requiereSesion();
        if (Auth::rol() !== $rol) {
            http_response_code(403);
            $this->vista('errores/403');
            exit;
        }
    }

    /** Verifica el token CSRF de un formulario enviado por POST. */
    protected function validarPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::verificarCsrf(isset($_POST['csrf']) ? $_POST['csrf'] : null)) {
            http_response_code(400);
            exit('Solicitud no válida.');
        }
    }

    /** Guarda un mensaje para mostrarlo en la siguiente página. */
    protected function mensaje($tipo, $texto)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
    }
}
