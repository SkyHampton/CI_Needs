<?php
$ini_arr = parse_ini_file("ini/dbcred.ini");
$user = $ini_arr["USER"];
$password = $ini_arr["PASS"];
$host = "127.0.0.1";
$database = "CINeeds";
$replyData = $_POST['replyData'];
$postID = $_POST['postID'];
$userID = $_POST['userID'];

if ($_SERVER["REQUEST_METHOD"]=="POST"){
    try {
        $db = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $sql = "INSERT INTO CIN_Reply (postID, userID, replyData, replyDate) VALUES (?,?,?,?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$postID, $userID, $replyData, date('Y-m-d')]);
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage(). "<br/>";
        http_response_code(500);
        die();
    }
}

http_response_code(201);
?>