<?php
// admin/inc/logout.php
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

