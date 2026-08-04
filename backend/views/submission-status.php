<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$assignment_id = $_GET['id'] ?? null;

$asg_res = mysqli_query($conn, "SELECT title, subject, due_date FROM assignments WHERE id = '$assignment_id'");
$asg = mysqli_fetch_assoc($asg_res);

$query = "SELECT u.name, u.email, s.submitted_at, s.file_path, s.id AS submission_id,
          IF(s.id IS NULL, 'Pending', 'Submitted') AS status
          FROM users u
          LEFT JOIN submissions s ON u.id = s.student_id AND s.assignment_id = '$assignment_id'
          WHERE u.role = 'student'
          ORDER BY status DESC, u.name ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Submission Report | SASS</title>
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
            background: #eef2ff;
            color: #4f46e5;
            border-right: 4px solid #4f46e5;
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
                <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg shadow-indigo-200">
                        <i data-lucide="briefcase" class="text-white w-5 h-5"></i>
                    </div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-blue-600">SASS Teacher</h1>
                </div>
            </div>
            <a href="manage-assignments.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
            </a>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="teacher-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="manage-assignments.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group">
                <i data-lucide="folder-plus" class="w-5 h-5"></i> Manage Assignments
            </a>
            <a href="view-submissions.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="clipboard-check" class="w-5 h-5"></i> Submissions
            </a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4" data-aos="fade-down">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo htmlspecialchars($asg['title']); ?></h2>
                <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">Subject: <?php echo htmlspecialchars($asg['subject']); ?> | Due: <?php echo date('d M Y', strtotime($asg['due_date'])); ?></p>
            </div>
            <a href="export-submissions.php?id=<?php echo $assignment_id; ?>" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg hover:bg-emerald-700 transition flex items-center gap-2">
                <i data-lucide="download" class="w-5 h-5"></i> Download Report
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                            <th class="px-6 py-5">Student Information</th>
                            <th class="px-6 py-5">Status</th>
                            <th class="px-6 py-5">Submitted At</th>
                            <th class="px-6 py-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <span class="font-bold text-slate-700 block"><?php echo htmlspecialchars($row['name']); ?></span>
                                    <span class="text-xs text-slate-400"><?php echo htmlspecialchars($row['email']); ?></span>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                <?php echo $row['status'] == 'Submitted' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-500">
                                    <?php echo $row['submitted_at'] ? date('d M Y, h:i A', strtotime($row['submitted_at'])) : '---'; ?>
                                </td>
                                <td class="px-6 py-5 text-right space-x-2">
                                    <?php if ($row['status'] == 'Submitted'): ?>
                                        <a href="review-copy.php?id=<?php echo $row['submission_id']; ?>"
                                            class="p-2 inline-block bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition shadow-sm"
                                            title="Check for Copies">
                                            <i data-lucide="copy-check" class="w-4 h-4"></i>
                                        </a>
                                        <a href="../../uploads/<?php echo $row['file_path']; ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs underline decoration-2">View File</a>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-xs italic">No file</span>
                                    <?php endif; ?>
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
        AOS.init({
            duration: 800,
            once: true
        });
        lucide.createIcons();

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-hidden');
            document.getElementById('overlay').classList.toggle('hidden');
        }
    </script>

</body>

</html>