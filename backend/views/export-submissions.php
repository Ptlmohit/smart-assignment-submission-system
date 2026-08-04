<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    exit("Unauthorized access.");
}

$assignment_id = $_GET['id'] ?? null;

$asg_res = mysqli_query($conn, "SELECT title FROM assignments WHERE id = '$assignment_id'");
$asg = mysqli_fetch_assoc($asg_res);
$filename = str_replace(' ', '_', $asg['title']) . "_Status_Report.csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

fputcsv($output, array('Student Name', 'Email Address', 'Submission Status', 'Date Submitted'));

$query = "SELECT u.name, u.email, 
          IF(s.id IS NULL, 'Pending', 'Submitted') AS status,
          IFNULL(s.submitted_at, '---') AS submission_date
          FROM users u
          LEFT JOIN submissions s ON u.id = s.student_id AND s.assignment_id = '$assignment_id'
          WHERE u.role = 'student'
          ORDER BY status DESC, u.name ASC";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>