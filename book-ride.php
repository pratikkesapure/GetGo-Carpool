<?php
include 'navbar.php';

// Database connection
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Retrieve ride details from the database
$rideId = $_GET['ride_id'];
$rideId2 = $rideId;
$stmt = $conn->prepare("SELECT r.*, u.firstName AS driverFirstName, u.lastName AS driverLastName, u.email AS driverEmail
                        FROM rides r
                        JOIN users u ON r.user_id = u.id
                        WHERE r.id = ?");
$stmt->bind_param("i", $rideId);
$stmt->execute();
$result = $stmt->get_result();
$ride = $result->fetch_assoc();

// Retrieve pickup and drop-off locations from session
$pickupLocation = isset($_SESSION['pickup_location']) ? $_SESSION['pickup_location'] : '';
$dropoffLocation = isset($_SESSION['dropoff_location']) ? $_SESSION['dropoff_location'] : '';

// Retrieve distance variables from the session
$a_to_b = isset($_SESSION['a_to_b']) ? $_SESSION['a_to_b'] : 0;
$a_to_b_direct = isset($_SESSION['a_to_b_direct']) ? $_SESSION['a_to_b_direct'] : 0;

// Calculate the fare per seat based on the distance and original fare
$farePerSeat = calculateFare($a_to_b, $a_to_b_direct, $ride['fare']);

// Check if the user has already booked the ride
$userId = $_SESSION['user_id'];
$checkBookingStmt = $conn->prepare("SELECT * FROM bookings WHERE ride_id = ? AND user_id = ?");
$checkBookingStmt->bind_param("ii", $rideId, $userId);
$checkBookingStmt->execute();
$existingBooking = $checkBookingStmt->get_result()->fetch_assoc();
$checkBookingStmt->close();

if ($existingBooking) {
    $error_message = "You have already booked this ride.";
    $totalFare = $existingBooking['fare'];
} else {
    // Check if there are enough seats available
    $availableSeats = $ride['seats'] - $ride['booked_seats'];
    if ($availableSeats <= 0) {
        $error_message = "Sorry, all seats for this ride have been booked.";
    } else {
        $totalFare = 0; // Initialize total fare

        // If the form is submitted, insert the booking into the database
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if the 'seats' key exists in the $_POST array
            if (isset($_POST['seats'])) {
                $bookedSeats = $_POST['seats'];

                // Check if the requested seats are available
                if ($bookedSeats <= $availableSeats) {
                    $totalFare = $bookedSeats * $farePerSeat;

                    $bookingStmt = $conn->prepare("INSERT INTO bookings (ride_id, user_id, seats_booked, fare) VALUES (?, ?, ?, ?)");
                    $bookingStmt->bind_param("iiid", $rideId, $userId, $bookedSeats, $totalFare);
                    if ($bookingStmt->execute()) {
                        // Get the inserted booking ID
                        $bookingId = $conn->insert_id;

                        // Insert the passenger locations
                        $insertLocationStmt = $conn->prepare("INSERT INTO passenger_locations (booking_id, passenger_pickup, passenger_dropoff) VALUES (?, ?, ?)");
                        $insertLocationStmt->bind_param("iss", $bookingId, $pickupLocation, $dropoffLocation);
                        $insertLocationStmt->execute();
                        $insertLocationStmt->close();

                        // Update the booked_seats column in the rides table
                        $updateRideStmt = $conn->prepare("UPDATE rides SET booked_seats = booked_seats + ? WHERE id = ?");
                        $updateRideStmt->bind_param("ii", $bookedSeats, $rideId);
                        $updateRideStmt->execute();
                        $updateRideStmt->close();

                        // Get the rider's first and last name
                        $riderFirstName = $ride['driverFirstName'];
                        $riderLastName = $ride['driverLastName'];

                        // Send email to the rider (the person who created the ride)
                        sendConfirmationEmailToRider($ride['driverEmail'], $riderFirstName, $riderLastName, $bookedSeats, $pickupLocation, $dropoffLocation, $totalFare);

                        $success_message = "Booking confirmed successfully! Total Fare: ₹" . $totalFare;
                    } else {
                        $error_message = "Error confirming booking: " . $conn->error;
                    }
                    $bookingStmt->close();
                } else {
                    $error_message = "Sorry, not enough available seats for the requested number.";
                }
            } else {
                // Set a default value or handle the error accordingly
                $error_message = "Please select the number of seats.";
            }
        }
    }
}

$stmt->close();
$conn->close();

// Function to calculate the fare per seat based on the distance and original fare
function calculateFare($a_to_b, $a_to_b_direct, $originalFare) {
    $directDistance = $a_to_b_direct;
    $totalDistance = $a_to_b; // Assuming $a_to_b represents the direct distance from origin to destination

    if ($totalDistance <= 0.6 * $directDistance) {
        // If the distance is less than or equal to 60% of the direct distance, apply 30% discount
        $fare = $originalFare * 0.7; // 30% discount
    } elseif ($totalDistance > 0.6 * $directDistance && $totalDistance <= 0.7 * $directDistance) {
        // If the distance is between 60% and 80% of the direct distance, apply 10% discount
        $fare = $originalFare * 0.9; // 10% discount
    } else {
        // If the distance is more than 90% of the direct distance, no discount
        $fare = $originalFare;
    }

    return $fare;
}

// Function to send confirmation email to the rider
// Function to send confirmation email to the rider
function sendConfirmationEmailToRider($riderEmail, $riderFirstName, $riderLastName, $bookedSeats, $pickupLocation, $dropoffLocation, $totalFare) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server address
    $mail->SMTPAuth = true;
    $mail->Username = 'pratikkesapure@gmail.com'; // Replace with your email address
    $mail->Password = 'dgmexuvcqyymejhv'; // Replace with your email password
    $mail->SMTPSecure = 'ssl'; // or 'ssl' depending on your SMTP server
    $mail->Port = 465; // or the appropriate port for your SMTP server

    $mail->setFrom('pratikkesapure@gmail.com', 'GetGo'); // Replace with your email address and name
    $mail->addAddress($riderEmail, $riderFirstName . ' ' . $riderLastName);
    $mail->Subject = 'New Booking for Your Ride';

    $mailBody = "Dear " . $riderFirstName . " " . $riderLastName . ",\n\n";
    $mailBody .= "A passenger has booked seats for your ride. Here are the details:\n\n";
    $mailBody .= "Number of Seats Booked: " . $bookedSeats . "\n";
    $mailBody .= "Passenger Pickup Location: " . $pickupLocation . "\n";
    $mailBody .= "Passenger Drop-off Location: " . $dropoffLocation . "\n";
    $mailBody .= "Total Fare for the Booking: ₹" . $totalFare . "\n\n";
    $mailBody .= "Please make the necessary arrangements for the passenger.\n\n";
    $mailBody .= "Best regards,\n";
    $mailBody .= "GetGo carpool";

    $mail->Body = $mailBody;

    if (!$mail->send()) {
        // Handle email sending error
        echo 'Email could not be sent: ' . $mail->ErrorInfo;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Confirm Booking</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <!-- Custom CSS -->
    <!-- ... -->
</head>
<body>
    <div class="content-wrapper">
        <!-- Navigation Bar -->
        <!-- ... -->

        <!-- Success or Error Message -->
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success container mt-5" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger container mt-5" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php } ?>
    </div>

    <!-- Confirmation Page -->
    <div class="container my-5">
        <h2>Confirm Booking</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Ride Details</h5>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?ride_id=' . $rideId2); ?>">
                    <!-- Display pickup and drop-off locations -->
                    <p class="card-text"><strong>Pickup Location:</strong> <?php echo $pickupLocation; ?></p>
                    <p class="card-text"><strong>Drop-off Location:</strong> <?php echo $dropoffLocation; ?></p>
                    <p class="card-text"><strong>Date:</strong> <?php echo $ride['date']; ?></p>
                    <div class="form-group">
                        <strong><label for="seats">Select Number of Seats</label></strong>
                        <select class="form-control" id="seats" name="seats" required onchange="updateTotalFare()">
                            <?php for ($i = 1; $i <= $availableSeats; $i++) { ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <p class="card-text"><strong>Available Seats:</strong> <?php echo $ride['seats']; ?></p>
                    <p class="card-text"><strong>Fare per Seat:</strong> ₹<?php echo $farePerSeat; ?></p>
                    <p class="card-text"><strong>Driver:</strong> <?php echo $ride['driverFirstName'] . ' ' . $ride['driverLastName']; ?></p>
                    <p class="card-text"><strong>Total Fare:</strong> ₹<span id="totalFare"><?php echo $totalFare; ?></span></p>
                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <!-- ... -->

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function updateTotalFare() {
            var seats = document.getElementById('seats').value;
            var farePerSeat = <?php echo $farePerSeat; ?>;
            var totalFare = seats * farePerSeat;
            document.getElementById('totalFare').textContent = totalFare.toFixed(2);
        }

        // Update the total fare when the page loads
        updateTotalFare();
    </script>
</body>
</html>