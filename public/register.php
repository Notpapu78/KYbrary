<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(__DIR__ . '/../src/config.php')) {
    require_once __DIR__ . '/../src/config.php';
}

$googleClientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
$googleRedirectUri = defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : '';
$googleData = $_SESSION['google_pending'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login-register.css">
    <title>Registrarse - KYbrary</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <main>
        <section id="register-form">
            <form action="process_register.php" method="POST">
                <h1>Registrarse</h1>

                <?php if ($googleData): ?>
                    <p style="color: #ff4d4d; text-align: center; margin-bottom: 1.5rem; font-size: 1.2rem; background: rgba(0, 255, 255, 0.1); padding: 0.8rem; border-radius: 6px; border: 1px solid rgba(0, 255, 255, 0.3);">
                        Completa tu RUT y contraseña para vincular tu cuenta de Google (<b><?php echo htmlspecialchars($googleData['email']); ?></b>).
                    </p>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <p style="color: #ff5252; text-align: center; margin-bottom: 1.5rem; font-size: 0.95rem; background: rgba(255, 82, 82, 0.1); padding: 0.8rem; border-radius: 6px; border: 1px solid rgba(255, 82, 82, 0.3);">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </p>
                <?php endif; ?>

                <fieldset class="input-box rut-container">
                    <input type="text" class="rut-input-body" id="rut_body" name="rut_body" placeholder="RUT" required>
                    <span class="rut-hyphen">-</span>
                    <input type="text" class="rut-input-dv" id="rut_dv" name="rut_dv" maxlength="1" placeholder="DV" required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="text" id="name" name="name" placeholder="Nombres" value="<?php echo htmlspecialchars($googleData['name'] ?? ''); ?>" required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="text" id="surname" name="surname" placeholder="Apellidos" value="<?php echo htmlspecialchars($googleData['surname'] ?? ''); ?>" required>
                </fieldset>

                <fieldset class="input-box">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Correo (@liceorbl.cl o @liceosofofa.cl)" 
                        value="<?php echo htmlspecialchars($googleData['email'] ?? ''); ?>"
                        pattern=".+@(liceorbl\.cl|liceosofofa\.cl)" 
                        title="Debes ingresar un correo institucional (@liceorbl.cl o @liceosofofa.cl)" 
                        required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </fieldset>

                <button type="submit">Registrarse</button>

                <div id="g_id_onload"
                    data-client_id="<?php echo htmlspecialchars($googleClientId); ?>"
                    data-login_uri="<?php echo htmlspecialchars($googleRedirectUri); ?>"
                    data-auto_prompt="false">
                </div>
                
                <div class="g_id_signin google-btn"
                    data-type="standard"
                    data-shape="pill"
                    data-theme="outline"
                    data-text="signin"
                    data-size="large"
                    data-width="220"
                    data-locale="es">
                </div>

                <div style="margin-top: 1.5rem;">
                    <span>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></span>
                </div>
            </form>
        </section>
    </main>
</body>
</html>