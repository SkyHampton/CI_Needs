<?php
$host = "137.184.46.194";
$user = "cineedsc_sky";
$password = "N3ph@ndus";
$database = "cineedsc_db";
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
        die();
    }
}
?>