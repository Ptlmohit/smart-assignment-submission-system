<?php
session_start();
include "../config/db.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    
    $query = "TRUNCATE TABLE system_logs";
    
    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "Error: Could not clear logs.";
    }
}
?>