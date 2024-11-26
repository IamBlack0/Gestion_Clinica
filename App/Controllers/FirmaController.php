<?php
// App/Controllers/FirmaController.php
class FirmaController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerFirma($medico_id)
    {
        try {
            $query = "SELECT firma FROM firmas_recetas WHERE medico_id = :medico_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':medico_id', $medico_id, PDO::PARAM_INT);
            $stmt->execute();
            $firma = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($firma) {
                header('Content-Type: application/json');
                echo json_encode(['firma' => $firma['firma']]);
            } else {
                throw new Exception("Firma no encontrada");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
