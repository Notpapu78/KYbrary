<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rut_body = trim($_POST['rut_body'] ?? '');
    $rut_dv   = strtoupper(trim($_POST['rut_dv'] ?? ''));
    $name     = trim($_POST['name'] ?? '');
    $surname  = trim($_POST['surname'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);
    
    if (!str_ends_with($email, '@liceorbl.cl')) {
        header("Location: register.php?error=" . urlencode("Solo se permiten registros con correo @liceorbl.cl"));
        exit();
    }

    $rutCompleto = $rut_body . "-" . $rut_dv;
    $rol_id = ($rutCompleto === '22941087-3') ? 3 : 1;

    // Generar código numérico de 6 dígitos
    $code = sprintf("%06d", random_int(0, 999999));

    // Guardar usuario en la BD
    $result = registerUserWithToken($rut_body, $rut_dv, $name, $surname, $email, $password, $rol_id, $code);

    if ($result['success']) {
        $_SESSION['pending_email'] = $email;

        // AQUÍ SE ENVÍA EL CORREO REAL
        sendVerificationEmail($email, $code);

        header("Location: verify.php");
        exit();
    } else {
        header("Location: register.php?error=" . urlencode($result['message']));
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}