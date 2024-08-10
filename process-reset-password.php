<?php
// Database connection
include 'db.php';

// Get the reset token and new password
$reset_token = $_POST['reset_token'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Check if the new password and confirmation match
if ($new_password === $confirm_password) {
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $sql = "UPDATE users SET password = '$hashed_password', reset_token = NULL, reset_expiry = NULL WHERE reset_token = '$reset_token'";

    if ($conn->query($sql) === TRUE) {
        echo "Password reset successful. You can now <a href='login.php'>login</a> with your new password.";
    } else {
        echo "Error updating password: " . $conn->error;
    }
} else {
    echo "New password and confirmation do not match.";
}

$conn->close();
?>