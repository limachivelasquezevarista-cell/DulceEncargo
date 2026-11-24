<?php
/**
 * Modelo Usuario: administrador y clientes.
 * Tabla: usuarios (relacionada con roles).
 */
class Usuario extends Model
{
    protected $tabla = 'usuarios';
    protected $clavePrimaria = 'id_usuario';

    /** Busca un usuario activo por correo, junto con el nombre de su rol. */
    public function buscarPorCorreo($correo)
    {
        $sql = "SELECT u.*, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON r.id_rol = u.id_rol
                WHERE u.correo = :correo AND u.estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch() ?: null;
    }

    /** Verifica correo y contraseña. Devuelve el usuario o null. */
    public function autenticar($correo, $contrasena)
    {
        $usuario = $this->buscarPorCorreo($correo);
        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            return $usuario;
        }
        return null;
    }

    public function existeCorreo($correo)
    {
        $stmt = $this->db->prepare("SELECT 1 FROM usuarios WHERE correo = :correo");
        $stmt->execute([':correo' => $correo]);
        return (bool) $stmt->fetchColumn();
    }

    /** Registra un cliente nuevo (rol 2). La contraseña se guarda cifrada. */
    public function registrarCliente(array $datos)
    {
        $sql = "INSERT INTO usuarios (id_rol, nombres, apellidos, correo, telefono, contrasena)
                VALUES ((SELECT id_rol FROM roles WHERE nombre = 'cliente'),
                        :nombres, :apellidos, :correo, :telefono, :contrasena)";
        $this->db->prepare($sql)->execute([
            ':nombres'    => $datos['nombres'],
            ':apellidos'  => $datos['apellidos'],
            ':correo'     => $datos['correo'],
            ':telefono'   => $datos['telefono'],
            ':contrasena' => password_hash($datos['contrasena'], PASSWORD_DEFAULT),
        ]);
        return (int) $this->db->lastInsertId();
    }
}
