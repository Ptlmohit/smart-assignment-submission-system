<?php
session_start();
include "../config/db.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../frontend/index.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage-users.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("User not found.");
}

// 3. Handle Form Submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $update = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $role, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "User updated successfully!";
        // Refresh local data
        $user['name'] = $name;
        $user['email'] = $email;
        $user['role'] = $role;
    } else {
        $message = "Error updating user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | SASS Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

<nav class="fixed w-full top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
        <div class="bg-slate-900 p-1.5 rounded-lg shadow-lg"><i data-lucide="user-cog" class="text-white w-5 h-5"></i></div>
        <h1 class="text-xl font-bold text-slate-900">Edit Account</h1>
    </div>
    <a href="manage-users.php" class="text-sm font-bold text-slate-500 hover:text-slate-900 flex items-center gap-1">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Directory
    </a>
</nav>

<main class="max-w-2xl mx-auto pt-32 px-6 pb-12">
    <div class="bg-white p-8 md:p-12 rounded-3xl border border-slate-100 shadow-sm">
        <div class="mb-8 text-center">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-2xl font-black text-slate-400 mx-auto mb-4">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <h2 class="text-2xl font-black text-slate-800">Modify User Profile</h2>
            <p class="text-slate-500 text-sm">Update credentials for User ID: #<?php echo $user['id']; ?></p>
        </div>

        <?php if($message): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 font-bold text-sm text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required 
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-slate-900 outline-none transition font-medium">
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-slate-900 outline-none transition font-medium">
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">System Role</label>
                <select name="role" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-slate-900 outline-none transition font-medium appearance-none">
                    <option value="student" <?php if($user['role'] == 'student') echo 'selected'; ?>>Student</option>
                    <option value="teacher" <?php if($user['role'] == 'teacher') echo 'selected'; ?>>Teacher</option>
                    <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black hover:bg-slate-800 transition shadow-xl uppercase tracking-widest text-sm flex items-center justify-center gap-3">
                    <i data-lucide="save" class="w-5 h-5"></i> Update Account
                </button>
            </div>
        </form>
    </div>
</main>

<script>lucide.createIcons();</script>
</body>
</html>