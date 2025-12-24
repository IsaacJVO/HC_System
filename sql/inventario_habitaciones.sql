-- Tabla para inventario físico de cada habitación
CREATE TABLE inventario_habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_numero VARCHAR(10) NOT NULL UNIQUE,
    tipo VARCHAR(20) DEFAULT 'habitacion', -- 'habitacion' o 'almacen'
    
    -- Items para habitaciones
    cortinas INT DEFAULT 0,
    veladores INT DEFAULT 0,
    roperos INT DEFAULT 0,
    colgadores INT DEFAULT 0,
    basureros INT DEFAULT 0,
    shampoo INT DEFAULT 0,
    jabon_liquido INT DEFAULT 0,
    sillas INT DEFAULT 0,
    sillones INT DEFAULT 0,
    alfombras INT DEFAULT 0,
    camas INT DEFAULT 0,
    television INT DEFAULT 0,
    lamparas INT DEFAULT 0,
    
    -- Items para almacén
    manteles INT DEFAULT 0,
    cubrecamas INT DEFAULT 0,
    sabanas_media_plaza INT DEFAULT 0,
    sabanas_doble_plaza INT DEFAULT 0,
    almohadas INT DEFAULT 0,
    fundas INT DEFAULT 0,
    frazadas INT DEFAULT 0,
    toallas INT DEFAULT 0,
    cortinas_almacen INT DEFAULT 0,
    alfombras_almacen INT DEFAULT 0,
    
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_habitacion (habitacion_numero),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar registro para Almacén
INSERT INTO inventario_habitaciones (habitacion_numero, tipo) VALUES ('ALMACEN', 'almacen');
