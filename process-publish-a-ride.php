<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db.php';

// Retrieve form data
$leavingFrom = $_POST['leavingFrom'];
$goingTo = $_POST['goingTo'];
$date = $_POST['date'];
$time = $_POST['time'];
$seats = $_POST['seats'];
$fare = $_POST['fare']; // Get the fare amount from the form
$userId = $_SESSION['user_id'];

// Calculate the distance using Google Maps Distance Matrix API
$origin = str_replace(' ', '+', $leavingFrom);
$destination = str_replace(' ', '+', $goingTo);
$apiUrl = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origin&destinations=$destination&key=AIzaSyDfUqgPfFa4Aoez2fQln-FOfjfaFIVAE-4";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if ($data['status'] == 'OK') {
    $distance = $data['rows'][0]['elements'][0]['distance']['text'];
    $distanceValue = $data['rows'][0]['elements'][0]['distance']['value'] / 1000; // Convert meters to kilometers
} else {
    $distance = 'N/A';
    $distanceValue = 0;
}

// Prepare and execute SQL query
$stmt = $conn->prepare("INSERT INTO rides (leaving_from, going_to, date, time, seats, user_id, fare, distance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiidd", $leavingFrom, $goingTo, $date, $time, $seats, $userId, $fare, $distanceValue);

if ($stmt->execute()) {
    $success_message = "Ride published successfully!";
} else {
    $error_message = "Error publishing ride: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

<!-- Rest of the code remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Publish a Ride</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <!-- ... (Same as in publish-a-ride.php) -->
</head>
<body>
    <div class="content-wrapper">
        <!-- Navigation Bar -->
        <!-- ... (Same as in publish-a-ride.php) -->
    </div>

    <!-- Success or Error Message -->
    <?php if (isset($success_message)) { ?>
        <div class="alert alert-success mx-auto mt-5" role="alert" style="max-width: 600px;">
            <?php echo $success_message; ?>
        </div>
    <?php } elseif (isset($error_message)) { ?>
        <div class="alert alert-danger mx-auto mt-5" role="alert" style="max-width: 600px;">
            <?php echo $error_message; ?>
        </div>
    <?php } ?>

    <!-- Bottom Section -->
    <!-- ... (Same as in publish-a-ride.php) -->

    <!-- Footer -->
    <!-- ... (Same as in publish-a-ride.php) -->

    <!-- Bootstrap JS -->
    <!-- ... (Same as in publish-a-ride.php) -->
</body>
</html>