<?php
// admin/funciones/crearTablas.php

function crearTablas($pdo) 
{
    // Drop all relevant tables if you want a fresh start:
    $pdo->exec("DROP TABLE IF EXISTS admin_users");
    $pdo->exec("DROP TABLE IF EXISTS resources");
    $pdo->exec("DROP TABLE IF EXISTS resource_availability");
    $pdo->exec("DROP TABLE IF EXISTS holidays");
    $pdo->exec("DROP TABLE IF EXISTS reservations");
    $pdo->exec("DROP TABLE IF EXISTS invoices");
    $pdo->exec("DROP TABLE IF EXISTS customers");

    // 1) Table of admin users (including superadmin)
    $pdo->exec("CREATE TABLE admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'admin', -- could be 'admin' or 'superadmin'
        business_name TEXT,
        address TEXT,
        vat_id TEXT,
        email TEXT,
        phone TEXT
    )");

    // 2) Table of resources
    $pdo->exec("CREATE TABLE resources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        owner_id INTEGER NOT NULL,             -- references admin_users(id)
        nombre TEXT NOT NULL,
        descripcion TEXT,
        foto TEXT,
        price_per_unit REAL DEFAULT 0,
        color TEXT DEFAULT '',
        FOREIGN KEY (owner_id) REFERENCES admin_users(id)
    )");

    // 3) Table of resource availability
    $pdo->exec("CREATE TABLE resource_availability (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        owner_id INTEGER NOT NULL,             -- references admin_users(id)
        resource_id INTEGER NOT NULL,
        day_of_week INTEGER NOT NULL,
        hour INTEGER NOT NULL,
        available INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (resource_id) REFERENCES resources(id),
        FOREIGN KEY (owner_id) REFERENCES admin_users(id)
    )");

    // 4) Table of holidays
    $pdo->exec("CREATE TABLE holidays (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        owner_id INTEGER NOT NULL,
        fecha TEXT NOT NULL,
        FOREIGN KEY (owner_id) REFERENCES admin_users(id)
    )");

    // 5) Table of reservations
    $pdo->exec("CREATE TABLE reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        owner_id INTEGER NOT NULL,        -- references admin_users(id)
        resource_id INTEGER,
        fecha_reserva TEXT NOT NULL,
        hora_reserva TEXT NOT NULL,
        nombre TEXT NOT NULL,
        apellidos TEXT,
        email TEXT NOT NULL,
        telefono TEXT,
        notas TEXT,
        creado_en TEXT,
        invoice_id INTEGER DEFAULT NULL,  -- if not NULL => already invoiced
        -- Billing fields from front user if they request an invoice
        billing_request INTEGER NOT NULL DEFAULT 0,
        billing_name TEXT,
        billing_address TEXT,
        billing_vat_id TEXT,
        FOREIGN KEY (resource_id) REFERENCES resources(id),
        FOREIGN KEY (owner_id) REFERENCES admin_users(id)
    )");

    // 6) Table of invoices
    $pdo->exec("CREATE TABLE invoices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        owner_id INTEGER NOT NULL,
        invoice_number TEXT UNIQUE,
        invoice_date TEXT,
        total REAL DEFAULT 0,
        FOREIGN KEY (owner_id) REFERENCES admin_users(id)
    )");

    // 7) Table of customers (if you need it)
    $pdo->exec("CREATE TABLE customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        owner_id INTEGER NOT NULL,
        nombre TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        FOREIGN KEY (owner_id) REFERENCES admin_users(id)
    )");

    // Optionally insert a default superadmin or do it manually.
    $pdo->exec("INSERT INTO admin_users (username, password, role, email)
                VALUES ('superadmin', 'supersecret', 'superadmin', 'superadmin@domain.com')");
}

