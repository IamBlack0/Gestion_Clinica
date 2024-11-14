<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión para manejar el inicio de sesión y autenticación
}

// Incluir los archivos necesarios
require_once './Config/DataBase.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Paciente.php';
require_once __DIR__ . '/../Models/Colaborador.php';

/**
 * Clase UserController para manejar las acciones del usuario.
 */
class UserController
{
    private $db;
    private $user;
    private $paciente;
    private $colaborador;


    /**
     * Constructor que inicializa la conexión a la base de datos y los modelos de usuario, paciente y colaborador.
     */
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->paciente = new Paciente($this->db);
        $this->colaborador = new Colaborador($this->db);
    }

    /**
     * Método para manejar el registro de un nuevo usuario.
     */
    public function registro()
    {
        // Verificar si la solicitud es POST (formulario enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asignar los datos del formulario al objeto User
            $this->user->email = $_POST['email'];
            $this->user->password = $_POST['password'];
            $this->user->rol = $_POST['rol_id']; // Asignar el rol de paciente o colaborador

            // Registrar al usuario
            if ($this->user->registro()) {
                // Asignar los datos específicos según el rol
                if ($this->user->rol == 1) { // Rol de paciente
                    $this->paciente->id = $this->user->id;
                    $this->paciente->nombre = $_POST['nombre'];
                    $this->paciente->apellido = $_POST['apellido'];
                    if ($this->paciente->registro()) {
                        header('Location: ./login');
                    } else {
                        echo "Error en el registro del paciente.";
                    }
                } elseif ($this->user->rol == 2) { // Rol de colaborador
                    $this->colaborador->id = $this->user->id;
                    $this->colaborador->nombre = $_POST['nombre'];
                    $this->colaborador->apellido = $_POST['apellido'];
                    if ($this->colaborador->registro()) {
                        header('Location: ./login');
                    } else {
                        echo "Error en el registro del colaborador.";
                    }
                }
            } else {
                echo "Error en el registro del usuario.";
            }
        } else {
            // Cargar la vista del formulario de registro si la solicitud no es POST
            require_once __DIR__ . '/../Views/registro.php';
        }
    }

    /**
     * Método para manejar el inicio de sesión del usuario.
     */
    public function login()
    {
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
                    $_SESSION['email'] = $this->user->email; // Guardar el correo del usuario en la sesión
                    $_SESSION['rol'] = $this->user->rol; // Guardar el rol del usuario en la sesión

                    // Obtener el nombre y apellido del usuario
                    if ($this->user->obtenerNombreApellido()) {
                        $_SESSION['nombre'] = $this->user->nombre;
                        $_SESSION['apellido'] = $this->user->apellido;
                    } else {
                        $_SESSION['nombre'] = 'Nombre no disponible';
                        $_SESSION['apellido'] = 'Apellido no disponible';
                    }

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
    public function obtenerInformacionPaciente()
    {
        // Verificar si el usuario está autenticado
        if (isset($_SESSION['user_id'])) {
            $this->paciente->id = $_SESSION['user_id'];
            if ($this->paciente->obtenerInformacionPaciente()) {
                // Pasar la información del paciente a la vista
                $informacionPaciente = [
                    'cedula' => $this->paciente->cedula, // Nuevo campo
                    'fecha_nacimiento' => $this->paciente->fecha_nacimiento, // Nuevo campo
                    'sexo' => $this->paciente->sexo,
                    'telefono' => $this->paciente->telefono,
                    'direccion' => $this->paciente->direccion,
                    'tipo_sangre' => $this->paciente->tipo_sangre,
                    'nacionalidad_id' => $this->paciente->nacionalidad_id,
                    'provincia_id' => $this->paciente->provincia_id,
                    'foto_perfil' => $this->paciente->foto_perfil
                ];
            } else {
                // Inicializar los campos con valores predeterminados
                $informacionPaciente = [
                    'cedula' => '', // Nuevo campo
                    'fecha_nacimiento' => '', // Nuevo campo
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
    public function actualizarInformacionPaciente()
    {
        header('Content-Type: application/json'); // Asegurarse de que la respuesta sea JSON
        // Verificar si la solicitud es POST (formulario enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si el usuario está autenticado
            if (isset($_SESSION['user_id'])) {
                $this->paciente->id = $_SESSION['user_id'];

                // Verificar si el paciente existe en la tabla pacientes
                $queryVerificarPaciente = "SELECT id FROM pacientes WHERE usuario_id = :usuario_id";
                $stmtVerificarPaciente = $this->db->prepare($queryVerificarPaciente);
                $stmtVerificarPaciente->bindParam(':usuario_id', $this->paciente->id);
                $stmtVerificarPaciente->execute();

                if ($stmtVerificarPaciente->rowCount() == 0) {
                    echo json_encode(['success' => false, 'message' => 'El paciente no existe en la base de datos.']);
                    return;
                }

                // Obtener el id del paciente
                $pacienteData = $stmtVerificarPaciente->fetch(PDO::FETCH_ASSOC);
                $this->paciente->id = $pacienteData['id'];

                // Asignar los datos del formulario al objeto Paciente
                $this->paciente->cedula = $_POST['cedula'] ?? null; // Asignar el valor de cedula
                $this->paciente->fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null; // Asignar el valor de fecha_nacimiento
                $this->paciente->sexo = $_POST['sexo'] ?? null;
                $this->paciente->telefono = $_POST['telefono'] ?? null;
                $this->paciente->direccion = $_POST['direccion'] ?? null;
                $this->paciente->tipo_sangre = $_POST['tipo_sangre'] ?? null;
                $this->paciente->nacionalidad_id = $_POST['nacionalidad_id'] ?? null;
                $this->paciente->provincia_id = $_POST['provincia_id'] ?? null;

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
                        $this->paciente->foto_perfil = $encryptedContent;
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Solo se permiten JPG y PNG.']);
                        return;
                    }
                } else {
                    $this->paciente->foto_perfil = null;
                }

                // Actualizar la información del paciente
                try {
                    if ($this->paciente->actualizarInformacionPaciente()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al actualizar la información del paciente.']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
        }
    }

    /**
     * Método para mostrar la lista de usuarios.
     */
    public function mostrarListaUsuarios()
    {
        // Obtener la lista de usuarios
        $usuarios = $this->user->obtenerTodosLosUsuarios();
        require_once __DIR__ . '/../Views/actualizarInformacionUsuarios.php';
    }

    /**
     * Método para manejar la actualización de la información de cualquier usuario.
     */
    public function actualizarInformacionUsuarios()
    {
        // Verificar si la solicitud es POST (formulario enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asignar los datos del formulario al objeto User
            $this->user->id = $_POST['id'];
            $this->user->email = $_POST['email'];
            $this->user->rol = $_POST['rol'];

            // Actualizar la información del usuario y redirigir a la lista de usuarios si tiene éxito
            if ($this->user->actualizarInformacionUsuarios()) {
                header('Location: ./actualizarInformacionUsuarios');
            } else {
                echo "Error al actualizar la información del usuario.";
            }
        } else {
            // Cargar la vista del formulario de actualización si la solicitud no es POST
            require_once __DIR__ . '/../Views/actualizarInformacionUsuarios.php';
        }
    }

    public function agregarUsuario()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->email = $_POST['email'];
            $this->user->password = $_POST['password'];
            $this->user->rol = 1; // Rol de paciente

            if ($this->user->registro()) {
                $this->paciente->id = $this->user->id;
                $this->paciente->nombre = $_POST['nombre'];
                $this->paciente->apellido = $_POST['apellido'];
                if ($this->paciente->registro()) {
                    echo json_encode(['success' => true, 'message' => 'Paciente registrado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error en el registro del paciente.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error en el registro del usuario.']);
            }
        } else {
            require_once __DIR__ . '/../Views/actualizarInformacionUsuarios.php';
        }
    }


    public function agregarColaborador()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->email = $_POST['email'];
            $this->user->password = $_POST['password'];
            $this->user->rol = $_POST['rol_id']; // Rol de colaborador

            if ($this->user->registro()) {
                $this->colaborador->id = $this->user->id;
                $this->colaborador->nombre = $_POST['nombre'];
                $this->colaborador->apellido = $_POST['apellido'];
                $this->colaborador->rol_id = $_POST['rol_id'];
                $this->colaborador->fecha_contratacion = date('Y-m-d'); // Asignar la fecha actual como fecha de contratación

                // Asignar especialidad solo si el rol es "medico"
                if ($_POST['rol_id'] == 2) { // Asumiendo que el ID del rol de medico es 2
                    $this->colaborador->especialidad_id = $_POST['especialidad'];
                } else {
                    $this->colaborador->especialidad_id = null;
                }

                if ($this->colaborador->registro()) {
                    echo json_encode(['success' => true, 'message' => 'Colaborador registrado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error en el registro del colaborador.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error en el registro del usuario.']);
            }
        } else {
            require_once __DIR__ . '/../Views/actualizarInformacionUsuarios.php';
        }
    }




    public function obtenerUsuarios()
    {
        $usuarios = $this->user->obtenerTodosLosUsuarios();
        echo json_encode($usuarios);
    }

    /**
     * Método para cerrar sesión.
     */
    public function logout()
    {
        session_destroy(); // Destruir la sesión actual
        header('Location: ./login'); // Redirigir al formulario de inicio de sesión
    }



    public function obtenerUsuariosPaginados($page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT u.id, u.email, r.nombre AS rol, p.nombre, p.apellido
                  FROM usuarios u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  LEFT JOIN pacientes p ON u.id = p.usuario_id
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarUsuarios()
    {
        $query = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

}




