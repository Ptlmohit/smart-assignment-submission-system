<?php
session_start();
include "../config/db.php";


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../frontend/index.html");
    exit();
}

$logs_query = "SELECT sl.*, u.name as user_name 
               FROM system_logs sl 
               JOIN users u ON sl.user_id = u.id 
               ORDER BY sl.timestamp DESC LIMIT 50";

$logs_result = mysqli_query($conn, $logs_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>System Logs | SASS Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .sidebar-transition {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .active-link {
            background: #f8fafc;
            color: #0f172a;
            border-right: 4px solid #0f172a;
        }

        @media (max-width: 1024px) {
            .sidebar-hidden {
                transform: translateX(-100%);
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

    <nav class="glass-nav fixed w-full top-0 z-50">
        <div class="flex justify-between items-center px-6 py-4">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition"><i data-lucide="menu" class="w-6 h-6"></i></button>
                <div class="flex items-center gap-2">
                    <div class="bg-slate-900 p-1.5 rounded-lg shadow-lg"><i data-lucide="activity" class="text-white w-5 h-5"></i></div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">Activity Logs</h1>
                </div>
            </div>
            <a href="admin-dashboard.php" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="admin-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layout-grid" class="w-5 h-5"></i> System Overview</a>
            <a href="manage-users.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="users" class="w-5 h-5"></i> User Management</a>
            <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Configurations</div>
            <a href="system-logs.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="activity" class="w-5 h-5"></i> Activity Logs</a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
        <div class="mb-8 flex justify-between items-end" data-aos="fade-down">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Audit Trail</h2>
                <p class="text-slate-500 mt-1">Review critical system events and security notifications.</p>
            </div>
            <button onclick="clearLogs()" class="bg-rose-50 text-rose-600 px-5 py-2.5 rounded-2xl border border-rose-100 hover:bg-rose-100 transition font-bold text-sm flex items-center gap-2">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Clear History
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                            <th class="px-6 py-5">Timestamp</th>
                            <th class="px-6 py-5">User</th>
                            <th class="px-6 py-5">Action</th>
                            <th class="px-6 py-5">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (mysqli_num_rows($logs_result) > 0): ?>
                            <?php while ($log = mysqli_fetch_assoc($logs_result)): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-5 text-xs text-slate-400 font-medium">
                                        <?php echo date('d M, H:i:s', strtotime($log['timestamp'])); ?>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($log['user_name']); ?></span>
                                        <span class="text-[10px] text-slate-400 block tracking-tight">UID: #<?php echo $log['user_id']; ?></span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border border-slate-200 bg-slate-50 text-slate-600">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-sm text-slate-500 leading-relaxed">
                                        <?php echo htmlspecialchars($log['description']); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-20 text-center opacity-30 font-bold">No system activity logged yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('sidebar-hidden');
            if (overlay) overlay.classList.toggle('hidden');
        }
    </script>
</body>

</html>