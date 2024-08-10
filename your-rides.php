<?php
include 'navbar.php';

// Database connection
include 'db.php';

// Check if the ride ID and action are provided in the URL
if (isset($_GET['ride_id']) && isset($_GET['action'])) {
    $rideId = $_GET['ride_id'];
    $userId = $_SESSION['user_id'];
    $action = $_GET['action'];

    if ($action === 'cancel_ride') {
        // Check if the ride belongs to the logged-in user
        $stmt = $conn->prepare("SELECT * FROM rides WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $rideId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // The ride belongs to the user, proceed with cancellation
            $updateRideStmt = $conn->prepare("UPDATE rides SET ride_status = 0 WHERE id = ?");
            $updateRideStmt->bind_param("i", $rideId);
            $updateRideStmt->execute();

            if ($updateRideStmt->affected_rows > 0) {
                $success_message = "Ride cancellation successful.";
            } else {
                $error_message = "Error canceling ride: " . $conn->error;
            }
        } else {
            $error_message = "You are not authorized to cancel this ride.";
        }
    } elseif ($action === 'cancel_booking') {
        // Check if the booking belongs to the logged-in user
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE ride_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $rideId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // The booking belongs to the user, proceed with cancellation
            $updateBookingStmt = $conn->prepare("UPDATE bookings SET booking_status = 0 WHERE ride_id = ? AND user_id = ?");
            $updateBookingStmt->bind_param("ii", $rideId, $userId);
            $updateBookingStmt->execute();

            if ($updateBookingStmt->affected_rows > 0) {
                $success_message = "Booking cancellation successful.";
            } else {
                $error_message = "Error canceling booking: " . $conn->error;
            }
        } else {
            // Check if the user is the ride creator and removing a passenger
            $stmt = $conn->prepare("SELECT * FROM rides WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $rideId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // The user is the ride creator, proceed with removing the passenger
                $updateBookingStmt = $conn->prepare("UPDATE bookings SET booking_status = 0 WHERE ride_id = ? AND id = ?");
                $updateBookingStmt->bind_param("ii", $rideId, $_GET['booking_id']);
                $updateBookingStmt->execute();

                if ($updateBookingStmt->affected_rows > 0) {
                    $success_message = "Passenger removed successfully.";
                } else {
                    $error_message = "Error removing passenger: " . $conn->error;
                }
            } else {
                $error_message = "You are not authorized to cancel this booking or remove the passenger.";
            }
        }
    } else {
        $error_message = "Invalid action.";
    }
}

// Retrieve the user's rides from the database
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT *, rides.fare, (SELECT SUM(seats_booked) FROM bookings WHERE ride_id = rides.id AND booking_status = 1) AS booked_seats FROM rides WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRides = $stmt->get_result();

// Retrieve the rides booked by the user
$stmt = $conn->prepare("SELECT r.*, r.fare, b.seats_booked, b.booking_status, b.id AS booking_id, u.firstName, u.lastName FROM bookings b JOIN rides r ON b.ride_id = r.id JOIN users u ON r.user_id = u.id WHERE b.user_id = ? ORDER BY b.id DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$bookedRides = $stmt->get_result();

$conn->close();
?>

<!-- Rest of the HTML code remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Your Rides</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container my-5">
        <h2>Your Rides</h2>
        
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php } ?>

        <h3>Rides Created by You</h3>
        <?php if ($userRides->num_rows > 0) { ?>
            <table class="table table-striped">
            <thead>
                <tr>
                    <th>Leaving From</th>
                    <th>Going To</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Seats</th>
                    <th>Booked Seats</th>
                    <th>Fare per Seat</th>
                    <th>Ride Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $userRides->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $row['leaving_from']; ?></td>
                        <td><?php echo $row['going_to']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['time']; ?></td>
                        <td><?php echo $row['seats']; ?></td>
                        <td><?php echo $row['booked_seats']; ?></td>
                        <td>₹<?php echo $row['fare']; ?></td>
                        <td><?php echo $row['ride_status'] ? 'Active' : 'Canceled'; ?></td>
                        <td>
                          <a href="ride-bookings.php?ride_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Bookings</a>
                          <?php if ($row['ride_status']) { ?>
                            <a href="your-rides.php?ride_id=<?php echo $row['id']; ?>&action=cancel_ride" class="btn btn-danger btn-sm" onclick="return confirmCancelRide()">Cancel</a>
                          <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            </table>
        <?php } else { ?>
            <p>You haven't created any rides yet.</p>
        <?php } ?>
        
        <h3 class="mt-5">Rides Booked by You</h3>
        <?php if ($bookedRides->num_rows > 0) { ?>
            <table class="table table-striped">
            <thead>
                <tr>
                    <th>Leaving From</th>
                    <th>Going To</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Seats Booked</th>
                    <th>Driver</th>
                    <th>Fare per Seat</th>
                    <th>Total Fare</th>
                    <th>Booking Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookedRides->fetch_assoc()) {
                    $totalFare = $row['fare'] * $row['seats_booked'];
                    ?>
                    <tr>
                        <td><?php echo $row['leaving_from']; ?></td>
                        <td><?php echo $row['going_to']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['time']; ?></td>
                        <td><?php echo $row['seats_booked']; ?></td>
                        <td><?php echo $row['firstName'] . ' ' . $row['lastName']; ?></td>
                        <td>₹<?php echo $row['fare']; ?></td>
                        <td>₹<?php echo $totalFare; ?></td>
                        <td><?php echo $row['booking_status'] ? 'Active' : 'Canceled'; ?></td>
                        <td>
                            <?php if ($row['booking_status']) { ?>
                                <a href="contact.php?ride_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Contact</a>
                                <a href="your-rides.php?ride_id=<?php echo $row['id']; ?>&action=cancel_booking&booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmCancelBooking()">Cancel</a>
                            <?php } else { ?>
                                <span class="badge badge-secondary">Canceled</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            </table>
        <?php } else { ?>
            <p>You haven't booked any rides yet.</p>
        <?php } ?>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script>
    function confirmCancelRide() {
        return confirm("Are you sure you want to cancel this ride?");
    }
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function confirmCancelBooking() {
        return confirm("Are you sure you want to cancel this booking?");
    }
    </script>
</body>
</html>