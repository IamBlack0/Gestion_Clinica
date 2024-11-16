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
        // Obtener todos los médicos de la especialidad
        $query = "SELECT DISTINCT c.id, c.nombre, c.apellido
              FROM colaboradores c
              WHERE c.especialidad_id = :especialidadId";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':especialidadId', $especialidadId, PDO::PARAM_INT);
        $stmt->execute();
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener las citas existentes para cada médico en la fecha especificada
        $queryHorarios = "SELECT medico_id, TIME_FORMAT(horario, '%h:%i %p') as horario
                      FROM citas
                      WHERE fecha_cita = :fecha
                      AND medico_id IN (SELECT id FROM colaboradores WHERE especialidad_id = :especialidadId)";

        $stmtHorarios = $this->db->prepare($queryHorarios);
        $stmtHorarios->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmtHorarios->bindParam(':especialidadId', $especialidadId, PDO::PARAM_INT);
        $stmtHorarios->execute();
        $citasOcupadas = $stmtHorarios->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        // Preparar la respuesta
        $medicosDisponibles = [];
        foreach ($medicos as $medico) {
            $medicosDisponibles[] = [
                'id' => $medico['id'],
                'nombre' => $medico['nombre'],
                'apellido' => $medico['apellido']
            ];
        }

        return [
            'medicos' => array_values($medicosDisponibles)
        ];
    }

    public function obtenerHorariosDisponibles($medicoId, $fecha, $especialidadId)
    {
        // Consultar horarios ocupados para la fecha específica
        $query = "SELECT TIME_FORMAT(horario, '%h:%i %p') as horario 
              FROM citas 
              WHERE medico_id = :medicoId 
              AND fecha_cita = :fecha";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':medicoId', $medicoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $citasOcupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Generar todos los horarios posibles (8:00 AM a 5:00 PM)
        $horariosDisponibles = [];
        $inicio = strtotime('08:00');
        $fin = strtotime('17:00');

        for ($hora = $inicio; $hora <= $fin; $hora += 1800) { // 1800 segundos = 30 minutos
            $horarioFormateado = date('h:i A', $hora);

            // Solo agregar el horario si no está ocupado para esa fecha específica
            if (!in_array($horarioFormateado, $citasOcupadas)) {
                $horariosDisponibles[] = $horarioFormateado;
            }
        }
        
        // Asegurar que la respuesta sea JSON
        header('Content-Type: application/json');
        echo json_encode(array_values($horariosDisponibles));
        exit();
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

    public function procesarAgendarCitaMedico()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Obtener los datos del formulario
                $paciente_id = $_POST['paciente_id'];
                $especialidad_id = $_POST['especialidad_id'];
                $medico_id = $_POST['medico_id'];
                $horario = $_POST['horario'];
                $razon = $_POST['razon'];
                $fecha_cita = $_POST['fecha_cita'];

                // Iniciar transacción
                $this->db->beginTransaction();

                // 1. Insertar la cita
                $queryCita = "INSERT INTO citas (paciente_id, especialidad_id, medico_id, horario, razon, fecha_cita)
                         VALUES (:paciente_id, :especialidad_id, :medico_id, :horario, :razon, :fecha_cita)";

                $stmtCita = $this->db->prepare($queryCita);
                $stmtCita->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
                $stmtCita->bindParam(':especialidad_id', $especialidad_id, PDO::PARAM_INT);
                $stmtCita->bindParam(':medico_id', $medico_id, PDO::PARAM_INT);
                $stmtCita->bindParam(':horario', $horario, PDO::PARAM_STR);
                $stmtCita->bindParam(':razon', $razon, PDO::PARAM_STR);
                $stmtCita->bindParam(':fecha_cita', $fecha_cita, PDO::PARAM_STR);

                if ($stmtCita->execute()) {
                    $this->db->commit();
                    header('Location: ./dashboard?mensaje=suceso');
                    exit();
                } else {
                    $this->db->rollBack();
                    throw new Exception("Error al agendar la cita.");
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                echo "Error: " . $e->getMessage();
            }
        } else {
            header('Location: ./agendarCitaMedico');
            exit();
        }
    }


    public function obtenerHorariosCitas($fecha)
    {
        // Consultar horarios ocupados para la fecha específica, excluyendo citas completadas
        $query = "SELECT DISTINCT TIME_FORMAT(c.horario, '%h:%i %p') as horario 
              FROM citas c
              JOIN historial_citas hc ON c.paciente_id = hc.paciente_id 
              AND c.fecha_cita = hc.fecha_cita
              WHERE c.fecha_cita = :fecha
              AND hc.estado_cita != 'completada'
              ORDER BY c.horario ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $horarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Asegurar que la respuesta sea JSON
        header('Content-Type: application/json');
        echo json_encode($horarios);
        exit();
    }

    public function buscarPacientePorCedula($cedula)
    {
        $query = "SELECT p.id, p.nombre, p.apellido 
              FROM pacientes p
              JOIN informacion_paciente ip ON p.id = ip.paciente_id
              WHERE ip.cedula = :cedula";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
        $stmt->execute();

        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['paciente' => $paciente]);
        exit();
    }

    public function obtenerTodosPacientes()
    {
        $query = "SELECT p.id, p.nombre, p.apellido 
              FROM pacientes p
              JOIN citas c ON p.id = c.paciente_id
              WHERE c.medico_id = :medico_id
              GROUP BY p.id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':medico_id', $_SESSION['medico_id'], PDO::PARAM_INT);
        $stmt->execute();

        $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['pacientes' => $pacientes]);
        exit();
    }
}