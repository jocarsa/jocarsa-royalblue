<?php
session_start();
require_once 'inc/dbinit.php';

// Original tenant slug and tenant ID logic
if (isset($_GET['id'])) {
    // Store the tenant slug in the session
    $_SESSION['front_tenant_slug'] = $_GET['id'];
    
    // Query the tenant record based on the slug.
    // Adjust the query if your tenant identifier is stored in a different column.
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE LOWER(business_name) = LOWER(:tenant) LIMIT 1");
    $stmt->execute([':tenant' => $_GET['id']]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tenant) {
        $_SESSION['front_tenant_id'] = $tenant['id'];
    } else {
        // Handle the case when the tenant is not found.
        $_SESSION['front_tenant_id'] = 0;
    }
} else {
    // If "id" is not set, define a default tenant.
    $_SESSION['front_tenant_slug'] = 'default';
    $_SESSION['front_tenant_id']   = 0;
}

// Determine the current step in the reservation process.
$step = isset($_GET['step']) ? $_GET['step'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>jocarsa | royalblue</title>
  <link rel="icon" type="image/svg+xml" href="royalblue.png">
  <link rel="stylesheet" href="css/estilo.css">
  <style>
    /* Additional styles for the floating help button and panel */
    .front-help-toggle {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 50px;
      height: 50px;
      background: #007bff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 1000;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .front-help-toggle img {
      width: 24px;
      height: 24px;
    }
    .front-help-panel {
      position: fixed;
      top: 0;
      right: -300px; /* Hidden by default */
      width: 300px;
      height: 100%;
      background: #f9f9f9;
      box-shadow: -2px 0 5px rgba(0,0,0,0.2);
      padding: 20px;
      overflow-y: auto;
      transition: right 0.3s ease;
      z-index: 999;
    }
    .front-help-panel h3 {
      color: #007bff;
      margin-bottom: 10px;
    }
    .front-help-panel p {
      font-size: 14px;
      margin-bottom: 10px;
      color: #333;
    }
  </style>
  <script>
    // Function to toggle the visibility of the help panel
    function toggleFrontHelp() {
      var panel = document.getElementById('frontHelpPanel');
      if (panel.style.right === "0px") {
        panel.style.right = "-300px";
      } else {
        panel.style.right = "0px";
      }
    }
  </script>
</head>
<body>
  <!-- (Optional) Header -->
  <header>
    
    <h1 style="color:white;">jocarsa | royalblue</h1>
  </header>

  <!-- Main content area -->
  <main>
    <?php
      // Include the appropriate step file without using exit, so the help panel is rendered.
      if ($step == 1) {
          include "partes/recurso.php";
      } elseif ($step == 2) {
          include "partes/calendariosemanal.php";
      } elseif ($step == 3) {
          include "partes/datospersonales.php";
      } elseif ($step == 4) {
          include "partes/confirmacionyguardado.php";
      } elseif (isset($_GET['step']) && $_GET['step'] === 'done') {
          include "partes/mensajefinal.php";
      } else {
          // Fallback to resource selection if no valid step is given.
          include "partes/recurso.php";
      }
    ?>
  </main>

  <!-- Floating Help Toggle Button -->
  <div class="front-help-toggle" onclick="toggleFrontHelp()">
    <img src="royalblue.png" alt="Ayuda" title="Ayuda">
  </div>

  <!-- Front Help Panel (hidden by default) -->
  <div id="frontHelpPanel" class="front-help-panel">
    <h3>Ayuda - Reserva</h3>
    <?php
      // Output help content based on the current step
      switch($step) {
          case 1:
              echo "<p><strong>Paso 1 - Selección de Recurso:</strong></p>";
              echo "<p>Elige el recurso que deseas reservar. Cada recurso puede tener características y disponibilidad específicas.</p>";
              break;
          case 2:
              echo "<p><strong>Paso 2 - Calendario:</strong></p>";
              echo "<p>Selecciona los horarios disponibles en el calendario. Las horas marcadas como festivos o ya reservadas no se podrán seleccionar.</p>";
              break;
          case 3:
              echo "<p><strong>Paso 3 - Datos Personales:</strong></p>";
              echo "<p>Introduce tus datos personales. Si ya has iniciado sesión, algunos campos se completarán automáticamente.</p>";
              break;
          case 4:
              echo "<p><strong>Paso 4 - Confirmación:</strong></p>";
              echo "<p>Revisa todos los detalles de tu reserva y confirma si todo es correcto. Podrás volver a editar tus datos si lo necesitas.</p>";
              break;
          case 'done':
              echo "<p><strong>Reserva Confirmada:</strong></p>";
              echo "<p>Tu reserva ha sido registrada correctamente. Consulta el resumen y los detalles de tu reserva.</p>";
              break;
          default:
              echo "<p>Selecciona un paso para ver la ayuda correspondiente.</p>";
              break;
      }
    ?>
  </div>
</body>
</html>

