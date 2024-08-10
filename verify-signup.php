<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carpooling Website - Verify Sign Up</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css">

</head>
<body>


    <!-- Verify Sign Up Form -->
    <div class="container my-5">
      <div class="verify-form">
        <h2>Verify Sign Up</h2>
        <p>An OTP has been sent to your registered email address. Please enter the OTP to verify your account.</p>
        <form>
          <div class="form-group">
            <label for="otp">OTP</label>
            <input type="text" class="form-control" id="otp" placeholder="Enter the OTP">
          </div>
          <button type="submit" class="btn btn-primary btn-block">Verify</button>
        </form>
      </div>
    </div>

    <?php include 'footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html>