<?php
/**
 * Modelo Categoria: tortas, postres, bocaditos.
 * Tabla: categorias.
 */
class Categoria extends Model
{
    protected $tabla = 'categorias';
    protected $clavePrimaria = 'id_categoria';

    /** Lista las categorías activas (para filtros y formularios). */
    public function listarActivas()
    {
        return $this->db->query("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre")->fetchAll();
    }
}
