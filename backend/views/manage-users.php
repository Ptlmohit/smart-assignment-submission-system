<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../frontend/index.html");
    exit();
}

if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    if ($del_id == $_SESSION['user_id']) {
        $msg = "Error: You cannot delete your own admin account.";
    } else {
        $user_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE id = '$del_id'"));
        $target_name = $user_info['name'];

        $delete_query = "DELETE FROM users WHERE id = ?";
        $del_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($del_stmt, "i", $del_id);

        if (mysqli_stmt_execute($del_stmt)) {
            $admin_id = $_SESSION['user_id'];
            $log_action = "DELETE";
            $log_details = "Permanently removed user: " . $target_name;
            $log_query = "INSERT INTO system_logs (user_id, action, description, timestamp) VALUES (?, ?, ?, NOW())";
            $log_stmt = mysqli_prepare($conn, $log_query);
            mysqli_stmt_bind_param($log_stmt, "iss", $admin_id, $log_action, $log_details);
            mysqli_stmt_execute($log_stmt);

            $msg = "User account permanently removed and logged.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $target_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);

    $update_query = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $new_role, $target_id);

    if (mysqli_stmt_execute($stmt)) {

        $admin_id = $_SESSION['user_id'];
        $log_action = "UPDATE";
        $log_details = "Updated role to $new_role for user ID: $target_id";
        $log_query = "INSERT INTO system_logs (user_id, action, description, timestamp) VALUES (?, ?, ?, NOW())";
        $log_lstmt = mysqli_prepare($conn, $log_query);
        mysqli_stmt_bind_param($log_lstmt, "iss", $admin_id, $log_action, $log_details);
        mysqli_stmt_execute($log_lstmt);

        $msg = "User role updated successfully and logged.";
    }
}

$users_result = mysqli_query($conn, "SELECT id, name, email, role FROM users ORDER BY role ASC, name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users | SASS Admin</title>
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
            background: #f8fafc;
            color: #0f172a;
            border-right: 4px solid #0f172a;
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
                <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition"><i data-lucide="menu" class="w-6 h-6"></i></button>
                <div class="flex items-center gap-2">
                    <div class="bg-slate-900 p-1.5 rounded-lg shadow-lg"><i data-lucide="users" class="text-white w-5 h-5"></i></div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">User Management</h1>
                </div>
            </div>
            <a href="admin-dashboard.php" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard
            </a>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="admin-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layout-grid" class="w-5 h-5"></i> System Overview</a>
            <a href="manage-users.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="users" class="w-5 h-5"></i> User Management</a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
        <div class="mb-8" data-aos="fade-down">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">System Directory</h2>
            <p class="text-slate-500 mt-1">Audit user accounts and modify access permissions globally.</p>
        </div>

        <?php if (isset($msg)): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 font-bold text-sm">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase tracking-[0.1em] font-black">
                            <th class="px-6 py-5">User</th>
                            <th class="px-6 py-5">Email</th>
                            <th class="px-6 py-5">Access Role</th>
                            <th class="px-6 py-5">Update Permissions</th>
                            <th class="px-6 py-5 text-right">Remove</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <span class="font-bold text-slate-700"><?php echo htmlspecialchars($user['name']); ?></span>
                                </td>
                                <td class="px-6 py-5 text-sm text-slate-500"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border <?php echo $user['role'] == 'admin' ? 'bg-rose-50 text-rose-600 border-rose-100' : ($user['role'] == 'teacher' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-blue-50 text-blue-600 border-blue-100'); ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <form method="POST" class="inline-flex gap-2 items-center">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="role" class="text-xs font-bold bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 outline-none focus:ring-2 focus:ring-slate-900">
                                            <option value="student" <?php if ($user['role'] == 'student') echo 'selected'; ?>>Student</option>
                                            <option value="teacher" <?php if ($user['role'] == 'teacher') echo 'selected'; ?>>Teacher</option>
                                            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="update_role" class="p-1.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage-users.php?delete_id=<?php echo $user['id']; ?>"
                                            onclick="return confirm('WARNING: Are you sure you want to permanently delete this user? This action cannot be undone.')"
                                            class="p-2 inline-block bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition shadow-sm">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-[9px] font-bold text-slate-300 uppercase tracking-tighter italic">Primary Admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-hidden');
        }
    </script>
</body>

</html>