<?php
include 'navbar.php';

// Check if an error or success message is passed via query parameter
$error = isset($_GET['error']) ? urldecode($_GET['error']) : '';
$success = isset($_GET['success']) ? urldecode($_GET['success']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carpooling Website - Sign Up</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Sign Up Form -->
    <div class="container my-5">
      <?php if (!empty($error)) { ?>
        <div class="alert alert-danger" role="alert">
          <?php echo $error; ?>
        </div>
      <?php } ?>

      <?php if (!empty($success)) { ?>
        <div class="alert alert-success" role="alert">
          <?php echo $success; ?>
        </div>
      <?php } ?>

    <!-- Sign Up Form -->
    
      <div class="signup-form">
        <h2>Sign Up</h2>
        <form id="signupForm" action="process-signup.php" method="post">
  <div class="form-group">
    <label for="email">Email</label>
    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
  </div>
  <div class="form-row">
    <div class="form-group col-md-6">
      <label for="firstName">First Name</label>
      <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter your first name" required>
    </div>
    <div class="form-group col-md-6">
      <label for="lastName">Last Name</label>
      <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter your last name" required>
    </div>
  </div>
  <div class="form-group">
    <label for="phone">Phone Number</label>
    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" pattern="[0-9]{10}" required>
  </div>
  <div class="form-group">
    <label for="dob">Date of Birth</label>
    <input type="date" class="form-control" id="dob" name="dob" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" required>
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
  </div>
  <div class="form-group">
    <label for="confirmPassword">Confirm Password</label>
    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
</form>
<div class="form-group form-check">
            <a href="login.php" class="forgot-password">Already Registered? Login Here</a>
</div>

      </div>
    </div>

    <?php include 'footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>

  <script>
    // Client-side validation
    const form = document.getElementById('signupForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');

    form.addEventListener('submit', function(event) {
      // Check if passwords match
      if (passwordInput.value !== confirmPasswordInput.value) {
        alert('Passwords do not match');
        event.preventDefault();
      }

      // Check for duplicate email (implement server-side validation as well)
      // ...
    });
  </script>
</body>
</html>