<?php
include 'navbar.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db.php';


// Check if the ride ID is provided in the URL
if (isset($_GET['ride_id'])) {
    $rideId = $_GET['ride_id'];
    $userId = $_SESSION['user_id'];

    // Check if the ride belongs to the logged-in user
    $stmt = $conn->prepare("SELECT * FROM rides WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $rideId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // The ride belongs to the user, retrieve the bookings
        $stmt = $conn->prepare("SELECT b.id, b.seats_booked, b.booking_status, u.firstName, u.lastName, u.phone, u.email, u.unique_id, pl.passenger_pickup, pl.passenger_dropoff
                        FROM bookings b
                        JOIN users u ON b.user_id = u.id
                        LEFT JOIN passenger_locations pl ON b.id = pl.booking_id
                        WHERE b.ride_id = ?");
        $stmt->bind_param("i", $rideId);
        $stmt->execute();
        $bookings = $stmt->get_result();
    } else {
        $error_message = "You are not authorized to view bookings for this ride.";
    }

    $stmt->close();
} else {
    $error_message = "Invalid ride ID.";
}

// Handle remove passenger action
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['booking_id'])) {
    $bookingId = $_GET['booking_id'];

    // Retrieve the seats booked for the booking
    $stmt = $conn->prepare("SELECT seats_booked FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $seatsBooked = $booking['seats_booked'];

    // Update the booking_status column to 0 (canceled)
    $updateBookingStmt = $conn->prepare("UPDATE bookings SET booking_status = 0 WHERE id = ?");
    $updateBookingStmt->bind_param("i", $bookingId);
    $updateBookingStmt->execute();

    // Update the booked_seats column in the rides table
    $updateRideStmt = $conn->prepare("UPDATE rides SET booked_seats = booked_seats - ? WHERE id = ?");
    $updateRideStmt->bind_param("ii", $seatsBooked, $rideId);
    $updateRideStmt->execute();

    if ($updateBookingStmt->affected_rows > 0 && $updateRideStmt->affected_rows > 0) {
        $success_message = "Passenger removed successfully.";
    } else {
        $error_message = "Error removing passenger: " . $conn->error;
    }
}



$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Ride Bookings</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container my-5">
        <h2>Ride Bookings</h2>
        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php } elseif (isset($success_message)) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php } else { ?>
            <?php if ($bookings !== null && $bookings->num_rows > 0) { ?>
                <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Seats Booked</th>
                        <th>Ride Status</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Pickup Location</th>
                        <th>Drop-off Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    <?php while ($booking = $bookings->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $booking['firstName'] . ' ' . $booking['lastName']; ?></td>
            <td><?php echo $booking['seats_booked']; ?></td>
            <td><?php echo $booking['booking_status'] ? 'Active' : 'Canceled'; ?></td>
            <td><?php echo $booking['phone']; ?></td>
            <td><?php echo $booking['email']; ?></td>
            <td><?php echo isset($booking['passenger_pickup']) ? $booking['passenger_pickup'] : ''; ?></td>
            <td><?php echo isset($booking['passenger_dropoff']) ? $booking['passenger_dropoff'] : ''; ?></td>
            <td>
                <?php if ($booking['booking_status']) { ?>
                    <a href="chat.php?user_id=<?php echo $booking['unique_id']; ?>" class="btn btn-primary btn-sm">Chat</a>
                    <a href="ride-bookings.php?ride_id=<?php echo $rideId; ?>&action=remove&booking_id=<?php echo $booking['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this passenger?')">Remove</a>
                <?php } else { ?>
                    <span class="badge badge-secondary">Canceled</span>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</tbody>
                </table>
            <?php } else { ?>
                <p>No bookings found for this ride.</p>
            <?php } ?>
        <?php } ?>
        <a href="your-rides.php" class="btn btn-primary">Back to Your Rides</a>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>