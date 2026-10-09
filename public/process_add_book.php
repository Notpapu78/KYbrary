<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_book.php");
    exit;
}

$isbn       = trim($_POST['book_isbn'] ?? '');
$title      = trim($_POST['book_title'] ?? '');
$author     = trim($_POST['book_author'] ?? '');
$editorial  = trim($_POST['book_editorial'] ?? '');
$categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$copies     = isset($_POST['copies']) && $_POST['copies'] > 0 ? (int)$_POST['copies'] : 1;

if (empty($isbn) || empty($title) || empty($author) || empty($editorial) || empty($categoryId)) {
    header("Location: add_book.php?error=" . urlencode("Todos los campos obligatorios deben ser completados."));
    exit;
}

$imagePath = 'icons/book.png';

if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath   = $_FILES['book_image']['tmp_name'];
    $fileName      = $_FILES['book_image']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (in_array($fileExtension, $allowedExtensions)) {
        $cleanIsbn   = preg_replace('/[^0-9]/', '', $isbn);
        $newFileName = 'book_' . ($cleanIsbn ?: time()) . '_' . time() . '.' . $fileExtension;
        
        $uploadFileDir = __DIR__ . '/uploads/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $destPath = $uploadFileDir . $newFileName;
        
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $imagePath = 'uploads/' . $newFileName;
        }
    } else {
        header("Location: add_book.php?error=" . urlencode("Formato de imagen no permitido. Usa JPG, PNG o WEBP."));
        exit;
    }
}

$result = addNewBook($isbn, $title, $author, $editorial, $categoryId, $copies, $imagePath);

if ($result['success']) {
    header("Location: add_book.php?status=success");
    exit;
} else {
    header("Location: add_book.php?error=" . urlencode($result['message']));
    exit;
}