<?php
session_start();
include "../config/db.php"; 


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../frontend/index.html");
    exit();
}


$query = "SELECT a.*, u.name as teacher_name 
          FROM assignments a 
          JOIN users u ON a.teacher_id = u.id 
          ORDER BY a.id DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Assignments | SASS Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .active-link { background: #f8fafc; color: #0f172a; border-right: 4px solid #0f172a; }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

<nav class="glass-nav fixed w-full top-0 z-50 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition"><i data-lucide="menu" class="w-6 h-6"></i></button>
        <div class="flex items-center gap-2">
            <div class="bg-slate-900 p-1.5 rounded-lg shadow-lg"><i data-lucide="file-text" class="text-white w-5 h-5"></i></div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Assignment Master</h1>
        </div>
    </div>
    <a href="admin-dashboard.php" class="text-sm font-bold text-slate-500 hover:text-slate-900 flex items-center gap-1">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
    </a>
</nav>

<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
    <div class="px-4 py-8 space-y-2">
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layout-grid" class="w-5 h-5"></i> System Overview</a>
        <a href="manage-users.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="users" class="w-5 h-5"></i> User Management</a>
        <a href="all-assignments.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="file-text" class="w-5 h-5"></i> Global Assignments</a>
        <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Configurations</div>
        <a href="manage-subjects.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layers" class="w-5 h-5"></i> Subject Master</a>
    </div>
</aside>

<main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
    <div class="mb-8" data-aos="fade-down">
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">System-Wide Assignments</h2>
        <p class="text-slate-500 mt-1">Audit and monitor all tasks published across the SASS platform.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                        <th class="px-6 py-5">Assignment Title</th>
                        <th class="px-6 py-5">Subject</th>
                        <th class="px-6 py-5">Instructor</th>
                        <th class="px-6 py-5">Due Date</th>
                        <th class="px-6 py-5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-5 font-bold text-slate-700"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="px-6 py-5">
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black rounded-full border border-slate-200">
                                    <?php echo htmlspecialchars($row['subject']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-sm font-medium text-slate-600"><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                            <td class="px-6 py-5 text-sm text-slate-400">
                                <i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>
                                <?php echo date('d M Y', strtotime($row['due_date'])); ?>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border <?php echo $row['status'] == 'Pending' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="py-20 text-center opacity-30 font-bold">No assignments found in the system.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    lucide.createIcons();
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar-hidden');
    }
</script>
</body>
</html>