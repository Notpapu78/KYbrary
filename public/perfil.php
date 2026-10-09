<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

if (!isset($_SESSION['user_rut'])) {
    header('Location: index.php');
    exit;
}

$userRut = $_SESSION['user_rut'];
$userName = $_SESSION['user_name'] ?? 'Usuario';
$userRole = $_SESSION['user_role'] ?? $_SESSION['rol_name'] ?? 'Estudiante';

$loansData = getUserLoans($userRut);
$activos = $loansData['activos'];
$historial = $loansData['historial'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <title>Mi Perfil - KYbrary</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/profile.css">
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
        <div class="profile-container">
            <div class="profile-card">
                <img class="profile-avatar" src="media/default_avatar.jpg" alt="Perfil">
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($userName); ?></h2>
                    <p><strong>RUT:</strong> <?php echo htmlspecialchars($userRut); ?></p>
                    
                    <?php 
                        $rolesMap = [
                            '1'             => 'Estudiante',
                            '2'             => 'Profesor',
                            '3'             => 'Bibliotecario',
                            'ESTUDIANTE'    => 'Estudiante',
                            'PROFESOR'      => 'Profesor',
                            'BIBLIOTECARIO' => 'Bibliotecario'
                        ];
                        $nombreRol = $rolesMap[strtoupper($userRole)] ?? $userRole;
                    ?>
                    <span class="badge-role"><?php echo htmlspecialchars($nombreRol); ?></span>
                    
                    <div style="margin-top: 1rem;">
                        <a href="logout.php" class="btn-logout" >
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>

            <div id="container-books">
                <div class="profile-section">
                    <h3>Reservas y Préstamos Activos</h3>
                    <?php if (!empty($activos)): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Libro</th>
                                        <th>Ejemplar #</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Límite</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activos as $loan): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['unit_id']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_start']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_finish']); ?></td>
                                            <td>
                                                <?php if ($loan['state'] === 'RESERVADO'): ?>
                                                    <span class="status-badge pending">Listo para Retiro</span>
                                                <?php else: ?>
                                                    <span class="status-badge active">Préstamo Activo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="empty-msg">No tienes reservas ni préstamos activos en este momento.</p>
                    <?php endif; ?>
                </div>

                <div class="profile-section">
                    <h3>Historial de Prestaciones</h3>
                    <?php if (!empty($historial)): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Libro</th>
                                        <th>Ejemplar #</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Devolución</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $loan): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['unit_id']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_start']); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_finish']); ?></td>
                                            <td><span class="status-badge returned">Devuelto</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="empty-msg">No hay historial registrado.</p>
                    <?php endif; ?>
                </div>
            </div>            
        </div>
    </main>

    <footer>
        <p>Copyright &copy;2026 <a href="nosotros.php">Waos Company</a> Todos los derechos reservados</p>
    </footer>

    <script src="js/sidebar.js"></script>
</body>
</html>