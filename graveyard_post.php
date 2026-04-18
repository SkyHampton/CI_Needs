<?php
/* ── Configuration ── */
$host     = "137.184.46.194";
$user     = "cineedsc_sky";
$password = "N3ph@ndus";
$database = "cineedsc_db";

/* ── Helper: send JSON response and exit ── */
function respond(bool $ok, string $message, array $extra = []): void {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(array_merge(["success" => $ok, "message" => $message], $extra));
    exit;
}

/* ── Only accept POST requests ── */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Method not allowed. Use POST.");
}

/* ── Collect and sanitize fields ── */
$postID  = (int)   ($_POST["postID"]  ?? 0);
$adminID = (int)   ($_POST["adminID"] ?? 0);
$reason  = trim(   $_POST["reason"]   ?? "");

/* ── Validate required fields ── */
$errors = [];

if ($postID <= 0) {
    $errors[] = "A valid postID is required.";
}
if ($adminID <= 0) {
    $errors[] = "A valid adminID is required.";
}
if ($reason === "") {
    $errors[] = "A reason is required.";
} elseif (strlen($reason) > 255) {
    $errors[] = "Reason must be 255 characters or fewer.";
}

if (!empty($errors)) {
    http_response_code(422);
    respond(false, implode(" ", $errors));
}

/* ── Call the graveyard_post stored procedure ── */
try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    /* Verify the post exists before proceeding */
    $check = $db->prepare("SELECT postID FROM CIN_Post WHERE postID = :postID");
    $check->execute([":postID" => $postID]);

    if (!$check->fetch()) {
        http_response_code(404);
        respond(false, "Post not found.");
    }

    /* Verify the admin exists and is a valid user */
    $adminCheck = $db->prepare("SELECT userID FROM CIN_User WHERE userID = :adminID");
    $adminCheck->execute([":adminID" => $adminID]);

    if (!$adminCheck->fetch()) {
        http_response_code(403);
        respond(false, "Admin user not found.");
    }

    /* Call the stored procedure — copies post to graveyard then deletes from CIN_Post */
    $stmt = $db->prepare("CALL graveyard_post(:postID, :adminID, :reason)");
    $stmt->execute([
        ":postID"  => $postID,
        ":adminID" => $adminID,
        ":reason"  => $reason,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}

/* ── Success ── */
http_response_code(200);
respond(true, "Post has been sent to the graveyard.", [
    "postID"  => $postID,
    "adminID" => $adminID,
    "reason"  => $reason,
]);