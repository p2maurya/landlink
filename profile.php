<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
include("db.php");

// Fetch user info
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);

// Handle profile update
if(isset($_POST['update_profile'])){
    // Optional profile picture
    if(isset($_FILES['profile']) && $_FILES['profile']['error'] === 0){
        $uploads_dir = "uploads/";
        if(!is_dir($uploads_dir)){
            mkdir($uploads_dir, 0777, true);
        }
        $profile_pic = time() . '_profile_' . basename($_FILES['profile']['name']);
        move_uploaded_file($_FILES['profile']['tmp_name'], $uploads_dir . $profile_pic);

        // Update DB
        mysqli_query($conn, "UPDATE users SET profile_pic='$profile_pic' WHERE id='$user_id'");
        $_SESSION['profile_pic'] = $profile_pic;
        header("Location: profile.php");
        exit;
    }
}

// Handle password change
if(isset($_POST['change_password'])){
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $cnew = $_POST['confirm_password'];

    if(password_verify($current, $user['password'])){
        if($new === $cnew){
            $hash = password_hash($new, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id='$user_id'");
            $msg = "Password updated successfully!";
        } else {
            $msg = "New passwords do not match!";
        }
    } else {
        $msg = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>
<style>
body {
    font-family: Arial;
    background: linear-gradient(135deg, #667eea, #764ba2);
    margin: 0;
    padding: 0;
}

.container {
    max-width: 400px;
    margin: 50px auto;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    padding: 30px;
    border-radius: 20px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    text-align: center;
}

img.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
}

input[type="file"], input[type="password"], button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 10px;
    border: none;
    outline: none;
}

button {
    background: #ff7a18;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #ff5200;
}

.message {
    background: rgba(0,255,0,0.2);
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
}

a {
    color: white;
    text-decoration: underline;
    display: block;
    margin-top: 10px;
}
</style>
</head>
<body>

<div class="container">
    <h2>👤 My Profile</h2>

    <img src="<?php echo $user['profile_pic'] ? 'uploads/'.$user['profile_pic'] : 'https://via.placeholder.com/120'; ?>" class="profile-pic">

    <h3><?php echo $user['username']; ?></h3>
    <p><?php echo $user['email']; ?></p>

    <?php if(isset($msg)) echo "<div class='message'>$msg</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <h4>Update Profile Picture</h4>
        <input type="file" name="profile" accept="image/*" required>
        <button name="update_profile">Update Picture</button>
    </form>

    <form method="POST">
        <h4>Change Password</h4>
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button name="change_password">Change Password</button>
    </form>

    <a href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>