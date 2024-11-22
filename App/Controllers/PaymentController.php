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

                // Primero, obtener el ID del historial_citas
                $queryHistorial = "SELECT hc.id 
                             FROM historial_citas hc
                             JOIN citas c ON (
                                 c.paciente_id = hc.paciente_id 
                                 AND c.fecha_cita = hc.fecha_cita
                                 AND c.medico_id = hc.medico_id
                             )
                             WHERE hc.paciente_id = :paciente_id 
                             AND hc.fecha_cita = :fecha_cita
                             AND TIME_FORMAT(c.horario, '%h:%i %p') = :horario
                             LIMIT 1";

                $stmtHistorial = $this->db->prepare($queryHistorial);
                $stmtHistorial->bindParam(':paciente_id', $paciente_id);
                $stmtHistorial->bindParam(':fecha_cita', $fecha_cita);
                $stmtHistorial->bindParam(':horario', $horario);
                $stmtHistorial->execute();

                $historialCita = $stmtHistorial->fetch(PDO::FETCH_ASSOC);

                if (!$historialCita) {
                    throw new Exception("No se encontró la cita correspondiente");
                }

                $historialCitaId = $historialCita['id'];

                // Procesar el pago
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
            ) VALUES (
                :historial_cita_id,
                :monto_consulta,
                :monto_insumos,
                :metodo_pago,
                :forma_pago,
                :numero_comprobante
            )";

                $stmtPago = $this->db->prepare($queryPago);
                $stmtPago->bindParam(':historial_cita_id', $historialCitaId);
                $stmtPago->bindParam(':monto_consulta', $montoConsulta);
                $stmtPago->bindParam(':monto_insumos', $montoInsumos);
                $stmtPago->bindParam(':metodo_pago', $metodoPago);
                $stmtPago->bindParam(':forma_pago', $formaPago);
                $stmtPago->bindParam(':numero_comprobante', $numeroComprobante);

                if (!$stmtPago->execute()) {
                    throw new Exception("Error al procesar el pago");
                }

                // Actualizar estado en historial_citas
                $queryUpdate = "UPDATE historial_citas 
                          SET estado_pago = 'pendiente',
                              estado_cita = 'completada'
                          WHERE id = :historial_cita_id";

                $stmtUpdate = $this->db->prepare($queryUpdate);
                $stmtUpdate->bindParam(':historial_cita_id', $historialCitaId);

                if (!$stmtUpdate->execute()) {
                    throw new Exception("Error al actualizar el estado de la cita");
                }

                $this->db->commit();
                header('Content-Type: application/json');
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
    public function verPagosPendientes()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Usuario no autenticado");
            }

            // Obtener ID del paciente
            $queryPaciente = "SELECT id FROM pacientes WHERE usuario_id = :user_id";
            $stmtPaciente = $this->db->prepare($queryPaciente);
            $stmtPaciente->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmtPaciente->execute();
            $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

            if (!$paciente) {
                throw new Exception("No se encontró el paciente");
            }

            // Modificar la consulta para mostrar citas completadas con pago pendiente
            $query = "SELECT DISTINCT 
            c.id, 
            col.nombre as medico_nombre, 
            col.apellido as medico_apellido,
            c.fecha_cita,
            c.horario,
            hc.estado_pago,
            p.metodo_pago
        FROM citas c
        INNER JOIN historial_citas hc ON c.id = hc.cita_id
        INNER JOIN colaboradores col ON c.medico_id = col.id
        INNER JOIN pagos p ON hc.id = p.historial_cita_id
        WHERE c.paciente_id = :paciente_id
        AND hc.estado_cita = 'completada'
        AND hc.estado_pago = 'pendiente'
        ORDER BY c.fecha_cita ASC, c.horario ASC";

            // Agregar logs para depuración
            error_log("Ejecutando consulta para paciente_id: " . $paciente['id']);

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':paciente_id', $paciente['id'], PDO::PARAM_INT);
            $stmt->execute();
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Citas encontradas: " . count($citas));
            error_log("Datos de citas: " . print_r($citas, true));

            require_once __DIR__ . '/../Views/procesarPago.php';

        } catch (Exception $e) {
            error_log("Error en verPagosPendientes: " . $e->getMessage());
            header('Location: ./dashboard?error=' . urlencode($e->getMessage()));
            exit();
        }
    }

    public function actualizarEstadoPago()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $cita_id = $_POST['cita_id'] ?? null;

                if (!$cita_id) {
                    throw new Exception("ID de cita no proporcionado");
                }

                // Obtener el ID del historial_citas usando el cita_id
                $queryHistorial = "SELECT hc.id 
                     FROM historial_citas hc
                     JOIN citas c ON c.id = hc.cita_id
                     WHERE c.id = :cita_id
                     AND hc.estado_pago = 'pendiente'
                     LIMIT 1";

                $stmtHistorial = $this->db->prepare($queryHistorial);
                $stmtHistorial->bindParam(':cita_id', $cita_id);
                $stmtHistorial->execute();

                $historialCita = $stmtHistorial->fetch(PDO::FETCH_ASSOC);

                if (!$historialCita) {
                    throw new Exception("No se encontró la cita correspondiente");
                }

                // Actualizar solo el estado de pago
                $queryUpdate = "UPDATE historial_citas 
                      SET estado_pago = 'pagado'
                      WHERE id = :historial_cita_id";

                $stmtUpdate = $this->db->prepare($queryUpdate);
                $stmtUpdate->bindParam(':historial_cita_id', $historialCita['id']);

                if (!$stmtUpdate->execute()) {
                    throw new Exception("Error al actualizar el estado del pago");
                }

                $this->db->commit();
                echo json_encode(['success' => true]);
                exit();

            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error en actualizarEstadoPago: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit();
            }
        }
    }
}