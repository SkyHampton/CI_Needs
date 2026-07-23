<?php
session_start();

/* ── Configuration ── */
$ini_arr = parse_ini_file("ini/dbcred.ini");
$user = $ini_arr["USER"];
$password = $ini_arr["PASS"];
$host = "127.0.0.1";
$database = "CINeeds";

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
$currentUserID = $_SESSION['userID'] ?? 0;

$adminID = $currentUserID;
$reason = trim($_POST["reason"] ?? "Deleted by user");

/* ── Validate required fields ── */
$errors = [];

if ($postID <= 0) {
    $errors[] = "A valid postID is required.";
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
    /* Current logged-in user */
    $currentUserID = $_SESSION['userID'] ?? 0;

    /* Get current user's admin status */
    $userCheck = $db->prepare("
        SELECT admin
        FROM CIN_User
        WHERE userID = :userID
    ");

    $userCheck->execute([
        ":userID" => $currentUserID
    ]);

    $userData = $userCheck->fetch(PDO::FETCH_ASSOC);

    $isAdmin = false;

    if ($userData) {
        $isAdmin = (bool)$userData['admin'];
    }
    
    $postOwnerCheck = $db->prepare("
        SELECT userID
        FROM CIN_Post
        WHERE postID = :postID
    ");

    $postOwnerCheck->execute([
        ":postID" => $postID
    ]);

    $postOwner = $postOwnerCheck->fetch(PDO::FETCH_ASSOC);

    if (!$postOwner) {
        respond(false, "Post not found.");
    }

    /* allow if:
    - owner deleting own post
    - OR admin removing post
    */

    $isOwner = ($postOwner['userID'] == $currentUserID);

    /* Allow:
    - owner deleting own post
    - admin removing any post
    */
    if (!$isOwner && !$isAdmin) {
        respond(false, "Unauthorized action.");
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