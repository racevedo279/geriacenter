<?php
// Suppress headers already sent errors for testing redirects and ensure output buffering.
ob_start();

// Initialize error/success message variables
$error_message = "";
$success_message = "";
$username_to_change = ""; // For cambiar_clave.php form repopulation
$new_password = "";       // For cambiar_clave.php form repopulation


echo "--- Test Part 1: User Creation ---
";
require_once 'config.php'; // Includes session_start() and $mysqli

function db_ensure_connection(&$mysqli_conn) {
    if (!$mysqli_conn || $mysqli_conn->connect_errno) {
        // echo "Attempting DB reconnection...\n";
        // @ চিহ্ন ব্যবহার করা হয়েছে পিএইচপি এর ডিফল্ট এরর মেসেজ দমন করার জন্য, যদি কানেকশন ইতিমধ্যে খোলা থাকে।
        @$mysqli_conn->close(); // Close previous (if any and failed) before creating new
        $mysqli_conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($mysqli_conn->connect_errno) {
            // echo "DB Reconnection Failed: " . $mysqli_conn->connect_error . "\n";
            return false; // Indicate failure
        }
        $mysqli_conn->set_charset("utf8mb4");
        // echo "DB Reconnection Successful.\n";
    }
    return true; // Indicate success or already connected
}


function deleteUserIfExists($mysqli_conn, $username_del) {
    if (!db_ensure_connection($mysqli_conn)) {
        echo "DB connection error in deleteUserIfExists for '$username_del'.\n";
        return;
    }
    $sql_del_check = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?";
    if ($stmt_del_check = $mysqli_conn->prepare($sql_del_check)) {
        $stmt_del_check->bind_param("s", $username_del);
        $stmt_del_check->execute();
        $stmt_del_check->store_result();
        if ($stmt_del_check->num_rows > 0) {
            $stmt_del_check->close();
            $sql_delete = "DELETE FROM usuarios WHERE nombre_usuario = ?";
            if($stmt_delete = $mysqli_conn->prepare($sql_delete)){
                $stmt_delete->bind_param("s", $username_del);
                if($stmt_delete->execute()){
                    echo "Pre-existing user '$username_del' deleted for fresh test run.
";
                } else {
                    echo "Failed to delete pre-existing user '$username_del': " . $stmt_delete->error . "
";
                }
                $stmt_delete->close();
            } else {
                 echo "Failed to prepare delete statement for pre-existing user '$username_del'.
";
            }
        } else {
            $stmt_del_check->close();
        }
    } else {
        echo "Failed to prepare check statement for deleting user '$username_del'. Error: " . $mysqli_conn->error . "
";
    }
}

deleteUserIfExists($mysqli, 'admin_for_pass_change');
deleteUserIfExists($mysqli, 'user_for_pass_change');
deleteUserIfExists($mysqli, 'regular_admin_test');


function createUserForTest($mysqli_conn, $username_create, $password_create, $role_create) {
    if (!db_ensure_connection($mysqli_conn)) {
         return "DB connection error before creating user '$username_create'.";
    }
    $sql_insert = "INSERT INTO usuarios (nombre_usuario, clave_hash, rol) VALUES (?, ?, ?)";
    if ($stmt_insert = $mysqli_conn->prepare($sql_insert)) {
        $hashed_password = password_hash($password_create, PASSWORD_DEFAULT);
        $stmt_insert->bind_param("sss", $username_create, $hashed_password, $role_create);
        if ($stmt_insert->execute()) {
            $stmt_insert->close();
            return "User '$username_create' as '$role_create' created successfully.";
        } else {
            $err = $stmt_insert->error;
            $stmt_insert->close();
            return "Failed to create user '$username_create': " . $err;
        }
    }
    return "Failed to prepare insert statement for user '$username_create'. Error: " . $mysqli_conn->error;
}

echo createUserForTest($mysqli, 'admin_for_pass_change', 'AdminPassOriginal1', 'administrador') . "
";
echo createUserForTest($mysqli, 'user_for_pass_change', 'UserPassOriginal1', 'usuario') . "
";
echo createUserForTest($mysqli, 'regular_admin_test', 'RegularAdminPass1', 'administrador') . "
";


echo "
--- Test Part 2: Login ---
";

function simulateLogin($username_login, $password_login, $expected_role = null) {
    global $mysqli, $error_message, $success_message;
    $_SESSION = [];
    $_POST = ['username' => $username_login, 'password' => $password_login];
    $_SERVER['REQUEST_METHOD'] = 'POST';

    if (!db_ensure_connection($GLOBALS['mysqli'])) { // Use $GLOBALS to ensure modification of global $mysqli
        return "Login Test for '$username_login': FAILED (DB Connection Error)";
    }

    $error_message = ""; $success_message = "";

    $login_script_content = file_get_contents('login.php');
    $modified_login_content = str_replace('exit;', '// exit; // Modified by test_runner.php', $login_script_content);
    $modified_login_content = str_replace('$mysqli->close();', '// $mysqli->close(); // Modified by test_runner.php', $modified_login_content);
    $temp_login_file = 'temp_login_for_test_login.php';
    file_put_contents($temp_login_file, $modified_login_content);

    ob_start();
    include $temp_login_file;
    ob_end_clean();
    unlink($temp_login_file);

    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        if ($expected_role && $_SESSION["rol"] === $expected_role) {
            return "Login Test for '$username_login': SUCCESS. Role: " . $_SESSION["rol"];
        } elseif ($expected_role) {
            return "Login Test for '$username_login': FAIL (Logged in, but role mismatch. Got: ".$_SESSION['rol'].", Expected: ".$expected_role.")";
        }
        return "Login Test for '$username_login': FAIL (Logged in unexpectedly).";
    } else {
        if ($expected_role) {
             return "Login Test for '$username_login': FAIL (Did not log in). Error: " . ($error_message ?: "None captured");
        }
        return "Login Test for '$username_login': SUCCESS (Did not log in, as expected). Error: " . ($error_message ?: "None captured");
    }
}

echo simulateLogin('regular_admin_test', 'RegularAdminPass1', 'administrador') . "
";
$_SESSION = [];

echo "
--- Test Part 3: Password Change (as Admin) ---
";

function simulatePasswordChange($admin_user, $admin_pass, $target_user, $new_target_pass) {
    global $mysqli, $error_message, $success_message, $username_to_change, $new_password;

    if (!db_ensure_connection($GLOBALS['mysqli'])) {
        return "PassChange Test for '$target_user': FAILED (Admin Login DB Connection Error)";
    }

    $_SESSION = [];
    if ($admin_user === 'regular_admin_test' && $admin_pass === 'RegularAdminPass1') {
         $_SESSION["loggedin"] = true;
         $admin_id_query = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?";
         if($stmt_admin_id = $mysqli->prepare($admin_id_query)) {
             $stmt_admin_id->bind_param("s", $admin_user);
             if($stmt_admin_id->execute()){
                 $result_admin_id = $stmt_admin_id->get_result();
                 if($row_admin_id = $result_admin_id->fetch_assoc()){
                     $_SESSION["id_usuario"] = $row_admin_id['id_usuario'];
                 } else { $_SESSION["id_usuario"] = 999; }
             } else { $_SESSION["id_usuario"] = 998; }
             $stmt_admin_id->close();
         } else { $_SESSION["id_usuario"] = 997; }

         $_SESSION["nombre_usuario"] = $admin_user;
         $_SESSION["rol"] = 'administrador';
         echo "Admin '$admin_user' session context set for password change test.
";
    } else {
        return "PassChange Test for '$target_user': FAILED (Admin login part - incorrect test admin credentials used in test setup)";
    }

    $_POST = ['username_to_change' => $target_user, 'new_password' => $new_target_pass];
    $_SERVER['REQUEST_METHOD'] = 'POST';

    if (!db_ensure_connection($GLOBALS['mysqli'])) {
        return "PassChange Test for '$target_user': FAILED (DB Connection Error during change)";
    }

    $error_message = ""; $success_message = "";
    $username_to_change = ""; $new_password = "";

    $cambiar_clave_content = file_get_contents('cambiar_clave.php');
    $modified_cambiar_clave_content = preg_replace('/if \(isset\(\$mysqli\) && \$mysqli instanceof mysqli\) {\s*\$mysqli->close\(\);\s*}/', '// $mysqli->close(); // Modified by test_runner.php', $cambiar_clave_content);
    $temp_cambiar_clave_file = 'temp_cambiar_clave_for_test.php';
    file_put_contents($temp_cambiar_clave_file, $modified_cambiar_clave_content);

    ob_start();
    include $temp_cambiar_clave_file;
    ob_end_clean();
    unlink($temp_cambiar_clave_file);


    if (!empty($success_message)) {
        $_SESSION = [];
        $login_result_msg = simulateLogin($target_user, $new_target_pass, 'usuario');
        if (strpos($login_result_msg, 'SUCCESS') !== false) {
            return "PassChange Test for '$target_user': SUCCESS. ($success_message Login with new pass: $login_result_msg)";
        } else {
            return "PassChange Test for '$target_user': FAIL (Changed, but login with new pass failed). Login attempt: $login_result_msg. Change success message: $success_message";
        }
    } else {
        if ($target_user === 'non_existent_user_change' && !empty($error_message) && strpos($error_message, 'No se encontró ningún usuario') !== false) {
            return "PassChange Test for '$target_user': SUCCESS (Correctly failed as user does not exist). Error: $error_message";
        }
        return "PassChange Test for '$target_user': FAIL (Password change script reported error or no success). Error: '$error_message'. Success: '$success_message'";
    }
}

echo simulatePasswordChange('regular_admin_test', 'RegularAdminPass1', 'user_for_pass_change', 'NewUserPass2') . "
";
echo simulatePasswordChange('regular_admin_test', 'RegularAdminPass1', 'non_existent_user_change', 'AnyPass') . "
";


echo "
--- Test Part 4: Access Control (Code Check) ---
";
function checkAccessControl($filename, $isAdminPage = false) {
    if (!file_exists($filename)) return "Access control in $filename check: FAIL ($filename does not exist)";
    $content = file_get_contents($filename);
    $session_check_loggedin = strpos($content, '$_SESSION["loggedin"] !== true') !== false || strpos($content, '!isset($_SESSION["loggedin"])') !== false ;
    $session_check_admin_role = strpos($content, '$_SESSION["rol"] !== \'administrador\'') !== false;
    $redirect_present = strpos($content, 'header("location: login.php");') !== false;
    $exit_present = strpos($content, 'exit;') !== false;

    $all_checks_pass = $session_check_loggedin && $redirect_present && $exit_present;
    if ($isAdminPage) {
        $all_checks_pass = $all_checks_pass && $session_check_admin_role;
    }

    if ($all_checks_pass) {
        return "Access control in $filename seems correct: SUCCESS";
    }
    $details = "Loggedin_Check: ".($session_check_loggedin?'Yes':'No').
               ", Admin_Role_Check: ".($isAdminPage?($session_check_admin_role?'Yes':'No'):'N/A').
               ", Redirect: ".($redirect_present?'Yes':'No').
               ", Exit: ".($exit_present?'Yes':'No');
    return "Access control in $filename check: FAIL or needs review ($details)";
}

echo checkAccessControl('crear_usuario.php', true) . "
";
echo checkAccessControl('cambiar_clave.php', true) . "
";
echo checkAccessControl('welcome_user.php', false) . "
";


deleteUserIfExists($mysqli, 'admin_for_pass_change');
deleteUserIfExists($mysqli, 'user_for_pass_change');
deleteUserIfExists($mysqli, 'regular_admin_test');


if (isset($mysqli) && $mysqli && !$mysqli->connect_errno) {
    $mysqli->close();
}
ob_end_flush();
?>
