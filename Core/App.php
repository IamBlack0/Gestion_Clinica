<?php
// Incluir el archivo de configuración
require_once __DIR__ . '/../config.php';

// Incluir el controlador de usuarios
require_once __DIR__ . '/../App/Controllers/UserController.php';

// Incluir el controlador de productos
require_once __DIR__ . '/../App/Controllers/InventarioController.php';

// Incluir el controlador de citas
require_once __DIR__ . '/../App/Controllers/CitasController.php';
// Incluir el controlador de historial médico
require_once __DIR__ . '/../App/Controllers/HistorialMedicoController.php';

/**
 * Clase App para manejar las rutas de la aplicación.
 */
class App
{
    public function __construct()
    {
        // Obtener la URL solicitada
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : null;
        $url = explode('/', $url);
        // Instanciar el controlador de usuarios
        $controller = new UserController();
        // Instanciar el controlador de inventario
        $controllerInv = new InventarioController();
        // Instanciar el controlador de citas
        $citasController = new CitasController();
        // Instanciar el controlador de historial médico
        $historialMedicoController = new HistorialMedicoController();

        // Si no hay una URL, cargar la página de inicio de sesión por defecto
        if (empty($url[0])) {
            require_once __DIR__ . '/../App/Views/login.php';
            return;
        }
        // Controlar las diferentes rutas según la URL
        switch ($url[0]) {
            case 'login':
                $controller->login(); // Cargar el método de inicio de sesión
                break;
            case 'registro':
                $controller->registro(); // Cargar el método de registro
                break;
            case 'dashboard':
                // Verificar si el usuario está autenticado antes de mostrar el panel de administración
                if (isset($_SESSION['user_id'])) {
                    require_once __DIR__ . '/../App/Views/dashboard.php';
                } else {
                    header('Location: ./login');
                }
                break;
            case 'configuracionCuenta':
                // Verificar si el usuario está autenticado antes de mostrar la configuración de la cuenta
                if (isset($_SESSION['user_id'])) {
                    $controller->obtenerInformacionPaciente(); // Llamar al método para obtener la información del paciente
                } else {
                    header('Location: ./login');
                }
                break;
            case 'actualizarInformacionPaciente':
                // Verificar si el usuario está autenticado antes de actualizar la información del paciente
                if (isset($_SESSION['user_id'])) {
                    $controller->actualizarInformacionPaciente(); // Llamar al método para actualizar la información del paciente
                } else {
                    header('Location: ./login');
                }
                break;
            case 'actualizarInformacionUsuarios':
                // Verificar si el usuario está autenticado y es administrador antes de mostrar la lista de usuarios
                if (isset($_SESSION['user_id']) && $_SESSION['rol'] === 'administrador') {
                    $controller->mostrarListaUsuarios(); // Llamar al método para mostrar la lista de usuarios
                } else {
                    header('Location: ./login');
                }
                break;
            case 'agregarUsuario':
                $controller->agregarUsuario(); // Llamar al método para agregar un usuario
                break;
            case 'agregarColaborador':
                $controller->agregarColaborador(); // Llamar al método para agregar un colaborador
                break;
            case 'obtenerUsuarios':
                $controller->obtenerUsuarios();
                break;

                //CASOS PARA EL INVENTARIO
            case 'obtenerInventarios':
                $controllerInv->obtenerInventarios();
                break;
            case 'gestionInventario':
                // Verificar si el usuario está autenticado y es administrador antes de mostrar la lista de productos
                if (isset($_SESSION['user_id']) && $_SESSION['rol'] === 'administrador') {
                    $controllerInv->mostrarListaProductos(); // Llamar al método para mostrar la lista de productos

                } else {
                    header('Location: ./login');
                }
                break;
            case 'agregarProducto':
                $controllerInv->agregarProducto(); // Llamar al método para agregar un producto
                break;
            case 'getProductoById':
                $controllerInv->obtenerProductoId();
                break;


                //CASO PARA AGENDAR CITA
            case 'agendarCita':
                // Verificar si el usuario está autenticado antes de mostrar la vista de agendar citas
                if (isset($_SESSION['user_id'])) {
                    require_once __DIR__ . '/../App/Views/agendarCita.php';
                } else {
                    header('Location: ./login');
                }
                break;
            case 'obtenerMedicosPorEspecialidad':
                $especialidadId = $_GET['especialidad_id'];
                $medicos = $citasController->obtenerMedicosPorEspecialidad($especialidadId);
                echo json_encode(['medicos' => $medicos]);
                break;
            case 'obtenerMedicosDisponibles':
                $especialidadId = $_GET['especialidad_id'];
                $fecha = $_GET['fecha'];
                $medicosDisponibles = $citasController->obtenerMedicosDisponibles($especialidadId, $fecha);
                echo json_encode($medicosDisponibles);
                break;
            case 'procesarAgendarCita':
                // Verificar si el usuario está autenticado antes de procesar la solicitud
                if (isset($_SESSION['user_id'])) {
                    $citasController->procesarAgendarCita();
                } else {
                    header('Location: ./login');
                }
                break;
            case 'obtenerHorariosDisponibles':
                $medicoId = $_GET['medico_id'];
                $fecha = $_GET['fecha'];
                $especialidadId = $_GET['especialidad_id'];
                $horariosDisponibles = $citasController->obtenerHorariosDisponibles($medicoId, $fecha, $especialidadId);
                echo json_encode(['horarios' => $horariosDisponibles]);
                break;
            case 'verCitas':
                if (isset($_SESSION['user_id'])) {
                    $citas = $citasController->obtenerHistorialCitas();
                    require_once __DIR__ . '/../App/Views/verCitas.php';
                } else {
                    header('Location: ./login');
                }
                break;

                // PARA AGENDAR CITAS DESDE EL MEDICO
            case 'agendarCitaMedico':
                // Verificar si el usuario está autenticado antes de mostrar la vista de agendar citas
                if (isset($_SESSION['user_id'])) {
                    require_once __DIR__ . '/../App/Views/agendarCitaMedico.php';
                } else {
                    header('Location: ./login');
                }
                break;
            case 'calendarioCitasMedico':
                // Verificar si el usuario está autenticado antes de mostrar la vista de agendar citas
                if (isset($_SESSION['user_id'])) {
                    require_once __DIR__ . '/../App/Views/calendarioCitasMedico.php';
                } else {
                    header('Location: ./login');
                }
                break;
            case 'aceptarCita':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $citaId = $_GET['cita_id'];
                    $success = $citasController->aceptarCita($citaId);
                    echo json_encode(['success' => $success]);
                }
                break;
            case 'procesarAgendarCitaMedico':
                // Verificar si el usuario está autenticado antes de procesar la solicitud
                if (isset($_SESSION['user_id'])) {
                    $citasController->procesarAgendarCitaMedico();
                } else {
                    header('Location: ./login');
                }
                break;




                //CASO PARA VER CITAS DEL MEDICO
            case 'verCitasMedico':
                if (isset($_SESSION['user_id'])) {
                    $historialMedicoController->verCitasMedico();
                } else {
                    header('Location: ./login');
                }
                break;

            case 'procesarHistorialMedico':
                if (isset($_SESSION['user_id'])) {
                    $historialMedicoController->procesarHistorialMedico();
                } else {
                    header('Location: ./login');
                }
                break;

            case 'verHistorialMedico':
                if (isset($_SESSION['user_id'])) {
                    $historialMedicoController->verHistorialMedico();
                } else {
                    header('Location: ./login');
                }
                break;

                //CASO PARA CERRAR SESION
            case 'logout':
                $controller->logout(); // Cargar el método de cierre de sesión
                break;
            default:
                echo "Página no encontrada.";
                break;
        }
    }
}
