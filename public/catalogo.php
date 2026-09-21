<?php 
require_once __DIR__ . '/../src/functions.php'; 

$books = getBooks();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <title>Catálogo - KYbrary</title>
</head>
<body>
    <header>
        <nav>
            <div class="pageTitle">
                <h1><a href="index.php">KYbrary</a></h1>
            </div>
            <div class="menuItem">
                <button class="menu-btn" id="openBtn">
                    <img src="icons/iconMenu.png" alt="Menú" width="36px" height="30px">
                </button>
            </div>
        </nav>
    </header>

    <?php renderSidebar(); ?>

    <main>
        <section id="bookScroller">
            <article class="searcher">
                <h1>Catálogo</h1>
                <label>
                    <img src="icons/magnifyingGlass.png" alt="Buscar">
                    <input type="text" placeholder="Nombre del libro...">
                </label>
            </article>
            <hr>
            <article class="bookLibrary">
                <?php if (!empty($books)): ?>
                    <?php foreach ($books as $book): ?>
                        <?php renderBooks($book); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php for ($i = 0; $i < 18; $i++): ?>
                        <?php renderBooks(); ?>
                    <?php endfor; ?>
                <?php endif; ?>
            </article>
        </section>
    </main>

    <footer>
        <p>Copyright &copy;2026 <a href="nosotros.php">Waos Company</a> Todos los derechos reservados</p>
        <p>Contacto: a.alfarogonzalez@liceorbl.cl</p>
    </footer>

    <script src="js/sidebar.js"></script>
</body>
</html>