<?php
session_start();

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Sin contraseña para el usuario root por defecto en esta configuración
define('DB_NAME', 'gestion_geriatrico');

// Conexión a la base de datos MySQL usando mysqli
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Comprobar la conexión
if($mysqli === false){
    die("ERROR: No se pudo conectar a la base de datos. " . $mysqli->connect_error);
}

// Establecer el conjunto de caracteres a utf8mb4 (recomendado para MySQL)
if (!$mysqli->set_charset("utf8mb4")) {
    // Opcional: registrar este error o manejarlo de otra manera
    // printf("Error cargando el conjunto de caracteres utf8mb4: %s\n", $mysqli->error);
}
?>
