<?php
include("db.php");

if(isset($_POST['signup'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Check passwords match
    if($password !== $cpassword){
        $error = "Passwords do not match!";
    } else {
        // Hash the password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Optional profile picture upload
        $profile_pic = NULL;
        if(isset($_FILES['profile']) && $_FILES['profile']['error'] === 0){
            $uploads_dir = "uploads/";
            if(!is_dir($uploads_dir)){
                mkdir($uploads_dir, 0777, true);
            }
            $profile_pic = time() . '_profile_' . basename($_FILES['profile']['name']);
            move_uploaded_file($_FILES['profile']['tmp_name'], $uploads_dir . $profile_pic);
        }

        // Check if username/email exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email'");
        if(mysqli_num_rows($check) > 0){
            $error = "Username or Email already exists!";
        } else {
            // Insert new user
            $query = "INSERT INTO users (username, email, password, profile_pic) 
                      VALUES ('$username','$email','$hash','$profile_pic')";
            if(mysqli_query($conn, $query)){
                echo "<script>alert('Signup successful! Please login.'); window.location='login.php';</script>";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Signup</title>
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
    padding: 40px;
    border-radius: 20px;
    width: 350px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    text-align: center;
}

h2 {
    margin-bottom: 30px;
}

input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
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
    margin-top: 10px;
    transition: 0.3s;
}

button:hover {
    background: #ff5200;
}

.error {
    background: rgba(255,0,0,0.2);
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.show-password {
    font-size: 0.8em;
    color: #fff;
    cursor: pointer;
    float: right;
    margin-top: -30px;
    margin-right: 10px;
    position: relative;
}

.profile-preview {
    max-width: 100px;
    margin: 10px auto;
    display: block;
    border-radius: 50%;
}
</style>
</head>
<body>

<div class="container">
    <h2>📝 Signup</h2>

    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="username" placeholder="Username" required>

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" id="password" placeholder="Password" required>
        <span class="show-password" onclick="togglePassword('password')">Show</span>

        <input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password" required>
        <span class="show-password" onclick="togglePassword('cpassword')">Show</span>

        <input type="file" name="profile" id="profileInput" accept="image/*">
        <img id="profilePreview" class="profile-preview" style="display:none;" />

        <button name="signup">Signup</button>
    </form>

    <p style="margin-top:10px;">Already have an account? <a href="login.php" style="color:#fff;text-decoration:underline;">Login</a></p>
</div>

<script>
// Show/Hide password
function togglePassword(id){
    const pass = document.getElementById(id);
    pass.type = pass.type === "password" ? "text" : "password";
}

// Profile preview
const profileInput = document.getElementById('profileInput');
const profilePreview = document.getElementById('profilePreview');
profileInput.addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            profilePreview.src = e.target.result;
            profilePreview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        profilePreview.style.display = 'none';
    }
});
</script>

</body>
</html>