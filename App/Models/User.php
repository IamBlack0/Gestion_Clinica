<?php
/**
 * Clase User para manejar las operaciones CRUD con los usuarios.
 */
class User {
    private $conn;
    private $tableUsuarios = 'usuarios'; // Nombre de la tabla de usuarios
    private $tablePacientes = 'pacientes'; // Nombre de la tabla de pacientes
    private $tableInformacionPaciente = 'informacion_paciente'; // Nombre de la tabla de información del paciente

    public $id;
    public $email; // Propiedad para el correo electrónico
    public $password;
    public $nombre; // Propiedad para el nombre del paciente
    public $apellido; // Propiedad para el apellido del paciente
    public $rol; // Propiedad para el rol del usuario
    public $edad;
    public $sexo;
    public $telefono;
    public $direccion;
    public $tipo_sangre;
    public $nacionalidad_id;
    public $provincia_id;
    public $foto_perfil;

    /**
     * Constructor de la clase que recibe la conexión a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Método para registrar un nuevo usuario y paciente.
     */
    public function registro() {
        try {
            // Iniciar una transacción
            $this->conn->beginTransaction();

            // Consulta SQL para insertar un nuevo usuario
            $queryUsuario = "INSERT INTO " . $this->tableUsuarios . " (email, contraseña) VALUES (:email, :password)";
            $stmtUsuario = $this->conn->prepare($queryUsuario);

            // Encriptar la contraseña antes de guardarla
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);

            // Enlazar los parámetros
            $stmtUsuario->bindParam(':email', $this->email);
            $stmtUsuario->bindParam(':password', $this->password);

            // Ejecutar la consulta para insertar el usuario
            if ($stmtUsuario->execute()) {
                // Obtener el ID del usuario insertado
                $this->id = $this->conn->lastInsertId();

                // Consulta SQL para insertar un nuevo paciente
                $queryPaciente = "INSERT INTO " . $this->tablePacientes . " (usuario_id, nombre, apellido) VALUES (:usuario_id, :nombre, :apellido)";
                $stmtPaciente = $this->conn->prepare($queryPaciente);

                // Enlazar los parámetros
                $stmtPaciente->bindParam(':usuario_id', $this->id);
                $stmtPaciente->bindParam(':nombre', $this->nombre);
                $stmtPaciente->bindParam(':apellido', $this->apellido);

                // Ejecutar la consulta para insertar el paciente
                if ($stmtPaciente->execute()) {
                    // Confirmar la transacción
                    $this->conn->commit();
                    return true;
                } else {
                    // Revertir la transacción si falla la inserción del paciente
                    $this->conn->rollBack();
                    return false;
                }
            } else {
                // Revertir la transacción si falla la inserción del usuario
                $this->conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            // Revertir la transacción en caso de excepción
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Método para iniciar sesión.
     */
    public function login() {
        // Consulta SQL para buscar el usuario por correo electrónico
        $query = "SELECT u.*, p.nombre, p.apellido FROM " . $this->tableUsuarios . " u
                  JOIN " . $this->tablePacientes . " p ON u.id = p.usuario_id
                  WHERE u.email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email); // Enlazar el parámetro del correo electrónico
        $stmt->execute();

        // Obtener los resultados
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si la contraseña proporcionada coincide con la almacenada
        if ($user && password_verify($this->password, $user['contraseña'])) {
            $this->id = $user['id']; // Guardar el ID del usuario
            $this->nombre = $user['nombre']; // Guardar el nombre del usuario
            $this->apellido = $user['apellido']; // Guardar el apellido del usuario
            $this->email = $user['email']; // Guardar el correo del usuario
            return true;
        }
        return false;
    }

    /**
     * Método para obtener el rol del usuario.
     */
    public function obtenerRol() {
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
     * Método para obtener la información del paciente.
     */
    public function obtenerInformacionPaciente() {
        $query = "SELECT edad, sexo, telefono, direccion, tipo_sangre, nacionalidad_id, provincia_id, foto_perfil
                  FROM informacion_paciente
                  WHERE paciente_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->edad = $row['edad'];
            $this->sexo = $row['sexo'];
            $this->telefono = $row['telefono'];
            $this->direccion = $row['direccion'];
            $this->tipo_sangre = $row['tipo_sangre'];
            $this->nacionalidad_id = $row['nacionalidad_id'];
            $this->provincia_id = $row['provincia_id'];
            $this->foto_perfil = $row['foto_perfil'];
            return true;
        }
        return false;
    }

    public function actualizarInformacionPaciente() {
        $query = "INSERT INTO informacion_paciente (paciente_id, edad, sexo, telefono, direccion, tipo_sangre, nacionalidad_id, provincia_id, foto_perfil)
                  VALUES (:paciente_id, :edad, :sexo, :telefono, :direccion, :tipo_sangre, :nacionalidad_id, :provincia_id, :foto_perfil)
                  ON DUPLICATE KEY UPDATE
                  edad = VALUES(edad),
                  sexo = VALUES(sexo),
                  telefono = VALUES(telefono),
                  direccion = VALUES(direccion),
                  tipo_sangre = VALUES(tipo_sangre),
                  nacionalidad_id = VALUES(nacionalidad_id),
                  provincia_id = VALUES(provincia_id),
                  foto_perfil = VALUES(foto_perfil)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':paciente_id', $this->id);
        $stmt->bindParam(':edad', $this->edad);
        $stmt->bindParam(':sexo', $this->sexo);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':tipo_sangre', $this->tipo_sangre);
        $stmt->bindParam(':nacionalidad_id', $this->nacionalidad_id);
        $stmt->bindParam(':provincia_id', $this->provincia_id);
        $stmt->bindParam(':foto_perfil', $this->foto_perfil);
    
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            echo "Error al actualizar la información del paciente: " . $e->getMessage();
        }
        return false;
    }
}