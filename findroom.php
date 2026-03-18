
<?php
include("db.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Find Rooms - LandLink</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:#f5f6fa;}

/* Navbar */
nav{display:flex;justify-content:space-between;padding:15px 40px;background:rgba(168, 202, 149, 0.15);backdrop-filter: blur(15px);box-shadow:0 2px 8px rgba(0,0,0,0.1);}
.logo{font-size:1.5rem;font-weight:700;color:#2563eb;}
nav ul{display:flex;gap:20px;list-style:none;}
nav a{text-decoration:none;color:#333;font-weight:500;}
nav a:hover{color:#2563eb;}

/* Search Filter */
.search-box{
  background:#fff;
  padding:20px;
  margin:30px;
  border-radius:12px;
  display:flex;
  gap:15px;
  flex-wrap:wrap;
  box-shadow:0 4px 10px rgba(0,0,0,0.1);
}
.search-box select, .search-box input{
  padding:10px;
  border-radius:8px;
  border:1px solid #ddd;
}
.search-box button{
  background:#2563eb;
  color:#fff;
  border:none;
  padding:10px 20px;
  border-radius:8px;
  cursor:pointer;
}

/* Room Cards */
.rooms{
  padding:20px 40px;
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
  gap:20px;
}
.card{
  background:#fff;
  border-radius:12px;
  overflow:hidden;
  box-shadow:0 4px 10px rgba(0,0,0,0.1);
  transition:0.3s;
}
.card:hover{transform:translateY(-5px);}
.card img{width:100%;height:180px;object-fit:cover;}
.card .content{padding:15px;}
.card h3{color:#2563eb;margin-bottom:8px;}
.price{color:green;font-weight:bold;}
</style>
</head>

<body>

<!-- Navbar -->
<nav>
  <div class="logo">LandLink</div>
  <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="findroom.php">Find Room</a></li>
    <li><a href="post-room.php">Post Room</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<!-- Search Filters -->
<form method="GET" class="search-box">
  <select name="city">
    <option value="">City</option>
    <option value="Lucknow">Lucknow</option>
    <option value="Kanpur">Kanpur</option>
  </select>

  <select name="type">
    <option value="">Room Type</option>
    <option value="1BHK">1BHK</option>
    <option value="2BHK">2BHK</option>
    <option value="Shared">Shared</option>
  </select>

  <input type="number" name="budget" placeholder="Max Budget">

  <button type="submit">Search</button>
</form>

<!-- Room Results -->
<div class="rooms">

<?php

// Default query
$query = "SELECT * FROM rooms WHERE 1";

// Filters apply
if(isset($_GET['city']) && $_GET['city'] != ""){
  $city = $_GET['city'];
  $query .= " AND city='$city'";
}

if(isset($_GET['type']) && $_GET['type'] != ""){
  $type = $_GET['type'];
  $query .= " AND type='$type'";
}

if(isset($_GET['budget']) && $_GET['budget'] != ""){
  $budget = $_GET['budget'];
  $query .= " AND price <= $budget";
}

$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)){
?>

<div class="card">
  <img src="<?php echo $row['image']; ?>" alt="Room">
  <div class="content">
    <h3><?php echo $row['title']; ?></h3>
    <p><?php echo $row['city']; ?></p>
    <p class="price">₹<?php echo $row['price']; ?>/month</p>
  </div>
</div>

<?php } ?>

</div>

</body>
</html>