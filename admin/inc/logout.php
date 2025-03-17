<?php
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
