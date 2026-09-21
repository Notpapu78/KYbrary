<?php
require_once __DIR__ . '/database.php';

function getCategories() {
    global $pdo;
    if (!isset($pdo)) return [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM CATEGORIES ORDER BY CATEGORY_NAME ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getBooks() {
    global $pdo;
    if (!isset($pdo)) return [];

    try {
        $sql = "SELECT b.*, c.CATEGORY_NAME 
                FROM BOOK b 
                LEFT JOIN CATEGORIES c ON b.CATEGORIES_CATEGORY_ID = c.CATEGORY_ID 
                ORDER BY b.BOOK_TITLE ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function renderBooks($book = null) {
    $titulo = $book['book_title'] ?? $book['BOOK_TITLE'] ?? 'Título';
    $autor = $book['book_author'] ?? $book['BOOK_AUTHOR'] ?? 'Autor no especificado';
    $categoria = $book['category_name'] ?? $book['CATEGORY_NAME'] ?? 'Sin Categoría';

    ?>
    <div class="cBook">
        <img src="icons/book.png" alt="">
        <h1><?php echo htmlspecialchars($titulo); ?></h1>
        <p><?php echo htmlspecialchars($autor); ?></p>
        <small style="color: var(--gris_plata); font-size: 1.2rem; margin-top: 0.5rem; display: block;">
            <?php echo htmlspecialchars($categoria); ?>
        </small>
    </div>
    <?php
}
function renderSidebar() {
    ?>
    <div class="main-app-container">
        <aside class="sidebar" id="sidebar">
            <div class="nav-links">
                <a href="index.php" class="nav-item"><span>🏠</span> Inicio</a>
                <a href="catalogo.php" class="nav-item"><span>📂</span> Catálogo</a>
                <a href="add_book.php" class="nav-item"><span>⏱️</span> Add Book</a>
            </div>
        </aside>
    </div>
    <?php
}

?>