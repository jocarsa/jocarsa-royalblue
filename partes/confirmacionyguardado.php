<?php
// Only start session if one is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'inc/dbinit.php'; // Ensure $pdo is available

// If no reservation data exists in the session, redirect back to step 1
if (empty($_SESSION['reserva'])) {
    header("Location: index.php?step=1");
    exit;
}

// Handle form submission: insert reservation into database and clear session data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservationData = $_SESSION['reserva'];
    // Prepare the insertion statement. Adjust the query if your schema is different.
    $stmt = $pdo->prepare("INSERT INTO reservations (resource_id, fecha_reserva, hora_reserva, nombre, apellidos, email, telefono, notas, creado_en)
                           VALUES (:resource_id, :fecha_reserva, :hora_reserva, :nombre, :apellidos, :email, :telefono, :notas, datetime('now'))");
    // Loop through each selected slot.
    // We assume each slot is in the format "YYYY-MM-DD_HH"
    foreach ($reservationData['slots'] as $slot) {
        list($fecha, $hora) = explode("_", $slot);
        $stmt->execute([
            ':resource_id'   => $reservationData['resource_id'],
            ':fecha_reserva' => $fecha,
            ':hora_reserva'  => $hora,
            ':nombre'        => $reservationData['nombre'],
            ':apellidos'     => isset($reservationData['apellidos']) ? $reservationData['apellidos'] : '',
            ':email'         => $reservationData['email'],
            ':telefono'      => isset($reservationData['telefono']) ? $reservationData['telefono'] : '',
            ':notas'         => isset($reservationData['notas']) ? $reservationData['notas'] : ''
        ]);
    }
    // Remove the reservation data from the session so it isn’t reinserted on refresh
    unset($_SESSION['reserva']);
    // Redirect to the final message page to complete the process
    header("Location: index.php?step=done");
    exit;
}

// Retrieve reservation data from the session for display
$r = $_SESSION['reserva'];
$slots = isset($r['slots']) && is_array($r['slots']) ? $r['slots'] : [];

// Retrieve the resource name from the database using the resource_id from session data
if (isset($r['resource_id'])) {
    $stmtRes = $pdo->prepare("SELECT nombre FROM resources WHERE id = :id");
    $stmtRes->execute([':id' => $r['resource_id']]);
    $recursoNombre = $stmtRes->fetchColumn();
    if (!$recursoNombre) {
        $recursoNombre = "Desconocido";
    }
} else {
    $recursoNombre = "Desconocido";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Confirmación de la Reserva - Jocarsa Reservations</title>
  <link rel="stylesheet" href="css/estilo.css">
  <style>
    /* Additional styles for the confirmation report */
    .confirmation-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .confirmation-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .confirmation-header h1 {
      font-size: 36px;
      color: #0073e6;
      margin-bottom: 10px;
    }
    .confirmation-header p {
      font-size: 18px;
      color: #555;
    }
    .reservation-details {
      margin-bottom: 30px;
    }
    .reservation-details h2 {
      font-size: 24px;
      color: #0073e6;
      border-bottom: 2px solid #0073e6;
      padding-bottom: 5px;
      margin-bottom: 15px;
    }
    .reservation-details .detail-item {
      font-size: 16px;
      margin-bottom: 10px;
    }
    .reservation-details .detail-item strong {
      color: #0073e6;
    }
    .reservation-slots ul {
      list-style: none;
      padding: 0;
    }
    .reservation-slots ul li {
      background: #f2f2f2;
      padding: 8px 12px;
      margin-bottom: 8px;
      border-radius: 4px;
    }
    .action-buttons {
      text-align: center;
      margin-top: 20px;
    }
    .action-buttons button {
      padding: 12px 30px;
      font-size: 16px;
      background: #0073e6;
      border: none;
      border-radius: 4px;
      color: #fff;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .action-buttons button:hover {
      background: #005bb5;
    }
    .edit-link {
      display: block;
      text-align: center;
      margin-top: 15px;
      color: #0073e6;
      text-decoration: none;
      font-size: 14px;
    }
    .footer-summary {
      text-align: center;
      margin-top: 30px;
      font-size: 14px;
      color: #777;
    }
    .footer-summary img {
      vertical-align: middle;
      width: 30px;
      margin: 0 5px;
    }
  </style>
</head>
<body>
  <div class="confirmation-container">
    <div class="confirmation-header">
      <img src="https://jocarsa.com/img/logo.svg" alt="Logo" style="max-width:100px;">
      <h1>Reserva Confirmada</h1>
      <p>Gracias por elegir Jocarsa Reservations</p>
    </div>

    <div class="reservation-details">
      <h2>Detalles de la Reserva</h2>
      <div class="detail-item"><strong>Recurso:</strong> <?php echo htmlspecialchars($recursoNombre); ?></div>
      <div class="detail-item reservation-slots">
        <strong>Slots Seleccionados:</strong>
        <ul>
          <?php if (!empty($slots)): ?>
            <?php foreach ($slots as $s): ?>
              <li><?php echo htmlspecialchars($s); ?></li>
            <?php endforeach; ?>
          <?php else: ?>
              <li>Ningún slot seleccionado</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="detail-item"><strong>Presupuesto Total:</strong> <?php echo isset($r['budget']) ? number_format($r['budget'], 2) : "0.00"; ?> €</div>
    </div>

    <div class="reservation-details">
      <h2>Datos Personales</h2>
      <div class="detail-item"><strong>Nombre:</strong> <?php echo isset($r['nombre']) ? htmlspecialchars($r['nombre'] . " " . ($r['apellidos'] ?? "")) : "No proporcionado"; ?></div>
      <div class="detail-item"><strong>Email:</strong> <?php echo isset($r['email']) ? htmlspecialchars($r['email']) : "No proporcionado"; ?></div>
      <div class="detail-item"><strong>Teléfono:</strong> <?php echo isset($r['telefono']) ? htmlspecialchars($r['telefono']) : "No proporcionado"; ?></div>
      <div class="detail-item"><strong>Notas:</strong> <?php echo isset($r['notas']) ? nl2br(htmlspecialchars($r['notas'])) : "Sin notas"; ?></div>
    </div>

    <div class="action-buttons">
      <form method="post">
        <button type="submit">Confirmar Reserva</button>
      </form>
      <a href="index.php?step=3" class="edit-link">&laquo; Volver a editar datos personales</a>
    </div>

    <div class="footer-summary">
      <p>powered by Jocarsa | royalblue <img src="royalblue.png" alt="Royalblue"></p>
    </div>
  </div>
</body>
</html>

