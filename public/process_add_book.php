<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_book.php");
    exit;
}

$isbn = trim($_POST['book_isbn'] ?? '');
$title = trim($_POST['book_title'] ?? '');
$author = trim($_POST['book_author'] ?? '');
$editorial = trim($_POST['book_editorial'] ?? '');
$categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

if (empty($isbn) || empty($title) || empty($author) || empty($editorial) || empty($categoryId)) {
    header("Location: add_book.php?error=" . urlencode("Todos los campos son obligatorios."));
    exit;
}

global $pdo;

if (!$pdo) {
    header("Location: add_book.php?error=" . urlencode("Error de conexión a la base de datos."));
    exit;
}

try {
    $sql = "INSERT INTO BOOK (BOOK_ISBN, BOOK_TITLE, BOOK_AUTHOR, BOOK_EDITORIAL, CATEGORIES_CATEGORY_ID) 
            VALUES (:isbn, :title, :author, :editorial, :category_id)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':isbn' => $isbn,
        ':title' => $title,
        ':author' => $author,
        ':editorial' => $editorial,
        ':category_id' => $categoryId
    ]);

    header("Location: add_book.php?status=success");
    exit;

} catch (PDOException $e) {
    if ($e->getCode() === '23505') {
        $msg = "El ISBN ingresado ya se encuentra registrado.";
    } else {
        $msg = "Error al guardar el libro: " . $e->getMessage();
    }
    
    header("Location: add_book.php?error=" . urlencode($msg));
    exit;
}