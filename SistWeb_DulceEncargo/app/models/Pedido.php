<?php
/**
 * Modelo Pedido: cabecera, detalle y opciones elegidas.
 * Tablas: pedidos, detalle_pedido, detalle_opcion.
 * RF04 (registro de pedidos), RF05 (gestión), RF06 (filtros), RF08 (reportes).
 */
class Pedido extends Model
{
    protected $tabla = 'pedidos';
    protected $clavePrimaria = 'id_pedido';

    const ESTADOS = ['pendiente', 'confirmado', 'en_preparacion', 'entregado', 'cancelado'];

    /** Nombres de los productos de un pedido, separados por coma (subconsulta reutilizable). */
    const SQL_PRODUCTOS = "(SELECT GROUP_CONCAT(pr.nombre SEPARATOR ', ')
                            FROM detalle_pedido d
                            INNER JOIN productos pr ON pr.id_producto = d.id_producto
                            WHERE d.id_pedido = v.id_pedido)";

    /**
     * Registra el pedido completo dentro de una transacción:
     * 1) pedidos  2) detalle_pedido  3) detalle_opcion
     * Si algo falla, se deshace todo (rollBack) y no queda un pedido a medias.
     */
    public function registrar(array $pedido, array $detalle, array $opciones)
    {
        try {
            $this->db->beginTransaction();

            $this->db->prepare(
                "INSERT INTO pedidos (id_usuario, fecha_entrega, hora_entrega, direccion, latitud, longitud, observaciones, total)
                 VALUES (:usuario, :fecha, :hora, :direccion, :lat, :lng, :obs, :total)"
            )->execute([
                ':usuario'   => $pedido['id_usuario'],
                ':fecha'     => $pedido['fecha_entrega'],
                ':hora'      => $pedido['hora_entrega'],
                ':direccion' => $pedido['direccion'],
                ':lat'       => $pedido['latitud'],
                ':lng'       => $pedido['longitud'],
                ':obs'       => $pedido['observaciones'],
                ':total'     => $pedido['total'],
            ]);
            $idPedido = (int) $this->db->lastInsertId();

            $this->db->prepare(
                "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, dedicatoria)
                 VALUES (:pedido, :producto, :cantidad, :precio, :dedicatoria)"
            )->execute([
                ':pedido'      => $idPedido,
                ':producto'    => $detalle['id_producto'],
                ':cantidad'    => $detalle['cantidad'],
                ':precio'      => $detalle['precio_unitario'],
                ':dedicatoria' => $detalle['dedicatoria'],
            ]);
            $idDetalle = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare(
                "INSERT INTO detalle_opcion (id_detalle, id_opcion, recargo) VALUES (:detalle, :opcion, :recargo)"
            );
            foreach ($opciones as $op) {
                $stmt->execute([':detalle' => $idDetalle, ':opcion' => $op['id_opcion'], ':recargo' => $op['recargo']]);
            }

            $this->db->commit();
            return $idPedido;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    /** Cuenta los pedidos no cancelados para una fecha (control de capacidad diaria). */
    public function contarPorFecha($fecha)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM pedidos WHERE fecha_entrega = :fecha AND estado <> 'cancelado'"
        );
        $stmt->execute([':fecha' => $fecha]);
        return (int) $stmt->fetchColumn();
    }

    /** Fechas que ya alcanzaron el límite diario (para bloquearlas en el calendario). */
    public function fechasLlenas($limite)
    {
        $stmt = $this->db->prepare(
            "SELECT fecha_entrega FROM pedidos
             WHERE estado <> 'cancelado' AND fecha_entrega >= CURDATE()
             GROUP BY fecha_entrega HAVING COUNT(*) >= :limite"
        );
        $stmt->execute([':limite' => (int) $limite]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Pedidos de un cliente (vista "Mis pedidos"). */
    public function listarPorCliente($idUsuario)
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, " . self::SQL_PRODUCTOS . " AS producto
             FROM pedidos v
             WHERE v.id_usuario = :id
             ORDER BY v.fecha_entrega DESC, v.id_pedido DESC"
        );
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetchAll();
    }

    /** Lista de pedidos para el administrador con filtros: fecha, estado y cliente. */
    public function listarConFiltros(array $filtros = [])
    {
        $sql = "SELECT v.*, CONCAT(u.nombres, ' ', u.apellidos) AS cliente, u.telefono,
                       " . self::SQL_PRODUCTOS . " AS producto
                FROM pedidos v
                INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
                WHERE 1 = 1";
        $parametros = [];

        if (!empty($filtros['fecha'])) {
            $sql .= " AND v.fecha_entrega = :fecha";
            $parametros[':fecha'] = $filtros['fecha'];
        }
        if (!empty($filtros['estado']) && in_array($filtros['estado'], self::ESTADOS, true)) {
            $sql .= " AND v.estado = :estado";
            $parametros[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['cliente'])) {
            $sql .= " AND CONCAT(u.nombres, ' ', u.apellidos) LIKE :cliente";
            $parametros[':cliente'] = '%' . $filtros['cliente'] . '%';
        }
        $sql .= " ORDER BY v.fecha_entrega DESC, v.id_pedido DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    /** Pedido completo: cabecera + cliente + productos con sus opciones. */
    public function obtenerDetalle($idPedido)
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, CONCAT(u.nombres, ' ', u.apellidos) AS cliente, u.telefono, u.correo
             FROM pedidos p
             INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
             WHERE p.id_pedido = :id"
        );
        $stmt->execute([':id' => $idPedido]);
        $pedido = $stmt->fetch();
        if (!$pedido) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT d.*, pr.nombre
             FROM detalle_pedido d
             INNER JOIN productos pr ON pr.id_producto = d.id_producto
             WHERE d.id_pedido = :id"
        );
        $stmt->execute([':id' => $idPedido]);
        $productos = $stmt->fetchAll();

        $stmtOp = $this->db->prepare(
            "SELECT o.nombre
             FROM detalle_opcion dop
             INNER JOIN opciones o ON o.id_opcion = dop.id_opcion
             WHERE dop.id_detalle = :detalle
             ORDER BY o.id_tipo"
        );
        foreach ($productos as $i => $d) {
            $stmtOp->execute([':detalle' => $d['id_detalle']]);
            $productos[$i]['opciones'] = $stmtOp->fetchAll(PDO::FETCH_COLUMN);
        }
        $pedido['productos'] = $productos;
        return $pedido;
    }

    public function cambiarEstado($idPedido, $estado)
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE pedidos SET estado = :estado WHERE id_pedido = :id");
        return $stmt->execute([':estado' => $estado, ':id' => $idPedido]);
    }

    /** Próximas entregas (desde hoy) para el panel del administrador. */
    public function proximasEntregas($cantidad = 5)
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, CONCAT(u.nombres, ' ', u.apellidos) AS cliente, " . self::SQL_PRODUCTOS . " AS producto
             FROM pedidos v
             INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
             WHERE v.fecha_entrega >= CURDATE() AND v.estado IN ('pendiente', 'confirmado', 'en_preparacion')
             ORDER BY v.fecha_entrega, v.id_pedido
             LIMIT " . (int) $cantidad
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Resumen para el panel del administrador. */
    public function resumenPanel()
    {
        $sql = "SELECT
                  (SELECT COUNT(*) FROM pedidos WHERE fecha_entrega = CURDATE() AND estado <> 'cancelado') AS hoy,
                  (SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente') AS pendientes,
                  (SELECT COUNT(*) FROM pedidos WHERE estado = 'en_preparacion') AS en_preparacion";
        return $this->db->query($sql)->fetch();
    }

    /**
     * Datos para el reporte PDF de pedidos entre dos fechas de entrega.
     * Devuelve los pedidos (sin cancelados), los totales y los productos más pedidos.
     */
    public function reportePedidos($desde, $hasta)
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, CONCAT(u.nombres, ' ', u.apellidos) AS cliente, " . self::SQL_PRODUCTOS . " AS producto
             FROM pedidos v
             INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
             WHERE v.fecha_entrega BETWEEN :desde AND :hasta AND v.estado <> 'cancelado'
             ORDER BY v.fecha_entrega, v.id_pedido"
        );
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);
        $pedidos = $stmt->fetchAll();

        $total = 0;
        foreach ($pedidos as $p) {
            $total   += (float) $p['total'];
        }

        $stmt = $this->db->prepare(
            "SELECT pr.nombre, SUM(d.cantidad) AS cantidad, SUM(d.cantidad * d.precio_unitario) AS importe
             FROM detalle_pedido d
             INNER JOIN pedidos p ON p.id_pedido = d.id_pedido
             INNER JOIN productos pr ON pr.id_producto = d.id_producto
             WHERE p.fecha_entrega BETWEEN :desde AND :hasta AND p.estado <> 'cancelado'
             GROUP BY pr.id_producto, pr.nombre
             ORDER BY cantidad DESC, importe DESC
             LIMIT 5"
        );
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);

        return [
            'pedidos' => $pedidos,
            'total'   => $total,
            'top'     => $stmt->fetchAll(),
        ];
    }
}
