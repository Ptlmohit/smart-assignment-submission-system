<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'student') {
    header("Location: ../../frontend/index.html");
    exit();
}

$assign_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_date'])) {
    $new_date = $_POST['new_due_date'];
    $id = $_POST['assign_id'];

    $query = "UPDATE assignments SET due_date = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $new_date, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Deadline extended successfully!'); window.location.href='view-submissions.php';</script>";
        exit();
    }
}

$res = mysqli_query($conn, "SELECT * FROM assignments WHERE id = '$assign_id'");
$asg = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Extend Deadline | SASS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-10">
    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-sm border">
        <h2 class="text-xl font-bold mb-4">Extend Deadline</h2>
        <p class="text-sm text-slate-500 mb-6">Assignment: <?php echo htmlspecialchars($asg['title']); ?></p>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="assign_id" value="<?php echo $asg['id']; ?>">
            <div>
                <label class="block text-sm font-medium mb-1">New Due Date & Time</label>
                <input type="datetime-local" name="new_due_date" required 
                       class="w-full border rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" name="update_date" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                Update Deadline
            </button>
        </form>
    </div>
</body>
</html>