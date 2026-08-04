<?php
session_start();
include "../config/db.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../frontend/index.html");
    exit();
}

$admin_name = $_SESSION['user_name'];

$teacher_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role = 'teacher'"))['c'];
$student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role = 'student'"))['c'];
$total_assignments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM assignments"))['c'];
$total_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM submissions"))['c'];

$recent_users = mysqli_query($conn, "SELECT name, email, role, id FROM users ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | SASS</title>
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

<nav class="glass-nav fixed w-full top-0 z-50">
    <div class="flex justify-between items-center px-6 py-4">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition"><i data-lucide="menu" class="w-6 h-6"></i></button>
            <div class="flex items-center gap-2">
                <div class="bg-slate-900 p-1.5 rounded-lg shadow-lg">
                    <i data-lucide="shield-check" class="text-white w-5 h-5"></i>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">SASS Admin</h1>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm font-bold">
            <span class="hidden md:inline text-slate-400 uppercase tracking-widest text-[10px]">Superuser: <?php echo htmlspecialchars($admin_name); ?></span>
            <a href="../auth/logout.php" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl border border-rose-100 hover:bg-rose-100 transition"><i data-lucide="log-out" class="w-4 h-4 inline mr-1"></i> Logout</a>
        </div>
    </div>
</nav>

<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
    <div class="px-4 py-8 space-y-2">
        <a href="admin-dashboard.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="layout-grid" class="w-5 h-5"></i> System Overview</a>
        <a href="manage-users.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="users" class="w-5 h-5"></i> User Management</a>
        <a href="all-assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="file-text" class="w-5 h-5"></i> Global Assignments</a>
        <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Configurations</div>
        <a href="system-logs.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="activity" class="w-5 h-5"></i> Activity Logs</a>
    </div>
</aside>

<main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
    <div class="mb-8" data-aos="fade-down">
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Control Center</h2>
        <p class="text-slate-500 mt-1">Global management of the Smart Assignment Submission System.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up" data-aos-delay="100">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-blue-50 p-3 rounded-2xl text-blue-600"><i data-lucide="graduation-cap" class="w-6 h-6"></i></div>
            </div>
            <h3 class="text-slate-500 text-sm font-medium">Total Students</h3>
            <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $student_count; ?></p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up" data-aos-delay="200">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-indigo-50 p-3 rounded-2xl text-indigo-600"><i data-lucide="briefcase" class="w-6 h-6"></i></div>
            </div>
            <h3 class="text-slate-500 text-sm font-medium">Total Teachers</h3>
            <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $teacher_count; ?></p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up" data-aos-delay="300">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-emerald-50 p-3 rounded-2xl text-emerald-600"><i data-lucide="file-check" class="w-6 h-6"></i></div>
            </div>
            <h3 class="text-slate-500 text-sm font-medium">Assignments</h3>
            <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $total_assignments; ?></p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up" data-aos-delay="400">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-amber-50 p-3 rounded-2xl text-amber-600"><i data-lucide="upload-cloud" class="w-6 h-6"></i></div>
            </div>
            <h3 class="text-slate-500 text-sm font-medium">Submissions</h3>
            <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $total_submissions; ?></p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="500">
        <div class="p-6 border-b border-slate-50">
            <h2 class="text-xl font-bold text-slate-800">Recently Registered Users</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                        <th class="px-6 py-5">User Profile</th>
                        <th class="px-6 py-5">Role</th>
                        <th class="px-6 py-5">Email</th>
                        <th class="px-6 py-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php while($user = mysqli_fetch_assoc($recent_users)): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <span class="font-bold text-slate-700"><?php echo htmlspecialchars($user['name']); ?></span>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border <?php echo $user['role'] == 'admin' ? 'bg-rose-50 text-rose-600 border-rose-100' : ($user['role'] == 'teacher' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-blue-50 text-blue-600 border-blue-100'); ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-5 text-sm text-slate-500"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-5 text-right">
                            <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="text-slate-400 hover:text-slate-900 transition"><i data-lucide="more-horizontal"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-30 hidden lg:hidden"></div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    lucide.createIcons();
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar-hidden');
        document.getElementById('overlay').classList.toggle('hidden');
    }
</script>
</body>
</html>