<?php
session_start();
require_once __DIR__ . '/../config.php';
date_default_timezone_set(TIMEZONE);

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB (superadmin): " . $ex->getMessage());
}

// Include superadmin login system
include "inc/logout.php";
include "inc/login.php"; // If not logged in or not superadmin, it will show login form.

// IMPORTANT: Only require gestionarAdmins.php once
require_once "funciones/gestionarAdmins.php";

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Superadmin Panel - Jocarsa</title>
    <link rel="stylesheet" href="../admin/css/estilo.css"> <!-- or your custom CSS -->
</head>
<body>
<div class="admin-wrapper">
    <header class="admin-header">
        <div class="logo">
            <h1>Superadmin Panel</h1>
        </div>
        <div class="user-info">
            <span>Superadmin: <?php echo htmlspecialchars($_SESSION['superadmin_username'] ?? ''); ?></span>
            <a href="?action=logout" class="logout-link">Cerrar sesión</a>
        </div>
    </header>
    <div class="admin-body">
        <nav class="admin-nav">
            <ul>
                <li><a href="?action=list" <?php if($action=='list') echo 'class="active"'; ?>>Administrar Usuarios</a></li>
                <li><a href="?action=add" <?php if($action=='add') echo 'class="active"'; ?>>Nuevo Admin</a></li>
            </ul>
        </nav>
        <main class="admin-content">
            <?php
            switch($action) {
                case 'list':
                    gestionarAdmins($pdo);
                    break;
                case 'edit':
                    editarAdmin($pdo);
                    break;
                case 'add':
                    nuevoAdmin($pdo);
                    break;
                case 'logout':
                    // handled in inc/logout.php
                    break;
                default:
                    echo "<h2>Bienvenido al Panel Superadmin</h2>";
                    echo "<p>Selecciona una opción del menú.</p>";
                    break;
            }
            ?>
        </main>
    </div>
</div>
</body>
</html>

