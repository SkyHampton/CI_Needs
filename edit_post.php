<?php
/* ── edit_post.php — handles POST requests to update an existing post ── */

session_start();
$ini_arr = parse_ini_file("ini/dbcred.ini");
$user = $ini_arr["USER"];
$password = $ini_arr["PASS"];
$host = "127.0.0.1";
$database = "CINeeds";
$table    = "CIN_Post";

$uploadDir    = __DIR__ . "/uploads/posts/";
$uploadUri    = "uploads/posts/";
$maxBytes     = 5 * 1024 * 1024;
$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/gif"  => "gif",
    "image/webp" => "webp",
];
$allowedCategories = ['food', 'housing', 'financial', 'health', 'academic', 'other'];
$allowedPostTypes  = ['Need', 'Offering'];

function respond(bool $ok, string $message, array $extra = []): void {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(array_merge(["success" => $ok, "message" => $message], $extra));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Method not allowed.");
}

if (empty($_SESSION['userID'])) {
    http_response_code(401);
    respond(false, "You must be logged in to edit a post.");
}

/* ── Collect and sanitize fields ── */
$postID    = (int) ($_POST['postID']    ?? 0);
$category  = strtolower(trim($_POST['category']  ?? ""));
$postType  = ucfirst(strtolower(trim($_POST['postType'] ?? "")));
$postTitle = trim($_POST['postTitle'] ?? "");
$postData  = trim($_POST['postData']  ?? "");
$contact   = trim($_POST['contact']   ?? "");
$removePhoto = ($_POST['removePhoto'] ?? '') === '1';

/* ── Validate ── */
$errors = [];
if ($postID <= 0)                                   $errors[] = "Invalid post ID.";
if (!in_array($category, $allowedCategories, true)) $errors[] = "Invalid category.";
if (!in_array($postType, $allowedPostTypes, true))  $errors[] = "Invalid post type.";
if ($postTitle === "")                              $errors[] = "Title is required.";
elseif (strlen($postTitle) > 255)                  $errors[] = "Title must be 255 characters or fewer.";
if ($postData === "")                               $errors[] = "Description is required.";
elseif (strlen($postData) > 5000)                  $errors[] = "Description must be 5,000 characters or fewer.";

if (!empty($errors)) {
    http_response_code(422);
    respond(false, implode(" ", $errors));
}

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verify post belongs to logged-in user
    $check = $db->prepare("SELECT userID, imagePath FROM $table WHERE postID = ?");
    $check->execute([$postID]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!$row) { http_response_code(404); respond(false, "Post not found."); }
    if ((int)$row['userID'] !== (int)$_SESSION['userID']) {
        http_response_code(403);
        respond(false, "You can only edit your own posts.");
    }

    // ── Handle photo ──
    $imagePath = $row['imagePath']; // keep existing by default

    // User clicked Remove Photo
    if ($removePhoto) {
        $imagePath = null;
    }

    // User uploaded a new photo
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES["image"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => "File exceeds server upload size limit.",
                UPLOAD_ERR_FORM_SIZE  => "File exceeds form size limit.",
                UPLOAD_ERR_PARTIAL    => "File was only partially uploaded.",
                UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
                UPLOAD_ERR_EXTENSION  => "Upload blocked by server extension.",
            ];
            http_response_code(422);
            respond(false, $uploadErrors[$file["error"]] ?? "Unknown upload error.");
        }

        if ($file["size"] > $maxBytes) {
            http_response_code(422);
            respond(false, "Image must be 5 MB or smaller.");
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file["tmp_name"]);

        if (!array_key_exists($mimeType, $allowedTypes)) {
            http_response_code(422);
            respond(false, "Invalid image type. Allowed: JPEG, PNG, GIF, WebP.");
        }

        $ext      = $allowedTypes[$mimeType];
        $filename = bin2hex(random_bytes(16)) . "." . $ext;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $dest = $uploadDir . $filename;
        if (!move_uploaded_file($file["tmp_name"], $dest)) {
            http_response_code(500);
            respond(false, "Server error: could not save the uploaded file.");
        }

        $imagePath = $uploadUri . $filename;
    }

    // ── Run the UPDATE ──
    $stmt = $db->prepare(
        "UPDATE $table
         SET postType  = :postType,
             category  = :category,
             postTitle = :postTitle,
             postData  = :postData,
             contact   = :contact,
             imagePath = :imagePath,
             fulfilled = FALSE
         WHERE postID = :postID"
    );
    $stmt->execute([
        ":postType"  => $postType,
        ":category"  => $category,
        ":postTitle" => $postTitle,
        ":postData"  => $postData,
        ":contact"   => $contact !== "" ? $contact : null,
        ":imagePath" => $imagePath,
        ":postID"    => $postID,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}

respond(true, "Post updated successfully.", ["postID" => $postID]);