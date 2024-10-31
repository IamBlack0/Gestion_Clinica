<?php

class HistorialMedicoController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function verCitasMedico()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $medico_id = $_SESSION['user_id'];
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
            $horario = isset($_GET['horario']) ? $_GET['horario'] : null;

            if ($fecha && $horario) {
                $query = "SELECT c.id, p.id AS paciente_id, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, c.fecha_cita, c.horario, c.razon
                          FROM citas c
                          JOIN pacientes p ON c.paciente_id = p.id
                          WHERE c.medico_id = :medico_id AND c.fecha_cita = :fecha AND c.horario = :horario";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':medico_id', $medico_id, PDO::PARAM_INT);
                $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':horario', $horario, PDO::PARAM_STR);
                $stmt->execute();
                $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($citas) > 0) {
                    $paciente_id = $citas[0]['paciente_id'];
                    $informacionPaciente = $this->obtenerInformacionPaciente($paciente_id);
                } else {
                    $informacionPaciente = null;
                }
            } else {
                $citas = [];
                $informacionPaciente = null;
            }

            require_once __DIR__ . '/../Views/verCitasMedico.php';
        } else {
            header('Location: ./dashboard');
            exit();
        }
    }

    private function obtenerInformacionPaciente($paciente_id)
    {
        $queryUsuario = "SELECT u.email
                         FROM usuarios u
                         JOIN pacientes p ON u.id = p.usuario_id
                         WHERE p.id = :paciente_id";
        $stmtUsuario = $this->db->prepare($queryUsuario);
        $stmtUsuario->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        $queryPaciente = "SELECT * FROM pacientes WHERE id = :paciente_id";
        $stmtPaciente = $this->db->prepare($queryPaciente);
        $stmtPaciente->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
        $stmtPaciente->execute();
        $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

        $queryInformacionPaciente = "SELECT ip.*, p.nombre AS provincia_nombre, n.nombre AS nacionalidad_nombre
                                     FROM informacion_paciente ip
                                     LEFT JOIN provincias p ON ip.provincia_id = p.id
                                     LEFT JOIN nacionalidades n ON ip.nacionalidad_id = n.id
                                     WHERE ip.paciente_id = :paciente_id";
        $stmtInformacionPaciente = $this->db->prepare($queryInformacionPaciente);
        $stmtInformacionPaciente->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
        $stmtInformacionPaciente->execute();
        $informacion_paciente = $stmtInformacionPaciente->fetch(PDO::FETCH_ASSOC);

        $queryHistorialMedico = "SELECT * FROM historial_medico WHERE paciente_id = :paciente_id";
        $stmtHistorialMedico = $this->db->prepare($queryHistorialMedico);
        $stmtHistorialMedico->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
        $stmtHistorialMedico->execute();
        $historial_medico = $stmtHistorialMedico->fetch(PDO::FETCH_ASSOC);

        $queryProvincias = "SELECT id, nombre FROM provincias";
        $stmtProvincias = $this->db->prepare($queryProvincias);
        $stmtProvincias->execute();
        $provincias = $stmtProvincias->fetchAll(PDO::FETCH_ASSOC);

        $queryNacionalidades = "SELECT id, nombre FROM nacionalidades";
        $stmtNacionalidades = $this->db->prepare($queryNacionalidades);
        $stmtNacionalidades->execute();
        $nacionalidades = $stmtNacionalidades->fetchAll(PDO::FETCH_ASSOC);

        return [
            'usuario' => $usuario,
            'paciente' => $paciente,
            'informacion_paciente' => $informacion_paciente,
            'historial_medico' => $historial_medico,
            'provincias' => $provincias,
            'nacionalidades' => $nacionalidades
        ];
    }

    public function procesarHistorialMedico()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paciente_id = $_POST['paciente_id'];
            $peso = $_POST['peso'];
            $altura = $_POST['altura'];
            $presion_arterial = $_POST['presion_arterial'];
            $frecuencia_cardiaca = $_POST['frecuencia_cardiaca'];
            $temperatura = $_POST['temperatura'];
            $alergias = $_POST['alergias'];
            $medicamentos = $_POST['medicamentos'];
            $cirugias = $_POST['cirugias'];
            $habitos = $_POST['habitos'];
            $antecedentes_familiares = $_POST['antecedentes_familiares'];
            $motivo_consulta = $_POST['motivo_consulta'];
            $diagnostico = $_POST['diagnostico'];
            $tratamiento = $_POST['tratamiento'];
            $enfermedades_preexistentes = $_POST['enfermedades_preexistentes'];
    
            $query = "INSERT INTO historial_medico (paciente_id, peso, altura, presion_arterial, frecuencia_cardiaca, temperatura, alergias, medicamentos, cirugias, habitos, antecedentes_familiares, motivo_consulta, diagnostico, tratamiento, enfermedades_preexistentes, fecha_registro)
                      VALUES (:paciente_id, :peso, :altura, :presion_arterial, :frecuencia_cardiaca, :temperatura, :alergias, :medicamentos, :cirugias, :habitos, :antecedentes_familiares, :motivo_consulta, :diagnostico, :tratamiento, :enfermedades_preexistentes, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
            $stmt->bindParam(':peso', $peso, PDO::PARAM_STR);
            $stmt->bindParam(':altura', $altura, PDO::PARAM_STR);
            $stmt->bindParam(':presion_arterial', $presion_arterial, PDO::PARAM_STR);
            $stmt->bindParam(':frecuencia_cardiaca', $frecuencia_cardiaca, PDO::PARAM_INT);
            $stmt->bindParam(':temperatura', $temperatura, PDO::PARAM_STR);
            $stmt->bindParam(':alergias', $alergias, PDO::PARAM_STR);
            $stmt->bindParam(':medicamentos', $medicamentos, PDO::PARAM_STR);
            $stmt->bindParam(':cirugias', $cirugias, PDO::PARAM_STR);
            $stmt->bindParam(':habitos', $habitos, PDO::PARAM_STR);
            $stmt->bindParam(':antecedentes_familiares', $antecedentes_familiares, PDO::PARAM_STR);
            $stmt->bindParam(':motivo_consulta', $motivo_consulta, PDO::PARAM_STR);
            $stmt->bindParam(':diagnostico', $diagnostico, PDO::PARAM_STR);
            $stmt->bindParam(':tratamiento', $tratamiento, PDO::PARAM_STR);
            $stmt->bindParam(':enfermedades_preexistentes', $enfermedades_preexistentes, PDO::PARAM_STR);
    
            if ($stmt->execute()) {
                header('Location: ./dashboard');
                exit();
            } else {
                echo "Error al guardar el historial médico.";
            }
        } else {
            header('Location: ./dashboard');
            exit();
        }
    }

    public function verHistorialMedico()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $paciente_id = $_SESSION['user_id'];
    
            $queryUsuario = "SELECT email FROM usuarios WHERE id = :user_id";
            $stmtUsuario = $this->db->prepare($queryUsuario);
            $stmtUsuario->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmtUsuario->execute();
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
    
            $queryPaciente = "SELECT * FROM pacientes WHERE usuario_id = :user_id";
            $stmtPaciente = $this->db->prepare($queryPaciente);
            $stmtPaciente->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmtPaciente->execute();
            $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
    
            $queryInformacionPaciente = "SELECT ip.*, p.nombre AS provincia_nombre, n.nombre AS nacionalidad_nombre
                                         FROM informacion_paciente ip
                                         LEFT JOIN provincias p ON ip.provincia_id = p.id
                                         LEFT JOIN nacionalidades n ON ip.nacionalidad_id = n.id
                                         WHERE ip.paciente_id = :paciente_id";
            $stmtInformacionPaciente = $this->db->prepare($queryInformacionPaciente);
            $stmtInformacionPaciente->bindParam(':paciente_id', $paciente['id'], PDO::PARAM_INT);
            $stmtInformacionPaciente->execute();
            $informacion_paciente = $stmtInformacionPaciente->fetch(PDO::FETCH_ASSOC);
    
            $queryHistorialMedico = "SELECT * FROM historial_medico WHERE paciente_id = :paciente_id";
            $stmtHistorialMedico = $this->db->prepare($queryHistorialMedico);
            $stmtHistorialMedico->bindParam(':paciente_id', $paciente['id'], PDO::PARAM_INT);
            $stmtHistorialMedico->execute();
            $historial_medico = $stmtHistorialMedico->fetchAll(PDO::FETCH_ASSOC);
    
            require_once __DIR__ . '/../Views/verHistorialMedico.php';
        } else {
            header('Location: ./dashboard');
            exit();
        }
    }



}