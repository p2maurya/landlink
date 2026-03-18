<?php

$conn = mysqli_connect("localhost", "root", "", "landlink1.0", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully";

?>