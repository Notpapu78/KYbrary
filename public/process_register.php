<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rut_body = trim($_POST['rut_body'] ?? '');
    $rut_dv   = strtoupper(trim($_POST['rut_dv'] ?? ''));
    $name     = trim($_POST['name'] ?? '');
    $surname  = trim($_POST['surname'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);
    
    $isRbl    = str_ends_with($email, '@liceorbl.cl');
    $isSofofa = str_ends_with($email, '@liceosofofa.cl');

    if (!$isRbl && !$isSofofa) {
        header("Location: register.php?error=" . urlencode("Solo se permiten correos institucionales (@liceorbl.cl o @liceosofofa.cl)"));
        exit();
    }

    $rutCompleto = $rut_body . "-" . $rut_dv;

    if ($isSofofa) {
        if ($rutCompleto === '22941087-3') {
            $rol_id = 3; 
        } else {
            $rol_id = 2; 
        }
    } else {
        $rol_id = 1;
    }

    $code = sprintf("%06d", random_int(0, 999999));

    $result = registerUserWithToken($rut_body, $rut_dv, $name, $surname, $email, $password, $rol_id, $code);

    if ($result['success']) {
        if (isset($_SESSION['google_pending'])) {
            unset($_SESSION['google_pending']);
        }

        $_SESSION['pending_email'] = $email;

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