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

    // Check if Mark as Done button is pressed (using GET request with 'done' parameter)
    if (isset($_GET['done'])) {
        $id = $_GET['done'];
        
        // Update the 'is_done' status in the database
        $stmt = $pdo->prepare("UPDATE form SET is_done = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Redirect back to the same page after marking as done
        header("Location: view_responses.php");
        exit;
    }

    // Fetch the form responses, divided into two sections (Not Responded and Responded)
    $sql_not_responded = "SELECT * FROM form WHERE is_done = 0 ORDER BY submitted_at DESC";
    $stmt_not_responded = $pdo->query($sql_not_responded);
    $not_responded = $stmt_not_responded->fetchAll();

    $sql_responded = "SELECT * FROM form WHERE is_done = 1 ORDER BY submitted_at DESC";
    $stmt_responded = $pdo->query($sql_responded);
    $responded = $stmt_responded->fetchAll();

    echo '<h2 class="section-title">Form Responses</h2>';

    // Not Responded Yet Section
    echo '<div class="section-heading">Not Responded Yet</div>';
    if ($not_responded) {
        echo '<div class="responses-container">';
        foreach ($not_responded as $response) {
            displayResponse($response);
        }
        echo '</div>';
    } else {
        echo '<p>No responses yet.</p>';
    }

    // Responded Section
    echo '<div class="section-heading">Responded</div>';
    if ($responded) {
        echo '<div class="responses-container">';
        foreach ($responded as $response) {
            displayResponse($response);
        }
        echo '</div>';
    } else {
        echo '<p>No responded entries.</p>';
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Function to Display Each Response
function displayResponse($response) {
    echo '<div class="response-item">';
    echo '<h3>' . htmlspecialchars($response['name']) . '</h3>';
    echo '<p><strong>Contact Number:</strong> ' . htmlspecialchars($response['contact_number']) . '</p>';
    echo '<p><strong>Email:</strong> ' . htmlspecialchars($response['email']) . '</p>';
    echo '<p><strong>Description:</strong> ' . htmlspecialchars($response['description']) . '</p>';
    echo '<p><strong>Submitted At:</strong> ' . htmlspecialchars($response['submitted_at']) . '</p>';
    echo '<div class="buttons">';
    echo '<button class="call-btn" onclick="window.location.href=\'tel:' . htmlspecialchars($response['contact_number']) . '\'"><i class="fas fa-phone-alt"></i> Call</button>';
    echo '<button class="email-btn" onclick="window.location.href=\'mailto:' . htmlspecialchars($response['email']) . '\'"><i class="fas fa-envelope"></i> Email</button>';
    
    if ($response['is_done'] == 0) {
        echo '<button class="done-btn"><a href="view_responses.php?done=' . $response['id'] . '"><i class="fas fa-check-circle"></i> Mark as Done</a></button>';
    } else {
        echo '<button class="done-btn" style="background-color: #f44336;"><i class="fas fa-check-circle"></i> Done</button>';
    }
    echo '</div>'; 
    echo '</div>';
}
?>

<!-- CSS for Styling the Response Cards and Buttons -->
<style>
/* =========================
   General Page Styling
========================= */
body {
  background: linear-gradient(45deg, #1f2531, #4e6d7e); /* Gradient background */
  font-family: Arial, sans-serif;
  color: #cfe1ff;
  margin: 0;
  padding: 0;
}

.section-title {
  font-size: 2rem;
  color: #00bcd4;
  text-align: center;
  margin-bottom: 30px;
}

/* =========================
   Section Heading (Not Responded and Responded)
========================= */
.section-heading {
  font-size: 1.75rem;
  color: #00bcd4;
  margin-top: 40px;
  margin-bottom: 10px;
  text-align: center;
}

/* =========================
   Responses List View (Professional and Colorful UI)
========================= */
.responses-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Responsive grid */
  gap: 20px;
  padding: 40px;
}

.response-item {
  background: linear-gradient(180deg, #1f2531, #101a2d); /* Dark background for cards */
  border-radius: 10px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 10px 25px rgba(0, 188, 212, 0.1); /* Soft shadow */
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.response-item:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 35px rgba(0, 188, 212, 0.15); /* Elevated effect on hover */
}

.response-item h3 {
  font-size: 1.5rem;
  color: #fff;
  margin-bottom: 15px;
}

.response-item p {
  font-size: 1rem;
  color: #cfe1ff;
  opacity: 0.9;
  margin: 5px 0;
}

.response-item strong {
  color: #00bcd4;
}

/* =========================
   Buttons Styling (Call, Email, Mark as Done)
========================= */
.buttons {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.buttons button {
  padding: 8px 15px; /* Smaller buttons */
  font-size: 12px;
  border-radius: 5px;
  color: white;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.buttons button i {
  font-size: 16px; /* Icon size */
}

.buttons button:hover {
  transform: translateY(-3px);
}

.call-btn {
  background-color: #5dff60; /* Green for Call */
}

.email-btn {
  background-color: #ff6b6b; /* Red for Email */
}

.done-btn {
  background-color: #ffb84d; /* Orange for Mark as Done */
}

.done-btn.done {
  background-color: #f44336; /* Red when Done */
}

.done-btn a {
  color: white;
  text-decoration: none;
}

.done-btn:disabled {
  background-color: #e0e0e0;
  cursor: not-allowed;
}

@media (max-width: 699px) {
  .responses-container {
    padding: 20px;
  }

  .response-item {
    padding: 15px;
  }

  .buttons button {
    padding: 6px 15px;
  }
}
</style>

<!-- FontAwesome CDN for Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
