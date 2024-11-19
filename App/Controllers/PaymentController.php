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

    public function obtenerSiguienteComprobante()
    {
        try {
            $query = "SELECT numero_comprobante 
                  FROM pagos 
                  WHERE numero_comprobante LIKE 'MDF-%' 
                  ORDER BY CAST(SUBSTRING(numero_comprobante, 5) AS UNSIGNED) DESC 
                  LIMIT 1";

            $stmt = $this->db->query($query);
            $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ultimo) {
                $ultimoNumero = intval(substr($ultimo['numero_comprobante'], 4));
                $siguienteNumero = $ultimoNumero + 1;
            } else {
                $siguienteNumero = 1;
            }

            $nuevoComprobante = sprintf("MDF-%05d", $siguienteNumero);

            echo json_encode(['comprobante' => $nuevoComprobante]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

    public function confirmarPagoEfectivo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $cita_id = $data['cita_id'] ?? null;

                if (!$cita_id) {
                    throw new Exception("ID de cita no proporcionado");
                }

                $this->db->beginTransaction();

                // Actualizar el estado a pagado
                $query = "UPDATE historial_citas 
                     SET estado_pago = 'pagado' 
                     WHERE id = :cita_id";

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':cita_id', $cita_id);

                if (!$stmt->execute()) {
                    throw new Exception("Error al confirmar el pago");
                }

                $this->db->commit();
                echo json_encode(['success' => true]);

            } catch (Exception $e) {
                $this->db->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit();
        }
    }

    

    public function procesarPagoTarjeta()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $cita_id = $data['cita_id'];

                // Aquí irían las validaciones de la tarjeta y la integración
                // con el gateway de pago real

                $this->db->beginTransaction();

                // Actualizar el estado a pagado
                $query = "UPDATE historial_citas 
                     SET estado_pago = 'pagado' 
                     WHERE id = :cita_id";

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':cita_id', $cita_id);

                if (!$stmt->execute()) {
                    throw new Exception("Error al procesar el pago");
                }

                $this->db->commit();
                echo json_encode(['success' => true]);

            } catch (Exception $e) {
                $this->db->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit();
        }
    }

    public function verPagosPendientes()
{
    try {
        $query = "SELECT 
            hc.id,
            c.medico_id,
            col.nombre as medico_nombre,
            col.apellido as medico_apellido,
            c.fecha_cita,
            c.horario,
            c.razon,
            hc.estado_pago,
            p.metodo_pago,
            p.numero_comprobante
        FROM historial_citas hc
        JOIN citas c ON hc.paciente_id = c.paciente_id 
            AND hc.fecha_cita = c.fecha_cita
        JOIN colaboradores col ON c.medico_id = col.id
        LEFT JOIN pagos p ON hc.id = p.historial_cita_id
        WHERE hc.paciente_id = (
            SELECT id FROM pacientes WHERE usuario_id = :user_id
        )
        AND (hc.estado_pago = 'pendiente' OR hc.estado_pago = 'pendiente_efectivo')";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/procesarPago.php';
        
    } catch (Exception $e) {
        error_log("Error en verPagosPendientes: " . $e->getMessage());
        header('Location: ./dashboard?error=Error al cargar los pagos pendientes');
        exit();
    }
}

}