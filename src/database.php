<?php
$host = "127.0.0.1";
$port = "5432";
$user = "waos";
$dbname = "test_yuyito";
$password = "022009";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "¡Conexión exitosa a PostgreSQL con PHP!";

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>