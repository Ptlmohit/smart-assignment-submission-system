<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sass_db"; 
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function logActivity($conn, $user_id, $user_name, $action, $description) {
    $stmt = mysqli_prepare($conn, "INSERT INTO system_logs (user_id, user_name, action, description) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $user_name, $action, $description);
    mysqli_stmt_execute($stmt);
}
?>