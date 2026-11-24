<?php
/**
 * Modelo Opcion: tamaños, sabores, rellenos y decoraciones con su recargo.
 * Tablas: opciones y tipos_opcion.
 * RF02 (gestión de opciones) y RF04 (configurador de pedidos).
 */
class Opcion extends Model
{
    protected $tabla = 'opciones';
    protected $clavePrimaria = 'id_opcion';

    /** Lista todas las opciones con el nombre de su tipo (para el administrador). */
    public function listar()
    {
        $sql = "SELECT o.*, t.nombre AS tipo
                FROM opciones o
                INNER JOIN tipos_opcion t ON t.id_tipo = o.id_tipo
                ORDER BY o.id_tipo, o.recargo, o.nombre";
        return $this->db->query($sql)->fetchAll();
    }

    /** Opciones activas agrupadas por tipo: ['Tamaño' => [...], 'Sabor' => [...], ...] */
    public function agrupadasPorTipo()
    {
        $sql = "SELECT o.*, t.nombre AS tipo
                FROM opciones o
                INNER JOIN tipos_opcion t ON t.id_tipo = o.id_tipo
                WHERE o.estado = 1
                ORDER BY o.id_tipo, o.recargo, o.nombre";
        $grupos = [];
        foreach ($this->db->query($sql)->fetchAll() as $opcion) {
            $grupos[$opcion['tipo']][] = $opcion;
        }
        return $grupos;
    }

    /** Lista los tipos de opción (Tamaño, Sabor, Relleno, Decoración). */
    public function tipos()
    {
        return $this->db->query("SELECT * FROM tipos_opcion ORDER BY id_tipo")->fetchAll();
    }

    public function crear(array $datos)
    {
        $sql = "INSERT INTO opciones (id_tipo, nombre, recargo) VALUES (:tipo, :nombre, :recargo)";
        $this->db->prepare($sql)->execute([
            ':tipo'    => $datos['id_tipo'],
            ':nombre'  => $datos['nombre'],
            ':recargo' => $datos['recargo'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar($id, array $datos)
    {
        $sql = "UPDATE opciones SET id_tipo = :tipo, nombre = :nombre, recargo = :recargo WHERE id_opcion = :id";
        return $this->db->prepare($sql)->execute([
            ':tipo'    => $datos['id_tipo'],
            ':nombre'  => $datos['nombre'],
            ':recargo' => $datos['recargo'],
            ':id'      => $id,
        ]);
    }

    /** ¿Ya existe una opción con ese nombre dentro del mismo tipo? (sin contar la que se edita) */
    public function existeNombre($idTipo, $nombre, $excepto = 0)
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM opciones WHERE id_tipo = :tipo AND nombre = :nombre AND id_opcion <> :id"
        );
        $stmt->execute([':tipo' => $idTipo, ':nombre' => $nombre, ':id' => $excepto]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Busca las opciones activas elegidas por el cliente.
     * Devuelve solo las que existen, con su tipo y recargo actual.
     */
    public function buscarActivas(array $idsOpciones)
    {
        $ids = array_values(array_unique(array_map('intval', $idsOpciones)));
        if (!$ids) {
            return [];
        }
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT o.id_opcion, o.id_tipo, o.nombre, o.recargo, t.nombre AS tipo
             FROM opciones o
             INNER JOIN tipos_opcion t ON t.id_tipo = o.id_tipo
             WHERE o.estado = 1 AND o.id_opcion IN ($marcas)"
        );
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    /** Suma los recargos de las opciones elegidas (el precio siempre se calcula en el servidor). */
    public function sumarRecargos(array $idsOpciones)
    {
        $total = 0.0;
        foreach ($this->buscarActivas($idsOpciones) as $opcion) {
            $total += (float) $opcion['recargo'];
        }
        return $total;
    }

    /** Ids de los tipos que el cliente debe elegir siempre (obligatorio = 1). */
    public function tiposObligatorios()
    {
        return $this->db->query("SELECT id_tipo FROM tipos_opcion WHERE obligatorio = 1")->fetchAll(PDO::FETCH_COLUMN);
    }
}
