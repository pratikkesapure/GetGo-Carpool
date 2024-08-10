<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Start the session
session_start();

// Include the file containing database connection
include 'db.php';

// Process signup form if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs to prevent SQL injection
    $ran_id = rand(time(), 100000000);
    $status = "Active now";
    $email = $conn->real_escape_string($_POST['email']);
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $password = $conn->real_escape_string($_POST['password']);

    // Check if email already exists in the database
    $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
    $emailResult = $conn->query($checkEmailQuery);

    if ($emailResult->num_rows > 0) {
        $error = "Error: Email already exists";
        header("Location: signup.php?error=" . urlencode($error));
        exit();
    } else {
        // Check if phone number already exists in the database
        $checkPhoneQuery = "SELECT * FROM users WHERE phone = '$phone'";
        $phoneResult = $conn->query($checkPhoneQuery);

        if ($phoneResult->num_rows > 0) {
            $error = "Error: Phone number already exists";
            header("Location: signup.php?error=" . urlencode($error));
            exit();
        } else {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Send email with OTP
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'pratikkesapure@gmail.com';
                $mail->Password = 'dgmexuvcqyymejhv';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                // Recipients
                $mail->setFrom('pratikkesapure@gmail.com', 'GetGo');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Email Verification OTP';
                $mail->Body = "Your OTP for email verification is: $otp";

                // Send the email
                $mail->send();

                // Store the email and OTP in session variables
                $_SESSION['email'] = $email;
                $_SESSION['otp'] = $otp;
                $_SESSION['firstName'] = $firstName;
                $_SESSION['lastName'] = $lastName;
                $_SESSION['phone'] = $phone;
                $_SESSION['dob'] = $dob;
                $_SESSION['password'] = $password;

                // Prompt the user to enter the OTP
                $enterOTP = true;
            } catch (Exception $e) {
                // No need to handle the exception here
            }
        }
    }
}

// If the user needs to enter the OTP
if (isset($enterOTP) && $enterOTP) {
    // Display a form for the user to enter the OTP
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Verify OTP</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container my-5">
            <h2>Verify OTP</h2>
            <p>Please enter the OTP sent to your email address.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                <div class="form-group">
                    <label for="otp">OTP:</label>
                    <input type="text" class="form-control" id="otp" name="otp" required>
                </div>
                <button type="submit" class="btn btn-primary">Verify</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle the OTP verification process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $enteredOTP = $_POST['otp'];

    // Retrieve the OTP, email, and user data from session variables
    $otp = $_SESSION['otp'];
    $email = $_SESSION['email'];
    $firstName = $_SESSION['firstName'];
    $lastName = $_SESSION['lastName'];
    $phone = $_SESSION['phone'];
    $dob = $_SESSION['dob'];
    $password = $_SESSION['password'];

    // Verify the OTP entered by the user
    if ($enteredOTP == $otp) {
        // If OTP is correct, insert user data into the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertQuery = "INSERT INTO users (unique_id, email, firstName, lastName, phone, dob, password, status) VALUES ('$ran_id', '$email', '$firstName', '$lastName', '$phone', '$dob', '$hashedPassword', '$status')";

        if ($conn->query($insertQuery) === TRUE) {
            $success = "Signup Successful, You can now login!";
            header("Location: signup.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Error: " . $insertQuery . "<br>" . $conn->error;
            header("Location: signup.php?error=" . urlencode($error));
            exit();
        }
    } else {
        $error = "Invalid OTP. Please try again.";
        header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?error=" . urlencode($error));
        exit();
    }
}

// Close connection (optional, as it might be used elsewhere)
$conn->close();
?>