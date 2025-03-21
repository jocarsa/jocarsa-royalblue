<?php

require_once __DIR__ . '/../config.php';
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}

// If your table has an owner_id column, do:
$ownerId = $_SESSION['admin_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    // If 'users' table does not have owner_id, skip that part
    if ($nombre && $email && $usuario && $password /* && $ownerId>0 if needed */) {
        // Insert the new user
        // If your table has 'owner_id', add that field to the query
        $stmt = $pdo->prepare("INSERT INTO users(nombre, email, usuario, password)
                               VALUES(:n,:e,:u,:p)");
        $stmt->execute([
            ':n' => $nombre,
            ':e' => $email,
            ':u' => $usuario,
            ':p' => $password
        ]);
        header("Location: index.php?action=users");
        exit;
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Nuevo Usuario</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Añadir Nuevo Usuario</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        
        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" id="usuario" required>
        
        <label for="password">Contraseña:</label>
        <input type="text" name="password" id="password" required>
        
        <button type="submit" name="nuevo_usuario">Crear Usuario</button>
    </form>
    <p><a href="index.php?action=users">&laquo; Volver a Usuarios</a></p>
</div>
</body>
</html>

