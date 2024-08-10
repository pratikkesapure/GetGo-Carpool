<?php
session_start();
// Include database connection
include 'db.php';

// Check if ride ID is provided
if (isset($_GET['ride_id'])) {
    $ride_id = $_GET['ride_id'];

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

    // Query to fetch chat messages for the given ride ID
    $query = "SELECT u.firstName, u.lastName, c.message, c.timestamp, c.incoming_msg_id, c.outgoing_msg_id
              FROM chat c
              JOIN users u ON c.user_id = u.id
              WHERE c.ride_id = ?
              ORDER BY c.timestamp ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare the chat messages array
    $chat_messages = array();

    while ($row = $result->fetch_assoc()) {
        $sender = $row['firstName'] . ' ' . $row['lastName'];
        $message = $row['message'];
        $timestamp = $row['timestamp'];
        $incoming_msg_id = $row['incoming_msg_id'];
        $outgoing_msg_id = $row['outgoing_msg_id'];

        $is_outgoing = ($outgoing_msg_id === $user_id);

        $chat_messages[] = array(
            'sender' => $sender,
            'message' => $message,
            'timestamp' => $timestamp,
            'is_outgoing' => $is_outgoing
        );
    }

    // Return chat messages as JSON
    echo json_encode($chat_messages);

    $stmt->close();
} else {
    echo json_encode(array('error' => 'Ride ID not provided.'));
}

// Close database connection
$conn->close();
?>