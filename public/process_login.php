<?php
session_start();
require_once __DIR__ . '/../src/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM USERS WHERE USER_EMAIL = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['user_password'])) {
        if (!$user['is_verified']) {
            die("Debes verificar tu cuenta desde el enlace enviado al registrarte antes de iniciar sesión.");
        }

        $_SESSION['user_rut'] = $user['user_nrun'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['user_role'] = $user['roles_rol_id'];

        header("Location: index.php");
        exit();
    } else {
        die("Credenciales incorrectas.");
    }
}
?>