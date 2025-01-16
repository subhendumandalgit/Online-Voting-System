<?php
session_start();
include('db.php');

// Check if voter is logged in
if (!isset($_SESSION['voter_id'])) {
    header("Location: index.html"); // Redirect if the voter is not logged in
    exit();
}

// Get voter_id from session
$voter_id = $_SESSION['voter_id']; // This should be a string or integer
$event_id = $_POST['event_id'];  // Get the selected event
$candidate_id = $_POST['candidate_id'];  // Get the selected candidate

// Ensure the voter_id is properly escaped for the query
$voter_id = mysqli_real_escape_string($election_conn, $voter_id);

// Step 1: Check if voter has already voted for the event
$query = "SELECT * FROM votes WHERE voter_id = '$voter_id' AND event_id = $event_id"; // Enclose $voter_id in quotes
$result = mysqli_query($election_conn, $query);

// If the voter has already voted, redirect back with an error message
if (mysqli_num_rows($result) > 0) {
    $_SESSION['error_message'] = "You have already voted in this event!";
    header("Location: vote.php");
    exit();
}

// Step 2: Insert the vote into the database
$insert_query = "INSERT INTO votes (voter_id, event_id, candidate_id) VALUES ('$voter_id', $event_id, $candidate_id)";

if (mysqli_query($election_conn, $insert_query)) {
    $_SESSION['success_message'] = "Your vote has been submitted successfully!";
} else {
    $_SESSION['error_message'] = "Error submitting your vote: " . mysqli_error($election_conn);
}

header("Location: vote.php");  // Redirect back to voting page
exit();
?>
