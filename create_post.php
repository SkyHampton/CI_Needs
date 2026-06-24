<?php
/* ── Configuration ── */
$host = "127.0.0.1";
$user = "root";
$password = "Skolekosophy";
$database = "CINeeds";

$table      = "CIN_Post";
$uploadDir  = __DIR__ . "/uploads/posts/";
$uploadUri  = "uploads/posts/";        // relative URL stored in DB
$maxBytes   = 5 * 1024 * 1024;        // 5 MB
$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/gif"  => "gif",
    "image/webp" => "webp",
];
$allowedCategories = [
    "food",
    "housing",
    "financial",
    "health",
    "academic",
    "other",
];

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

/* ── Collect and sanitize text fields ── */
$category  = strtolower(trim($_POST["category"]  ?? ""));
$postType = ucfirst(strtolower(trim($_POST["postType"] ?? "")));
$postTitle = trim($_POST["postTitle"] ?? "");
$postData  = trim($_POST["postData"]  ?? "");
$contact   = trim($_POST["contact"]   ?? "");
$userID    = (int) ($_POST["userID"]  ?? 0);

/* ── Validate required fields ── */
$errors = [];

if (!in_array($category, $allowedCategories, true)) {
    $errors[] = "Category must be one of: " . implode(", ", $allowedCategories) . ".";
}
if ($postTitle === "") {
    $errors[] = "Post title is required.";
} elseif (strlen($postTitle) > 255) {
    $errors[] = "Post title must be 255 characters or fewer.";
}
if ($postData === "") {
    $errors[] = "Description is required.";
} elseif (strlen($postData) > 5000) {
    $errors[] = "Description must be 5,000 characters or fewer.";
}
if ($userID <= 0) {
    $errors[] = "A valid userID is required.";
}
if (!empty($errors)) {
    http_response_code(422);
    respond(false, implode(" ", $errors));
}

/* ── Handle optional image upload ── */
$imagePath = null;

if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES["image"];

    // Check for upload errors
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => "File exceeds server upload size limit.",
            UPLOAD_ERR_FORM_SIZE  => "File exceeds form size limit.",
            UPLOAD_ERR_PARTIAL    => "File was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "Upload blocked by server extension.",
        ];
        $errMsg = $uploadErrors[$file["error"]] ?? "Unknown upload error.";
        http_response_code(422);
        respond(false, $errMsg);
    }

    // Check file size
    if ($file["size"] > $maxBytes) {
        http_response_code(422);
        respond(false, "Image must be 5 MB or smaller.");
    }

    // Verify MIME type using finfo (not the browser-supplied type)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file["tmp_name"]);

    if (!array_key_exists($mimeType, $allowedTypes)) {
        http_response_code(422);
        respond(false, "Invalid image type. Allowed: JPEG, PNG, GIF, WebP.");
    }

    $ext      = $allowedTypes[$mimeType];
    $filename = bin2hex(random_bytes(16)) . "." . $ext;   // collision-resistant name

    // Create upload directory if it does not exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            http_response_code(500);
            respond(false, "Server error: unable to create upload directory.");
        }
    }

    $dest = $uploadDir . $filename;
    if (!move_uploaded_file($file["tmp_name"], $dest)) {
        http_response_code(500);
        respond(false, "Server error: could not save the uploaded file.");
    }

    $imagePath = $uploadUri . $filename;   // relative path stored in DB
}

/* ── Insert into database ── */
try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $db->prepare(
        "INSERT INTO $table (userID, postType, category, postTitle, postData, contact, imagePath, postDate, offerExpDate)
         VALUES (:userID, :postType, :category, :postTitle, :postData, :contact, :imagePath, :postDate, :offerExpDate)"
    );

    $stmt->execute([
        ":userID"       => $userID,
        ":postType"     => $postType,
        ":category"     => $category,
        ":postTitle"    => $postTitle,
        ":postData"     => $postData,
        ":contact"      => $contact !== "" ? $contact : null,
        ":imagePath"    => $imagePath,
        ":postDate" => date('Y-m-d H:i:s'),
        ":offerExpDate" => null,
    ]);

    $newPostID = (int) $db->lastInsertId();

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}

/* ── Success ──*/
http_response_code(201);
respond(true, "Post created successfully.", [
    // "postID"    => $newPostID,
    "imagePath" => $imagePath,
]);