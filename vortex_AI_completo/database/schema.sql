CREATE DATABASE IF NOT EXISTS vortex
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vortex;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol VARCHAR(40) NOT NULL DEFAULT 'medico',
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pacientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(160) NOT NULL,
  edad INT NOT NULL,
  genero VARCHAR(30) NOT NULL,
  alergias TEXT NULL,
  antecedentes TEXT NULL,
  descriptor_facial LONGTEXT NULL,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pacientes_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consultas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  paciente_id INT NOT NULL,
  sintomas TEXT NOT NULL,
  temperatura DECIMAL(4,1) NOT NULL,
  frecuencia_cardiaca INT NOT NULL,
  presion_arterial VARCHAR(20) NULL,
  saturacion_oxigeno INT NOT NULL,
  nivel_dolor TINYINT NOT NULL,
  observaciones TEXT NULL,
  fecha_consulta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_consultas_paciente (paciente_id),
  CONSTRAINT fk_consultas_paciente
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS triage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  consulta_id INT NOT NULL UNIQUE,
  nivel_urgencia VARCHAR(30) NOT NULL,
  confianza DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  recomendacion TEXT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_triage_nivel (nivel_urgencia),
  CONSTRAINT fk_triage_consulta
    FOREIGN KEY (consulta_id) REFERENCES consultas(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (nombre, username, password, rol)
SELECT 'Administrador Vortex', 'admin', '$2y$10$06RBCAOYwGTnmuXm0rAN1uOQR5lM2o297UOG7hhbrA9UK4edzCRSK', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE username = 'admin');
