<?php

require_once 'inc/dbinit.php';

// Get owner (tenant) id from session (or set default if needed)
$ownerId = $_SESSION['front_owner_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resourceId = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;
    if ($resourceId > 0) {
        // Fetch resource details (price per unit)
        $stmt = $pdo->prepare("SELECT price_per_unit FROM resources WHERE id = :id AND owner_id = :owner");
        $stmt->execute([
            ':id'   => $resourceId,
            ':owner'=> $ownerId
        ]);
        $price = $stmt->fetchColumn();

        if (!$price) {
            $price = 0; // fallback value
        }

        // Save reservation data in session
        $_SESSION['reserva'] = [
            'resource_id'     => $resourceId,
            'slots'           => [],
            'nombre'          => '',
            'apellidos'       => '',
            'email'           => '',
            'telefono'        => '',
            'notas'           => '',
            'budget'          => 0,
            'unit_price'      => $price,
            'billing_request' => 0,
            'billing_name'    => '',
            'billing_address' => '',
            'billing_vat_id'  => ''
        ];

        // Build the redirect URL including the required tenant slug ("id")
        // Assumes that front_tenant_slug was set earlier in index.php.
        $redirect = 'index.php?id=' . urlencode($_SESSION['front_tenant_slug'] ?? 'default') . '&step=2';
        if (isset($_GET['owner'])) {
            $redirect .= '&owner=' . urlencode($_GET['owner']);
        }
        header("Location: $redirect");
        exit;
    } else {
        $error = "Por favor, selecciona un recurso.";
    }
}

// Load resources for the tenant
$stmt = $pdo->prepare("SELECT id, nombre, descripcion, foto FROM resources WHERE owner_id = :ownerId ORDER BY nombre ASC");
$stmt->execute([':ownerId' => $_SESSION['front_tenant_id']]);
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas - Selección de Recurso</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="container">
    <h1>Selecciona el Recurso</h1>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <div class="resource-grid">
        <?php foreach ($recursos as $r): ?>
            <div class="resource-item">
                <?php if (!empty($r['foto'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo $r['foto']; ?>" alt="<?php echo htmlspecialchars($r['nombre']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($r['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($r['descripcion']); ?></p>
                <form method="post">
                    <button type="submit" name="resource_id" value="<?php echo $r['id']; ?>">Seleccionar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>

