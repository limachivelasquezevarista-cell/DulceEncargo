<?php
/**
 * RF03 - Catálogo con búsqueda (cliente).
 * Muestra los productos activos, con búsqueda por nombre y filtro por categoría.
 */
class CatalogoController extends Controller
{
    public function __construct()
    {
        $this->requiereRol(Auth::CLIENTE);
    }

    /** GET /catalogo?q=texto&categoria=id */
    public function index()
    {
        $filtros = [
            'texto'        => isset($_GET['q']) ? $_GET['q'] : '',
            'id_categoria' => isset($_GET['categoria']) ? $_GET['categoria'] : '',
            'solo_activos' => true,
        ];
        $this->vista('catalogo/index', [
            'titulo'     => 'Catálogo',
            'productos'  => (new Producto())->listar($filtros),
            'categorias' => (new Categoria())->listarActivas(),
            'filtros'    => $filtros,
        ]);
    }
}
