<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    mysqli_begin_transaction($conn);

    try {
        
        $query = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($query, "i", $user_id);
        mysqli_stmt_execute($query);

        mysqli_commit($conn);

        session_unset();
        session_destroy();

        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: Could not delete account. " . $e->getMessage();
    }
}
?>