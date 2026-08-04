<?php
session_start();
include "../config/db.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: student-list.php");
    exit();
}

$student_id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. Fetch Student Details
$student_query = mysqli_query($conn, "SELECT name, email FROM users WHERE id = '$student_id' AND role = 'student'");
$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    die("Student not found.");
}

// 4. Fetch Submission Stats
$stats_query = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN marks IS NOT NULL THEN 1 ELSE 0 END) as graded,
    AVG(marks) as avg_grade
    FROM submissions WHERE student_id = '$student_id'");
$stats = mysqli_fetch_assoc($stats_query);

// 5. Fetch Detailed Submission History
$history_query = "SELECT s.id, a.title, a.subject, s.marks, s.submitted_at, s.feedback 
                  FROM submissions s 
                  JOIN assignments a ON s.assignment_id = a.id 
                  WHERE s.student_id = ? 
                  ORDER BY s.submitted_at DESC";
$stmt = mysqli_prepare($conn, $history_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$history_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Report | SASS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

<nav class="glass-nav fixed w-full top-0 z-50 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
        <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg shadow-indigo-200">
            <i data-lucide="line-chart" class="text-white w-5 h-5"></i>
        </div>
        <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-blue-600">Performance Report</h1>
    </div>
    <a href="student-list.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Directory
    </a>
</nav>

<main class="max-w-6xl mx-auto pt-28 px-6 pb-12">
    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm mb-8 flex flex-col md:flex-row justify-between items-center gap-6" data-aos="fade-down">
        <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-3xl font-black">
                <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-800"><?php echo htmlspecialchars($student['name']); ?></h2>
                <p class="text-slate-500 font-medium"><?php echo htmlspecialchars($student['email']); ?></p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="text-center px-6 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Avg. Grade</p>
                <p class="text-2xl font-black text-indigo-600"><?php echo round($stats['avg_grade'] ?? 0, 1); ?>%</p>
            </div>
            <div class="text-center px-6 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Submissions</p>
                <p class="text-2xl font-black text-slate-800"><?php echo $stats['total']; ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
        <div class="p-6 border-b border-slate-50">
            <h3 class="font-bold text-slate-800">Academic History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-widest font-black">
                        <th class="px-6 py-4">Assignment</th>
                        <th class="px-6 py-4">Subject</th>
                        <th class="px-6 py-4">Submitted</th>
                        <th class="px-6 py-4">Grade</th>
                        <th class="px-6 py-4">Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (mysqli_num_rows($history_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-5 font-bold text-slate-700"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="px-6 py-5 text-sm font-semibold text-slate-500"><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td class="px-6 py-5 text-sm text-slate-500"><?php echo date('d M Y', strtotime($row['submitted_at'])); ?></td>
                            <td class="px-6 py-5">
                                <?php if ($row['marks']): ?>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-full border border-emerald-100">
                                        <?php echo $row['marks']; ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black rounded-full border border-amber-100">PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-400 italic">
                                <?php echo $row['feedback'] ? htmlspecialchars($row['feedback']) : 'No feedback given yet.'; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-20 text-center opacity-30 font-bold">No academic records found for this student.</td>
                        </tr>
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
</script>
</body>
</html>