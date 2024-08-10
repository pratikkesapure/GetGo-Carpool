<?php
session_start();

// Database connection
include 'db.php';

// Retrieve form data
$email = $_POST['email'];
$password = $_POST['password'];

// Prepare and execute SQL query
$stmt = $conn->prepare("SELECT id, email, password, unique_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if a user with the provided email exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password'];

    // Verify the password
    if (password_verify($password, $hashed_password)) {
        // Password is correct, start the session
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['unique_id'] = $row['unique_id'];
        header("Location: index.php"); // Redirect to the homepage
        exit();
    } else {
        $error_message = "Invalid email or password.";
    }
} else {
    $error_message = "Invalid email or password.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <!-- ... (Same as in login.php) -->
</head>
<body>
    <div class="content-wrapper">
        <!-- Navigation Bar -->
        <!-- ... (Same as in login.php) -->
    </div>

    <!-- Error Message -->
    <?php if (isset($error_message)) { ?>
        <div class="alert alert-danger mx-auto mt-5" role="alert" style="max-width: 400px;">
            <?php echo $error_message; ?>
        </div>
    <?php } ?>

    <!-- Bottom Section -->
    <!-- ... (Same as in login.php) -->

    <!-- Footer -->
    <!-- ... (Same as in login.php) -->

    <!-- Bootstrap JS -->
    <!-- ... (Same as in login.php) -->
</body>
</html>