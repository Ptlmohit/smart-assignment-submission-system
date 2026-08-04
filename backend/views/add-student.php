<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_file = "../config/db.php";
if (!file_exists($db_file)) {
    die("<h2>Critical Error</h2><p>Database file not found at: $db_file</p>");
}
include $db_file; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../frontend/index.html");
    exit();
}

$teacher_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Teacher';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enroll Student | SASS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .active-link { background: #eef2ff; color: #4f46e5; border-right: 4px solid #4f46e5; }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

<nav class="glass-nav fixed w-full top-0 z-50">
    <div class="flex justify-between items-center px-6 py-4">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition"><i data-lucide="menu" class="w-6 h-6"></i></button>
            <div class="flex items-center gap-2 text-indigo-600 font-bold text-xl">
                <i data-lucide="user-plus" class="w-6 h-6"></i> SASS
            </div>
        </div>
        <a href="student-list.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Directory
        </a>
    </div>
</nav>

<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 w-72 h-full bg-white border-r border-slate-100 z-40 pt-20 sidebar-hidden lg:translate-x-0">
    <div class="px-4 py-8 space-y-2">
        <a href="teacher-dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
        <a href="manage-assignments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="folder-plus" class="w-5 h-5"></i> Manage Assignments</a>
        <a href="view-submissions.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-xl font-medium transition group"><i data-lucide="clipboard-check" class="w-5 h-5"></i> Submissions</a>
        <div class="pt-8 pb-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Admin Tools</div>
        <a href="student-list.php" class="active-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition group"><i data-lucide="users" class="w-5 h-5"></i> Student Directory</a>
    </div>
</aside>

<main class="lg:ml-72 pt-28 px-4 lg:px-8 pb-12 transition-all">
    <div class="max-w-xl mx-auto bg-white p-10 rounded-3xl border border-slate-100 shadow-sm" data-aos="fade-up">
        <h2 class="text-2xl font-black text-slate-800 mb-2">Enroll New Student</h2>
        <p class="text-slate-500 mb-8 text-sm">Assign a student to a specific course and generate their credentials.</p>

        <form id="addStudentForm" class="space-y-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Full Name</label>
                <input type="text" name="name" required placeholder="e.g. Rahul Sharma" 
                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-medium">
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Course / Specialization</label>
                <select name="course" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-medium appearance-none">
                    <option value="">Select Course</option>
                    <option value="B.Voc Software Development">B.Voc Software Development</option>
                    <option value="B.Voc Data Analytics">B.Voc Data Analytics</option>
                    <option value="B.Voc Cyber Security">B.Voc Cyber Security</option>
                    <option value="B.Sc Computer Science">B.Sc Computer Science</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Email Address</label>
                <input type="email" name="email" required placeholder="student@college.edu" 
                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-medium">
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Temporary Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-medium">
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-4 mt-4 rounded-2xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                <i data-lucide="user-check" class="w-5 h-5"></i> Confirm Enrollment
            </button>
        </form>
    </div>
</main>

<div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-30 hidden lg:hidden"></div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    lucide.createIcons();
    
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar-hidden');
        document.getElementById('overlay').classList.toggle('hidden');
    }

    document.getElementById('addStudentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('role', 'student'); 
        try {
            const response = await fetch('../auth/register-student-logic.php', { method: 'POST', body: formData });
            const result = await response.text();
            alert(result.trim() === 'success' ? 'Student Enrolled!' : result);
            if(result.trim() === 'success') window.location.href = 'student-list.php';
        } catch (error) { console.error('Error:', error); }
    });
</script>
</body>
</html>