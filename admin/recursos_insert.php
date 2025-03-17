<?php
// Insertar nuevo recurso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_recurso'])) {
    $nom = trim($_POST['nombre']);
    $desc = trim($_POST['descripcion'] ?? '');
    $price = floatval($_POST['price_per_unit'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $foto_base64 = '';
    if (!empty($_FILES['foto']['tmp_name'])) {
        $foto_data = file_get_contents($_FILES['foto']['tmp_name']);
        $foto_base64 = base64_encode($foto_data);
    }
    if ($nom) {
        $stmt = $pdo->prepare("INSERT INTO resources(nombre, descripcion, foto, price_per_unit, color) VALUES(:n, :d, :f, :p, :c)");
        $stmt->execute([':n' => $nom, ':d' => $desc, ':f' => $foto_base64, ':p' => $price, ':c' => $color]);
        header("Location: index.php?action=resources");
        exit;
    } else {
        $error = "El nombre es obligatorio.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Nuevo Recurso</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
      /* Style adjustments for color input */
      input[type="color"] {
          border: none;
          width: 50px;
          height: 40px;
          cursor: pointer;
      }
    </style>
</head>
<body>
<div class="container">
    <h1>Añadir Nuevo Recurso</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>
        
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" id="descripcion"></textarea>
        
        <label for="foto">Foto:</label>
        <input type="file" name="foto" id="foto" accept="image/*">
        
        <label for="price_per_unit">Precio por unidad:</label>
        <input type="number" step="0.01" name="price_per_unit" id="price_per_unit" required>
        
        <label for="color">Color:</label>
        <input type="color" name="color" id="color" value="#0073e6" required>
        
        <button type="submit" name="nuevo_recurso">Crear Recurso</button>
    </form>
    <p><a href="index.php?action=resources">&laquo; Volver a Recursos</a></p>
</div>
</body>
</html>

