<?php

require_once __DIR__ . '/../config.php';
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}

// Retrieve admin ID
$ownerId = $_SESSION['admin_id'] ?? 0;

// Editar recurso existente
if (!isset($_GET['id'])) {
    header("Location: index.php?action=resources");
    exit;
}
$id = (int)$_GET['id'];

// Obtener datos actuales del recurso, filtering by owner_id
$stmt = $pdo->prepare("
    SELECT * 
    FROM resources 
    WHERE id=:id AND owner_id=:oid
");
$stmt->execute([':id' => $id, ':oid' => $ownerId]);
$resource = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resource) {
    echo "<p>Recurso no encontrado o no pertenece a este admin.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_recurso'])) {
    $nom = trim($_POST['nombre']);
    $desc = trim($_POST['descripcion'] ?? '');
    $price = floatval($_POST['price_per_unit'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $foto_base64 = null;

    if (!empty($_FILES['foto']['tmp_name'])) {
        $foto_data = file_get_contents($_FILES['foto']['tmp_name']);
        $foto_base64 = base64_encode($foto_data);
    }

    if ($nom) {
        if ($foto_base64 !== null) {
            // Update with new photo
            $stmt = $pdo->prepare("
                UPDATE resources
                SET nombre=:n, descripcion=:d, foto=:f, price_per_unit=:p, color=:c
                WHERE id=:id AND owner_id=:oid
            ");
            $stmt->execute([
                ':n' => $nom,
                ':d' => $desc,
                ':f' => $foto_base64,
                ':p' => $price,
                ':c' => $color,
                ':id' => $id,
                ':oid'=> $ownerId
            ]);
        } else {
            // Keep existing photo
            $stmt = $pdo->prepare("
                UPDATE resources
                SET nombre=:n, descripcion=:d, price_per_unit=:p, color=:c
                WHERE id=:id AND owner_id=:oid
            ");
            $stmt->execute([
                ':n' => $nom,
                ':d' => $desc,
                ':p' => $price,
                ':c' => $color,
                ':id' => $id,
                ':oid'=> $ownerId
            ]);
        }
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
    <title>Editar Recurso</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
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
    <h1>Editar Recurso</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" 
               value="<?php echo htmlspecialchars($resource['nombre']); ?>" 
               required>
        
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" id="descripcion"><?php 
            echo htmlspecialchars($resource['descripcion']); 
        ?></textarea>
        
        <label for="foto">Foto: (Dejar en blanco para mantener la actual)</label>
        <input type="file" name="foto" id="foto" accept="image/*">
        
        <label for="price_per_unit">Precio por unidad:</label>
        <input type="number" step="0.01" name="price_per_unit" id="price_per_unit"
               value="<?php echo htmlspecialchars($resource['price_per_unit']); ?>" required>
        
        <label for="color">Color:</label>
        <input type="color" name="color" id="color" 
               value="<?php echo htmlspecialchars($resource['color']); ?>" required>
        
        <button type="submit" name="editar_recurso">Actualizar Recurso</button>
    </form>
    <p><a href="index.php?action=resources">&laquo; Volver a Recursos</a></p>
</div>
</body>
</html>

