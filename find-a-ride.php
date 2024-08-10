<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carpooling Website - Find a Ride</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Find a Ride Form -->
    <div class="container my-5">
      <div class="find-ride-form">
        <h2>Find a Ride</h2>
        <form action="process-find-a-ride.php" method="post">
          <div class="form-group">
            <label for="leavingFrom">Leaving From</label>
            <input type="text" class="form-control" id="leavingFrom" name="leavingFrom" placeholder="Enter the starting location" required>
          </div>
          <div class="form-group">
            <label for="goingTo">Going To</label>
            <input type="text" class="form-control" id="goingTo" name="goingTo" placeholder="Enter the destination" required>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="date">Date</label>
              <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="form-group col-md-6">
              <label for="passengers">Number of Passengers</label>
              <input type="number" class="form-control" id="passengers" name="passengers" placeholder="Enter the number of passengers" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Find a Ride</button>
        </form>
      </div>
    </div>

    <?php include 'footer.php'; ?>

  <!-- Bootstrap JS -->
  <script>
    function initAutocomplete() {
      const leavingFromInput = document.getElementById('leavingFrom');
      const goingToInput = document.getElementById('goingTo');

      const leavingFromAutocomplete = new google.maps.places.Autocomplete(leavingFromInput);
      const goingToAutocomplete = new google.maps.places.Autocomplete(goingToInput);

      const findRideForm = document.querySelector('.find-ride-form form');
      findRideForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent the form from submitting initially

        // Get the first suggestion from the Autocomplete instances
        const leavingFromPlace = leavingFromAutocomplete.getPlace();
        const goingToPlace = goingToAutocomplete.getPlace();

        if (leavingFromPlace && goingToPlace) {
          // Set the input values with the first suggestion
          leavingFromInput.value = leavingFromPlace.formatted_address;
          goingToInput.value = goingToPlace.formatted_address;

          // Submit the form
          findRideForm.submit();
        } else {
          // Handle the case when no suggestion is selected
          alert('Please select a valid location from the suggestions.');
        }
      });
    }
  </script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDfUqgPfFa4Aoez2fQln-FOfjfaFIVAE-4&libraries=places&callback=initAutocomplete" async defer></script>

  <!-- Google Maps JavaScript API -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDfUqgPfFa4Aoez2fQln-FOfjfaFIVAE-4&libraries=places"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html>