<?php
// Assumes session_start() is already called.
?>
<header style="background:#0073e6; padding:10px; color:white; text-align:right;">
  <?php if (isset($_SESSION['customer_id'])): ?>
    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['customer_nombre']); ?></span>
    <a href="logout_customer.php" style="color: white; margin-left:10px;">Cerrar sesión</a>
  <?php else: ?>
    <a href="login_customer.php?redirect=datospersonales" style="color: white; margin-right:10px;">Iniciar Sesión</a>
    <a href="signup_customer.php?redirect=datospersonales" style="color: white; margin-right:10px;">Registrarse</a>
    <a href="index.php" style="color: white;">Continuar sin iniciar sesión</a>
  <?php endif; ?>
</header>

