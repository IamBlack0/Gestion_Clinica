<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../Config/DataBase.php';

// Obtener el método HTTP y la ruta
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$endpoint = $request[0] ?? '';

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Manejar las diferentes rutas
try {
    switch ($endpoint) {
        case 'citas':
            handleCitas($method, $db, $request);
            break;
        case 'medicos':
            handleMedicos($method, $db, $request);
            break;
        case 'pacientes':
            handlePacientes($method, $db, $request);
            break;
        default:
            throw new Exception('Endpoint no encontrado', 404);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleCitas($method, $db, $request)
{
    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // Obtener una cita específica
                $query = "SELECT c.*, p.nombre as paciente_nombre, p.apellido as paciente_apellido,
                         m.nombre as medico_nombre, m.apellido as medico_apellido
                         FROM citas c
                         JOIN pacientes p ON c.paciente_id = p.id
                         JOIN colaboradores m ON c.medico_id = m.id
                         WHERE c.id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$request[1]]);
                $cita = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($cita);
            } else {
                // Listar todas las citas
                $query = "SELECT c.*, p.nombre as paciente_nombre, p.apellido as paciente_apellido,
                         m.nombre as medico_nombre, m.apellido as medico_apellido
                         FROM citas c
                         JOIN pacientes p ON c.paciente_id = p.id
                         JOIN colaboradores m ON c.medico_id = m.id";
                $stmt = $db->query($query);
                $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($citas);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $query = "INSERT INTO citas (paciente_id, medico_id, fecha_cita, horario, razon) 
                     VALUES (:paciente_id, :medico_id, :fecha_cita, :horario, :razon)";
            $stmt = $db->prepare($query);
            if ($stmt->execute($data)) {
                http_response_code(201);
                echo json_encode(['message' => 'Cita creada', 'id' => $db->lastInsertId()]);
            }
            break;

        default:
            throw new Exception('Método no permitido', 405);
    }
}

function handleMedicos($method, $db, $request)
{
    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // Obtener un médico específico
                $query = "SELECT * FROM colaboradores WHERE id = ? AND rol_id = 2";
                $stmt = $db->prepare($query);
                $stmt->execute([$request[1]]);
                $medico = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($medico);
            } else {
                // Listar todos los médicos
                $query = "SELECT * FROM colaboradores WHERE rol_id = 2";
                $stmt = $db->query($query);
                $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($medicos);
            }
            break;

        default:
            throw new Exception('Método no permitido', 405);
    }
}

function handlePacientes($method, $db, $request)
{
    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // Obtener un paciente específico
                $query = "SELECT * FROM pacientes WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$request[1]]);
                $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($paciente);
            } else {
                // Listar todos los pacientes
                $query = "SELECT * FROM pacientes";
                $stmt = $db->query($query);
                $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($pacientes);
            }
            break;

        default:
            throw new Exception('Método no permitido', 405);
    }
}