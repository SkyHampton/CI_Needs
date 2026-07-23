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

/* ── Collect and validate postID ── */
$postID = (int) ($_POST["postID"] ?? 0);
$userID = $_SESSION['userID'] ?? 0;

if ($userID <= 0) {
    http_response_code(401);
    respond(false, "Login required.");
}

if ($postID <= 0) {
    http_response_code(422);
    respond(false, "A valid postID is required.");
}

/* ── Call the flag_post stored procedure ── */
try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    /* Verify the post actually exists before flagging */
    $check = $db->prepare("SELECT postID FROM CIN_Post WHERE postID = :postID");
    $check->execute([":postID" => $postID]);
    $post = $check->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        respond(false, "Post not found.");
    }
    
    $alreadyFlagged = $db->prepare("
    SELECT flagID
    FROM CIN_Flag
    WHERE postID = :postID
    AND userID = :userID
    ");

    $alreadyFlagged->execute([
        ":postID" => $postID,
        ":userID" => $userID
    ]);

    if ($alreadyFlagged->fetch()) {
        respond(false, "You already flagged this post.");
    }

    /* Call the stored procedure */
    $stmt = $db->prepare("
        INSERT INTO CIN_Flag (
            postID,
            userID,
            flagReason,
            flagComment
        )
        VALUES (
            :postID,
            :userID,
            :flagReason,
            :flagComment
        )
    ");

    $stmt->execute([
        ":postID" => $postID,
        ":userID" => $userID,
        ":flagReason" => $_POST['flagReason'] ?? '',
        ":flagComment" => $_POST['flagComment'] ?? ''
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}

/* ── Success ── */
http_response_code(200);
respond(true, "Post flagged successfully.", [
    "postID"    => $postID
]);