<?php
require_once __DIR__ . '/../config.php';
date_default_timezone_set(TIMEZONE);
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
require_once __DIR__ . '/../admin/funciones/crearTablas.php';
crearTablas($pdo);

// Create table for front‑end customers
$pdo->exec("CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
)");

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($step < 1 || $step > 4) {
    $step = 1;
}
?>

