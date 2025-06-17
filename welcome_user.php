<?php
require_once 'config.php'; // To ensure session is started via config.php

// Initialize the session
// session_start(); // Removed, as it's now in config.php

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; text-align: center; padding: 20px; }
        .page-header h1{ font-size: 2.5em; }
        .user-info { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Hola, <b><?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?></b>. ¡Bienvenido/a a la aplicación!</h1>
    </div>
    <div class="user-info">
        <p>Tu ID de usuario es: <?php echo htmlspecialchars($_SESSION["id_usuario"]); ?></p>
        <p>Tu rol es: <?php echo htmlspecialchars($_SESSION["rol"]); ?></p>
    </div>
    <p>
        <?php if($_SESSION["rol"] == "administrador"): ?>
            <a href="crear_usuario.php" class="btn btn-primary">Crear Usuarios</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-danger ml-3">Cerrar Sesión</a>
    </p>
</body>
</html>
