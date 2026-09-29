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

if (empty($isbn) || empty($title) || empty($author) || empty($editorial) || empty($categoryId)) {
    header("Location: add_book.php?error=" . urlencode("Todos los campos son obligatorios."));
    exit;
}

$result = addNewBook($isbn, $title, $author, $editorial, $categoryId);

if ($result['success']) {
    header("Location: add_book.php?status=success");
    exit;
} else {
    header("Location: add_book.php?error=" . urlencode($result['message']));
    exit;
}