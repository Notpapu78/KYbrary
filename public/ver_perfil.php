<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

$userRoleSession = strtoupper($_SESSION['user_role'] ?? $_SESSION['rol_name'] ?? $_SESSION['rol_id'] ?? $_SESSION['roles_rol_id'] ?? 'USUARIO');
$isAdminOrLibrarian = in_array($userRoleSession, ['ADMIN', 'ADMINISTRADOR', 'BIBLIOTECARIO', '1', '2', '3']);

if (!$isAdminOrLibrarian) {
    header('Location: index.php');
    exit;
}

$targetRut = $_GET['rut'] ?? $_POST['target_rut'] ?? '';

if (empty($targetRut)) {
    header('Location: usuarios.php');
    exit;
}

$cleanRut = getNrunFromRut($targetRut);

$alertMessage = '';
$alertType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_reservation') {
    $benefitsId = $_POST['loan_id'] ?? null;
    
    if ($benefitsId && isset($pdo)) {
        try {
            $pdo->beginTransaction();

            $stmtUnit = $pdo->prepare("SELECT unit_unit_id FROM detail_book WHERE benefits_benefits_id = :benefits_id LIMIT 1");
            $stmtUnit->execute([':benefits_id' => $benefitsId]);
            $unitId = $stmtUnit->fetchColumn();

            $stmtDetail = $pdo->prepare("DELETE FROM detail_book WHERE benefits_benefits_id = :benefits_id");
            $stmtDetail->execute([':benefits_id' => $benefitsId]);

            $stmtBenefits = $pdo->prepare("DELETE FROM benefits WHERE benefits_id = :benefits_id AND benefits_state = 'RESERVADO'");
            $stmtBenefits->execute([':benefits_id' => $benefitsId]);

            if ($unitId) {
                $stmtFreeUnit = $pdo->prepare("UPDATE unit SET unit_status = 'DISPONIBLE' WHERE unit_id = :unit_id");
                $stmtFreeUnit->execute([':unit_id' => $unitId]);
            }

            $pdo->commit();
            $alertMessage = 'Reserva eliminada con éxito y ejemplar liberado.';
            $alertType = 'success';
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $alertMessage = 'Error al eliminar la reserva: ' . $e->getMessage();
            $alertType = 'error';
        }
    }
}

$targetUser = null;
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.user_nrun, u.user_dvrun, u.user_name, u.user_surname, u.user_email, r.rol_name
            FROM users u
            LEFT JOIN roles r ON u.roles_rol_id = r.rol_id
            WHERE u.user_nrun = :rut
            LIMIT 1
        ");
        $stmt->execute([':rut' => $cleanRut]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $targetUser = null;
    }
}

if (!$targetUser) {
    header('Location: usuarios.php');
    exit;
}

$userName = $targetUser['user_name'] . ' ' . $targetUser['user_surname'];
$userRut = $targetUser['user_nrun'] . '-' . $targetUser['user_dvrun'];
$userRole = $targetUser['rol_name'] ?? 'Usuario';

$loansData = getUserLoans($targetUser['user_nrun']);
$activos = $loansData['activos'];
$historial = $loansData['historial'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/Pluma.ico" type="image/x-icon">
    <title>Perfil de Usuario - KYbrary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($targetUser['user_email']); ?></p>
                    
                    <?php 
                        $rolesMap = [
                            '1'             => 'Administrador',
                            '2'             => 'Bibliotecario',
                            '3'             => 'Usuario',
                            'ADMIN'         => 'Administrador',
                            'ADMINISTRADOR' => 'Administrador',
                            'BIBLIOTECARIO' => 'Bibliotecario',
                            'USUARIO'       => 'Usuario'
                        ];
                        $nombreRol = $rolesMap[strtoupper($userRole)] ?? $userRole;
                    ?>
                    <span class="badge-role"><?php echo htmlspecialchars($nombreRol); ?></span>
                    
                    <div style="margin-top: 1.5rem;">
                        <a href="usuarios.php" class="btn-logout" style="background-color: #4a5568; text-decoration: none; display: inline-block;">
                            Volver a usuarios
                        </a>
                    </div>
                </div>
            </div>

            <div id="container-books">
                <?php if (!empty($alertMessage)): ?>
                    <p class="alert" style="margin-bottom: 1rem; color: #fff; padding: 0.8rem; border-radius: 6px; text-align: center; background-color: <?php echo $alertType === 'success' ? '#2e7d32' : '#c62828'; ?>;">
                        <?php echo htmlspecialchars($alertMessage); ?>
                    </p>
                <?php endif; ?>

                <div class="profile-section">
                    <h3>Reservas y préstamos activos</h3>
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
                                        <th style="text-align: center;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activos as $loan): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($loan['book_title'] ?? $loan['BOOK_TITLE'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($loan['unit_id'] ?? $loan['UNIT_ID'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_start'] ?? $loan['DATE_START'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_finish'] ?? $loan['DATE_FINISH'] ?? ''); ?></td>
                                            <td>
                                                <?php $st = strtoupper($loan['state'] ?? $loan['STATE'] ?? ''); ?>
                                                <?php if ($st === 'RESERVADO'): ?>
                                                    <span class="status-badge pending">Listo para Retiro</span>
                                                <?php else: ?>
                                                    <span class="status-badge active">Préstamo Activo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php if ($st === 'RESERVADO'): ?>
                                                    <form method="POST" action="ver_perfil.php" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta reserva?');" style="display: inline;">
                                                        <input type="hidden" name="action" value="cancel_reservation">
                                                        <input type="hidden" name="loan_id" value="<?php echo htmlspecialchars($loan['loan_id'] ?? $loan['LOAN_ID'] ?? ''); ?>">
                                                        <input type="hidden" name="target_rut" value="<?php echo htmlspecialchars($targetUser['user_nrun']); ?>">
                                                        <button type="submit" title="Eliminar Reserva" style="background: none; border: none; color: #ff5252; cursor: pointer; font-size: 1.2rem; padding: 0.2rem 0.5rem; transition: color 0.2s;">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span style="color: #888;">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="empty-msg">El usuario no tiene reservas ni préstamos activos en este momento.</p>
                    <?php endif; ?>
                </div>

                <div class="profile-section">
                    <h3>Historial de prestaciones</h3>
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
                                            <td><?php echo htmlspecialchars($loan['book_title'] ?? $loan['BOOK_TITLE'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($loan['unit_id'] ?? $loan['UNIT_ID'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_start'] ?? $loan['DATE_START'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($loan['date_finish'] ?? $loan['DATE_FINISH'] ?? ''); ?></td>
                                            <td><span class="status-badge returned">Devuelto</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="empty-msg">No hay historial registrado para este usuario.</p>
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