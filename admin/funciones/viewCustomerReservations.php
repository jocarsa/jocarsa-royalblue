<?php
function viewCustomerReservations($pdo, $email) {
    echo "<h2>Reservas para el Cliente: " . htmlspecialchars($email) . "</h2>";
    $stmt = $pdo->prepare("SELECT r.*, re.nombre AS recurso 
                           FROM reservations r 
                           LEFT JOIN resources re ON r.resource_id = re.id 
                           WHERE r.email = :email 
                           ORDER BY r.fecha_reserva, r.hora_reserva");
    $stmt->execute([':email' => $email]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($reservations) {
        echo '<table class="admin-table">';
        echo '<thead><tr>
                <th>ID</th>
                <th>Recurso</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Notas</th>
                <th>Creado</th>
                <th>Acciones</th>
              </tr></thead><tbody>';
        foreach ($reservations as $res) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($res['id']).'</td>';
            echo '<td>'.htmlspecialchars($res['recurso']).'</td>';
            echo '<td>'.htmlspecialchars($res['fecha_reserva']).'</td>';
            echo '<td>'.htmlspecialchars($res['hora_reserva']).'</td>';
            echo '<td>'.htmlspecialchars($res['nombre'].' '.$res['apellidos']).'</td>';
            echo '<td>'.htmlspecialchars($res['email']).'</td>';
            echo '<td>'.htmlspecialchars($res['telefono']).'</td>';
            echo '<td>'.htmlspecialchars($res['notas']).'</td>';
            echo '<td>'.htmlspecialchars($res['creado_en']).'</td>';
            echo '<td>
                    <a href="?action=edit_reserva&id='.htmlspecialchars($res['id']).'" class="button small">Editar</a>
                    <a href="?action=reservations&del='.htmlspecialchars($res['id']).'" class="button small danger" onclick="return confirm(\'¿Eliminar esta reserva?\');">Eliminar</a>
                  </td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo "<p>No hay reservas para este cliente.</p>";
    }
}
?>

