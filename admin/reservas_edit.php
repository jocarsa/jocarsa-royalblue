<?php

require_once __DIR__ . '/../config.php';
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}
$ownerId = $_SESSION['admin_id'] ?? 0;

if (!isset($_GET['id'])) {
    header("Location: index.php?action=reservations");
    exit;
}
$id = (int) $_GET['id'];

// Filter by owner_id
$stmt = $pdo->prepare("
    SELECT * 
    FROM reservations 
    WHERE id=:id AND owner_id=:oid
");
$stmt->execute([':id' => $id, ':oid'=>$ownerId]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo "<p>Reserva no encontrada o no pertenece a este admin.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_reserva'])) {
    $resource_id   = trim($_POST['resource_id']);
    $fecha_reserva = trim($_POST['fecha_reserva']);
    $hora_reserva  = trim($_POST['hora_reserva']);
    $nombre        = trim($_POST['nombre']);
    $apellidos     = trim($_POST['apellidos'] ?? '');
    $email         = trim($_POST['email']);
    $telefono      = trim($_POST['telefono'] ?? '');
    $notas         = trim($_POST['notas'] ?? '');
    
    if ($resource_id && $fecha_reserva && $hora_reserva && $nombre && $email) {
        $stmt = $pdo->prepare("
            UPDATE reservations
            SET resource_id=:rid,
                fecha_reserva=:fecha,
                hora_reserva=:hora,
                nombre=:nombre,
                apellidos=:apellidos,
                email=:email,
                telefono=:telefono,
                notas=:notas
            WHERE id=:id AND owner_id=:oid
        ");
        $stmt->execute([
            ':rid' => $resource_id,
            ':fecha' => $fecha_reserva,
            ':hora' => $hora_reserva,
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':email' => $email,
            ':telefono' => $telefono,
            ':notas' => $notas,
            ':id' => $id,
            ':oid'=> $ownerId
        ]);
        header("Location: index.php?action=reservations");
        exit;
    } else {
        $error = "Todos los campos obligatorios deben ser completados.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reserva</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Editar Reserva</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="resource_id">ID del Recurso:</label>
        <input type="number" name="resource_id" id="resource_id" 
               value="<?php echo htmlspecialchars($reserva['resource_id']); ?>" required>
        
        <label for="fecha_reserva">Fecha de Reserva:</label>
        <input type="date" name="fecha_reserva" id="fecha_reserva" 
               value="<?php echo htmlspecialchars($reserva['fecha_reserva']); ?>" required>
        
        <label for="hora_reserva">Hora de Reserva:</label>
        <input type="time" name="hora_reserva" id="hora_reserva" 
               value="<?php echo htmlspecialchars($reserva['hora_reserva']); ?>" required>
        
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" 
               value="<?php echo htmlspecialchars($reserva['nombre']); ?>" required>
        
        <label for="apellidos">Apellidos:</label>
        <input type="text" name="apellidos" id="apellidos" 
               value="<?php echo htmlspecialchars($reserva['apellidos']); ?>">
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" 
               value="<?php echo htmlspecialchars($reserva['email']); ?>" required>
        
        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono" 
               value="<?php echo htmlspecialchars($reserva['telefono']); ?>">
        
        <label for="notas">Notas:</label>
        <textarea name="notas" id="notas"><?php 
            echo htmlspecialchars($reserva['notas']); 
        ?></textarea>
        
        <button type="submit" name="editar_reserva">Actualizar Reserva</button>
    </form>
    <p><a href="index.php?action=reservations">&laquo; Volver a Reservas</a></p>
</div>
</body>
</html>

