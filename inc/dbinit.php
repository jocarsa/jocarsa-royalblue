<?php
// Incluir archivo de configuración (se asume que config.php está en la raíz)
require_once __DIR__ . '/../config.php';

// Establecer la zona horaria según la configuración
date_default_timezone_set(TIMEZONE);

// Conexión con PDO a SQLite utilizando la constante DB_PATH
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Inicializar las tablas (incluyendo los nuevos campos y tablas)
require_once __DIR__ . '/../admin/funciones/crearTablas.php';
crearTablas($pdo);

// Create table for front-end customers
$pdo->exec("CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
)");

// Determinar el paso actual: 
// 1: Seleccionar recurso, 2: Calendario, 3: Datos personales, 4: Confirmación
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($step < 1 || $step > 4) {
    $step = 1;
}
?>

