<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

/* FETCH OLD DATA */
$result = mysqli_query($conn, "SELECT * FROM rooms WHERE id=$id AND user_id=".$_SESSION['user_id']);$row = mysqli_fetch_assoc($result);

/* UPDATE LOGIC */
if(isset($_POST['update'])){
    $title = $_POST['title'];
    $city = $_POST['city'];
    $price = $_POST['price'];
    $type = $_POST['room_type'];
    $desc = $_POST['description'];
    $contact = $_POST['contact_no'];
    $image = $row['image']; // Old image (for now, we won't change it)
    $video = $row['video']; // Old video

    mysqli_query($conn, "
        UPDATE rooms SET
        title='$title',
        city='$city',
        price='$price',
        room_type='$type',
        description='$desc',
        contact_no='$contact',
        image='$image',
        video='$video'
        WHERE id=$id

    ");

    echo "<script>alert('Updated Successfully!'); window.location='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Room</title>
<style>
body{font-family:Poppins;background:#0f172a;color:white;padding:20px;}
form{max-width:400px;margin:auto;}
input,textarea{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:none;
    border-radius:8px;
}
button{
    padding:10px;
    background:#2563eb;
    border:none;
    color:white;
    border-radius:8px;
}
</style>
</head>

<body>

<h2>Edit Room</h2>

<form method="POST">

<input type="text" name="title" value="<?php echo $row['title']; ?>" required>

<input type="text" name="city" value="<?php echo $row['city']; ?>" required>

<input type="number" name="price" value="<?php echo $row['price']; ?>" required>

<input type="text" name="room_type" value="<?php echo $row['room_type']; ?>" required>

<textarea name="description"><?php echo $row['description']; ?></textarea>

<input type="text" name="contact_no" value="<?php echo $row['contact_no']; ?>" required>

<button name="update">Update Room</button>

</form>

</body>
</html>