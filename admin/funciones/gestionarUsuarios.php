<?php
function gestionarUsuarios($pdo) {
    echo "<h2>Gestión de Usuarios</h2>";
    // Handle deletion
    if (isset($_GET['del'])) {
        $id = (int) $_GET['del'];
        if ($id != $_SESSION['usuario_id']) {
            $pdo->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>$id]);
            echo "<p>Usuario eliminado.</p>";
        } else {
            echo "<p>No puedes eliminar tu propia cuenta mientras estás logueado.</p>";
        }
    }
    // Retrieve users
    $rows = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <p><a href="?action=add_usuario" class="button">Añadir Nuevo Usuario</a></p>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Usuario</th>
                <th>Contraseña</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($rows as $u): ?>
            <tr>
                <td><?php echo $u['id'];?></td>
                <td><?php echo htmlspecialchars($u['nombre']);?></td>
                <td><?php echo htmlspecialchars($u['email']);?></td>
                <td><?php echo htmlspecialchars($u['usuario']);?></td>
                <td><?php echo htmlspecialchars($u['password']);?></td>
                <td>
                    <a href="?action=edit_usuario&id=<?php echo $u['id'];?>" class="button small">Editar</a>
                    <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                        <a href="?action=users&del=<?php echo $u['id'];?>" class="button small danger" onclick="return confirm('¿Eliminar este usuario?');">Eliminar</a>
                    <?php else: ?>
                        <span class="no-delete-msg">No eliminar</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <?php
}
?>

