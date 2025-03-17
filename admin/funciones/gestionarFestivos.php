<?php
function gestionarFestivos($pdo) {
    echo "<h2>Gestión de Festivos</h2>";
    // Handle deletion if requested
    if (isset($_GET['del'])) {
        $id = (int) $_GET['del'];
        $pdo->prepare("DELETE FROM holidays WHERE id=:id")->execute([':id'=>$id]);
        echo "<p>Festivo eliminado.</p>";
    }
    // Retrieve all holidays
    $rows = $pdo->query("SELECT * FROM holidays ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
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

