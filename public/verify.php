<?php
require_once __DIR__ . '/../src/database.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "UPDATE USERS SET IS_VERIFIED = TRUE, USER_TOKEN = NULL WHERE USER_TOKEN = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);

    if ($stmt->rowCount() > 0) {
        header("Location: index.php");
        exit();
    } else {
        echo "Token inválido o la cuenta ya fue verificada.";
    }
} else {
    echo "No se proporcionó ningún token.";
}
?>