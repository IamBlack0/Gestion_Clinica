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
    private $insumo;

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
            try {
                // Log de datos recibidos
                error_log("Datos recibidos: " . print_r($_POST, true));

                $producto_id = $_POST['producto_id'];

                $this->producto->producto_id = $producto_id;
                $this->producto->nombre_producto = $_POST['nombre'];
                $this->producto->codigo_sku = $_POST['codigo'];
                $this->producto->categoria_id = $_POST['tipoProducto'];
                $this->producto->cantidad = $_POST['cantidad'];
                $this->producto->precio = $_POST['precio'];
                $this->producto->forma = $_POST['forma'];

                if ($this->producto->actualizarProducto()) {
                    echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
                } else {
                    throw new Exception('Error al actualizar el producto');
                }
            } catch (Exception $e) {
                error_log("Error en actualizarProducto: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit();
        }
    }

    public function salidaProducto()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            try {
                // Validar que los datos necesarios existen
                if (!isset($_POST['producto_id']) || !isset($_POST['cantidadSalida'])) {
                    throw new Exception("Faltan datos requeridos");
                }

                $this->producto->producto_id = $_POST['producto_id'];
                $this->producto->movimiento = $_POST['movimiento'];
                $this->producto->cantidadSalida = $_POST['cantidadSalida'];
                $this->producto->fechaSalida = $_POST['fechaSalida'];

                // Intenta registrar la salida
                if ($this->producto->registrarSalidaProducto()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Salida registrada correctamente'
                    ]);
                } else {
                    throw new Exception("Error al registrar la salida");
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit(); // Asegura que no haya más output
        }
    }

    public function agregarInsumo() 
    {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Asignar valores de los campos del formulario a las propiedades del objeto insumo
        $this->insumo->nombre_insumo = $_POST['nombre_insumo'];
        $this->insumo->descripcion_insu = $_POST['descripcion_insu'];
        $this->insumo->cantidad_insumo = $_POST['cantidad_insumo'];
        $this->insumo->precio_insumo = $_POST['precio_insumo'];
        $this->insumo->fechaRegistro = $_POST['fechaRegistro'];

        // Intentar registrar el insumo
        if ($this->insumo->registroInsumo()) {
            echo json_encode(['success' => true, 'message' => 'Insumo agregado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en el registro del insumo.']);
        }
    } else {
        // Manejar el caso donde no es un POST
        require_once __DIR__ . '/../Views/agregarInsumo.php';
    }
}

}