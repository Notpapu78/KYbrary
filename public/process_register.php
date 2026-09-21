<?php
require_once __DIR__ . '/../src/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut_body = trim($_POST['rut_body']);
    $rut_dv = strtoupper(trim($_POST['rut_dv']));
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    
    if (!str_ends_with(strtolower($email), '@liceorbl.cl')) {
        header("Location: register.php?error=Solo+se+permiten+registros+con+correo+@liceorbl.cl");
        exit();
    }

    $rutCompleto = $rut_body . "-" . $rut_dv;
    $rol_id = ($rutCompleto === '22941087-3') ? 3 : 1;

    $token = bin2hex(random_bytes(16));

    try {
        $sql = "INSERT INTO USERS (USER_NRUN, USER_DVRUN, USER_NAME, USER_SURNAME, USER_EMAIL, ROLES_ROL_ID, USER_PASSWORD, USER_TOKEN, IS_VERIFIED) 
                VALUES (:nrun, :dvrun, :name, :surname, :email, :rol, :pass, :token, FALSE)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nrun' => $rut_body,
            ':dvrun' => $rut_dv,
            ':name' => $name,
            ':surname' => $surname,
            ':email' => $email,
            ':rol' => $rol_id,
            ':pass' => $password,
            ':token' => $token
        ]);

        $enlace_verificacion = "http://localhost:8000/verify.php?token=" . $token;

    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            header("Location: register.php?error=El+RUT+o+correo+ya+se+encuentra+registrado");
            exit();
        }

        header("Location: register.php?error=Ocurrio+un+error+al+registrar+el+usuario");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login-register.css">
    <title>Verificación Pendiente</title>
</head>
<body>
    <main>
        <section id="register-form">
            <form action="">
                <h1>¡Casi listo!</h1>
                <fieldset class="input-box" style="margin-bottom: 2rem;">
                    <p style="color: whitesmoke; font-size: 16px; line-height: 1.5;">
                        Te hemos enviado un correo para verificarte.
                    </p>
                    <p style="color: var(--gris_plata); font-size: 14px; margin-top: 1rem; word-break: break-all;">
                        <strong>[Modo Prueba Local]:</strong> Haz clic en el enlace para verificar:<br>
                        <a href="<?php echo $enlace_verificacion; ?>" style="color: #00ffff;"><?php echo $enlace_verificacion; ?></a>
                    </p>
                </fieldset>
            </form>
        </section>
    </main>
</body>
</html>