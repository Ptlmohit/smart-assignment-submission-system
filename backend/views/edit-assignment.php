<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$teacher_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: manage-assignments.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM assignments WHERE id = ? AND teacher_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $id, $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$asg = mysqli_fetch_assoc($result);

if (!$asg) {
    die("Assignment not found or unauthorized access.");
}

// 3. Handle Update Request
$message = "";
if (isset($_POST['update_btn'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $update_query = "UPDATE assignments SET title = ?, subject = ?, due_date = ?, description = ? WHERE id = ? AND teacher_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssssii", $title, $subject, $due_date, $description, $id, $teacher_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $message = "success";
        // Refresh data for the form
        $asg['title'] = $title;
        $asg['subject'] = $subject;
        $asg['due_date'] = $due_date;
        $asg['description'] = $description;
    } else {
        $message = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Assignment | SASS</title>
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
            <div class="bg-indigo-600 p-1.5 rounded-lg shadow-lg"><i data-lucide="edit-3" class="text-white w-5 h-5"></i></div>
            <h1 class="text-xl font-bold text-slate-800">Edit Task</h1>
        </div>
        <a href="manage-assignments.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600 flex items-center gap-1 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
        </a>
    </nav>

    <main class="max-w-3xl mx-auto pt-28 px-6 pb-12">
        <?php if ($message === "success"): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl flex items-center gap-3 font-bold text-sm" data-aos="zoom-in">
                <i data-lucide="check-circle"></i> Assignment updated successfully!
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up">
            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Assignment Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($asg['title']); ?>" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-semibold">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Subject</label>
                        <select name="subject" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm">
                            <?php
                            $sub_list = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name ASC");
                            while ($s = mysqli_fetch_assoc($sub_list)) {
                                $selected = ($s['subject_name'] == $asg['subject']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($s['subject_name']) . "' $selected>" . htmlspecialchars($s['subject_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Due Date</label>
                        <input type="date" name="due_date" value="<?php echo $asg['due_date']; ?>" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Instructions/Description</label>
                    <textarea name="description" rows="5" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm"><?php
                                                                                                                                                                    /* Safely echo the description or an empty string to prevent warnings */
                                                                                                                                                                    echo isset($asg['description']) ? htmlspecialchars($asg['description']) : '';
                                                                                                                                                                    ?></textarea>
                </div>
                <button type="submit" name="update_btn" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 uppercase tracking-widest text-sm flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Save Changes
                </button>
            </form>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        lucide.createIcons();
    </script>
</body>

</html>