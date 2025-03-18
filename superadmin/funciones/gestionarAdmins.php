<?php
// superadmin/funciones/gestionarAdmins.php

function gestionarAdmins($pdo) {
    echo "<h2>Administrar Usuarios (role=admin)</h2>";

    // Delete
    if (isset($_GET['del'])) {
        $id = (int) $_GET['del'];
        // Do not let superadmin delete themselves or another superadmin
        $stmt = $pdo->prepare("SELECT role FROM admin_users WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $role = $stmt->fetchColumn();
        if ($role === 'superadmin') {
            echo "<p>No puedes eliminar un superadmin desde aquí.</p>";
        } else {
            $pdo->prepare("DELETE FROM admin_users WHERE id=:id")->execute([':id' => $id]);
            echo "<p>Usuario admin eliminado.</p>";
        }
    }

    // List all admin users
    $stmt = $pdo->query("SELECT * FROM admin_users WHERE role='admin' ORDER BY id ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<p><a href="?action=add" class="button">Añadir Nuevo Admin</a></p>';
    echo '<table class="admin-table">';
    echo '<thead><tr>
            <th>ID</th>
            <th>Username</th>
            <th>Business Name</th>
            <th>VAT ID</th>
            <th>Email</th>
            <th>Acciones</th>
          </tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>'.$r['id'].'</td>';
        echo '<td>'.htmlspecialchars($r['username']).'</td>';
        echo '<td>'.htmlspecialchars($r['business_name']).'</td>';
        echo '<td>'.htmlspecialchars($r['vat_id']).'</td>';
        echo '<td>'.htmlspecialchars($r['email']).'</td>';
        echo '<td>
                <a href="?action=edit&id='.$r['id'].'" class="button small">Editar</a>
                <a href="?action=list&del='.$r['id'].'" class="button small danger" onclick="return confirm(\'¿Eliminar?\');">Eliminar</a>
             </td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function editarAdmin($pdo) {
    if (!isset($_GET['id'])) {
        header("Location: index.php?action=list");
        exit;
    }
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id=:id AND role='admin'");
    $stmt->execute([':id' => $id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin) {
        echo "<p>Admin no encontrado o no es 'role=admin'.</p>";
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $businessName = trim($_POST['business_name']);
        $address = trim($_POST['address']);
        $vatId = trim($_POST['vat_id']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        if ($username && $password) {
            $stmtU = $pdo->prepare("UPDATE admin_users
                SET username=:u, password=:p, business_name=:bn, address=:ad, vat_id=:vat, email=:em, phone=:ph
                WHERE id=:id");
            $stmtU->execute([
                ':u' => $username,
                ':p' => $password, // Use password_hash() in production
                ':bn' => $businessName,
                ':ad' => $address,
                ':vat' => $vatId,
                ':em' => $email,
                ':ph' => $phone,
                ':id' => $id
            ]);
            echo "<p>Admin actualizado.</p>";
            echo '<p><a href="?action=list">&laquo; Volver</a></p>';
            return;
        } else {
            echo "<p>Error: se requiere username y password.</p>";
        }
    }

    // Show edit form
    ?>
    <h2>Editar Admin</h2>
    <form method="post">
        <label>Usuario:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>

        <label>Contraseña:</label>
        <input type="text" name="password" value="<?php echo htmlspecialchars($admin['password']); ?>" required>

        <label>Business Name:</label>
        <input type="text" name="business_name" value="<?php echo htmlspecialchars($admin['business_name']); ?>">

        <label>Dirección:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($admin['address']); ?>">

        <label>VAT ID:</label>
        <input type="text" name="vat_id" value="<?php echo htmlspecialchars($admin['vat_id']); ?>">

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>">

        <label>Teléfono:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">

        <button type="submit" name="update_admin">Actualizar</button>
    </form>
    <p><a href="?action=list">&laquo; Volver</a></p>
    <?php
}

function nuevoAdmin($pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $businessName = trim($_POST['business_name']);
        $address = trim($_POST['address']);
        $vatId = trim($_POST['vat_id']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        if ($username && $password) {
            // role=admin always here
            $stmt = $pdo->prepare("INSERT INTO admin_users (username,password,role,business_name,address,vat_id,email,phone)
                VALUES (:u, :p, 'admin', :bn, :ad, :vat, :em, :ph)");
            $stmt->execute([
                ':u' => $username,
                ':p' => $password,  // Use password_hash() in production
                ':bn' => $businessName,
                ':ad' => $address,
                ':vat' => $vatId,
                ':em' => $email,
                ':ph' => $phone
            ]);
            echo "<p>Admin creado.</p>";
            echo '<p><a href="?action=list">&laquo; Volver</a></p>';
            return;
        } else {
            echo "<p>Error: Username y Password son obligatorios.</p>";
        }
    }
    ?>
    <h2>Crear Nuevo Admin</h2>
    <form method="post">
        <label>Usuario:</label>
        <input type="text" name="username" required>

        <label>Contraseña:</label>
        <input type="text" name="password" required>

        <label>Business Name:</label>
        <input type="text" name="business_name">

        <label>Dirección:</label>
        <input type="text" name="address">

        <label>VAT ID:</label>
        <input type="text" name="vat_id">

        <label>Email:</label>
        <input type="email" name="email">

        <label>Teléfono:</label>
        <input type="text" name="phone">

        <button type="submit" name="create_admin">Crear</button>
    </form>
    <p><a href="?action=list">&laquo; Volver</a></p>
    <?php
}

