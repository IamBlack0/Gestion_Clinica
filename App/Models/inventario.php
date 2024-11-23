<?php
require_once 'User.php';

class Inventario
{
    protected $conn;
    protected $tableProductos = 'productos';
    protected $tableInsumos = 'insumos';

    public $nombre_producto;
    public $codigo_sku;
    public $forma;
    public $cantidad;
    public $precio;
    public $fecha;
    public $movimiento;
    public $fechaExpiracion;
    public $categoria_id;
    public $proveedor_id;
    public $proveedor_nombre;
    public $producto_id;

    public $fechaSalida;
    public $cantidadSalida;

    public $nombre_insumo;
    public $descripcion_insu;
    public $cantidad_insumo;
    public $precio_insumo;
    public $fechaRegistro;


    /**
     * Constructor de la clase que recibe la conexión a la base de datos.
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Método para obtener todos los productos.
     */
    public function obtenerTodosLosProductos()
    {
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

    public function registroProducto()
    {
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
            $queryCantidad = "INSERT INTO cantidad (producto_id, cantidad)
              VALUES (:producto_id, :cantidad)";
            $stmtCantidad = $this->conn->prepare($queryCantidad);

            $stmtCantidad->bindParam(':producto_id', $this->producto_id);
            $stmtCantidad->bindParam(':cantidad', $this->cantidad);

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
                    $queryMovimientoInventario = "INSERT INTO movimientos_inventario (producto_id, fecha_movimiento, tipo_movimiento, cantidad)
                      VALUES (:producto_id, :fecha_movimiento, :tipo_movimiento, :cantidad)";
                    $stmtMovimientoInventario = $this->conn->prepare($queryMovimientoInventario);

                    $stmtMovimientoInventario->bindParam(':producto_id', $this->producto_id);
                    $stmtMovimientoInventario->bindParam(':fecha_movimiento', $this->fecha);
                    $stmtMovimientoInventario->bindParam(':tipo_movimiento', $this->movimiento);
                    $stmtMovimientoInventario->bindParam(':cantidad', $this->cantidad);

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

    public function obtenerProductoPorId($producto_id)
    {
        $query = "SELECT * FROM productos WHERE producto_id = :producto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $producto_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarProducto()
    {
        try {
            $this->conn->beginTransaction();

            // Actualizar tabla productos
            $queryEditarProductos = "UPDATE productos 
                SET nombre = :nombre_producto, 
                    codigo_sku = :codigo_sku, 
                    categoria_id = :categoria_id, 
                    unidad_medida = :forma 
                WHERE producto_id = :producto_id";

            $stmtEditarProductos = $this->conn->prepare($queryEditarProductos);
            $stmtEditarProductos->bindParam(':producto_id', $this->producto_id);
            $stmtEditarProductos->bindParam(':nombre_producto', $this->nombre_producto);
            $stmtEditarProductos->bindParam(':codigo_sku', $this->codigo_sku);
            $stmtEditarProductos->bindParam(':categoria_id', $this->categoria_id);
            $stmtEditarProductos->bindParam(':forma', $this->forma);

            if (!$stmtEditarProductos->execute()) {
                throw new Exception("Error al actualizar producto");
            }

            // Actualizar cantidad
            $queryEditarCantidad = "UPDATE cantidad 
                               SET cantidad = :cantidad 
                               WHERE producto_id = :producto_id";

            $stmtEditarCantidad = $this->conn->prepare($queryEditarCantidad);
            $stmtEditarCantidad->bindParam(':cantidad', $this->cantidad);
            $stmtEditarCantidad->bindParam(':producto_id', $this->producto_id);

            if (!$stmtEditarCantidad->execute()) {
                throw new Exception("Error al actualizar cantidad");
            }

            // Actualizar precio en productos_proveedores
            $queryEditarPrecio = "UPDATE productos_proveedores 
                             SET precio = :precio 
                             WHERE producto_id = :producto_id";

            $stmtEditarPrecio = $this->conn->prepare($queryEditarPrecio);
            $stmtEditarPrecio->bindParam(':precio', $this->precio);
            $stmtEditarPrecio->bindParam(':producto_id', $this->producto_id);

            if (!$stmtEditarPrecio->execute()) {
                throw new Exception("Error al actualizar precio");
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error en actualizarProducto: " . $e->getMessage());
            return false;
        }
    }


    public function registrarSalidaProducto()
    {
        try {
            $this->conn->beginTransaction();

            // Verificar stock disponible
            $query = "SELECT cantidad FROM cantidad WHERE producto_id = :producto_id FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':producto_id', $this->producto_id);
            $stmt->execute();

            $stock = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$stock || $stock['cantidad'] < $this->cantidadSalida) {
                throw new Exception("Stock insuficiente para realizar la salida");
            }

            // Actualizar cantidad
            $nuevaCantidad = $stock['cantidad'] - $this->cantidadSalida;
            $queryUpdate = "UPDATE cantidad SET cantidad = :nueva_cantidad WHERE producto_id = :producto_id";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(':nueva_cantidad', $nuevaCantidad);
            $stmtUpdate->bindParam(':producto_id', $this->producto_id);

            if (!$stmtUpdate->execute()) {
                throw new Exception("Error al actualizar el stock");
            }

            // Registrar movimiento
            $queryMovimiento = "INSERT INTO movimientos_inventario (producto_id, fecha_movimiento, tipo_movimiento, cantidad) 
                           VALUES (:producto_id, :fecha, 'salida', :cantidad)";
            $stmtMovimiento = $this->conn->prepare($queryMovimiento);
            $stmtMovimiento->bindParam(':producto_id', $this->producto_id);
            $stmtMovimiento->bindParam(':fecha', $this->fechaSalida);
            $stmtMovimiento->bindParam(':cantidad', $this->cantidadSalida);

            if (!$stmtMovimiento->execute()) {
                throw new Exception("Error al registrar el movimiento");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Registros de insumos 
    public function registroInsumo() {
    // Insertar el insumo en la tabla 'insumos'
    $queryInsumos = "INSERT INTO insumos (nombre, descripcion, cantidad, precio, fecha_registro)
      VALUES (:nombre_insumo, :descripcion, :cantidad, :precio, :fecha_registro)";

    $stmtInsumos = $this->conn->prepare($queryInsumos);

    // Enlazar los parámetros
    $stmtInsumos->bindParam(':nombre_insumo', $this->nombre_insumo);
    $stmtInsumos->bindParam(':descripcion_insu', $this->descripcion_insu);
    $stmtInsumos->bindParam(':cantidad_insumo', $this->cantidad_insumo);
    $stmtInsumos->bindParam(':precio_insumo', $this->precio_insumo);
    $stmtInsumos->bindParam(':fechaRegistro', $this->fechaRegistro);

    // Ejecutar la consulta para insertar el insumo
    if ($stmtInsumos->execute()) {        
        return true; // Todo se ejecutó correctamente
    } else {
        return false; // Falló la inserción de insumo
    }
}
}