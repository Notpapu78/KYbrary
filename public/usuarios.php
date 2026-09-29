<?php 
session_start();
require_once __DIR__ . '/../src/functions.php'; 

$userRole = strtoupper($_SESSION['user_role'] ?? $_SESSION['rol_name'] ?? $_SESSION['rol_id'] ?? $_SESSION['roles_rol_id'] ?? 'USUARIO');
$isAdminOrLibrarian = in_array($userRole, ['ADMIN', 'ADMINISTRADOR', 'BIBLIOTECARIO', '1', '2', '3']);

if (!$isAdminOrLibrarian) {
    header('Location: index.php');
    exit;
}

$message = '';
$msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_penalty'])) {
    $rut = $_POST['rut'] ?? '';
    $action = $_POST['action_penalty'];
    
    if (!empty($rut)) {
        if (updateUserPenaltyStatus($rut, $action)) {
            $message = ($action === 'sancionar') ? 'Usuario sancionado por 1 mes.' : 'Sanción eliminada correctamente.';
            $msgType = 'success';
        } else {
            $message = 'Error al actualizar el estado del usuario.';
            $msgType = 'error';
        }
    }
}

$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$users = getUsersForManagement($search, $filter_status); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/users.css">
    <title>Gestión de Usuarios - KYbrary</title>
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
        <div class="user-container">
            <h1 style="text-align: center; margin-bottom: 1.5rem;">Gestión de Usuarios</h1>

            <?php if (!empty($message)): ?>
                <p style="text-align: center; margin-bottom: 1.5rem; color: <?php echo $msgType === 'success' ? '#00ffff' : '#ff4d4d'; ?>; font-weight: bold; font-size: 1.4rem;">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endif; ?>

            <form method="GET" action="usuarios.php" class="filter-bar">
                <input type="text" name="search" placeholder="Buscar por RUT, nombre o apellido..." value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="status">
                    <option value="" style="background: #111;">Todos los estados</option>
                    <option value="activa" <?php echo $filter_status === 'activa' ? 'selected' : ''; ?> style="background: #111;">Con préstamo activo</option>
                    <option value="pendiente" <?php echo $filter_status === 'pendiente' ? 'selected' : ''; ?> style="background: #111;">Con reserva pendiente</option>
                    <option value="atrasado" <?php echo $filter_status === 'atrasado' ? 'selected' : ''; ?> style="background: #111;">Con atrasos</option>
                    <option value="sancionado" <?php echo $filter_status === 'sancionado' ? 'selected' : ''; ?> style="background: #111;">Sancionados</option>
                    <option value="sin_prestamos" <?php echo $filter_status === 'sin_prestamos' ? 'selected' : ''; ?> style="background: #111;">Sin actividad</option>
                </select>

                <button type="submit">Buscar</button>
            </form>

            <div class="user-grid">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <?php 
                            $rutCompleto = htmlspecialchars($u['user_nrun'] . '-' . $u['user_dvrun']);
                            $nombreCompleto = htmlspecialchars($u['user_name'] . ' ' . $u['user_surname']);
                            $email = htmlspecialchars($u['user_email']);
                            $rol = htmlspecialchars($u['rol_name'] ?? 'Usuario');
                            
                            $estaSancionado = !empty($u['banned_until']) && strtotime($u['banned_until']) >= strtotime(date('Y-m-d'));
                            $bannedUntilFormatted = $estaSancionado ? date('d/m/Y', strtotime($u['banned_until'])) : '';
                        ?>
                        <div class="user-card <?php echo $estaSancionado ? 'banned' : ''; ?>">
                            <div class="user-info">
                                <h3><?php echo $nombreCompleto; ?></h3>
                                <p><strong>RUT:</strong> <?php echo $rutCompleto; ?></p>
                                <p><strong>Email:</strong> <?php echo $email; ?></p>
                                <p><strong>Rol:</strong> <?php echo $rol; ?></p>
                                
                                <div style="margin-top: 0.8rem; display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    <?php if ($estaSancionado): ?>
                                        <span class="badge badge-banned">Sancionado hasta <?php echo $bannedUntilFormatted; ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-active">Activo</span>
                                    <?php endif; ?>

                                    <?php if ((int)($u['atrasados'] ?? 0) > 0): ?>
                                        <span class="badge badge-overdue"><?php echo $u['atrasados']; ?> Atraso(s)</span>
                                    <?php endif; ?>

                                    <?php if ((int)($u['reservas_pendientes'] ?? 0) > 0): ?>
                                        <span class="badge badge-pending"><?php echo $u['reservas_pendientes']; ?> Reserva(s)</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="user-actions">
                                <form method="POST" action="usuarios.php">
                                    <input type="hidden" name="rut" value="<?php echo htmlspecialchars($u['user_nrun']); ?>">
                                    
                                    <?php if ($estaSancionado): ?>
                                        <input type="hidden" name="action_penalty" value="quitar">
                                        <button type="submit" class="btn-unban" onclick="return confirm('¿Quitar la sanción a este usuario?');">
                                            🔓 Quitar Sanción
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="action_penalty" value="sancionar">
                                        <button type="submit" class="btn-ban" onclick="return confirm('¿Aplicar sanción de 1 mes a este usuario?');">
                                            🚫 Sancionar (1 mes)
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: var(--gris_plata); font-size: 1.4rem; padding: 3rem;">
                        No se encontraron usuarios en la base de datos.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/sidebar.js"></script>
</body>
</html>