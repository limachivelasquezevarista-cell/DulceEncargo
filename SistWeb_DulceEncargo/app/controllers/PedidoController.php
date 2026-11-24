<?php
/**
 * RF04 - Registro de pedidos (cliente): configurador con precio automático y límite diario.
 * RF05 - Gestión de pedidos (administrador): listar, consultar y cambiar estado.
 * RF06 - Búsqueda y filtros de pedidos (administrador).
 * RF07 - Ubicación de entrega con mapa (API Leaflet + OpenStreetMap).
 */
class PedidoController extends Controller
{
    private $pedidos;

    public function __construct()
    {
        $this->requiereSesion();
        $this->pedidos = new Pedido();
    }

    // ===================== CLIENTE Y ADMINISTRADOR =====================

    /** GET /pedidos  -> cliente: "Mis pedidos" | administrador: gestión con filtros */
    public function index()
    {
        if (Auth::esAdmin()) {
            $filtros = [
                'fecha'   => isset($_GET['fecha']) ? $_GET['fecha'] : '',
                'estado'  => isset($_GET['estado']) ? $_GET['estado'] : '',
                'cliente' => trim(isset($_GET['cliente']) ? $_GET['cliente'] : ''),
            ];
            $this->vista('pedidos/gestion', [
                'titulo'  => 'Gestión de pedidos',
                'pedidos' => $this->pedidos->listarConFiltros($filtros),
                'filtros' => $filtros,
                'estados' => Pedido::ESTADOS,
            ]);
            return;
        }

        $this->vista('pedidos/mis_pedidos', [
            'titulo'  => 'Mis pedidos',
            'pedidos' => $this->pedidos->listarPorCliente(Auth::id()),
        ]);
    }

    /** GET /pedidos/ver/{id}  -> detalle (el cliente solo ve sus propios pedidos) */
    public function ver($id = 0)
    {
        $pedido = $this->pedidos->obtenerDetalle((int) $id);
        if ($pedido && !Auth::esAdmin() && (int) $pedido['id_usuario'] !== Auth::id()) {
            http_response_code(403);
            $this->vista('errores/403');
            return;
        }
        $this->vista('pedidos/detalle', [
            'titulo' => 'Pedido #' . (int) $id,
            'pedido' => $pedido,
        ]);
    }

    // ============================ CLIENTE ============================

    /** GET /pedidos/nuevo/{idProducto}  -> configurador del pedido */
    public function nuevo($idProducto = 0)
    {
        $this->requiereRol(Auth::CLIENTE);
        $producto = (new Producto())->buscarPorId((int) $idProducto);
        if (!$producto || !$producto['estado']) {
            $this->mensaje('warning', 'Ese producto no está disponible.');
            $this->redirigir('catalogo');
        }
        $config = new Configuracion();
        $this->vista('pedidos/nuevo', [
            'titulo'       => 'Armar pedido',
            'producto'     => $producto,
            'opciones'     => (new Opcion())->agrupadasPorTipo(),
            'fechasLlenas' => $this->pedidos->fechasLlenas($config->limitePedidosDia()),
        ]);
    }

    /** POST /pedidos/calcular  -> devuelve el precio en JSON, calculado en el servidor */
    public function calcular()
    {
        $this->requiereRol(Auth::CLIENTE);
        $producto = (new Producto())->buscarPorId((int) (isset($_POST['id_producto']) ? $_POST['id_producto'] : 0));
        if (!$producto) {
            $this->json(['error' => 'Producto no encontrado'], 404);
        }
        $ids      = isset($_POST['opciones']) ? (array) $_POST['opciones'] : [];
        $cantidad = max(1, (int) (isset($_POST['cantidad']) ? $_POST['cantidad'] : 1));
        $unitario = (float) $producto['precio_base'] + (new Opcion())->sumarRecargos($ids);
        $this->json(['unitario' => $unitario, 'total' => $unitario * $cantidad]);
    }

    /** POST /pedidos/guardar  -> valida, verifica el cupo del día, recalcula el precio y registra */
    public function guardar()
    {
        $this->requiereRol(Auth::CLIENTE);
        $this->validarPost();

        $idProducto = (int) (isset($_POST['id_producto']) ? $_POST['id_producto'] : 0);
        $volver     = 'pedidos/nuevo/' . $idProducto;
        $producto   = (new Producto())->buscarPorId($idProducto);
        if (!$producto || !$producto['estado']) {
            $this->mensaje('danger', 'Ese producto no está disponible.');
            $this->redirigir('catalogo');
        }

        $datos = [
            'fecha_entrega' => isset($_POST['fecha_entrega']) ? $_POST['fecha_entrega'] : '',
            'hora_entrega'  => isset($_POST['hora_entrega']) ? $_POST['hora_entrega'] : '',
            'direccion'     => trim(isset($_POST['direccion']) ? $_POST['direccion'] : ''),
            'latitud'       => isset($_POST['latitud']) ? $_POST['latitud'] : '',
            'longitud'      => isset($_POST['longitud']) ? $_POST['longitud'] : '',
            'cantidad'      => (int) (isset($_POST['cantidad']) ? $_POST['cantidad'] : 0),
            'dedicatoria'   => trim(isset($_POST['dedicatoria']) ? $_POST['dedicatoria'] : ''),
        ];

        // 1) Validaciones del servidor
        $v = (new Validador())
            ->fechaFutura('fecha_entrega', $datos['fecha_entrega'])
            ->requerido('direccion', $datos['direccion'], 'dirección');
        $errores = array_values($v->errores());
        if ($datos['cantidad'] < 1 || $datos['cantidad'] > 10) {
            $errores[] = 'La cantidad debe estar entre 1 y 10.';
        }
        if (mb_strlen($datos['dedicatoria']) > 120) {
            $errores[] = 'La dedicatoria puede tener hasta 120 caracteres.';
        }
        if ($datos['hora_entrega'] !== '') {
            $hora = substr($datos['hora_entrega'], 0, 5);
            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $datos['hora_entrega']) || $hora < '08:00' || $hora > '20:00') {
                $errores[] = 'La hora de entrega debe estar entre 08:00 y 20:00.';
            }
        }

        // 2) Opciones elegidas: deben existir, estar activas y cubrir los tipos obligatorios
        $modeloOpcion = new Opcion();
        $elegidas = $modeloOpcion->buscarActivas(isset($_POST['opciones']) ? (array) $_POST['opciones'] : []);
        $tiposElegidos = array_map('intval', array_column($elegidas, 'id_tipo'));
        if (count($tiposElegidos) !== count(array_unique($tiposElegidos))) {
            $errores[] = 'Elige solo una opción de cada tipo.';
        }
        foreach ($modeloOpcion->tiposObligatorios() as $idTipo) {
            if (!in_array((int) $idTipo, $tiposElegidos, true)) {
                $errores[] = 'Falta elegir una opción obligatoria (tamaño, sabor o relleno).';
                break;
            }
        }

        // 3) Cupo del día: no se aceptan más pedidos que el límite configurado
        if (!$errores) {
            $limite = (new Configuracion())->limitePedidosDia();
            if ($this->pedidos->contarPorFecha($datos['fecha_entrega']) >= $limite) {
                $errores[] = 'La fecha ' . fecha($datos['fecha_entrega']) . ' ya no tiene cupo. Elige otra fecha.';
            }
        }

        if ($errores) {
            $this->mensaje('danger', implode(' ', $errores));
            $this->redirigir($volver);
        }

        // 4) Precio calculado en el servidor (nunca se confía en el total que envía el navegador)
        $recargos = 0;
        foreach ($elegidas as $op) {
            $recargos += (float) $op['recargo'];
        }
        $unitario = (float) $producto['precio_base'] + $recargos;
        $total    = $unitario * $datos['cantidad'];

        // 5) Registro con transacción
        $idPedido = $this->pedidos->registrar(
            [
                'id_usuario'    => Auth::id(),
                'fecha_entrega' => $datos['fecha_entrega'],
                'hora_entrega'  => $datos['hora_entrega'] !== '' ? $datos['hora_entrega'] : null,
                'direccion'     => $datos['direccion'],
                'latitud'       => is_numeric($datos['latitud']) ? $datos['latitud'] : null,
                'longitud'      => is_numeric($datos['longitud']) ? $datos['longitud'] : null,
                'observaciones' => null,
                'total'         => $total,
            ],
            [
                'id_producto'     => $idProducto,
                'cantidad'        => $datos['cantidad'],
                'precio_unitario' => $unitario,
                'dedicatoria'     => $datos['dedicatoria'] !== '' ? $datos['dedicatoria'] : null,
            ],
            $elegidas
        );

        if (!$idPedido) {
            $this->mensaje('danger', 'No se pudo registrar el pedido. Intenta de nuevo.');
            $this->redirigir($volver);
        }
        $this->mensaje('success', '¡Pedido #' . $idPedido . ' registrado! Total: ' . bs($total) . '. Coordina la entrega con el negocio por WhatsApp.');
        $this->redirigir('pedidos/ver/' . $idPedido);
    }

    // ========================= ADMINISTRADOR =========================

    /** POST /pedidos/estado/{id}  -> cambia el estado del pedido */
    public function estado($id = 0)
    {
        $this->requiereRol(Auth::ADMIN);
        $this->validarPost();
        $ok = $this->pedidos->cambiarEstado((int) $id, isset($_POST['estado']) ? $_POST['estado'] : '');
        $this->mensaje($ok ? 'success' : 'danger', $ok ? 'Estado actualizado.' : 'Estado no válido.');
        $this->redirigir('pedidos/ver/' . (int) $id);
    }
}
