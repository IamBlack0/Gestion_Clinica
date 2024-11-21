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
        require_once __DIR__ . '/../Views/agregarProductos.php';
    }

    public function editarProductosVista()
    {
        // Obtener la lista de productos
        $productos = $this->producto->obtenerTodosLosProductos();
        require_once __DIR__ . '/../Views/editarMedicamentos.php';
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
            require_once __DIR__ . '/../Views/agregarProductos.php';
        }
    }

    public function obtenerInventarios()
    {
        $productos = $this->producto->obtenerTodosLosProductos();
        echo json_encode($productos);
    }

    public function editarProducto($producto_id)
    {
        // Obtener el producto por ID
        $producto = $this->producto->obtenerProductoPorId($producto_id);
        require_once __DIR__ . '/../Views/editarMedicamentos.php'; // Vista de edición
    }

    public function actualizarProducto()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener el ID del producto desde el formulario
            $producto_id = $_POST['producto_id'];

            // Asignar valores del formulario a las propiedades del producto
            $this->producto->producto_id = $producto_id;
            $this->producto->nombre_producto = $_POST['nombre'];
            $this->producto->codigo_sku = $_POST['codigo'];
            $this->producto->categoria_id = $_POST['tipoProducto'];
            $this->producto->cantidad = $_POST['cantidad'];
            $this->producto->precio = $_POST['precio'];
            $this->producto->forma = $_POST['forma'];

            // Intentar actualizar el producto
            if ($this->producto->actualizarProducto()) {
                echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto.']);
            }
        } else {
            require_once __DIR__ . '/../Views/editarMedicamentos.php';
        }
    }
}