<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
include("db.php");

// Fetch rooms
$result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<style>
body {
    font-family: Arial;
    background: linear-gradient(135deg, #667eea, #764ba2);
    margin: 0;
    padding: 0;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    padding: 15px 30px;
    color: white;
}

.navbar a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
    font-weight: bold;
}

.container {
    padding: 20px;
}

h1 {
    color: white;
}

.room-card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 15px;
    margin: 15px 0;
    border-radius: 15px;
    color: white;
}

.room-card img, .room-card video {
    max-width: 100%;
    border-radius: 10px;
}

button {
    padding: 10px 20px;
    background: #ff7a18;
    border: none;
    border-radius: 10px;
    color: white;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background: #ff5200;
}
</style>
</head>
<body>

<div class="navbar">
    <div>
        Welcome, <?php echo $_SESSION['username']; ?>
    </div>
    <div>
        <a href="index.php">Home</a>
        <a href="post-room.php">Post Room</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h1>Available Rooms</h1>

    <?php
    if(mysqli_num_rows($result) > 0){
        while($room = mysqli_fetch_assoc($result)){
            echo "<div class='room-card'>";
            echo "<h3>".$room['title']." - ".$room['city']."</h3>";
            if(str_contains($room['image'], '.mp4') || str_contains($room['image'], '.webm') || str_contains($room['image'], '.ogg')){
                echo "<video src='uploads/".$room['image']."' controls></video>";
            } else {
                echo "<img src='uploads/".$room['image']."' />";
            }
            echo "<p>Type: ".$room['room_type']." | Price: ₹".$room['price']."</p>";
            echo "<p>".$room['description']."</p>";
            echo "</div>";
        }
    } else {
        echo "<p style='color:white;'>No rooms posted yet.</p>";
    }
    ?>
</div>

</body>
</html>