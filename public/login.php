<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(__DIR__ . '/../src/config.php')) {
    require_once __DIR__ . '/../src/config.php';
}

$googleClientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
$googleRedirectUri = defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login-register.css">
    <title>Iniciar Sesión - KYbrary</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <main>
        <section id="login-form">
            <form action="process_login.php" method="POST">
                <h1>Iniciar Sesión</h1>
                
                <?php if (isset($_GET['error'])): ?>
                    <p style="color: #ff5252; text-align: center; margin-bottom: 1.5rem; font-size: 0.95rem; background: rgba(255, 82, 82, 0.1); padding: 0.8rem; border-radius: 6px; border: 1px solid rgba(255, 82, 82, 0.3);">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </p>
                <?php endif; ?>

                <fieldset class="input-box">
                    <input type="email" id="user-email" name="email" placeholder="Correo electrónico" required>
                    <i class="ri-user-fill"></i>
                </fieldset>
                
                <fieldset class="input-box">
                    <input type="password" id="user-password" name="password" placeholder="Contraseña" required>
                    <i class="ri-lock-fill"></i>
                </fieldset>

                <hr>
                <button type="submit">Ingresar</button>
                <hr>
                
                <div id="g_id_onload"
                    data-client_id="<?php echo htmlspecialchars($googleClientId); ?>"
                    data-login_uri="<?php echo htmlspecialchars($googleRedirectUri); ?>"
                    data-auto_prompt="false">
                </div>
                
                <div class="g_id_signin google-btn"
                    data-type="standard"
                    data-shape="pill"
                    data-theme="outline"
                    data-text="signin_with"
                    data-size="large"
                    data-locale="es">
                </div>
            </form>
        </section>
    </main>
</body>
</html>