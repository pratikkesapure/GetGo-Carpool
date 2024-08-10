<?php include 'navbar.php'; ?>
<?php

include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit();
}

// Get the logged-in user's unique ID
$outgoing_id = $_SESSION['unique_id'];

// Retrieve the list of users with whom the logged-in user has conversations and the last message
$sql = "SELECT users.unique_id, users.firstName, users.lastName, users.profilePicture, users.status, messages.msg
FROM users
JOIN (
    SELECT MAX(msg_id) AS latest_msg_id, incoming_msg_id, outgoing_msg_id
    FROM messages
    WHERE incoming_msg_id = {$outgoing_id} OR outgoing_msg_id = {$outgoing_id}
    GROUP BY IF(incoming_msg_id = {$outgoing_id}, outgoing_msg_id, incoming_msg_id)
) AS latest_msgs ON users.unique_id = IF(latest_msgs.incoming_msg_id = {$outgoing_id}, latest_msgs.outgoing_msg_id, latest_msgs.incoming_msg_id)
JOIN messages ON latest_msgs.latest_msg_id = messages.msg_id
WHERE users.unique_id != {$outgoing_id}
ORDER BY latest_msgs.latest_msg_id DESC;";
$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpooling Website - Inbox</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    
    <style>
        .list-group-item img {
            object-fit: cover;
        }
    </style>
    
</head>
<body>

    <div class="container my-5">
        <h2>Inbox</h2>

        <?php if (mysqli_num_rows($query) > 0) { ?>
            <ul class="list-group">
                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                    <li class="list-group-item">
                    <a href="chat.php?user_id=<?php echo $row['unique_id']; ?>" class="d-flex align-items-center">
                            <img src="<?php echo ($row['profilePicture'] != NULL) ? $row['profilePicture'] : 'default_profile.jpg'; ?>" alt="Profile Picture" class="rounded-circle mr-3" width="50" height="50">
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?php echo $row['firstName'] . ' ' . $row['lastName']; ?></h6>
                                <small><?php echo $row['status']; ?></small>
                                <p class="mb-0 text-truncate"><?php echo $row['msg']; ?></p>
                            </div>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>No conversations found.</p>
        <?php } ?>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>