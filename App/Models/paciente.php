<?php
require_once 'User.php';

class Paciente extends User {
    private $tablePacientes = 'pacientes';
    private $tableInformacionPaciente = 'informacion_paciente';
    public $nombre;
    public $apellido;
    public $edad;
    public $sexo;
    public $telefono;
    public $direccion;
    public $tipo_sangre;
    public $nacionalidad_id;
    public $provincia_id;
    public $foto_perfil;

    public function registro() {
        // Consulta SQL para insertar un nuevo paciente
        $queryPaciente = "INSERT INTO " . $this->tablePacientes . " (usuario_id, nombre, apellido) VALUES (:usuario_id, :nombre, :apellido)";
        $stmtPaciente = $this->conn->prepare($queryPaciente);
    
        // Enlazar los parámetros
        $stmtPaciente->bindParam(':usuario_id', $this->id);
        $stmtPaciente->bindParam(':nombre', $this->nombre);
        $stmtPaciente->bindParam(':apellido', $this->apellido);
    
        // Ejecutar la consulta para insertar el paciente
        if ($stmtPaciente->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function obtenerInformacionPaciente() {
        $query = "SELECT edad, sexo, telefono, direccion, tipo_sangre, nacionalidad_id, provincia_id, foto_perfil
                  FROM " . $this->tableInformacionPaciente . "
                  WHERE paciente_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->edad = $row['edad'];
            $this->sexo = $row['sexo'];
            $this->telefono = $row['telefono'];
            $this->direccion = $row['direccion'];
            $this->tipo_sangre = $row['tipo_sangre'];
            $this->nacionalidad_id = $row['nacionalidad_id'];
            $this->provincia_id = $row['provincia_id'];
            $this->foto_perfil = $row['foto_perfil'];
            return true;
        }
        return false;
    }

    public function actualizarInformacionPaciente() {
        $query = "UPDATE " . $this->tableInformacionPaciente . " SET 
                    edad = :edad, 
                    sexo = :sexo, 
                    telefono = :telefono, 
                    direccion = :direccion, 
                    tipo_sangre = :tipo_sangre, 
                    nacionalidad_id = :nacionalidad_id, 
                    provincia_id = :provincia_id, 
                    foto_perfil = :foto_perfil 
                  WHERE paciente_id = :paciente_id";
    
        $stmt = $this->conn->prepare($query);
    
        // Vincular los parámetros
        $stmt->bindParam(':edad', $this->edad);
        $stmt->bindParam(':sexo', $this->sexo);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':tipo_sangre', $this->tipo_sangre);
        $stmt->bindParam(':nacionalidad_id', $this->nacionalidad_id);
        $stmt->bindParam(':provincia_id', $this->provincia_id);
        $stmt->bindParam(':foto_perfil', $this->foto_perfil);
        $stmt->bindParam(':paciente_id', $this->id);
    
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}