<?php
function gestionarHorarioCompleto($pdo) {
    $ownerId = $_SESSION['admin_id'];
    echo "<h2>Horario Semanal (7x24)</h2>";
    echo "<p>Marca las horas disponibles para cada día de la semana.</p>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Reset all hours to 0
        $stmt = $pdo->prepare("UPDATE hourly_availability SET available=0 WHERE owner_id=:owner_id");
        $stmt->execute([':owner_id' => $ownerId]);

        if (isset($_POST['availability']) && is_array($_POST['availability'])) {
            foreach ($_POST['availability'] as $dayOfWeek => $hoursArr) {
                foreach ($hoursArr as $hour => $val) {
                    if ($val == "1") {
                        $stmt = $pdo->prepare("UPDATE hourly_availability SET available=1 WHERE day_of_week=:d AND hour=:h AND owner_id=:owner_id");
                        $stmt->execute([':d' => $dayOfWeek, ':h' => $hour, ':owner_id' => $ownerId]);
                    }
                }
            }
        }
        echo "<p>Horario actualizado.</p>";
    }

    // Load current availability filtered by owner_id
    $disp = [];
    $stmt = $pdo->prepare("SELECT day_of_week, hour, available FROM hourly_availability WHERE owner_id=:owner_id");
    $stmt->execute([':owner_id' => $ownerId]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = $row['day_of_week'];
        $h = $row['hour'];
        $disp[$d][$h] = $row['available'];
    }

    $nombresDias = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
    ?>
    <form method="post">
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
            <?php for($hour=0; $hour<24; $hour++): ?>
                <tr>
                    <td><?php echo str_pad($hour,2,"0",STR_PAD_LEFT).":00 - ".str_pad($hour+1,2,"0",STR_PAD_LEFT).":00"; ?></td>
                    <?php for($d=0; $d<7; $d++):
                        $checked = (!empty($disp[$d][$hour])) ? "checked" : "";
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
    <?php
}

?>

