<?php
if (empty($_SESSION['reserva']['resource_id'])) {
    header("Location: index.php?step=1");
    exit;
}
if (empty($_SESSION['reserva']['slots'])) {
    header("Location: index.php?step=2");
    exit;
}

// If customer is logged in, prefill name and email
$nombreValue = isset($_SESSION['customer_nombre']) ? $_SESSION['customer_nombre'] : '';
$emailValue  = isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error = "El nombre y el email son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos Personales</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Tus datos</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
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
    <p><a href="index.php?step=2">&laquo; Volver al calendario</a></p>
    <p class="footer"><img src="https://jocarsa.com/img/logo.svg">powered by jocarsa | royalblue<img src="royalblue.png"></p>
</div>
</body>
</html>

