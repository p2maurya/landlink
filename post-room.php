<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(isset($_POST['submit'])){

    $user_id = $_SESSION['user_id'];

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $price = intval($_POST['price']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);

    $uploads_dir = "uploads/";
    if(!is_dir($uploads_dir)){
        mkdir($uploads_dir, 0777, true);
    }

    $file_name = "";

    /* IMAGE UPLOAD */
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $file_name = time() . "_img_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploads_dir . $file_name);
    }

    /* VIDEO UPLOAD */
    if(isset($_FILES['video']) && $_FILES['video']['error'] === 0){
        $allowed_types = ['video/mp4','video/webm','video/ogg'];

        if(!in_array($_FILES['video']['type'], $allowed_types)){
            echo "<script>alert('Only MP4/WebM/OGG allowed');</script>";
        } else {
            $file_name = time() . "_vid_" . basename($_FILES['video']['name']);
            move_uploaded_file($_FILES['video']['tmp_name'], $uploads_dir . $file_name);
        }
    }

    if($file_name == ""){
        echo "<script>alert('Please upload image or video');</script>";
    } else {

        $query = "INSERT INTO rooms 
        (title, city, room_type, price, description, contact_no, image, user_id)
        VALUES 
        ('$title','$city','$type','$price','$desc','$contact_no','$file_name','$user_id')";

        if(mysqli_query($conn, $query)){
            echo "<script>alert('🚀 Room Posted Successfully!'); window.location='dashboard.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Post Room</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg,#667eea,#764ba2);
}

/* Container */
.container{
    width:420px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(20px);
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    color:white;
}

/* Title */
h2{
    text-align:center;
    margin-bottom:20px;
    font-weight:600;
}

/* Inputs */
input,select,textarea{
    width:100%;
    padding:12px;
    margin:8px 0;
    border:none;
    border-radius:10px;
    outline:none;
    font-size:0.95rem;
}

/* Button */
button{
    width:100%;
    padding:12px;
    margin-top:10px;
    background:#ff7a18;
    border:none;
    border-radius:10px;
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
button:hover{
    background:#ff5200;
}

/* Preview */
.preview{
    margin-top:10px;
}
.preview img, .preview video{
    width:100%;
    border-radius:10px;
    margin-top:10px;
}

/* Small text */
small{
    opacity:0.8;
}
</style>
</head>

<body>

<div class="container">
<h2>🏠 Post Your Room</h2>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="title" placeholder="Room Title" required>
<input type="text" name="city" placeholder="City" required>

<select name="type" required>
<option value="">Select Room Type</option>
<option>Single</option>
<option>Double</option>
<option>PG</option>
</select>

<input type="number" name="price" placeholder="Price" required>
<textarea name="description" placeholder="Description" required></textarea>

<input type="text" name="contact_no" placeholder="Contact Number" required>

<label>📷 Upload Image</label>
<input type="file" name="image" id="imgInput" accept="image/*">

<label>🎥 Upload Video</label>
<input type="file" name="video" id="vidInput" accept="video/*">

<div class="preview">
<img id="imgPreview" style="display:none;">
<video id="vidPreview" controls style="display:none;"></video>
</div>

<button name="submit">🚀 Post Room</button>

</form>
</div>

<script>
// Image preview
imgInput.onchange = e=>{
    const file = e.target.files[0];
    if(file){
        imgPreview.src = URL.createObjectURL(file);
        imgPreview.style.display="block";
    }
}

// Video preview
vidInput.onchange = e=>{
    const file = e.target.files[0];
    if(file){
        vidPreview.src = URL.createObjectURL(file);
        vidPreview.style.display="block";
    }
}
</script>

</body>
</html>