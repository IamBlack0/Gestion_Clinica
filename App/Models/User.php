<?php
/**
 * Clase User para manejar las operaciones CRUD con los usuarios.
 */
class User
{
    protected $conn;
    protected $tableUsuarios = 'usuarios'; // Nombre de la tabla de usuarios

    public $id;
    public $email; // Propiedad para el correo electrónico
    public $password;
    public $rol; // Propiedad para el rol del usuario
    public $nombre; // Propiedad para el nombre del usuario
    public $apellido; // Propiedad para el apellido del usuario

 

    /**
     * Constructor de la clase que recibe la conexión a la base de datos.
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }


    /**
     * Método para verificar si un correo electrónico ya existe en la base de datos.
     */
    public function emailExiste($email)
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->tableUsuarios . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }



    /**
     * Método para registrar un nuevo usuario.
     */

    public function registro()
    {
        if ($this->emailExiste($this->email)) {
            throw new Exception("El correo electrónico ya está registrado.");
        }

        // Consulta SQL para insertar un nuevo usuario con rol
        $queryUsuario = "INSERT INTO " . $this->tableUsuarios . " (email, contraseña, rol_id) VALUES (:email, :password, :rol_id)";
        $stmtUsuario = $this->conn->prepare($queryUsuario);

        // Encriptar la contraseña antes de guardarla
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        // Enlazar los parámetros
        $stmtUsuario->bindParam(':email', $this->email);
        $stmtUsuario->bindParam(':password', $this->password);
        $stmtUsuario->bindParam(':rol_id', $this->rol); // Enlazar el rol

        // Ejecutar la consulta para insertar el usuario
        if ($stmtUsuario->execute()) {
            // Obtener el ID del usuario insertado
            $this->id = $this->conn->lastInsertId();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Método para iniciar sesión.
     */
    public function login()
    {
        // Consulta SQL para buscar el usuario por correo electrónico
        $query = "SELECT * FROM " . $this->tableUsuarios . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email); // Enlazar el parámetro del correo electrónico
        $stmt->execute();

        // Obtener los resultados
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si la contraseña proporcionada coincide con la almacenada
        if ($user && password_verify($this->password, $user['contraseña'])) {
            $this->id = $user['id']; // Guardar el ID del usuario
            $this->email = $user['email']; // Guardar el correo del usuario
            $this->rol = $user['rol_id']; // Guardar el rol del usuario
            return true;
        }
        return false;
    }

    /**
     * Método para obtener el rol del usuario.
     */
    public function obtenerRol()
    {
        // Consulta SQL para obtener el rol del usuario
        $query = "SELECT r.nombre AS rol FROM " . $this->tableUsuarios . " u
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id); // Enlazar el parámetro del ID del usuario
        $stmt->execute();

        // Obtener los resultados
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si se obtuvo el rol
        if ($result) {
            $this->rol = $result['rol']; // Guardar el rol del usuario
            return true;
        }
        return false;
    }

    /**
     * Método para obtener el nombre y apellido del usuario.
     */
    public function obtenerNombreApellido()
    {
        // Consulta SQL para obtener el nombre y apellido del usuario
        $query = "SELECT COALESCE(p.nombre, c.nombre) AS nombre, 
                         COALESCE(p.apellido, c.apellido) AS apellido
                  FROM " . $this->tableUsuarios . " u
                  LEFT JOIN pacientes p ON u.id = p.usuario_id
                  LEFT JOIN colaboradores c ON u.id = c.usuario_id
                  WHERE u.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id); // Enlazar el parámetro del ID del usuario
        $stmt->execute();

        // Obtener los resultados
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si se obtuvieron el nombre y apellido
        if ($result) {
            $this->nombre = $result['nombre']; // Guardar el nombre del usuario
            $this->apellido = $result['apellido']; // Guardar el apellido del usuario
            return true;
        }
        return false;
    }

    /**
     * Método para obtener todos los usuarios.
     */
    public function obtenerTodosLosUsuarios()
    {
        $query = "SELECT u.id, u.email, u.rol_id, 
                         COALESCE(p.nombre, c.nombre) AS nombre, 
                         COALESCE(p.apellido, c.apellido) AS apellido, 
                         r.nombre AS rol
                  FROM " . $this->tableUsuarios . " u
                  LEFT JOIN pacientes p ON u.id = p.usuario_id
                  LEFT JOIN colaboradores c ON u.id = c.usuario_id
                  LEFT JOIN roles r ON u.rol_id = r.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para actualizar la información de un usuario.
     */
    public function actualizarInformacionUsuarios()
    {
        $query = "UPDATE " . $this->tableUsuarios . " 
                  SET email = :email, rol_id = :rol_id 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Enlazar los parámetros
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':rol_id', $this->rol);

        // Ejecutar la consulta para actualizar el usuario
        return $stmt->execute();
    }


}