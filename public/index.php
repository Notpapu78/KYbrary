<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../src/functions.php'; 

$trendingBooks = getTrendingBooks(3);
$recentBooks   = getRecentBooks(3);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <title>KYbrary</title>
</head>
<body>
    <header>
        <nav>
            <div class="pageTitle">
                <a href="index.php">KYbrary</a>

                <div>
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <span>
                            Hola, <?php echo htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]); ?>
                        </span>
                    <?php else: ?>
                        <a href="register.php"><button>Registarse</button></a>
                        <a href="login.php"><button>Acceder</button></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="menuItem">
                <button class="menu-btn" id="openBtn"><img src="icons/iconMenu.png" alt="Menú" width="36px" height="30px"></button>
            </div>
        </nav>
        <?php renderSidebar(); ?>
    </header>
    
    <main>
        <section id="introduction">
            <article>
                <h1>Bienvenido a KYbrary</h1>
                <hr>
                <p>El sistema bibliotecario definitivo, <br>simple pero eficiente, <br>ligero pero potente.</p>
            </article>

            <article class="cardApply">
                <p>
                    ¡Pruébelo ahora!
                    Póngase en contacto
                    con nuestro equipo
                    y agende una demo
                    ahora mismo.                        
                </p>
                <a href="catalogo.php"><button>Más información</button></a>
            </article>
        </section>

        <section id="tendencyBooks">
            <h2>Libros en tendencia</h2>
            <div class="book-wrapper">
                <?php if (!empty($trendingBooks)): ?>
                    <?php foreach ($trendingBooks as $book): ?>
                        <article class="cardBook">
                            <div>
                                <img src="icons/book.png" alt="<?php echo htmlspecialchars($book['book_title']); ?>">
                            </div>
                            <h2><?php echo htmlspecialchars($book['book_title']); ?></h2>
                            <p><?php echo htmlspecialchars($book['book_author'] ?? 'Autor no especificado'); ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: whitesmoke;">No hay libros registrados en la base de datos.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="recentBooks">
            <h2>Nuestros libros más recientes</h2>
            <div class="book-wrapper">
                <?php if (!empty($recentBooks)): ?>
                    <?php foreach ($recentBooks as $book): ?>
                        <article class="cardBook">
                            <div>
                                <img src="icons/book.png" alt="<?php echo htmlspecialchars($book['book_title']); ?>">
                            </div>
                            <h2><?php echo htmlspecialchars($book['book_title']); ?></h2>
                            <p><?php echo htmlspecialchars($book['book_author'] ?? 'Autor no especificado'); ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: whitesmoke;">No hay libros registrados recientemente.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>Copyright &copy;2026 <a href="nosotros.php">Waos Company</a> Todos los derechos reservados</p>
        <p>Contacto: a.alfarogonzalez@liceorbl.cl</p>
    </footer>
    <script src="js/sidebar.js"></script>
</body>
</html>