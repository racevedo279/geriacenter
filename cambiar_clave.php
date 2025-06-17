<?php
// Initialize the session
// session_start(); // Removed, as it's now in config.php

// Check if the user is logged in and is an administrator
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["rol"] !== 'administrador'){
    header("location: login.php"); // Redirect to login page if not logged in or not an admin
    exit;
}

require_once 'config.php'; // For database connection ($mysqli)

$username_to_change = "";
$new_password = "";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_to_change = trim($_POST["username_to_change"]);
    $new_password = trim($_POST["new_password"]);

    if (empty($username_to_change) || empty($new_password)) {
        $error_message = "Por favor, complete todos los campos: nombre de usuario y nueva contraseña.";
    } else {
        // Check if the user to change exists
        $sql_check = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?";
        if ($stmt_check = $mysqli->prepare($sql_check)) {
            $stmt_check->bind_param("s", $param_username_check);
            $param_username_check = $username_to_change;

            if ($stmt_check->execute()) {
                $stmt_check->store_result();
                if ($stmt_check->num_rows == 1) {
                    // User exists, proceed to update password
                    $sql_update = "UPDATE usuarios SET clave_hash = ? WHERE nombre_usuario = ?";
                    if ($stmt_update = $mysqli->prepare($sql_update)) {
                        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt_update->bind_param("ss", $hashed_new_password, $username_to_change);

                        if ($stmt_update->execute()) {
                            $success_message = "Contraseña para el usuario '" . htmlspecialchars($username_to_change) . "' cambiada exitosamente.";
                            // Clear fields on success
                            $username_to_change = "";
                            $new_password = "";
                        } else {
                            $error_message = "Algo salió mal al cambiar la contraseña. Por favor, inténtelo de nuevo.";
                        }
                        $stmt_update->close();
                    } else {
                        $error_message = "Error preparando la consulta de actualización de contraseña.";
                    }
                } else {
                    $error_message = "No se encontró ningún usuario con el nombre '" . htmlspecialchars($username_to_change) . "'.";
                }
            } else {
                $error_message = "Error ejecutando la verificación del usuario.";
            }
            $stmt_check->close();
        } else {
            $error_message = "Error preparando la consulta de verificación de usuario.";
        }
    }
    // It's generally better to close the connection at the very end of the script,
    // or when it's certain no more DB operations will be needed for this request.
    // If the form is re-rendered with errors, the connection might be needed again by other parts of a larger app.
    // For this specific script, closing here is fine if POST is the only path to DB ops.
    // However, if a GET request might also involve DB (e.g. populating a list of users), this would be premature.
    // $mysqli->close(); // Removed as per instruction to keep connection open for form re-render.
}

// Close connection if it's still open and no longer needed (e.g., after processing or if page is just displayed)
// This part is tricky because if there was an error and form is redisplayed, $mysqli is needed.
// A common pattern is to close it at the very end of the script, outside of conditional blocks.
// For now, let's assume $mysqli might be needed if the page re-renders.
// if ($mysqli) { $mysqli->close(); } // This should be placed at the very end of the script if we decide to close it.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña de Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #ffc107; /* Yellow for warning/change */
            color: #333;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            width: 100%;
        }
        .btn:hover { background-color: #e0a800; }
        .message {
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
        }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .nav-links { text-align: center; margin-bottom: 20px; }
        .nav-links a { margin: 0 10px; text-decoration: none; color: #007bff; }
        .nav-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="crear_usuario.php">Crear Usuario</a>
            <a href="welcome_user.php">Dashboard</a> <!-- This might be better named admin_dashboard.php -->
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <h2>Cambiar Contraseña de Usuario</h2>
        <?php if(!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if(!empty($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username_to_change">Nombre de Usuario a Modificar:</label>
                <input type="text" name="username_to_change" id="username_to_change" value="<?php echo htmlspecialchars($username_to_change); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nueva Contraseña:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Cambiar Contraseña">
            </div>
        </form>
    </div>
<?php
// Close the database connection if it was opened.
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>
</body>
</html>
