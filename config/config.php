<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_cecil');

// Configuración general del sistema
define('SITE_NAME', 'Hotel Cecil - Sistema de Gestión');
define('TIMEZONE', 'America/La_Paz');

// Ruta base del proyecto (ajusta esto según tu instalación)
define('BASE_PATH', '/Sistem Hotel Cecil');
define('BASE_URL', 'http://localhost' . BASE_PATH);

// Establecer zona horaria
date_default_timezone_set(TIMEZONE);

// Conexión a la base de datos
function getConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            // Establecer charset explícitamente
            $conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Función para limpiar datos de entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para formatear fechas
function formatDate($date) {
    if (empty($date)) return '';
    return date('d/m/Y', strtotime($date));
}

// Función para formatear montos
function formatMoney($amount) {
    return number_format($amount, 2, '.', ',');
}

// Función para verificar si el usuario es administrador
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador';
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
