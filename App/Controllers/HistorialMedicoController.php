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
            // Primero obtener el ID del colaborador (médico) basado en el user_id
            $query = "SELECT id FROM colaboradores WHERE usuario_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $medico = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$medico) {
                die('Error: No se encontró el registro del médico.');
            }

            $medico_id = $medico['id'];
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
            $horario = isset($_GET['horario']) ? $_GET['horario'] : null;

            if ($fecha && $horario) {
                $query = "SELECT c.id, p.id AS paciente_id, p.nombre AS paciente_nombre, 
          p.apellido AS paciente_apellido, c.fecha_cita, c.horario, c.razon,
          hc.id AS historial_cita_id
          FROM citas c
          JOIN pacientes p ON c.paciente_id = p.id
          LEFT JOIN historial_citas hc ON c.paciente_id = hc.paciente_id 
          AND c.fecha_cita = hc.fecha_cita
          WHERE c.medico_id = :medico_id 
          AND c.fecha_cita = :fecha
          AND TIME_FORMAT(c.horario, '%h:%i %p') = :horario";

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':medico_id', $medico_id, PDO::PARAM_INT);
                $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':horario', $horario, PDO::PARAM_STR);
                $stmt->execute();
                $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($citas) > 0) {
                    $paciente_id = $citas[0]['paciente_id'];
                    $informacionPaciente = $this->obtenerInformacionPaciente($paciente_id);
                }
            }

            require_once __DIR__ . '/../Views/verCitasMedico.php';
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

        $queryInformacionPaciente = "SELECT ip.*, p.nombre AS provincia_nombre, n.nombre AS nacionalidad_nombre,
                                   ip.cedula, ip.fecha_nacimiento
                            FROM informacion_paciente ip
                            LEFT JOIN provincias p ON ip.provincia_id = p.id
                            LEFT JOIN nacionalidades n ON ip.nacionalidad_id = n.id
                            WHERE ip.paciente_id = :paciente_id";
        $stmtInformacionPaciente = $this->db->prepare($queryInformacionPaciente);
        $stmtInformacionPaciente->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
        $stmtInformacionPaciente->execute();
        $informacion_paciente = $stmtInformacionPaciente->fetch(PDO::FETCH_ASSOC);

        $queryHistorialMedico = "
    SELECT 
        peso, altura, presion_arterial, frecuencia_cardiaca, 
        temperatura, alergias, medicamentos, cirugias, habitos, 
        antecedentes_familiares
    FROM historial_medico
    WHERE paciente_id = :paciente_id
    ORDER BY fecha_registro DESC LIMIT 1";

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
            try {
                $this->db->beginTransaction();

                // Obtener datos del formulario
                $paciente_id = $_POST['paciente_id'] ?? null;
                $fecha_cita = $_POST['fecha_cita'] ?? null;
                $horario = $_POST['horario'] ?? null;

                // Validar datos requeridos
                if (!$paciente_id || !$fecha_cita || !$horario) {
                    throw new Exception("Faltan datos requeridos");
                }

                // Insertar nuevo historial médico
                $query = "INSERT INTO historial_medico (
                paciente_id, 
                peso, altura, presion_arterial, frecuencia_cardiaca,
                temperatura, alergias, medicamentos, cirugias, 
                habitos, antecedentes_familiares,
                motivo_consulta, diagnostico, tratamiento, 
                enfermedades_preexistentes
            ) VALUES (
                :paciente_id,
                :peso, :altura, :presion_arterial, :frecuencia_cardiaca,
                :temperatura, :alergias, :medicamentos, :cirugias,
                :habitos, :antecedentes_familiares,
                :motivo_consulta, :diagnostico, :tratamiento,
                :enfermedades_preexistentes
            )";

                $stmt = $this->db->prepare($query);

                // Bind todos los parámetros
                $params = [
                    ':paciente_id' => $paciente_id,
                    ':peso' => $_POST['peso'] ?? null,
                    ':altura' => $_POST['altura'] ?? null,
                    ':presion_arterial' => $_POST['presion_arterial'] ?? null,
                    ':frecuencia_cardiaca' => $_POST['frecuencia_cardiaca'] ?? null,
                    ':temperatura' => $_POST['temperatura'] ?? null,
                    ':alergias' => $_POST['alergias'] ?? null,
                    ':medicamentos' => $_POST['medicamentos'] ?? null,
                    ':cirugias' => $_POST['cirugias'] ?? null,
                    ':habitos' => $_POST['habitos'] ?? null,
                    ':antecedentes_familiares' => $_POST['antecedentes_familiares'] ?? null,
                    ':motivo_consulta' => $_POST['motivo_consulta'] ?? null,
                    ':diagnostico' => $_POST['diagnostico'] ?? null,
                    ':tratamiento' => $_POST['tratamiento'] ?? null,
                    ':enfermedades_preexistentes' => $_POST['enfermedades_preexistentes'] ?? null
                ];

                foreach ($params as $param => $value) {
                    $stmt->bindValue($param, $value);
                }

                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar el historial médico");
                }

                $this->db->commit();
                return true;

            } catch (Exception $e) {
                $this->db->rollBack();
                error_log($e->getMessage());
                throw $e;
            }
        }
        return false;
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