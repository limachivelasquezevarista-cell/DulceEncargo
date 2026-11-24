<?php
/**
 * Configuración del negocio: límite de pedidos por día (RF04) y WhatsApp del negocio. Solo administrador.
 */
class ConfiguracionController extends Controller
{
    private $config;

    public function __construct()
    {
        $this->requiereRol(Auth::ADMIN);
        $this->config = new Configuracion();
    }

    /** GET /configuracion */
    public function index()
    {
        $this->vista('admin/configuracion', [
            'titulo' => 'Configuración',
            'limite'   => $this->config->limitePedidosDia(),
            'telefono' => $this->config->obtener('telefono_negocio', ''),
        ]);
    }

    /** POST /configuracion/guardar */
    public function guardar()
    {
        $this->validarPost();
        $limite = filter_var(isset($_POST['limite']) ? $_POST['limite'] : '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
        $telefono = trim(isset($_POST['telefono']) ? $_POST['telefono'] : '');
        $v = (new Validador())->telefono('telefono', $telefono);

        if ($limite === false) {
            $this->mensaje('danger', 'El límite debe ser un número entre 1 y 50.');
        } elseif (!$v->esValido()) {
            $this->mensaje('danger', 'WhatsApp del negocio: ' . implode(' ', $v->errores()));
        } else {
            $this->config->guardar('limite_pedidos_dia', (string) $limite);
            $this->config->guardar('telefono_negocio', $telefono);
            $this->mensaje('success', 'Configuración actualizada.');
        }
        $this->redirigir('configuracion');
    }
}
