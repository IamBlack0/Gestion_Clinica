<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión para manejar el inicio de sesión y autenticación
}

// Incluir los archivos necesarios
require_once './Config/DataBase.php';    
require_once __DIR__ . '/../Models/User.php';

/**
 * Clase UserController para manejar las acciones del usuario.
 */
class UserController {
    private $db;
    private $user;

    /**
     * Constructor que inicializa la conexión a la base de datos y el modelo de usuario.
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Método para manejar el registro de un nuevo usuario.
     */
    public function registro() {
        // Verificar si la solicitud es POST (formulario enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asignar los datos del formulario al objeto User
            $this->user->email = $_POST['email'];
            $this->user->password = $_POST['password'];
            $this->user->nombre = $_POST['nombre'];
            $this->user->apellido = $_POST['apellido'];
            $this->user->rol = $_POST['rol_id']; // Asignar el rol de paciente
    
            // Registrar al usuario y redirigir a la página de inicio de sesión si tiene éxito
            if ($this->user->registro()) {
                header('Location: ./login');
            } else {
                echo "Error en el registro.";
            }
        } else {
            // Cargar la vista del formulario de registro si la solicitud no es POST
            require_once __DIR__ . '/../Views/registro.php';
        }
    }

    /**
     * Método para manejar el inicio de sesión del usuario.
     */
    public function login() {
        // Verificar si la solicitud es POST (formulario enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asignar los datos del formulario al objeto User
            $this->user->email = $_POST['email-username'];
            $this->user->password = $_POST['password'];

            // Intentar iniciar sesión y redirigir al panel de administración si tiene éxito
            if ($this->user->login()) {
                // Obtener el rol del usuario
                if ($this->user->obtenerRol()) {
                    $_SESSION['user_id'] = $this->user->id; // Guardar el ID del usuario en la sesión
                    $_SESSION['nombre'] = $this->user->nombre; // Guardar el nombre del usuario en la sesión
                    $_SESSION['apellido'] = $this->user->apellido; // Guardar el apellido del usuario en la sesión
                    $_SESSION['email'] = $this->user->email; // Guardar el correo del usuario en la sesión
                    $_SESSION['rol'] = $this->user->rol; // Guardar el rol del usuario en la sesión
                    header('Location: ./dashboard'); 
                } else {
                    $errorMessage = "No se pudo obtener el rol del usuario.";
                    require_once __DIR__ . '/../Views/login.php';
                }
            } else {
                $errorMessage = "Credenciales incorrectas.";
                require_once __DIR__ . '/../Views/login.php';
            }
        } else {
            // Cargar la vista del formulario de inicio de sesión si la solicitud no es POST
            require_once __DIR__ . '/../Views/login.php';
        }
    }

    /**
     * Método para manejar la obtención de la información del paciente.
     */
    public function obtenerInformacionPaciente() {
        // Verificar si el usuario está autenticado
        if (isset($_SESSION['user_id'])) {
            $this->user->id = $_SESSION['user_id'];
            if ($this->user->obtenerInformacionPaciente()) {
                // Pasar la información del paciente a la vista
                $informacionPaciente = [
                    'edad' => $this->user->edad,
                    'sexo' => $this->user->sexo,
                    'telefono' => $this->user->telefono,
                    'direccion' => $this->user->direccion,
                    'tipo_sangre' => $this->user->tipo_sangre,
                    'nacionalidad_id' => $this->user->nacionalidad_id,
                    'provincia_id' => $this->user->provincia_id,
                    'foto_perfil' => $this->user->foto_perfil
                ];
            } else {
                // Inicializar los campos con valores predeterminados
                $informacionPaciente = [
                    'edad' => '',
                    'sexo' => '',
                    'telefono' => '',
                    'direccion' => '',
                    'tipo_sangre' => '',
                    'nacionalidad_id' => '',
                    'provincia_id' => '',
                    'foto_perfil' => ''
                ];
            }
            require_once __DIR__ . '/../Views/configuracionCuenta.php';
        } else {
            header('Location: ./login');
        }
    }

    /**
     * Método para manejar la actualización de la información del paciente.
     */
    public function actualizarInformacionPaciente() {
        header('Content-Type: application/json'); // Asegurarse de que la respuesta sea JSON
        // Verificar si la solicitud es POST (formulario enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si el usuario está autenticado
            if (isset($_SESSION['user_id'])) {
                $this->user->id = $_SESSION['user_id'];
                // Asignar los datos del formulario al objeto User
                $this->user->edad = $_POST['edad'] ?? null;
                $this->user->sexo = $_POST['sexo'] ?? null;
                $this->user->telefono = $_POST['telefono'] ?? null;
                $this->user->direccion = $_POST['direccion'] ?? null;
                $this->user->tipo_sangre = $_POST['tipo_sangre'] ?? null;
                $this->user->nacionalidad_id = $_POST['nacionalidad_id'] ?? null;
                $this->user->provincia_id = $_POST['provincia_id'] ?? null;
    
                // Manejar la subida de la imagen
                if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['foto_perfil']['tmp_name'];
                    $fileName = $_FILES['foto_perfil']['name'];
                    $fileSize = $_FILES['foto_perfil']['size'];
                    $fileType = $_FILES['foto_perfil']['type'];
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
    
                    // Verificar si el archivo es una imagen JPG o PNG
                    $allowedfileExtensions = array('jpg', 'jpeg', 'png');
                    if (in_array($fileExtension, $allowedfileExtensions)) {
                        // Leer el contenido del archivo
                        $fileContent = file_get_contents($fileTmpPath);
                        // Encriptar el contenido del archivo
                        $encryptedContent = base64_encode($fileContent);
                        $this->user->foto_perfil = $encryptedContent;
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Solo se permiten JPG y PNG.']);
                        return;
                    }
                } else {
                    $this->user->foto_perfil = null;
                }
    
                // Actualizar la información del paciente
                if ($this->user->actualizarInformacionPaciente()) {
                    echo json_encode(['success' => true]);
                    return;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar la información del paciente.']);
                    return;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
                return;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
            return;
        }
    }

    /**
     * Método para cerrar sesión.
     */
    public function logout() {
        session_destroy(); // Destruir la sesión actual
        header('Location: ./login'); // Redirigir al formulario de inicio de sesión
    }
}