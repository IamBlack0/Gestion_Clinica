<?php
require_once 'User.php';

class Paciente extends User
{
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

    public function obtenerInformacionPaciente()
    {
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
        $query = "INSERT INTO " . $this->tableInformacionPaciente . " (paciente_id, edad, sexo, telefono, direccion, tipo_sangre, nacionalidad_id, provincia_id, foto_perfil)
                  VALUES (:paciente_id, :edad, :sexo, :telefono, :direccion, :tipo_sangre, :nacionalidad_id, :provincia_id, :foto_perfil)
                  ON DUPLICATE KEY UPDATE
                  edad = VALUES(edad),
                  sexo = VALUES(sexo),
                  telefono = VALUES(telefono),
                  direccion = VALUES(direccion),
                  tipo_sangre = VALUES(tipo_sangre),
                  nacionalidad_id = VALUES(nacionalidad_id),
                  provincia_id = VALUES(provincia_id),
                  foto_perfil = VALUES(foto_perfil)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':paciente_id', $this->id);
        $stmt->bindParam(':edad', $this->edad);
        $stmt->bindParam(':sexo', $this->sexo);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':tipo_sangre', $this->tipo_sangre);
        $stmt->bindParam(':nacionalidad_id', $this->nacionalidad_id);
        $stmt->bindParam(':provincia_id', $this->provincia_id);
        $stmt->bindParam(':foto_perfil', $this->foto_perfil);
    
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar la información del paciente: " . $e->getMessage());
        }
        return false;
    }
}