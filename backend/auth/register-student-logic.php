<?php
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']); // Manual ID / Roll No
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $raw_password = $_POST['password'];
    $role = 'student';

    $password_regex = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';
    
    if (!preg_match($password_regex, $raw_password)) {
        echo "Error: Password must be at least 8 characters, containing 1 uppercase letter and 1 number.";
        exit();
    }

    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (id, name, email, course, password, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    mysqli_stmt_bind_param($stmt, "isssss", $student_id, $name, $email, $course, $hashed_password, $role);

    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Error: Roll Number or Email already registered.";
    }
}
?>