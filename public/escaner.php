<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/functions.php';

$userRole = strtoupper((string)($_SESSION['user_role'] ?? $_SESSION['rol_name'] ?? $_SESSION['rol_id'] ?? $_SESSION['roles_rol_id'] ?? '1'));
$isAdminOrLibrarian = in_array($userRole, ['3', 'BIBLIOTECARIO', 'ADMIN', 'ADMINISTRADOR']);

if (!$isAdminOrLibrarian) {
    header('Location: index.php');
    exit;
}

$mensaje = null;
$tipoMensaje = '';
$libroConsultado = null;
$modoActual = $_POST['modo'] ?? 'consultar';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');
    $rutUser = trim($_POST['user_rut'] ?? '');
    $diasPrestamo = (int)($_POST['dias_prestamo'] ?? 7);

    if (!empty($barcode)) {
        if ($modoActual === 'consultar') {
            if (function_exists('getBookByBarcode')) {
                $libroConsultado = getBookByBarcode($barcode);
                if (!$libroConsultado) {
                    $mensaje = "No se encontró ningún libro asociado al código escaneado.";
                    $tipoMensaje = 'error';
                }
            } else {
                $mensaje = "Aviso: La función getBookByBarcode() no está definida en functions.php";
                $tipoMensaje = 'error';
            }
        } elseif ($modoActual === 'prestamo') {
            if (empty($rutUser)) {
                $mensaje = "Debe ingresar el RUT del usuario para realizar el préstamo.";
                $tipoMensaje = 'error';
            } else {
                if (function_exists('processLoanByBarcode')) {
                    $res = processLoanByBarcode($barcode, $rutUser, $diasPrestamo);
                    $mensaje = nl2br(htmlspecialchars($res['message'] ?? ''));
                    $tipoMensaje = !empty($res['success']) ? 'success' : 'error';
                } else {
                    $mensaje = "Aviso: La función processLoanByBarcode() no está definida en functions.php";
                    $tipoMensaje = 'error';
                }
            }
        } elseif ($modoActual === 'devolucion') {
            if (function_exists('processReturnByBarcode')) {
                $res = processReturnByBarcode($barcode);
                $mensaje = nl2br(htmlspecialchars($res['message'] ?? ''));
                $tipoMensaje = !empty($res['success']) ? 'success' : 'error';
            } else {
                $mensaje = "Aviso: La función processReturnByBarcode() no está definida en functions.php";
                $tipoMensaje = 'error';
            }
        }
    } else {
        $mensaje = "Por favor, pase el libro por el lector o ingrese el código manualmente.";
        $tipoMensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <title>Estación de Escaneo - KYbrary</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/escaner.css">
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

    <?php 
    if (function_exists('renderSidebar')) {
        renderSidebar(); 
    }
    ?>

    <main>
        <div class="scanner-container">
            <div class="scanner-header">
                <h2>Estación de Escaneo</h2>
                <p>Selecciona el modo de operación, completa los datos requeridos y pasa el código de barras.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert <?php echo $tipoMensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="escaner.php" method="POST" id="scanForm">
                <!-- Selector de Modo -->
                <div class="mode-selector">
                    <label>
                        <input type="radio" name="modo" value="consultar" <?php echo $modoActual === 'consultar' ? 'checked' : ''; ?> onchange="toggleFormFields()">
                        <span>Consultar</span>
                    </label>
                    <label>
                        <input type="radio" name="modo" value="prestamo" <?php echo $modoActual === 'prestamo' ? 'checked' : ''; ?> onchange="toggleFormFields()">
                        <span>Préstamo</span>
                    </label>
                    <label>
                        <input type="radio" name="modo" value="devolucion" <?php echo $modoActual === 'devolucion' ? 'checked' : ''; ?> onchange="toggleFormFields()">
                        <span>Devolución</span>
                    </label>
                </div>

                <div id="loanFields" class="loan-options-grid" style="display: <?php echo $modoActual === 'prestamo' ? 'grid' : 'none'; ?>;">
                    <div class="scan-input-group">
                        <label for="user_rut">RUT del Usuario Solicitante:</label>
                        <input type="text" id="user_rut" name="user_rut" placeholder="Ej: 12345678-9" value="<?php echo htmlspecialchars($_POST['user_rut'] ?? ''); ?>">
                    </div>
                    
                    <div class="scan-input-group">
                        <label for="dias_prestamo">Días de Préstamo:</label>
                        <select id="dias_prestamo" name="dias_prestamo">
                            <option value="3" selected>3 Días</option>
                            <option value="7">7 Días (1 Semana)</option>
                            <option value="14">14 Días (2 Semanas)</option>
                            <option value="21">21 Días (3 Semanas)</option>
                            <option value="30">30 Días (1 Mes)</option>
                        </select>
                    </div>
                </div>

                <div class="scan-input-group">
                    <label for="barcode">Código de Barras / ISBN / Ejemplar:</label>
                    <input type="text" id="barcode" name="barcode" placeholder="Escanee aquí el código..." autofocus required autocomplete="off">
                </div>

                <button type="submit" style="display:none;">Procesar</button>
            </form>

            <?php if ($libroConsultado): ?>
                <div class="book-result-card">
                    <h3><?php echo htmlspecialchars($libroConsultado['BOOK_TITLE'] ?? $libroConsultado['book_title'] ?? ''); ?></h3>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($libroConsultado['BOOK_ISBN'] ?? $libroConsultado['book_isbn'] ?? ''); ?></p>
                    <p><strong>Autor:</strong> <?php echo htmlspecialchars($libroConsultado['BOOK_AUTHOR'] ?? $libroConsultado['book_author'] ?? ''); ?></p>
                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars($libroConsultado['CATEGORY_NAME'] ?? $libroConsultado['category_name'] ?? 'General'); ?></p>
                    <p><strong>Stock Disponible:</strong> <span class="stock-highlight"><?php echo $libroConsultado['STOCK'] ?? $libroConsultado['stock'] ?? 0; ?> copias</span></p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>Copyright &copy;2026 <a href="nosotros.php">Waos Company</a> Todos los derechos reservados</p>
    </footer>

    <script src="js/sidebar.js"></script>
    <script>
        function toggleFormFields() {
            const modoPrestamo = document.querySelector('input[name="modo"][value="prestamo"]').checked;
            const loanFields = document.getElementById('loanFields');
            loanFields.style.display = modoPrestamo ? 'grid' : 'none';
        }
    </script>
</body>
</html>