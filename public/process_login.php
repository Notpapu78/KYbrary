<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=" . urlencode("Por favor completa todos los campos."));
        exit();
    }

    global $pdo;

    $sql = "SELECT * FROM users WHERE LOWER(user_email) = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['user_password'])) {
        if (isset($user['is_verified']) && !$user['is_verified']) {
            header("Location: login.php?error=" . urlencode("Debes verificar tu cuenta antes de iniciar sesión."));
            exit();
        }

        $_SESSION['user_rut']   = $user['user_nrun'];
        $_SESSION['user_name']  = $user['user_name'];
        $_SESSION['user_role']  = $user['roles_rol_id'];
        $_SESSION['user_email'] = $user['user_email'];

        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?error=" . urlencode("Credenciales incorrectas."));
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}