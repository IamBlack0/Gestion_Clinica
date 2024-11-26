<?php

class RecetasController
{
    private $db;
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function verRecetas()
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

            // Obtener las recetas del paciente del historial médico
            $query = "SELECT 
                r.fecha_emision,
                r.tratamiento,
                c.nombre as medico_nombre,
                c.apellido as medico_apellido,
                f.firma AS medico_firma
                FROM recetas r
                INNER JOIN colaboradores c ON r.medico_id = c.id
                LEFT JOIN firmas_recetas f ON r.medico_id = f.medico_id
                WHERE r.paciente_id = :paciente_id
                ORDER BY r.fecha_emision DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':paciente_id', $paciente['id'], PDO::PARAM_INT);
            $stmt->execute();
            $recetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require_once __DIR__ . '/../Views/verRecetas.php';
        } catch (Exception $e) {
            error_log("Error en verRecetasPaciente: " . $e->getMessage());
            header('Location: ./dashboard?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
    public function procesarReceta()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                // Obtener datos del formulario
                $paciente_id = $_POST['paciente_id'] ?? null;
                $medico_id = $_POST['medico_id'] ?? null;
                $tratamiento = $_POST['tratamiento'] ?? null;

                // Validar datos requeridos
                if (!$paciente_id || !$medico_id || !$tratamiento) {
                    throw new Exception("Faltan datos requeridos");
                }

                // Insertar nueva receta
                $query = "INSERT INTO recetas (paciente_id, medico_id, tratamiento) VALUES (:paciente_id, :medico_id, :tratamiento)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':paciente_id', $paciente_id, PDO::PARAM_INT);
                $stmt->bindParam(':medico_id', $medico_id, PDO::PARAM_INT);
                $stmt->bindParam(':tratamiento', $tratamiento, PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar la receta");
                }

                $this->db->commit();
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit();
            } catch (Exception $e) {
                $this->db->rollBack();
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit();
            }
        }
    }
}
