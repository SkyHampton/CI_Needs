<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "cineeds_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Get post ID
$postID = $_POST['postID'] ?? null;

if (!$postID) {
    die("Invalid request");
}

// Optional: check ownership
$userID = $_SESSION['userID'] ?? null;

$check = $conn->prepare("
    SELECT userID, fulfilled FROM CIN_Post WHERE postID = ?
");
$check->bind_param("i", $postID);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("Post not found");
}

$post = $result->fetch_assoc();

// Prevent double update
if ($post['fulfilled']) {
    echo "Post already fulfilled.";
    exit;
}

// Ownership check (recommended)
if ($userID && $post['userID'] != $userID) {
    die("Unauthorized action");
}

// Update fulfilled
$stmt = $conn->prepare("
    UPDATE CIN_Post 
    SET fulfilled = TRUE 
    WHERE postID = ?
");

$stmt->bind_param("i", $postID);

if ($stmt->execute()) {
    echo "Post marked as fulfilled!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>