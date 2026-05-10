<?php
session_start();


/* ── Configuration ── */
$host     = "137.184.46.194";
$user     = "cineedsc_sky";
$password = "N3ph@ndus";
$database = "cineedsc_db";

/* ── Helper ── */
function respond(bool $ok, string $message, array $extra = []): void {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(array_merge(["success" => $ok, "message" => $message], $extra));
    exit;
}

/* ── Only accept POST ── */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Method not allowed. Use POST.");
}

/* ── Collect fields ── */
$email    = trim($_POST["email"]    ?? "");
$password_input = $_POST["password"] ?? "";

/* ── Validate ── */
if ($email === "" || $password_input === "") {
    http_response_code(422);
    respond(false, "Email and password are required.");
}

/* ── Query DB ── */
try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    
    $stmt = $db->prepare("SELECT * FROM CIN_User WHERE email = :email");
    $stmt->execute([":email" => $email]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userData || $password_input != $userData['password']) {
        http_response_code(401);
        respond(false, "Invalid email or password.");
    }
    if ($userData['banned']) {
        http_response_code(401);
        respond(false, "You have been banned.");
    }

    /* ── Set session ── */
    $_SESSION["userID"] = $userData['userID'];
    $_SESSION["admin"]   = $userData['admin'];

    respond(true, "Login successful.", ["admin" => $_SESSION["admin"], "userID" => $_SESSION["userID"]]);

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}