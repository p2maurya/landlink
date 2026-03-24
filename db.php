
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if running on localhost
if ($_SERVER['SERVER_NAME'] == 'localhost') {

    // Localhost DB
    $conn = mysqli_connect(
        "localhost",
        "root",
        "",
        "landlink1.0"
    );

} else {

    // Live Server DB
    $conn = mysqli_connect(
        "sql305.infinityfree.com",
        "if0_41420793",
        "oQLLMCckBV",
        "if0_41420793_epiz_12345678_landlink"
    );
}

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>