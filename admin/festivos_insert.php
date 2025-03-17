<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_festivo'])) {
    $fecha = $_POST['fecha'];
    if ($fecha) {
        $stmt = $pdo->prepare("INSERT INTO holidays(fecha) VALUES(:f)");
        $stmt->execute([':f' => $fecha]);
        header("Location: index.php?action=festivos");
        exit;
    } else {
        $error = "La fecha es obligatoria.";
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

