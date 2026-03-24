<?php
session_start();
include("db.php");

/* ---------------- LIKE ---------------- */
if(isset($_POST['like'])){
    if(isset($_SESSION['user_id'])){
        $uid = (int)$_SESSION['user_id'];
        $rid = (int)$_POST['room_id'];

        mysqli_query($conn, "INSERT IGNORE INTO likes (user_id, room_id) VALUES ($uid,$rid)");

        // Redirect to same page with filters preserved
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
        exit;
    } else {
        header("Location: login.php");
        exit;
    }
}

/* ---------------- RATING ---------------- */
if(isset($_POST['rate'])){
    $uid = (int)$_SESSION['user_id'];
    $rid = (int)$_POST['room_id'];
    $rating = (int)$_POST['rating'];

    mysqli_query($conn, "INSERT INTO ratings (user_id, room_id, rating)
        VALUES ($uid,$rid,$rating)
        ON DUPLICATE KEY UPDATE rating=$rating");

    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    exit;
}

     

/* ---------------- BOOK ---------------- */
if(isset($_POST['book'])){
    if(!isset($_SESSION['user_id'])){
        header("Location: login.php");
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];
    $room_id = (int)$_POST['room_id'];

    mysqli_query($conn, "INSERT INTO bookings (user_id, room_id) VALUES ($user_id, $room_id)");

    echo "<script>alert('Room Booked Successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "';</script>";
    exit;
}

/* ---------------- FILTER ROOMS ---------------- */
$city = isset($_GET['city']) ? mysqli_real_escape_string($conn, strtolower(trim($_GET['city']))) : '';
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, strtolower(trim($_GET['type']))) : '';
$budget = isset($_GET['budget']) ? (int)$_GET['budget'] : 0;

$query = "SELECT * FROM rooms WHERE 1";
if($city != '') $query .= " AND LOWER(city) LIKE '%$city%'";
if($type != '') $query .= " AND (LOWER(type) LIKE '%$type%' OR LOWER(title) LIKE '%$type%' OR type IS NULL)";
if($budget > 0) $query .= " AND price <= $budget";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Find Rooms - LandLink</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background: linear-gradient(135deg,#dbeafe,#f8fafc);}

/* Navbar */
nav{display:flex;justify-content:space-between;padding:15px 40px;background:rgba(255,255,255,0.7);backdrop-filter:blur(10px);box-shadow:0 5px 20px rgba(0,0,0,0.1);}
.logo{font-size:1.7rem;font-weight:700;color:#2563eb;}
nav ul{display:flex; gap:25px; list-style:none;}
nav a{text-decoration:none;color:#333;font-weight:500;}
nav a:hover{color:#2563eb;}

/* Search */
.search-box{background:rgba(255,255,255,0.9);padding:20px;margin:30px auto;border-radius:15px;display:flex;gap:15px;flex-wrap:wrap;justify-content:center;box-shadow:0 10px 30px rgba(0,0,0,0.1);max-width:900px;}
.search-box select,input{padding:12px;border-radius:10px;border:1px solid #ddd;min-width:150px;transition:0.3s;}
.search-box select:focus,input:focus{outline:none;border-color:#2563eb;}
.search-box button{background:#2563eb;color:#fff;border:none;padding:12px 25px;border-radius:10px;cursor:pointer;font-weight:600;transition:0.3s;}
.search-box button:hover{background:#1e40af;transform:scale(1.05);}

/* Rooms Grid */
.rooms{padding:20px 40px;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:25px;}

/* Card */
.card{background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.1);transition:0.3s;cursor:pointer;}
.card:hover{transform:translateY(-8px) scale(1.02);}
.card img,.card video{width:100%;height:180px;object-fit:cover;}
.content{padding:15px;}
.content h3{color:#2563eb;margin-bottom:8px;}
.content p{margin-bottom:5px;}
.price{color:#16a34a;font-weight:700;}
.actions{display:flex; gap:10px; margin-top:10px;}
.actions form{margin:0;}
.actions button{padding:5px 10px; border:none; border-radius:5px; cursor:pointer; transition:0.2s;}
.actions button:hover{opacity:0.8;}

/* No Result */
.no-result{text-align:center;font-size:1.2rem;color:#555;margin-top:40px;}
</style>
</head>
<body>

<nav>
  <div class="logo">LandLink</div>
  <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="my-bookings.php">My Bookings</a></li>
    <li><a href="post-room.php">Post Room</a></li>
    <li><a href="login.php">Login</a></li>
    <li><a href="dashboard.php">Dashboard</a></li>
  </ul>
</nav>

<form method="GET" class="search-box">
  <select name="city">
    <option value="">City</option>
    <option>Lucknow</option>
    <option>Kanpur</option>
    <option>Varanasi</option>
    <option>Allahabad</option>
    <option>Gorakhpur</option>
    <option>Agra</option>
    <option>Meerut</option>
    <option>Jhansi</option>
    <option>Prayagraj</option>
    <option>Mirzapur</option>
  </select>

  <select name="type">
    <option value="">Room Type</option>
    <option value="single">Single</option>
    <option value="double">Double</option>
    <option value="shared">Shared</option>
  </select>

  <input type="number" name="budget" placeholder="Max Budget">
  <button type="submit">🔍 Search</button>
</form>

<div class="rooms">
<?php
if(mysqli_num_rows($result) > 0){
  while($row = mysqli_fetch_assoc($result)):

    $room_id = $row['id'];
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    // Fetch existing rating
    $existing_rating = 0;
    if($user_id){
        $res = mysqli_query($conn, "SELECT rating FROM ratings WHERE user_id=$user_id AND room_id=$room_id");
        if(mysqli_num_rows($res) > 0){
            $existing_rating = (int)mysqli_fetch_assoc($res)['rating'];
        }
    }

    // Fetch like status
    $liked = false;
    if($user_id){
        $res_like = mysqli_query($conn, "SELECT * FROM likes WHERE user_id=$user_id AND room_id=$room_id");
        if(mysqli_num_rows($res_like) > 0){
            $liked = true;
        }
    }
?>
<div class="card" onclick="window.location='room-details.php?id=<?php echo $room_id; ?>'">

    <?php if(!empty($row['image'])){ ?>
      <img src="uploads/<?php echo $row['image']; ?>" alt="Room">
    <?php } ?>

    <?php if(!empty($row['video'])){ ?>
      <video muted>
        <source src="uploads/<?php echo $row['video']; ?>" type="video/mp4">
      </video>
    <?php } ?>

    <div class="content">
      <h3><?php echo $row['title']; ?></h3>
      <p>📍 <?php echo $row['city']; ?></p>
      <p class="price">₹<?php echo $row['price']; ?>/month</p>

      <!-- BOOK -->
      <form method="POST" onclick="event.stopPropagation();">
        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
        <button name="book">Book Now</button>
      </form>

      <div class="actions">
        <!-- LIKE -->
        <form method="POST" onclick="event.stopPropagation();">
          <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
          <button name="like" style="background:#ff4757;"><?php echo $liked ? '❤️' : '🤍'; ?></button>
        </form>

        <!-- RATING -->
        <form method="POST" onclick="event.stopPropagation();">
          <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
          <select name="rating">
            <option value="1" <?php if($existing_rating==1) echo "selected"; ?>>1⭐</option>
            <option value="2" <?php if($existing_rating==2) echo "selected"; ?>>2⭐</option>
            <option value="3" <?php if($existing_rating==3) echo "selected"; ?>>3⭐</option>
            <option value="4" <?php if($existing_rating==4) echo "selected"; ?>>4⭐</option>
            <option value="5" <?php if($existing_rating==5) echo "selected"; ?>>5⭐</option>
          </select>
          <button name="rate" style="background:#ffa502;">⭐</button>
          
        </form>
      </div>
    </div>
</div>
<?php
  endwhile;
} else {
  echo "<p class='no-result'>No rooms found 😔</p>";
}
?>
</div>