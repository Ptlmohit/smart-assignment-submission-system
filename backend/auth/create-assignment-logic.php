<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);

    $teacher_id = $_SESSION['user_id']; 

   
    $query = mysqli_prepare($conn, "INSERT INTO assignments (title, subject, due_date, teacher_id, status) VALUES (?, ?, ?, ?, 'Pending')");
    mysqli_stmt_bind_param($query, "sssi", $title, $subject, $due_date, $teacher_id);

    if (mysqli_stmt_execute($query)) {
        echo "success";
    } else {
        echo "Error creating assignment: " . mysqli_error($conn);
    }
}
?>