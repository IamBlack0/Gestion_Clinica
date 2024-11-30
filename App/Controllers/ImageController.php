<?php
class ImageController
{
    private $db;
    private $cloudName = 'TU_NOMBRE_DE_CLOUDINARY';
    private $apiKey = 'TU_API_KEY';
    private $apiSecret = 'TU_API_SECRET';

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function subirFotoPerfil()
    {
        try {
            if (!isset($_FILES['foto_perfil'])) {
                throw new Exception('No se ha enviado ninguna imagen');
            }

            $file = $_FILES['foto_perfil'];
            $fileTmpName = $file['tmp_name'];
            $fileError = $file['error'];

            // Validar el archivo
            if ($fileError !== 0) {
                throw new Exception('Error al subir el archivo: ' . $fileError);
            }

            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png'];
            $fileType = mime_content_type($fileTmpName);
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG y PNG');
            }

            // Generar timestamp y firma
            $timestamp = time();
            $signature = sha1("timestamp=" . $timestamp . $this->apiSecret);

            // Preparar los datos del formulario
            $data = [
                'file' => new CURLFile($fileTmpName),
                'timestamp' => $timestamp,
                'api_key' => $this->apiKey,
                'signature' => $signature
            ];

            // Inicializar cURL
            $ch = curl_init();
            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

            // Configurar cURL con autenticación básica
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret)
            ]);

            // Ejecutar la petición
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('Error en cURL: ' . curl_error($ch));
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                throw new Exception('Error HTTP: ' . $httpCode . ' Response: ' . $response);
            }

            curl_close($ch);

            // Decodificar respuesta
            $result = json_decode($response, true);
            if (!$result) {
                throw new Exception('Error al decodificar la respuesta: ' . json_last_error_msg());
            }

            if (!isset($result['secure_url'])) {
                throw new Exception('Error al obtener la URL de la imagen');
            }

            $imageUrl = $result['secure_url'];

            // Actualizar la base de datos
            $query = "UPDATE informacion_paciente 
                     SET foto_perfil = :foto_perfil 
                     WHERE paciente_id = (
                         SELECT id FROM pacientes WHERE usuario_id = :user_id
                     )";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':foto_perfil', $imageUrl);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);

            if (!$stmt->execute()) {
                throw new Exception('Error al actualizar la base de datos');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Imagen subida correctamente',
                'path' => $imageUrl
            ]);

        } catch (Exception $e) {
            error_log("Error en subirFotoPerfil: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}