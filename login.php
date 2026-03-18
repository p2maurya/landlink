<?php
session_start();
include("db.php");

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check credentials
    $query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user['password'])) { // Assuming password hashed
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo "<script>window.location='dashboard.php';</script>";
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body {
    font-family: Arial, sans-serif;
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

.forgot {
    display: block;
    margin-top: 10px;
    color: #fff;
    text-decoration: underline;
    font-size: 0.9em;
    cursor: pointer;
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
</style>
</head>
<body>

<div class="container">
    <h2>🔒 Login</h2>

    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">

        <input type="text" name="username" placeholder="Username or Email" required>

        <input type="password" name="password" id="password" placeholder="Password" required>
        <span class="show-password" onclick="togglePassword()">Show</span>

        <button name="login">Login</button>
        <a class="forgot" href="#">Forgot Password?</a>
    </form>
</div>

<script>
function togglePassword(){
    const pass = document.getElementById('password');
    if(pass.type === "password"){
        pass.type = "text";
    } else {
        pass.type = "password";
    }
}
</script>

</body>
</html>