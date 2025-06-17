<?php
// Initialize the session
// session_start(); // Removed, as it's now in config.php

// Check if the user is already logged in, if yes then redirect him to a welcome page (e.g., admin_dashboard.php or crear_usuario.php for now)
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    // Redirect to a page appropriate for logged-in users
    // For now, let's assume we'll create an admin_dashboard.php later.
    // If an admin, maybe redirect to crear_usuario.php or a new dashboard.
    // If a regular user, to a user-specific page.
    // For this step, redirecting to crear_usuario.php is fine as a placeholder.
    if ($_SESSION["rol"] == 'administrador') {
        header("location: crear_usuario.php"); // Or an admin dashboard
    } else {
        header("location: welcome_user.php"); // A generic welcome page for other roles
    }
    exit;
}

require_once 'config.php'; // For database connection ($mysqli)

$username = "";
$password = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        $error_message = "Por favor, ingrese su nombre de usuario y contraseña.";
    } else {
        $sql = "SELECT id_usuario, nombre_usuario, clave_hash, rol FROM usuarios WHERE nombre_usuario = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id_usuario, $db_username, $hashed_password, $rol);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            // session_start(); // Already started at the top

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_usuario"] = $id_usuario;
                            $_SESSION["nombre_usuario"] = $db_username;
                            $_SESSION["rol"] = $rol;

                            // Redirect user to a welcome page (e.g., an admin dashboard or user page based on role)
                            // For now, redirecting to crear_usuario.php if admin, otherwise a placeholder
                            if ($rol == 'administrador') {
                                header("location: crear_usuario.php"); // Or an admin dashboard
                            } else {
                                header("location: welcome_user.php"); // A generic welcome page for other roles
                            }
                            exit;
                        } else {
                            $error_message = "La contraseña que has introducido no es válida.";
                        }
                    }
                } else {
                    $error_message = "No se encontró ninguna cuenta con ese nombre de usuario.";
                }
            } else {
                $error_message = "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            }
            $stmt->close();
        } else {
            $error_message = "Error preparando la consulta.";
        }
    }
    if (isset($mysqli)) {
      $mysqli->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 400px; margin: 50px auto; }
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
            background-color: #28a745; /* Green color for login */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            width: 100%;
        }
        .btn:hover { background-color: #218838; }
        .message {
            padding: 10px;
            margin-top: 0;
            margin-bottom:15px;
            border-radius: 4px;
        }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .create-account-link { text-align: center; margin-top: 15px; }
        .create-account-link a { color: #007bff; text-decoration: none; }
        .create-account-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <?php if(!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
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
                <input type="submit" class="btn" value="Login">
            </div>
            <div class="create-account-link">
                <!-- This link is optional, but useful if self-registration were allowed.
                     For now, user creation is admin-only via crear_usuario.php -->
                <!-- <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>.</p> -->
            </div>
        </form>
    </div>
</body>
</html>
