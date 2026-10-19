<?php
define('GOOGLE_CLIENT_ID', '1067809345634-8ngqhks4ogmlc6gl4v2e37u27dkt05k1.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';

if ($host === 'kybrary.cl') {
    define('GOOGLE_REDIRECT_URI', 'https://kybrary.cl/google_callback.php');
} else {
    define('GOOGLE_REDIRECT_URI', 'http://localhost:8080/google_callback.php');
}
?>