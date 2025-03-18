<?php

if (empty($_SESSION['reserva']['resource_id'])) {
    header("Location: index.php?step=1");
    exit;
}
if (empty($_SESSION['reserva']['slots'])) {
    header("Location: index.php?step=2");
    exit;
}
$error = "";
// Check if customer is already logged in
$isLoggedIn = isset($_SESSION['customer_id']);

// If already logged in, we prefill with session data.
$nombreValue = $isLoggedIn ? $_SESSION['customer_nombre'] : "";
$emailValue  = $isLoggedIn ? $_SESSION['customer_email']  : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only the guest option submits data here.
    $option = $_POST['option_type'] ?? "";
    if ($option === "guest") {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');
        $notas     = trim($_POST['notas'] ?? '');
        if ($nombre && $email) {
            $_SESSION['reserva']['nombre']    = $nombre;
            $_SESSION['reserva']['apellidos'] = $apellidos;
            $_SESSION['reserva']['email']     = $email;
            $_SESSION['reserva']['telefono']  = $telefono;
            $_SESSION['reserva']['notas']     = $notas;
            header("Location: index.php?step=4");
            exit;
        } else {
            $error = "El nombre y el email son obligatorios para continuar como invitado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos Personales - Jocarsa Reservations</title>
    <link rel="stylesheet" href="css/estilo.css">
    <script>
    function showOption(option) {
        document.getElementById("guestForm").style.display = (option === "guest") ? "block" : "none";
        document.getElementById("registerSection").style.display = (option === "register") ? "block" : "none";
        document.getElementById("loginSection").style.display = (option === "login") ? "block" : "none";
    }
    </script>
</head>
<body>
<?php /*include "header.php";*/ ?>
<div class="container">
    <h1>Tus Datos / Información</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($isLoggedIn): ?>
        <p>Ya has iniciado sesión. Tus datos se han pre-llenado.</p>
        <form method="post">
            <input type="hidden" name="option_type" value="guest">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($nombreValue); ?>" required>
            
            <label for="apellidos">Apellidos:</label>
            <input type="text" name="apellidos" id="apellidos">
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($emailValue); ?>" required>
            
            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" id="telefono">
            
            <label for="notas">Notas:</label>
            <textarea name="notas" id="notas"></textarea>
            
            <button type="submit">Siguiente &raquo;</button>
        </form>
    <?php else: ?>
        <p>Selecciona una opción para continuar:</p>
        <div style="margin-bottom:20px;">
            <label><input type="radio" name="option" value="guest" onclick="showOption('guest')" checked> Continuar como Invitado</label>
            <label style="margin-left:20px;"><input type="radio" name="option" value="register" onclick="showOption('register')"> Registrarse</label>
            <label style="margin-left:20px;"><input type="radio" name="option" value="login" onclick="showOption('login')"> Iniciar Sesión</label>
        </div>
        
        <div id="guestForm" style="display:block;">
            <h2>Continuar como Invitado</h2>
            <form method="post">
                <input type="hidden" name="option_type" value="guest">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" required>
                
                <label for="apellidos">Apellidos:</label>
                <input type="text" name="apellidos" id="apellidos">
                
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                
                <label for="telefono">Teléfono:</label>
                <input type="text" name="telefono" id="telefono">
                
                <label for="notas">Notas:</label>
                <textarea name="notas" id="notas"></textarea>
                
                <button type="submit">Siguiente &raquo;</button>
            </form>
        </div>
        
        <div id="registerSection" style="display:none;">
            <h2>Registrarse</h2>
            <p>Si deseas registrarte, haz clic en el botón de abajo. Después de registrarte, serás redirigido a este proceso.</p>
            <p><a href="signup_customer.php?redirect=datospersonales" class="button">Registrarse</a></p>
        </div>
        
        <div id="loginSection" style="display:none;">
            <h2>Iniciar Sesión</h2>
            <p>Si ya tienes una cuenta, haz clic en el botón de abajo para iniciar sesión. Luego volverás a este proceso.</p>
            <p><a href="login_customer.php?redirect=datospersonales" class="button">Iniciar Sesión</a></p>
        </div>
    <?php endif; ?>
    
    <p><a href="index.php?step=2">&laquo; Volver al calendario</a></p>
    <p class="footer"><img src="https://jocarsa.com/img/logo.svg">powered by jocarsa | royalblue<img src="royalblue.png"></p>
</div>
</body>
</html>

