<?php
session_start();
require_once __DIR__ . '/../config.php';
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}

$ownerId = $_SESSION['admin_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_reserva'])) {
    $resource_id    = trim($_POST['resource_id']);
    $fecha_reserva  = trim($_POST['fecha_reserva']);
    $hora_reserva   = trim($_POST['hora_reserva']);
    $nombre         = trim($_POST['nombre']);
    $apellidos      = trim($_POST['apellidos'] ?? '');
    $email          = trim($_POST['email']);
    $telefono       = trim($_POST['telefono'] ?? '');
    $notas          = trim($_POST['notas'] ?? '');

    if ($resource_id && $fecha_reserva && $hora_reserva && $nombre && $email && $ownerId > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO reservations (
                owner_id,
                resource_id,
                fecha_reserva,
                hora_reserva,
                nombre,
                apellidos,
                email,
                telefono,
                notas,
                creado_en
            ) VALUES (
                :oid,
                :rid,
                :fecha,
                :hora,
                :nom,
                :ape,
                :email,
                :tel,
                :notas,
                datetime('now')
            )
        ");
        $stmt->execute([
            ':oid'   => $ownerId,
            ':rid'   => $resource_id,
            ':fecha' => $fecha_reserva,
            ':hora'  => $hora_reserva,
            ':nom'   => $nombre,
            ':ape'   => $apellidos,
            ':email' => $email,
            ':tel'   => $telefono,
            ':notas' => $notas
        ]);
        header("Location: index.php?action=reservations");
        exit;
    } else {
        $error = "Todos los campos obligatorios deben ser completados o falta owner_id.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Nueva Reserva</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Añadir Nueva Reserva</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="resource_id">ID del Recurso:</label>
        <input type="number" name="resource_id" id="resource_id" required>
        
        <label for="fecha_reserva">Fecha de Reserva:</label>
        <input type="date" name="fecha_reserva" id="fecha_reserva" required>
        
        <label for="hora_reserva">Hora de Reserva:</label>
        <input type="time" name="hora_reserva" id="hora_reserva" required>
        
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>
        
        <label for="apellidos">Apellidos:</label>
        <input type="text" name="apellidos" id="apellidos">
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        
        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono">
        
        <label for="notas">Notas:</label>
        <textarea name="notas" id="notas"></textarea>
        
        <button type="submit" name="nueva_reserva">Crear Reserva</button>
    </form>
    <p><a href="index.php?action=reservations">&laquo; Volver a Reservas</a></p>
</div>
</body>
</html>

