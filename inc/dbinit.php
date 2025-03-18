<?php
/**
 * inc/dbinit.php
 * --------------
 * Ensures DB is connected, and creates tables if they do not exist.
 * Also inserts a default superadmin user:
 *   username: jocarsa
 *   password: jocarsa
 *   name    : Jose Vicente Carratala
 *   email   : info@josevicentecarratala.com
 */

require_once __DIR__ . '/../config.php';
date_default_timezone_set(TIMEZONE);

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

/**
 * 1) Table: admin_users
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'admin', -- 'admin' or 'superadmin'
    business_name TEXT,
    address TEXT,
    vat_id TEXT,
    email TEXT,
    phone TEXT
);
");

/**
 * 2) Table: resources
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL,
    nombre TEXT NOT NULL,
    descripcion TEXT,
    foto TEXT,
    price_per_unit REAL DEFAULT 0,
    color TEXT DEFAULT '',
    FOREIGN KEY (owner_id) REFERENCES admin_users(id)
);
");

/**
 * 3) Table: resource_availability
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS resource_availability (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL,
    resource_id INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL,  -- 0..6
    hour INTEGER NOT NULL,         -- 0..23
    available INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (resource_id) REFERENCES resources(id),
    FOREIGN KEY (owner_id) REFERENCES admin_users(id)
);
");

/**
 * 4) Table: holidays
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS holidays (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL,
    fecha TEXT NOT NULL,            -- e.g. '2025-12-25'
    FOREIGN KEY (owner_id) REFERENCES admin_users(id)
);
");

/**
 * 5) Table: reservations
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL,       -- references admin_users(id)
    resource_id INTEGER,
    fecha_reserva TEXT NOT NULL,
    hora_reserva TEXT NOT NULL,
    nombre TEXT NOT NULL,
    apellidos TEXT,
    email TEXT NOT NULL,
    telefono TEXT,
    notas TEXT,
    creado_en TEXT,
    invoice_id INTEGER DEFAULT NULL, -- if not NULL => invoice linked
    billing_request INTEGER NOT NULL DEFAULT 0,
    billing_name TEXT,
    billing_address TEXT,
    billing_vat_id TEXT,
    FOREIGN KEY (resource_id) REFERENCES resources(id),
    FOREIGN KEY (owner_id) REFERENCES admin_users(id)
);
");

/**
 * 6) Table: invoices
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL,
    invoice_number TEXT UNIQUE,
    invoice_date TEXT,
    total REAL DEFAULT 0,
    FOREIGN KEY (owner_id) REFERENCES admin_users(id)
);
");

/**
 * 7) Table: customers
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    FOREIGN KEY (owner_id) REFERENCES admin_users(id)
);
");

/**
 * 8) (Optional) Table: users
 *    If you keep old "users" for your admin system, or 
 *    you can remove if not needed.
 */
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT,
    email TEXT,
    usuario TEXT UNIQUE,
    password TEXT
);
");

/**
 * Insert default superadmin: jocarsa
 * We'll check if there's already a user with username='jocarsa' AND role='superadmin'.
 */
$stmtCheckSuper = $pdo->prepare("SELECT COUNT(*) FROM admin_users 
                                WHERE username=:u AND role='superadmin'");
$stmtCheckSuper->execute([':u' => 'jocarsa']);
$existsSuper = $stmtCheckSuper->fetchColumn();

if (!$existsSuper) {
    $stmtInsSuper = $pdo->prepare("
        INSERT INTO admin_users (username, password, role, email, business_name)
        VALUES (:u, :p, 'superadmin', :e, :bn)
    ");
    $stmtInsSuper->execute([
        ':u'  => 'jocarsa',
        ':p'  => 'jocarsa', // In production, use password_hash()
        ':e'  => 'info@josevicentecarratala.com',
        ':bn' => 'Jose Vicente Carratala' // Storing the name in 'business_name' or use 'address' if you prefer
    ]);
}

/**
 * Insert a default 'admin' user if you want an example admin (optional).
 */
$stmtCheckAdmin = $pdo->prepare("SELECT COUNT(*) FROM admin_users 
                                 WHERE username=:u AND role='admin'");
$stmtCheckAdmin->execute([':u' => 'admin']);
$existsAdmin = $stmtCheckAdmin->fetchColumn();

if (!$existsAdmin) {
    $stmtInsAdmin = $pdo->prepare("
        INSERT INTO admin_users (username, password, role, email)
        VALUES (:u, :p, 'admin', :e)
    ");
    $stmtInsAdmin->execute([
        ':u' => 'admin',
        ':p' => 'admin123', // In production, use password_hash()
        ':e' => 'admin@domain.com'
    ]);
}

// If your front-end uses step logic:
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($step < 1 || $step > 4) {
    $step = 1;
}
?>

