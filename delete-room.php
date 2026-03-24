<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'];

/* CHECK OWNER */
$result = mysqli_query($conn, "SELECT * FROM rooms WHERE id=$id");
$row = mysqli_fetch_assoc($result);

if($row['user_id'] != $user_id){
    echo "Unauthorized!";
    exit;
}

/* DELETE */
mysqli_query($conn, "DELETE FROM rooms WHERE id=$id");

header("Location: dashboard.php");
?>