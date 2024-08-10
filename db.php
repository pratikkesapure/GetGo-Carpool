<?php
// MySQL database credentials
$servername = "localhost"; // Change this if your database is hosted elsewhere
$username = "pratik"; // Your MySQL username
$password = "kesapure"; // Your MySQL password
$database = "car"; // Your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} //else {
    //echo "Connected successfully";
    // You can execute your queries here
//}

// Close connection
//$conn->close();
?>
