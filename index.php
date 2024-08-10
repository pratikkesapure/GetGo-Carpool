<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carpooling Website</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css">

</head>
<body>

  <div class="container my-5">
    <h1 class="text-center mb-4">Welcome to Carpooling Website</h1>

    <!-- Search Form -->
    <div class="row justify-content-center">
      <div class="col-md-10">
        <form action="process-find-a-ride.php" method="post" class="search-form d-flex justify-content-between align-items-center">
          <div class="form-group mr-2">
            <input type="text" id="leavingFrom" name="leavingFrom" class="form-control" placeholder="Leaving From" required>
          </div>
          <div class="form-group mr-2">
            <input type="text" class="form-control" id="goingTo" name="goingTo" placeholder="Going To" required>
          </div>
          <div class="form-group mr-2">
            <input type="date" id="date" name="date" class="form-control" required>
          </div>
          <div class="form-group mr-2">
            <input type="number" class="form-control" id="passengers" name="passengers" placeholder="Number of Passengers" required>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-bottom: 1rem;">Search</button>
        </form>
      </div>
    </div>

    <!-- About Section -->
    <div class="row mt-5">
      <div class="col-md-4">
        <h3>About Us</h3>
        <p>Welcome to GetGo, where we connect people, reduce carbon footprints, and enhance community bonds through shared rides. Our platform was created to revolutionize commuting with sustainability, convenience, and meaningful connections at its core.</p>
      </div>
      <div class="col-md-4">
        <h3>Our Mission</h3>
        <p>At GetGo, we're on a mission to promote sustainability, enhance community connectivity, and ensure convenience through carpooling. We aim to reduce vehicles on the road, decrease carbon emissions, and foster a sense of camaraderie and community spirit.</p>
      </div>
      <div class="col-md-4">
        <h3>Why Choose Us?</h3>
        <p>Choose GetGo for reliability, affordability, and environmental impact. Our platform matches users with reliable carpool partners, ensuring a safe and enjoyable ride-sharing experience. Save money on fuel costs, tolls, and parking fees by sharing expenses. By choosing us, you're contributing to a greener planet. </p>
      </div>
    </div>
  </div>

  <!-- Popular Routes -->
  <div class="popular-routes bg-light">
    <div class="container">
      <h2 class="text-center mb-4">Where do you want a ride to?</h2>
      <div class="d-flex justify-content-center">
        <a href="#" class="route-container">
          <div class="route">
            <span>Nashik</span>
            <span class="arrow">&rarr;</span>
            <span>Pune</span>
          </div>
        </a>
        <a href="#" class="route-container">
          <div class="route">
            <span>Mumbai</span>
            <span class="arrow">&rarr;</span>
            <span>Delhi</span>
          </div>
        </a>
        <a href="#" class="route-container">
          <div class="route">
            <span>Pune</span>
            <span class="arrow">&rarr;</span>
            <span>Hydrabad</span>
          </div>
        </a>
      </div>
    </div>
  </div>


  <?php include 'footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    function initAutocomplete() {
      const leavingFromInput = document.getElementById('leavingFrom');
      const goingToInput = document.getElementById('goingTo');

      const leavingFromAutocomplete = new google.maps.places.Autocomplete(leavingFromInput);
      const goingToAutocomplete = new google.maps.places.Autocomplete(goingToInput);

      const searchForm = document.querySelector('.search-form');
      searchForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent the form from submitting initially

        // Get the first suggestion from the Autocomplete instances
        const leavingFromPlace = leavingFromAutocomplete.getPlace();
        const goingToPlace = goingToAutocomplete.getPlace();

        if (leavingFromPlace && goingToPlace) {
          // Set the input values with the first suggestion
          leavingFromInput.value = leavingFromPlace.formatted_address;
          goingToInput.value = goingToPlace.formatted_address;

          // Submit the form
          searchForm.submit();
        } else {
          // Handle the case when no suggestion is selected
          alert('Please select a valid location from the suggestions.');
        }
      });
    }
  </script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDfUqgPfFa4Aoez2fQln-FOfjfaFIVAE-4&libraries=places&callback=initAutocomplete" async defer></script>
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html>