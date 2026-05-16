<?php
/* ── edit_post.php — handles POST requests to update an existing post ── */

session_start();

$host     = "137.184.46.194";
$user     = "cineedsc_sky";
$password = "N3ph@ndus";
$database = "cineedsc_db";
$table    = "CIN_Post";

$allowedCategories = ['food', 'housing', 'financial', 'health', 'academic', 'other'];

/* ── Helper ── */
function respond(bool $ok, string $message, array $extra = []): void {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(array_merge(["success" => $ok, "message" => $message], $extra));
    exit;
}

/* ── Only accept POST ── */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Method not allowed.");
}

/* ── Require login via PHP session ── */
if (empty($_SESSION['userID'])) {
    http_response_code(401);
    respond(false, "You must be logged in to edit a post.");
}

/* ── Collect and sanitize fields ── */
$postID    = (int) ($_POST['postID']    ?? 0);
$category  = strtolower(trim($_POST['category']  ?? ""));
$postTitle = trim($_POST['postTitle'] ?? "");
$postData  = trim($_POST['postData']  ?? "");
$contact   = trim($_POST['contact']   ?? "");

/* ── Validate ── */
$errors = [];

if ($postID <= 0) {
    $errors[] = "Invalid post ID.";
}
if (!in_array($category, $allowedCategories, true)) {
    $errors[] = "Invalid category.";
}
if ($postTitle === "") {
    $errors[] = "Title is required.";
} elseif (strlen($postTitle) > 255) {
    $errors[] = "Title must be 255 characters or fewer.";
}
if ($postData === "") {
    $errors[] = "Description is required.";
} elseif (strlen($postData) > 5000) {
    $errors[] = "Description must be 5,000 characters or fewer.";
}
if (!empty($errors)) {
    http_response_code(422);
    respond(false, implode(" ", $errors));
}

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // ── Verify the post belongs to the logged-in user ──
    // Prevents one user from editing another user's post
    $check = $db->prepare("SELECT userID FROM $table WHERE postID = ?");
    $check->execute([$postID]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        respond(false, "Post not found.");
    }
    if ((int)$row['userID'] !== (int)$_SESSION['userID']) {
        http_response_code(403);
        respond(false, "You can only edit your own posts.");
    }

    // ── Run the UPDATE ──
    $stmt = $db->prepare(
        "UPDATE $table
         SET category = :category,
             postTitle = :postTitle,
             postData = :postData,
             contact = :contact
         WHERE postID = :postID"
    );
    $stmt->execute([
        ":category"  => $category,
        ":postTitle" => $postTitle,
        ":postData"  => $postData,
        ":contact"   => $contact !== "" ? $contact : null,
        ":postID"    => $postID,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}

respond(true, "Post updated successfully.", ["postID" => $postID]);