<?php
/**
 * RF02 - Gestión de productos (solo administrador).
 * CRUD: registrar, listar, consultar, modificar y desactivar (eliminación lógica).
 */
class ProductoController extends Controller
{
    private $productos;

    public function __construct()
    {
        $this->requiereRol(Auth::ADMIN);
        $this->productos = new Producto();
    }

    /** GET /productos  -> listar con búsqueda por nombre y filtro por categoría */
    public function index()
    {
        $filtros = [
            'texto'        => trim(isset($_GET['q']) ? $_GET['q'] : ''),
            'id_categoria' => isset($_GET['categoria']) ? $_GET['categoria'] : '',
        ];
        $this->vista('productos/index', [
            'titulo'     => 'Productos',
            'productos'  => $this->productos->listar($filtros),
            'categorias' => (new Categoria())->listarActivas(),
            'filtros'    => $filtros,
        ]);
    }

    /** GET /productos/crear  -> formulario vacío */
    public function crear()
    {
        $this->vista('productos/form', [
            'titulo'     => 'Nuevo producto',
            'categorias' => (new Categoria())->listarActivas(),
        ]);
    }

    /** POST /productos/guardar  -> valida, sube la foto y registra */
    public function guardar()
    {
        $this->validarPost();
        $datos   = $this->datosFormulario();
        $errores = $this->validar($datos);

        $foto = $this->subirFoto($errores);
        if ($errores) {
            $this->mensaje('danger', implode(' ', $errores));
            $this->redirigir('productos/crear');
        }

        $datos['foto'] = $foto;
        $this->productos->crear($datos);
        $this->mensaje('success', 'Producto registrado correctamente.');
        $this->redirigir('productos');
    }

    /** GET /productos/editar/{id} */
    public function editar($id = 0)
    {
        $producto = $this->productos->buscarPorId((int) $id);
        if (!$producto) {
            $this->mensaje('danger', 'El producto no existe.');
            $this->redirigir('productos');
        }
        $this->vista('productos/form', [
            'titulo'     => 'Editar producto',
            'producto'   => $producto,
            'categorias' => (new Categoria())->listarActivas(),
        ]);
    }

    /** POST /productos/actualizar/{id} */
    public function actualizar($id = 0)
    {
        $this->validarPost();
        $id       = (int) $id;
        $anterior = $this->productos->buscarPorId($id);
        if (!$anterior) {
            $this->redirigir('productos');
        }

        $datos   = $this->datosFormulario();
        $errores = $this->validar($datos);
        $foto    = $this->subirFoto($errores);
        if ($errores) {
            $this->mensaje('danger', implode(' ', $errores));
            $this->redirigir('productos/editar/' . $id);
        }

        $datos['foto'] = $foto;
        $this->productos->actualizar($id, $datos);
        if ($foto && $anterior['foto'] && file_exists(UPLOAD_PATH . $anterior['foto'])) {
            unlink(UPLOAD_PATH . $anterior['foto']);   // se borra la foto vieja
        }
        $this->mensaje('success', 'Producto actualizado.');
        $this->redirigir('productos');
    }

    /** POST /productos/desactivar/{id}  -> eliminación lógica */
    public function desactivar($id = 0)
    {
        $this->validarPost();
        $this->productos->desactivar((int) $id);
        $this->mensaje('success', 'Producto desactivado. Ya no aparece en el catálogo.');
        $this->redirigir('productos');
    }

    /** POST /productos/activar/{id} */
    public function activar($id = 0)
    {
        $this->validarPost();
        $this->productos->activar((int) $id);
        $this->mensaje('success', 'Producto activado.');
        $this->redirigir('productos');
    }

    // ---------- Funciones de apoyo (privadas: solo se usan dentro de este controlador) ----------

    /** Lee los campos del formulario. */
    private function datosFormulario()
    {
        return [
            'nombre'       => trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
            'id_categoria' => (int) (isset($_POST['id_categoria']) ? $_POST['id_categoria'] : 0),
            'precio_base'  => isset($_POST['precio_base']) ? $_POST['precio_base'] : '',
            'descripcion'  => trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
        ];
    }

    /** Validaciones del servidor. Devuelve la lista de errores. */
    private function validar(array $datos)
    {
        $v = (new Validador())
            ->requerido('nombre', $datos['nombre'], 'nombre')
            ->montoPositivo('precio_base', $datos['precio_base'], 'El precio base');
        $errores = array_values($v->errores());

        if (mb_strlen($datos['nombre']) > 80) {
            $errores[] = 'El nombre puede tener hasta 80 caracteres.';
        }
        if (!(new Categoria())->buscarPorId($datos['id_categoria'])) {
            $errores[] = 'Elige una categoría válida.';
        }
        return $errores;
    }

    /**
     * Sube la foto si el usuario eligió una. Devuelve el nombre del archivo o null.
     * Solo acepta JPG o PNG de hasta 2 MB; si no cumple, agrega un error.
     */
    private function subirFoto(array &$errores)
    {
        if (empty($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $archivo = $_FILES['foto'];
        if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > 2 * 1024 * 1024) {
            $errores[] = 'La foto debe pesar como máximo 2 MB.';
            return null;
        }
        $info = @getimagesize($archivo['tmp_name']);   // comprueba que de verdad sea una imagen
        $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        if (!$info || !isset($tipos[$info['mime']])) {
            $errores[] = 'La foto debe ser una imagen JPG o PNG.';
            return null;
        }
        if ($errores) {
            return null;   // no se guarda la foto si el resto del formulario tiene errores
        }
        $nombre = 'producto_' . date('YmdHis') . '_' . mt_rand(100, 999) . '.' . $tipos[$info['mime']];
        if (!move_uploaded_file($archivo['tmp_name'], UPLOAD_PATH . $nombre)) {
            $errores[] = 'No se pudo guardar la foto.';
            return null;
        }
        return $nombre;
    }
}
