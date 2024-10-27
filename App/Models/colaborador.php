<?php
require_once 'User.php';

class Colaborador extends User {
    private $tableColaboradores = 'colaboradores';
    public $nombre;
    public $apellido;

    public function registro() {
        try {
            // Iniciar una transacción
            $this->conn->beginTransaction();
    
            // Lógica para registrar el usuario
            parent::registro();
    
            // Consulta SQL para insertar un nuevo colaborador
            $queryColaborador = "INSERT INTO " . $this->tableColaboradores . " (usuario_id, nombre, apellido) VALUES (:usuario_id, :nombre, :apellido)";
            $stmtColaborador = $this->conn->prepare($queryColaborador);
    
            // Enlazar los parámetros
            $stmtColaborador->bindParam(':usuario_id', $this->id);
            $stmtColaborador->bindParam(':nombre', $this->nombre);
            $stmtColaborador->bindParam(':apellido', $this->apellido);
    
            // Ejecutar la consulta para insertar el colaborador
            if ($stmtColaborador->execute()) {
                // Confirmar la transacción
                $this->conn->commit();
                return true;
            } else {
                // Revertir la transacción si falla la inserción del colaborador
                $this->conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            // Revertir la transacción en caso de excepción
            $this->conn->rollBack();
            return false;
        }
    }

    // Métodos específicos para colaboradores
}