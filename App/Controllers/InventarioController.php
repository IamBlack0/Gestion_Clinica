<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión para manejar el inicio de sesión y autenticación
}

require_once './Config/DataBase.php';    
require_once __DIR__ . '/../Models/inventario.php';

class InventarioController {
    private $db;
    private $producto;

    /**
     * Constructor que inicializa la conexión a la base de datos y los modelos de usuario, paciente y colaborador.
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->producto = new Inventario($this->db);
    }

    public function mostrarListaProductos() {
        // Obtener la lista de productos
        $productos = $this->producto->obtenerTodosLosProductos();
        require_once __DIR__ . '/../Views/gestionInventario.php';
    }

    public function agregarProducto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            var_dump($_POST);
            // Asignar valores de los campos del formulario a las propiedades del objeto producto
            $this->producto->nombre_producto = $_POST['nombre'];
            $this->producto->codigo_sku = $_POST['codigo'];
            $this->producto->descripcion = $_POST['desc'];
            $this->producto->forma = $_POST['forma'];
            $this->producto->cantidad = $_POST['cantidad'];
            $this->producto->precio = $_POST['precio'];
            $this->producto->ubicacion = $_POST['almacen'];
            $this->producto->fecha = $_POST['fecha-registro'];
            $this->producto->movimiento = $_POST['movimiento'];
            $this->producto->fechaExpiracion = $_POST['fecha'];
            // Asignar la categoría y el proveedor
            $this->producto->categoria_id = $_POST['tipoProducto']; // ID de categoría
            $this->producto->proveedor_id = $_POST['proveedor_id']; // ID del proveedor
            $this->producto->proveedor_nombre = $_POST['proveedor_nombre']; // Nombre del proveedor (si lo necesitas)
            $this->producto->contacto_proveedor = $_POST['contacto'];
            $this->producto->telefono_proveedor = $_POST['telefono'];
    
            // Intentar registrar el producto
            if ($this->producto->registroProducto()) {
                echo json_encode(['success' => true, 'message' => 'Producto agregado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error en el registro del producto.']);
            }
        } else {
            // Manejar el caso donde no es un POST
            require_once __DIR__ . '/../Views/gestionInventario.php'; // Cambia esta ruta según tu estructura
        }
    }

    public function obtenerInventarios(){
        $productos = $this->producto->obtenerTodosLosProductos();
        echo json_encode($productos);
    }
}
?>