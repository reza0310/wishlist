 <?php
include "../utils.php";

// Create connection
$conn = dbconnect();

// Create database
$sql = "CREATE DATABASE wishlist";
if ($conn->query($sql) === TRUE) {
  echo "Database created successfully";
} else {
  echo "Error creating database: " . $conn->error;
}

$conn->close();
?>