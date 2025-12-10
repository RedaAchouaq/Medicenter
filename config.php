<?php
$host = "localhost";   // or "127.0.0.1"
$user = "root";        // default WAMP user
$pass = "";            // default WAMP password (empty)
$db   = "Homepaged";   // the database you created in phpMyAdmin

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
