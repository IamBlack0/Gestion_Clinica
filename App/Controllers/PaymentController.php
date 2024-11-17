<?php
// PaymentController.php
class PaymentController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function procesarPago()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $paciente_id = $_POST['paciente_id'] ?? null;
                $fecha_cita = $_POST['fecha_cita'] ?? null;
                $horario = $_POST['horario'] ?? null;

                // Primero insertar o actualizar el historial médico
                $queryHistorialMedico = "INSERT INTO historial_medico (
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

                $stmtHistorialMedico = $this->db->prepare($queryHistorialMedico);

                // Bind todos los parámetros del historial médico
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
                    $stmtHistorialMedico->bindValue($param, $value);
                }

                if (!$stmtHistorialMedico->execute()) {
                    throw new Exception("Error al guardar el historial médico");
                }

                // Obtener el medico_id
                $queryMedico = "SELECT medico_id FROM citas 
                           WHERE paciente_id = :paciente_id 
                           AND fecha_cita = :fecha_cita 
                           AND horario = :horario";

                $stmtMedico = $this->db->prepare($queryMedico);
                $stmtMedico->bindParam(':paciente_id', $paciente_id);
                $stmtMedico->bindParam(':fecha_cita', $fecha_cita);
                $stmtMedico->bindParam(':horario', $horario);
                $stmtMedico->execute();
                $medico = $stmtMedico->fetch(PDO::FETCH_ASSOC);

                if (!$medico) {
                    throw new Exception("No se encontró la cita correspondiente");
                }

                // Actualizar o insertar en historial_citas
                $queryHistorialCitas = "UPDATE historial_citas 
                      SET estado_cita = 'completada'
                      WHERE paciente_id = :paciente_id 
                      AND fecha_cita = :fecha_cita";

                $stmtHistorialCitas = $this->db->prepare($queryHistorialCitas);
                $stmtHistorialCitas->bindParam(':paciente_id', $paciente_id);
                $stmtHistorialCitas->bindParam(':fecha_cita', $fecha_cita);

                if (!$stmtHistorialCitas->execute()) {
                    throw new Exception("Error al actualizar el estado de la cita");
                }

                // Procesar el pago manteniendo estado pendiente
                $montoConsulta = 50.00;
                $montoInsumos = 0.00;
                $metodoPago = $_POST['metodo_pago'];
                $formaPago = $_POST['forma_pago'];
                $numeroComprobante = $_POST['numero_comprobante'] ?? null;

                $queryPago = "INSERT INTO pagos (
    historial_cita_id, 
    monto_consulta,
    monto_insumos,
    metodo_pago,
    forma_pago,
    numero_comprobante
) SELECT 
    hc.id,
    :monto_consulta,
    :monto_insumos,
    :metodo_pago,
    :forma_pago,
    :numero_comprobante
FROM historial_citas hc
WHERE hc.paciente_id = :paciente_id
AND hc.fecha_cita = :fecha_cita";

                $stmtPago = $this->db->prepare($queryPago);
                $stmtPago->bindParam(':paciente_id', $paciente_id);
                $stmtPago->bindParam(':fecha_cita', $fecha_cita);
                $stmtPago->bindParam(':monto_consulta', $montoConsulta);
                $stmtPago->bindParam(':monto_insumos', $montoInsumos);
                $stmtPago->bindParam(':metodo_pago', $metodoPago);
                $stmtPago->bindParam(':forma_pago', $formaPago);
                $stmtPago->bindParam(':numero_comprobante', $numeroComprobante);

                if (!$stmtPago->execute()) {
                    throw new Exception("Error al procesar el pago");
                }

                $this->db->commit();
                echo json_encode(['success' => true]);
                exit();

            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error en procesarPago: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit();
            }
        }
    }



}