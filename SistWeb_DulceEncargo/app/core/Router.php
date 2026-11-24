<?php
/**
 * Enrutador: convierte la URL en controlador + método + parámetros.
 * Ejemplo: /productos/editar/5  ->  ProductoController->editar(5)
 */
class Router
{
    /** Alias de URL en plural hacia el nombre del controlador. */
    private $alias = [
        'productos'     => 'Producto',
        'opciones'      => 'Opcion',
        'pedidos'       => 'Pedido',
        'reportes'      => 'Reporte',
        'catalogo'      => 'Catalogo',
        'configuracion' => 'Configuracion',
        'panel'         => 'Panel',
        'auth'          => 'Auth',
    ];

    public function despachar()
    {
        $url    = trim(isset($_GET['url']) ? $_GET['url'] : '', '/');
        $partes = $url === '' ? [] : explode('/', filter_var($url, FILTER_SANITIZE_URL));

        $nombre      = strtolower(isset($partes[0]) ? $partes[0] : '');
        $controlador = (isset($this->alias[$nombre]) ? $this->alias[$nombre] : DEFAULT_CONTROLLER) . 'Controller';
        $metodo      = isset($partes[1]) ? $partes[1] : DEFAULT_METHOD;
        $parametros  = array_slice($partes, 2);

        if ($nombre !== '' && !isset($this->alias[$nombre])) {
            $this->noEncontrado();
        }

        $archivo = APP_PATH . '/controllers/' . $controlador . '.php';
        if (!file_exists($archivo)) {
            $this->noEncontrado();
        }
        require_once $archivo;

        $objeto = new $controlador();
        if ($nombre !== '' && !isset($partes[1])) {
            $metodo = 'index';
        }
        // Solo se pueden llamar desde la URL los métodos PÚBLICOS del controlador
        // (los protegidos/privados como vista() o redirigir() quedan fuera)
        if (!method_exists($objeto, $metodo) || substr($metodo, 0, 1) === '_') {
            $this->noEncontrado();
        }
        $reflexion = new ReflectionMethod($objeto, $metodo);
        if (!$reflexion->isPublic() || $reflexion->isStatic()) {
            $this->noEncontrado();
        }

        call_user_func_array([$objeto, $metodo], $parametros);
    }

    private function noEncontrado()
    {
        http_response_code(404);
        $titulo = 'Página no encontrada';
        require VIEW_PATH . 'layouts/head.php';
        echo '<body class="auth-fondo">';
        require VIEW_PATH . 'errores/404.php';
        echo '</body></html>';
        exit;
    }
}
