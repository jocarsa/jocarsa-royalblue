<?php
if (!isset($_SESSION['usuario_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $user = trim($_POST['user']);
        $pass = trim($_POST['password']);
        $stmt = $pdo->prepare("SELECT id, usuario, password FROM users WHERE usuario=:u LIMIT 1");
        $stmt->execute([':u'=>$user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['password'] === $pass) {
            // En producción se recomienda usar password_verify()
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario']    = $row['usuario'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Panel de Control - Jocarsa</title>
        <link rel="stylesheet" href="css/estilo.css">
    </head>
    <body>
        <div class="login-container">
        <img src="../royalblue.png">
            <h1>jocarsa | royalblue</h1>
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <label for="user">Usuario:</label>
                <input type="text" name="user" id="user" required>
    
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
    
                <button type="submit" name="login">Iniciar Sesión</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

