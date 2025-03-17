<?php
if (empty($_SESSION['reserva']['slots']) || empty($_SESSION['reserva']['resource_id'])) {
    header("Location: index.php?step=1");
    exit;
}
if (empty($_SESSION['reserva']['nombre']) || empty($_SESSION['reserva']['email'])) {
    header("Location: index.php?step=3");
    exit;
}

// Al enviar el formulario, insertar reservas y enviar el email de confirmación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slots   = $_SESSION['reserva']['slots'];
    $rid     = $_SESSION['reserva']['resource_id'];
    $nombre  = $_SESSION['reserva']['nombre'];
    $ap      = $_SESSION['reserva']['apellidos'] ?? '';
    $em      = $_SESSION['reserva']['email'];
    $tel     = $_SESSION['reserva']['telefono'] ?? '';
    $notas   = $_SESSION['reserva']['notas'] ?? '';
    $budget  = $_SESSION['reserva']['budget'] ?? 0;

    try {
        foreach ($slots as $slot) {
            list($fecha, $hora) = explode("_", $slot);
            $horaReserva = $hora . ":00";
            $stmt = $pdo->prepare("INSERT INTO reservations
               (resource_id, fecha_reserva, hora_reserva, nombre, apellidos, email, telefono, notas, creado_en)
               VALUES
               (:rid, :f, :h, :n, :a, :e, :t, :no, datetime('now'))");
            $stmt->execute([
                ':rid'=>$rid,
                ':f'=>$fecha,
                ':h'=>$horaReserva,
                ':n'=>$nombre,
                ':a'=>$ap,
                ':e'=>$em,
                ':t'=>$tel,
                ':no'=>$notas
            ]);
        }

        // Obtener nombre del recurso para el email
        $stmtRes = $pdo->prepare("SELECT nombre FROM resources WHERE id=:id");
        $stmtRes->execute([':id' => $rid]);
        $recursoNombre = $stmtRes->fetchColumn();

        // Componer y enviar el email de confirmación
        $to = $em;
        $subject = "Confirmación de Reserva";
        $message = "Hola " . $nombre . ",\n\n" .
                   "Tu reserva ha sido confirmada.\n\n" .
                   "Detalles de la reserva:\n" .
                   "Recurso: " . $recursoNombre . "\n" .
                   "Slots seleccionados:\n" . implode("\n", $slots) . "\n" .
                   "Presupuesto: " . number_format($budget, 2) . " €\n\n" .
                   "Gracias por reservar con nosotros.\n";
        $headers = "From: reservas@tudominio.com\r\n" .
                   "Reply-To: reservas@tudominio.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        mail($to, $subject, $message, $headers);

        // Limpiar la sesión y redirigir a la pantalla final
        unset($_SESSION['reserva']);
        header("Location: index.php?step=done");
        exit;
    } catch (Exception $ex) {
        $error = "Error al guardar reservas: " . $ex->getMessage();
    }
}

// Mostrar datos de confirmación
$r = $_SESSION['reserva'];
$slots = $r['slots'];
$stmt = $pdo->prepare("SELECT nombre FROM resources WHERE id=:id");
$stmt->execute([':id'=>$r['resource_id']]);
$recursoNombre = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Reserva</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Confirmación de la Reserva</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <h2>Recurso:</h2>
    <p><?php echo htmlspecialchars($recursoNombre); ?></p>

    <h2>Slots seleccionados:</h2>
    <ul>
    <?php foreach ($slots as $s): ?>
        <li><?php echo htmlspecialchars($s); ?></li>
    <?php endforeach; ?>
    </ul>

    <h2>Datos Personales</h2>
    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($r['nombre'] . " " . $r['apellidos']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($r['email']); ?></p>
    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($r['telefono']); ?></p>
    <p><strong>Notas:</strong> <?php echo nl2br(htmlspecialchars($r['notas'])); ?></p>

    <h2>Presupuesto:</h2>
    <p><?php echo number_format($r['budget'], 2); ?> €</p>

    <form method="post">
        <button type="submit">Confirmar Reserva</button>
    </form>
    <p><a href="index.php?step=3">&laquo; Volver a datos personales</a></p>
    <p class="footer"><img src="https://jocarsa.com/img/logo.svg" alt="Logo">powered by jocarsa | royalblue<img src="royalblue.png" alt="Royalblue"></p>
</div>
</body>
</html>

