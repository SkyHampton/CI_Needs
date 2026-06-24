<?php
$host = "127.0.0.1";
$user = "root";
$password = "Skolekosophy";
$database = "CINeeds";
// $postID = $_POST['postID'];

// if ($_SERVER["REQUEST_METHOD"]=="POST"){
//     try {
//         $db = new PDO("mysql:host=$host;dbname=$database", $user, $password);
//         $sql = "CALL fulfill_post(?)";
//         $stmt = $db->prepare($sql);
//         $stmt->execute([$postID]);
//     } catch (PDOException $e) {
//         print "Error!: " . $e->getMessage(). "<br/>";
//         http_response_code(500);
//         die();
//     }
// }

// http_response_code(201);


session_start();

$postID = $_POST['postID'] ?? null;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Method not allowed");
}

if (!$postID) {
    die("Invalid request");
}

try {

    $db = new PDO(
        "mysql:host=$host;dbname=$database",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    /* Check post exists */
    $checkPost = $db->prepare("
        SELECT userID, fulfilled
        FROM CIN_Post
        WHERE postID = ?
    ");

    $checkPost->execute([$postID]);

    $post = $checkPost->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        die("Post not found");
    }

    /* Prevent duplicate fulfillment */
    if ($post['fulfilled']) {
        die("Post already fulfilled");
    }

    /* Current logged-in user */
    $userID = $_SESSION['userID'] ?? null;

    if (!$userID) {
        die("Please login first");
    }

    /* Owner check */
    $isOwner = ($post['userID'] == $userID);

    /* Admin check */
    $adminStmt = $db->prepare("
        SELECT admin
        FROM CIN_User
        WHERE userID = ?
    ");

    $adminStmt->execute([$userID]);

    $adminData = $adminStmt->fetch(PDO::FETCH_ASSOC);

    $isAdmin = false;

    if ($adminData) {
        $isAdmin = (bool)$adminData['admin'];
    }

    /* Authorization */
    if (!$isOwner && !$isAdmin) {
        die("Unauthorized action");
    }

    /* Keep teammate implementation */
    $sql = "CALL fulfill_post(?)";

    $stmt = $db->prepare($sql);

    $stmt->execute([$postID]);

    http_response_code(200);

    echo "Post marked as fulfilled!";

} catch (PDOException $e) {

    print "Error!: " . $e->getMessage();

    http_response_code(500);

    die();
}
?>