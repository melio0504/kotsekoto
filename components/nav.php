<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="bg-blue-600 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/kotsekoto/index.php" class="text-2xl font-bold">KotseKoTo</a>
        <div class="space-x-4">
            <a href="/kotsekoto/pages/browse.php" class="hover:underline">Browse Cars</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/kotsekoto/pages/profile.php" class="hover:underline">Profile</a>
                <button id="openLogoutModal" class="bg-red-600 px-4 py-2 rounded hover:bg-red-700">Logout</button>
            <?php else: ?>
                <a href="/kotsekoto/pages/login.php" class="hover:underline">Login</a>
                <a href="/kotsekoto/pages/register.php" class="bg-white text-blue-600 px-4 py-2 rounded hover:bg-gray-100">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php if (isset($_SESSION['user_id'])): ?>
<div id="logoutModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
    <div id="logoutModalContent" class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full text-center transform transition-all duration-300 scale-95 opacity-0">
        <h2 class="text-2xl font-bold mb-4">Confirm Logout</h2>
        <p class="mb-6">Are you sure you want to log out?</p>
        <div class="flex justify-center gap-4">
            <button id="confirmLogout" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Logout</button>
            <button id="cancelLogout" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
        </div>
    </div>
</div>
<script>
    document.getElementById('openLogoutModal').onclick = function() {
        const modal = document.getElementById('logoutModal');
        const content = document.getElementById('logoutModalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    };
    document.getElementById('cancelLogout').onclick = function() {
        const modal = document.getElementById('logoutModal');
        const content = document.getElementById('logoutModalContent');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    };
    document.getElementById('confirmLogout').onclick = function() {
        fetch('/kotsekoto/includes/auth.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=logout'
        })
        .then(res => res.json())
        .then(data => {
            window.location.href = '/kotsekoto/pages/login.php';
        });
    };
</script>
<?php endif; ?>