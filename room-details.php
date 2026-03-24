<?php
include("db.php");

$id = $_GET['id'];

$query = "SELECT * FROM rooms WHERE id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<title>Room Details</title>
<style>
body{font-family:Poppins; background:#f1f5f9; padding:30px;}
.container{max-width:800px; margin:auto; background:#fff; padding:20px; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.1);}
img{width:100%; height:300px; object-fit:cover; border-radius:10px;}
video{width:100%; margin-top:15px; border-radius:10px;}
h2{color:#2563eb;}
.price{color:green; font-weight:bold;}
</style>
</head>

<body>

<div class="container">
  <img src="uploads/<?php echo $row['image']; ?>" alt="Room">

  <h2><?php echo $row['title']; ?></h2>
  <p>📍 <?php echo $row['city']; ?></p>
  <p>🛏️ <?php echo $row['room_type']; ?> Beds</p>
  <p><?php echo $row['description']; ?></p>

  <p class="price">₹<?php echo $row['price']; ?>/month</p>
  <p style="margin-top:10px; font-weight:600;">
📞 Contact: 
<a href="tel:<?php echo $row['contact_no']; ?>" style="color:green;">
    <?php echo $row['contact_no']; ?>
</a>
</p>
<a href="https://wa.me/<?php echo $row['contact_no']; ?>" target="_blank">
    <button style="margin-top:10px;">💬 WhatsApp</button>
</a>

  <!-- Video (agar DB me ho) -->
  <?php if(!empty($row['video'])){ ?>
  <video controls style="width:100%; margin-top:15px; border-radius:10px;">
    <source src="uploads/<?php echo $row['video']; ?>" type="video/mp4">
  </video>
<?php } ?>

</div>

</body>
</html>