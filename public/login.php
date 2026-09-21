<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    data-client_id="TU_CLIENT_ID_DE_GOOGLE.apps.googleusercontent.com"
                    data-login_uri="https://tusitio.com/tu_backend_de_login.php"
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