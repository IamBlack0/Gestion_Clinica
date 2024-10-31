<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión para manejar el inicio de sesión y autenticación
}

require_once './Config/DataBase.php';
require_once __DIR__ . '/../Models/Inventario.php';

class InventarioController
{
    private $db;
    private $producto;

    /**
     * Constructor que inicializa la conexión a la base de datos y los modelos de usuario, paciente y colaborador.
     */
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->producto = new Inventario($this->db);
    }

    public function mostrarListaProductos()
    {
        // Obtener la lista de productos
        $productos = $this->producto->obtenerTodosLosProductos();
        require_once __DIR__ . '/../Views/gestionInventario.php';
    }

    public function agregarProducto()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asignar valores de los campos del formulario a las propiedades del objeto producto
            $this->producto->nombre_producto = $_POST['nombre'];
            $this->producto->codigo_sku = $_POST['codigo'];
            $this->producto->forma = $_POST['forma'];
            $this->producto->cantidad = $_POST['cantidad'];
            $this->producto->precio = $_POST['precio'];
            $this->producto->fecha = $_POST['fecha-registro'];
            $this->producto->movimiento = $_POST['movimiento'];
            $this->producto->fechaExpiracion = $_POST['fecha'];
            // Asignar la categoría y el proveedor
            $this->producto->categoria_id = $_POST['tipoProducto']; // ID de categoría
            $this->producto->proveedor_id = $_POST['proveedor_id']; // ID del proveedor
            $this->producto->proveedor_nombre = $_POST['proveedor_nombre']; // Nombre del proveedor (si lo necesitas)

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

    public function obtenerInventarios()
    {
        $productos = $this->producto->obtenerTodosLosProductos();
        echo json_encode($productos);
    }

    public function obtenerProductoId()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log(print_r($_POST, true));
            $this->producto->producto_id = $_POST['producto_id'];

            $producto = $this->producto->getProductoById();
            if ($producto) {
                var_dump($producto);
                echo json_encode(['success' => true, 'producto' => $producto]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error obteniendo Producto ID']);
            }
        } else {
            require_once __DIR__ . '/../Views/gestionInventario.php';
        }
    }
}
