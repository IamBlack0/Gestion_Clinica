<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once './Config/DataBase.php';

class MovimientoInventarioController
{
    public function getMovimientos()
    {
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Modificar la consulta para obtener el nombre del producto
            $query = "SELECT 
                m.movimiento_id,
                p.nombre as producto_nombre,
                m.fecha_movimiento,
                m.tipo_movimiento,
                m.cantidad,
                CONCAT(c.nombre, ' ', c.apellido) as usuario
            FROM movimientos_inventario m
            INNER JOIN productos p ON m.producto_id = p.producto_id
            LEFT JOIN colaboradores c ON c.usuario_id = :user_id
            ORDER BY m.fecha_movimiento DESC";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;

        } catch (PDOException $e) {
            error_log("Error en getMovimientos: " . $e->getMessage());
            return [];
        }
    }
}