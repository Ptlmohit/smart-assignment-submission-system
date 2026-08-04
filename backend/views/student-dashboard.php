<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../frontend/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM assignments");
$total_data = mysqli_fetch_assoc($total_query);
$total = $total_data['total'];

$sub_query = mysqli_query($conn, "SELECT COUNT(*) as submitted FROM submissions WHERE student_id = '$user_id'");
$sub_data = mysqli_fetch_assoc($sub_query);
$submitted = $sub_data['submitted'];

$pending = $total - $submitted;

$recent_query = mysqli_query($conn, "SELECT * FROM assignments ORDER BY id DESC LIMIT 5");
$assignments = [];
while ($row = mysqli_fetch_assoc($recent_query)) {
    $assign_id = $row['id'];
    $check = mysqli_query($conn, "SELECT id FROM submissions WHERE assignment_id = '$assign_id' AND student_id = '$user_id'");
    $row['current_status'] = (mysqli_num_rows($check) > 0) ? 'Submitted' : 'Pending';
    $assignments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | SASS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            scroll-behavior: smooth;
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
            background: #eff6ff;
            color: #2563eb;
            border-right: 4px solid #2563eb;
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
                    <div class="bg-blue-600 p-1.5 rounded-lg shadow-lg shadow-blue-200">
                        <i data-lucide="graduation-cap" class="text-white w-5 h-5"></i>
                    </div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">SASS</h1>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:block text-right">
                    <p class="text-sm font-bold leading-none"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Student</p>
                </div>
                <a href="../auth/logout.php" class="flex items-center gap-2 bg-red-50 text-red-600 px-4 py-2 rounded-xl border border-red-100 hover:bg-red-100 transition font-medium">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="student-dashboard.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="book-open" class="w-5 h-5 group-hover:text-blue-600"></i> My Assignments
            </a>
            <a href="grades.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="award" class="w-5 h-5 group-hover:text-blue-600"></i> My Grades
            </a>
            <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Account</div>
            <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
                <i data-lucide="user" class="w-5 h-5 group-hover:text-blue-600"></i> Profile
            </a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">

        <div class="mb-8" data-aos="fade-down">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Dashboard Overview</h2>
            <p class="text-slate-500 mt-1">Check your assignment progress and upcoming deadlines.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-blue-50 p-3 rounded-2xl text-blue-600"><i data-lucide="layers" class="w-6 h-6"></i></div>
                </div>
                <h3 class="text-slate-500 text-sm font-medium">Total Assignments</h3>
                <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $total; ?></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-emerald-500/5 transition duration-300" data-aos="fade-up" data-aos-delay="200">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-emerald-50 p-3 rounded-2xl text-emerald-600"><i data-lucide="check-circle-2" class="w-6 h-6"></i></div>
                </div>
                <h3 class="text-slate-500 text-sm font-medium">Submitted</h3>
                <p class="text-4xl font-black text-emerald-600 mt-1"><?php echo $submitted; ?></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-rose-500/5 transition duration-300" data-aos="fade-up" data-aos-delay="300">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-rose-50 p-3 rounded-2xl text-rose-600"><i data-lucide="clock-3" class="w-6 h-6"></i></div>
                </div>
                <h3 class="text-slate-500 text-sm font-medium">Pending Tasks</h3>
                <p class="text-4xl font-black text-rose-600 mt-1"><?php echo $pending; ?></p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="400">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-white">
                <h2 class="text-xl font-bold text-slate-800">Your Current Assignments</h2>
                <div class="flex gap-2">
                    <button class="p-2 hover:bg-slate-50 rounded-lg transition text-slate-400"><i data-lucide="filter" class="w-4 h-4"></i></button>
                    <button class="p-2 hover:bg-slate-50 rounded-lg transition text-slate-400"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                            <th class="px-6 py-4">Assignment Info</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4">Deadline</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (count($assignments) > 0): ?>
                            <?php foreach ($assignments as $row):
                                $isOverdue = $row['current_status'] == 'Pending' && strtotime($row['due_date']) < time();
                                $statusLabel = $isOverdue ? 'Overdue' : $row['current_status'];
                                $statusColor = $row['current_status'] == 'Submitted' ? 'emerald' : ($isOverdue ? 'rose' : 'orange');
                            ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-5">
                                        <span class="font-bold text-slate-700 block"><?php echo htmlspecialchars($row['title']); ?></span>
                                        <span class="text-xs text-slate-400">ID: #ASG-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            <span class="text-sm font-medium text-slate-600"><?php echo htmlspecialchars($row['subject']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2 text-sm <?php echo $isOverdue ? 'text-rose-500 font-bold' : 'text-slate-500'; ?>">
                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                            <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 bg-<?php echo $statusColor; ?>-50 text-<?php echo $statusColor; ?>-600 text-[10px] font-black rounded-full border border-<?php echo $statusColor; ?>-100 uppercase tracking-wider">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <?php if ($row['current_status'] == 'Submitted'): ?>
                                            <div class="flex items-center gap-2 text-emerald-600 text-xs font-bold">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i> Completed
                                            </div>
                                        <?php elseif ($isOverdue): ?>
                                            <div class="flex flex-col">
                                                <button class="inline-flex items-center gap-2 bg-slate-200 text-slate-400 px-5 py-2 rounded-xl text-xs font-bold cursor-not-allowed" disabled>
                                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i> Closed
                                                </button>
                                                <span class="text-[9px] text-rose-500 font-bold mt-1 uppercase tracking-tighter">Deadline Passed</span>
                                            </div>
                                        <?php else: ?>
                                            <a href="submit-assignment.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                                                <i data-lucide="upload" class="w-3.5 h-3.5"></i> Submit Now
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-24 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-20">
                                        <i data-lucide="file-x" class="w-16 h-16 mb-4"></i>
                                        <p class="text-xl font-bold">No Recent Assignments</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity"></div>

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
            overlay.classList.toggle('hidden');
        }
    </script>

</body>

</html>