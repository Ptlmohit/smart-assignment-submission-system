<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $new_name = mysqli_real_escape_string($conn, $_POST['name']);
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];

    $update_name = mysqli_prepare($conn, "UPDATE users SET name = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_name, "si", $new_name, $user_id);
    mysqli_stmt_execute($update_name);
    $_SESSION['user_name'] = $new_name;

    if (!empty($current_pass) && !empty($new_pass)) {
        $query = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($query, "i", $user_id);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);
        $user = mysqli_fetch_assoc($result);

        if (password_verify($current_pass, $user['password'])) {
            $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_pass = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_pass, "si", $hashed_new_pass, $user_id);
            mysqli_stmt_execute($update_pass);
            echo "success";
        } else {
            echo "Incorrect current password.";
        }
    } else {
        echo "success"; 
    }
}
?>