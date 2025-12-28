<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/AsistenteIA.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Obtener mensaje del usuario
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Debug: verificar qué llega
if ($input === null) {
    echo json_encode(['error' => 'JSON inválido', 'raw' => $rawInput]);
    exit;
}

$mensaje = isset($input['mensaje']) ? trim($input['mensaje']) : '';

if (empty($mensaje)) {
    echo json_encode(['error' => 'Mensaje vacío', 'received' => $input]);
    exit;
}

// Consultar IA
try {
    $asistente = new AsistenteIA();
    $respuesta = $asistente->consultar($mensaje);
    
    echo json_encode([
        'respuesta' => $respuesta,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en IA: ' . $e->getMessage()]);
}
