<?php
function crearTablas($pdo) {
    // Tabla de usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT,
        email TEXT,
        usuario TEXT UNIQUE,
        password TEXT
    )");

    // Tabla de recursos (con nuevos campos price_per_unit y color)
    $pdo->exec("CREATE TABLE IF NOT EXISTS resources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        descripcion TEXT,
        foto TEXT,
        price_per_unit REAL DEFAULT 0,
        color TEXT DEFAULT ''
    )");

    // Tabla de festivos
    $pdo->exec("CREATE TABLE IF NOT EXISTS holidays (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        fecha TEXT NOT NULL
    )");

    // Tabla de reservas
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        resource_id INTEGER,
        fecha_reserva TEXT NOT NULL,
        hora_reserva TEXT NOT NULL,
        nombre TEXT NOT NULL,
        apellidos TEXT,
        email TEXT NOT NULL,
        telefono TEXT,
        notas TEXT,
        creado_en TEXT,
        FOREIGN KEY (resource_id) REFERENCES resources(id)
    )");

    // Tabla de disponibilidad global (horario 7x24)
    $pdo->exec("CREATE TABLE IF NOT EXISTS hourly_availability (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        day_of_week INTEGER NOT NULL,  
        hour INTEGER NOT NULL,         
        available INTEGER NOT NULL     
    )");

    // Tabla de disponibilidad por recurso (nuevo)
    $pdo->exec("CREATE TABLE IF NOT EXISTS resource_availability (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        resource_id INTEGER NOT NULL,
        day_of_week INTEGER NOT NULL,
        hour INTEGER NOT NULL,
        available INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (resource_id) REFERENCES resources(id)
    )");

    // Rellenar la tabla de disponibilidad global si está vacía
    $count = $pdo->query("SELECT COUNT(*) FROM hourly_availability")->fetchColumn();
    if ($count < 7*24) {
        for ($d=0; $d<7; $d++) {
            for ($h=0; $h<24; $h++) {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM hourly_availability
                                            WHERE day_of_week=:d AND hour=:h");
                $stmtCheck->execute([':d'=>$d, ':h'=>$h]);
                $existe = $stmtCheck->fetchColumn();
                if (!$existe) {
                    $ins = $pdo->prepare("INSERT INTO hourly_availability(day_of_week, hour, available)
                                          VALUES(:d, :h, 0)");
                    $ins->execute([':d'=>$d, ':h'=>$h]);
                }
            }
        }
    }
}
?>

