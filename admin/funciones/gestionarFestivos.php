<?php
function gestionarFestivos($pdo) {
    $ownerId = $_SESSION['admin_id'];
    echo "<h2>Gestión de Festivos</h2>";
    // Handle deletion if requested
    if (isset($_GET['del'])) {
        $id = (int)$_GET['del'];
        $stmt = $pdo->prepare("DELETE FROM holidays WHERE id=:id AND owner_id=:owner_id");
        $stmt->execute([':id' => $id, ':owner_id' => $ownerId]);
        echo "<p>Festivo eliminado.</p>";
    }
    // Retrieve all holidays filtered by owner_id
    $stmt = $pdo->prepare("SELECT * FROM holidays WHERE owner_id=:owner_id ORDER BY fecha ASC");
    $stmt->execute([':owner_id' => $ownerId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <p><a href="?action=add_festivo" class="button">Añadir Nuevo Festivo</a></p>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($rows as $r): ?>
            <tr>
                <td><?php echo $r['id'];?></td>
                <td><?php echo htmlspecialchars($r['fecha']);?></td>
                <td>
                    <a href="?action=edit_festivo&id=<?php echo $r['id'];?>" class="button small">Editar</a>
                    <a href="?action=festivos&del=<?php echo $r['id'];?>" class="button small danger" onclick="return confirm('¿Eliminar festivo?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <?php
}

?>

