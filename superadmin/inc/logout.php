<?php
// superadmin/inc/logout.php
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // log out superadmin
    unset($_SESSION['superadmin_id'], $_SESSION['superadmin_username']);
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

