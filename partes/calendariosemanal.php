<?php
// partes/calendariosemanal.php
// Asegurar que se eligió un recurso en step=1

require_once 'inc/dbinit.php'; // ensures $pdo is available

if (empty($_SESSION['reserva']['resource_id'])) {
    header("Location: index.php?step=1");
    exit;
}

// Use the tenant ID stored in session
$ownerId = $_SESSION['front_tenant_id'] ?? 0;

// Determinar la semana a mostrar
$weekOffset = isset($_GET['weekOffset']) ? (int)$_GET['weekOffset'] : 0;
$mondayThisWeek = strtotime("monday this week");
$startOfWeekTs  = strtotime("$weekOffset week", $mondayThisWeek);

// Generamos 7 días (lunes a domingo)
$days = [];
for ($i = 0; $i < 7; $i++) {
    $ts = strtotime("+$i day", $startOfWeekTs);
    $days[] = date("Y-m-d", $ts); 
}

// Obtener festivos desde la tabla "holidays", filtrando por owner
$stmtH = $pdo->prepare("SELECT fecha FROM holidays WHERE owner_id=:oid");
$stmtH->execute([':oid' => $ownerId]);
$holidays = $stmtH->fetchAll(PDO::FETCH_COLUMN);

// Al enviar el form (checkboxes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_slots'])) {
    $selectedSlots = $_POST['selected_slots'];
    $countSlots = count($selectedSlots);
    $unitPrice = isset($_SESSION['reserva']['unit_price']) ? floatval($_SESSION['reserva']['unit_price']) : 0;
    $_SESSION['reserva']['budget'] = $countSlots * $unitPrice;
    $_SESSION['reserva']['slots'] = $selectedSlots;
    
    // Build redirect URL including the tenant slug ("id") so that index.php remains in the correct context.
    $redirect = 'index.php?id=' . urlencode($_SESSION['front_tenant_slug'] ?? 'default') . '&step=3';
    header("Location: $redirect");
    exit;
}

// Retrieve resource-specific availability, but also filter by owner
$availability = [];
$rid = $_SESSION['reserva']['resource_id'];
$stmtAv = $pdo->prepare("SELECT day_of_week, hour, available
                         FROM resource_availability
                         WHERE resource_id = :rid 
                           AND owner_id = :oid");
$stmtAv->execute([':rid' => $rid, ':oid' => $ownerId]);
$rowsAv = $stmtAv->fetchAll(PDO::FETCH_ASSOC);

if (!empty($rowsAv)) {
    foreach ($rowsAv as $r) {
        $dw = $r['day_of_week']; 
        $h  = $r['hour'];
        $availability[$dw][$h] = $r['available'];
    }
} else {
    // Fallback: if no availability is set for the resource, default to all not available
    for ($d = 0; $d < 7; $d++) {
        for ($h = 0; $h < 24; $h++) {
            $availability[$d][$h] = 0;
        }
    }
}

// Cargar reservas existentes (para evitar solapamientos), filtrando by owner & resource
$reservadas = [];
$res2 = $pdo->prepare("SELECT resource_id, fecha_reserva, hora_reserva 
                       FROM reservations
                       WHERE owner_id=:oid 
                         AND resource_id=:rid");
$res2->execute([':oid'=>$ownerId, ':rid'=>$rid]);
$rowsRes = $res2->fetchAll(PDO::FETCH_ASSOC);

foreach ($rowsRes as $rx) {
    $dia   = $rx['fecha_reserva'];
    $hora  = substr($rx['hora_reserva'], 0, 2);
    $reservadas[$dia][$hora] = true;
}

// Función para obtener el día de la semana (0=Dom, 1=Lun, ... 6=Sáb)
function getDayOfWeek($dateYmd) {
    return date('w', strtotime($dateYmd));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas - Calendario Semanal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
    /* Push-button styles for calendar slots */
    .push-button {
        display: inline-block;
        cursor: pointer;
    }
    .push-button input[type="checkbox"] {
        display: none;
    }
    .push-button .button-label {
        display: inline-block;
        padding: 10px 15px;
        background-color: #0073e6;
        color: #fff;
        border-radius: 4px;
        box-shadow: 0 4px 0 #005bb5, 0 4px 10px rgba(0,0,0,0.2);
        transition: transform 0.1s ease, box-shadow 0.1s ease, background-color 0.3s ease;
    }
    .push-button input[type="checkbox"]:checked + .button-label {
        transform: translateY(4px);
        box-shadow: 0 0 0 #005bb5, 0 2px 4px rgba(0,0,0,0.2);
        background-color: #28a745;
    }
    </style>
</head>
<body>
<div class="container">
    <h1>Selecciona Horas Disponibles</h1>

    <p>Recurso seleccionado: 
    <?php 
        // Recuperar nombre del recurso
        $stmtR = $pdo->prepare("SELECT nombre FROM resources 
                                WHERE id = :id AND owner_id = :oid");
        $stmtR->execute([':id'=>$rid, ':oid'=>$ownerId]);
        $recursoNombre = $stmtR->fetchColumn();
        echo htmlspecialchars($recursoNombre ?: "Desconocido");
    ?></p>

    <div class="week-navigation">
        <a href="?step=2&weekOffset=<?php echo $weekOffset-1; ?>">&laquo; Semana Anterior</a> |
        <a href="?step=2&weekOffset=<?php echo $weekOffset+1; ?>">Semana Siguiente &raquo;</a>
    </div>

    <form method="post">
        <table class="calendar-week">
            <thead>
                <tr>
                    <th>Hora</th>
                    <?php foreach ($days as $dayDate):
                        $labelDia = date("D d/m", strtotime($dayDate));
                    ?>
                        <th><?php echo $labelDia; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php for ($hour = 0; $hour < 24; $hour++):
                $timeLabel = sprintf("%02d:00 - %02d:00", $hour, $hour + 1);
            ?>
                <tr>
                    <td style="text-align:center;"><?php echo str_pad($hour, 2, "0", STR_PAD_LEFT) . ":00"; ?></td>
                    <?php foreach ($days as $dayDate):
                        $dw = getDayOfWeek($dayDate);
                        $hStr = str_pad($hour, 2, "0", STR_PAD_LEFT);
                        $isAvailable = (!empty($availability[$dw][$hour]) && $availability[$dw][$hour]==1);
                        $isReserved  = !empty($reservadas[$dayDate][$hStr]);
                        $slotName    = $dayDate . "_" . $hStr;
                    ?>
                        <td style="text-align:center;">
                            <?php if (in_array($dayDate, $holidays)): ?>
                                <span class="holiday">-</span>
                            <?php else: ?>
                                <?php if ($isAvailable && !$isReserved): ?>
                                    <label class="push-button">
                                        <input type="checkbox" name="selected_slots[]" value="<?php echo $slotName; ?>">
                                        <span class="button-label"><?php echo $timeLabel; ?></span>
                                    </label>
                                <?php elseif ($isReserved): ?>
                                    <span class="reserved">Ocupado</span>
                                <?php else: ?>
                                    <span class="not-available">-</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
        <br>
        <button type="submit">Siguiente &raquo;</button>
    </form>
    <p><a href="index.php?step=1">&laquo; Volver a seleccionar recurso</a></p>
    <p class="footer">
        <img src="https://jocarsa.com/img/logo.svg" alt="Logo"> powered by jocarsa | royalblue
        <img src="royalblue.png" alt="Royalblue">
    </p>
</div>
</body>
</html>

