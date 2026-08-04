<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$submission_id = $_GET['id'] ?? null;
if (!$submission_id) { header("Location: manage-assignments.php"); exit(); }


$current_res = mysqli_query($conn, "SELECT s.*, u.name as student_name, a.title as asg_title 
    FROM submissions s 
    JOIN users u ON s.student_id = u.id 
    JOIN assignments a ON s.assignment_id = a.id 
    WHERE s.id = '$submission_id'");
$current_sub = mysqli_fetch_assoc($current_res);
$assignment_id = $current_sub['assignment_id'];
$textA = isset($current_sub['submission_text']) ? $current_sub['submission_text'] : ""; 

if (empty($textA)) {
    echo "<script>alert('No text content found for this submission. Similarity check cannot be performed.'); window.location.href='submission-status.php?id=$assignment_id';</script>";
    exit();
}

$others = mysqli_query($conn, "SELECT s.*, u.name 
    FROM submissions s 
    JOIN users u ON s.student_id = u.id 
    WHERE s.assignment_id = '$assignment_id' AND s.id != '$submission_id'");

$highest_match = 0;
$match_name = "No matches found";
$textB = "";

while ($row = mysqli_fetch_assoc($others)) {
    
    similar_text($textA, $row['submission_text'], $percent);
    
    if ($percent > $highest_match) {
        $highest_match = round($percent, 2);
        $match_name = $row['name'];
        $textB = $row['submission_text'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Copy Checker | SASS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

<main class="max-w-7xl mx-auto pt-12 px-6">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Similarity Analysis</h2>
            <p class="text-slate-500">Comparing <b><?php echo $current_sub['student_name']; ?></b> against peer submissions.</p>
        </div>
        <div class="text-right">
            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Highest Match Found</span>
            <div class="px-6 py-3 rounded-2xl font-black text-xl <?php echo $highest_match > 75 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'; ?>">
                <?php echo $highest_match; ?>% Similarity
            </div>
        </div>
    </div>

    

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">A</div>
                <h3 class="font-bold text-slate-800"><?php echo $current_sub['student_name']; ?></h3>
            </div>
            <div class="bg-slate-50 p-6 rounded-2xl text-sm leading-relaxed text-slate-600 h-[400px] overflow-y-auto border border-slate-100">
                <?php echo nl2br(htmlspecialchars($textA)); ?>
            </div>
        </div>

        <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-bold">B</div>
                <h3 class="font-bold text-slate-800">Best Match: <?php echo $match_name; ?></h3>
            </div>
            <div class="bg-slate-50 p-6 rounded-2xl text-sm leading-relaxed text-slate-600 h-[400px] overflow-y-auto border border-slate-100">
                <?php echo $textB ? nl2br(htmlspecialchars($textB)) : "<p class='italic text-slate-400'>No other submissions to compare.</p>"; ?>
            </div>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm mb-12">
        <h3 class="text-xl font-bold text-slate-800 mb-6">Final Evaluation</h3>
        <form action="../api/grade_submission.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
            <div class="md:col-span-1">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Award Marks</label>
                <input type="number" name="marks" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Teacher Feedback</label>
                <input type="text" name="feedback" placeholder="Reason for grade or notes on similarity..." class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black hover:bg-black transition shadow-xl">
                    Submit Grade & Feedback
                </button>
            </div>
        </form>
    </div>
</main>

<script>lucide.createIcons();</script>
</body>
</html>