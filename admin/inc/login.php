<?php
// admin/inc/login.php
if (!isset($_SESSION['admin_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $user = trim($_POST['user']);
        $pass = trim($_POST['password']);
        $stmt = $pdo->prepare("SELECT id, username, password, role 
                               FROM admin_users 
                               WHERE username=:u LIMIT 1");
        $stmt->execute([':u'=>$user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // We recommend password_verify() in production
        if ($row && $row['password'] === $pass && $row['role'] === 'admin') {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Usuario/Contraseña inválidos, o no es rol=admin.";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Panel de Control - Jocarsa (Admin)</title>
        <link rel="stylesheet" href="css/estilo.css">
    </head>
    <body>
        <div class="login-container">
            <h1>Panel Admin</h1>
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

