<?php
session_start();
include "../config/db.php";



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $due_date = $_POST['due_date'];

    $query = "INSERT INTO assignments (teacher_id, title, subject, due_date, status) VALUES (?, ?, ?, ?, 'Pending')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isss", $teacher_id, $title, $subject, $due_date);

    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Assignment | SASS</title>
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
                        <i data-lucide="plus-circle" class="text-white w-5 h-5"></i>
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
        <div class="max-w-2xl mx-auto" data-aos="fade-up">
            <div class="bg-white p-8 md:p-12 rounded-3xl border border-slate-100 shadow-sm">
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-slate-800">New Assignment</h2>
                    <p class="text-slate-500 mt-2">Set clear expectations and deadlines for your students.</p>
                </div>

                <form id="createAssignmentForm" class="space-y-6">
                    <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Assignment Title</label>
                        <input type="text" name="title" required placeholder="e.g. Database Normalization Project"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-medium">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Subject</label>
                            <select name="subject" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-medium appearance-none">
                                <option value="">Select Subject</option>
                                <?php
                                $sub_list = mysqli_query($conn, "SELECT * FROM subjects");
                                while ($s = mysqli_fetch_assoc($sub_list)) {
                                    echo "<option value='{$s['subject_name']}'>{$s['subject_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Due Date</label>
                            <input type="date" name="due_date" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-medium">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 uppercase tracking-widest text-sm flex items-center justify-center gap-3">
                            <i data-lucide="send" class="w-5 h-5"></i> Publish Assignment
                        </button>
                    </div>
                </form>
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

        document.getElementById('createAssignmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('../auth/create-assignment-logic.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.text();

                if (result.trim() === 'success') {
                    alert('Assignment published successfully!');
                    window.location.href = 'manage-assignments.php';
                } else {
                    alert(result);
                }
            } catch (error) {
                console.error('Error:', error);
                alert("Connection error. Ensure register-assignment-logic.php exists.");
            }
        });
    </script>
</body>

</html>