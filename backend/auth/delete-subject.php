<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    $subject_id = mysqli_real_escape_string($conn, $_POST['id']);

    $query = mysqli_prepare($conn, "DELETE FROM subjects WHERE id = ?");
    mysqli_stmt_bind_param($query, "i", $subject_id);

    if (mysqli_stmt_execute($query)) {
        echo "success";
    } else {
        echo "Error: Could not delete subject. It might be linked to existing assignments.";
    }
}
?>