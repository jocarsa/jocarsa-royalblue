<?php
function gestionarClientes($pdo) {
    $ownerId = $_SESSION['admin_id'];
    echo "<h2>Gestión de Clientes</h2>";
    // Query unique customers from reservations (grouped by email) filtered by owner_id
    $sql = "SELECT email, nombre, apellidos, telefono, COUNT(*) as total_reservations, MIN(creado_en) as first_reservation
            FROM reservations
            WHERE owner_id=:owner_id
            GROUP BY email
            ORDER BY first_reservation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':owner_id' => $ownerId]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Total Reservas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($clientes as $cliente): ?>
            <tr>
                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                <td><?php echo htmlspecialchars($cliente['nombre'] . " " . $cliente['apellidos']); ?></td>
                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                <td><?php echo htmlspecialchars($cliente['total_reservations']); ?></td>
                <td>
                    <a href="?action=view_customer_reservations&email=<?php echo urlencode($cliente['email']); ?>" class="button small">Ver Reservas</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

?>

