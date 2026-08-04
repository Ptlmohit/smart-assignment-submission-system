<?php
session_start();
include "../config/db.php";

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../frontend/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : 'All Subjects';
$status_filter  = isset($_GET['status']) ? $_GET['status'] : 'All Status';
$search_query   = isset($_GET['search']) ? $_GET['search'] : '';

$subjects_list_query = "SELECT * FROM subjects ORDER BY subject_name ASC";
$subjects_list_result = mysqli_query($conn, $subjects_list_query);

$query = "SELECT * FROM assignments WHERE 1=1";
$params = [];
$types = "";

if ($subject_filter !== 'All Subjects') {
    $query .= " AND subject = ?";
    $params[] = $subject_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $query .= " AND title LIKE ?";
    $params[] = "%$search_query%";
    $types .= "s";
}

$query .= " ORDER BY due_date ASC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Assignments | SASS</title>
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
                <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg"><i data-lucide="menu"></i></button>
                <div class="flex items-center gap-2">
                    <div class="bg-blue-600 p-1.5 rounded-lg shadow-lg"><i data-lucide="graduation-cap" class="text-white"></i></div>
                    <h1 class="text-xl font-bold text-slate-800">SASS</h1>
                </div>
            </div>
            <a href="../auth/logout.php" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl border border-red-100 font-bold text-xs uppercase tracking-widest hover:bg-red-100 transition">Logout</a>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="student-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition"><i data-lucide="layout-dashboard"></i> Dashboard</a>
            <a href="assignments.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition"><i data-lucide="book-open"></i> My Assignments</a>
            <a href="submit-assignment.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition"><i data-lucide="upload-cloud"></i> Submit Work</a>
            <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Account</div>
            <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition"><i data-lucide="user"></i> Profile</a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
        <div class="mb-8" data-aos="fade-down">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Academic Tasks</h2>
            <p class="text-slate-500 mt-1">Filter, search, and manage your semester assignments.</p>
        </div>

        <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm mb-8" data-aos="fade-up">
            <form method="GET" action="assignments.php" class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[200px] relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" name="search" placeholder="Search by title..." value="<?php echo htmlspecialchars($search_query); ?>"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition text-sm">
                </div>

                <select name="subject" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="All Subjects" <?php if ($subject_filter == 'All Subjects') echo 'selected'; ?>>All Subjects</option>
                    <?php
                    mysqli_data_seek($subjects_list_result, 0);
                    while ($sub = mysqli_fetch_assoc($subjects_list_result)): ?>
                        <option value="<?php echo htmlspecialchars($sub['subject_name']); ?>" <?php if ($subject_filter == $sub['subject_name']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($sub['subject_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <select name="status" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="All Status" <?php if ($status_filter == 'All Status') echo 'selected'; ?>>All Status</option>
                    <option value="Pending" <?php if ($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Submitted" <?php if ($status_filter == 'Submitted') echo 'selected'; ?>>Submitted</option>
                </select>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100 text-sm flex items-center gap-2">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4"></i> Apply Filters
                </button>

                <a href="assignments.php" class="p-2.5 bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 transition"><i data-lucide="refresh-cw" class="w-4 h-4"></i></a>
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="100">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-widest font-black">
                            <th class="px-6 py-5">Assignment Info</th>
                            <th class="px-6 py-5">Subject</th>
                            <th class="px-6 py-5 text-center">Deadline</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-6 py-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)):
                                $assign_id = $row['id'];

                                $check_sub = mysqli_query($conn, "SELECT status FROM submissions WHERE assignment_id = '$assign_id' AND student_id = '$user_id'");
                                $submission = mysqli_fetch_assoc($check_sub);

                                $current_status = $submission ? 'Submitted' : 'Pending';

                                if ($status_filter !== 'All Status' && $status_filter !== $current_status) {
                                    continue;
                                }

                                $isOverdue = ($current_status == 'Pending' && strtotime($row['due_date']) < time());
                                $statusLabel = $isOverdue ? 'Overdue' : strtoupper($current_status);
                                $statusColor = $current_status == 'Submitted' ? 'emerald' : ($isOverdue ? 'rose' : 'orange');
                            ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-5">
                                        <span class="font-bold text-slate-700 block"><?php echo htmlspecialchars($row['title']); ?></span>
                                        <span class="text-[10px] text-slate-400 font-medium tracking-wider">REF: #SASS-<?php echo $row['id']; ?></span>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-semibold text-slate-600">
                                        <?php echo htmlspecialchars($row['subject']); ?>
                                    </td>
                                    <td class="px-6 py-5 text-center text-sm <?php echo $isOverdue ? 'text-rose-500 font-bold' : 'text-slate-500'; ?>">
                                        <?php echo date('d M Y', strtotime($row['due_date'])); ?>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="px-3 py-1 bg-<?php echo $statusColor; ?>-50 text-<?php echo $statusColor; ?>-600 text-[10px] font-black rounded-full border border-<?php echo $statusColor; ?>-100 uppercase">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <?php if ($current_status == 'Pending'): ?>
                                            <a href="submit-assignment.php?id=<?php echo $row['id']; ?>" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition">
                                                Submit
                                            </a>
                                        <?php else: ?>
                                            <div class="flex items-center justify-end gap-2 text-emerald-600 font-bold text-xs">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i> Submitted
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-24 text-center opacity-30 font-bold">No assignments posted yet.</td>
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
        AOS.init();
        lucide.createIcons();

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-hidden');
            document.getElementById('overlay').classList.toggle('hidden');
        }
    </script>
</body>

</html>