<?php
session_start();
require_once 'inc/dbinit.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    if ($nombre && $email && $password) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "El email ya está registrado.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO customers (nombre, email, password) VALUES (:nombre, :email, :password)");
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password' => $password  // In production use password_hash()
            ]);
            $_SESSION['customer_id'] = $pdo->lastInsertId();
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_nombre'] = $nombre;
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Jocarsa Reservations</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Registro</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
        
        <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="login_customer.php">Inicia Sesión</a></p>
    <p><a href="index.php">Continuar sin registrarse</a></p>
</div>
</body>
</html>

