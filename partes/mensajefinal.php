<?php
// mensajefinal.php
// (If dynamic data is available, you can retrieve it here; otherwise, placeholders are used)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reserva Confirmada - Jocarsa Reservations</title>
  <link rel="stylesheet" href="css/estilo.css">
  <style>
    /* Additional styles for final summary page */
    .summary-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      text-align: center;
    }
    .summary-header {
      margin-bottom: 20px;
    }
    .summary-header h1 {
      font-size: 36px;
      color: #0073e6;
      margin-bottom: 10px;
    }
    .summary-header p {
      font-size: 18px;
      color: #555;
    }
    .summary-details {
      margin: 30px 0;
      text-align: left;
      border-top: 1px solid #eaeaea;
      padding-top: 20px;
    }
    .summary-details h2 {
      font-size: 24px;
      border-bottom: 2px solid #0073e6;
      padding-bottom: 5px;
      margin-bottom: 15px;
      color: #0073e6;
    }
    .summary-details p {
      font-size: 16px;
      margin: 8px 0;
      line-height: 1.5;
    }
    .btn-group {
      margin-top: 30px;
    }
    .btn-group a {
      display: inline-block;
      margin: 0 10px;
      padding: 12px 25px;
      background: #0073e6;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      font-size: 16px;
      transition: background 0.3s ease;
    }
    .btn-group a:hover {
      background: #005bb5;
    }
    .footer-summary {
      margin-top: 40px;
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
  <div class="summary-container">
    <div class="summary-header">
      <img src="https://jocarsa.com/img/logo.svg" alt="Logo" style="max-width: 100px;">
      <h1>¡Reserva Confirmada!</h1>
      <p>Gracias por elegir Jocarsa Reservations</p>
    </div>
    <div class="summary-details">
      <h2>Resumen de tu Reserva</h2>
      <!-- Replace the placeholders below with dynamic data if available -->
      <p><strong>Recurso:</strong> [Nombre del Recurso]</p>
      <p><strong>Fecha(s) y Hora(s):</strong> [Lista de Slots]</p>
      <p><strong>Presupuesto Total:</strong> [Presupuesto] €</p>
      <p><strong>Datos Personales:</strong></p>
      <p>Nombre: [Nombre y Apellidos]</p>
      <p>Email: [Email]</p>
      <p>Teléfono: [Teléfono]</p>
      <p>Notas: [Notas]</p>
    </div>
    <div class="btn-group">
      <a href="index.php?step=1">Realizar otra reserva</a>
      <a href="index.php?step=done">Ver detalle de reservas</a>
    </div>
    <div class="footer-summary">
      <p>powered by jocarsa | royalblue <img src="royalblue.png" alt="Royalblue"></p>
    </div>
  </div>
</body>
</html>

