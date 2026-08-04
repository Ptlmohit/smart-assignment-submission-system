<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../frontend/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT name, email, role FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$user_name = $user['name'];
$user_email = $user['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile | SASS</title>
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
                <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg"><i data-lucide="menu" class="w-6 h-6"></i></button>
                <div class="flex items-center gap-2">
                    <div class="bg-blue-600 p-1.5 rounded-lg shadow-lg shadow-blue-200"><i data-lucide="graduation-cap" class="text-white w-5 h-5"></i></div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">SASS</h1>
                </div>
            </div>
            <a href="../auth/logout.php" class="flex items-center gap-2 bg-red-50 text-red-600 px-4 py-2 rounded-xl border border-red-100 hover:bg-red-100 transition font-medium">
                <i data-lucide="log-out" class="w-4 h-4"></i><span class="hidden sm:inline">Logout</span>
            </a>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
        <div class="px-4 py-8 space-y-2">
            <a href="student-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
            <a href="assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="book-open" class="w-5 h-5"></i> My Assignments</a>
            <a href="submit-assignment.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="upload-cloud" class="w-5 h-5"></i> Submit Work</a>
            <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Account</div>
            <a href="profile.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="user" class="w-5 h-5"></i> Profile</a>
        </div>
    </aside>

    <main class="lg:ml-72 pt-24 px-4 lg:px-8 pb-12 transition-all">
        <div class="mb-10 text-center lg:text-left" data-aos="fade-down">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight text-center">Your Profile</h2>
            <p class="text-slate-500 mt-1 text-center">Manage your personal information and account settings.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">

            <div class="space-y-6" data-aos="fade-right">
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm text-center">
                    <div class="relative inline-block mb-4">
                        <div class="w-32 h-32 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 border-4 border-white shadow-lg">
                            <i data-lucide="user" class="w-16 h-16"></i>
                        </div>
                        <button class="absolute bottom-1 right-1 bg-blue-600 text-white p-2 rounded-full border-2 border-white shadow-md hover:bg-blue-700 transition">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($user_name); ?></h3>
                    <p class="text-sm text-slate-400 font-medium uppercase tracking-widest">Student ID: #<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></p>
                    <div class="mt-6 pt-6 border-t border-slate-50 space-y-3 text-left">
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <i data-lucide="mail" class="w-4 h-4 text-blue-500"></i>
                            <?php echo htmlspecialchars($user_email); ?>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                            Status: Active Student
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6" data-aos="fade-up">
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="text-lg font-bold mb-6 flex items-center gap-2">
                        <i data-lucide="settings-2" class="w-5 h-5 text-blue-600"></i>
                        Profile Settings
                    </h4>
                    <form id="profileForm" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Full Name</label>
                                <input type="text" name="name" id="profileName" value="<?php echo htmlspecialchars($user_name); ?>" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition text-sm font-medium">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1">Email Address</label>
                                <input type="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-2xl cursor-not-allowed text-sm text-slate-400 font-medium outline-none">
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest pl-1 text-blue-600">Change Password (Optional)</label>
                            <input type="password" name="current_password" id="currentPass" placeholder="Current Password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition text-sm mb-3">
                            <input type="password" name="new_password" id="newPass" placeholder="New Password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition text-sm">
                        </div>

                        <div class="pt-6">
                            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-blue-700 hover:scale-[1.02] active:scale-[0.98] transition shadow-lg shadow-blue-100 text-sm flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                            </button>
                        </div>
                    </form>

                </div>

                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="text-lg font-bold mb-4 flex items-center gap-2 text-rose-600">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        Danger Zone
                    </h4>
                    <p class="text-sm text-slate-500 mb-6">Once you delete your account, there is no going back. Please be certain.</p>
                    <button onclick="deleteAccount()" class="px-6 py-2 border-2 border-rose-100 text-rose-600 rounded-xl text-xs font-bold hover:bg-rose-50 transition active:scale-95">
                        Request Account Deletion
                    </button>
                </div>
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

        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);

            try {
                const response = await fetch('../auth/update-profile.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();

                if (result.trim() === 'success') {
                    alert('Profile updated successfully!');

                    document.getElementById('currentPass').value = '';
                    document.getElementById('newPass').value = '';
                    location.reload();
                } else {
                    alert(result);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });



        async function deleteAccount() {
            const confirmation = confirm("WARNING: This action is permanent. All your data, including assignments and submissions, will be deleted. Are you sure you want to proceed?");

            if (confirmation) {
                try {
                    const response = await fetch('../auth/delete-account.php', {
                        method: 'POST'
                    });

                    if (!response.ok) {
                        alert("Server error (404/500). Please check the file path for delete-account.php");
                        return;
                    }

                    const result = await response.text();

                    if (result.trim() === 'success') {
                        alert("Account deleted successfully. Redirecting to home...");
                        window.location.href = "../../frontend/index.html";
                    } else {
                        alert(result);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert("An error occurred while trying to delete the account.");
                }
            }
        }
    </script>
</body>

</html>