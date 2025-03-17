<?php
if (!isset($_GET['id'])) {
    header("Location: index.php?action=festivos");
    exit;
}
$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM holidays WHERE id=:id");
$stmt->execute([':id' => $id]);
$festivo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$festivo) {
    echo "<p>Festivo no encontrado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_festivo'])) {
    $fecha = $_POST['fecha'];
    if ($fecha) {
        $stmt = $pdo->prepare("UPDATE holidays SET fecha=:f WHERE id=:id");
        $stmt->execute([':f' => $fecha, ':id' => $id]);
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
    <title>Editar Festivo</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Editar Festivo</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" value="<?php echo htmlspecialchars($festivo['fecha']); ?>" required>
        <button type="submit" name="editar_festivo">Actualizar Festivo</button>
    </form>
    <p><a href="index.php?action=festivos">&laquo; Volver a Festivos</a></p>
</div>
</body>
</html>

