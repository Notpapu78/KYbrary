<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../src/functions.php';

$mensajeReserva = null;
$tipoMensajeReserva = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reservar') {
    $isbn = trim($_POST['isbn'] ?? '');
    $rutUser = $_SESSION['user_rut'] ?? '';
    
    if (!empty($isbn) && !empty($rutUser)) {
        $res = reserveBook($isbn, $rutUser);
        $mensajeReserva = $res['message'];
        $tipoMensajeReserva = $res['success'] ? 'success' : 'error';
    } else {
        $mensajeReserva = "Debes estar autenticado para agendar un libro.";
        $tipoMensajeReserva = 'error';
    }
}

$books = getBooks();
$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <title>Catálogo - KYbrary</title>
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
                <button class="menu-btn" id="openBtn">
                    <img src="icons/iconMenu.png" alt="Menú" width="36px" height="30px">
                </button>
            </div>
        </nav>
        <?php renderSidebar(); ?>
    </header>

    <main>
        <section id="bookScroller">
            <article class="searcher">
                <h1>Catálogo</h1>
                
                <?php if (!empty($mensajeReserva)): ?>
                    <div class="alert <?php echo $tipoMensajeReserva; ?>" style="margin-bottom: 15px; padding: 10px; border-radius: 5px; text-align: center;">
                        <?php echo htmlspecialchars($mensajeReserva); ?>
                    </div>
                <?php endif; ?>

                <label>
                    <img src="icons/magnifyingGlass.png" alt="Buscar">
                    <input type="text" id="searchInput" placeholder="Nombre del libro...">
                    
                    <select id="categorySelect">
                        <option value="">Todas las categorías</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php $catName = $cat['CATEGORY_NAME'] ?? $cat['category_name'] ?? ''; ?>
                                <option value="<?php echo htmlspecialchars($catName); ?>">
                                    <?php echo htmlspecialchars($catName); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </label>
            </article>
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
    <script src="js/categori.js"></script>
</body>
</html>