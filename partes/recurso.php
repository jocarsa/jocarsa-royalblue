<?php
// Al enviar el formulario al hacer clic en un recurso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resourceId = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;
    if ($resourceId > 0) {
        // Obtener el precio unitario del recurso
        $stmt = $pdo->prepare("SELECT price_per_unit FROM resources WHERE id=:id");
        $stmt->execute([':id' => $resourceId]);
        $price = $stmt->fetchColumn();
        
        // Guardar en sesión los datos iniciales de la reserva, incluyendo unit_price
        $_SESSION['reserva'] = [
            'resource_id' => $resourceId,
            'slots'       => [],
            'nombre'      => '',
            'apellidos'   => '',
            'email'       => '',
            'telefono'    => '',
            'notas'       => '',
            'budget'      => 0,
            'unit_price'  => $price
        ];
        // Redirigir al paso 2
        header("Location: index.php?step=2");
        exit;
    } else {
        $error = "Por favor, selecciona un recurso.";
    }
}

// Cargar recursos de la tabla `resources`
$stmt = $pdo->query("SELECT id, nombre, descripcion, foto FROM resources ORDER BY nombre ASC");
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas - Selección de Recurso</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        /* Estilos para el grid de recursos */
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
        .resource-item button {
            background: #0073e6;
            border: none;
            border-radius: 4px;
            color: #fff;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .resource-item button:hover {
            background: #005bb5;
        }
    </style>
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
    <p class="footer"><img src="https://jocarsa.com/img/logo.svg">powered by jocarsa | royalblue<img src="royalblue.png"></p>
</div>
</body>
</html>

