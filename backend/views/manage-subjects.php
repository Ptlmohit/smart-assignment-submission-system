<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_subject'])) {
    $sub_name = mysqli_real_escape_string($conn, $_POST['new_subject']);
    $check = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_name = '$sub_name'");

    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO subjects (subject_name) VALUES ('$sub_name')");
        $message = "Subject added successfully!";
    } else {
        $message = "Error: Subject already exists.";
    }
}

$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Subjects | SASS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900">

    <main class="max-w-4xl mx-auto pt-12 px-6">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-800">Subject Management</h2>
            <a href="teacher-dashboard.php" class="text-indigo-600 font-bold text-sm hover:underline">← Back to Dashboard</a>
        </div>

        <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm mb-10">
            <form method="POST" class="flex flex-col md:flex-row gap-4">
                <input type="text" name="new_subject" required placeholder="Enter Subject Name (e.g. Python Programming)"
                    class="flex-1 px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition">
                <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-indigo-700 transition shadow-lg">
                    Add Subject
                </button>
            </form>
            <?php if ($message): ?>
                <p class="mt-4 text-sm font-bold <?php echo strpos($message, 'Error') !== false ? 'text-rose-500' : 'text-emerald-500'; ?>">
                    <?php echo $message; ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-widest font-black">
                    <tr>
                        <th class="px-6 py-4">Active Subjects</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($s['subject_name']); ?></td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="deleteSubject(<?php echo $s['id']; ?>)"
                                    class="text-rose-500 hover:text-rose-700 transition text-sm font-bold flex items-center gap-1 ml-auto">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        lucide.createIcons();

        async function deleteSubject(id) {
            if (confirm("Are you sure? Removing this subject will affect assignment filters.")) {
                const formData = new FormData();
                formData.append('id', id);

                try {
                    const response = await fetch('../auth/delete-subject.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.text();

                    if (result.trim() === 'success') {
                        location.reload(); 
                    } else {
                        alert(result);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }
    </script>
</body>

</html>