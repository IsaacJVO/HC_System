-- Tabla para registro de uso de garaje
CREATE TABLE IF NOT EXISTS registro_garaje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ocupacion_id INT NOT NULL,
    huesped_nombre VARCHAR(255) NOT NULL,
    fecha DATE NOT NULL,
    costo DECIMAL(10,2) NOT NULL DEFAULT 10.00,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ocupacion_id) REFERENCES registro_ocupacion(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para mejorar consultas
CREATE INDEX idx_fecha ON registro_garaje(fecha);
CREATE INDEX idx_ocupacion ON registro_garaje(ocupacion_id);
