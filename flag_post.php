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

/* ── Collect and validate postID ── */
$postID = (int) ($_POST["postID"] ?? 0);

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
    $check = $db->prepare("SELECT postID, flagCount FROM CIN_Post WHERE postID = :postID");
    $check->execute([":postID" => $postID]);
    $post = $check->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        respond(false, "Post not found.");
    }

    /* Call the stored procedure */
    $stmt = $db->prepare("CALL flag_post(:postID)");
    $stmt->execute([":postID" => $postID]);

    $newFlagCount = $post["flagCount"] + 1;

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}

/* ── Success ── */
http_response_code(200);
respond(true, "Post flagged successfully.", [
    "postID"    => $postID,
    "flagCount" => $newFlagCount,
]);