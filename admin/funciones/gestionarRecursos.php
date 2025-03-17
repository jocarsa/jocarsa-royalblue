<?php
function gestionarRecursos($pdo) {
    echo "<h2>Gestión de Recursos</h2>";
    
    // Check for deletion if GET parameter exists
    if (isset($_GET['del'])) {
        $id = (int)$_GET['del'];
        $pdo->prepare("DELETE FROM resources WHERE id=:id")->execute([':id' => $id]);
        echo "<p>Recurso eliminado.</p>";
    }
    
    // Get all resources from DB
    $rows = $pdo->query("SELECT * FROM resources ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <p><a href="?action=add_resource" class="button">Añadir Nuevo Recurso</a></p>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Color</th>
                <th>Foto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['nombre']); ?></td>
                <td><?php echo htmlspecialchars($r['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($r['price_per_unit']); ?></td>
                <td>
                    <?php if (!empty($r['color'])): ?>
                        <div style="width:30px; height:30px; background: <?php echo htmlspecialchars($r['color']); ?>; border:1px solid #ccc;"></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($r['foto'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo $r['foto']; ?>" alt="Foto" style="max-width:100px;">
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?action=edit_resource&id=<?php echo $r['id']; ?>" class="button small">Editar</a>
                    <a href="?action=resources&del=<?php echo $r['id']; ?>" class="button small danger" onclick="return confirm('¿Eliminar este recurso?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
?>

