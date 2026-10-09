<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

$email = $_SESSION['pending_email'] ?? $_GET['email'] ?? '';
$error = '';

if (empty($email)) {
    header("Location: register.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (strlen($code) !== 6 || !ctype_digit($code)) {
        $error = "El código debe tener exactamente 6 dígitos numéricos.";
    } else {
        if (verifyUserCode($email, $code)) {
            unset($_SESSION['pending_email']);
            unset($_SESSION['debug_code']);
            header("Location: login.php?msg=" . urlencode("¡Cuenta verificada con éxito! Ya puedes iniciar sesión."));
            exit();
        } else {
            $error = "El código de verificación es incorrecto o no coincide.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login-register.css">
    <title>Verificar Cuenta - KYbrary</title>
</head>
<body>
    <main>
        <section id="register-form">
            <form action="verify.php" method="POST">
                <h1>Verificación de Cuenta</h1>

                <p style="color: whitesmoke; font-size: 2.4rem; text-align: center; margin-bottom: 1.5rem;">
                    Ingresa el código de 6 dígitos enviado a:<br>
                    <strong style="color: #ff4d4d;"><?php echo htmlspecialchars($email); ?></strong>
                </p>

                <?php if (!empty($error)): ?>
                    <p style="color: #ff5252; text-align: center; margin-bottom: 1.5rem; font-size: 0.95rem; background: rgba(255, 82, 82, 0.1); padding: 0.8rem; border-radius: 6px; border: 1px solid rgba(255, 82, 82, 0.3);">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endif; ?>

                <?php if (isset($_SESSION['debug_code'])): ?>
                    <div style="background: rgba(0, 255, 255, 0.1); border: 1px dashed #ff4d4d; padding: 0.8rem; border-radius: 6px; margin-bottom: 1.5rem; text-align: center; color: #ff4d4d;">
                        Tu código es: <strong style="font-size: 1.2rem; letter-spacing: 2px; color: #ff4d4d;"><?php echo $_SESSION['debug_code']; ?></strong>
                    </div>
                <?php endif; ?>

                <fieldset class="input-box">
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        placeholder="Ej. 123456" 
                        maxlength="6" 
                        pattern="[0-9]{6}" 
                        style="text-align: center; font-size: 1.4rem; letter-spacing: 4px;" 
                        required 
                        autofocus>
                </fieldset>

                <hr>
                <button type="submit">Verificar Código</button>
                <hr>

                <div>
                    <span>¿No recibiste el código? <a href="register.php">Volver a intentar</a></span>
                </div>
            </form>
        </section>
    </main>
</body>
</html>