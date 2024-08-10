<?php
include 'navbar.php';
include 'db.php'; // Include the database connection

// Set the timezone to your desired value
date_default_timezone_set('Asia/Kolkata'); // Replace with your timezone

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email is present in the database
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Email found in the database
        try {
            // Generate the reset token
            $reset_token = bin2hex(random_bytes(16));

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'pratikkesapure@gmail.com'; // Replace with your SMTP username
            $mail->Password = 'dgmexuvcqyymejhv'; // Replace with your SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            // Recipients
            $mail->setFrom('pratikkesapure@gmail.com', 'GetGo'); // Replace with your email address and name
            $mail->addAddress($email); // Add the user's email address

            // Content
            $resetLink = 'http://localhost/car/reset-password.php?email=' . urlencode($email) . '&token=' . $reset_token;

            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Reset Your Password';
            $mail->Body = 'Click the following link to reset your password: <a href="' . $resetLink . '">' . $resetLink . '</a>';

            $mail->send();
            $successMessage = '<div class="alert alert-success mt-4" role="alert">A password reset link has been sent to your email address.</div>';

            // Update the user's reset token and expiry time in the database
            $reset_expiry = date('Y-m-d H:i:s', time() + 3600); // Set the expiry time to 1 hour from now

            $sql = "UPDATE users SET reset_token = '$reset_token', reset_expiry = '$reset_expiry' WHERE email = '$email'";

            if ($conn->query($sql) === TRUE) {
                // Token and expiry time updated successfully
            } else {
                $errorMessage = '<div class="alert alert-danger mt-4" role="alert">Error updating reset token and expiry time: ' . $conn->error . '</div>';
            }
        } catch (Exception $e) {
            $errorMessage = '<div class="alert alert-danger mt-4" role="alert">Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</div>';
        }
    } else {
        // Email not found in the database
        $errorMessage = '<div class="alert alert-danger mt-4" role="alert">The email address you entered is not registered.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Forgot Password</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Forgot Password Form -->
    <div class="container my-5">
        <?php
        // Display success or error messages if present
        if (isset($successMessage)) {
            echo $successMessage;
        }
        if (isset($errorMessage)) {
            echo $errorMessage;
        }
        ?>
        <div class="login-form">
            <h2>Forgot Password</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>