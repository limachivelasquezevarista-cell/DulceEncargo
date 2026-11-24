<?php
/**
 * RF02 - Gestión de opciones de personalización (solo administrador).
 * Tamaños, sabores, rellenos y decoraciones con su recargo.
 */
class OpcionController extends Controller
{
    private $opciones;

    public function __construct()
    {
        $this->requiereRol(Auth::ADMIN);
        $this->opciones = new Opcion();
    }

    /** GET /opciones */
    public function index()
    {
        $this->vista('opciones/index', [
            'titulo'   => 'Opciones de personalización',
            'opciones' => $this->opciones->listar(),
            'tipos'    => $this->opciones->tipos(),
        ]);
    }

    /** GET /opciones/crear */
    public function crear()
    {
        $this->vista('opciones/form', ['titulo' => 'Nueva opción', 'tipos' => $this->opciones->tipos()]);
    }

    /** POST /opciones/guardar */
    public function guardar()
    {
        $this->validarPost();
        $datos   = $this->datosFormulario();
        $errores = $this->validar($datos, 0);
        if ($errores) {
            $this->mensaje('danger', implode(' ', $errores));
            $this->redirigir('opciones/crear');
        }
        $this->opciones->crear($datos);
        $this->mensaje('success', 'Opción registrada.');
        $this->redirigir('opciones');
    }

    /** GET /opciones/editar/{id} */
    public function editar($id = 0)
    {
        $opcion = $this->opciones->buscarPorId((int) $id);
        if (!$opcion) {
            $this->redirigir('opciones');
        }
        $this->vista('opciones/form', [
            'titulo' => 'Editar opción',
            'opcion' => $opcion,
            'tipos'  => $this->opciones->tipos(),
        ]);
    }

    /** POST /opciones/actualizar/{id} */
    public function actualizar($id = 0)
    {
        $this->validarPost();
        $id      = (int) $id;
        $datos   = $this->datosFormulario();
        $errores = $this->validar($datos, $id);
        if ($errores) {
            $this->mensaje('danger', implode(' ', $errores));
            $this->redirigir('opciones/editar/' . $id);
        }
        $this->opciones->actualizar($id, $datos);
        $this->mensaje('success', 'Opción actualizada.');
        $this->redirigir('opciones');
    }

    /** POST /opciones/desactivar/{id}  -> deja de aparecer en el configurador del cliente */
    public function desactivar($id = 0)
    {
        $this->validarPost();
        $this->opciones->desactivar((int) $id);
        $this->mensaje('success', 'Opción desactivada.');
        $this->redirigir('opciones');
    }

    /** POST /opciones/activar/{id} */
    public function activar($id = 0)
    {
        $this->validarPost();
        $this->opciones->activar((int) $id);
        $this->mensaje('success', 'Opción activada.');
        $this->redirigir('opciones');
    }

    // ---------- Funciones de apoyo (privadas) ----------

    private function datosFormulario()
    {
        return [
            'id_tipo' => (int) (isset($_POST['id_tipo']) ? $_POST['id_tipo'] : 0),
            'nombre'  => trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
            'recargo' => isset($_POST['recargo']) ? $_POST['recargo'] : '',
        ];
    }

    private function validar(array $datos, $idActual)
    {
        $v = (new Validador())->requerido('nombre', $datos['nombre'], 'nombre');
        $errores = array_values($v->errores());

        $tiposValidos = array_column($this->opciones->tipos(), 'id_tipo');
        if (!in_array($datos['id_tipo'], array_map('intval', $tiposValidos), true)) {
            $errores[] = 'Elige un tipo de opción válido.';
        }
        if (!is_numeric($datos['recargo']) || (float) $datos['recargo'] < 0) {
            $errores[] = 'El recargo debe ser 0 o un número mayor.';
        }
        if ($datos['nombre'] !== '' && $this->opciones->existeNombre($datos['id_tipo'], $datos['nombre'], $idActual)) {
            $errores[] = 'Ya existe una opción con ese nombre en ese tipo.';
        }
        return $errores;
    }
}
