<?php

class CitasController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerEspecialidades()
    {
        $query = "SELECT * FROM especialidades";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMedicosPorEspecialidad($especialidadId)
    {
        $query = "SELECT id, nombre, apellido FROM colaboradores WHERE especialidad_id = :especialidadId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':especialidadId', $especialidadId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMedicosDisponibles($especialidadId, $fecha)
    {
        $query = "SELECT c.id, c.nombre, c.apellido, h.horario 
                  FROM colaboradores c
                  LEFT JOIN citas h ON c.id = h.medico_id AND h.fecha_cita = :fecha
                  WHERE c.especialidad_id = :especialidadId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':especialidadId', $especialidadId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar los horarios por médico
        $medicosDisponibles = [];
        foreach ($medicos as $medico) {
            if (!isset($medicosDisponibles[$medico['id']])) {
                $medicosDisponibles[$medico['id']] = [
                    'id' => $medico['id'],
                    'nombre' => $medico['nombre'],
                    'apellido' => $medico['apellido'],
                    'horarios' => ['mañana', 'tarde', 'noche']
                ];
            }
            if ($medico['horario']) {
                $index = array_search($medico['horario'], $medicosDisponibles[$medico['id']]['horarios']);
                if ($index !== false) {
                    unset($medicosDisponibles[$medico['id']]['horarios'][$index]);
                }
            }
        }

        // Filtrar médicos que no tienen horarios disponibles
        $medicosDisponibles = array_filter($medicosDisponibles, function ($medico) {
            return !empty($medico['horarios']);
        });

        return [
            'medicos' => array_values($medicosDisponibles)
        ];
    }

    public function obtenerHorariosDisponibles($medicoId, $fecha, $especialidadId)
    {
        $query = "SELECT horario FROM citas WHERE medico_id = :medicoId AND fecha_cita = :fecha";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':medicoId', $medicoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $horariosOcupados = array_column($citas, 'horario');
        $horariosDisponibles = array_diff(['mañana', 'tarde', 'noche'], $horariosOcupados);

        return array_values($horariosDisponibles);
    }

    public function procesarAgendarCita()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener los datos del formulario
            $paciente_id = $_POST['paciente_id'];
            $especialidad_id = $_POST['especialidad_id'];
            $medico_id = $_POST['medico_id'];
            $horario = $_POST['horario'];
            $razon = $_POST['razon'];
            $fecha_cita = $_POST['fecha_cita'];

            // Insertar la cita en la base de datos
            $query = "INSERT INTO citas (paciente_id, especialidad_id, medico_id, horario, razon, fecha_cita)
                      VALUES (:paciente_id, :especialidad_id, :medico_id, :horario, :razon, :fecha_cita)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
            $stmt->bindParam(':especialidad_id', $especialidad_id, PDO::PARAM_INT);
            $stmt->bindParam(':medico_id', $medico_id, PDO::PARAM_INT);
            $stmt->bindParam(':horario', $horario, PDO::PARAM_STR);
            $stmt->bindParam(':razon', $razon, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_cita', $fecha_cita, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Redirigir a la página de inicio con un mensaje de éxito
                header('Location: ./dashboard?mensaje=suceso');
                exit();
            } else {
                // Mostrar un mensaje de error
                echo "Error al agendar la cita.";
            }
        } else {
            // Redirigir al formulario de agendar cita si la solicitud no es POST
            header('Location: ./agendarCita');
            exit();
        }
    }



    public function obtenerHistorialCitas()
    {
        $query = "SELECT hc.id, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, 
                     c.nombre AS medico_nombre, c.apellido AS medico_apellido, 
                     hc.fecha_cita, hc.estado_pago, hc.estado_cita
              FROM historial_citas hc
              JOIN pacientes p ON hc.paciente_id = p.id
              JOIN colaboradores c ON hc.medico_id = c.id
              WHERE p.usuario_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function aceptarCita($citaId)
    {
        $query = "UPDATE historial_citas SET estado_cita = 'aceptada' WHERE id = :citaId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':citaId', $citaId, PDO::PARAM_INT);
        return $stmt->execute();
    }
   
    public function procesarAgendarCitaMedico()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener los datos del formulario
            $paciente_id = $_POST['paciente_id'];
            $especialidad_id = $_POST['especialidad_id'];
            $medico_id = $_POST['medico_id'];
            $horario = $_POST['horario'];
            $razon = $_POST['razon'];
            $fecha_cita = $_POST['fecha_cita'];
    
            // Insertar la cita en la base de datos
            $query = "INSERT INTO citas (paciente_id, especialidad_id, medico_id, horario, razon, fecha_cita)
                      VALUES (:paciente_id, :especialidad_id, :medico_id, :horario, :razon, :fecha_cita)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
            $stmt->bindParam(':especialidad_id', $especialidad_id, PDO::PARAM_INT);
            $stmt->bindParam(':medico_id', $medico_id, PDO::PARAM_INT);
            $stmt->bindParam(':horario', $horario, PDO::PARAM_STR);
            $stmt->bindParam(':razon', $razon, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_cita', $fecha_cita, PDO::PARAM_STR);
    
            if ($stmt->execute()) {
                // Redirigir a la página de inicio con un mensaje de éxito
                header('Location: ./dashboard?mensaje=suceso');
                exit();
            } else {
                // Mostrar un mensaje de error
                echo "Error al agendar la cita.";
            }
        } else {
            // Redirigir al formulario de agendar cita si la solicitud no es POST
            header('Location: ./agendarCitaMedico');
            exit();
        }
    }
}