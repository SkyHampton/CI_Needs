<?php
$host = "127.0.0.1";
$user = "root";
$password = "Skolekosophy";
$database = "CINeeds";
$userID = $_POST['userID'];

if ($_SERVER["REQUEST_METHOD"]=="POST"){
    try {
        $db = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $sql = "UPDATE CIN_User SET banned = TRUE WHERE userID = (?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userID]);
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage(). "<br/>";
        die();
    }
}
?>