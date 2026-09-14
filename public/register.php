<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login-register.css">
    <title>Register</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <main>
        <section id="register-form">
            <form action="">
                <h1>Register</h1>
                
                <fieldset class="input-box rut-container">
                    <input type="text" placeholder="Rut: 22941087" maxlength="8" class="rut-input-body">
                    <span class="rut-hyphen">-</span>
                    <input type="text" placeholder="3" maxlength="1" class="rut-input-dv">
                </fieldset>
                
                <fieldset class="input-box">
                    <input type="email" placeholder="Correo electrónico">
                </fieldset>
                
                <fieldset class="input-box">
                    <input type="password" placeholder="Contraseña">
                </fieldset>

                <button type="submit">Registrarse</button>
                <hr>
                
                <div id="g_id_onload"
                    data-client_id="TU_CLIENT_ID_DE_GOOGLE.apps.googleusercontent.com"
                    data-login_uri="https://tusitio.com/tu_backend_de_login.php"
                    data-auto_prompt="false">
                </div>
                
                <div class="g_id_signin"
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