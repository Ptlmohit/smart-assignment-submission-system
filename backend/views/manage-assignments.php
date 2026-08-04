<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$teacher_name = $_SESSION['user_name'];
$teacher_id = $_SESSION['user_id'];

$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : 'All Subjects';
$search_query   = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM assignments WHERE teacher_id = ?";
$params = [$teacher_id];
$types = "i";

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




$query .= " ORDER BY due_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Assignments | SASS</title>
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
                <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg"><i data-lucide="menu" class="w-6 h-6"></i></button>
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg shadow-indigo-200"><i data-lucide="briefcase" class="text-white w-5 h-5"></i></div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-blue-600">SASS Teacher</h1>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm font-bold">
                <span class="hidden md:inline text-slate-400 uppercase tracking-widest text-[10px]">Instructor: <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="../auth/logout.php" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl border border-rose-100 hover:bg-rose-100 transition"><i data-lucide="log-out" class="w-4 h-4 inline mr-1"></i> Logout</a>
            </div>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="teacher-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
            <a href="manage-assignments.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="folder-plus" class="w-5 h-5"></i> Manage Assignments</a>
            <a href="view-submissions.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="clipboard-check" class="w-5 h-5"></i> Submissions</a>
            <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Admin Tools</div>
            <a href="student-list.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="users" class="w-5 h-5"></i> Student Directory</a>
            <a href="teacher-profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="settings" class="w-5 h-5"></i> Profile</a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4" data-aos="fade-down">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Manage Assignments</h2>
                <p class="text-slate-500 mt-1">Full control over classroom tasks and scheduling.</p>
            </div>
            <a href="create-assignment.php" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg hover:bg-indigo-700 transition flex items-center gap-2"><i data-lucide="plus" class="w-5 h-5"></i> Create New</a>
        </div>

        <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm mb-8" data-aos="fade-up">
            <form method="GET" class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[200px] relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" name="search" placeholder="Search assignments..." value="<?php echo htmlspecialchars($search_query); ?>"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                </div>
                <select name="subject" onchange="this.form.submit()" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="All Subjects" <?php if ($subject_filter == 'All Subjects') echo 'selected'; ?>>All Subjects</option>
                    <?php
                    $sub_list = mysqli_query($conn, "SELECT * FROM subjects");
                    while ($s = mysqli_fetch_assoc($sub_list)) {
                        $name = $s['subject_name'];
                        $selected = ($subject_filter == $name) ? 'selected' : '';
                        echo "<option value='$name' $selected>$name</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-2xl font-bold hover:bg-indigo-700 transition text-sm">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="100">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                            <th class="px-6 py-5">Assignment Detail</th>
                            <th class="px-6 py-5">Subject</th>
                            <th class="px-6 py-5">Due Date</th>
                            <th class="px-6 py-5">Submissions</th>
                            <th class="px-6 py-5 text-right">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)):
                                $aid = $row['id'];

                                $count_query = "SELECT COUNT(*) as c FROM submissions WHERE assignment_id = $aid";
                                $count_result = mysqli_query($conn, $count_query);
                                $sub_count = mysqli_fetch_assoc($count_result)['c'];
                            ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-5">
                                        <span class="font-bold text-slate-700 block"><?php echo htmlspecialchars($row['title']); ?></span>
                                        <span class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">REF: #ASG-<?php echo $row['id']; ?></span>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-semibold text-slate-600"><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td class="px-6 py-5 text-sm text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="clock" class="w-4 h-4 text-rose-400"></i>
                                            <?php echo date('d M Y', strtotime($row['due_date'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-slate-700"><?php echo $sub_count; ?></span>
                                            <span class="text-xs text-slate-400">Turned in</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right space-x-2">
                                        <a href="submission-status.php?id=<?php echo $row['id']; ?>"
                                            class="p-2 inline-block bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition shadow-sm"
                                            title="View Submission Status">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="edit-assignment.php?id=<?php echo $row['id']; ?>" class="p-2 inline-block bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-100 transition shadow-sm"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                        <button onclick="deleteAssignment(<?php echo $row['id']; ?>)" class="p-2 inline-block bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition shadow-sm"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-24 text-center opacity-30">
                                    <i data-lucide="folder-x" class="w-16 h-16 mx-auto mb-4"></i>
                                    <p class="text-xl font-bold">No assignments found</p>
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

        function deleteAssignment(id) {
            if (confirm("Are you sure you want to delete this assignment? All associated student records will be removed.")) {
                alert("Delete logic for ID " + id + " would execute here.");
            }
        }
    </script>
</body>

</html>