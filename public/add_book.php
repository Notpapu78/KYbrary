<?php 
session_start();
require_once __DIR__ . '/../src/functions.php'; 

$userRole = strtoupper((string)($_SESSION['user_role'] ?? $_SESSION['rol_name'] ?? $_SESSION['rol_id'] ?? $_SESSION['roles_rol_id'] ?? '1'));
$isAdminOrLibrarian = in_array($userRole, ['3', 'BIBLIOTECARIO', 'ADMIN', 'ADMINISTRADOR']);

if (!$isAdminOrLibrarian) {
    header('Location: index.php');
    exit;
}

$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/add_book.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <title>Agregar Libro - KYbrary</title>
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

    <main class="main-content">
        <section id="add-book-form">
            <form action="process_add_book.php" method="POST" enctype="multipart/form-data">
                <h1>Nuevo Libro</h1>

                <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                    <p style="color: #4CAF50; text-align: center; margin-top: 1rem; font-size: 1.6rem;">
                        ¡Libro registrado con éxito!
                    </p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p style="color: #ff5252; text-align: center; margin-top: 1rem; font-size: 1.4rem;">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </p>
                <?php endif; ?>

                <fieldset class="input-box">
                    <input type="text" id="book_isbn" name="book_isbn" maxlength="13" placeholder="ISBN (ej: 9789563456789)" required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="text" id="book_title" name="book_title" placeholder="Título del libro" required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="text" id="book_author" name="book_author" placeholder="Autor" required>
                </fieldset>

                <fieldset class="input-box">
                    <input type="text" id="book_editorial" name="book_editorial" placeholder="Editorial" required>
                </fieldset>

                <fieldset class="input-box">
                    <select name="category_id" required>
                        <option value="" disabled selected style="background: #222;">Selecciona una Categoría</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id'] ?? $cat['CATEGORY_ID']; ?>" style="background: #222;">
                                <?php echo htmlspecialchars($cat['category_name'] ?? $cat['CATEGORY_NAME']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </fieldset>

                <fieldset class="input-box">
                    <input type="number" id="copies" name="copies" min="1" max="100" value="1" placeholder="Stock / Cantidad de copias (Opcional)">
                </fieldset>

                <fieldset class="input-box" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem; padding: 0.5rem 1rem;">
                    <label for="book_image">Portada del libro (Opcional):</label>
                    <input type="file" id="book_image" name="book_image" accept="image/*" style="width: 100%;">
                </fieldset>

                <hr style="margin-top: 2rem;">
                <button type="submit">Guardar Libro</button>
                <hr>

                <p style="text-align: center; margin-top: 1rem; font-size: 2rem;">
                    <a href="catalogo.php" style="color: var(--gris_plata); text-decoration: none;">Volver al Catálogo</a>
                </p>
            </form>
        </section>
    </main>

    <script src="js/sidebar.js"></script>
</body>
</html>