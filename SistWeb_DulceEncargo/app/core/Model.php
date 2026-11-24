<?php
/**
 * Clase base de todos los modelos.
 * Entrega la conexión PDO y métodos comunes de consulta.
 */
abstract class Model
{
    protected $db;
    protected $tabla;
    protected $clavePrimaria;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /** Busca un registro por su clave primaria. */
    public function buscarPorId($id)
    {
        $sql  = "SELECT * FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Desactivación lógica: no se borra el registro, se pone estado = 0. */
    public function desactivar($id)
    {
        $sql  = "UPDATE {$this->tabla} SET estado = 0 WHERE {$this->clavePrimaria} = :id";
        return $this->db->prepare($sql)->execute([':id' => $id]);
    }

    /** Reactiva un registro desactivado. */
    public function activar($id)
    {
        $sql  = "UPDATE {$this->tabla} SET estado = 1 WHERE {$this->clavePrimaria} = :id";
        return $this->db->prepare($sql)->execute([':id' => $id]);
    }
}
