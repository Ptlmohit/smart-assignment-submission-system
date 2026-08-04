<?php
session_start();
include "../config/db.php";

if (isset($_POST['submit_btn'])) {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $_SESSION['user_id'];

    $file = $_FILES['submission_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = array('pdf', 'docx', 'zip', 'jpg', 'png');

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 5000000) {

                $newFileName = "sub_" . $assignment_id . "_" . $student_id . "_" . time() . "." . $fileExt;
                $uploadDir = "../uploads/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileDestination = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpName, $fileDestination)) {

                    $dbSavePath = "backend/uploads/" . $newFileName;
                    
                    $query = "INSERT INTO submissions (assignment_id, student_id, file_path, status) VALUES (?, ?, ?, 'Submitted')";
                    $stmt = mysqli_prepare($conn, $query);

                    mysqli_stmt_bind_param($stmt, "iis", $assignment_id, $student_id, $fileDestination);

                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: ../views/assignments.php?upload=success");
                        exit();
                    } else {
                        echo "Database error: " . mysqli_error($conn);
                    }
                } else {
                    echo "Failed to move uploaded file.";
                }
            } else {
                echo "File is too large (Max 5MB).";
            }
        } else {
            echo "There was an error uploading your file.";
        }
    } else {
        echo "Invalid file type.";
    }
}
