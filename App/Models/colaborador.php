<?php
require_once 'User.php';

class Colaborador extends User {
    private $tableColaboradores = 'colaboradores';
    public $nombre;
    public $apellido;
    public $rol_id;
    public $especialidad_id;
    public $fecha_contratacion;

    public function registro() {
        try {
            // Iniciar una transacción
            $this->conn->beginTransaction();
    
            // Consulta SQL para insertar un nuevo colaborador
            $queryColaborador = "INSERT INTO " . $this->tableColaboradores . " (usuario_id, rol_id, nombre, apellido, especialidad_id, fecha_contratacion) VALUES (:usuario_id, :rol_id, :nombre, :apellido, :especialidad_id, :fecha_contratacion)";
            $stmtColaborador = $this->conn->prepare($queryColaborador);
    
            // Enlazar los parámetros
            $stmtColaborador->bindParam(':usuario_id', $this->id);
            $stmtColaborador->bindParam(':rol_id', $this->rol_id);
            $stmtColaborador->bindParam(':nombre', $this->nombre);
            $stmtColaborador->bindParam(':apellido', $this->apellido);
            $stmtColaborador->bindParam(':especialidad_id', $this->especialidad_id);
            $stmtColaborador->bindParam(':fecha_contratacion', $this->fecha_contratacion);
    
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