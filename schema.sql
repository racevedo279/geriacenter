-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS gestion_geriatrico;

-- Usar la base de datos
USE gestion_geriatrico;

-- Crear la tabla residentes
CREATE TABLE IF NOT EXISTS residentes (
    id_residente INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    apellidos VARCHAR(255),
    fecha_nacimiento DATE,
    dni VARCHAR(20) UNIQUE,
    direccion TEXT,
    telefono_contacto VARCHAR(20),
    email_contacto VARCHAR(255),
    persona_contacto_emergencia VARCHAR(255),
    telefono_emergencia VARCHAR(20),
    historial_medico_resumen TEXT,
    fecha_ingreso DATE,
    estado ENUM('activo', 'inactivo', 'alta') DEFAULT 'activo'
);

-- Crear la tabla trabajadores
CREATE TABLE IF NOT EXISTS trabajadores (
    id_trabajador INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    apellidos VARCHAR(255),
    dni VARCHAR(20) UNIQUE,
    puesto VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(255),
    fecha_contratacion DATE,
    estado ENUM('activo', 'inactivo', 'baja') DEFAULT 'activo'
);

-- Crear la tabla usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    clave_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'usuario',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
