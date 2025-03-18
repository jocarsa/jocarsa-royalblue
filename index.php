<?php
session_start();
require_once 'inc/dbinit.php';

// Check if the tenant slug is passed in the URL (e.g. index.php?id=jocarsa3)
if (isset($_GET['id'])) {
    // Store the tenant slug in the session
    $_SESSION['front_tenant_slug'] = $_GET['id'];
    
    // Query the tenant record based on the slug.
    // Adjust the query if your tenant identifier is stored in a different column.
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE LOWER(business_name) = LOWER(:tenant) LIMIT 1");
    $stmt->execute([':tenant' => $_GET['id']]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tenant) {
        $_SESSION['front_tenant_id'] = $tenant['id'];
    } else {
        // Handle the case when the tenant is not found.
        $_SESSION['front_tenant_id'] = 0;
    }
} else {
    // Optionally, if "id" is not set, you can define a default tenant
    // or redirect the user to an error page.
    $_SESSION['front_tenant_slug'] = 'default';
    $_SESSION['front_tenant_id']   = 0;
}

// Determine the current step in the reservation process.
// For example, step=1 for resource selection, step=2 for calendar, step=3 for personal data,
// step=4 for confirmation/insert, and step=done for the final message.
$step = isset($_GET['step']) ? $_GET['step'] : 1;

if ($step == 1) {
    include "partes/recurso.php";
    exit;
} elseif ($step == 2) {
    include "partes/calendariosemanal.php";
    exit;
} elseif ($step == 3) {
    include "partes/datospersonales.php";
    exit;
} elseif ($step == 4) {
    include "partes/confirmacionyguardado.php";
    exit;
} elseif (isset($_GET['step']) && $_GET['step'] === 'done') {
    include "partes/mensajefinal.php";
    exit;
}

// Fallback: if no matching step is found, default to step 1.
include "partes/recurso.php";
exit;
?>

