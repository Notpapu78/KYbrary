<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login-register.css">
    <title>Registrarse - KYbrary</title>
</head>
<body>
    <main>
        <section id="register-form">
            <form action="process_register.php" method="POST">
                <h1>Registrarse</h1>

                <fieldset class="input-box rut-container">
                    <input type="text" class="rut-input-body" id="rut_body" name="rut_body" placeholder="RUT" required>
                    <span class="rut-hyphen">-</span>
                    <input type="text" class="rut-input-dv" id="rut_dv" name="rut_dv" maxlength="1" placeholder="DV" required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="text" id="name" name="name" placeholder="Nombres" required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="text" id="surname" name="surname" placeholder="Apellidos" required>
                </fieldset>

                <fieldset class="input-box">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Correo electrónico (@liceorbl.cl)" 
                        pattern=".+@liceorbl\.cl" 
                        title="Debes ingresar un correo con dominio @liceorbl.cl" 
                        required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </fieldset>

                <hr>
                <button type="submit">Registrarse</button>
                <hr>

                <p style="text-align: center; margin-top: 1rem;">
                    <a href="login.php" style="color: var(--gris_plata); text-decoration: none;">¿Ya tienes cuenta? Inicia sesión</a>
                </p>
            </form>
        </section>
    </main>
</body>
</html>