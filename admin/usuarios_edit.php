<?php
if (!isset($_GET['id'])) {
    header("Location: index.php?action=users");
    exit;
}
$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute([':id'=>$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $usuarioNombre = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    if ($nombre && $email && $usuarioNombre && $password) {
        $stmt = $pdo->prepare("UPDATE users SET nombre=:n, email=:e, usuario=:u, password=:p WHERE id=:id");
        $stmt->execute([':n'=>$nombre,':e'=>$email,':u'=>$usuarioNombre,':p'=>$password,':id'=>$id]);
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
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Editar Usuario</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        
        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" id="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
        
        <label for="password">Contraseña:</label>
        <input type="text" name="password" id="password" value="<?php echo htmlspecialchars($usuario['password']); ?>" required>
        
        <button type="submit" name="editar_usuario">Actualizar Usuario</button>
    </form>
    <p><a href="index.php?action=users">&laquo; Volver a Usuarios</a></p>
</div>
</body>
</html>

