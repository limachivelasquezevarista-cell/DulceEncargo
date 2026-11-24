<?php
/**
 * Modelo Producto: catálogo de tortas, postres y bocaditos.
 * Tabla: productos (relacionada con categorias).
 * RF02 (gestión) y RF03 (catálogo con búsqueda).
 */
class Producto extends Model
{
    protected $tabla = 'productos';
    protected $clavePrimaria = 'id_producto';

    /**
     * Lista productos con el nombre de su categoría.
     * Filtros opcionales: texto (nombre), id_categoria y solo_activos.
     */
    public function listar(array $filtros = [])
    {
        $sql = "SELECT p.*, c.nombre AS categoria
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE 1 = 1";
        $parametros = [];

        if (!empty($filtros['texto'])) {
            $sql .= " AND p.nombre LIKE :texto";
            $parametros[':texto'] = '%' . $filtros['texto'] . '%';
        }
        if (!empty($filtros['id_categoria'])) {
            $sql .= " AND p.id_categoria = :categoria";
            $parametros[':categoria'] = (int) $filtros['id_categoria'];
        }
        if (!empty($filtros['solo_activos'])) {
            $sql .= " AND p.estado = 1";
        }
        $sql .= " ORDER BY p.estado DESC, c.nombre, p.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    /** Registra un producto nuevo. Devuelve el id creado. */
    public function crear(array $datos)
    {
        $sql = "INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, foto)
                VALUES (:categoria, :nombre, :descripcion, :precio, :foto)";
        $this->db->prepare($sql)->execute([
            ':categoria'   => $datos['id_categoria'],
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio'      => $datos['precio_base'],
            ':foto'        => $datos['foto'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Modifica los datos de un producto. Si no llega foto nueva, se mantiene la anterior. */
    public function actualizar($id, array $datos)
    {
        $sql = "UPDATE productos
                SET id_categoria = :categoria, nombre = :nombre, descripcion = :descripcion, precio_base = :precio";
        $parametros = [
            ':categoria'   => $datos['id_categoria'],
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio'      => $datos['precio_base'],
            ':id'          => $id,
        ];
        if (!empty($datos['foto'])) {
            $sql .= ", foto = :foto";
            $parametros[':foto'] = $datos['foto'];
        }
        $sql .= " WHERE id_producto = :id";
        return $this->db->prepare($sql)->execute($parametros);
    }
}
