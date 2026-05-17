// DB connection
<?php
$host = "137.184.46.194";
$user = "cineedsc_sky";
$password = "N3ph@ndus";
$database = "cineedsc_db";
$postID = $_POST['postID'];

if ($_SERVER["REQUEST_METHOD"]=="POST"){
    try {
        $db = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $sql = "CALL fulfill_post(?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$postID]);
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage(). "<br/>";
        http_response_code(500);
        die();
    }
}

http_response_code(201);
?>