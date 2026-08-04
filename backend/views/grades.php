<?php
session_start();
include "../config/db.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../frontend/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$query = "SELECT s.marks, s.feedback, s.submitted_at, a.title, a.subject 
          FROM submissions s
          JOIN assignments a ON s.assignment_id = a.id
          WHERE s.student_id = ? AND s.marks IS NOT NULL
          ORDER BY s.submitted_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 3. Calculate Average Grade for Header
$avg_query = mysqli_query($conn, "SELECT AVG(marks) as average FROM submissions WHERE student_id = '$user_id' AND marks IS NOT NULL");
$avg_data = mysqli_fetch_assoc($avg_query);
$overall_avg = $avg_data['average'] ? round($avg_data['average'], 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Grades | SASS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .active-link { background: #eff6ff; color: #2563eb; border-right: 4px solid #2563eb; }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
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
                <div class="bg-blue-600 p-1.5 rounded-lg shadow-lg shadow-blue-200">
                    <i data-lucide="graduation-cap" class="text-white w-5 h-5"></i>
                </div>
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">SASS</h1>
            </div>
        </div>
        <div class="bg-blue-50 px-4 py-1.5 rounded-full border border-blue-100">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">GPA: <?php echo $overall_avg; ?>%</span>
        </div>
    </div>
</nav>

<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
    <div class="px-4 py-8 space-y-2">
        <a href="student-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
        </a>
        <a href="assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
            <i data-lucide="book-open" class="w-5 h-5"></i> My Assignments
        </a>
        <a href="grades.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group">
            <i data-lucide="award" class="w-5 h-5"></i> My Grades
        </a>
        <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Account</div>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
            <i data-lucide="user" class="w-5 h-5"></i> Profile
        </a>
    </div>
</aside>

<main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
    <div class="mb-8" data-aos="fade-down">
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Academic Achievement</h2>
        <p class="text-slate-500 mt-1">Review your scores and instructor feedback for all graded work.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                        <th class="px-6 py-5">Assignment</th>
                        <th class="px-6 py-5">Subject</th>
                        <th class="px-6 py-5">Submission Date</th>
                        <th class="px-6 py-5">Result</th>
                        <th class="px-6 py-5">Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $gradeColor = $row['marks'] >= 75 ? 'emerald' : ($row['marks'] >= 40 ? 'blue' : 'rose');
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5">
                                <span class="font-bold text-slate-700 block"><?php echo htmlspecialchars($row['title']); ?></span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-semibold text-slate-500"><?php echo htmlspecialchars($row['subject']); ?></span>
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-500">
                                <?php echo date('d M Y', strtotime($row['submitted_at'])); ?>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <span class="text-lg font-black text-<?php echo $gradeColor; ?>-600"><?php echo $row['marks']; ?>%</span>
                                    <div class="w-16 h-1 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-<?php echo $gradeColor; ?>-500" style="width: <?php echo $row['marks']; ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <p class="text-xs text-slate-500 italic max-w-xs leading-relaxed">
                                    "<?php echo $row['feedback'] ? htmlspecialchars($row['feedback']) : 'Well done on your submission.'; ?>"
                                </p>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-24 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <i data-lucide="award" class="w-16 h-16 mb-4"></i>
                                    <p class="text-xl font-bold">No graded work found</p>
                                    <p class="text-sm">Grades will appear here once your instructors review your submissions.</p>
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
    AOS.init({ duration: 800, once: true });
    lucide.createIcons();

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar-hidden');
        document.getElementById('overlay').classList.toggle('hidden');
    }
</script>
</body>
</html>