<?php
require_once __DIR__ . '/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function getNrunFromRut($rutInput) {
    $clean = preg_replace('/[^0-9kK-]/', '', trim((string)$rutInput));
    
    if (strpos($clean, '-') !== false) {
        return explode('-', $clean)[0];
    }
    
    if (strlen($clean) === 9) {
        return substr($clean, 0, -1);
    }
    
    return $clean;
}

function renderSidebar() {
    $fullName  = $_SESSION['user_name'] ?? 'Usuario';
    $firstName = explode(' ', trim($fullName))[0];
    $avatarPath = 'media/default_avatar.jpg';
    
    $rawRole   = $_SESSION['user_role'] ?? $_SESSION['rol_name'] ?? $_SESSION['rol_id'] ?? $_SESSION['roles_rol_id'] ?? '1';
    $userRole  = strtoupper((string)$rawRole);
    
    $isLibrarian = in_array($userRole, ['3', 'BIBLIOTECARIO', 'ADMIN', 'ADMINISTRADOR']); 
    ?>
    <aside class="sidebar" id="sidebar">
        <?php if (isset($_SESSION['user_rut'])): ?>
            <div class="sidebar-user-card">
                <div class="user-avatar-circle">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="Foto de perfil">
                </div>
                <span class="user-name"><?php echo htmlspecialchars($firstName); ?></span>
                <a href="perfil.php" class="btn-profile">Perfil</a>
            </div>
        <?php endif; ?>

        <div class="nav-links">
            <a href="index.php" class="nav-item">Inicio</a>
            <a href="catalogo.php" class="nav-item">Catálogo</a>
            
            <?php if ($isLibrarian): ?>
                <a href="escaner.php" class="nav-item">Escáner</a>
                <a href="usuarios.php" class="nav-item">Usuarios</a>
                <a href="add_book.php" class="nav-item">Añadir Libro</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_rut'])): ?>
                <a href="logout.php" class="nav-item logout-item" style="color: #ff6b6b; margin-top: 2rem;">
                    Cerrar sesión
                </a>
            <?php endif; ?>
        </div>
    </aside>
    <?php
}

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
        $sql = "SELECT b.BOOK_ISBN AS \"BOOK_ISBN\", 
                       b.BOOK_TITLE AS \"BOOK_TITLE\", 
                       b.BOOK_AUTHOR AS \"BOOK_AUTHOR\", 
                       b.BOOK_EDITORIAL AS \"BOOK_EDITORIAL\", 
                       c.CATEGORY_NAME AS \"CATEGORY_NAME\"
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
    $titulo    = $book['BOOK_TITLE'] ?? $book['book_title'] ?? 'Título';
    $autor     = $book['BOOK_AUTHOR'] ?? $book['book_author'] ?? 'Autor no especificado';
    $categoria = $book['CATEGORY_NAME'] ?? $book['category_name'] ?? 'Sin Categoría';
    $isbn      = $book['BOOK_ISBN'] ?? $book['book_isbn'] ?? '';

    ?>
    <div class="cBook">
        <img src="icons/book.png" alt="">
        <h1><?php echo htmlspecialchars($titulo); ?></h1>
        <p><?php echo htmlspecialchars($autor); ?></p>
        <small><?php echo htmlspecialchars($categoria); ?></small>
        
        <?php if (!empty($isbn)): ?>
            <form action="catalogo.php" method="POST">
                <input type="hidden" name="action" value="reservar">
                <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>">
                <button type="submit">Reservar Libro</button>
            </form>
        <?php else: ?>
            <button type="button">Reservar Libro</button>
        <?php endif; ?>
    </div>
    <?php
}

function reserveBook($isbn, $rutUser) {
    global $pdo;
    if (!isset($pdo)) {
        return ['success' => false, 'message' => "Error de conexión con la base de datos."];
    }

    $cleanRut = getNrunFromRut($rutUser);
    if (empty($cleanRut)) {
        return ['success' => false, 'message' => "Debes estar autenticado para agendar un libro."];
    }

    // Validación: Máximo 1 reserva activa a la vez
    try {
        $stmtCheckRes = $pdo->prepare("
            SELECT COUNT(*) 
            FROM benefits 
            WHERE users_user_nrun = :rut AND benefits_state = 'RESERVADO'
        ");
        $stmtCheckRes->execute([':rut' => $cleanRut]);
        if ((int)$stmtCheckRes->fetchColumn() > 0) {
            return ['success' => false, 'message' => "Ya tienes una reserva activa. Solo puedes reservar 1 libro a la vez."];
        }
    } catch (PDOException $e) {
        // Continuar si ocurre alguna inconsistencia
    }

    $dateStart  = date('Y-m-d');
    $dateFinish = date('Y-m-d', strtotime("+7 days"));

    try {
        $pdo->beginTransaction();

        $stmtUnit = $pdo->prepare("SELECT UNIT_ID FROM UNIT WHERE BOOK_BOOK_ISBN = :isbn AND UNIT_STATUS = 'DISPONIBLE' LIMIT 1");
        $stmtUnit->execute([':isbn' => $isbn]);
        $unit = $stmtUnit->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            $pdo->rollBack();
            return ['success' => false, 'message' => "No hay ejemplares disponibles para agendar este libro."];
        }

        $unitId = $unit['UNIT_ID'] ?? $unit['unit_id'];

        $stmtUpdateUnit = $pdo->prepare("UPDATE UNIT SET UNIT_STATUS = 'RESERVADO' WHERE UNIT_ID = :unit_id");
        $stmtUpdateUnit->execute([':unit_id' => $unitId]);

        $stmtBenefits = $pdo->prepare("
            INSERT INTO BENEFITS (BENEFITS_ID, DATE_START, DATE_FINISH, BENEFITS_STATE, USERS_USER_NRUN)
            VALUES ((SELECT COALESCE(MAX(BENEFITS_ID), 0) + 1 FROM BENEFITS), :d_start, :d_finish, 'RESERVADO', :rut)
            RETURNING BENEFITS_ID
        ");
        $stmtBenefits->execute([
            ':d_start'  => $dateStart,
            ':d_finish' => $dateFinish,
            ':rut'      => $cleanRut
        ]);
        $benefitId = $stmtBenefits->fetchColumn();

        $stmtDetail = $pdo->prepare("
            INSERT INTO DETAIL_BOOK (DETAIL_ID, BENEFITS_BENEFITS_ID, UNIT_UNIT_ID)
            VALUES ((SELECT COALESCE(MAX(DETAIL_ID), 0) + 1 FROM DETAIL_BOOK), :benefit_id, :unit_id)
        ");
        $stmtDetail->execute([':benefit_id' => $benefitId, ':unit_id' => $unitId]);

        $pdo->commit();
        return [
            'success' => true, 
            'message' => "¡Libro agendado exitosamente! Tu copia (Ejemplar #{$unitId}) ha sido reservada."
        ];

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return ['success' => false, 'message' => "Error al agendar el libro: " . $e->getMessage()];
    }
}

function getBookByBarcode($barcode) {
    global $pdo;
    if (!isset($pdo)) return null;

    $barcode = trim((string)$barcode);
    if (empty($barcode)) return null;

    try {
        $sql = "SELECT 
                    b.BOOK_ISBN AS \"BOOK_ISBN\", 
                    b.BOOK_TITLE AS \"BOOK_TITLE\", 
                    b.BOOK_AUTHOR AS \"BOOK_AUTHOR\", 
                    b.BOOK_EDITORIAL AS \"BOOK_EDITORIAL\", 
                    c.CATEGORY_NAME AS \"CATEGORY_NAME\",
                    COUNT(u.UNIT_ID) AS \"STOCK\"
                FROM BOOK b
                LEFT JOIN CATEGORIES c ON b.CATEGORIES_CATEGORY_ID = c.CATEGORY_ID
                LEFT JOIN UNIT u ON b.BOOK_ISBN = u.BOOK_BOOK_ISBN AND u.UNIT_STATUS = 'DISPONIBLE'
                WHERE b.BOOK_ISBN = :barcode1 
                   OR b.BOOK_ISBN = (
                       SELECT BOOK_BOOK_ISBN 
                       FROM UNIT 
                       WHERE CAST(UNIT_ID AS VARCHAR) = :barcode2 
                       LIMIT 1
                   )
                GROUP BY b.BOOK_ISBN, b.BOOK_TITLE, b.BOOK_AUTHOR, b.BOOK_EDITORIAL, c.CATEGORY_NAME";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':barcode1' => $barcode,
            ':barcode2' => $barcode
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

function processLoanByBarcode($barcode, $rutUser, $diasPrestamo = 7) {
    global $pdo;

    if (!isset($pdo) || $pdo === null) {
        return [
            'success' => false, 
            'message' => "Error: No hay conexión con la base de datos (\$pdo es null). Revisa src/database.php."
        ];
    }

    $cleanRut = getNrunFromRut($rutUser);
    if (empty($cleanRut)) {
        return ['success' => false, 'message' => "Debes ingresar un RUT válido."];
    }

    // 1. Verificar si el usuario existe
    try {
        $stmtUser = $pdo->prepare("SELECT user_nrun FROM users WHERE user_nrun = :rut LIMIT 1");
        $stmtUser->execute([':rut' => $cleanRut]);
        if (!$stmtUser->fetch()) {
            return ['success' => false, 'message' => "El RUT ingresado ($rutUser) no está registrado."];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Error al consultar usuario: " . $e->getMessage()];
    }

    $barcodeClean = trim((string)$barcode);
    $dateStart = date('Y-m-d');
    $dateFinish = date('Y-m-d', strtotime("+$diasPrestamo days"));

    try {
        $pdo->beginTransaction();

        // 2. Buscar la unidad o libro
        $stmtUnit = $pdo->prepare("
            SELECT u.unit_id, u.unit_status, b.book_title 
            FROM unit u
            JOIN book b ON u.book_book_isbn = b.book_isbn
            WHERE CAST(u.unit_id AS VARCHAR) = :barcode1 OR b.book_isbn = :barcode2
            ORDER BY CASE WHEN u.unit_status = 'DISPONIBLE' THEN 1 ELSE 2 END
            LIMIT 1
        ");
        $stmtUnit->execute([':barcode1' => $barcodeClean, ':barcode2' => $barcodeClean]);
        $unit = $stmtUnit->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            $pdo->rollBack();
            return ['success' => false, 'message' => "No se encontró ejemplar o libro con el código ($barcodeClean)."];
        }

        $unitId = $unit['unit_id'];
        $unitStatus = strtoupper($unit['unit_status']);
        $bookTitle = $unit['book_title'];

        if ($unitStatus === 'PRESTADO') {
            $pdo->rollBack();
            return ['success' => false, 'message' => "El ejemplar ya se encuentra PRESTADO."];
        }

        // 3. Verificar si tenía reserva previa
        $stmtReserva = $pdo->prepare("
            SELECT bn.benefits_id 
            FROM benefits bn
            JOIN detail_book db ON db.benefits_benefits_id = bn.benefits_id
            WHERE bn.users_user_nrun = :rut 
              AND db.unit_unit_id = :unit_id 
              AND bn.benefits_state = 'RESERVADO'
            LIMIT 1
        ");
        $stmtReserva->execute([':rut' => $cleanRut, ':unit_id' => $unitId]);
        $reserva = $stmtReserva->fetch(PDO::FETCH_ASSOC);

        if ($unitStatus === 'RESERVADO' && !$reserva) {
            $pdo->rollBack();
            return ['success' => false, 'message' => "El ejemplar está RESERVADO por otro usuario."];
        }

        // 4. Registrar el préstamo
        if ($reserva) {
            $stmtUpdateBen = $pdo->prepare("
                UPDATE benefits 
                SET benefits_state = 'ACTIVO', date_start = :d_start, date_finish = :d_finish 
                WHERE benefits_id = :ben_id
            ");
            $stmtUpdateBen->execute([
                ':d_start'  => $dateStart,
                ':d_finish' => $dateFinish,
                ':ben_id'   => $reserva['benefits_id']
            ]);
        } else {
            $stmtBenefits = $pdo->prepare("
                INSERT INTO benefits (benefits_id, date_start, date_finish, benefits_state, users_user_nrun)
                VALUES ((SELECT COALESCE(MAX(benefits_id), 0) + 1 FROM benefits), :d_start, :d_finish, 'ACTIVO', :rut)
                RETURNING benefits_id
            ");
            $stmtBenefits->execute([
                ':d_start'  => $dateStart,
                ':d_finish' => $dateFinish,
                ':rut'      => $cleanRut
            ]);
            $benefitId = $stmtBenefits->fetchColumn();

            $stmtDetail = $pdo->prepare("
                INSERT INTO detail_book (detail_id, benefits_benefits_id, unit_unit_id)
                VALUES ((SELECT COALESCE(MAX(detail_id), 0) + 1 FROM detail_book), :benefit_id, :unit_id)
            ");
            $stmtDetail->execute([':benefit_id' => $benefitId, ':unit_id' => $unitId]);
        }

        // Actualizar estado a PRESTADO
        $stmtUpdateUnit = $pdo->prepare("UPDATE unit SET unit_status = 'PRESTADO' WHERE unit_id = :unit_id");
        $stmtUpdateUnit->execute([':unit_id' => $unitId]);

        $pdo->commit();

        $fechaFormat = date('d/m/Y', strtotime($dateFinish));
        return [
            'success' => true,
            'message' => "Préstamo realizado con éxito para \"{$bookTitle}\" (Ejemplar #{$unitId}). Devolución: {$fechaFormat}."
        ];

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => "Error en la base de datos: " . $e->getMessage()];
    }
}

function processReturnByBarcode($barcode) {
    global $pdo;
    if (!isset($pdo)) {
        return ['success' => false, 'message' => "Error de conexión con la base de datos."];
    }

    try {
        $stmt = $pdo->prepare("SELECT fn_registrar_devolucion(:barcode) AS respuesta");
        $stmt->execute([
            ':barcode' => trim((string)$barcode)
        ]);

        $respuesta = $stmt->fetchColumn();

        if ($respuesta && strpos($respuesta, 'OK:') === 0) {
            return [
                'success' => true, 
                'message' => trim(substr($respuesta, 3))
            ];
        } else {
            $errorMsg = $respuesta ? trim(substr($respuesta, 6)) : "Ocurrió un error inesperado en la BD.";
            return [
                'success' => false, 
                'message' => $errorMsg
            ];
        }

    } catch (PDOException $e) {
        return [
            'success' => false, 
            'message' => "Error PDO: " . $e->getMessage()
        ];
    }
}

function getUserLoans($rutUser) {
    global $pdo;
    if (!isset($pdo)) return ['activos' => [], 'historial' => []];

    $cleanRut = getNrunFromRut($rutUser);

    try {
        $sql = "SELECT 
                    bn.BENEFITS_ID AS loan_id,
                    bn.DATE_START AS date_start,
                    bn.DATE_FINISH AS date_finish,
                    bn.BENEFITS_STATE AS state,
                    u.UNIT_ID AS unit_id,
                    b.BOOK_TITLE AS book_title,
                    b.BOOK_AUTHOR AS book_author,
                    b.BOOK_ISBN AS book_isbn
                FROM BENEFITS bn
                JOIN DETAIL_BOOK db ON db.BENEFITS_BENEFITS_ID = bn.BENEFITS_ID
                JOIN UNIT u ON u.UNIT_ID = db.UNIT_UNIT_ID
                JOIN BOOK b ON u.BOOK_BOOK_ISBN = b.BOOK_ISBN
                WHERE bn.USERS_USER_NRUN = :rut
                ORDER BY bn.DATE_START DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':rut' => $cleanRut]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activos = [];
        $historial = [];

        foreach ($rows as $row) {
            if ($row['state'] === 'ACTIVO' || $row['state'] === 'RESERVADO') {
                $activos[] = $row;
            } else {
                $historial[] = $row;
            }
        }

        return ['activos' => $activos, 'historial' => $historial];

    } catch (PDOException $e) {
        return ['activos' => [], 'historial' => []];
    }
}

function addNewBook($isbn, $title, $author, $editorial, $categoryId, $copies = 1, $imagePath = 'icons/book.png') {
    global $pdo;
    if (!isset($pdo)) {
        return ['success' => false, 'message' => "Error de conexión a la base de datos."];
    }

    try {
        $stmt = $pdo->prepare("CALL sp_agregar_libro(:isbn, :title, :author, :editorial, :cat_id, :copias, :imagen)");
        $stmt->execute([
            ':isbn'      => trim($isbn),
            ':title'     => trim($title),
            ':author'    => trim($author),
            ':editorial' => trim($editorial),
            ':cat_id'    => (int)$categoryId,
            ':copias'    => max(1, (int)$copies),
            ':imagen'    => $imagePath
        ]);

        return ['success' => true, 'message' => "Libro y sus ejemplares registrados exitosamente."];

    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            return ['success' => false, 'message' => "El ISBN ingresado ya se encuentra registrado."];
        }
        return ['success' => false, 'message' => "Error al guardar el libro: " . $e->getMessage()];
    }
}

function registerUserWithToken($nrun, $dvrun, $name, $surname, $email, $passwordHash, $roleId, $token) {
    global $pdo;
    if (!isset($pdo)) {
        return ['success' => false, 'message' => "Error de conexión a la base de datos."];
    }

    try {
        $stmt = $pdo->prepare("SELECT fn_registrar_usuario(:nrun, :dvrun, :name, :surname, :email, :pass, :rol, :token) AS respuesta");
        $stmt->execute([
            ':nrun'    => trim($nrun),
            ':dvrun'   => strtoupper(trim($dvrun)),
            ':name'    => trim($name),
            ':surname' => trim($surname),
            ':email'   => strtolower(trim($email)),
            ':pass'    => $passwordHash,
            ':rol'     => (int)$roleId,
            ':token'   => $token
        ]);

        $respuesta = $stmt->fetchColumn();

        if ($respuesta && strpos($respuesta, 'OK:') === 0) {
            return ['success' => true, 'message' => trim(substr($respuesta, 3))];
        } else {
            $errorMsg = $respuesta ? trim(substr($respuesta, 6)) : "Error al registrar el usuario.";
            return ['success' => false, 'message' => $errorMsg];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Error PDO: " . $e->getMessage()];
    }
}

function verifyUserCode($email, $code) {
    global $pdo;
    if (!isset($pdo)) return false;

    try {
        $stmt = $pdo->prepare("SELECT fn_verificar_codigo(:email, :code)");
        $stmt->execute([
            ':email' => strtolower(trim($email)),
            ':code'  => trim($code)
        ]);
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return false;
    }
}

function getTrendingBooks($limit = 3) {
    global $pdo;

    if (!isset($pdo)) {
        return [];
    }

    try {
        $sql = "SELECT 
                    b.book_isbn, 
                    b.book_title, 
                    b.book_author, 
                    COUNT(db.detail_id) AS total_prestamos
                FROM book b
                LEFT JOIN unit u ON b.book_isbn = u.book_book_isbn
                LEFT JOIN detail_book db ON u.unit_id = db.unit_unit_id
                GROUP BY b.book_isbn, b.book_title, b.book_author
                ORDER BY total_prestamos DESC, b.book_isbn DESC
                LIMIT :limit";
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        try {
            $stmt = $pdo->prepare("SELECT book_isbn, book_title, book_author FROM book ORDER BY book_isbn DESC LIMIT :limit");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return [];
        }
    }
}

function getRecentBooks($limit = 3) {
    global $pdo;

    if (!isset($pdo)) {
        return [];
    }

    try {
        $sql = "SELECT 
                    book_isbn, 
                    book_title, 
                    book_author 
                FROM book 
                ORDER BY book_isbn DESC 
                LIMIT :limit";
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function sendVerificationEmail($email, $code) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'm.arroyocriollo@liceorbl.cl';
        $mail->Password   = 'ckdmrknfeazenmtg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('m.arroyocriollo@liceorbl.cl', 'KYbrary');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Código de verificación - KYbrary';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; background-color: #121212; color: #ffffff; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #9; font-size: 2.4rem'>Verificación de cuenta - KYbrary</h2>
                <p style='font-size: 1.6rem;'>Tu código de verificación es:</p>
                <div style='background: #1e1e1e; padding: 15px; font-size: 28px; font-weight: bold; letter-spacing: 1.5rem; text-align: center; color: #ff0033; border: 1px solid #ff0033; border-radius: 6px;'>
                    {$code}
                </div>
                <p style='margin-top: 15px; font-size: 1.6rem; color: #888;'>Ingresa este código en la pantalla de verificación para activar tu cuenta.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function getUsersForManagement($search = '', $filter = '') {
    global $pdo;
    if (!isset($pdo)) return [];

    try {
        $sql = "
            SELECT 
                u.user_nrun,
                u.user_dvrun,
                u.user_name,
                u.user_surname,
                u.user_email,
                r.rol_name,
                COUNT(CASE WHEN b.benefits_state = 'ACTIVO' AND b.date_finish >= CURRENT_DATE THEN 1 END) AS prestamos_activos,
                COUNT(CASE WHEN b.benefits_state = 'RESERVADO' THEN 1 END) AS reservas_pendientes,
                COUNT(CASE WHEN b.benefits_state = 'ACTIVO' AND b.date_finish < CURRENT_DATE THEN 1 END) AS atrasados
            FROM users u
            LEFT JOIN roles r ON u.roles_rol_id = r.rol_id
            LEFT JOIN benefits b ON u.user_nrun = b.users_user_nrun
            WHERE 1=1
        ";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (LOWER(u.user_name) LIKE :search OR LOWER(u.user_surname) LIKE :search OR u.user_nrun LIKE :search OR LOWER(u.user_email) LIKE :search)";
            $params[':search'] = '%' . strtolower(trim($search)) . '%';
        }

        $sql .= " GROUP BY u.user_nrun, u.user_dvrun, u.user_name, u.user_surname, u.user_email, r.rol_name";

        if ($filter === 'activa') {
            $sql .= " HAVING COUNT(CASE WHEN b.benefits_state = 'ACTIVO' AND b.date_finish >= CURRENT_DATE THEN 1 END) > 0";
        } elseif ($filter === 'pendiente') {
            $sql .= " HAVING COUNT(CASE WHEN b.benefits_state = 'RESERVADO' THEN 1 END) > 0";
        } elseif ($filter === 'atrasado') {
            $sql .= " HAVING COUNT(CASE WHEN b.benefits_state = 'ACTIVO' AND b.date_finish < CURRENT_DATE THEN 1 END) > 0";
        } elseif ($filter === 'sin_prestamos') {
            $sql .= " HAVING COUNT(CASE WHEN b.benefits_state IN ('ACTIVO', 'RESERVADO') THEN 1 END) = 0";
        }

        $sql .= " ORDER BY u.user_surname ASC, u.user_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        return [];
    }
}?>