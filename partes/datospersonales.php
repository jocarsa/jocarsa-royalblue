<?php
// partes/datospersonales.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'inc/dbinit.php';

// Ensure the tenant slug is stored in session. 
// If not set and the query parameter "id" is present, store it.
if (empty($_SESSION['front_tenant_slug']) && isset($_GET['id'])) {
    $_SESSION['front_tenant_slug'] = $_GET['id'];
}

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

// If already logged in, prefill with session data.
$nombreValue = $isLoggedIn ? $_SESSION['customer_nombre'] : "";
$emailValue  = $isLoggedIn ? $_SESSION['customer_email']  : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process guest submission
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $notas     = trim($_POST['notas'] ?? '');

    // Invoice request fields
    $billingRequest = isset($_POST['billing_request']) ? 1 : 0;
    $billingName    = trim($_POST['billing_name'] ?? '');
    $billingAddress = trim($_POST['billing_address'] ?? '');
    $billingVatId   = trim($_POST['billing_vat_id'] ?? '');

    if ($nombre && $email) {
        $_SESSION['reserva']['nombre']    = $nombre;
        $_SESSION['reserva']['apellidos'] = $apellidos;
        $_SESSION['reserva']['email']     = $email;
        $_SESSION['reserva']['telefono']  = $telefono;
        $_SESSION['reserva']['notas']     = $notas;

        // Store invoice data
        $_SESSION['reserva']['billing_request'] = $billingRequest;
        $_SESSION['reserva']['billing_name']    = $billingName;
        $_SESSION['reserva']['billing_address'] = $billingAddress;
        $_SESSION['reserva']['billing_vat_id']  = $billingVatId;

        // Build redirect URL including the tenant slug ("id")
        $tenantSlug = $_SESSION['front_tenant_slug'] ?? 'default';
			$redirect = 'index.php?id=' . urlencode($tenantSlug) . '&step=4';
			header("Location: $redirect");
			exit;
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
    <title>Datos Personales - Jocarsa Reservations</title>
    <link rel="stylesheet" href="css/estilo.css">
    <script>
    // Show/hide billing fields if user wants invoice
    function toggleBillingFields() {
        var cb = document.getElementById("billing_request_cb");
        var bf = document.getElementById("billing_fields");
        bf.style.display = cb.checked ? "block" : "none";
    }
    </script>
</head>
<body>
<div class="container">
    <h1>Tus Datos / Información</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($isLoggedIn): ?>
        <p>Ya has iniciado sesión. Tus datos se han pre-llenado.</p>
        <form method="post">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required value="<?php echo htmlspecialchars($nombreValue); ?>">
            
            <label for="apellidos">Apellidos:</label>
            <input type="text" name="apellidos" id="apellidos">
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($emailValue); ?>">
            
            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" id="telefono">
            
            <label for="notas">Notas:</label>
            <textarea name="notas" id="notas"></textarea>

            <!-- Invoice request -->
            <p>
              <label>
                <input type="checkbox" id="billing_request_cb" name="billing_request" value="1" onchange="toggleBillingFields()">
                Solicito Factura
              </label>
            </p>
            <div id="billing_fields" style="display:none; border:1px solid #ccc; padding:10px; margin-bottom:20px;">
                <label for="billing_name">Nombre/Empresa Facturación:</label>
                <input type="text" name="billing_name" id="billing_name">
                <label for="billing_address">Dirección Facturación:</label>
                <input type="text" name="billing_address" id="billing_address">
                <label for="billing_vat_id">NIF/CIF (VAT ID):</label>
                <input type="text" name="billing_vat_id" id="billing_vat_id">
            </div>
            
            <button type="submit">Siguiente &raquo;</button>
        </form>
    <?php else: ?>
        <p>Selecciona una opción para continuar:</p>
        <div style="margin-bottom:20px;">
            <label>
              <input type="radio" name="option" value="guest" checked 
                   onclick="document.getElementById('guestForm').style.display='block'; 
                            document.getElementById('registerSection').style.display='none'; 
                            document.getElementById('loginSection').style.display='none';">
               Continuar como Invitado
            </label>
            <label style="margin-left:20px;">
              <input type="radio" name="option" value="register"
                     onclick="document.getElementById('guestForm').style.display='none'; 
                              document.getElementById('registerSection').style.display='block'; 
                              document.getElementById('loginSection').style.display='none';">
               Registrarse
            </label>
            <label style="margin-left:20px;">
              <input type="radio" name="option" value="login"
                     onclick="document.getElementById('guestForm').style.display='none'; 
                              document.getElementById('registerSection').style.display='none'; 
                              document.getElementById('loginSection').style.display='block';">
               Iniciar Sesión
            </label>
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

                <!-- Invoice request -->
                <p>
                  <label>
                    <input type="checkbox" id="billing_request_cb" name="billing_request" value="1" onchange="toggleBillingFields()">
                    Solicito Factura
                  </label>
                </p>
                <div id="billing_fields" style="display:none; border:1px solid #ccc; padding:10px; margin-bottom:20px;">
                    <label for="billing_name">Nombre/Empresa Facturación:</label>
                    <input type="text" name="billing_name" id="billing_name">
                    <label for="billing_address">Dirección Facturación:</label>
                    <input type="text" name="billing_address" id="billing_address">
                    <label for="billing_vat_id">NIF/CIF (VAT ID):</label>
                    <input type="text" name="billing_vat_id" id="billing_vat_id">
                </div>
                
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
    <p class="footer"><img src="https://jocarsa.com/img/logo.svg"> powered by jocarsa | royalblue <img src="royalblue.png"></p>
</div>
</body>
</html>

