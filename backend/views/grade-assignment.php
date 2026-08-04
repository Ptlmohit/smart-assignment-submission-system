<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

if (!isset($_GET['sub_id'])) {
    header("Location: view-submissions.php");
    exit();
}

$sub_id = mysqli_real_escape_string($conn, $_GET['sub_id']);

// EXECUTE THE QUERY: You must fetch the data to fill the $data variable
$query = "SELECT s.*, u.name as student_name, a.title as assignment_title, a.subject 
          FROM submissions s
          JOIN users u ON s.student_id = u.id
          JOIN assignments a ON s.assignment_id = a.id
          WHERE s.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $sub_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Submission not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Grade Submission | SASS</title>
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
    </style>
</head>

<body class="bg-slate-50 text-slate-900">

    <nav class="glass-nav fixed w-full top-0 z-50 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg shadow-indigo-200">
                <i data-lucide="pen-tool" class="text-white w-5 h-5"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Grading Portal</h1>
        </div>
        <a href="view-submissions.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600 flex items-center gap-1 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Submissions
        </a>
    </nav>

    <main class="max-w-4xl mx-auto pt-28 px-6 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <div class="space-y-6" data-aos="fade-right">
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h3 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-4">Submission Info</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Student Name</p>
                            <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($data['student_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Assignment</p>
                            <p class="text-md font-semibold text-slate-700"><?php echo htmlspecialchars($data['assignment_title']); ?></p>
                            <p class="text-xs text-indigo-400 font-bold mt-1"><?php echo htmlspecialchars($data['subject']); ?></p>
                        </div>
                        <div class="pt-4 border-t border-slate-50">
                            <a href="../uploads/<?php echo basename($data['file_path']); ?>" target="_blank"
                                class="flex items-center justify-center gap-2 w-full py-4 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition shadow-lg">
                                <i data-lucide="external-link" class="w-4 h-4"></i> View Submission File
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6" data-aos="fade-left">
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h3 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-6">Evaluation</h3>

                    <form id="gradingForm" class="space-y-5">
                        <input type="hidden" name="sub_id" value="<?php echo $sub_id; ?>">

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Marks (Out of 100)</label>
                            <input type="number" name="marks" value="<?php echo $data['marks']; ?>" required min="0" max="100"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-black text-xl">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Instructor Feedback</label>
                            <textarea name="feedback" rows="4" placeholder="Enter constructive feedback here..."
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm font-medium"><?php echo htmlspecialchars($data['feedback']); ?></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 uppercase tracking-widest text-sm flex items-center justify-center gap-2">
                                <i data-lucide="check-circle" class="w-5 h-5"></i> Save & Publish Grade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        lucide.createIcons();

        document.getElementById('gradingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('../auth/update-grade.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.text();

                if (result.trim() === 'success') {
                    alert('Grade saved successfully!');
                    window.location.href = 'view-submissions.php';
                } else {
                    alert(result);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>

</html>