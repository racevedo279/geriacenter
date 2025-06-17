<?php
// Initialize the session
// session_start(); // Removed, as it's now in config.php

// Check if the user is logged in and is an administrator
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["rol"] !== 'administrador'){
    header("location: login.php"); // Redirect to login page
    exit;
}

require_once 'config.php'; // For database connection ($mysqli)

$username = "";
$password = "";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]); // Role from form

    if (empty($username) || empty($password) || empty($role)) {
        $error_message = "Por favor, complete todos los campos.";
    } else {
        // Check if username already exists
        $sql_check = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?";
        if ($stmt_check = $mysqli->prepare($sql_check)) {
            $stmt_check->bind_param("s", $param_username_check);
            $param_username_check = $username;
            if ($stmt_check->execute()) {
                $stmt_check->store_result();
                if ($stmt_check->num_rows == 1) {
                    $error_message = "Este nombre de usuario ya existe.";
                } else {
                    // Proceed to insert new user
                    $sql_insert = "INSERT INTO usuarios (nombre_usuario, clave_hash, rol) VALUES (?, ?, ?)";
                    if ($stmt_insert = $mysqli->prepare($sql_insert)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_insert->bind_param("sss", $username, $hashed_password, $role);

                        if ($stmt_insert->execute()) {
                            $success_message = "Usuario '" . htmlspecialchars($username) . "' creado exitosamente como '" . htmlspecialchars($role) . "'.";
                            // Clear form fields after successful creation
                            $username = "";
                        } else {
                            $error_message = "Algo salió mal al crear el usuario. Por favor, inténtelo de nuevo más tarde.";
                        }
                        $stmt_insert->close();
                    } else {
                        $error_message = "Error preparando la consulta de inserción.";
                    }
                }
            } else {
                $error_message = "Error ejecutando la verificación de usuario.";
            }
            $stmt_check->close();
        } else {
            $error_message = "Error preparando la consulta de verificación.";
        }
    }
    // It's good practice to close the main connection only when all operations for the request are done.
    // If the script had more operations after this block, $mysqli->close() would be premature here.
    // However, for this specific script structure, closing it here is acceptable if no further DB ops are needed.
    if (isset($mysqli)) { // Check if $mysqli is set before trying to close
        $mysqli->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nuevo Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
        .nav-links { text-align: center; margin-bottom: 20px; }
        .nav-links a { margin: 0 10px; text-decoration: none; color: #007bff; }
        .nav-links a:hover { text-decoration: underline; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], select {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            width: 100%;
        }
        .btn:hover { background-color: #0056b3; }
        .message {
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
        }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="cambiar_clave.php">Cambiar Contraseña</a>
            <a href="welcome_user.php">Dashboard</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <h2>Crear Nuevo Usuario</h2>
        <?php if(!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if(!empty($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Nombre de Usuario:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="role">Rol:</label>
                <select name="role" id="role" required>
                    <option value="administrador" <?php echo (isset($_POST['role']) && $_POST['role'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="usuario" <?php echo ( (isset($_POST['role']) && $_POST['role'] == 'usuario') || !isset($_POST['role']) ) ? 'selected' : ''; ?>>Usuario</option>
                    <!-- Add other roles here if needed -->
                </select>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Crear Usuario">
            </div>
        </form>
    </div>
</body>
</html>
