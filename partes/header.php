<?php
// Assumes session_start() is already called.

// ADDED: preserve ?owner=... if present
$ownerQS = isset($_GET['owner']) ? ('?owner=' . urlencode($_GET['owner'])) : '';
?>
<header style="background:#0073e6; padding:10px; color:white; text-align:right;">
  <?php if (isset($_SESSION['customer_id'])): ?>
    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['customer_nombre']); ?></span>
    <!-- MODIFIED: added $ownerQS to logout link -->
    <a href="logout_customer.php<?php echo $ownerQS; ?>" style="color: white; margin-left:10px;">Cerrar sesión</a>
  <?php else: ?>
    <!-- MODIFIED: appended $ownerQS + "&redirect=datospersonales" to preserve multi-user param and flow -->
    <a href="login_customer.php<?php echo $ownerQS ? $ownerQS . '&redirect=datospersonales' : '?redirect=datospersonales'; ?>" 
       style="color: white; margin-right:10px;">Iniciar Sesión</a>
    <a href="signup_customer.php<?php echo $ownerQS ? $ownerQS . '&redirect=datospersonales' : '?redirect=datospersonales'; ?>" 
       style="color: white; margin-right:10px;">Registrarse</a>
    <a href="index.php<?php echo $ownerQS; ?>" style="color: white;">Continuar sin iniciar sesión</a>
  <?php endif; ?>
</header>

