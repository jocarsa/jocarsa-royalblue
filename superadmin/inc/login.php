<?php
// superadmin/inc/login.php
if (!isset($_SESSION['superadmin_id'])) {
    // If form is submitted:
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $user = trim($_POST['username']);
        $pass = trim($_POST['password']);

        // Connect using the same $pdo from index
        $stmt = $pdo->prepare("SELECT id, username, password, role 
                               FROM admin_users 
                               WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // We strongly recommend using password_hash() + password_verify() in real production
        if ($row && $row['password'] === $pass && $row['role'] === 'superadmin') {
            $_SESSION['superadmin_id'] = $row['id'];
            $_SESSION['superadmin_username'] = $row['username'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Usuario o contraseña inválidos, o no es superadmin.";
        }
    }

    // Show login form if not logged in
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Superadmin Login</title>
        <link rel="stylesheet" href="../../admin/css/estilo.css">
    </head>
    <body>
        <div class="login-container">
            <h1>Superadmin Panel</h1>
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <label for="username">Usuario:</label>
                <input type="text" name="username" id="username" required>

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

