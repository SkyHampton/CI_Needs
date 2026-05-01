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
$username = trim($_POST["username"] ?? "");
$password_input = $_POST["password"] ?? "";

/* ── Validate: required fields ── */
if ($email === "" || $username === "" || $password_input === "") {
    http_response_code(422);
    respond(false, "Email, username, and password are required.");
}

/* ── Validate: must be @myci.csuci.edu ── */
if (!str_ends_with($email, "@myci.csuci.edu")) {
    http_response_code(422);
    respond(false, "Email must end with @myci.csuci.edu.");
}

/* ── Validate: username max 32 characters ── */
if (strlen($username) > 32) {
    http_response_code(422);
    respond(false, "Username must be 32 characters or fewer.");
}

/* ── Insert into DB ── */
try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    /* ── Check for duplicate email ── */
    $check = $db->prepare("SELECT userID FROM CIN_User WHERE email = :email");
    $check->execute([":email" => $email]);
    if ($check->fetch()) {
        http_response_code(409);
        respond(false, "An account with that email already exists.");
    }

    /* ── Insert new user ── */
    $stmt = $db->prepare(
        "INSERT INTO CIN_User (email, username, password, admin, banned)
         VALUES (:email, :username, :password, FALSE, FALSE)"
    );
    $stmt->execute([
        ":email"    => $email,
        ":username" => $username,
        ":password" => $password_input,
    ]);

    $newUserID = $db->lastInsertId();

    /* ── Auto login after registration ── */
    $_SESSION["userID"] = $newUserID;
    $_SESSION["admin"]  = 0;

    respond(true, "Account created successfully.", [
        "userID" => $newUserID,
        "admin"  => 0
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}