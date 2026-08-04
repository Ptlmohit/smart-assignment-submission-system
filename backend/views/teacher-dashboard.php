<?php
session_start();
include "../config/db.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['user_name'];

$total_asg_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM assignments WHERE teacher_id = '$teacher_id'");
$total_asg = mysqli_fetch_assoc($total_asg_query)['total'];

$total_sub_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM submissions s 
                                        JOIN assignments a ON s.assignment_id = a.id 
                                        WHERE a.teacher_id = '$teacher_id'");
$total_sub = mysqli_fetch_assoc($total_sub_query)['total'];

$pending_grading_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM submissions s 
                                              JOIN assignments a ON s.assignment_id = a.id 
                                              WHERE a.teacher_id = '$teacher_id' AND s.marks IS NULL");
$pending_grading = mysqli_fetch_assoc($pending_grading_query)['total'];

$recent_subs_query = "SELECT s.*, u.name as student_name, a.title as assignment_title 
                      FROM submissions s
                      JOIN users u ON s.student_id = u.id
                      JOIN assignments a ON s.assignment_id = a.id
                      WHERE a.teacher_id = '$teacher_id'
                      ORDER BY s.id DESC LIMIT 6";
$recent_subs = mysqli_query($conn, $recent_subs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard | SASS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

<nav class="glass-nav fixed w-full top-0 z-50 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
        <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg"><i data-lucide="layout-dashboard" class="text-white w-5 h-5"></i></div>
        <h1 class="text-xl font-bold text-slate-800">SASS Teacher</h1>
    </div>
    <div class="flex items-center gap-4">
        <span class="hidden md:block text-sm font-bold text-slate-600">Welcome, <?php echo htmlspecialchars($teacher_name); ?></span>
        <a href="../auth/logout.php" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest border border-red-100 hover:bg-red-100 transition">Logout</a>
    </div>
</nav>

<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
    <div class="px-4 py-8 space-y-2">
        <a href="teacher-dashboard.php" class="bg-indigo-50 text-indigo-600 flex items-center gap-3 px-4 py-3 rounded-xl font-semibold border-r-4 border-indigo-600 transition">
            <i data-lucide="home" class="w-5 h-5"></i> Overview
        </a>
        <a href="manage-assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Manage Tasks
        </a>
        <a href="view-submissions.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition">
            <i data-lucide="clipboard-list" class="w-5 h-5"></i> Submissions
        </a>
        <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Admin Tools</div>
         <a href="teacher-profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="settings" class="w-5 h-5"></i> Profile</a>

    </div>
</aside>

<main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
    <div class="mb-8" data-aos="fade-down">
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Teacher Overview</h2>
        <p class="text-slate-500 mt-1">Monitor classroom activity and grading progress.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up" data-aos-delay="100">
            <div class="bg-indigo-50 w-12 h-12 rounded-2xl flex items-center justify-center text-indigo-600 mb-4"><i data-lucide="book"></i></div>
            <h3 class="text-slate-500 text-sm font-medium">Active Assignments</h3>
            <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $total_asg; ?></p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up" data-aos-delay="200">
            <div class="bg-emerald-50 w-12 h-12 rounded-2xl flex items-center justify-center text-emerald-600 mb-4"><i data-lucide="check-circle"></i></div>
            <h3 class="text-slate-500 text-sm font-medium">Total Submissions</h3>
            <p class="text-4xl font-black text-emerald-600 mt-1"><?php echo $total_sub; ?></p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up" data-aos-delay="300">
            <div class="bg-amber-50 w-12 h-12 rounded-2xl flex items-center justify-center text-amber-600 mb-4"><i data-lucide="clock"></i></div>
            <h3 class="text-slate-500 text-sm font-medium">Needs Grading</h3>
            <p class="text-4xl font-black text-amber-600 mt-1"><?php echo $pending_grading; ?></p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">Recent Submissions</h3>
            <a href="view-submissions.php" class="text-sm font-bold text-indigo-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-widest font-black">
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Assignment</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php while($row = mysqli_fetch_assoc($recent_subs)): 
                        $isGraded = !is_null($row['marks']);
                    ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5 font-bold text-slate-700"><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td class="px-6 py-5 text-sm text-slate-500"><?php echo htmlspecialchars($row['assignment_title']); ?></td>
                        <td class="px-6 py-5 text-center">
                            <span class="px-3 py-1 <?php echo $isGraded ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'; ?> text-[10px] font-black rounded-full uppercase border">
                                <?php echo $isGraded ? 'Graded' : 'Pending'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="grade-assignment.php?sub_id=<?php echo $row['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                <?php echo $isGraded ? 'Edit' : 'Grade Now'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    lucide.createIcons();
</script>
</body>
</html>