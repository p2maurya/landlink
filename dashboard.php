<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

include("db.php");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Fetch only rooms of the logged-in user for dashboard
$user_id = (int)$_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM rooms WHERE user_id=$user_id ORDER BY id DESC");

if(!$result){
    die("Query Failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
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
    padding: 20px 40px;
}

h1 {
    color: white;
    margin-bottom: 20px;
}

/* Grid for rooms */
.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}

/* Card */
.room-card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 15px;
    border-radius: 15px;
    color: white;
    transition: transform 0.3s ease;
}

.room-card:hover {
    transform: translateY(-5px) scale(1.02);
}

.room-card img,
.room-card video {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 10px;
}

.room-card h3 {
    margin: 10px 0 5px;
    font-size: 1.2rem;
}

.room-card p {
    margin: 5px 0;
    font-size: 0.95rem;
}

.room-card .actions {
    margin-top: 10px;
}

button {
    padding: 8px 15px;
    margin-top: 10px;
    margin-right: 10px;
    background: #ff7a18;
    border: none;
    border-radius: 10px;
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
}

button:hover {
    background: #ff5200;
}

button.delete-btn {
    background: red;
}

button.delete-btn:hover {
    background: darkred;
}
</style>
</head>
<body>

<div class="navbar">
    <div>Welcome, <?php echo $_SESSION['username']; ?></div>
    <div>
        <a href="index.php">Home</a>
        <a href="findroom.php">Find Room</a>
        <a href="post-room.php">Post Room</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
<h1>My Rooms</h1>

<div class="rooms-grid">
<?php
if(mysqli_num_rows($result) > 0){
    while($room = mysqli_fetch_assoc($result)):
        $room_media = !empty($room['video']) ? $room['video'] : $room['image'];
?>
<div class="room-card" onclick="window.location='room-details.php?id=<?php echo $room['id']; ?>'">

    <h3><?php echo $room['title']; ?> - <?php echo $room['city']; ?></h3>

    <?php if(!empty($room['video'])){ ?>
        <video src="uploads/<?php echo $room['video']; ?>" controls></video>
    <?php } elseif(!empty($room['image'])) { ?>
        <img src="uploads/<?php echo $room['image']; ?>" alt="Room Image">
    <?php } ?>

    <p>Type: <?php echo $room['room_type']; ?> | Price: ₹<?php echo $room['price']; ?></p>
    <p><?php echo $room['description']; ?></p>
    <p>📞 Contact: <?php echo $room['contact_no']; ?></p>

    <div class="actions">
        <!-- STOP PROPAGATION so clicking buttons won't open details -->
        <a href="edit-room.php?id=<?php echo $room['id']; ?>" onclick="event.stopPropagation();">
            <button>Edit</button>
        </a>
        <a href="delete-room.php?id=<?php echo $room['id']; ?>" onclick="event.stopPropagation(); return confirm('Delete this room?');">
            <button class="delete-btn">Delete</button>
        </a>
    </div>

</div>
<?php
    endwhile;
} else {
    echo "<p style='color:white;'>You have not posted any rooms yet.</p>";
}
?>
</div>
</div>

</body>
</html>