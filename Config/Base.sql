-- Crear la base de datos
CREATE DATABASE gestion_clinica;

-- Usar la base de datos
USE gestion_clinica;

-- Crear tabla roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Insertar datos en la tabla roles
INSERT INTO roles (nombre) VALUES
('paciente'),
('medico'),
('contador'),
('gestion_inventarios'),
('administrativo');

-- Crear tabla usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Crear tabla colaboradores
CREATE TABLE colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100), -- Solo para médicos, puede ser NULL para otros roles
    fecha_contratacion DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Tabla pacientes
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de provincias
CREATE TABLE provincias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Insertar las 10 provincias
INSERT INTO provincias (nombre) VALUES
('Bocas del Toro'),
('Coclé'),
('Colón'),
('Chiriquí'),
('Darién'),
('Herrera'),
('Los Santos'),
('Panamá'),
('Panamá Oeste'),
('Veraguas');

-- Tabla informacion_paciente con referencia a provincias
CREATE TABLE informacion_paciente (
    paciente_id INT NOT NULL PRIMARY KEY,
    edad INT, -- Puede ser NULL inicialmente
    sexo ENUM('masculino', 'femenino', 'otro'), -- Puede ser NULL inicialmente
    telefono VARCHAR(20), -- Puede ser NULL
    direccion VARCHAR(255), -- Puede ser NULL
    tipo_sangre ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'), -- Puede ser NULL
    provincia_id INT, -- Relación con la tabla provincias
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (provincia_id) REFERENCES provincias(id) ON DELETE SET NULL
);


-- Crear tabla de nacionalidades
CREATE TABLE nacionalidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Insertar las nacionalidades
INSERT INTO nacionalidades (nombre) VALUES
('Panamá'),
('Colombia'),
('Costa Rica'),
('Venezuela');

-- Modificar la tabla informacion_paciente para incluir la relación con la tabla nacionalidades
ALTER TABLE informacion_paciente
ADD COLUMN nacionalidad_id INT,
ADD FOREIGN KEY (nacionalidad_id) REFERENCES nacionalidades(id) ON DELETE SET NULL;

-- Agregar la columna foto_perfil a la tabla informacion_paciente
ALTER TABLE informacion_paciente
ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL;


DELIMITER //
CREATE TRIGGER before_insert_usuario
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    SET NEW.rol_id = (SELECT id FROM roles WHERE nombre = 'paciente');
END;
//
DELIMITER ;
