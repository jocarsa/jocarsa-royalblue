<?php
function visualizarReservas($pdo) {
    echo "<h2>Reservas</h2>";

    // Handle deletion if requested
    if (isset($_GET['del'])) {
        $id = (int) $_GET['del'];
        $pdo->prepare("DELETE FROM reservations WHERE id=:id")->execute([':id'=>$id]);
        echo "<p>Reserva eliminada.</p>";
    }
    
    // View switcher controls
    $view = isset($_GET['view']) ? $_GET['view'] : 'table';
    echo '<div class="view-switcher" style="margin-bottom:20px;">';
    echo 'Ver: ';
    echo '<a href="?action=reservations&view=table"'.($view=='table' ? ' class="active-view"' : '').'>Tabla</a> | ';
    echo '<a href="?action=reservations&view=monthly"'.($view=='monthly' ? ' class="active-view"' : '').'>Mensual</a> | ';
    echo '<a href="?action=reservations&view=weekly"'.($view=='weekly' ? ' class="active-view"' : '').'>Semanal</a> | ';
    echo '<a href="?action=reservations&view=daily"'.($view=='daily' ? ' class="active-view"' : '').'>Diario</a>';
    echo '</div>';
    
    if ($view == 'monthly') {
        // === MONTHLY VIEW ===
        $monthOffset = isset($_GET['monthOffset']) ? (int)$_GET['monthOffset'] : 0;
        $firstDayThisMonth = strtotime(date("Y-m-01"));
        $targetMonthTs = strtotime("$monthOffset month", $firstDayThisMonth);
        $year = date("Y", $targetMonthTs);
        $month = date("m", $targetMonthTs);
        $daysInMonth = date("t", $targetMonthTs);
        $firstDayWeekday = date("N", strtotime("$year-$month-01")); // Monday=1 ... Sunday=7

        // Retrieve global reservations for the month (with resource info)
        $startDate = "$year-$month-01";
        $endDate = "$year-$month-$daysInMonth";
        $stmt = $pdo->prepare("SELECT r.*, re.nombre AS recurso 
                               FROM reservations r 
                               LEFT JOIN resources re ON r.resource_id = re.id 
                               WHERE fecha_reserva BETWEEN :start AND :end 
                               ORDER BY fecha_reserva, hora_reserva");
        $stmt->execute([':start'=>$startDate, ':end'=>$endDate]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resByDate = [];
        foreach ($reservations as $res) {
            $date = $res['fecha_reserva'];
            $resByDate[$date][] = $res;
        }
        
        // Month navigation
        echo '<div class="calendar-navigation" style="margin-bottom:10px;">';
        echo '<a href="?action=reservations&view=monthly&monthOffset='.($monthOffset - 1).'">&laquo; Mes Anterior</a> ';
        echo '<span style="margin:0 10px;">'.date("F Y", $targetMonthTs).'</span>';
        echo '<a href="?action=reservations&view=monthly&monthOffset='.($monthOffset + 1).'">Mes Siguiente &raquo;</a>';
        echo '</div>';
        
        // --- Global Monthly Calendar ---
        echo '<h3>Calendario Global</h3>';
        echo '<table class="admin-table">';
        echo '<thead><tr>';
        $daysOfWeek = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];
        foreach ($daysOfWeek as $dName) {
            echo '<th>'.$dName.'</th>';
        }
        echo '</tr></thead><tbody>';
        $numCells = 0;
        echo '<tr>';
        // Empty cells before the first day
        for ($i = 1; $i < $firstDayWeekday; $i++) {
            echo '<td style="background:#f0f0f0;"></td>';
            $numCells++;
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            if ($numCells % 7 == 0 && $numCells != 0) { echo '</tr><tr>'; }
            $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $d);
            echo '<td valign="top" style="height:100px; vertical-align: top; border:1px solid #ddd; padding:5px;">';
            echo '<strong>'.$d.'</strong><br>';
            if (isset($resByDate[$dateStr])) {
                foreach ($resByDate[$dateStr] as $res) {
                    echo '<div style="font-size:10px;">(' . htmlspecialchars(substr($res['hora_reserva'],0,5)) . ') ';
                    echo 'Recurso: ' . htmlspecialchars($res['recurso']) . '<br>';
                    echo htmlspecialchars($res['nombre']) . '</div>';
                }
            }
            echo '</td>';
            $numCells++;
        }
        while ($numCells % 7 != 0) {
            echo '<td style="background:#f0f0f0;"></td>';
            $numCells++;
        }
        echo '</tr>';
        echo '</tbody></table>';
        
        // --- Resource-Specific Monthly Calendars ---
        $stmtResources = $pdo->query("SELECT * FROM resources ORDER BY nombre ASC");
        $resources = $stmtResources->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resources as $resource) {
            echo '<h3>Recurso: ' . htmlspecialchars($resource['nombre']) . '</h3>';
            $resByDateRes = [];
            foreach ($reservations as $res) {
                if ($res['resource_id'] == $resource['id']) {
                    $date = $res['fecha_reserva'];
                    $resByDateRes[$date][] = $res;
                }
            }
            echo '<table class="admin-table">';
            echo '<thead><tr>';
            foreach ($daysOfWeek as $dName) {
                echo '<th>'.$dName.'</th>';
            }
            echo '</tr></thead><tbody>';
            $numCells = 0;
            echo '<tr>';
            for ($i = 1; $i < $firstDayWeekday; $i++) {
                echo '<td style="background:#f0f0f0;"></td>';
                $numCells++;
            }
            for ($d = 1; $d <= $daysInMonth; $d++) {
                if ($numCells % 7 == 0 && $numCells != 0) { echo '</tr><tr>'; }
                $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $d);
                echo '<td valign="top" style="height:100px; vertical-align: top; border:1px solid #ddd; padding:5px;">';
                echo '<strong>'.$d.'</strong><br>';
                if (isset($resByDateRes[$dateStr])) {
                    foreach ($resByDateRes[$dateStr] as $res) {
                        echo '<div style="font-size:10px;">(' . htmlspecialchars(substr($res['hora_reserva'],0,5)) . ') ';
                        echo htmlspecialchars($res['nombre']) . '</div>';
                    }
                }
                echo '</td>';
                $numCells++;
            }
            while ($numCells % 7 != 0) {
                echo '<td style="background:#f0f0f0;"></td>';
                $numCells++;
            }
            echo '</tr>';
            echo '</tbody></table>';
        }
        
    } elseif ($view == 'weekly') {
        // === WEEKLY VIEW ===
        $weekOffset = isset($_GET['weekOffset']) ? (int)$_GET['weekOffset'] : 0;
        $mondayThisWeek = strtotime("monday this week");
        $startOfWeek = strtotime("$weekOffset week", $mondayThisWeek);
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = date("Y-m-d", strtotime("+$i day", $startOfWeek));
        }
        
        // Retrieve global reservations for the week (with resource info)
        $startDate = $weekDays[0];
        $endDate = $weekDays[6];
        $stmt = $pdo->prepare("SELECT r.*, re.nombre AS recurso 
                               FROM reservations r 
                               LEFT JOIN resources re ON r.resource_id = re.id 
                               WHERE fecha_reserva BETWEEN :start AND :end 
                               ORDER BY fecha_reserva, hora_reserva");
        $stmt->execute([':start'=>$startDate, ':end'=>$endDate]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resByDateHour = [];
        foreach ($reservations as $res) {
            $date = $res['fecha_reserva'];
            $hour = substr($res['hora_reserva'], 0, 2);
            $resByDateHour[$date][$hour][] = $res;
        }
        
        // Week navigation
        echo '<div class="calendar-navigation" style="margin-bottom:10px;">';
        echo '<a href="?action=reservations&view=weekly&weekOffset='.($weekOffset - 1).'">&laquo; Semana Anterior</a> ';
        echo '<span style="margin:0 10px;">'.date("d/m/Y", strtotime($weekDays[0]))." - ".date("d/m/Y", strtotime($weekDays[6])).'</span>';
        echo '<a href="?action=reservations&view=weekly&weekOffset='.($weekOffset + 1).'">Semana Siguiente &raquo;</a>';
        echo '</div>';
        
        // --- Global Weekly Grid ---
        echo '<h3>Calendario Global</h3>';
        echo '<table class="admin-table">';
        echo '<thead><tr><th>Hora</th>';
        foreach ($weekDays as $day) {
            echo '<th>'.date("D d/m", strtotime($day)).'</th>';
        }
        echo '</tr></thead><tbody>';
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStr = str_pad($hour,2,"0",STR_PAD_LEFT).":00";
            echo '<tr>';
            echo '<td class="hour-label">'.$hourStr.'</td>';
            foreach ($weekDays as $day) {
                echo '<td>';
                $hourKey = str_pad($hour,2,"0",STR_PAD_LEFT);
                if (isset($resByDateHour[$day][$hourKey])) {
                    foreach ($resByDateHour[$day][$hourKey] as $res) {
                        echo '<div style="font-size:10px;">(' . htmlspecialchars(substr($res['hora_reserva'],0,5)) . ') ';
                        echo 'Res: '.htmlspecialchars($res['recurso']).' - '.htmlspecialchars($res['nombre']).'</div>';
                    }
                } else {
                    echo '-';
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
        
        // --- Resource-Specific Weekly Grids ---
        $stmtResources = $pdo->query("SELECT * FROM resources ORDER BY nombre ASC");
        $resources = $stmtResources->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resources as $resource) {
            echo '<h3>Recurso: ' . htmlspecialchars($resource['nombre']) . '</h3>';
            $resByDateHourRes = [];
            foreach ($reservations as $res) {
                if ($res['resource_id'] == $resource['id']) {
                    $date = $res['fecha_reserva'];
                    $hour = substr($res['hora_reserva'], 0, 2);
                    $resByDateHourRes[$date][$hour][] = $res;
                }
            }
            echo '<table class="admin-table">';
            echo '<thead><tr><th>Hora</th>';
            foreach ($weekDays as $day) {
                echo '<th>'.date("D d/m", strtotime($day)).'</th>';
            }
            echo '</tr></thead><tbody>';
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStr = str_pad($hour,2,"0",STR_PAD_LEFT).":00";
                echo '<tr>';
                echo '<td class="hour-label">'.$hourStr.'</td>';
                foreach ($weekDays as $day) {
                    echo '<td>';
                    $hourKey = str_pad($hour,2,"0",STR_PAD_LEFT);
                    if (isset($resByDateHourRes[$day][$hourKey])) {
                        foreach ($resByDateHourRes[$day][$hourKey] as $res) {
                            echo '<div style="font-size:10px;">(' . htmlspecialchars(substr($res['hora_reserva'],0,5)) . ') ';
                            echo htmlspecialchars($res['nombre']).'</div>';
                        }
                    } else {
                        echo '-';
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
    } elseif ($view == 'daily') {
        // === DAILY VIEW ===
        $date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
        $stmt = $pdo->prepare("SELECT r.*, re.nombre AS recurso 
                               FROM reservations r 
                               LEFT JOIN resources re ON r.resource_id = re.id 
                               WHERE fecha_reserva = :date 
                               ORDER BY hora_reserva");
        $stmt->execute([':date'=>$date]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $prevDate = date("Y-m-d", strtotime("$date -1 day"));
        $nextDate = date("Y-m-d", strtotime("$date +1 day"));
        echo '<div class="calendar-navigation" style="margin-bottom:10px;">';
        echo '<a href="?action=reservations&view=daily&date='.$prevDate.'">&laquo; Día Anterior</a> ';
        echo '<span style="margin:0 10px;">'.date("D d/m/Y", strtotime($date)).'</span>';
        echo '<a href="?action=reservations&view=daily&date='.$nextDate.'">Día Siguiente &raquo;</a>';
        echo '</div>';
        
        // --- Global Daily Table ---
        echo '<h3>Calendario Global</h3>';
        echo '<table class="admin-table">';
        echo '<thead><tr>
                <th>Hora</th>
                <th>Recurso</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Notas</th>
              </tr></thead><tbody>';
        if ($reservations) {
            foreach ($reservations as $res) {
                echo '<tr>';
                echo '<td>'.htmlspecialchars($res['hora_reserva']).'</td>';
                echo '<td>'.htmlspecialchars($res['recurso']).'</td>';
                echo '<td>'.htmlspecialchars($res['nombre'].' '.$res['apellidos']).'</td>';
                echo '<td>'.htmlspecialchars($res['email']).'</td>';
                echo '<td>'.htmlspecialchars($res['telefono']).'</td>';
                echo '<td>'.htmlspecialchars($res['notas']).'</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6">No hay reservas para este día.</td></tr>';
        }
        echo '</tbody></table>';
        
        // --- Resource-Specific Daily Tables ---
        $stmtResources = $pdo->query("SELECT * FROM resources ORDER BY nombre ASC");
        $resources = $stmtResources->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resources as $resource) {
            echo '<h3>Recurso: ' . htmlspecialchars($resource['nombre']) . '</h3>';
            $stmtRes = $pdo->prepare("SELECT * FROM reservations WHERE fecha_reserva = :date AND resource_id = :rid ORDER BY hora_reserva");
            $stmtRes->execute([':date'=>$date, ':rid'=>$resource['id']]);
            $resDaily = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
            echo '<table class="admin-table">';
            echo '<thead><tr><th>Hora</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Notas</th></tr></thead><tbody>';
            if ($resDaily) {
                foreach ($resDaily as $res) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($res['hora_reserva']).'</td>';
                    echo '<td>'.htmlspecialchars($res['nombre'].' '.$res['apellidos']).'</td>';
                    echo '<td>'.htmlspecialchars($res['email']).'</td>';
                    echo '<td>'.htmlspecialchars($res['telefono']).'</td>';
                    echo '<td>'.htmlspecialchars($res['notas']).'</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">No hay reservas para este recurso en este día.</td></tr>';
            }
            echo '</tbody></table>';
        }
        
    } else {
        // === DEFAULT TABLE VIEW ===
        $sql = "SELECT r.*, re.nombre AS recurso 
                FROM reservations r 
                LEFT JOIN resources re ON r.resource_id = re.id 
                ORDER BY r.id DESC";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
        foreach ($rows as $r) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($r['id']).'</td>';
            echo '<td>'.htmlspecialchars($r['recurso']).'</td>';
            echo '<td>'.htmlspecialchars($r['fecha_reserva']).'</td>';
            echo '<td>'.htmlspecialchars($r['hora_reserva']).'</td>';
            echo '<td>'.htmlspecialchars($r['nombre'].' '.$r['apellidos']).'</td>';
            echo '<td>'.htmlspecialchars($r['email']).'</td>';
            echo '<td>'.htmlspecialchars($r['telefono']).'</td>';
            echo '<td>'.htmlspecialchars($r['notas']).'</td>';
            echo '<td>'.htmlspecialchars($r['creado_en']).'</td>';
            echo '<td>
                    <a href="?action=edit_reserva&id='.htmlspecialchars($r['id']).'" class="button small">Editar</a>
                    <a href="?action=reservations&del='.htmlspecialchars($r['id']).'" class="button small danger" onclick="return confirm(\'¿Eliminar esta reserva?\');">Eliminar</a>
                  </td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}
?>

