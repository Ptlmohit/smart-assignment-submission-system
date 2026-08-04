<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../frontend/index.html");
    exit();
}

$current_time = date("Y-m-d H:i:s");

$id = $_GET['id'] ?? null;
if ($id) {
    $res = mysqli_query($conn, "SELECT due_date FROM assignments WHERE id = '$id'");
    $asg = mysqli_fetch_assoc($res);
    
    if (strtotime($asg['due_date']) < time()) {
        echo "<script>alert('Deadline passed! You cannot submit this assignment.'); window.location.href='student-dashboard.php';</script>";
        exit();
    }
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$assignment_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$query = "SELECT * FROM assignments WHERE id = '$assignment_id'";
$result = mysqli_query($conn, $query);
$assign_details = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Work | SASS</title>
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
        
        .upload-area:hover { border-color: #2563eb; background-color: #f8faff; }
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
        <a href="../auth/logout.php" class="flex items-center gap-2 bg-red-50 text-red-600 px-4 py-2 rounded-xl border border-red-100 hover:bg-red-100 transition font-medium">
            <i data-lucide="log-out" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Logout</span>
        </a>
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
        <a href="submit-assignment.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group">
            <i data-lucide="upload-cloud" class="w-5 h-5"></i> Submit Work
        </a>
        <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Account</div>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group">
            <i data-lucide="user" class="w-5 h-5 group-hover:text-blue-600"></i> Profile
        </a>
    </div>
</aside>

<main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
    <div class="mb-8" data-aos="fade-down">
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">
            <?php echo $assign_details ? htmlspecialchars($assign_details['title']) : 'Upload Assignment'; ?>
        </h2>
        
        <p class="text-slate-500 mt-1">
            <?php echo isset($assign_details['description']) ? htmlspecialchars($assign_details['description']) : 'Submit your completed work for review and grading.'; ?>
        </p>
    </div>
    <div class="max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
        <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
            <form action="../api/upload_logic.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i data-lucide="list-checks" class="w-4 h-4 text-blue-600"></i>
                        Target Assignment
                    </label>
                    <select name="assignment_id_fallback" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition text-sm appearance-none" 
                            <?php echo $assignment_id ? 'disabled' : 'required'; ?>>
                        <option value="">-- Choose an assignment --</option>
                        <?php
                        $list = mysqli_query($conn, "SELECT id, title FROM assignments WHERE student_id = $user_id AND status = 'Pending'");
                        while($row = mysqli_fetch_assoc($list)) {
                            $selected = ($row['id'] == $assignment_id) ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['title']}</option>";
                        }
                        ?>
                    </select>
                    <?php if($assignment_id): ?>
                        <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider pl-1">Selected via dashboard</p>
                    <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i data-lucide="file-up" class="w-4 h-4 text-blue-600"></i>
                        Document Upload
                    </label>
                    <div class="upload-area relative border-2 border-dashed border-slate-200 p-12 text-center rounded-3xl transition duration-300">
                        <input type="file" name="submission_file" id="file" class="hidden" required onchange="updateFileName()">
                        <label for="file" class="cursor-pointer group">
                            <div class="bg-blue-50 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition duration-300">
                                <i data-lucide="cloud-upload" class="text-blue-600 w-8 h-8"></i>
                            </div>
                            <p id="file-label-text" class="text-blue-600 font-bold group-hover:underline">Click to browse or drag and drop</p>
                            <p class="text-xs text-slate-400 mt-2 font-medium">Supported: PDF, DOCX, ZIP (Max 5MB)</p>
                        </label>
                    </div>
                    <div id="file-preview" class="hidden flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-100 rounded-2xl">
                        <i data-lucide="file-check" class="text-emerald-600 w-5 h-5"></i>
                        <span id="selected-file-name" class="text-xs font-bold text-emerald-700 truncate"></span>
                    </div>
                </div>

                <button type="submit" name="submit_btn" class="w-full flex items-center justify-center gap-3 bg-blue-600 text-white py-4 rounded-2xl font-black hover:bg-blue-700 hover:scale-[1.01] active:scale-[0.98] transition shadow-xl shadow-blue-500/20 uppercase tracking-widest text-sm">
                    Final Submission
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</main>

<div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity"></div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    lucide.createIcons();

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar-hidden');
        document.getElementById('overlay').classList.toggle('hidden');
    }

    function updateFileName() {
        const fileInput = document.getElementById('file');
        const fileName = fileInput.files[0].name;
        const preview = document.getElementById('file-preview');
        const nameLabel = document.getElementById('selected-file-name');
        const mainLabel = document.getElementById('file-label-text');

        if (fileName) {
            preview.classList.remove('hidden');
            nameLabel.textContent = fileName;
            mainLabel.textContent = "Change File";
            lucide.createIcons();
        }
    }
</script>

</body>
</html>