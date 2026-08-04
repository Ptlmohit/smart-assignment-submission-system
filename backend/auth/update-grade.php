<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] === 'teacher') {
    $sub_id = mysqli_real_escape_string($conn, $_POST['sub_id']);
    $marks = mysqli_real_escape_string($conn, $_POST['marks']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    $query = "UPDATE submissions SET marks = ?, feedback = ?, status = 'Graded' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isi", $marks, $feedback, $sub_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>