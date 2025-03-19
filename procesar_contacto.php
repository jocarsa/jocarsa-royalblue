<?php
// Database file path
$dbFile = '../databases/marketing.db';

try {
    // Create or open SQLite database
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        message TEXT NOT NULL,
        origin_url TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $message = isset($_POST['message']) ? trim($_POST['message']) : null;
    $origin_url = isset($_POST['origin_url']) ? trim($_POST['origin_url']) : null;

    // Validate required fields
    if (!empty($name) && !empty($email) && !empty($message) && !empty($origin_url)) {
        try {
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message, origin_url) VALUES (:name, :email, :message, :origin_url)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':message' => $message,
                ':origin_url' => $origin_url
            ]);

            // Show success message
            echo "<!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Gracias por contactarnos</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                    }
                    .container {
                        max-width: 500px;
                        background: #ffffff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                        text-align: center;
                    }
                    h1 {
                        color: #2c3e50;
                    }
                    p {
                        color: #555;
                        font-size: 16px;
                        line-height: 1.5;
                    }
                    .footer {
                        margin-top: 20px;
                        font-size: 12px;
                        color: #777;
                    }
                    .button {
                        display: inline-block;
                        padding: 12px 24px;
                        margin-top: 20px;
                        background-color: #3498db;
                        color: #ffffff;
                        text-decoration: none;
                        font-weight: bold;
                        border-radius: 5px;
                    }
                    .button:hover {
                        background-color: #2980b9;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>¡Gracias por contactarnos!</h1>
                    <p>Hola <strong>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                    <p>Hemos recibido tu mensaje y nuestro equipo se pondrá en contacto contigo lo antes posible.</p>
                    <p>Si tienes alguna consulta adicional, no dudes en visitarnos.</p>
                    <a href='/' class='button'>Volver al sitio web</a>
                    <p class='footer'>Atentamente,<br><strong>El equipo de nuestra empresa</strong></p>
                </div>
            </body>
            </html>";

            exit;
        } catch (PDOException $e) {
            echo "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        echo "Error: Todos los campos son obligatorios.";
    }
} else {
    echo "Error: Método de solicitud no válido.";
}
?>

