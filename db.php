<?php
// Admin Database Connection
$admin_host = "localhost";
$admin_user = "root";
$admin_pass = "";
$admin_db = "admin_db";

// Election Database Connection
$election_host = "localhost";
$election_user = "root";
$election_pass = "";
$election_db = "election_db";

// Connect to Admin DB
$admin_conn = mysqli_connect($admin_host, $admin_user, $admin_pass, $admin_db);
if (!$admin_conn) {
    die("Admin Database connection failed: " . mysqli_connect_error());
}

// Connect to Election DB
$election_conn = mysqli_connect($election_host, $election_user, $election_pass, $election_db);
if (!$election_conn) {
    die("Election Database connection failed: " . mysqli_connect_error());
}
?>
