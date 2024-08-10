<?php
session_start();

// Check if the user is logged in
$logged_in = isset($_SESSION['user_id']);

// If the user is not logged in and the current page is not login.php or signup.php, redirect to login.php
$current_page = basename($_SERVER['PHP_SELF']);
if (!$logged_in && $current_page != 'login.php' && $current_page != 'signup.php' && $current_page != 'index.php' && $current_page != 'process-find-a-ride.php' && $current_page != 'find-a-ride.php' && $current_page != 'forgot-password.php' && $current_page != 'process-forgot-password.php' && $current_page != 'reset-password.php' && $current_page != 'process-reset-password.php' ) {
    header("Location: login.php");
    exit();
}

// Function to generate the HTML markup for the profile dropdown
function getProfileDropdownHTML($isLoggedIn) {
    $html = '<li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-user"></i> Profile
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">';

    if ($isLoggedIn) {
        $html .= '<a class="dropdown-item" href="profile.php">Profile</a>
                  <a class="dropdown-item" href="inbox.php">Inbox</a>
                  <a class="dropdown-item" href="your-rides.php">Your Rides</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="logout.php">Logout</a>';
    } else {
        $html .= '<a class="dropdown-item" href="login.php">Login</a>
                  <a class="dropdown-item" href="signup.php">Sign Up</a>';
    }

    $html .= '</div>
             </li>';

    return $html;
}

// Function to display the profile dropdown
function displayProfileDropdown() {
    global $logged_in;
    echo getProfileDropdownHTML($logged_in);
}
?>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <a class="navbar-brand" href="index.php">
    <img src="logo.png" alt="Logo">
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="find-a-ride.php">Search</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="publish-a-ride.php">Publish a Ride</a>
      </li>
      <?php displayProfileDropdown(); ?>
    </ul>
  </div>
</nav>