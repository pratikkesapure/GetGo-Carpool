<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Reset Password</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Reset Password Form -->
    <div class="container my-5">
        <div class="login-form">
            <h2>Reset Password</h2>
            <?php
            // Database connection
            include 'db.php';

            // Get the reset token from the URL
            $reset_token = $_GET['token'];
            

            // Check if the reset token is valid
            $sql = "SELECT * FROM users WHERE reset_token = '$reset_token' AND reset_expiry >= NOW()";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Reset token is valid, display the reset password form
                ?>
                <form action="process-reset-password.php" method="post">
                    <input type="hidden" name="reset_token" value="<?php echo $reset_token; ?>">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>
                <?php
            } else {
                // Reset token is invalid or expired
                echo "Invalid or expired reset token.";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>