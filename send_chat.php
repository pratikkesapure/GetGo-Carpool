<?php
session_start();
// Include database connection
include 'db.php';

// Check if ride ID and message are provided
if (isset($_POST['ride_id'], $_POST['message'])) {
    $ride_id = $_POST['ride_id'];
    $message = $_POST['message'];

    // Get the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Query to get the ride creator's user ID
    $query = "SELECT user_id FROM rides WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $ride_creator_id = $row['user_id'];
    $stmt->close();

    // Determine if the message is incoming or outgoing
    $incoming_msg_id = null;
    $outgoing_msg_id = null;
    if ($user_id === $ride_creator_id) {
        // Outgoing message
        $outgoing_msg_id = $user_id;
        $incoming_msg_id = ($ride_creator_id === $user_id) ? null : $ride_creator_id;
    } else {
        // Incoming message
        $incoming_msg_id = $user_id;
        $outgoing_msg_id = $ride_creator_id;
    }

    // Query to insert the new chat message
    $query = "INSERT INTO chat (ride_id, user_id, incoming_msg_id, outgoing_msg_id, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiss", $ride_id, $user_id, $incoming_msg_id, $outgoing_msg_id, $message);

    if ($stmt->execute()) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Error sending message.'));
    }

    $stmt->close();
} else {
    echo json_encode(array('success' => false, 'error' => 'Ride ID or message not provided.'));
}

// Close database connection
$conn->close();
?>