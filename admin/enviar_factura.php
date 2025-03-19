<?php
// /var/www/html/jocarsa-royalblue/admin/enviar_factura.php

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/dbinit.php';
require_once __DIR__ . '/../inc/integration.php';

if (!isset($_GET['id'])) {
    echo "Invoice ID not provided.";
    exit;
}
$invoiceId = (int)$_GET['id'];

// Load the invoice record from "invoices"
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND owner_id = ?");
$stmt->execute([$invoiceId, $_SESSION['admin_id']]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$invoice) {
    echo "Invoice not found or not accessible.";
    exit;
}

// Load invoice line items from "lineas_factura"
$stmtLines = $pdo->prepare("SELECT * FROM lineas_factura WHERE invoice_id = ?");
$stmtLines->execute([$invoiceId]);
$lines = $stmtLines->fetchAll(PDO::FETCH_ASSOC);

// Load customer data from "customers"
$stmtCust = $pdo->prepare("SELECT * FROM customers WHERE id = ? AND owner_id = ?");
$stmtCust->execute([$invoice['customer_id'], $_SESSION['admin_id']]);
$customer = $stmtCust->fetch(PDO::FETCH_ASSOC);
if (!$customer) {
    echo "Customer not found.";
    exit;
}

// Build the JSON payload to send
$invoiceData = [
    "invoice_number" => $invoice['invoice_number'],
    "date" => $invoice['fecha'],
    "customer" => [
        "name" => $customer['name'],
        "email" => $customer['email'],
        "address" => $customer['address'] ?? "",
        "id_number" => $customer['id_number'] ?? ""
    ],
    "lines" => [],
    "subtotal" => 0,
    "iva" => 0,
    "retention" => 0,
    "total" => $invoice['total'],
    "software_b_user_id" => getSoftwareBUserId() // Mapping function for Software B user id
];

$subtotal = 0;
foreach ($lines as $line) {
    $cantidad = $line['cantidad'] ?? 0;
    $precio_unitario = $line['precio_unitario'] ?? 0;
    $lineTotal = $cantidad * $precio_unitario;
    $subtotal += $lineTotal;
    $invoiceData['lines'][] = [
         "product" => [
             "name" => $line['producto'] ?? "Service",
             "description" => "",
             "price" => $precio_unitario
         ],
         "quantity" => $cantidad,
         "unit_price" => $precio_unitario,
         "total" => $lineTotal
    ];
}
$invoiceData['subtotal'] = $subtotal;
$invoiceData['iva'] = $subtotal * 0.21;        // Example IVA: 21%
$invoiceData['retention'] = $subtotal * 0.15;    // Example retention: 15%
$invoiceData['total'] = $subtotal + $invoiceData['iva'] - $invoiceData['retention'];

// Send the JSON payload to Software B
$result = sendInvoiceToInvoicing($invoiceData);

if (isset($result['error'])) {
    $_SESSION['flash'] = "Error sending invoice: " . $result['error'];
} else {
    $_SESSION['flash'] = "Invoice sent successfully. Received ID: " . $result['invoice_id'];
}

header("Location: index.php?action=invoices");
exit;

