<?php
// Database connection details
$DB_HOST = 'localhost';
$DB_NAME = 'portfolio_db';
$DB_USER = 'root';  // Change if needed
$DB_PASS = '';      // Change if needed

try {
    // Create a PDO instance
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Collect form data and sanitize it
        $name = htmlspecialchars($_POST['name']);
        $contact_number = htmlspecialchars($_POST['contact_number']);
        $email = htmlspecialchars($_POST['email']);
        $description = htmlspecialchars($_POST['description']);

        // Insert into the database
        $sql = "INSERT INTO form (name, contact_number, email, description) 
                VALUES (:name, :contact_number, :email, :description)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':contact_number' => $contact_number,
            ':email' => $email,
            ':description' => $description
        ]);

        // Redirect or show success message
        echo '<script>';
        echo 'window.location.href = "/portfolio/index.html";';  // Redirect to home page (or contact page)
        echo 'alert("Thank you for contacting us, ' . $name . '! Your message has been received.");'; // Show notification
        echo '</script>';
    }
} catch (PDOException $e) {
    // If there’s an error with the database connection
    echo "Error: " . $e->getMessage();
}
?>
