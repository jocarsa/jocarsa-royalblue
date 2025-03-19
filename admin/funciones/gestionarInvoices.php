<?php
// admin/funciones/gestionarInvoices.php

function gestionarInvoices($pdo, $owner_id) {
    echo "<h2>Gestión de Facturas</h2>";

    // 1) If "create_invoice" is posted, we create a new invoice
    if (isset($_POST['create_invoice']) && !empty($_POST['reservation_ids'])) {
        $resIds = $_POST['reservation_ids']; // array of selected reservation IDs

        // Insert new invoice
        $invoiceNumber = 'INV-' . time();  // or generate your own unique reference
        $invoiceDate = date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO invoices (owner_id, invoice_number, invoice_date, total)
                               VALUES (:oid, :invnum, :invdate, 0)");
        $stmt->execute([
            ':oid' => $owner_id,
            ':invnum' => $invoiceNumber,
            ':invdate' => $invoiceDate
        ]);
        $invoice_id = $pdo->lastInsertId();

        // Next, link those reservations to this new invoice
        // Also sum the total
        $total = 0;
        foreach ($resIds as $rid) {
            // We might want to fetch the price of that reservation's resource
            $stmtR = $pdo->prepare("SELECT r.price_per_unit
                                    FROM reservations rs
                                    JOIN resources r ON rs.resource_id = r.id
                                    WHERE rs.id=:res AND rs.owner_id=:oid");
            $stmtR->execute([':res' => $rid, ':oid' => $owner_id]);
            $price = $stmtR->fetchColumn();
            $price = $price ? floatval($price) : 0;
            $total += $price;

            // Update the reservation's invoice_id
            $stmtUpd = $pdo->prepare("UPDATE reservations SET invoice_id=:inv WHERE id=:res AND owner_id=:oid");
            $stmtUpd->execute([':inv' => $invoice_id, ':res' => $rid, ':oid' => $owner_id]);
        }

        // Update the invoice total
        $stmtU = $pdo->prepare("UPDATE invoices SET total=:t WHERE id=:iid AND owner_id=:oid");
        $stmtU->execute([':t' => $total, ':iid' => $invoice_id, ':oid' => $owner_id]);

        echo "<p>Factura creada con número <strong>$invoiceNumber</strong>, total: <strong>$total €</strong></p>";
    }

    // 2) Show all existing invoices filtered by owner_id
    echo "<h3>Facturas Existentes</h3>";
    $stmtI = $pdo->prepare("SELECT * FROM invoices WHERE owner_id=:oid ORDER BY id DESC");
    $stmtI->execute([':oid' => $owner_id]);
    $invoices = $stmtI->fetchAll(PDO::FETCH_ASSOC);
    echo '<table class="admin-table">';
    echo '<thead><tr>
            <th>ID</th>
            <th>Número</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Acciones</th>
          </tr></thead><tbody>';
    foreach ($invoices as $inv) {
        echo '<tr>';
        echo '<td>'.$inv['id'].'</td>';
        echo '<td>'.htmlspecialchars($inv['invoice_number']).'</td>';
        echo '<td>'.htmlspecialchars($inv['invoice_date']).'</td>';
        echo '<td>'.htmlspecialchars($inv['total']).'</td>';
        // When printing each row in your invoices table, add:
// Example snippet within the loop that outputs each invoice row:
echo '<td>
        <a href="?action=edit_invoice&id=' . $inv['id'] . '" class="button small">Editar</a>
        <a href="?action=invoices&del=' . $inv['id'] . '" class="button small danger" onclick="return confirm(\'¿Eliminar?\');">Eliminar</a>
        <a href="enviar_factura.php?id=' . $inv['id'] . '" class="button small" onclick="return confirm(\'¿Enviar esta factura al sistema de facturación?\');">Enviar a Rosybrown</a>
      </td>';


        echo '</tr>';
    }
    echo '</tbody></table>';

    // 3) If we click “view” to see invoice details
    if (isset($_GET['view'])) {
        $invoice_id = (int) $_GET['view'];
        $stmtV = $pdo->prepare("SELECT * FROM invoices WHERE id=:iid AND owner_id=:oid");
        $stmtV->execute([':iid' => $invoice_id, ':oid' => $owner_id]);
        $invoice = $stmtV->fetch(PDO::FETCH_ASSOC);
        if ($invoice) {
            echo "<h4>Detalle de Factura #".htmlspecialchars($invoice['invoice_number'])."</h4>";
            // Show reservations
            $stmtR = $pdo->prepare("SELECT rs.*, r.nombre AS recurso, r.price_per_unit
                                    FROM reservations rs
                                    JOIN resources r ON rs.resource_id = r.id
                                    WHERE rs.owner_id=:oid AND rs.invoice_id=:iid");
            $stmtR->execute([':oid' => $owner_id, ':iid' => $invoice_id]);
            $items = $stmtR->fetchAll(PDO::FETCH_ASSOC);

            echo '<table class="admin-table">';
            echo '<thead><tr>
                    <th>ID Res.</th>
                    <th>Recurso</th>
                    <th>Fecha/Hora</th>
                    <th>Cliente</th>
                    <th>Precio</th>
                  </tr></thead><tbody>';
            foreach ($items as $it) {
                echo '<tr>';
                echo '<td>'.$it['id'].'</td>';
                echo '<td>'.htmlspecialchars($it['recurso']).'</td>';
                echo '<td>'.htmlspecialchars($it['fecha_reserva']).' '.htmlspecialchars($it['hora_reserva']).'</td>';
                echo '<td>'.htmlspecialchars($it['nombre']).' '.htmlspecialchars($it['apellidos']).'</td>';
                echo '<td>'.htmlspecialchars($it['price_per_unit']).'</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

            echo "<p>Total Factura: ".$invoice['total']." €</p>";
        }
    }

    // 4) Show reservations not yet invoiced for selection filtered by owner_id
    echo "<hr><h3>Crear Factura Nueva</h3>";
    // Reservations with invoice_id IS NULL => not invoiced
    $stmtN = $pdo->prepare("SELECT rs.*, r.nombre AS recurso, r.price_per_unit
                            FROM reservations rs
                            JOIN resources r ON rs.resource_id = r.id
                            WHERE rs.owner_id=:oid
                              AND rs.invoice_id IS NULL
                            ORDER BY rs.fecha_reserva DESC");
    $stmtN->execute([':oid' => $owner_id]);
    $unInvoiced = $stmtN->fetchAll(PDO::FETCH_ASSOC);
    if ($unInvoiced) {
        echo '<form method="post" style="margin-top:20px;">';
        echo '<table class="admin-table">';
        echo '<thead><tr>
                <th></th>
                <th>ID</th>
                <th>Recurso</th>
                <th>Fecha/Hora</th>
                <th>Precio</th>
                <th>Cliente</th>
              </tr></thead><tbody>';
        foreach ($unInvoiced as $u) {
            echo '<tr>';
            echo '<td><input type="checkbox" name="reservation_ids[]" value="'.$u['id'].'"></td>';
            echo '<td>'.$u['id'].'</td>';
            echo '<td>'.htmlspecialchars($u['recurso']).'</td>';
            echo '<td>'.htmlspecialchars($u['fecha_reserva']).' '.htmlspecialchars($u['hora_reserva']).'</td>';
            echo '<td>'.htmlspecialchars($u['price_per_unit']).'</td>';
            echo '<td>'.htmlspecialchars($u['nombre'].' '.$u['apellidos']).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<button type="submit" name="create_invoice">Generar Factura</button>';
        echo '</form>';
    } else {
        echo "<p>No hay reservas pendientes de facturar.</p>";
    }
}

