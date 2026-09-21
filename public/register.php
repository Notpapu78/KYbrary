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

                <div>
                    <span>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></span>
                </div>
            </form>
        </section>
    </main>
</body>
</html>