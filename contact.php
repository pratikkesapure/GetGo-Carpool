<?php
include 'navbar.php';

// Check if the ride ID is passed in the URL
if (isset($_GET['ride_id'])) {
    $ride_id = $_GET['ride_id'];

    // Database connection
    include 'db.php';

    // Query the database to get the rider's information
    $query = "SELECT u.phone, u.firstName, u.lastName, u.unique_id
          FROM users u
          JOIN rides r ON u.id = r.user_id
          WHERE r.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $phone = $row['phone'];
        $firstName = $row['firstName'];
        $lastName = $row['lastName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Contact Rider</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container my-5">
        <h2>Contact Rider</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Rider Information</h5>
                <p class="card-text">Name: <?php echo $firstName . ' ' . $lastName; ?></p>
                <p class="card-text">Phone Number: <?php echo $phone; ?></p>
                <a href="chat.php?user_id=<?php echo $row['unique_id']; ?>" class="btn btn-primary">Chat with Rider</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
    } else {
        echo "Rider information not found.";
    }
    $stmt->close();
} else {
    echo "Ride ID not provided.";
}

// Close connection
$conn->close();
?>