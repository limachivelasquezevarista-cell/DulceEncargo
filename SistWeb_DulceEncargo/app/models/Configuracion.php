<?php
/**
 * Modelo Configuracion: parámetros del negocio (límite de pedidos por día).
 * Tabla: configuracion (clave / valor).
 */
class Configuracion extends Model
{
    protected $tabla = 'configuracion';
    protected $clavePrimaria = 'clave';

    public function obtener($clave, $porDefecto = null)
    {
        $stmt = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = :clave");
        $stmt->execute([':clave' => $clave]);
        $valor = $stmt->fetchColumn();
        return $valor === false ? $porDefecto : $valor;
    }

    public function guardar($clave, $valor)
    {
        $stmt = $this->db->prepare("UPDATE configuracion SET valor = :valor WHERE clave = :clave");
        return $stmt->execute([':valor' => $valor, ':clave' => $clave]);
    }

    public function limitePedidosDia()
    {
        return (int) $this->obtener('limite_pedidos_dia', 5);
    }
}
