<?php
session_start();
include("db.php");

/* ---------- ADMIN LOGIN CHECK ---------- */
$admin_user = "admin";
$admin_pass = "12345";

if(isset($_POST['login'])){
    if($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass){
        $_SESSION['admin'] = true;
    } else {
        $error = "Invalid Login!";
    }
}

/* ---------- LOGOUT ---------- */
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: admin_dashboard.php");
}

/* ---------- FETCH DATA ---------- */
if(isset($_SESSION['admin'])){
    $userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
    $roomCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms"))['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
body{
    margin:0;
    font-family:Poppins;
    background: linear-gradient(135deg,#0f172a,#1e293b);
    color:white;
}

/* Center login */
.login-box{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

form{
    background:#1e293b;
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

input{
    display:block;
    margin:10px 0;
    padding:10px;
    width:220px;
    border:none;
    border-radius:8px;
}

button{
    padding:10px;
    width:100%;
    background:#2563eb;
    border:none;
    color:white;
    border-radius:8px;
    cursor:pointer;
}

button:hover{
    background:#1e40af;
}

/* Dashboard */
.dashboard{
    padding:30px;
}

.box{
    background:#334155;
    padding:20px;
    margin:15px 0;
    border-radius:12px;
    font-size:1.2rem;
}

/* Logout */
.logout{
    display:inline-block;
    margin-top:20px;
    color:#f87171;
}
</style>
</head>

<body>

<?php if(!isset($_SESSION['admin'])){ ?>

<!-- LOGIN FORM -->
<div class="login-box">
    <form method="POST">
        <h2>Admin Login</h2>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Login</button>
    </form>
</div>

<?php } else { ?>

<!-- DASHBOARD -->
<div class="dashboard">
    <h1>Welcome Admin 😎</h1>

    <div class="box">👥 Total Users: <?php echo $userCount; ?></div>
    <div class="box">🏠 Total Rooms: <?php echo $roomCount; ?></div>

    <a href="?logout=true" class="logout">Logout</a>
</div>

<?php } ?>

</body>
</html>