<?php
// /var/www/html/jocarsa-royalblue/admin/crear_factura.php

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/dbinit.php';  // $pdo available

// For simplicity, we assume the admin is logged in and their id is in $_SESSION['admin_id']
// (In a real system, add proper authentication and validation.)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invoice_number = trim($_POST['invoice_number'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $lines = $_POST['lines'] ?? [];
    
    // Calculate total
    $total = 0;
    foreach ($lines as $line) {
        $cantidad = (int)$line['cantidad'];
        $precio = (float)$line['precio_unitario'];
        $total += $cantidad * $precio;
    }
    
    // Insert invoice
    $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, fecha, customer_id, total, owner_id) VALUES (?,?,?,?,?)");
    $stmt->execute([$invoice_number, $fecha, $customer_id, $total, $_SESSION['admin_id']]);
    $invoice_id = $pdo->lastInsertId();
    
    // Insert line items into lineas_factura
    foreach ($lines as $line) {
        $cantidad = (int)$line['cantidad'];
        $precio = (float)$line['precio_unitario'];
        $line_total = $cantidad * $precio;
        $producto = trim($line['producto']) ?: 'Service';
        $stmtLine = $pdo->prepare("INSERT INTO lineas_factura (invoice_id, producto, cantidad, precio_unitario, total) VALUES (?,?,?,?,?)");
        $stmtLine->execute([$invoice_id, $producto, $cantidad, $precio, $line_total]);
    }
    
    header("Location: index.php?action=invoices");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Factura</title>
</head>
<body>
<h1>Crear Factura</h1>
<form method="post" action="crear_factura.php">
    <label>Factura Nº:</label>
    <input type="text" name="invoice_number" required><br>
    <label>Fecha:</label>
    <input type="date" name="fecha" required><br>
    <label>Customer ID:</label>
    <input type="number" name="customer_id" required><br>
    
    <h3>Líneas de factura</h3>
    <div id="lines">
        <div class="line">
            <label>Producto:</label>
            <input type="text" name="lines[0][producto]" required>
            <label>Cantidad:</label>
            <input type="number" name="lines[0][cantidad]" required>
            <label>Precio Unitario:</label>
            <input type="number" step="0.01" name="lines[0][precio_unitario]" required>
        </div>
    </div>
    <button type="button" onclick="addLine()">Agregar línea</button><br>
    <button type="submit">Crear Factura</button>
</form>
<script>
let lineIndex = 1;
function addLine() {
    const div = document.createElement('div');
    div.className = 'line';
    div.innerHTML = `<label>Producto:</label>
        <input type="text" name="lines[${lineIndex}][producto]" required>
        <label>Cantidad:</label>
        <input type="number" name="lines[${lineIndex}][cantidad]" required>
        <label>Precio Unitario:</label>
        <input type="number" step="0.01" name="lines[${lineIndex}][precio_unitario]" required>`;
    document.getElementById('lines').appendChild(div);
    lineIndex++;
}
</script>
</body>
</html>

