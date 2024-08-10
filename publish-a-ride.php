<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carpooling Website - Publish a Ride</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Publish a Ride Form -->
    <div class="container my-5">
      <div class="publish-form">
        <h2>Publish a Ride</h2>
        <form id="publishRideForm" action="process-publish-a-ride.php" method="post">
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
              <label for="time">Time</label>
              <input type="time" class="form-control" id="time" name="time" required>
            </div>
          </div>
          <div class="form-group">
            <label for="seats">Number of Seats</label>
            <input type="number" class="form-control" id="seats" name="seats" placeholder="Enter the number of seats available" min="1" required>
          </div>
          <div class="form-group">
            <label for="fare">Fare</label>
            <input type="number" class="form-control" id="fare" name="fare" placeholder="Enter the fare amount" step="1" min="0" required>
          </div>
          <button type="button" class="btn btn-primary btn-block" id="showRouteOptionsBtn">Show Route Options</button>
        </form>
      </div>
    </div>

    <!-- Route Options Modal -->
    <div class="modal fade" id="routeOptionsModal" tabindex="-1" role="dialog" aria-labelledby="routeOptionsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="routeOptionsModalLabel">Route Options</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div id="routeMap" style="height: 400px;"></div>
            <div id="routeOptionsPanel"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmRideBtn">Confirm Ride</button>
          </div>
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

    

      const publishRideForm = document.getElementById('publishRideForm');
      const showRouteOptionsBtn = document.getElementById('showRouteOptionsBtn');
      const routeOptionsModal = $('#routeOptionsModal');
      const confirmRideBtn = document.getElementById('confirmRideBtn');

      showRouteOptionsBtn.addEventListener('click', function() {
  if (publishRideForm.checkValidity()) {
    const leavingFromPlace = leavingFromAutocomplete.getPlace();
    const goingToPlace = goingToAutocomplete.getPlace();

    if (leavingFromPlace && goingToPlace) {
      const leavingFromLatLng = leavingFromPlace.geometry.location;
      const goingToLatLng = goingToPlace.geometry.location;

      const routeMap = new google.maps.Map(document.getElementById('routeMap'), {
        zoom: 7,
        center: leavingFromLatLng
      });

      const directionsService = new google.maps.DirectionsService();

      const request = {
        origin: leavingFromLatLng,
        destination: goingToLatLng,
        travelMode: 'DRIVING',
        provideRouteAlternatives: true,
        unitSystem: google.maps.UnitSystem.METRIC
      };

      directionsService.route(request, function(response, status) {
        if (status === 'OK') {
          const renderer = new google.maps.DirectionsRenderer({
            map: routeMap,
            draggable: true,
            panel: document.getElementById('routeOptionsPanel'),
            provideRouteAlternatives: true
          });
          renderer.setDirections(response);
          routeOptionsModal.modal('show');
        } else {
          alert('Failed to calculate routes. Please try again.');
        }
      });
    } else {
      alert('Please select a valid location from the suggestions.');
    }
  } else {
    publishRideForm.reportValidity();
  }
});

confirmRideBtn.addEventListener('click', function() {
  const leavingFromPlace = leavingFromAutocomplete.getPlace();
  const goingToPlace = goingToAutocomplete.getPlace();

  if (leavingFromPlace && goingToPlace) {
    leavingFromInput.value = leavingFromPlace.formatted_address;
    goingToInput.value = goingToPlace.formatted_address;
  }

  document.getElementById('publishRideForm').submit();
});
    }
  </script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDfUqgPfFa4Aoez2fQln-FOfjfaFIVAE-4&libraries=places&callback=initAutocomplete" async defer></script>
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html>