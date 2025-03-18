<?php

require_once __DIR__ . '/../config.php';
date_default_timezone_set(TIMEZONE);

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    die("Error DB: " . $ex->getMessage());
}

$ownerId = $_SESSION['admin_id'] ?? 0;

// Process form submission: update availability for the selected resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $resource_id = (int)$_POST['resource_id'];

    // Delete all existing availability rows for this resource & owner
    $pdo->prepare("
        DELETE FROM resource_availability 
        WHERE resource_id = :rid AND owner_id = :oid
    ")->execute([':rid' => $resource_id, ':oid' => $ownerId]);

    // Loop days/hours
    for ($d = 0; $d < 7; $d++) {
        for ($h = 0; $h < 24; $h++) {
            $available = 0;
            if (isset($_POST['availability'][$d][$h]) && $_POST['availability'][$d][$h] == "1") {
                $available = 1;
            }
            $stmt = $pdo->prepare("
                INSERT INTO resource_availability (
                    owner_id, resource_id, day_of_week, hour, available
                ) VALUES (:oid, :rid, :d, :h, :a)
            ");
            $stmt->execute([
                ':oid' => $ownerId,
                ':rid' => $resource_id,
                ':d' => $d,
                ':h' => $h,
                ':a' => $available
            ]);
        }
    }
    $message = "Horario actualizado para el recurso seleccionado.";
}

// Check if a resource is selected
$selectedResource = isset($_GET['resource_id']) ? (int)$_GET['resource_id'] : 0;

// If no resource is selected, display the resources grid
if ($selectedResource <= 0) {
    $stmt = $pdo->prepare("
        SELECT id, nombre, descripcion, foto
        FROM resources
        WHERE owner_id=:oid
        ORDER BY nombre ASC
    ");
    $stmt->execute([':oid' => $ownerId]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Selecciona Recurso - Disponibilidad por Recurso</title>
        <link rel="stylesheet" href="css/estilo.css">
        <style>
            .resource-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
            }
            .resource-item {
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 15px;
                text-align: center;
                background-color: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                transition: transform 0.2s;
            }
            .resource-item:hover {
                transform: scale(1.02);
            }
            .resource-item img {
                max-width: 100%;
                height: auto;
                border-radius: 4px;
                margin-bottom: 10px;
            }
            .resource-item h3 {
                margin: 10px 0 5px;
                font-size: 18px;
                color: #0073e6;
            }
            .resource-item p {
                font-size: 14px;
                color: #555;
                margin-bottom: 10px;
            }
            .resource-item a {
                background: #0073e6;
                border: none;
                border-radius: 4px;
                color: #fff;
                padding: 10px 20px;
                font-size: 14px;
                text-decoration: none;
                display: inline-block;
                transition: background 0.3s ease;
            }
            .resource-item a:hover {
                background: #005bb5;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>Disponibilidad por Recurso</h1>
        <p>Selecciona un recurso para ver y editar su disponibilidad:</p>
        <div class="resource-grid">
            <?php foreach ($resources as $res): ?>
                <div class="resource-item">
                    <?php if (!empty($res['foto'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo $res['foto']; ?>" 
                             alt="<?php echo htmlspecialchars($res['nombre']); ?>">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($res['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($res['descripcion']); ?></p>
                    <a href="?action=resource_availability&resource_id=<?php echo $res['id']; ?>">
                        Ver Disponibilidad
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <p><a href="index.php">Volver al Panel de Control</a></p>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// If a resource is selected, load its availability
$availability = [];
$stmtAv = $pdo->prepare("
    SELECT day_of_week, hour, available
    FROM resource_availability
    WHERE resource_id=:rid AND owner_id=:oid
");
$stmtAv->execute([':rid'=>$selectedResource, ':oid'=>$ownerId]);
while ($row = $stmtAv->fetch(PDO::FETCH_ASSOC)) {
    $availability[$row['day_of_week']][$row['hour']] = $row['available'];
}

// If there's no availability data, init a blank grid
if (empty($availability)) {
    for ($d = 0; $d < 7; $d++) {
        for ($h = 0; $h < 24; $h++) {
            $availability[$d][$h] = 0;
        }
    }
}

$nombresDias = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

// Retrieve resource info
$stmtRes = $pdo->prepare("
    SELECT nombre, descripcion, foto
    FROM resources
    WHERE id=:id AND owner_id=:oid
");
$stmtRes->execute([':id'=>$selectedResource, ':oid'=>$ownerId]);
$resourceInfo = $stmtRes->fetch(PDO::FETCH_ASSOC);
if (!$resourceInfo) {
    echo "<p>Recurso no encontrado o no pertenece a este admin.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Disponibilidad por Recurso</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }
        .switch input {opacity: 0; width: 0; height: 0;}
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc; transition: .4s; border-radius: 28px;
        }
        .slider:before {
            position: absolute; content: "";
            height: 20px; width: 20px; left: 4px; bottom: 4px;
            background-color: white; transition: .4s; border-radius: 50%;
        }
        .switch input:checked + .slider { background-color: #0073e6; }
        .switch input:checked + .slider:before { transform: translateX(22px); }
    </style>
</head>
<body>
<div class="container">
    <h1>Disponibilidad para Recurso</h1>
    <h2><?php echo htmlspecialchars($resourceInfo['nombre']); ?></h2>
    <?php if (!empty($resourceInfo['foto'])): ?>
        <img src="data:image/jpeg;base64,<?php echo $resourceInfo['foto']; ?>" 
             alt="<?php echo htmlspecialchars($resourceInfo['nombre']); ?>" 
             style="max-width:200px;">
    <?php endif; ?>
    <p><?php echo htmlspecialchars($resourceInfo['descripcion']); ?></p>
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="resource_id" value="<?php echo $selectedResource; ?>">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <?php foreach($nombresDias as $diaLabel): ?>
                        <th><?php echo $diaLabel; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for($hour = 0; $hour < 24; $hour++): ?>
                    <tr>
                        <td><?php echo str_pad($hour, 2, "0", STR_PAD_LEFT) . ":00"; ?></td>
                        <?php for($d = 0; $d < 7; $d++):
                            $checked = (!empty($availability[$d][$hour]) && 
                                        $availability[$d][$hour] == 1) ? "checked" : "";
                        ?>
                        <td style="text-align:center;">
                            <label class="switch">
                                <input type="checkbox" 
                                       name="availability[<?php echo $d; ?>][<?php echo $hour; ?>]"
                                       value="1" <?php echo $checked; ?>>
                                <span class="slider"></span>
                            </label>
                        </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <br>
        <button type="submit">Guardar Cambios</button>
    </form>
    <p><a href="?action=resource_availability">Volver a la selección de recursos</a></p>
    <p><a href="index.php">Volver al Panel de Control</a></p>
</div>
</body>
</html>

