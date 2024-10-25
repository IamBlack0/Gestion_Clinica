<?php
// Incluir el archivo de configuración
require_once __DIR__ . '/../config.php';

// Incluir el controlador de usuarios
require_once __DIR__ . '/../App/Controllers/UserController.php';

/**
 * Clase App para manejar las rutas de la aplicación.
 */
class App {
    public function __construct() {
        // Obtener la URL solicitada
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : null;
        $url = explode('/', $url);
        // Instanciar el controlador de usuarios
        $controller = new UserController();
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
            case 'logout':
                $controller->logout(); // Cargar el método de cierre de sesión
                break;
            default:
                echo "Página no encontrada.";
                break;
        }
    }
}