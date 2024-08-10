<?php
include 'db.php';
include 'navbar.php';



$user_id = $_SESSION['user_id'];

// Retrieve user data from the database
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

// Handle form submission for updating user details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $vehicle = $conn->real_escape_string($_POST['vehicle']);

    // Handle profile picture upload
    $profilePicture = null;
    if (!empty($_FILES['profilePicture']['tmp_name'])) {
        $uploadDir = 'uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['profilePicture']['name']);
        $targetPath = $uploadDir . $fileName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profilePicture']['type'];

        if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['profilePicture']['tmp_name'], $targetPath)) {
            $profilePicture = $targetPath;
        } else {
            $error = "Error uploading profile picture. Only JPG, PNG, and GIF files are allowed.";
        }
    }

    $updateQuery = "UPDATE users SET firstName = '$firstName', lastName = '$lastName', phone = '$phone', dob = '$dob', bio = '$bio', vehicle = '$vehicle'";

    if ($profilePicture !== null) {
        $updateQuery .= ", profilePicture = '$profilePicture'";
    }

    $updateQuery .= " WHERE id = $user_id";

    if ($conn->query($updateQuery) === TRUE) {
        $success = "User details updated successfully.";
    } else {
        $error = "Error updating user details: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container my-5">
        <h2>About You</h2>
        <?php if (isset($success)) : ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif (isset($error)) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-4">
            <img src="<?php echo $user['profilePicture'] ? $user['profilePicture'] : 'default_profile_picture.jpg'; ?>" class="img-fluid" alt="Profile Picture" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; display: block;">
            </div>
            <div class="col-md-8">
                <h3><?php echo $user['firstName'] . ' ' . $user['lastName']; ?></h3>
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                <p><strong>Phone:</strong> <?php echo $user['phone']; ?></p>
                <p><strong>Bio:</strong> <?php echo $user['bio']; ?></p>
                <p><strong>Vehicle:</strong> <?php echo $user['vehicle']; ?></p>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editModal">Edit Personal Details</button>
            </div>
        </div>
    </div>

    <!-- Edit Personal Details Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Personal Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo $user['firstName']; ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo $user['lastName']; ?>" required>
                            </div>
                        </div>
                        

                        <div class="form-group">
                            <label for="profilePicture">Profile Picture</label>
                            <input type="file" class="form-control-file" id="profilePicture" name="profilePicture" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob" value="<?php echo $user['dob']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" required><?php echo $user['bio']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="vehicle">Vehicle</label>
                            <input type="text" class="form-control" id="vehicle" name="vehicle" value="<?php echo $user['vehicle']; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>