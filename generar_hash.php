<?php
// Script para generar el hash de la contraseña
// Contraseña: rodolfo106control

$password = 'rodolfo106control';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Hash generado para la contraseña 'rodolfo106control':\n";
echo $hash . "\n\n";
echo "Actualiza el archivo hotel_cecil.sql con este hash en la línea del INSERT.\n";
?>
