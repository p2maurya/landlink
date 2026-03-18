<?php
include("db.php");

if(isset($_POST['submit'])) {

    // Collect form data safely
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $price = intval($_POST['price']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    $uploads_dir = "uploads/";
    if(!is_dir($uploads_dir)){
        mkdir($uploads_dir, 0777, true);
    }

    // Handle image upload (optional)
    $image_name = NULL;
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $image_name = time() . '_img_' . basename($_FILES['image']['name']);
        $tmp_img = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp_img, $uploads_dir . $image_name);
    }

    // Handle video upload (optional)
    $video_name = NULL;
    if(isset($_FILES['video']) && $_FILES['video']['error'] === 0){
        $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
        if(!in_array($_FILES['video']['type'], $allowed_types)){
            echo "Invalid video format! Only MP4, WebM, OGG allowed.";
            exit;
        }
        $video_name = time() . '_vid_' . basename($_FILES['video']['name']);
        $tmp_vid = $_FILES['video']['tmp_name'];
        move_uploaded_file($tmp_vid, $uploads_dir . $video_name);
    }

    // At least one file required
    if(!$image_name && !$video_name){
        echo "Please upload at least a photo or video!";
        exit;
    }

    // Insert into database
    $query = "INSERT INTO rooms (title, city, room_type, price, description, image)
              VALUES ('$title','$city','$type','$price','$desc','" . ($video_name ?? $image_name) . "')";

    if(mysqli_query($conn, $query)){
        echo "<script>alert('Room posted successfully!'); window.location='post-room.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Post Room</title>
<style>
body {
    font-family: Arial;
    background: linear-gradient(135deg, #667eea, #764ba2);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    padding: 30px;
    border-radius: 20px;
    width: 400px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

input, select, textarea {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border-radius: 10px;
    border: none;
    outline: none;
}

button {
    width: 100%;
    padding: 12px;
    background: #ff7a18;
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #ff5200;
}

img, video {
    max-width: 100%;
    margin-top: 10px;
    border-radius: 10px;
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

        <input type="file" name="image" id="imageInput" accept="image/*">
        <img id="imgPreview" style="display:none;" />

        <input type="file" name="video" id="videoInput" accept="video/*">
        <video id="videoPreview" controls style="display:none;"></video>

        <button name="submit">🚀 Post Room</button>

    </form>
</div>

<script>
// Image preview
const imageInput = document.getElementById('imageInput');
const imgPreview = document.getElementById('imgPreview');
imageInput.addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            imgPreview.src = e.target.result;
            imgPreview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        imgPreview.style.display = 'none';
    }
});

// Video preview
const videoInput = document.getElementById('videoInput');
const videoPreview = document.getElementById('videoPreview');
videoInput.addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        videoPreview.src = URL.createObjectURL(file);
        videoPreview.style.display = 'block';
    } else {
        videoPreview.style.display = 'none';
    }
});
</script>

</body>
</html>