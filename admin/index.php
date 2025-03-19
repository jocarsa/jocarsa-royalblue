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
    <title>jocarsa | royalblue</title>
      <link rel="icon" type="image/svg+xml" href="../royalblue.png">
    <link rel="stylesheet" href="css/estilo.css">
    <script>
      // Función para alternar la visibilidad del panel de ayuda del admin
      function toggleHelp() {
          var helpPanel = document.getElementById('adminHelpPanel');
          if (helpPanel.style.right === "0px") {
              helpPanel.style.right = "-300px";
          } else {
              helpPanel.style.right = "0px";
          }
      }
    </script>
</head>
<body>
<div class="admin-wrapper">
    <header class="admin-header">
        <div class="logo">
            <h1><img src="../royalblue.png" alt="Logo"> jocarsa | royalblue</h1>
        </div>
        <div class="user-info">
            <span>Hola, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?>!</span>
            <a href="?action=logout" class="logout-link">Cerrar sesión</a>
        </div>
    </header>
    <div class="admin-body">
        <nav class="admin-nav">
            <ul>
                <li><a href="?action=resources" class="<?php echo ($action=='resources') ? 'active' : ''; ?>">Recursos</a></li>
                <li><a href="?action=resource_availability" class="<?php echo ($action=='resource_availability') ? 'active' : ''; ?>">Disponibilidad</a></li>
                <li><a href="?action=festivos" class="<?php echo ($action=='festivos') ? 'active' : ''; ?>">Festivos</a></li>
                <li><a href="?action=reservations" class="<?php echo ($action=='reservations') ? 'active' : ''; ?>">Reservas</a></li>
                <li><a href="?action=invoices" class="<?php echo ($action=='invoices') ? 'active' : ''; ?>">Facturas</a></li>
                <li><a href="?action=users" class="<?php echo ($action=='users') ? 'active' : ''; ?>">Usuarios</a></li>
                <li><a href="?action=customers" class="<?php echo ($action=='customers') ? 'active' : ''; ?>">Clientes</a></li>
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
    <!-- Icono para alternar la ayuda -->
    <div class="help-toggle" onclick="toggleHelp()">
        <img src="../royalblue.png" alt="Ayuda" title="Ayuda">
    </div>
    <!-- Panel de Ayuda del Admin (oculto por defecto) -->
    <div id="adminHelpPanel" class="admin-help">
        <h3>Ayuda - <?php echo ucfirst($action); ?></h3>
        <div class="help-content">
            <?php
            // Contenido de ayuda según la sección (en español)
            switch($action) {
                case 'resources':
                    echo "<p>En esta sección puedes administrar los recursos. Aquí puedes agregar, editar o eliminar recursos.</p>";
                    break;
                case 'resource_availability':
                    echo "<p>Configura la disponibilidad de cada recurso por hora y día. Marca las horas en las que el recurso está disponible.</p>";
                    break;
                case 'festivos':
                    echo "<p>Gestiona los días festivos. Los festivos se mostrarán como no disponibles en el calendario.</p>";
                    break;
                case 'reservations':
                    echo "<p>Visualiza y administra las reservas realizadas por los clientes.</p>";
                    break;
                case 'invoices':
                    echo "<p>Aquí puedes crear y ver facturas para las reservas realizadas.</p>";
                    break;
                case 'users':
                    echo "<p>Administra los usuarios del sistema. Puedes agregar, editar o eliminar usuarios.</p>";
                    break;
                case 'customers':
                    echo "<p>Consulta la información de los clientes y sus reservas.</p>";
                    break;
                default:
                    echo "<p>Selecciona una sección del menú para ver la ayuda correspondiente.</p>";
                    break;
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>

