<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$teacher_name = $_SESSION['user_name'];
$teacher_id = $_SESSION['user_id'];

$query = "SELECT s.*, u.name as student_name, a.title as assignment_title, a.subject 
          FROM submissions s
          JOIN users u ON s.student_id = u.id
          JOIN assignments a ON s.assignment_id = a.id
          WHERE a.teacher_id = ? 
          ORDER BY s.submitted_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $teacher_id); 
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Submissions | SASS</title>
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
                <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg shadow-indigo-200">
                        <i data-lucide="briefcase" class="text-white w-5 h-5"></i>
                    </div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-blue-600">SASS Teacher</h1>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline text-xs font-bold text-slate-400 uppercase tracking-widest">Grading Portal</span>
                <a href="../auth/logout.php" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl border border-rose-100 hover:bg-rose-100 transition font-medium text-sm">
                    <i data-lucide="log-out" class="w-4 h-4 inline mr-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="teacher-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="manage-assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="folder-plus" class="w-5 h-5"></i> Manage Assignments
            </a>
            <a href="view-submissions.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group">
                <i data-lucide="clipboard-check" class="w-5 h-5"></i> Submissions
            </a>
            <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Admin Tools</div>
            <a href="student-list.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="users" class="w-5 h-5"></i> Student Directory
            </a>
            <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="settings" class="w-5 h-5"></i> Profile
            </a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
        <div class="mb-8" data-aos="fade-down">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Student Submissions</h2>
            <p class="text-slate-500 mt-1">Review, download, and grade student work efficiently.</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                            <th class="px-6 py-5">Student</th>
                            <th class="px-6 py-5">Assignment Info</th>
                            <th class="px-6 py-5">Submitted At</th>
                            <th class="px-6 py-5">Grade Status</th>
                            <th class="px-6 py-5 text-right">Review</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)):
                                $isGraded = !is_null($row['marks']);
                                $statusColor = $isGraded ? 'emerald' : 'amber';
                            ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                                <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                                            </div>
                                            <span class="font-bold text-slate-700"><?php echo htmlspecialchars($row['student_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-sm font-semibold text-slate-600 block"><?php echo htmlspecialchars($row['assignment_title']); ?></span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase"><?php echo htmlspecialchars($row['subject']); ?></span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2 text-sm text-slate-500">
                                            <i data-lucide="clock" class="w-4 h-4"></i>
                                            <?php echo date('d M, H:i', strtotime($row['submitted_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <?php if ($isGraded): ?>
                                            <div class="flex flex-col">
                                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-full border border-emerald-100 uppercase text-center">Graded</span>
                                                <span class="text-xs font-bold text-slate-700 mt-1 text-center"><?php echo $row['marks']; ?>/100</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black rounded-full border border-amber-100 uppercase tracking-wider">Pending Grade</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="../uploads/<?php echo $row['file_path']; ?>" target="_blank" class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition" title="Download File">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>

                                            <a href="grade-assignment.php?sub_id=<?php echo $row['id']; ?>" class="flex items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                                <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i> <?php echo $isGraded ? 'Edit Grade' : 'Grade'; ?>
                                            </a>

                                            <?php if (!$isGraded && strtotime($row['due_date']) < time()): ?>
                                                <a href="extend-deadline.php?id=<?php echo $row['assignment_id']; ?>" class="bg-orange-500 text-white px-3 py-2 rounded-xl text-xs font-bold hover:bg-orange-600 transition">
                                                    Extend Date
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-24 text-center">
                                    <div class="flex flex-col items-center opacity-30">
                                        <i data-lucide="inbox" class="w-16 h-16 mb-4"></i>
                                        <p class="text-xl font-bold">No submissions received yet</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
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