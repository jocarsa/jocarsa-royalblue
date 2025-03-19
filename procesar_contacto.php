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
    die(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data (no JavaScript required)
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

            echo json_encode(["status" => "success", "message" => "Contact saved successfully"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "All fields are required",
            "debug" => [
                "name" => $name,
                "email" => $email,
                "message" => $message,
                "origin_url" => $origin_url
            ]
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>

