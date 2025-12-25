<?php
// Script para generar hashes de contraseñas para los nuevos usuarios

$usuarios = [
    [
        'nombre' => 'Isaac Vargas',
        'usuario' => 'Isaac Vargas',
        'password' => 'nisanrecepcion106'
    ],
    [
        'nombre' => 'Rodrigo Moscoso',
        'usuario' => 'Rodrigo Moscoso',
        'password' => 'rodrigo106control'
    ],
    [
        'nombre' => 'Claudia Limpieza',
        'usuario' => 'Claudia Limpieza',
        'password' => 'claudia106'
    ]
];

echo "-- SQL para insertar nuevos usuarios\n";
echo "-- Generar hashes con PASSWORD_DEFAULT de PHP\n\n";

$id = 2; // Comenzar desde el ID 2 (1 ya existe)
foreach ($usuarios as $user) {
    $hash = password_hash($user['password'], PASSWORD_DEFAULT);
    echo "INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre_completo`, `activo`) VALUES\n";
    echo "({$id}, '{$user['usuario']}', '{$hash}', '{$user['nombre']}', 1);\n\n";
    $id++;
}

echo "\n-- Contraseñas originales (NO ejecutar, solo para referencia):\n";
echo "-- Isaac Vargas: nisanrecepcion106\n";
echo "-- Rodrigo Moscoso: rodrigo106control\n";
echo "-- Claudia Limpieza: claudia106\n";
?>
