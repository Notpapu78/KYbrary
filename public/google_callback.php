<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
if (file_exists(__DIR__ . '/../src/config.php')) {
    require_once __DIR__ . '/../src/config.php';
}
if (file_exists(__DIR__ . '/../src/database.php')) {
    require_once __DIR__ . '/../src/database.php';
}
if (file_exists(__DIR__ . '/../src/functions.php')) {
    require_once __DIR__ . '/../src/functions.php';
}

use Google\Client;

$idToken = $_POST['credential'] ?? null;
$clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';

if ($idToken && !empty($clientId)) {
    try {
        $client = new Client(['client_id' => $clientId]);
        $payload = $client->verifyIdToken($idToken);

        if ($payload) {
            $email = strtolower(trim($payload['email']));
            $givenName = $payload['given_name'] ?? '';
            $familyName = $payload['family_name'] ?? '';

            /** @var PDO $pdo */
            global $pdo;

            if (isset($pdo)) {
                $stmt = $pdo->prepare("
                    SELECT u.user_nrun, u.user_name, u.user_email, r.rol_name 
                    FROM users u 
                    LEFT JOIN roles r ON u.roles_rol_id = r.rol_id 
                    WHERE LOWER(u.user_email) = :email 
                    LIMIT 1
                ");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $_SESSION['user_rut']   = $user['user_nrun'];
                    $_SESSION['user_name']  = $user['user_name'];
                    $_SESSION['user_email'] = $user['user_email'];
                    $_SESSION['user_role']  = $user['rol_name'] ?? 'USUARIO';

                    header('Location: index.php');
                    exit();
                } else {
                    $_SESSION['google_pending'] = [
                        'email'   => $email,
                        'name'    => $givenName,
                        'surname' => $familyName
                    ];
                    header('Location: register.php');
                    exit();
                }
            }
        }
    } catch (Exception $e) {
        header('Location: login.php?error=' . urlencode("Error de autenticación: " . $e->getMessage()));
        exit();
    }
}

header('Location: login.php?error=' . urlencode("No se recibieron credenciales válidas de Google"));
exit();