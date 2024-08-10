<?php
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Find a Ride</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Result Section -->
    <div class="container my-5" style="height:100vh;">

        <?php
        // Database connection
        include 'db.php';

        // Retrieve form data
        $leavingFrom = $_POST['leavingFrom'];
        $goingTo = $_POST['goingTo'];
        $date = $_POST['date'];
        $passengers = $_POST['passengers'];

        // After retrieving other form data
        $pickupLocation = $_POST['leavingFrom'];
        $dropoffLocation = $_POST['goingTo'];

        // Store the locations in session variables
        $_SESSION['pickup_location'] = $pickupLocation;
        $_SESSION['dropoff_location'] = $dropoffLocation;
        

        // Prepare and execute the SQL query without LIKE operator
        $stmt = $conn->prepare("SELECT r.*, u.firstName, u.lastName, r.leaving_from AS ride_source, r.going_to AS ride_destination
                                FROM rides r
                                JOIN users u ON r.user_id = u.id
                                WHERE r.date = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();

        $exceededDistanceCount = 0;

        if ($result->num_rows > 0) {
            echo "<h2>Available Rides</h2>";
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Source Location</th><th>Destination Location</th><th>Date</th><th>Available Seats</th><th>Driver</th><th>Fare</th><th>Action</th></tr></thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                $availableSeats = $row['seats'] - $row['booked_seats'];
                $origin = str_replace(' ', '+', $leavingFrom);
                $destination = str_replace(' ', '+', $goingTo);
                $rideSource = str_replace(' ', '+', $row['ride_source']);
                $rideDestination = str_replace(' ', '+', $row['ride_destination']);

                // Calculate distances using Google Maps Distance Matrix API
                $apiUrl = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$rideSource|$origin|$destination&destinations=$origin|$destination|$rideDestination&key=AIzaSyDfUqgPfFa4Aoez2fQln-FOfjfaFIVAE-4";
                $response = file_get_contents($apiUrl);
                $data = json_decode($response, true);

                if ($data['status'] == 'OK') {
                    // Check if the ride source is the same as the origin
                    if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
                        $c_to_a = $data['rows'][0]['elements'][0]['distance']['value'] / 1000; // Ride source to origin
                    } else {
                        $c_to_a = 0; // Set distance to 0 if ride source and origin are the same
                    }

                    // Check if the origin is the same as the destination
                    if (isset($data['rows'][1]['elements'][1]['distance']['value'])) {
                        $a_to_b = $data['rows'][1]['elements'][1]['distance']['value'] / 1000; // Origin to destination
                    } else {
                        $a_to_b = 0; // Set distance to 0 if origin and destination are the same
                    }

                    // Check if the destination is the same as the ride destination
                    if (isset($data['rows'][2]['elements'][2]['distance']['value'])) {
                        $b_to_d = $data['rows'][2]['elements'][2]['distance']['value'] / 1000; // Destination to ride destination
                    } else {
                        $b_to_d = 0; // Set distance to 0 if destination and ride destination are the same
                    }

                    $a_to_b_direct = $row['distance']; // Direct distance from ride source to ride destination

                    $totalDistance = $c_to_a + $a_to_b + $b_to_d;
                    $maxAllowedDistance = $a_to_b_direct + ($a_to_b_direct * 0.2); // 20% more than direct distance

                    // Store the distance variables in the session
                    $_SESSION['a_to_b'] = $a_to_b;
                    $_SESSION['a_to_b_direct'] = $a_to_b_direct;

                    if ($totalDistance <= $maxAllowedDistance && $a_to_b >= 0.5 * $a_to_b_direct) {
                        $exceededDistanceCount++;
                        echo "<tr>";
                        echo "<td>" . $row['ride_source'] . "</td>";
                        echo "<td>" . $row['ride_destination'] . "</td>";
                        echo "<td>" . $row['date'] . "</td>";
                        echo "<td>" . $availableSeats . "</td>";
                        echo "<td>" . $row['firstName'] . " " . $row['lastName'] . "</td>"; // Display driver name
                        echo "<td>₹" . $row['fare'] . "</td>"; // Display fare
                        if ($availableSeats > 0) {
                            echo "<td><a href='book-ride.php?ride_id=" . $row['id'] . "' class='btn btn-primary'>Book</a></td>";
                        } else {
                            echo "<td>All seats booked</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<p>Failed to calculate distances. Please try again.</p>";
                }
            }
            echo "</tbody>";
            echo "</table>";

            if ($exceededDistanceCount == 0) {
                echo "<p>No rides found matching your search criteria.</p>";
            }
            


        } else {
            echo "<p>No rides found matching your search criteria.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>

    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- ... (Same as in find-a-ride.php) -->
</body>
</html>