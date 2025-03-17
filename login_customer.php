<?php
session_start();
require_once 'inc/dbinit.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    // In production use password_hash() and password_verify()
    if ($customer && $customer['password'] === $password) {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_email'] = $customer['email'];
        $_SESSION['customer_nombre'] = $customer['nombre'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Email o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Jocarsa Reservations</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Iniciar Sesión</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
        
        <button type="submit">Iniciar Sesión</button>
    </form>
    <p>¿No tienes cuenta? <a href="signup_customer.php">Regístrate aquí</a></p>
    <p><a href="index.php">Continuar sin iniciar sesión</a></p>
</div>
</body>
</html>

