<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../frontend/index.html");
    exit();
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'All Status';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT a.*, u.name as student_name, u.email as student_email 
          FROM assignments a 
          JOIN users u ON a.student_id = u.id 
          WHERE 1=1";

if ($status_filter !== 'All Status') {
    $query .= " AND a.status = '$status_filter'";
}
if (!empty($search)) {
    $query .= " AND (a.title LIKE '%$search%' OR u.name LIKE '%$search%')";
}

$query .= " ORDER BY a.due_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Assignments | SASS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">
    <aside class="w-64 bg-slate-800 text-white shadow-lg hidden md:block">
        <div class="p-6 text-2xl font-bold border-b border-slate-700">SASS Admin</div>
        <nav class="px-4 py-6 space-y-2 text-sm">
            <a href="admin-dashboard.php" class="block px-4 py-2 rounded hover:bg-slate-700">Dashboard</a>
            <a href="manage-users.php" class="block px-4 py-2 rounded hover:bg-slate-700">Manage Users</a>
            <a href="system-assignments.php" class="block px-4 py-2 rounded bg-blue-600 font-semibold">All Assignments</a>
            <a href="../auth/logout.php" class="block px-4 py-2 rounded text-red-400 hover:bg-slate-700 mt-10">Logout</a>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Global Assignment Oversight</h1>
            <p class="text-gray-500 text-sm">Monitoring all student-teacher interactions and submissions.</p>
        </header>

        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <input type="text" name="search" placeholder="Search title or student..." value="<?php echo htmlspecialchars($search); ?>"
                       class="border rounded px-4 py-2 w-80 focus:ring-2 focus:ring-blue-500 outline-none" />
                
                <select name="status" onchange="this.form.submit()" class="border rounded px-4 py-2 outline-none">
                    <option <?php echo $status_filter == 'All Status' ? 'selected' : ''; ?>>All Status</option>
                    <option <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option <?php echo $status_filter == 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
                </select>

                <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded hover:bg-slate-700">Search</button>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="p-4 font-semibold text-slate-700">Student</th>
                        <th class="p-4 font-semibold text-slate-700">Assignment Title</th>
                        <th class="p-4 font-semibold text-slate-700">Subject</th>
                        <th class="p-4 font-semibold text-slate-700">Deadline</th>
                        <th class="p-4 font-semibold text-slate-700">Status</th>
                        <th class="p-4 font-semibold text-slate-700">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4">
                            <div class="font-medium text-slate-800"><?php echo htmlspecialchars($row['student_name']); ?></div>
                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($row['student_email']); ?></div>
                        </td>
                        <td class="p-4 text-gray-700 font-medium"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td class="p-4 text-gray-600"><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td class="p-4 text-gray-600"><?php echo date('d M Y', strtotime($row['due_date'])); ?></td>
                        <td class="p-4">
                            <?php 
                                $statusColor = $row['status'] == 'Submitted' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                            ?>
                            <span class="<?php echo $statusColor; ?> px-2 py-1 rounded text-xs font-bold uppercase">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <?php echo $row['marks'] !== null ? "<span class='font-bold text-blue-600'>{$row['marks']}</span>" : "—"; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">No assignments found in the system.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>