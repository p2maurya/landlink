<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* JOIN rooms + bookings */
$query = "
SELECT rooms.* 
FROM bookings 
JOIN rooms ON bookings.room_id = rooms.id 
WHERE bookings.user_id = $user_id
ORDER BY bookings.id DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Bookings</title>
<style>
body{
    font-family:Poppins;
    background:#0f172a;
    color:white;
    padding:20px;
}
h1{text-align:center;}

.container{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.card{
    background:#1e293b;
    padding:15px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
}

.card img{
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:10px;
}

.price{color:#22c55e;font-weight:bold;}
</style>
</head>

<body>

<h1>📌 My Bookings</h1>

<div class="container">

<?php
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
?>

<div class="card">
    <img src="uploads/<?php echo $row['image']; ?>">
    <h3><?php echo $row['title']; ?></h3>
    <p><?php echo $row['city']; ?></p>
    <p class="price">₹<?php echo $row['price']; ?>/month</p>
</div>

<?php
    }
}else{
    echo "<p>No bookings yet 😢</p>";
}
?>

</div>

</body>
</html>