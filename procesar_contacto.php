<?php
// Database file path
$dbFile = '../databases/marketing.db';

// Create or open SQLite database
try {
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
    die("Database error: " . $e->getMessage());
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get POST data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $origin_url = filter_input(INPUT_POST, 'origin_url', FILTER_SANITIZE_URL);

    // Validate required fields
    if ($name && $email && $message && $origin_url) {
        try {
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message, origin_url) VALUES (:name, :email, :message, :origin_url)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':message' => $message,
                ':origin_url' => $origin_url
            ]);

            echo json_encode(["status" => "success", "message" => "Contact saved successfully"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

?>

