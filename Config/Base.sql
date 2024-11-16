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
('secretaria'),
('gestion_inventarios'),
('administrador');

-- Crear tabla usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    restablecer varchar(250) NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Crear tabla provincias
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

-- Crear tabla pacientes
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla informacion_paciente con referencia a provincias y nacionalidades
CREATE TABLE informacion_paciente (
    paciente_id INT NOT NULL PRIMARY KEY,
    edad INT, -- Puede ser NULL inicialmente
    cedula VARCHAR(50) NOT NULL UNIQUE, -- Cédula única y no nula
    fecha_nacimiento DATE, -- Nuevo campo para almacenar la fecha de nacimiento
    sexo ENUM('masculino', 'femenino', 'otro'), -- Puede ser NULL inicialmente
    telefono VARCHAR(20), -- Puede ser NULL
    direccion VARCHAR(255), -- Puede ser NULL
    tipo_sangre ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'), -- Puede ser NULL
    provincia_id INT, -- Relación con la tabla provincias
    nacionalidad_id INT, -- Relación con la tabla nacionalidades
    foto_perfil VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (provincia_id) REFERENCES provincias(id) ON DELETE SET NULL,
    FOREIGN KEY (nacionalidad_id) REFERENCES nacionalidades(id) ON DELETE SET NULL
);


-- Crear tabla especialidades
CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Insertar especialidades importantes
INSERT INTO especialidades (nombre) VALUES
('Medicina General'),
('Pediatría'),
('Cardiología'),
('Cirugía General'),
('Neurología');

-- Crear tabla colaboradores
CREATE TABLE colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    especialidad_id INT,
    fecha_contratacion DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE SET NULL
);



-- Insertar un usuario con rol administrativo en la tabla usuarios (la contraseña es: admin1)
INSERT INTO usuarios (email, contraseña, rol_id)
VALUES ('admin1@clinica.com', '$2y$10$KBY96OpPNp7kU6rmyN2qwOuYUgjKTZDkAnlbFY4LJQVmAeTP.kBhe', (SELECT id FROM roles WHERE nombre = 'administrador'));

-- Insertar el registro del colaborador administrativo en la tabla colaboradores
INSERT INTO colaboradores (usuario_id, rol_id, nombre, apellido, fecha_contratacion)
VALUES (
    (SELECT id FROM usuarios WHERE email = 'admin1@clinica.com'),
    (SELECT id FROM roles WHERE nombre = 'administrador'),
    'John', -- Nombre del administrativo
    'Doe', -- Apellido del administrativo
    '2024-10-24' -- Fecha de contratación
);

CREATE TABLE categorias (
    categoria_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE proveedores (
    proveedor_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE productos (
    producto_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    codigo_sku VARCHAR(50) UNIQUE NOT NULL,
    categoria_id INT,
    unidad_medida VARCHAR(50),
    fecha_expiracion DATE NOT NULL,
    CONSTRAINT fk_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(categoria_id)
);

CREATE TABLE cantidad (
    stock_id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    cantidad INT DEFAULT 0 CHECK (cantidad >= 0),
    ubicacion VARCHAR(100),
    CONSTRAINT fk_producto_stock FOREIGN KEY (producto_id) REFERENCES productos(producto_id)
);

CREATE TABLE movimientos_inventario (
    movimiento_id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    fecha_movimiento DATE NOT NULL,
    tipo_movimiento ENUM('entrada', 'salida') NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    descripcion TEXT,
    CONSTRAINT fk_producto_movimiento FOREIGN KEY (producto_id) REFERENCES productos(producto_id)
);

CREATE TABLE productos_proveedores (
    producto_id INT,
    proveedor_id INT,
    precio DECIMAL(10, 2),
    PRIMARY KEY (producto_id, proveedor_id),
    CONSTRAINT fk_producto FOREIGN KEY (producto_id) REFERENCES productos(producto_id),
    CONSTRAINT fk_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(proveedor_id)
);

INSERT INTO categorias (nombre) VALUES ('Analgésicos');
INSERT INTO categorias (nombre) VALUES ('Antibióticos');

INSERT INTO proveedores (nombre) 
VALUES ('MEDICEL S.A');
INSERT INTO proveedores (nombre) 
VALUES ('AGENCIAS MOTTA, S. A');

INSERT INTO productos (nombre, codigo_sku, categoria_id, unidad_medida, fecha_expiracion) 
VALUES ('Ibuprofeno 400mg', 'IBU400', 1, 'Tableta', '2030-10-22');
INSERT INTO productos (nombre, codigo_sku, categoria_id, unidad_medida, fecha_expiracion) 
VALUES ('Amoxicilina 500mg', 'AMOX500', 2, 'Cápsula', '2030-10-22');

INSERT INTO cantidad (producto_id, cantidad, ubicacion) 
VALUES (1, 100, 'Almacén central');
INSERT INTO cantidad (producto_id, cantidad, ubicacion) 
VALUES (2, 50, 'Almacén central');

INSERT INTO productos_proveedores (producto_id, proveedor_id, precio)
VALUES (1, 1, 0.50);
INSERT INTO productos_proveedores (producto_id, proveedor_id, precio)
VALUES (2, 2, 0.75);

INSERT INTO movimientos_inventario (producto_id, fecha_movimiento, tipo_movimiento, cantidad)
VALUES (1, '2024-10-20', 'entrada', 100);
INSERT INTO movimientos_inventario (producto_id, fecha_movimiento, tipo_movimiento, cantidad)
VALUES (2, '2024-10-21', 'entrada', 50);



-- TABLA PARA AGENDAR CITAS
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    especialidad_id INT NOT NULL,
    medico_id INT NOT NULL,
    horario TIME NOT NULL,
    razon TEXT NOT NULL,
    fecha_cita DATE NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id),
    FOREIGN KEY (medico_id) REFERENCES colaboradores(id)
);

-- TABLA PARA EL HISTORIAL DE CITAS
CREATE TABLE historial_citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    medico_id INT NOT NULL,
    fecha_cita DATE NOT NULL,
    estado_pago ENUM('pendiente', 'pagado') NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_cita ENUM('aceptada', 'completada', 'pendiente') DEFAULT 'pendiente' NOT NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (medico_id) REFERENCES colaboradores(id)
);



DELIMITER //
CREATE TRIGGER after_insert_cita
AFTER INSERT ON citas
FOR EACH ROW
BEGIN
    INSERT INTO historial_citas (paciente_id, medico_id, fecha_cita, estado_pago, estado_cita, fecha_creacion)
    VALUES (NEW.paciente_id, NEW.medico_id, NEW.fecha_cita, 'pendiente', 'aceptada', CURRENT_TIMESTAMP);
END//

DELIMITER ;




-- TABLA DE PAGOS
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    historial_cita_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('tarjeta', 'efectivo') NOT NULL,
    FOREIGN KEY (historial_cita_id) REFERENCES historial_citas(id) ON DELETE CASCADE
);


-- Insertar usuarios para médicos
INSERT INTO usuarios (email, contraseña, rol_id) VALUES
('medico1@clinic.com', '$2y$10$KBY96OpPNp7kU6rmyN2qwOuYUgjKTZDkAnlbFY4LJQVmAeTP.kBhe', (SELECT id FROM roles WHERE nombre = 'medico')),
('medico2@clinic.com', '$2y$10$KBY96OpPNp7kU6rmyN2qwOuYUgjKTZDkAnlbFY4LJQVmAeTP.kBhe', (SELECT id FROM roles WHERE nombre = 'medico')),
('medico3@clinic.com', '$2y$10$KBY96OpPNp7kU6rmyN2qwOuYUgjKTZDkAnlbFY4LJQVmAeTP.kBhe', (SELECT id FROM roles WHERE nombre = 'medico')),
('medico4@clinic.com', '$2y$10$KBY96OpPNp7kU6rmyN2qwOuYUgjKTZDkAnlbFY4LJQVmAeTP.kBhe', (SELECT id FROM roles WHERE nombre = 'medico')),
('medico5@clinic.com', '$2y$10$KBY96OpPNp7kU6rmyN2qwOuYUgjKTZDkAnlbFY4LJQVmAeTP.kBhe', (SELECT id FROM roles WHERE nombre = 'medico'));


-- Insertar colaboradores con rol de médico y especialidad específica
INSERT INTO colaboradores (usuario_id, rol_id, nombre, apellido, especialidad_id, fecha_contratacion) VALUES
((SELECT id FROM usuarios WHERE email = 'medico1@clinic.com'), (SELECT id FROM roles WHERE nombre = 'medico'), 'Goku', 'Ramírez', (SELECT id FROM especialidades WHERE nombre = 'Medicina General'), '2024-10-29'),
((SELECT id FROM usuarios WHERE email = 'medico2@clinic.com'), (SELECT id FROM roles WHERE nombre = 'medico'), 'Carlos', 'Ramírez', (SELECT id FROM especialidades WHERE nombre = 'Pediatría'), '2024-10-29'),
((SELECT id FROM usuarios WHERE email = 'medico3@clinic.com'), (SELECT id FROM roles WHERE nombre = 'medico'), 'Laura', 'González', (SELECT id FROM especialidades WHERE nombre = 'Cardiología'), '2024-10-29'),
((SELECT id FROM usuarios WHERE email = 'medico4@clinic.com'), (SELECT id FROM roles WHERE nombre = 'medico'), 'Miguel', 'Herrera', (SELECT id FROM especialidades WHERE nombre = 'Cirugía General'), '2024-10-29'),
((SELECT id FROM usuarios WHERE email = 'medico5@clinic.com'), (SELECT id FROM roles WHERE nombre = 'medico'), 'Ana', 'Martínez', (SELECT id FROM especialidades WHERE nombre = 'Neurología'), '2024-10-29');

CREATE TABLE historial_medico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    peso DECIMAL(5, 2),
    altura DECIMAL(4, 2),
    presion_arterial VARCHAR(20),
    frecuencia_cardiaca INT,
    temperatura DECIMAL(4, 1),
    alergias TEXT,
    medicamentos TEXT,
    cirugias TEXT,
    habitos TEXT,
    antecedentes_familiares TEXT,
    motivo_consulta TEXT,   -- EDITAR
    diagnostico TEXT,       -- EDITAR
    tratamiento TEXT,       -- EDITAR
    enfermedades_preexistentes TEXT,   -- EDITAR
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- EDITAR
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);