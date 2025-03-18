<?php
session_start();
require_once __DIR__ . '/../config.php';
date_default_timezone_set(TIMEZONE);

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}

// Include login / logout
include "inc/logout.php";
include "inc/login.php";  // shows login if not logged in

$ownerId = $_SESSION['admin_id'] ?? 0;
$action = $_GET['action'] ?? 'reservations';

// Load your admin functions
require_once 'funciones/gestionarRecursos.php';
require_once 'funciones/gestionarFestivos.php';
require_once 'funciones/visualizarReservas.php';
require_once 'funciones/gestionarUsuarios.php'; 
require_once 'funciones/gestionarClientes.php';
require_once 'funciones/viewCustomerReservations.php';
require_once 'funciones/gestionarHorarioCompleto.php';
require_once 'funciones/gestionarInvoices.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Jocarsa Admin</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="admin-wrapper">
    <header class="admin-header">
        <div class="logo">
            <h1><img src="../royalblue.png">jocarsa | royalblue</h1>
        </div>
        <div class="user-info">
            <span>Hola, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?>!</span>
            <a href="?action=logout" class="logout-link">Cerrar sesión</a>
        </div>
    </header>
    <div class="admin-body">
        <nav class="admin-nav">
            <ul>
                <li><a href="?action=resources">Recursos</a></li>
                <li><a href="?action=resource_availability">Disponibilidad</a></li>
                <li><a href="?action=festivos">Festivos</a></li>
                <li><a href="?action=reservations">Reservas</a></li>
                <li><a href="?action=invoices">Facturas</a></li>
                <li><a href="?action=users">Usuarios</a></li>
                <li><a href="?action=customers">Clientes</a></li>
            </ul>
        </nav>
        <main class="admin-content">
            <?php
            switch($action) {
                case 'resources':
                    gestionarRecursos($pdo, $ownerId);
                    break;
                case 'add_resource':
                    include 'recursos_insert.php';
                    break;
                case 'edit_resource':
                    include 'recursos_edit.php';
                    break;
                
                case 'resource_availability':
                    include 'horario_recursos.php';
                    break;
                
                case 'festivos':
                    gestionarFestivos($pdo, $ownerId);
                    break;
                case 'add_festivo':
                    include 'festivos_insert.php';
                    break;
                case 'edit_festivo':
                    include 'festivos_edit.php';
                    break;
                
                case 'reservations':
                    visualizarReservas($pdo, $ownerId);
                    break;
                case 'add_reserva':
                    include 'reservas_insert.php';
                    break;
                case 'edit_reserva':
                    include 'reservas_edit.php';
                    break;
                
                case 'invoices':
                    gestionarInvoices($pdo, $ownerId);
                    break;
                
                case 'users':
                    gestionarUsuarios($pdo, $ownerId);
                    break;
                case 'add_usuario':
                    include 'usuarios_insert.php';
                    break;
                case 'edit_usuario':
                    include 'usuarios_edit.php';
                    break;
                
                case 'customers':
                    gestionarClientes($pdo, $ownerId);
                    break;
                case 'view_customer_reservations':
                    if (isset($_GET['email'])) {
                        viewCustomerReservations($pdo, $ownerId, $_GET['email']);
                    } else {
                        echo "<p>Email de cliente no especificado.</p>";
                    }
                    break;
                
                case 'logout':
                    // handled in inc/logout.php
                    break;
                
                default:
                    echo "<h2>Bienvenido al Panel de Control</h2>";
                    echo "<p>Selecciona una sección del menú.</p>";
                    break;
            }
            ?>
        </main>
    </div>
</div>
</body>
</html>

