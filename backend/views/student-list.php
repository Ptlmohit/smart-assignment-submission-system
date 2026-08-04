<?php
session_start();
include "../config/db.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$teacher_name = $_SESSION['user_name'];


$query = "SELECT u.id, u.name, u.email, u.course, 
          (SELECT COUNT(*) FROM submissions WHERE student_id = u.id) as total_submissions
          FROM users u 
          WHERE u.role = 'student' 
          ORDER BY u.name ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Directory | SASS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .active-link { background: #eef2ff; color: #4f46e5; border-right: 4px solid #4f46e5; }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

<nav class="glass-nav fixed w-full top-0 z-50">
    <div class="flex justify-between items-center px-6 py-4">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition"><i data-lucide="menu" class="w-6 h-6"></i></button>
            <div class="flex items-center gap-2 text-indigo-600 font-bold text-xl">
                 <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg shadow-indigo-200"><i data-lucide="briefcase" class="text-white w-5 h-5"></i></div>
                SASS Teacher
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="../auth/logout.php" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl border border-rose-100 hover:bg-rose-100 transition font-medium text-sm">
                <i data-lucide="log-out" class="w-4 h-4 inline mr-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
    <div class="px-4 py-8 space-y-2">
        <a href="teacher-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
        <a href="manage-assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="folder-plus" class="w-5 h-5"></i> Manage Assignments</a>
        <a href="view-submissions.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="clipboard-check" class="w-5 h-5"></i> Submissions</a>
        <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Admin Tools</div>
        <a href="manage-subjects.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layers" class="w-5 h-5"></i> Manage Subjects</a>
        <a href="student-list.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="users" class="w-5 h-5"></i> Student Directory</a>
        <a href="teacher-profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="settings" class="w-5 h-5"></i> Profile</a>
    </div>
</aside>

<main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
    <div class="mb-8" data-aos="fade-down">
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Student Directory</h2>
        <p class="text-slate-500 mt-1">Manage and monitor enrollment and student performance.</p>
    </div>

    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm mb-8 flex flex-col md:flex-row gap-4 items-center" data-aos="fade-up">
        <div class="flex-1 relative w-full">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" id="studentSearch" onkeyup="filterStudents()" placeholder="Search students by name or email..." 
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
        </div>
        <a href="add-student.php" class="w-full md:w-auto px-6 py-2.5 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Add Student
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0" id="studentTable">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                        <th class="px-6 py-5">Roll No.</th>
                        <th class="px-6 py-5">Profile</th>
                        <th class="px-6 py-5">Course</th>
                        <th class="px-6 py-5">Email</th>
                        <th class="px-6 py-5 text-center">Engagement</th>
                        <th class="px-6 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors student-row">
                            <td class="px-6 py-5">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg border border-slate-200">
                                    #<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                    </div>
                                    <span class="font-bold text-slate-700 student-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[11px] font-black px-3 py-1 bg-blue-50 text-blue-600 rounded-full border border-blue-100 uppercase student-course">
                                    <?php echo htmlspecialchars($row['course'] ?? 'B.Voc SD'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-medium text-slate-600 student-email"><?php echo htmlspecialchars($row['email']); ?></span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-slate-700"><?php echo $row['total_submissions']; ?></span>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Submissions</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="student-report.php?id=<?php echo $row['id']; ?>" class="p-2 bg-slate-50 text-slate-400 rounded-xl hover:text-indigo-600 transition" title="View Progress">
                                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                                    </a>
                                    <a href="mailto:<?php echo $row['email']; ?>" class="p-2 bg-slate-50 text-slate-400 rounded-xl hover:text-rose-600 transition" title="Message Student">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="py-24 text-center opacity-30 font-bold">No students registered</td></tr>
                    <?php endif; ?>
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

    function filterStudents() {
        let input = document.getElementById('studentSearch').value.toLowerCase();
        let rows = document.getElementsByClassName('student-row');
        for (let i = 0; i < rows.length; i++) {
            let name = rows[i].querySelector('.student-name').innerText.toLowerCase();
            let email = rows[i].querySelector('.student-email').innerText.toLowerCase();
            rows[i].style.display = (name.includes(input) || email.includes(input)) ? "" : "none";
        }
    }
</script>
</body>
</html>