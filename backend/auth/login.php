<?php
session_start();
include "../config/db.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    $role  = $_POST['role'];

    $query = "SELECT id, name, password, role FROM users WHERE email = ? AND role = ?";
    $stmt = mysqli_prepare($conn, $query); 
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                echo "success";
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with those credentials.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>