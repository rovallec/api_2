CREATE DATABASE api_db;

USE api_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- ID único para cada usuario
    name VARCHAR(100) NOT NULL,               -- Nombre del usuario
    email VARCHAR(100) NOT NULL UNIQUE,       -- Email único del usuario
    password VARCHAR(255) NOT NULL,           -- Contraseña cifrada del usuario
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de creación del registro
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


