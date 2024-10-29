<?php 
require_once 'User.php';

class Inventario {
    protected $conn;
    protected $tableProductos = 'productos';

    public $nombre_producto;
    public $codigo_sku;
    public $descripcion;
    public $forma;
    public $cantidad;
    public $precio;
    public $ubicacion;
    public $fecha;
    public $movimiento;
    public $fechaExpiracion;
    public $categoria_id;
    public $proveedor_id;
    public $proveedor_nombre;
    public $contacto_proveedor;
    public $telefono_proveedor;
    public $producto_id;

    /**
     * Constructor de la clase que recibe la conexión a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Método para obtener todos los productos.
     */
    public function obtenerTodosLosProductos() {
        $query = "SELECT p.producto_id, 
                         p.nombre, 
                         p.codigo_sku, 
                         c.nombre AS categoria_nombre, 
                         p.unidad_medida, 
                         COALESCE(ca.cantidad, 0) AS cantidad,  -- Usamos COALESCE para mostrar 0 si no hay stock
                         COALESCE(pp.precio, 0) AS precio       -- Usamos COALESCE para mostrar 0 si no hay precio
                  FROM productos p
                  LEFT JOIN categorias c ON p.categoria_id = c.categoria_id
                  LEFT JOIN cantidad ca ON p.producto_id = ca.producto_id
                  LEFT JOIN productos_proveedores pp ON p.producto_id = pp.producto_id"; // Agregamos el JOIN con productos_proveedores
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registroProducto() {
        // Asumiendo que el proveedor ya existe, no necesitamos insertar un nuevo proveedor.
        
        // Consulta para insertar un nuevo producto
        $queryProductos = "INSERT INTO productos (nombre, codigo_sku, categoria_id, unidad_medida, fecha_expiracion)
          VALUES (:nombre_producto, :codigo_sku, :categoria_id, :forma, :fechaExpiracion)";
        
        $stmtProductos = $this->conn->prepare($queryProductos);
    
        // Enlazar los parámetros
        $stmtProductos->bindParam(':nombre_producto', $this->nombre_producto);
        $stmtProductos->bindParam(':codigo_sku', $this->codigo_sku);
        $stmtProductos->bindParam(':categoria_id', $this->categoria_id);
        $stmtProductos->bindParam(':forma', $this->forma);
        $stmtProductos->bindParam(':fechaExpiracion', $this->fechaExpiracion);
    
        // Ejecutar la consulta para insertar el producto
        if ($stmtProductos->execute()) {
            // Obtener el ID del producto insertado
            $this->producto_id = $this->conn->lastInsertId();
            
            // Insertar la cantidad del producto
            $queryCantidad = "INSERT INTO cantidad (producto_id, cantidad, ubicacion)
              VALUES (:producto_id, :cantidad, :ubicacion)";
            $stmtCantidad = $this->conn->prepare($queryCantidad);
    
            $stmtCantidad->bindParam(':producto_id', $this->producto_id);
            $stmtCantidad->bindParam(':cantidad', $this->cantidad);
            $stmtCantidad->bindParam(':ubicacion', $this->ubicacion);
    
            if ($stmtCantidad->execute()) {
                // Insertar en productos_proveedores usando el proveedor existente
                $queryProductosProveedores = "INSERT INTO productos_proveedores (producto_id, proveedor_id, precio)
                  VALUES (:producto_id, :proveedor_id, :precio)";
                $stmtProductosProveedores = $this->conn->prepare($queryProductosProveedores);
    
                $stmtProductosProveedores->bindParam(':producto_id', $this->producto_id);
                $stmtProductosProveedores->bindParam(':proveedor_id', $this->proveedor_id); // Proveedor ya existente
                $stmtProductosProveedores->bindParam(':precio', $this->precio);
    
                if ($stmtProductosProveedores->execute()) {
                    // Insertar el movimiento de inventario
                    $queryMovimientoInventario = "INSERT INTO movimientos_inventario (producto_id, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
                      VALUES (:producto_id, :fecha_movimiento, :tipo_movimiento, :cantidad, :descripcion)";
                    $stmtMovimientoInventario = $this->conn->prepare($queryMovimientoInventario);
    
                    $stmtMovimientoInventario->bindParam(':producto_id', $this->producto_id);
                    $stmtMovimientoInventario->bindParam(':fecha_movimiento', $this->fecha);
                    $stmtMovimientoInventario->bindParam(':tipo_movimiento', $this->movimiento);
                    $stmtMovimientoInventario->bindParam(':cantidad', $this->cantidad);
                    $stmtMovimientoInventario->bindParam(':descripcion', $this->descripcion);
    
                    if ($stmtMovimientoInventario->execute()) {
                        return true; // Todo se ejecutó correctamente
                    } else {
                        return false; // Falló la inserción del movimiento de inventario
                    }
                } else {
                    return false; // Falló la inserción en productos_proveedores
                }
            } else {
                return false; // Falló la inserción de cantidad
            }
        } else {
            return false; // Falló la inserción de producto
        }
    }

    
}
