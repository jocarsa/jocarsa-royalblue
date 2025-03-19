<?php


// Make sure you have a DB connection here:
require_once __DIR__ . '/../config.php'; 
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}

// Retrieve the admin's ID:
$ownerId = $_SESSION['admin_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_festivo'])) {
    $fecha = $_POST['fecha'] ?? '';

    if ($fecha && $ownerId > 0) {
        // Insert with owner_id
        $stmt = $pdo->prepare("INSERT INTO holidays (owner_id, fecha) VALUES (:oid, :f)");
        $stmt->execute([
            ':oid' => $ownerId,
            ':f' => $fecha
        ]);
        header("Location: index.php?action=festivos");
        exit;
    } else {
        $error = "La fecha es obligatoria o falta owner_id.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Nuevo Festivo</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Añadir Nuevo Festivo</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" required>
        <button type="submit" name="nuevo_festivo">Añadir Festivo</button>
    </form>
    <p><a href="index.php?action=festivos">&laquo; Volver a Festivos</a></p>
</div>
</body>
</html>

