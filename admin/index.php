<?php
session_start();

// Incluir archivo de configuración
require_once __DIR__ . '/../config.php';
date_default_timezone_set(TIMEZONE);

// Conexión a SQLite utilizando DB_PATH
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}

// Incluir funciones y módulos necesarios
require_once 'funciones/crearTablas.php';
require_once 'funciones/insertarUsuarioAdminInicial.php';
require_once 'funciones/gestionarRecursos.php';
require_once 'funciones/gestionarFestivos.php';
require_once 'funciones/visualizarReservas.php';
require_once 'funciones/gestionarUsuarios.php';
// New files for customer management:
require_once 'funciones/gestionarClientes.php';
require_once 'funciones/viewCustomerReservations.php';

// Crear tablas si no existen y usuario admin inicial
crearTablas($pdo);
insertarUsuarioAdminInicial($pdo);

// Manejo de login / logout
include "inc/logout.php";
include "inc/login.php";  // Si no está logueado, este archivo redirige o muestra el login.

// Set default action to "reservations" after login
$action = isset($_GET['action']) ? $_GET['action'] : 'reservations';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Jocarsa Admin</title>
    <link rel="stylesheet" href="css/estilo.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-wrapper">
        <header class="admin-header">
            <div class="logo">
                <h1><img src="../royalblue.png">jocarsa | royalblue</h1>
            </div>
            <div class="user-info">
                <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</span>
                <a href="?action=logout" class="logout-link">Cerrar sesión</a>
            </div>
        </header>
        <div class="admin-body">
            <nav class="admin-nav">
                <ul>
                    <li><a href="?action=resources" <?php if($action=='resources') echo 'class="active"'; ?>>Recursos</a></li>
                    <li><a href="?action=resource_availability" <?php if($action=='resource_availability') echo 'class="active"'; ?>>Disponibilidad por Recurso</a></li>
                    <li><a href="?action=festivos" <?php if($action=='festivos') echo 'class="active"'; ?>>Festivos</a></li>
                    <li><a href="?action=reservations" <?php if($action=='reservations') echo 'class="active"'; ?>>Reservas</a></li>
                    <li><a href="?action=users" <?php if($action=='users') echo 'class="active"'; ?>>Usuarios</a></li>
                    <li><a href="?action=customers" <?php if($action=='customers') echo 'class="active"'; ?>>Clientes</a></li>
                </ul>
            </nav>
            <main class="admin-content">
                <?php
                switch($action) {
                    // Recursos
                    case 'resources':
                        gestionarRecursos($pdo);
                        break;
                    case 'add_resource':
                        include 'recursos_insert.php';
                        break;
                    case 'edit_resource':
                        include 'recursos_edit.php';
                        break;
                    // Disponibilidad por Recurso
                    case 'resource_availability':
                        include 'horario_recursos.php';
                        break;
                    // Festivos
                    case 'festivos':
                        gestionarFestivos($pdo);
                        break;
                    case 'add_festivo':
                        include 'festivos_insert.php';
                        break;
                    case 'edit_festivo':
                        include 'festivos_edit.php';
                        break;
                    // Reservas
                    case 'reservations':
                        visualizarReservas($pdo);
                        break;
                    case 'add_reserva':
                        include 'reservas_insert.php';
                        break;
                    case 'edit_reserva':
                        include 'reservas_edit.php';
                        break;
                    // Usuarios
                    case 'users':
                        gestionarUsuarios($pdo);
                        break;
                    case 'add_usuario':
                        include 'usuarios_insert.php';
                        break;
                    case 'edit_usuario':
                        include 'usuarios_edit.php';
                        break;
                    // Clientes (new)
                    case 'customers':
                        gestionarClientes($pdo);
                        break;
                    case 'view_customer_reservations':
                        if (isset($_GET['email'])) {
                            viewCustomerReservations($pdo, $_GET['email']);
                        } else {
                            echo "<p>Email de cliente no especificado.</p>";
                        }
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

